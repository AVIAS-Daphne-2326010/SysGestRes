<?php

namespace App\Form;

use App\Entity\BookingHistory;
use App\Entity\Booking;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class BookingHistoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('booking', ChoiceType::class, [
                'choices' => $this->getBookings(),
                'choice_label' => function (?Booking $booking) {
                    return $booking ? 'Booking #' . $booking->getId() : '';
                },
                'placeholder' => 'Select a booking',
                'required' => true,
            ])
            ->add('changedAt', null, [
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'attr' => ['rows' => 4],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save History',
                'attr' => ['class' => 'btn btn-primary'],
            ]);
    }

    private function getBookings()
    {
        return [
            new Booking(), 
            new Booking(),
        ];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BookingHistory::class,
        ]);
    }
}
