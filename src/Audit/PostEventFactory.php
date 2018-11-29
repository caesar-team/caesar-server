<?php

declare(strict_types=1);

namespace App\Audit;

use App\Entity\Audit\AbstractEvent;
use App\Entity\Audit\PostEvent;
use App\Entity\Post;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Security;

class PostEventFactory implements EventFactoryInterface
{
    /**
     * @var Security
     */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @param Request $request
     * @param Post    $target
     *
     * @return AbstractEvent
     */
    public function create(Request $request, $target): AbstractEvent
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new UsernameNotFoundException();
        }

        $event = new PostEvent();
        $event->setIp($request->getClientIp());
        $event->setBlame($user->getEmail());
        $event->setPost($target);

        return $event;
    }
}
