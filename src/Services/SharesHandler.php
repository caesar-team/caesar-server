<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Post;
use App\Model\Request\SharePostRequest;
use Doctrine\ORM\EntityManagerInterface;

class SharesHandler
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param SharePostRequest $request
     *
     * @throws \Exception
     */
    public function sharePost(SharePostRequest $request)
    {
        $request->getPost()->getSharedPosts()->clear();

        foreach ($request->getUsers() as $user) {
            $inbox = $user->getInbox();

            $post = new Post();
            $post->setParentList($inbox);
            $post->setSecret($request->getPost()->getSecret()); // TODO логика и шифрование на фронтовой стороне
            $post->setOriginalPost($request->getPost());

            $this->entityManager->persist($post);
        }

        $this->entityManager->flush();
    }

    /**
     * @param Post $post
     *
     * @throws \Exception
     */
    public function savePostWithShares(Post $post)
    {
        $this->entityManager->persist($post);
        foreach ($post->getSharedPosts() as $sharedPost) {
            $sharedPost->setSecret($post->getSecret());

            $this->entityManager->persist($sharedPost);
        }

        $this->entityManager->flush();
    }
}
