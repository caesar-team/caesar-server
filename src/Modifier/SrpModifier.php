<?php

declare(strict_types=1);

namespace App\Modifier;

use App\Entity\Srp;
use App\Request\Srp\LoginPrepareRequest;
use App\Services\SrpHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class SrpModifier
{
    private EntityManagerInterface $entityManager;

    private TranslatorInterface $translator;

    private SrpHandler $srpHandler;

    public function __construct(
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager,
        SrpHandler $srpHandler
    ) {
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->srpHandler = $srpHandler;
    }

    public function modifyByRequest(LoginPrepareRequest $request): Srp
    {
        $user = $request->getUser();
        if (null === $user) {
            $message = $this->translator->trans('app.exception.user_not_found');

            throw new AccessDeniedHttpException($message, null, Response::HTTP_BAD_REQUEST);
        }

        $srp = $user->getSrp();
        if (null === $srp) {
            $message = $this->translator->trans('app.exception.invalid_srp');

            throw new AccessDeniedHttpException($message, null, Response::HTTP_BAD_REQUEST);
        }
        $srp->setPublicClientEphemeralValue($request->getPublicEphemeralValue());

        $privateEphemeral = $this->srpHandler->getRandomSeed();
        $publicEphemeralValue = $this->srpHandler->generatePublicServerEphemeral($privateEphemeral, $srp->getVerifier());
        $srp->setPublicServerEphemeralValue($publicEphemeralValue);
        $srp->setPrivateServerEphemeralValue($privateEphemeral);

        $this->entityManager->persist($srp);
        $this->entityManager->flush();

        return $srp;
    }
}
