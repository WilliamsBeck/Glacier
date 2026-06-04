<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionLog extends Model
{
    protected $fillable = ['store_id', 'semi_finished_id', 'qty_produced', 'production_date', 'notes', 'created_by'];

    protected $casts = ['production_date' => 'date'];

    public function store()        { return $this->belongsTo(Store::class); }
    public function semiFinished() { return $this->belongsTo(Ingredient::class, 'semi_finished_id'); }
    public function createdBy()    { return $this->belongsTo(User::class, 'created_by'); }
    public function items()        { return $this->hasMany(ProductionLogItem::class); }
}
