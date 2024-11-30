<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Enums\UserEnum;
use Carbon\Carbon;
use App\Models\Traits\UserConnectionTrait;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_personal',
        'is_organization',
        'company_id',
        'profile_picture',
        'cover_picture',
        'bio',
        'website',
        'location',
        'phone',
        'gender',
        'birthdate',
        'status',
        'is_banned',
        'banned_until',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_personal' => 'boolean',
        'is_organization' => 'boolean',
        'is_banned' => 'boolean',
        'banned_until' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $table = 'users';




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
    public static function getStatuses(): array
    {
        return UserEnum::statuses();
    }

    /**
     * Activate a user
     */
    public static function activateUser(User $user): void
    {
        $user->status = UserEnum::getActive();
        $user->is_banned = false;
        $user->banned_until = null;
        $user->save();
    }

    /**
     * Deactivate a user
     */
    public static function deactivateUser(User $user): void
    {
        $user->status = UserEnum::getInactive();
        $user->save();
    }

    /**
     * Ban a user
     */
    public static function banUser(User $user, string $bannedUntil): void
    {
        $user->status = UserEnum::getBanned();
        $user->is_banned = true;
        $user->banned_until = Carbon::parse($bannedUntil)->toDateString();
        $user->save();
    }

    /**
     * Unban a user
     */
    public static function unbanUser(User $user): void
    {
        $user->status = UserEnum::getUnbanned();
        $user->is_banned = false;
        $user->banned_until = null;
        $user->save();
    }

    /**
     * Update user information
     */
    public static function updateUser(User $user, array $data): bool
    {
        $originalData = $user->toArray();
        $updatedData = array_intersect_key($data, array_flip($user->getFillable()));

        try {
            return $user->update($updatedData);
        } catch (\Exception $e) {
            $user->fill($originalData)->save();
            return false;
        }
    }

    /**
     * Check if user is banned
     */
    public function isBanned(): bool
    {
        if (!$this->is_banned) {
            return false;
        }

        if ($this->banned_until && Carbon::parse($this->banned_until)->isPast()) {
            $this->unban();
            return false;
        }

        return true;
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === UserEnum::getActive() && !$this->isBanned();
    }

    /**
     * Check if user is personal
     */
    public function isPersonal(): bool
    {
        return $this->is_personal;
    }

    /**
     * Check if user is organization
     */
    public function isOrganization(): bool
    {
        return $this->is_organization;
    }

    /**
     * Unban helper method
     */
    protected function unban(): void
    {
        static::unbanUser($this);
    }

    public function isAdmin()
    {
        return $this->role === 'Super Admin'; // Adjust this logic based on your role implementation
    }
}
