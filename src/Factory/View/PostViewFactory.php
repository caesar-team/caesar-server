<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\Post;
use App\Model\View\CredentialsList\PostView;
use App\Model\View\CredentialsList\SecretView;
use App\Repository\UserRepository;
use Symfony\Component\PropertyAccess\PropertyAccess;

class PostViewFactory
{
    /** @var UserRepository */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function create(Post $post): PostView
    {
        $view = new PostView();

        $view->id = $post->getId();
        $view->type = NodeEnumType::TYPE_CRED;
        $view->owner = null === $post->getOriginalPost();
        $view->lastUpdated = $post->getLastUpdated();
        $view->listId = $post->getParentList()->getId()->toString();

        $view->secret = $this->getSecret($post->getSecret());
        $view->shared = $this->getSharedCollection($post);
        $view->favorite = $post->isFavorite();

        return $view;
    }

    protected function getSecret(array $rawSecret): SecretView
    {
        $view = new SecretView();
        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($rawSecret as $name => $field) {
            $accessor->setValue($view, $name, $field);
        }

        return $view;
    }

    protected function getSharedCollection(Post $post)
    {
        $ownerPost = $post;
        if (null !== $post->getOriginalPost()) {
            $ownerPost = $post->getOriginalPost();
        }
        $userToExclude = $this->userRepository->getByPost($post);

        $sharesViewCollection = [];
        $allPosts = $ownerPost->getSharedPosts()->toArray();
        $allPosts[] = $ownerPost;
        foreach ($allPosts as $post) {
            $user = $this->userRepository->getByPost($post);
            if ($user !== $userToExclude) {
                $sharesViewCollection[] = $user->getId()->toString();
            }
        }

        return $sharesViewCollection;
    }
}
