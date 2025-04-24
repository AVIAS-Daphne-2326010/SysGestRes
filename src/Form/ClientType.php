<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\UserAccount;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('organization_name', TextType::class)
            ->add('address', TextareaType::class)
            ->add('user_account', EntityType::class, [
                'class' => UserAccount::class,
                'choice_label' => 'email', // On affiche l'email de l'utilisateur
                'placeholder' => 'Select an User Account',
                'required' => true,
            ])
            ->add('save', SubmitType::class);
    }
}
