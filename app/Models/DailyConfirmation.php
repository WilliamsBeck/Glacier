<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyConfirmation extends Model
{
    protected $fillable = ['store_id', 'confirmation_date', 'confirmed_by'];
    protected $casts    = ['confirmation_date' => 'date'];

    public function store()       { return $this->belongsTo(Store::class); }
    public function confirmedBy() { return $this->belongsTo(User::class, 'confirmed_by'); }
}
