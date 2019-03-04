<?php

declare(strict_types=1);

namespace App\Event\EventSubscriber;


use App\Entity\Share;
use App\Mailer\MailRegistry;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Response;

class ShareLinkCreatedSubscriber implements EventSubscriberInterface
{
    const EVENT_NAME = 'app.event.share.link_created';
    const METHOD_CREATE = 'create';
    const METHOD_UPDATE = 'update';

    /**
     * @var SenderInterface
     */
    private $sender;

    public function __construct(SenderInterface $sender)
    {
        $this->sender = $sender;
    }

    public static function getSubscribedEvents()
    {
        return [
            self::EVENT_NAME => 'handler'
        ];
    }

    public function handler(GenericEvent $event)
    {
        $share = $event->getSubject();
        if (!$share instanceof Share) {
            return;
        }

        try {
            $this->sender->send(MailRegistry::SHARE_SEND_MESSAGE, [$share->getUser()->getEmail()], [
                'url' => $share->getLink(),
                'method' => $event->getArgument('method'),
            ]);
        } catch (\Exception $exception) {
            throw new \LogicException($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }

    }
}