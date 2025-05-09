<?php

namespace App\Form;

use App\Entity\Resource;
use App\Entity\Client; 
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class ResourceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Resource Name',
                'required' => true,
            ])
            ->add('type', TextType::class, [
                'label' => 'Type of Resource',
                'required' => true,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 4],
            ])
            ->add('location', TextType::class, [
                'label' => 'Location',
                'required' => true,
            ])
            ->add('capacity', IntegerType::class, [
                'label' => 'Capacity',
                'required' => true,
                'attr' => [
                    'min' => 0, 
                ],
                'constraints' => [
                    new PositiveOrZero([
                        'message' => 'La capacité ne peut pas être négative.',
                    ])
                ]
            ])
            ->add('client', EntityType::class, [
                'class' => Client::class, 
                'choice_label' => 'organizationName', 
                'label' => 'Client',
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Resource::class,
        ]);
    }
}
