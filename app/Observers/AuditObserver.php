<?php
namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditObserver
{
    // Fields to always skip (timestamps, large binary etc.)
    private array $skipFields = ['updated_at', 'created_at', 'remember_token'];

    public function created(Model $model): void
    {
        AuditLog::record(
            action: 'created',
            model: class_basename($model),
            modelId: $model->getKey(),
            description: $this->label($model, 'ditambahkan'),
            oldValues: null,
            newValues: $this->cleanValues($model->getAttributes()),
        );
    }

    public function updated(Model $model): void
    {
        $dirty = $model->getDirty();
        if (empty($dirty)) return;

        $old = array_intersect_key($model->getOriginal(), $dirty);
        $new = $dirty;

        AuditLog::record(
            action: 'updated',
            model: class_basename($model),
            modelId: $model->getKey(),
            description: $this->label($model, 'diperbarui'),
            oldValues: $this->cleanValues($old),
            newValues: $this->cleanValues($new),
        );
    }

    public function deleted(Model $model): void
    {
        AuditLog::record(
            action: 'deleted',
            model: class_basename($model),
            modelId: $model->getKey(),
            description: $this->label($model, 'dihapus'),
            oldValues: $this->cleanValues($model->getAttributes()),
            newValues: null,
        );
    }

    // Nama model dalam Bahasa Indonesia untuk deskripsi yang ramah dibaca
    private array $modelLabels = [
        'Mutation' => 'Mutasi Stok', 'WasteLog' => 'Catatan Waste',
        'ProductionLog' => 'Produksi', 'Opname' => 'Stok Opname',
        'MonthlySale' => 'Penjualan Bulanan', 'MonthlyRevenue' => 'Omzet Bulanan',
        'Store' => 'Toko', 'Supplier' => 'Supplier', 'Ingredient' => 'Bahan Baku',
        'Menu' => 'Menu', 'Recipe' => 'Resep', 'User' => 'User',
        'IngredientCategory' => 'Kategori Bahan', 'MenuCategory' => 'Kategori Menu',
        'StoreStock' => 'Saldo Stok', 'HppMonthlyReport' => 'Laporan HPP',
        'DailyConfirmation' => 'Konfirmasi Harian',
    ];

    private function label(Model $model, string $verb): string
    {
        $base = class_basename($model);
        $name = $this->modelLabels[$base] ?? $base;

        // Mutasi: pakai label tipe transaksi (Pembelian Pusat, Pembelian Internal, dst.)
        if ($base === 'Mutation' && method_exists($model, 'getTypeLabelAttribute')) {
            $name = $model->type_label;
        }

        // Identitas ramah bila ada (tanpa #id teknis)
        $extra = '';
        foreach (['reference_no', 'invoice_no', 'name', 'title', 'code'] as $field) {
            if (!empty($model->$field)) {
                $extra = " — {$model->$field}";
                break;
            }
        }
        return "{$name}{$extra} {$verb}";
    }

    private function cleanValues(array $values): array
    {
        return array_filter($values, fn($k) => !in_array($k, $this->skipFields), ARRAY_FILTER_USE_KEY);
    }
}
