<?php

namespace App\Models\Traits;

use App\Models\User;

/**
 * This trait is used to connect users to companies
 */
trait UserConnectionTrait
{
    /**
     * This function is used to get the company members
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function companyMembers()
    {
        return $this->belongsToMany(User::class, 'company_members', 'user_id', 'company_id');
    }
}
