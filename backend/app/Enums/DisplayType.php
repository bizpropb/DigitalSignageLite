<?php

namespace App\Enums;

enum DisplayType: string
{
    case NEW = 'New';
    case INACTIVE = 'Inactive';
    case PUBLIC_FACING = 'Public Facing';
    case INTERNAL_USE = 'Internal Use';  
    case ADVERTISING = 'Advertising';

    public function getLabel(): string
    {
        return $this->value;
    }

    public static function getOptions(): array
    {
        return collect(self::cases())
            ->pluck('value', 'name')
            ->toArray();
    }
}
