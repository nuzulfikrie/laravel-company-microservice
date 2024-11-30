<?php

namespace App\Enums;

enum CompanyMemberEnum: string
{
    case ADMIN = 'admin';
    case MEMBER = 'member';
    case GUEST = 'guest';
    case OWNER = 'owner';
    case VENDOR = 'vendor';

    //to get admin role
    public static function getAdminRole(): string
    {
        return self::ADMIN->value;
    }

    //to get member role
    public static function getMemberRole(): string
    {
        return self::MEMBER->value;
    }

    //to get guest role
    public static function getGuestRole(): string
    {
        return self::GUEST->value;
    }

    //to get owner role
    public static function getOwnerRole(): string
    {
        return self::OWNER->value;
    }

    //to get vendor role
    public static function getVendorRole(): string
    {
        return self::VENDOR->value;
    }

    //get all roles in array
    public static function getRoles(): array
    {
        return [
            self::ADMIN->value,
            self::MEMBER->value,
            self::GUEST->value,
            self::OWNER->value,
            self::VENDOR->value
        ];
    }
}
