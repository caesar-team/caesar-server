<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\Entity\Post;
use App\Model\View\CredentialsList\PostView;

class PostListViewFactory
{
    /**
     * @var PostViewFactory
     */
    private $secretViewFactory;

    public function __construct(PostViewFactory $secretViewFactory)
    {
        $this->secretViewFactory = $secretViewFactory;
    }

    /**
     * @param Post[] $postCollection
     *
     * @return PostView[]
     */
    public function create(array $postCollection): array
    {
        $viewCollection = [];
        /** @var Post $post */
        foreach ($postCollection as $post) {
            $viewCollection[] = $this->secretViewFactory->create($post);
        }

        return $viewCollection;
    }
}
