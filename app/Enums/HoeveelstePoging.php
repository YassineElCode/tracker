<?php

declare(strict_types=1);

namespace App\Enums;

enum HoeveelstePoging: string
{
    case ONE_OR_TWO = 'oneOrTwo';
    case THREE_OR_MORE = 'threeOrMore';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
