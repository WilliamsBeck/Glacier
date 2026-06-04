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
            description: $this->label($model, 'Dibuat'),
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
            description: $this->label($model, 'Diubah'),
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
            description: $this->label($model, 'Dihapus'),
            oldValues: $this->cleanValues($model->getAttributes()),
            newValues: null,
        );
    }

    private function label(Model $model, string $verb): string
    {
        $name = class_basename($model);
        $id   = $model->getKey();
        // Try to get a human-friendly identifier
        $extra = '';
        foreach (['name', 'reference_no', 'invoice_no', 'title', 'code'] as $field) {
            if (isset($model->$field)) {
                $extra = " — {$model->$field}";
                break;
            }
        }
        return "{$verb}: {$name} #{$id}{$extra}";
    }

    private function cleanValues(array $values): array
    {
        return array_filter($values, fn($k) => !in_array($k, $this->skipFields), ARRAY_FILTER_USE_KEY);
    }
}
