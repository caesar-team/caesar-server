<?php

declare(strict_types=1);

namespace App\Form\Request\Invite;

use App\Model\Request\ItemCollectionRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ChildItemCollectionRequestType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('items', CollectionType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
                'allow_add' => true,
                'entry_type' => CreateChildItemType::class,
            ]);

        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'validateChildItems']);
    }

    /**
     * @param FormEvent $event
     */
    public function validateChildItems(FormEvent $event)
    {
        /** @var ItemCollectionRequest $request */
        $request = $event->getData();
        $form = $event->getForm();

        $parentItem = $request->getOriginalItem();
        foreach ($request->getItems() as $invite) {
            foreach ($parentItem->getSharedItems() as $sharedItem) {
                $owner = $sharedItem->getSignedOwner();

                if ($invite->getUser() === $owner && !$sharedItem->getTeam()) {
                    $form->addError(new FormError('item.invite.user.already_invited'));

                    return;
                }
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class' => ItemCollectionRequest::class,
        ]);
    }
}
