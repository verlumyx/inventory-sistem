<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'companies';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name_company',
        'dni',
        'address',
        'phone',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the first company record (singleton pattern).
     */
    public static function getCompany(): ?Company
    {
        return static::first();
    }

    /**
     * Get or create the company record.
     */
    public static function getOrCreateCompany(): Company
    {
        $company = static::first();

        if (!$company) {
            $company = static::create([
                'name_company' => 'Mi Empresa',
                'dni' => '12345678-9',
                'address' => 'Dirección de la empresa',
                'phone' => '+58 412-123-4567',
            ]);
        }

        return $company;
    }

    /**
     * Convert the model to an array for API responses.
     */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'name_company' => $this->name_company,
            'dni' => $this->dni,
            'address' => $this->address,
            'phone' => $this->phone,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
