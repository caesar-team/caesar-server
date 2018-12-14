<?php

declare(strict_types=1);

namespace App\Form\EventListener;

use App\Entity\Post;
use App\Entity\Tag;
use App\Repository\TagRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

final class InjectTagListener implements EventSubscriberInterface
{
    /**
     * @var TagRepository
     */
    private $tagRepository;

    public function __construct(TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::POST_SUBMIT => 'injectTags',
        ];
    }

    public function injectTags(FormEvent $event)
    {
        /** @var Post $post */
        $post = $event->getData();
        if ($post instanceof  Post && $event->getForm()->has('tags')) {
            $tags = $event->getForm()->get('tags')->getData();

            $names = [];
            $uniqueTags = [];
            foreach ($tags as $tag) {
                $names[] = $tag;
                $uniqueTags[$tag] = $tag;
            }

            $existTags = $this->tagRepository->getTags($names);
            $tags = [];
            foreach ($uniqueTags as $tag) {
                $tags[] = $existTags[$tag] ?? new Tag($tag);
            }

            $post->setTags($tags);
        }
    }
}
