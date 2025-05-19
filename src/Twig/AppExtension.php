<?php

namespace App\Twig;

use App\Entity\Resource;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('format_phone', [$this, 'formatPhone']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('resource_types', [$this, 'getResourceTypes']),
        ];
    }

    public function formatPhone(?string $phone): string
    {
        if (!$phone) return '';

        $digits = preg_replace('/\D/', '', $phone);

        if (strlen($digits) === 10) {
            return implode(' ', str_split($digits, 2));
        }

        return $phone; 
    }

    public function getResourceTypes(): array
    {
        $query = $this->em->createQuery('SELECT DISTINCT r.type FROM App\Entity\Resource r');
        return $query->getResult();
    }
}
