<?php

namespace App\Form;

use App\Entity\UserAccount;
use App\Entity\Role;
use Doctrine\ORM\EntityManagerInterface; 
use Symfony\Component\Form\AbstractType;
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
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email Address',
                'required' => true,
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Password',
                'required' => true,
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
            ->add('role', ChoiceType::class, [
                'label' => 'Role',
                'choices' => $this->getRoleChoices(),
                'required' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Save User Account',
            ]);
    }

    private function getRoleChoices(): array
    {
        $roles = $this->entityManager->getRepository(Role::class)->findAll();
        $choices = [];

        foreach ($roles as $role) {
            $choices[$role->getName()] = $role->getId();
        }

        return $choices;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserAccount::class,
        ]);
    }
}
