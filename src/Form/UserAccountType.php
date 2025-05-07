<?php

namespace App\Form;

use App\Entity\UserAccount;
use App\Entity\Role;
use App\Entity\Client; // Importation de l'entité Client
use Doctrine\ORM\EntityManagerInterface; 
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserAccountType extends AbstractType
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'] ?? false;
        $isClient = $options['is_client'] ?? false;  // Option pour gérer si l'utilisateur est un client
    
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email Address',
                'required' => true,
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Password',
                'required' => !$isEdit,
                'mapped' => false, 
            ])
            ->add('first_name', TextType::class, [
                'label' => 'First Name',
                'required' => true,
            ])
            ->add('last_name', TextType::class, [
                'label' => 'Last Name',
                'required' => true,
            ])
            ->add('phone', TextType::class, [
                'label' => 'Phone Number',
                'required' => false,
                'attr' => ['type' => 'tel'],
            ])
            ->add('role', EntityType::class, [
                'class' => Role::class,
                'choice_label' => 'name',
                'label' => 'Rôle',
                'placeholder' => 'Sélectionnez un rôle',
            ]);

        // Si c'est un client, ajouter les champs spécifiques
        if ($isClient) {
            $builder
                ->add('organization_name', TextType::class, [
                    'label' => 'Nom de l\'organisation',
                    'required' => true,
                ])
                ->add('address', TextType::class, [
                    'label' => 'Adresse',
                    'required' => true,
                ]);
        }
    }    

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserAccount::class,
            'is_edit' => false,
            'is_client' => false,  // Ajouter l'option pour savoir si l'utilisateur est un client
        ]);
    }
}
