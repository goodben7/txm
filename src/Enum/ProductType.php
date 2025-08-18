<?php

namespace App\Enum;

class ProductType
{
    // === TYPES DE PRODUITS ===
    public const string FOOD = 'food'; // Alimentaire
    public const string BEVERAGE = 'beverage'; // Boissons
    public const string TOOL = 'tool'; // Outils
    public const string HOUSEHOLD = 'household'; // Articles ménagers
    public const string GARDEN = 'garden'; // Jardinage

    public static function getAll(): array
    {
        $reflection = new \ReflectionClass(self::class);
        return $reflection->getConstants();
    }

    public static function getGrouped(): array
    {
        return [
            'grocery' => [
                self::FOOD,
                self::BEVERAGE,
            ],
            'hardware' => [
                self::TOOL,
                self::HOUSEHOLD,
                self::GARDEN,
            ],
        ];
    }
}