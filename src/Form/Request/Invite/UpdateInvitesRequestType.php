<?php

declare(strict_types=1);

namespace App\Form\Request\Invite;

use App\Entity\Item;
use App\Model\Request\InviteCollectionRequest;
use App\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class UpdateInvitesRequestType extends AbstractType
{
    /**
     * @var UserRepository
     */
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
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
                'entry_type' => SecretType::class,
            ]);

        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'validateInvites']);
    }

    public function validateInvites(FormEvent $event)
    {
        /** @var InviteCollectionRequest $request */
        $request = $event->getData();
        $form = $event->getForm();

        $parentItem = $request->getItem()->getOriginalItem() ?? $request->getItem();

        if ($request->getInvites()->count() !== $parentItem->getSharedItems()->count()) {
            $form->addError(new FormError('item.invite.update.invalid_user'));

            return;
        }

        $newUsers = [];
        foreach ($request->getInvites() as $invite) {
            if (null === $invite->getUser()) {
                return;
            }

            $newUsers[] = $invite->getUser()->getId()->toString();
        }

        $oldUsers = array_map(
            function (Item $item) {
                return $this->userRepository->getByItem($item)->getId()->toString();
            },
            $parentItem->getSharedItems()->toArray()
        );

        $diff = array_diff($newUsers, $oldUsers);
        if (!empty($diff)) {
            $form->addError(new FormError('item.invite.update.invalid_user'));
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
