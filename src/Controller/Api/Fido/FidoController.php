<?php

declare(strict_types=1);

namespace App\Controller\Api\Fido;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Fido\PublicKeyCredentialOptionsContext;
use App\Repository\PublicKeyCredentialSourceRepository;
use App\Repository\UserRepository;
use App\Validator\AssertionResponseValidator;
use App\Validator\AttestationResponseValidator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRequestOptions;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Route(path="/api/fido")
 * @Route(path="/api/anonymous/fido")
 */
final class FidoController extends AbstractController
{
    private const SESSION_CREDENTIAL_CREATION_OPTIONS = 'publicKeyCredentialCreationOptions';
    private const SESSION_CREDENTIAL_REQUEST_OPTIONS = 'publicKeyCredentialRequestOptions';

    /**
     * @Route(path="/create", name="fido_get_creation_options", methods={"GET"})
     * @Route(path="/create", name="fido_get_creation_options", methods={"GET"})
     * @param Request $request
     * @param PublicKeyCredentialOptionsContext $credentialOptionsContext
     * @param UserRepository $userRepository
     * @return string
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function creationOptions(
        Request $request,
        PublicKeyCredentialOptionsContext $credentialOptionsContext, UserRepository $userRepository
    )
    {
        $session = $request->getSession();
        $session->set(self::SESSION_CREDENTIAL_CREATION_OPTIONS, null);
        /** @var User $user */
        $user = $this->getUser();

        $user = $userRepository->findByEmail('gribanovskiy.mihail@gmail.com');

        $user->setIsTryingRegister(true);
        $credentialCreationOptions = $credentialOptionsContext->createOptions($user);

        $encodedOptions = json_encode($credentialCreationOptions, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $session->set(self::SESSION_CREDENTIAL_CREATION_OPTIONS, $encodedOptions);

        return $this->render('fido/fido_register_prepare.html.twig', ['options' => $encodedOptions]);

//        $response = new Response($encodedOptions);
//        $response->headers->set('Content-Type', 'application/json');
//
//        return $response;
    }

    /**
     * @Route(path="/register", name="fido_register")
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     */
    public function register(
        Request $request,
        PublicKeyCredentialSourceRepository $publicKeyCredentialSourceRepository,
        AttestationResponseValidator $responseValidator
    )
    {
        $session = $request->getSession();
        // Retrieve the PublicKeyCredentialCreationOptions object created earlier
        $publicKeyCredentialCreationOptions = $session->get(self::SESSION_CREDENTIAL_CREATION_OPTIONS);
        $publicKeyCredentialCreationOptions = PublicKeyCredentialCreationOptions::createFromString($publicKeyCredentialCreationOptions);

        // Retrieve de data sent by the device
        $data = base64_decode($request->query->get('data'));

        try {
            $responseValidator->check($data, $publicKeyCredentialCreationOptions);
        } catch (\Throwable $exception) {
            $this->redirectToRoute('fido_get_creation_options');
        }

        $publicKeyCredentialSource = $responseValidator->getVerifiedPublicKeyCredentialSource($publicKeyCredentialCreationOptions);
        $publicKeyCredentialSourceRepository->saveCredentialSource($publicKeyCredentialSource);

        return $this->redirectToRoute('fido_login_prepare');
    }

    /**
     * @Route(path="/login_prepare", name="fido_login_prepare")
     * @param Request $request
     * @param PublicKeyCredentialSourceRepository $publicKeyCredentialSourceRepository
     * @param UserRepository $userRepository
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function loginPrepare(
        Request $request,
        PublicKeyCredentialOptionsContext $credentialOptionsContext,
        UserRepository $userRepository
    )
    {
        $session = $request->getSession();
        $session->set(self::SESSION_CREDENTIAL_REQUEST_OPTIONS, null);

        /** @var User $user */
        $user = $this->getUser();
        $user = $userRepository->findByEmail('gribanovskiy.mihail@gmail.com');

        // Public Key Credential Request Options
        $publicKeyCredentialRequestOptions = $credentialOptionsContext->createOptions($user);
        $encodedOptions = json_encode($publicKeyCredentialRequestOptions, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $session->set(self::SESSION_CREDENTIAL_REQUEST_OPTIONS, $encodedOptions);

        return $this->render('fido/fido_login.html.twig', ['options' => $encodedOptions]);
    }

    /**
     * @Route(path="/login_check", name="fido_login_check")
     */
    public function loginCheck(
        Request $request,
        UserRepository $userRepository,
        AssertionResponseValidator $responseValidator
    )
    {
        $session = $request->getSession();
        $publicKeyCredentialRequestOptions = $session->get(self::SESSION_CREDENTIAL_REQUEST_OPTIONS);
        $publicKeyCredentialRequestOptions = PublicKeyCredentialRequestOptions::createFromString($publicKeyCredentialRequestOptions);
        $data = base64_decode($request->query->get('data'));

        /** @var User $user */
        $user = $userRepository->findOneBy(['email' => 'gribanovskiy.mihail@gmail.com']);

        try {
            $responseValidator->check($data, $publicKeyCredentialRequestOptions, $user);

            return $this->render('fido/fido_done.html.twig');
        } catch (\Throwable $throwable) {
            $this->redirectToRoute('fido_get_creation_options');
        }
    }
}