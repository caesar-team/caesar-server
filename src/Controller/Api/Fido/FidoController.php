<?php

declare(strict_types=1);

namespace App\Controller\Api\Fido;

use App\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webauthn\AuthenticationExtensions\AuthenticationExtension;
use Webauthn\AuthenticationExtensions\AuthenticationExtensionsClientInputs;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;
use Cose\Algorithms;
use Webauthn\PublicKeyCredentialParameters;
use Symfony\Component\Routing\Annotation\Route;

final class FidoController extends AbstractController
{
    /**
     * @Route(path="/api/anonymous/fido/test", name="fido")
     * @param Request $request
     * @return Response
     */
    public function test(Request $request): Response
    {
        $rpEntity = new PublicKeyCredentialRpEntity(
            'My Super Secured Application',
            '06ec9f6d.ngrok.io',
            'https://fourxxi.atlassian.net/secure/projectavatar?pid=15003&avatarId=18528&size=xxlarge'
        );
        $userEntity = new PublicKeyCredentialUserEntity(
            '@cypher-Angel-3000',
            '123e4567-e89b-12d3-a456-426655440000',
            'Mighty Mike',
            'https://foo.example.co/avatar/123e4567-e89b-12d3-a456-426655440000'
        );
        $challenge = random_bytes(32);
        $publicKeyCredentialParametersList = [
            new PublicKeyCredentialParameters('public-key', Algorithms::COSE_ALGORITHM_ES256),
            new PublicKeyCredentialParameters('public-key', Algorithms::COSE_ALGORITHM_RS256),
        ];
        $excludedPublicKeyDescriptors = [
            new PublicKeyCredentialDescriptor(PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY, 'ABCDEFGH'),
            new PublicKeyCredentialDescriptor(PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY, 'ABCDEFGH'),
        ];
        $timeout = 20000;
        $authenticatorSelectionCriteria = new AuthenticatorSelectionCriteria();
        $extensions = new AuthenticationExtensionsClientInputs();
        $extensions->add(new AuthenticationExtension('loc', true));

        $publicKeyCredentialCreationOptions = new PublicKeyCredentialCreationOptions(
            $rpEntity,
            $userEntity,
            $challenge,
            $publicKeyCredentialParametersList,
            $timeout,
            $excludedPublicKeyDescriptors,
            $authenticatorSelectionCriteria,
            PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE,
            $extensions
        );

        return $this->render('fido/fido_test.html.twig', ['options' => json_encode($publicKeyCredentialCreationOptions, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]);
    }
}