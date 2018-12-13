<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\User;
use App\Form\Request\TwoFactoryAuthEnableType;
use App\Normalizer\ErrorNormalizer;
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

class TwoFactorAuthController extends AbstractController
{
    /**
     * Enable 2FA on your account.
     *
     * @SWG\Tag(name="Security")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=\App\Form\Request\TwoFactoryAuthEnableType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success enabled 2FA",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="string",
     *             property="QR",
     *             example="/qp.png",
     *         ),
     *         @SWG\Property(
     *             type="string",
     *             property="code",
     *             example="f553f7c5-591a-4aed-9148-2958b7d88ee5",
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
     *     description="Returns errors",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="object",
     *             property="errors",
     *             @SWG\Property(
     *                 type="array",
     *                 property="auth_code",
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
     *     path="/api/2fa",
     *     name="api_security_2fa_enable",
     *     methods={"POST"}
     * )
     *
     * @param Request                $request
     * @param EntityManagerInterface $manager
     * @param ErrorNormalizer        $errorNormalizer
     *
     * @return FormInterface|JsonResponse
     */
    public function enable(Request $request, EntityManagerInterface $manager, ErrorNormalizer $errorNormalizer)
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($user->getGoogleAuthenticatorSecret()) {
            return new JsonResponse(['errors' => ['Two-factor authentication code already enabled']], Response::HTTP_BAD_REQUEST);
        }

        $form = $this->createForm(TwoFactoryAuthEnableType::class, $user);
        $form->submit($request->request->all());
        if ($form->isValid()) {
            $manager->persist($user);
            $manager->flush();

            return new JsonResponse();
        }

        return new JsonResponse($errorNormalizer->normalize($form), Response::HTTP_BAD_REQUEST);
    }

    /**
     * Disable 2FA on your account.
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
     *                 property="_auth_code",
     *                 type="integer"
     *             )
     *         }
     *     )
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success disable 2FA"
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @SWG\Response(
     *     response=400,
     *     description="Returns errors",
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
     *     name="api_security_2fa_disable",
     *     methods={"DELETE"}
     * )
     *
     * @param Request                      $request
     * @param EntityManagerInterface       $manager
     * @param GoogleAuthenticatorInterface $twoFactor
     *
     * @return FormInterface|JsonResponse
     */
    public function disable(Request $request, EntityManagerInterface $manager, GoogleAuthenticatorInterface $twoFactor)
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($twoFactor->checkCode($user, $request->request->get('_auth_code'))) {
            $user->setGoogleAuthenticatorSecret(null);
            $manager->persist($user);
            $manager->flush();

            return new JsonResponse();
        }

        return new JsonResponse(['errors' => 'Invalid two-factor authentication code.'], Response::HTTP_BAD_REQUEST);
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
     *             property="QR",
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
    public function code(GoogleAuthenticatorInterface $twoFactor)
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
     *                 property="_auth_code",
     *                 type="integer"
     *             ),
     *             @SWG\Property(
     *                 property="_trusted",
     *                 description="Set if we need trusted device token",
     *                 type="boolean"
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
     *             property="jwt",
     *             example="jwt-token-here",
     *         ),
     *         @SWG\Property(
     *             type="string",
     *             property="trustedDeviceToken",
     *             example="jwt-token-here",
     *         ),
     *         @SWG\Property(
     *             type="integer",
     *             property="trustedDeviceTokenExpiresAt",
     *             example="1548506076",
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
     *     path="/api/2fa_check",
     *     name="2fa_login_check",
     *     methods={"POST"}
     * )
     */
    public function check()
    {
        throw new \RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
    }
}
