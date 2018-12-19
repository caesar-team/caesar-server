<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Srp;
use App\Entity\User;
use App\Services\SrpHandler;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

final class SrpController extends AbstractController
{
    /**
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
        dump($request);
        $user = new User(new Srp());

        $user->setEmail($request->request->get('email'));
        $user->setUsername($request->request->get('email'));
        $user->setPlainPassword(uniqid());
        $user->setEnabled(true);

        $user->getSrp()->setSeed($request->request->get('seed'));
        $user->getSrp()->setVerifier($request->request->get('verifier'));

        $manager->updateUser($user);

        return null;
    }

    /**
     * @Route(
     *     path="/api/srp/login1",
     *     name="api_srp_login1",
     *     methods={"POST"}
     * )
     *
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     * @param SrpHandler             $srpHandler
     *
     * @return null
     */
    public function loginAction(Request $request, EntityManagerInterface $entityManager, SrpHandler $srpHandler)
    {
        $email = $request->request->get('email');
        /** @var User $user */
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $email]);
        $srp = $user->getSrp();

        $privateEphemeral = $srpHandler->getRandomSeed();
        $publicEphemeralValue = $srpHandler->generatePublicServerEphemeral($privateEphemeral, $srp->getVerifier());

        $srp->setPublicClientEphemeralValue($request->request->get('publicEphemeralValue'));
        $srp->setPublicServerEphemeralValue($publicEphemeralValue);
        $srp->setPrivateServerEphemeralValue($privateEphemeral);

        $entityManager->persist($srp);
        $entityManager->flush();

        return [
            'seed' => $srp->getSeed(),
            'publicEphemeralValue' => $publicEphemeralValue,
        ];
    }

    /**
     * @Route(
     *     path="/api/srp/login2",
     *     name="api_srp_login2",
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
