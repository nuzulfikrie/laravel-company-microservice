<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CompanyMember;
use App\Models\User;

/**
 * Class Company
 *
 * Represents a company entity within the application.
 *
 * @package App\Models
 */
class Company extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'companies';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'address',
        'email',
        'website',
        'phone',
        'note',
        'status',
        'has_multiple_subscriptions',
        'original_admin_id'
    ];

    protected $casts = [
        'email' => 'string',
        'has_multiple_subscriptions' => 'boolean',
    ];
    /**
     * Get the members associated with the company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function members()
    {
        return $this->hasMany(CompanyMember::class);
    }

    /**
     * Get the admin user associated with the company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'original_admin_id');
    }

    /**
     * Scope a query to only include active companies.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to include companies with multiple subscriptions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithMultipleSubscriptions($query)
    {
        return $query->where('has_multiple_subscriptions', true);
    }

    /**
     * Get the validation rules for creating or updating a company.
     *
     * @return array
     */
    public static function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:companies,email|max:255',
            'phone' => 'nullable|string|max:15',
            'website' => 'nullable|url|max:255',
            'status' => 'required|string|in:active,inactive',
            'has_multiple_subscriptions' => 'required|boolean',
            'original_admin_id' => 'required|exists:users,id',
        ];
    }

    /**
     * Create a new company with the given data.
     *
     * @param array $data
     * @return \App\Models\Company
     */
    public static function createCompany(array $data)
    {
        return self::create($data);
    }

    /**
     * Update an existing company with the provided data.
     *
     * @param int $companyId
     * @param array $data
     * @return bool
     * @throws \Exception If the company is not found or rollback fails.
     */
    public static function updateCompany(int $companyId, array $data)
    {
        $company = self::find($companyId);
        $oldData = $company->getAttributes();

        if (!$company) {
            throw new \Exception('Company not found');
        }

        // Attempt to update the company; rollback if update fails
        try {
            return $company->update($data);
        } catch (\Exception $e) {
            self::rollbackUpdate($companyId, $oldData);
            throw $e;
        }
    }

    /**
     * Rollback the company's data to its previous state.
     *
     * @param int $companyId
     * @param array $oldData
     * @return bool
     * @throws \Exception If the company is not found or rollback fails.
     */
    public static function rollbackUpdate(int $companyId, array $oldData)
    {
        // Find company by ID and update with old data
        $company = self::find($companyId);
        if (!$company) {
            throw new \Exception('Company not found');
        }

        // Attempt to rollback the update
        if (!$company->update($oldData)) {
            throw new \Exception('Failed to rollback update');
        }

        return true;
    }

    /**
     * Rollback the creation of a company.
     *
     * @param array $data
     * @return bool
     * @throws \Exception If the company is not found.
     */
    public static function rollbackCreation(array $data)
    {
        //find company with all data in array 
        $company = self::where($data)->first();
        if (!$company) {
            throw new \Exception('Company not found');
        }
        return $company->delete();
    }

    /**
     * Delete the company with the specified ID.
     *
     * @param int $companyId
     * @return bool|null
     * @throws \Exception If the company is not found.
     */
    public static function deleteCompany(int $companyId)
    {
        $company = self::find($companyId);
        if (!$company) {
            throw new \Exception('Company not found');
        }

        return $company->delete();
    }

    /**
     * Retrieve the company data for a given ID.
     *
     * @param int $companyId
     * @return \App\Models\Company|null
     */
    public static function getCompanyData(int $companyId)
    {
        return self::find($companyId);
    }

    /**
     * Get the members of the company with the specified ID.
     *
     * @param int $companyId
     * @return \Illuminate\Database\Eloquent\Collection|null
     * @throws \Exception If the company is not found.
     */
    public function getCompanyMembers(int $companyId)
    {
        $company = self::find($companyId);
        if (!$company) {
            throw new \Exception('Company not found');
        }

        return $company->members;
    }
}
