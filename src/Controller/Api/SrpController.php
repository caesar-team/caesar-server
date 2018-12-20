<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Srp;
use App\Entity\User;
use App\Factory\View\Srp\SrpPrepareViewFactory;
use App\Form\Request\Srp\LoginPrepareType;
use App\Form\Request\Srp\RegistrationType;
use App\Model\View\Srp\PreparedSrpView;
use App\Services\SrpHandler;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

final class SrpController extends AbstractController
{
    /**
     * @SWG\Tag(name="Srp")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=\App\Form\Request\Srp\RegistrationType::class)
     * )
     * @SWG\Response(
     *     response=204,
     *     description="Success registration"
     * )
     *
     * @SWG\Response(
     *     response=400,
     *     description="Error in user input",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="object",
     *             property="errors",
     *             @SWG\Property(
     *                 type="array",
     *                 property="email",
     *                 @SWG\Items(
     *                     type="string",
     *                     example="This value already used"
     *                 )
     *             )
     *         )
     *     )
     * )
     *
     * @Route(
     *     path="/api/srp/registration",
     *     name="api_srp_registration",
     *     methods={"POST"}
     * )
     *
     * @param Request              $request
     * @param UserManagerInterface $manager
     *
     * @return null
     */
    public function registerAction(Request $request, UserManagerInterface $manager)
    {
        $user = new User(new Srp());

        $form = $this->createForm(RegistrationType::class, $user); //TODO email confirmation
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $manager->updateUser($user);

        return null;
    }

    /**
     * @SWG\Tag(name="Srp")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=\App\Form\Request\Srp\LoginPrepareType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success login prepared",
     *     @Model(type=\App\Model\View\Srp\PreparedSrpView::class)
     * )
     *
     * @SWG\Response(
     *     response=400,
     *     description="Error in user input",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="object",
     *             property="errors",
     *             @SWG\Property(
     *                 type="array",
     *                 property="email",
     *                 @SWG\Items(
     *                     type="string",
     *                     example="This value already used"
     *                 )
     *             )
     *         )
     *     )
     * )
     *
     * @Route(
     *     path="/api/srp/login_prepare",
     *     name="api_srp_login_prepare",
     *     methods={"POST"}
     * )
     *
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     * @param SrpHandler             $srpHandler
     * @param SrpPrepareViewFactory  $viewFactory
     *
     * @return PreparedSrpView|FormInterface
     */
    public function prepareLoginAction(Request $request, EntityManagerInterface $entityManager, SrpHandler $srpHandler, SrpPrepareViewFactory $viewFactory)
    {
        $email = $request->request->get('email');
        /** @var User $user */
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $email]);
        if (null === $user) {
            throw new BadRequestHttpException('No such user');
        }
        $srp = $user->getSrp();

        $form = $this->createForm(LoginPrepareType::class, $srp);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $privateEphemeral = $srpHandler->getRandomSeed();
        $publicEphemeralValue = $srpHandler->generatePublicServerEphemeral($privateEphemeral, $srp->getVerifier());
        $srp->setPublicServerEphemeralValue($publicEphemeralValue);
        $srp->setPrivateServerEphemeralValue($privateEphemeral);

        $entityManager->persist($srp);
        $entityManager->flush();

        return $viewFactory->create($srp);
    }

    /**
     * @Route(
     *     path="/api/srp/login",
     *     name="api_srp_login",
     *     methods={"POST"}
     * )
     *
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     * @param SrpHandler             $srpHandler
     *
     * @return null
     */
    public function login2Action(Request $request, EntityManagerInterface $entityManager, SrpHandler $srpHandler)
    {
        $email = $request->request->get('email');
        /** @var User $user */
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $email]);
        $srp = $user->getSrp();

        $S = $srpHandler->generateSessionServer(
            $srp->getPublicClientEphemeralValue(),
            $srp->getPublicServerEphemeralValue(),
            $srp->getPrivateServerEphemeralValue(),
            $srp->getVerifier()
        );

        $matcher = $srpHandler->generateFirstMatcher(
            $srp->getPublicClientEphemeralValue(),
            $srp->getPublicServerEphemeralValue(),
            $S
        );

        if ($matcher !== $request->request->get('matcher')) {
            throw new BadCredentialsException('Matchers are not equals');
        }

//        dump($S);
        $k = $srpHandler->generateSessionKey($S); //This is session key
//        dump($k);
        $user->setToken($k);
        $entityManager->flush();
//        $user->setSessionKey($k);
//        dump($k);

        $m2 = $srpHandler->generateSecondMatcher(
            $srp->getPublicClientEphemeralValue(),
            $matcher,
            $S
        );

        return [
            'matcher2' => $m2,
        ];
    }

    /**
     * @Route(
     *     path="/srp",
     *     name="srp_form",
     *     methods={"GET"}
     * )
     */
    public function srpFormAction()
    {
        return $this->render('srp.html.twig');
    }
}
