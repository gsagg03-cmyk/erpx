<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'phone',
        'password',
        'created_by',
        'business_id',
        'due_system_enabled',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'due_system_enabled' => 'boolean',
        ];
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function username(): string
    {
        return 'phone';
    }

    // Relationships
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdUsers()
    {
        return $this->hasMany(User::class, 'created_by');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function stockEntries()
    {
        return $this->hasMany(StockEntry::class, 'added_by');
    }

    public function shopSetting()
    {
        return $this->hasOne(ShopSetting::class, 'owner_id');
    }

    // Helper methods
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('superadmin');
    }

    public function isOwner(): bool
    {
        return $this->hasRole('owner');
    }

    public function isManager(): bool
    {
        return $this->hasRole('manager');
    }

    public function isSalesman(): bool
    {
        return $this->hasRole('salesman');
    }

    // Check if due system is enabled for this user or their owner
    public function isDueSystemEnabled(): bool
    {
        if ($this->isSuperAdmin()) {
            return true; // Superadmin always sees everything
        }
        
        if ($this->isOwner()) {
            return $this->due_system_enabled;
        }
        
        // For Manager and Salesman, check their owner's setting
        if ($this->isManager()) {
            $owner = $this->creator; // Owner who created this manager
            return $owner && $owner->due_system_enabled;
        }
        
        if ($this->isSalesman()) {
            $manager = $this->creator; // Manager who created this salesman
            if ($manager) {
                $owner = $manager->creator; // Owner who created the manager
                return $owner && $owner->due_system_enabled;
            }
        }
        
        return false;
    }

    public function getDashboardRoute(): string
    {
        if ($this->isSuperAdmin()) {
            return 'superadmin.dashboard';
        } elseif ($this->isOwner()) {
            return 'owner.dashboard';
        } elseif ($this->isManager()) {
            return 'manager.dashboard';
        } else {
            return 'salesman.dashboard';
        }
    }
}
