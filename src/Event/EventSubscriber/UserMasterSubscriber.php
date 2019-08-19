<?php

declare(strict_types=1);

namespace App\Event\EventSubscriber;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
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
        'api_srp_update_password',
        'api_user_get_info',
        'webauthn_create',
        'webauthn_register',
        'webauthn_login_prepare',
        'webauthn_login_check',
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
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();

        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return;
        }

        if ($user->hasRole(User::ROLE_SUPER_ADMIN) || $user->hasRole(User::ROLE_ADMIN)) {
            return;
        }

        if (User::FLOW_STATUS_INCOMPLETE === $user->getFlowStatus()) {
            if (!in_array($request->get('_route'), self::GRANTED_ROUTES)) {
                $message = $this->translator->trans('app.exception.update_master_password');
                $event->setResponse(new JsonResponse(['master' => $message], Response::HTTP_UNAUTHORIZED));
            }
        }
    }
}
