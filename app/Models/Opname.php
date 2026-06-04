<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Opname extends Model
{
    protected $fillable = [
        'store_id', 'opname_date', 'period_month', 'period_year',
        'period_type', 'status', 'performed_by', 'approved_by', 'notes',
    ];
    protected $casts = ['opname_date' => 'date'];
    public function store()       { return $this->belongsTo(Store::class); }
    public function performedBy() { return $this->belongsTo(User::class, 'performed_by'); }
    public function approvedBy()  { return $this->belongsTo(User::class, 'approved_by'); }
    public function items()       { return $this->hasMany(OpnameItem::class); }

    /**
     * Tanggal opname approved TERBARU untuk sebuah toko (batas tutup periode).
     * Transaksi bertanggal <= ini dianggap periode tertutup.
     */
    public static function lockDateFor(int $storeId): ?string
    {
        // Berdasarkan PERIODE opname (bukan tanggal input): end_month → akhir bulan,
        // mid_month → tgl 15. Ambil tanggal terjauh dari semua opname approved.
        $max = null;
        foreach (static::where('store_id', $storeId)->where('status', 'approved')
                    ->get(['period_month', 'period_year', 'period_type']) as $o) {
            $d = $o->period_type === 'mid_month'
                ? \Carbon\Carbon::create($o->period_year, $o->period_month, 15)
                : \Carbon\Carbon::create($o->period_year, $o->period_month, 1)->endOfMonth();
            if (!$max || $d->gt($max)) $max = $d;
        }
        return $max?->toDateString();
    }

    /**
     * Apakah tanggal $date sudah tertutup oleh opname approved di toko $storeId.
     */
    public static function isDateLocked(int $storeId, string $date): bool
    {
        $lock = static::lockDateFor($storeId);
        return $lock !== null && \Carbon\Carbon::parse($date)->lte(\Carbon\Carbon::parse($lock));
    }

    /**
     * Pesan error standar bila periode tertutup.
     */
    public static function lockMessageFor(int $storeId): string
    {
        $lock = static::lockDateFor($storeId);
        $tgl  = $lock ? \Carbon\Carbon::parse($lock)->isoFormat('D MMMM Y') : '-';
        return "Periode sudah ditutup oleh Opname tanggal {$tgl}. "
             . "Transaksi pada/sebelum tanggal itu tidak bisa diinput/diubah. "
             . "Hapus opname tersebut dulu jika perlu koreksi.";
    }
}
