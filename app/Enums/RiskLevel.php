<?php

namespace App\Enums;

enum RiskLevel: string
{
    case Mild = 'mild';
    case Moderate = 'moderate';
    case High = 'high';

    public function label(): string
    {
        return match ($this) {
            self::Mild => 'Mild',
            self::Moderate => 'Moderate',
            self::High => 'High',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Mild => '#EAB308',
            self::Moderate => '#F97316',
            self::High => '#DC2626',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Derives the BFP risk level from a score.
     * Accepts either a 0.0–1.0 prediction score or a 0–100 percentage score.
     */
    public static function fromScore(float|int $score): self
    {
        $normalized = $score > 1.0 ? $score / 100 : (float) $score;

        if ($normalized >= 0.70) {
            return self::High;
        }

        if ($normalized >= 0.40) {
            return self::Moderate;
        }

        return self::Mild;
    }
}
