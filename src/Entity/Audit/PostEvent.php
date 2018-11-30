<?php

declare(strict_types=1);

namespace App\Entity\Audit;

use App\Entity\Post;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AuditPostEventRepository")
 * @ORM\Table(name="audit_events")
 */
class PostEvent extends AbstractEvent
{
    /**
     * @var Post|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Post", cascade={"persist"})
     * @ORM\JoinColumn(name="target_id", onDelete="SET NULL")
     */
    private $post;

    /**
     * @return Post|null
     */
    public function getPost(): ?Post
    {
        return $this->post;
    }

    /**
     * @param Post|null $post
     */
    public function setPost(?Post $post): void
    {
        $this->post = $post;
    }
}
