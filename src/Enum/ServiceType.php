<?php

namespace App\Enum;

class ServiceType
{
    // === TYPES DE SERVICES ===
    public const string GROCERY = 'grocery'; // Épicerie
    public const string HARDWARE = 'hardware'; // Quincaillerie

    public static function getAll(): array
    {
        $reflection = new \ReflectionClass(self::class);
        return $reflection->getConstants();
    }

    public static function getGrouped(): array
    {
        return [
            'services' => [
                self::GROCERY,
                self::HARDWARE,
            ],
        ];
    }
}