<?php

namespace App\Enums;

enum UserEnum: string
{
        // User Roles
    case SuperAdmin = 'super-admin';
    case Admin = 'admin';
    case Moderator = 'moderator';
    case User = 'user';

        // User Statuses
    case Active = 'active';
    case Inactive = 'inactive';
    case Banned = 'banned';
    case Pending = 'pending';


    // Role Getters
    public static function getSuperAdmin(): string
    {
        return self::SuperAdmin->value;
    }

    public static function getAdmin(): string
    {
        return self::Admin->value;
    }

    public static function getModerator(): string
    {
        return self::Moderator->value;
    }

    public static function getUser(): string
    {
        return self::User->value;
    }

    // Status Getters
    public static function getActive(): string
    {
        return self::Active->value;
    }

    public static function getInactive(): string
    {
        return self::Inactive->value;
    }

    public static function getBanned(): string
    {
        return self::Banned->value;
    }

    public static function getPending(): string
    {
        return self::Pending->value;
    }

    // Helper methods
    public static function getAllRoles(): array
    {
        return [
            self::getSuperAdmin(),
            self::getAdmin(),
            self::getModerator(),
            self::getUser(),
        ];
    }

    public static function getAllStatuses(): array
    {
        return [
            self::getActive(),
            self::getInactive(),
            self::getBanned(),
            self::getPending(),
        ];
    }

    // Convert roles to array
    public static function toArray(): array
    {
        return [
            self::SuperAdmin->value,
            self::Admin->value,
            self::Moderator->value,
            self::User->value,
        ];
    }

    /**
     * Get available roles
     */
    public static function getRoles(): array
    {
        return UserEnum::toArray();
    }

    /**
     * Get available statuses
     */
    public static function statuses(): array
    {
        return [
            self::Active->value,
            self::Inactive->value,
            self::Banned->value,
            self::Pending->value,
        ];
    }

    /**
     * Get unbanned status
     */
    public static function getUnbanned(): string
    {
        return self::Active->value; // Assuming 'active' is the unbanned status
    }
}
