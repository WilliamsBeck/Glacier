<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockLedger extends Model
{
    protected $table = 'stock_ledger';   // ← TAMBAH BARIS INI

    public $timestamps  = false;
    const CREATED_AT    = 'created_at';
    const UPDATED_AT    = null;

    protected $fillable = [
        'store_id', 'ingredient_id', 'movement_date', 'movement_type',
        'qty_change', 'reference_type', 'reference_id',
        'balance_after', 'notes', 'created_by',
    ];

    protected $casts = ['movement_date' => 'date', 'created_at' => 'datetime'];

    public function store()      { return $this->belongsTo(Store::class); }
    public function ingredient() { return $this->belongsTo(Ingredient::class); }
    public function createdBy()  { return $this->belongsTo(User::class, 'created_by'); }
}