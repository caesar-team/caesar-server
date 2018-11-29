<?php

declare(strict_types=1);

namespace App\Model\Query;

use App\Entity\Post;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;

class AuditEventsQuery extends AbstractQuery
{
    public const PAGE_PARAM = 'page';
    public const PAGE_SIZE_PARAM = 'limit';
    public const PAGE_SIZE_DEFAULT = 30;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Post
     */
    private $post;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public static function fromRequest(User $user, Request $request, int $pageSizeDefault = self::PAGE_SIZE_DEFAULT): self
    {
        $page = $request->query->getInt(self::PAGE_PARAM, 1);
        $pageSize = $request->query->getInt(self::PAGE_SIZE_PARAM, $pageSizeDefault);

        $query = new self($user);
        $query->setPage($page);
        $query->setPerPage($pageSize);

        return $query;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): void
    {
        $this->post = $post;
    }
}
