<?php

namespace App\Form;

use App\Entity\Timeslot;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TimeslotType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('start_datetime', DateTimeType::class, [
                'label' => 'Date/Heure de début',
                'required' => true,
                'widget' => 'single_text',
            ])
            ->add('end_datetime', DateTimeType::class, [
                'label' => 'Date/Heure de fin',
                'required' => true,
                'widget' => 'single_text',
            ])
            ->add('is_available', CheckboxType::class, [
                'label' => 'Cocher si disponible',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Timeslot::class,
        ]);
    }
}
