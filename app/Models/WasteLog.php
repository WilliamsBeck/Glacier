<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WasteLog extends Model
{
    protected $fillable = ['store_id', 'waste_date', 'notes', 'total_loss_amount', 'recorded_by'];
    protected $casts    = ['waste_date' => 'date'];
    public function store()      { return $this->belongsTo(Store::class); }
    public function recordedBy() { return $this->belongsTo(User::class, 'recorded_by'); }
    public function items()      { return $this->hasMany(WasteLogItem::class); }
}
