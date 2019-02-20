<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\User;
use App\Form\Request\TwoFactoryAuthEnableType;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class TwoFactorAuthController extends AbstractController
{
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
     *     path="/api/2fa",
     *     name="api_security_2fa_code",
     *     methods={"GET"}
     * )
     *
     * @param GoogleAuthenticatorInterface $twoFactor
     *
     * @return FormInterface|JsonResponse
     */
    public function getCode(GoogleAuthenticatorInterface $twoFactor)
    {
        /** @var User $user */
        $user = $this->getUser();
        $user->setGoogleAuthenticatorSecret($twoFactor->generateSecret());

        return new JsonResponse([
            'qr' => $twoFactor->getUrl($user),
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
     *             type="boolean",
     *             property="success",
     *             example="true",
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
     *     path="/api/2fa",
     *     name="2fa_check",
     *     methods={"POST"}
     * )
     */
    public function check()
    {
        throw new \RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
    }
}
