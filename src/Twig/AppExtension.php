<?php 

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('format_phone', [$this, 'formatPhone']),
        ];
    }

    public function formatPhone(?string $phone): string
    {
        if (!$phone) return '';

        // Supprime tous les caractères non numériques
        $digits = preg_replace('/\D/', '', $phone);

        // Si le numéro a 10 chiffres (standard FR), ajoute les espaces
        if (strlen($digits) === 10) {
            return implode(' ', str_split($digits, 2));
        }

        return $phone; // retour brut si non conforme
    }
}
