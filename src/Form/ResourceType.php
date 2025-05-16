<?php

namespace App\Form;

use App\Entity\Resource;
use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Doctrine\ORM\EntityManagerInterface;

class ResourceType extends AbstractType
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Récupère les types distincts depuis la base
        $types = $this->em->getRepository(Resource::class)
            ->createQueryBuilder('r')
            ->select('DISTINCT r.type')
            ->getQuery()
            ->getResult();

        $typeChoices = array_map(fn($t) => $t['type'], $types);

        $builder
            ->add('name', TextType::class, [
                'label' => 'Resource Name',
                'required' => true,
            ])
            ->add('type', TextType::class, [
                'required' => false,
                'attr' => [
                    'list' => 'resource_type_options', // lien avec le datalist dans le template
                ],
                'label' => 'Type de ressource',
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
                'attr' => ['min' => 0],
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
