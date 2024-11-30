<?php

namespace App\Enums;

enum CompanyEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case DORMANT = 'dormant';
    case SUSPENDED = 'suspended';
    case BANKRUPT = 'bankrupt';

    public static function getStatuses(): array
    {
        return [
            self::ACTIVE,
            self::INACTIVE,
            self::DORMANT,
            self::SUSPENDED,
            self::BANKRUPT
        ];
    }

    public static function getActive(): CompanyEnum
    {
        return CompanyEnum::ACTIVE;
    }

    public static function getInactive(): CompanyEnum
    {
        return CompanyEnum::INACTIVE;
    }

    public static function getDormant(): CompanyEnum
    {
        return CompanyEnum::DORMANT;
    }

    public static function getSuspended(): CompanyEnum
    {
        return CompanyEnum::SUSPENDED;
    }
}
