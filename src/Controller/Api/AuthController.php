<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Limiter\Exception\RestrictedException;
use App\Limiter\Inspector\UserCountInspector;
use App\Limiter\LimiterInterface;
use App\Limiter\Model\LimitCheck;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserManagerInterface;
use Swagger\Annotations as SWG;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AuthController extends AbstractController
{
    /**
     * @SWG\Tag(name="Auth")
     *
     * @SWG\Response(
     *     response=302,
     *     description="Account confirmed or invalidate"
     * )
     */
    public function confirm(
        Request $request,
        string $token,
        LimiterInterface $limiter,
        EventDispatcherInterface $eventDispatcher,
        TranslatorInterface $translator,
        UserManagerInterface $userManager
    ): Response {
        $user = $userManager->findUserByConfirmationToken($token);
        if (null === $user) {
            return new RedirectResponse(
                $this->generateUrl('front_sing_in', ['error' => $translator->trans('app.exception.user_not_found')], UrlGeneratorInterface::ABSOLUTE_URL)
            );
        }

        try {
            $limiter->check([new LimitCheck(UserCountInspector::class, 1)]);
        } catch (RestrictedException $exception) {
            return new RedirectResponse(
                $this->generateUrl(
                    'front_sing_in',
                    ['error' => $translator->trans($exception->getMessage())],
                    UrlGeneratorInterface::ABSOLUTE_URL
                )
            );
        }

        $user->setConfirmationToken(null);
        $user->setEnabled(true);

        $event = new GetResponseUserEvent($user, $request);
        /**
         * @phpstan-ignore-next-line
         * @psalm-suppress InvalidArgument
         * @psalm-suppress TooManyArguments
         */
        $eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_CONFIRM, $event);

        $userManager->updateUser($user);
        if (null === $response = $event->getResponse()) {
            $url = $this->generateUrl('front_sing_in', [], UrlGeneratorInterface::ABSOLUTE_URL);
            $response = new RedirectResponse($url);
        }
        /**
         * @phpstan-ignore-next-line
         * @psalm-suppress InvalidArgument
         * @psalm-suppress TooManyArguments
         */
        $eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_CONFIRMED, new FilterUserResponseEvent($user, $request, $response));

        return $response;
    }
}
