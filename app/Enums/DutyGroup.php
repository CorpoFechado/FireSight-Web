<?php

namespace App\Enums;

enum DutyGroup: string
{
    case A = 'A';
    case B = 'B';
    case Regular = 'regular';
    case Detached = 'detached';

    public function label(): string
    {
        return match ($this) {
            self::A => 'Duty A',
            self::B => 'Duty B',
            self::Regular => 'Always on Duty',
            self::Detached => 'Detached',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
