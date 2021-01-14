<?php

declare(strict_types=1);

namespace App\EventSubscriber\Kernel;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserMasterSubscriber implements EventSubscriberInterface
{
    private const GRANTED_ROUTES = [
        'api_keys_save',
        'api_keys_list',
        'api_user_security_bootstrap',
        'api_anonymous_share_check',
        'hwi_oauth_service_redirect',
        'google_login',
        'api_security_2fa_code',
        'api_security_2fa_activate',
        '2fa_check',
        'api_security_2fa_backup_codes',
        'api_security_2fa_backup_codes_accept',
        'api_srp_update_password',
        'api_user_get_info',
        'easyadmin',
        'app.swagger_ui',
    ];

    /**
     * @var Security
     */
    private $security;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(Security $security, TranslatorInterface $translator)
    {
        $this->security = $security;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', 16],
        ];
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        $request = $event->getRequest();

        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return;
        }

        if ($this->security->getToken() instanceof UsernamePasswordToken) {
            return;
        }

        if ($user->hasRole(User::ROLE_SUPER_ADMIN) || $user->hasRole(User::ROLE_ADMIN)) {
            return;
        }

        if (User::FLOW_STATUS_INCOMPLETE === $user->getFlowStatus()) {
            if (!in_array($request->get('_route'), self::GRANTED_ROUTES)) {
                $response = $event->getResponse();

                $message = $this->translator->trans('app.exception.update_user_password');
                $errorResponse = new JsonResponse(['errors' => [$message], 'route' => $request->get('_route')], Response::HTTP_UNAUTHORIZED);
                $errorResponse->headers->set('Access-Control-Allow-Origin', $response->headers->get('Access-Control-Allow-Origin'));
                if ($response->headers->has('Access-Control-Allow-Credentials')) {
                    $errorResponse->headers->set('Access-Control-Allow-Credentials', $response->headers->get('Access-Control-Allow-Credentials'));
                }
                if ($response->headers->has('Access-Control-Expose-Headers')) {
                    $errorResponse->headers->set('Access-Control-Expose-Headers', $response->headers->get('Access-Control-Expose-Headers'));
                }

                $event->setResponse($errorResponse);
            }
        }
    }
}
