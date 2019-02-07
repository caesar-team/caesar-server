<?php

declare(strict_types=1);

namespace App\Form\EventListener;

use App\Entity\Item;
use App\Entity\Tag;
use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
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
        $item = $event->getData();
        if ($item instanceof Item && $event->getForm()->has('tags')) {
            $tags = $event->getForm()->get('tags')->getData();
            if (null === $tags) {
                return;
            }

            $names = [];
            $uniqueTags = [];
            foreach ($tags as $tag) {
                $names[] = $tag;
                $uniqueTags[$tag] = $tag;
            }

            $existTags = $this->tagRepository->getTags($names);
            $tags = new ArrayCollection();
            foreach ($uniqueTags as $tag) {
                $tag = $existTags[$tag] ?? new Tag($tag);
                $tags->add($tag);
            }

            $item->setTags($tags);
        }
    }
}
