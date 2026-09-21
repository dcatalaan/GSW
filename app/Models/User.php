<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'billing_name',
        'doc_type',
        'doc_number',
        'nrc',
        'phone',
        'billing_address',
        'city',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Verificar si el usuario es administrador
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Verificar si el usuario es cliente
     */
    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    /**
     * Pedidos del usuario
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Items del carrito del usuario
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Reseñas del usuario
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
