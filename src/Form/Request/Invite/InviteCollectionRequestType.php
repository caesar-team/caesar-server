<?php

declare(strict_types=1);

namespace App\Form\Request\Invite;

use App\Model\Request\InviteCollectionRequest;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class InviteCollectionRequestType extends AbstractType
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(EntityManagerInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('invites', CollectionType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
                'allow_add' => true,
                'entry_type' => InviteType::class,
            ]);

        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'validateInvites']);
    }

    public function validateInvites(FormEvent $event)
    {
        /** @var InviteCollectionRequest $request */
        $request = $event->getData();
        $form = $event->getForm();

        $parentItem = $request->getItem();
        foreach ($request->getInvites() as $invite) {
            foreach ($parentItem->getSharedItems() as $sharedItem) {
                $owner = $this->userRepository->getByItem($sharedItem);

                if ($invite->getUser() === $owner) {
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
            'data_class' => InviteCollectionRequest::class,
        ]);
    }
}
