<?php

declare(strict_types=1);

namespace App\Enums;

enum TypeVoorlopigRijbewijs: string
{
    case TWELVE_MONTHS = '12 maand';
    case EIGHTEEN_MONTHS = '18 maand';
    case THIRTY_SIX_MONTHS = '36 maand';
    case MODEL_3 = 'Model 3';
    case STAGE_ATTEST = 'Stageattest';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
