<?php

namespace App\Form;

use App\Entity\Booking;
use App\Entity\Timeslot;
use App\Entity\Resource;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class BookingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('resource', ChoiceType::class, [
                'choices' => $this->getResources(),
                'choice_label' => function (?Resource $resource) {
                    return $resource ? $resource->getName() : '';
                },
                'placeholder' => 'Choose a resource',
                'required' => true,
            ])
            ->add('timeslot', ChoiceType::class, [
                'choices' => $this->getAvailableTimeslots(),
                'choice_label' => function (?Timeslot $timeslot) {
                    return $timeslot ? $timeslot->getStartDatetime()->format('Y-m-d H:i') : '';
                },
                'placeholder' => 'Select a time slot',
                'required' => true,
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'attr' => ['rows' => 3],
            ])
            ->add('comments', TextType::class, [
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Book Now',
                'attr' => ['class' => 'btn btn-primary']
            ]);
    }

    private function getResources()
    {

        return [
            'Resource 1' => new Resource(),  
            'Resource 2' => new Resource(),  
        ];
    }

    private function getAvailableTimeslots()
    {
        return [
            new Timeslot(),
            new Timeslot(),
        ];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Booking::class,
        ]);
    }
}
