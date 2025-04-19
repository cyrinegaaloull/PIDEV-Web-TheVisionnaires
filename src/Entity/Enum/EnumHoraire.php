<?php

namespace App\Enum;

class EnumHoraire
{
    public const HORAIRE_7_17 = 'HORAIRE_7_17';
    public const HORAIRE_8_17 = 'HORAIRE_8_17';
    public const HORAIRE_9_18 = 'HORAIRE_9_18';
    public const HORAIRE_10_19 = 'HORAIRE_10_19';
    public const HORAIRE_12_24 = 'HORAIRE_12_24';
    public const HORAIRE_24_24 = 'HORAIRE_24_24';
    public const SEMAINE_5J = 'SEMAINE_5J';
    public const SEMAINE_6J = 'SEMAINE_6J';
    public const SEMAINE_7J = 'SEMAINE_7J';
    public const WEEKEND_SEUL = 'WEEKEND_SEUL';
    public const JOUR_SEMAINE_SEUL = 'JOUR_SEMAINE_SEUL';
    public const NUIT_22_06 = 'NUIT_22_06';

    private static $labels = [
        self::HORAIRE_7_17 => '7h à 17h',
        self::HORAIRE_8_17 => '8h à 17h',
        self::HORAIRE_9_18 => '9h à 18h',
        self::HORAIRE_10_19 => '10h à 19h',
        self::HORAIRE_12_24 => '12h sur 24h',
        self::HORAIRE_24_24 => '24h/24h',
        self::SEMAINE_5J => 'Lundi - Vendredi',
        self::SEMAINE_6J => 'Lundi - Samedi',
        self::SEMAINE_7J => '7 jours sur 7',
        self::WEEKEND_SEUL => 'Uniquement le weekend',
        self::JOUR_SEMAINE_SEUL => 'Uniquement lundi - vendredi',
        self::NUIT_22_06 => 'Travail de nuit 22h - 6h'
    ];

    /**
     * Obtenir le libellé d'une valeur d'horaire
     */
    public static function getLabel($value): string
    {
        return isset(self::$labels[$value]) ? self::$labels[$value] : $value;
    }

    /**
     * Obtenir toutes les options d'horaires pour les formulaires (libellé => valeur)
     */
    public static function getChoices(): array
    {
        $choices = [];
        foreach (self::$labels as $value => $label) {
            $choices[$label] = $value;
        }
        return $choices;
    }

    /**
     * Obtenir toutes les options d'horaires (valeur => libellé)
     */
    public static function getValues(): array
    {
        return self::$labels;
    }
}