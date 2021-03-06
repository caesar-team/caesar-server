<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Form\Type\Request\User\TwoFactoryAuthEnableRequestType;
use App\Modifier\UserModifier;
use App\Repository\UserRepository;
use App\Request\User\TwoFactoryAuthEnableRequest;
use App\Security\BackupCodes\BackupCodeCreator;
use App\Security\TwoFactor\GoogleAuthenticator;
use App\Security\Voter\BackupCodesVoter;
use Fourxxi\RestRequestError\Exception\FormInvalidRequestException;
use Nelmio\ApiDocBundle\Annotation\Model;
use RuntimeException;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Swagger\Annotations as SWG;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class TwoFactorAuthController extends AbstractController
{
    /**
     * Activate 2FA on your account.
     *
     * @SWG\Tag(name="Security")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=TwoFactoryAuthEnableRequestType::class)
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Already created"
     * )
     * @SWG\Response(
     *     response=204,
     *     description="Succeed two factor created"
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Returns errors",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="object",
     *             property="errors",
     *             @SWG\Property(
     *                 type="array",
     *                 property="authCode",
     *                 @SWG\Items(
     *                     type="string",
     *                     example="List of errors"
     *                 )
     *             )
     *         )
     *     )
     * )
     *
     * @Route(
     *     path="/api/auth/2fa/activate",
     *     name="api_security_2fa_activate",
     *     methods={"POST"}
     * )
     */
    public function activateTwoFactor(Request $request, UserModifier $modifier): Response
    {
        $user = $this->getUser();
        if ($user->getGoogleAuthenticatorSecret()) {
            return Response::create(null, Response::HTTP_CREATED);
        }
        $user->setGoogleAuthenticatorSecret($request->get('secret'));

        $activateRequest = new TwoFactoryAuthEnableRequest($user);

        $form = $this->createForm(TwoFactoryAuthEnableRequestType::class, $activateRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        $modifier->modifyByRequest($activateRequest);

        return new Response();
    }

    /**
     * Get 2FA QR code.
     *
     * @SWG\Tag(name="Security")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Return QR code and Secret",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="string",
     *             description="Url to QR code",
     *             property="qr",
     *             example="https://chart.googleapis.com/chart?chs=200x200",
     *         ),
     *         @SWG\Property(
     *             type="string",
     *             property="code",
     *             description="Code",
     *             example="7IM4AJDIW4Z6KFXH",
     *         )
     *     )
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(
     *     path="/api/auth/2fa",
     *     name="api_security_2fa_code",
     *     methods={"GET"}
     * )
     *
     * @return FormInterface|JsonResponse
     */
    public function getCode(GoogleAuthenticatorInterface $twoFactor)
    {
        /** @var User $user */
        $user = $this->getUser();
        $user->setGoogleAuthenticatorSecret($twoFactor->generateSecret());

        if ($twoFactor instanceof GoogleAuthenticator) {
            return new JsonResponse([
                'qr' => $twoFactor->getUrl($user),
                'code' => $user->getGoogleAuthenticatorSecret(),
            ]);
        }

        return new JsonResponse([
            'qr' => $twoFactor->getQRContent($user),
            'code' => $user->getGoogleAuthenticatorSecret(),
        ]);
    }

    /**
     * Authenticate via 2FA.
     *
     * @SWG\Tag(name="Security")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *         type="object",
     *         properties={
     *             @SWG\Property(
     *                 property="authCode",
     *                 type="integer"
     *             ),
     *             @SWG\Property(
     *                 property="fingerprint",
     *                 description="Set if we need trusted device token",
     *                 type="string",
     *                 example="fc772c1049ac5342cd9bc77086373e22"
     *             )
     *         }
     *     )
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success auth 2FA",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="string",
     *             property="token",
     *             example="fc772c1049ac5342cd9bc77086373e22",
     *         )
     *     )
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @SWG\Response(
     *     response=400,
     *     description="Returns auth error",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="string",
     *             property="errors"
     *         )
     *     )
     * )
     *
     * @Route(
     *     path="/api/auth/2fa",
     *     name="2fa_check",
     *     methods={"POST"}
     * )
     */
    public function check()
    {
        $message = $this->translator->trans('app.exception.authentication_required');
        throw new RuntimeException($message);
    }

    /**
     * Return Backup codes.
     *
     * @SWG\Tag(name="Security")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Return Backup codes [231678,233764]",
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(
     *     path="/api/auth/2fa/backups",
     *     name="api_security_2fa_backup_codes",
     *     methods={"GET"}
     * )
     *
     * @return JsonResponse
     */
    public function getBackupCodes(BackupCodeCreator $backupCodeCreator)
    {
        /** @var User $user */
        $user = $this->getUser();
        $this->denyAccessUnlessGranted(BackupCodesVoter::GET, $user);

        $codes = $backupCodeCreator->createAndSaveBackupCodes($user);

        return new JsonResponse($codes);
    }

    /**
     * Accept Backup codes.
     *
     * @SWG\Tag(name="Security")
     *
     * @SWG\Response(
     *     response=204,
     *     description="Accepted backupcodes",
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(
     *     path="/api/auth/2fa/backups/accept",
     *     name="api_security_2fa_backup_codes_accept",
     *     methods={"POST"}
     * )
     */
    public function accept(UserRepository $repository): void
    {
        $user = $this->getUser();
        if ($user->canFinished()) {
            $user->setFlowStatus(User::FLOW_STATUS_FINISHED);
            $repository->save($user);
        }
    }
}
