<?php

namespace App\Form;

use App\Entity\Timeslot;
use App\Entity\Resource;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TimeslotType extends AbstractType
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('start_datetime', DateTimeType::class, [
                'label' => 'Start Date and Time',
                'required' => true,
                'widget' => 'single_text',  
            ])
            ->add('end_datetime', DateTimeType::class, [
                'label' => 'End Date and Time',
                'required' => true,
                'widget' => 'single_text',
            ])
            ->add('is_available', CheckboxType::class, [
                'label' => 'Is Available?',
                'required' => false,
            ])
            ->add('resource', ChoiceType::class, [
                'label' => 'Resource',
                'choices' => $this->getResourceChoices(),
                'required' => true,
            ]);
    }

    private function getResourceChoices(): array
    {
        $resources = $this->entityManager->getRepository(Resource::class)->findAll();
        $choices = [];

        foreach ($resources as $resource) {
            $choices[$resource->getName()] = $resource->getId();
        }

        return $choices;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Timeslot::class,
        ]);
    }
}
