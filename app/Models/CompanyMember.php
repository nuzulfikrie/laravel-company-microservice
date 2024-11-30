<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\CompanyMemberEnum;

class CompanyMember extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'email',
        'role',
        'company_id',
        'user_id',
    ];

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            if (!$model->role) {
                return false;
            }
        });

        self::updating(function ($model) {
            if (!$model->role) {
                return false;
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function rules()
    {
        return [
            // Start of Selection
            'company_id' => ['required', 'exists:companies,id'],
            'user_id' => ['required', 'exists:users,id'],
            'role' => ['required', 'string', 'in:' . implode(',', CompanyMemberEnum::getRoles())],
            'email' => ['required', 'email', 'unique:company_members,email'],
        ];
    }

    public static function createCompanyMember($data)
    {
        return self::create($data);
    }

    public static function updateCompanyMember($id, $data)
    {
        $oldData = self::find($id)->toArray();
        try {


            if (self::find($id)->update($data)) {
                return self::find($id);
            }

            return false;
        } catch (Exception $e) {
            self::find($id)->update($oldData);
            Log::error('Error updating company member: ' . $e->getMessage());
            return false;
        }
    }

    public static function deleteCompanyMember($id)
    {
        return self::find($id)->delete();
    }

    public static function getCompanyMember($companyId)
    {
        return self::where('company_id', $companyId)->get();
    }

    public static function getCompanyMemberById($id)
    {
        return self::find($id);
    }

    public static function getCompanyMemberByEmail($email)
    {
        return self::where('email', $email)->first();
    }

    public static function purgeCompanyMember($id)
    {
        return self::find($id)->forceDelete();
    }

    public static function rollbackUpdateCompanyMember($id, $oldData)
    {
        self::find($id)->update($oldData);

        return self::find($id);
    }

    public static function rollbackDeleteCompanyMember($id)
    {
        return self::withTrashed()->find($id)->restore();
    }
}
