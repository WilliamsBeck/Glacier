<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Observers\AuditObserver;
use App\Models\{Mutation, MutationItem, WasteLog, Opname, ProductionLog, MonthlySale, MonthlyRevenue, Store, Ingredient, Menu, Recipe, User, Supplier};
use Illuminate\Support\Facades\Blade;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        \Carbon\Carbon::setLocale('id');
        \Illuminate\Support\Facades\Date::setLocale('id');
        $models = [
            Mutation::class,
            MutationItem::class,
            WasteLog::class,
            Opname::class,
            ProductionLog::class,
            MonthlySale::class,
            MonthlyRevenue::class,
            Store::class,
            Ingredient::class,
            Menu::class,
            Recipe::class,
            User::class,
            Supplier::class,
        ];

        foreach ($models as $model) {
            $model::observe(AuditObserver::class);
        }

        // ── Blade directives untuk format angka Indonesia ────────────────────
        // @num($val)      → 1.000.000        (tanpa desimal)
        // @num($val, 2)   → 1.000.000,50     (dengan desimal)
        // @rp($val)       → Rp 1.000.000
        Blade::directive('num', function ($expression) {
            return "<?php echo number_format((float)({$expression} ?: 0), 0, ',', '.'); ?>";
        });

        Blade::directive('rp', function ($expression) {
            return "<?php echo 'Rp ' . number_format((float)({$expression} ?: 0), 0, ',', '.'); ?>";
        });
    }
}
