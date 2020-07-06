<?php

declare(strict_types=1);

namespace App\Form\Request;

use App\Entity\Directory;
use App\Entity\Item;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class MoveItemType extends AbstractType
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        /** @var User $user */
        $user = $this->security->getUser();

        $builder
            ->add('listId', EntityType::class, [
                'class' => Directory::class,
                'choice_value' => 'id',
                'property_path' => 'parentList',
                'constraints' => [
                    new NotBlank(),
                    new Callback(function (Directory $object, ExecutionContextInterface $context) use ($user) {
                        if (null !== $object->getTeam()) {
                            return;
                        }

                        if (!$user->isOwnerByDirectory($object)) {
                            $context->buildViolation('item.move.invalid_list')
                                ->atPath('listId')
                                ->addViolation()
                            ;
                        }
                    }),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class' => Item::class,
        ]);
    }
}
