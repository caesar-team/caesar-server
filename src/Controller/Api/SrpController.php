<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Entity\Srp;
use App\Entity\User;
use App\Event\User\RegistrationCompletedEvent;
use App\Factory\Entity\UserFactory;
use App\Factory\View\Srp\SrpPrepareViewFactory;
use App\Form\Type\Request\Srp\LoginPrepareRequestType;
use App\Form\Type\Request\Srp\LoginType;
use App\Form\Type\Request\Srp\RegistrationRequestType;
use App\Form\Type\Srp\UpdatePasswordType;
use App\Model\View\Srp\PreparedSrpView;
use App\Modifier\SrpModifier;
use App\Request\Auth\LoginRequest;
use App\Request\Srp\LoginPrepareRequest;
use App\Request\Srp\RegistrationRequest;
use App\Security\Authentication\SrppAuthenticator;
use App\Services\SrpHandler;
use App\Services\SrpUserManager;
use Exception;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserManagerInterface;
use Fourxxi\RestRequestError\Exception\FormInvalidRequestException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use RuntimeException;
use Swagger\Annotations as SWG;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

final class SrpController extends AbstractController
{
    /**
     * @SWG\Tag(name="Srp")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=RegistrationRequestType::class)
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
     *     path="/api/auth/srpp/registration",
     *     name="api_srp_registration",
     *     methods={"POST"}
     * )
     *
     * @throws \Exception
     */
    public function registerAction(
        Request $request,
        UserManagerInterface $userManager,
        UserFactory $factory,
        EventDispatcherInterface $eventDispatcher
    ): void {
        $registerRequest = new RegistrationRequest();

        $form = $this->createForm(RegistrationRequestType::class, $registerRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        $user = $factory->createFromRegistrationRequest($registerRequest);
        $userManager->updateUser($user);
        $eventDispatcher->dispatch(new RegistrationCompletedEvent($user));
    }

    /**
     * @SWG\Tag(name="Srp")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=LoginPrepareRequestType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success login prepared",
     *     @Model(type=PreparedSrpView::class)
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
     *     path="/api/auth/srpp/login_prepare",
     *     name="api_srp_login_prepare",
     *     methods={"POST"}
     * )
     *
     * @throws Exception
     */
    public function prepareLoginAction(
        Request $request,
        SrpModifier $modifier,
        SrpPrepareViewFactory $viewFactory
    ): PreparedSrpView {
        $prepareRequest = new LoginPrepareRequest();
        $form = $this->createForm(LoginPrepareRequestType::class, $prepareRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        $srp = $modifier->modifyByRequest($prepareRequest);

        return $viewFactory->createSingle($srp);
    }

    /**
     * @SWG\Tag(name="Srp")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=\App\Form\Request\Srp\LoginType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success login prepared",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="string",
     *             property="secondMatcher",
     *             example="129466c0cc982d254c6712e0a5155b1a7fed06eea59b3d7b4620442e54d38ec2"
     *         ),
     *         @SWG\Property(
     *             type="string",
     *             property="jwt",
     *             example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE1NDcwMjU4NjAsImV4cCI6MTU0NzExMjI2MCwicm9sZXMiOlsiUk9MRV9VU0VSIl0sInVzZXJuYW1lIjoiaXBvcG92KzZANHh4aS5jb20ifQ.WTL0Wsd6eQieq2xlukeRZvyoRW6dMhoXqFW7JTXia9-VZIHaWoTGPWZytOlY_VOhJ2NGYZ5LI3XD-_wtAmYpgUTutiV5DVl-zGrVmzEKtb2VagNggj0SQeo-4gSgTlrDxZW1iP3VU_FAN8-DCWion7M2WJxhQ1UHZaUALTdLVOOE-AWPWA9-ue5b5XgQ6-noaa6XtaI_vhnyR6C39O0OVV0VgfqXiKuhzknGWr5WrLYgFM1CGso3OIltvY8LZZqlhmBXF5hV7hgTKhPdWUxrMNLmJNAwhSLoWpbZGDuJzRCqC8p5wDv8Q6LSDFK5iG0Vueg5VBecGhVuyhVA9qaVHJRK1amfFweTSQ4RHd3Ly11FsEoUn5yB_sRlFHRyiVswvbjfkVaLusYy8RosOnCm3r8B_FR08ylCtDdEvj56EAag9W3dA1VCG8zcEbUwTqwsnPC97teCUbEseP2qpq-8Wic8DViuRv9z4x5yFrio1R7sCz4-5TI4lmwE002GbN_whd5YHDrRsdu6_9RxR7iuI_9OOdLLe7iVGtWd1RE_NqaOv-ymD5mhKDnZlOjXgsKbhDrvaTZ9s7v4l9H2EmJsTpjNRnaQ6yNmtYMyWR8UQP0cilwoHBSwo3L70kVwfMu0T4WTezd8hgTO6VoX9UG8tLpNcNaoMt6et8Rdsp7uQQU"
     *         )
     *     )
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
     *                     example="This value should not be blank."
     *                 )
     *             )
     *         )
     *     )
     * )
     *
     * @Route(
     *     path="/api/auth/srpp/login",
     *     name="api_srp_login",
     *     methods={"POST"}
     * )
     */
    public function loginAction(Request $request, SrpUserManager $srpUserManager, JWTTokenManagerInterface $jwtManager): array
    {
        $loginRequest = new LoginRequest();
        $form = $this->createForm(LoginType::class, $loginRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        $sessionMatcher = $srpUserManager->getMatcherSession($loginRequest);

        if ($sessionMatcher->getMatcher() !== $loginRequest->getMatcher()) {
            $message = $this->translator->trans('app.exception.not_equal_matchers');
            throw new BadRequestHttpException($message);
        }

        $secondMatcher = $srpUserManager->generateSecondMatcher($loginRequest, $sessionMatcher);

        return [
            'secondMatcher' => $secondMatcher,
            'jwt' => $jwtManager->create($loginRequest->getUser()),
        ];
    }

    /**
     * @SWG\Tag(name="Srp")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=UpdatePasswordType::class)
     * )
     * @SWG\Response(
     *     response=204,
     *     description="Password changed"
     * )
     *
     * @SWG\Response(
     *     response=403,
     *     description="Access denied"
     * )
     *
     * @Route(
     *     path="/api/auth/srpp/password",
     *     name="api_srp_update_password",
     *     methods={"PATCH"}
     * )
     *
     * @throws Exception
     */
    public function updatePassword(Request $request, UserManagerInterface $manager): void
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new AccessDeniedHttpException();
        }

        if (is_null($user->getSrp())) {
            $message = $this->translator->trans('app.exception.invalid_srp');
            throw new BadRequestHttpException($message);
        }

        $form = $this->createForm(UpdatePasswordType::class, $user);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        if ($user->hasRole(User::ROLE_READ_ONLY_USER)) {
            $user->setFlowStatus(User::FLOW_STATUS_FINISHED);
        }
        $manager->updateUser($user);
    }

    /**
     * @SWG\Tag(name="Srp")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=UpdatePasswordType::class)
     * )
     * @SWG\Response(
     *     response=204,
     *     description="Password changed"
     * )
     *
     * @SWG\Response(
     *     response=403,
     *     description="Access denied"
     * )
     *
     * @param mixed $token
     */
    public function resetPassword(
        Request $request,
        $token,
        UserManagerInterface $userManager,
        EventDispatcherInterface $eventDispatcher
    ): ?Response {
        $user = $userManager->findUserByConfirmationToken($token);

        if (!$user instanceof User) {
            throw new AccessDeniedHttpException();
        }

        if (is_null($user->getSrp())) {
            $message = $this->translator->trans('app.exception.invalid_srp');
            throw new BadRequestHttpException($message);
        }

        $event = new GetResponseUserEvent($user, $request);
        /**
         * @phpstan-ignore-next-line
         * @psalm-suppress InvalidArgument
         * @psalm-suppress TooManyArguments
         */
        $eventDispatcher->dispatch(FOSUserEvents::RESETTING_RESET_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $this->createForm(UpdatePasswordType::class, $user);
        $form->submit($request->request->all());

        if ($form->isValid()) {
            $event = new FormEvent($form, $request);
            /**
             * @phpstan-ignore-next-line
             * @psalm-suppress InvalidArgument
             * @psalm-suppress TooManyArguments
             */
            $eventDispatcher->dispatch(FOSUserEvents::RESETTING_RESET_SUCCESS, $event);
            $user->setEnabled(true);
            $userManager->updateUser($user);

            if (null === $response = $event->getResponse()) {
                $url = $this->generateUrl('google_login');
                $response = new RedirectResponse($url);
            }

            /**
             * @phpstan-ignore-next-line
             * @psalm-suppress InvalidArgument
             * @psalm-suppress TooManyArguments
             */
            $eventDispatcher->dispatch(
                FOSUserEvents::RESETTING_RESET_COMPLETED,
                new FilterUserResponseEvent($user, $request, $response)
            );
        } else {
            throw new FormInvalidRequestException($form);
        }

        return null;
    }

    /**
     * @Route(
     *     path="/api/auth/srpp/login2",
     *     name="srp_login2",
     *     methods={"POST"}
     * )
     */
    public function login2Action(Request $request, SrpHandler $srpHandler): JsonResponse
    {
        $parsedRequest = json_decode($request->getContent(), true);
        /** @var User $user */
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $parsedRequest['email']]);
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

        if ($matcher !== $parsedRequest['matcher']) {
            throw new BadRequestHttpException('Matchers are not equals');
        }

        $k = $srpHandler->generateSessionKey($S);
        $session = $request->getSession();
        $session->set(SrppAuthenticator::SERVER_SESSION_KEY_FIELD, $k);

        $m2 = $srpHandler->generateSecondMatcher(
            $srp->getPublicClientEphemeralValue(),
            $matcher,
            $S
        );

        return new JsonResponse([
            'secondMatcher' => $m2,
        ]);
    }

    /**
     * @Route(path="/api/srp_login_confirm", name="srp_login_confirm", methods={"POST"})
     */
    public function compareSessionKeysAndAuthorize(Request $request)
    {
        throw new RuntimeException('You should register an authenticator to pass this route');
    }
}
