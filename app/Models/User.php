<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'status',
        'last_login_at',
        'inactive_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'inactive_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function isPenyewa(): bool
    {
        return $this->role === 'penyewa';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function tenantProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(TenantProfile::class, 'user_id');
    }

    public function adminGoogleAccounts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AdminGoogleAccount::class, 'user_id');
    }

    public function submittedManualPayments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ManualPayment::class, 'submitted_by');
    }

    public function verifiedManualPayments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ManualPayment::class, 'verified_by');
    }

    public function posts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    public function complaintActions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ComplaintAction::class, 'staff_id');
    }

    public function expenses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Expense::class, 'recorded_by');
    }

    public function payrollRecordsReceived(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PayrollRecord::class, 'staff_id');
    }

    public function payrollRecordsRecorded(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PayrollRecord::class, 'recorded_by');
    }

    public function cashAdvancesReceived(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CashAdvance::class, 'staff_id');
    }

    public function cashAdvancesRecorded(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CashAdvance::class, 'recorded_by');
    }

    public function financialRecordsRecorded(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(FinancialRecord::class, 'recorded_by');
    }
}
