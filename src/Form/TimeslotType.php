<?php

namespace App\Form;

use App\Entity\Timeslot;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TimeslotType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('start_datetime', DateTimeType::class, [
                'label' => 'Date/Heure de début',
                'widget' => 'single_text',
            ])
            ->add('end_datetime', DateTimeType::class, [
                'label' => 'Date/Heure de fin',
                'widget' => 'single_text',
            ])
            ->add('is_available', ChoiceType::class, [
                'label' => false, // On désactive le label automatique
                'choices' => [
                    'Disponible' => true,
                    'Indisponible' => false,
                ],
                'expanded' => true,
                'multiple' => false,
                'label_attr' => ['class' => 'd-none'], // Cache les labels automatiques
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Timeslot::class,
        ]);
    }
}
