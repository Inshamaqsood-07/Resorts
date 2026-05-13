<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'full_name', 'email', 'password', 'role', 'status', 'phone',
        'email_verified_at', 'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'password'          => 'hashed',
    ];

    // Role helpers
    public function isAdmin(): bool       
    { 
        return $this->role === 'admin'; 
    }
    
    public function isManager(): bool     
    { 
        return $this->role === 'resort_manager'; 
    }
    
    public function isClient(): bool      
    { 
        return $this->role === 'client'; 
    }

    // Status helpers
    public function isActive(): bool      
    { 
        return $this->status === 'active'; 
    }
    
    public function isPending(): bool     
    { 
        return $this->status === 'pending'; 
    }
    
    public function isSuspended(): bool   
    { 
        return $this->status === 'suspended'; 
    }
    
    public function isApproved(): bool    
    { 
        return $this->status === 'approved'; 
    }

    // Relationships
    public function clientProfile()
    {
        return $this->hasOne(ClientProfile::class);
    }

    public function managerProfile()
    {
        return $this->hasOne(ResortManagerProfile::class);
    }

    public function resort()
    {
        return $this->hasOne(Resort::class, 'manager_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'client_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function unreadNotifications()
    {
        return $this->hasMany(Notification::class)->where('is_read', false);
    }
    
    // Check if user is the main admin (id = 1)
    public function isSuperAdmin(): bool
    {
        return $this->role === 'admin' && $this->id === 1;
    }
}