<?php

declare(strict_types=1);

namespace App\Form\Request;

<<<<<<< HEAD:src/Form/Request/UpdateShareType.php
use App\Entity\Share;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateShareType extends AbstractType
=======
use App\Model\Request\SendInviteRequests;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SendInvitesType extends AbstractType
>>>>>>> release/v1.2:src/Form/Request/SendInvitesType.php
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

<<<<<<< HEAD:src/Form/Request/UpdateShareType.php
        $builder->add('link', TextType::class);
=======
        $builder
            ->add('messages', CollectionType::class, [
                'entry_type' => SendInviteType::class,
            ])
        ;
>>>>>>> release/v1.2:src/Form/Request/SendInvitesType.php
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
<<<<<<< HEAD:src/Form/Request/UpdateShareType.php
            'data_class' => Share::class,
=======
            'data_class' => SendInviteRequests::class,
>>>>>>> release/v1.2:src/Form/Request/SendInvitesType.php
        ]);
    }
}