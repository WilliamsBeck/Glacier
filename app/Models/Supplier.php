<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = ['name', 'type', 'contact', 'address', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function packagings()
    {
        return $this->hasMany(IngredientPackaging::class);
    }

    public function mutations()
    {
        return $this->hasMany(Mutation::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'zhisheng'       => 'Pusat',
            'local_supplier' => 'Supplier Lokal',
            'other'          => 'Lainnya',
            default          => $this->type,
        };
    }
}
