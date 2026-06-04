<?php
namespace App\Http\Controllers;

use App\Models\{StoreStock, Store, DailyUsage};
use App\Notifications\LowStockNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    const DOS_WINDOW = 30;

    // ── Return JSON list of unread notifications ──────────────────────────────
    public function index()
    {
        $user  = auth()->user();
        $notifs = $user->unreadNotifications()->latest()->take(20)->get()->map(function ($n) {
            return [
                'id'         => $n->id,
                'type'       => $n->data['type']  ?? 'info',
                'message'    => $n->data['message'] ?? '',
                'store_name' => $n->data['store_name'] ?? '',
                'critical'   => $n->data['critical_count'] ?? 0,
                'warning'    => $n->data['warning_count']  ?? 0,
                'items'      => array_merge(
                    array_map(fn($i) => ['name' => $i['name'], 'dos' => $i['dos'], 'level' => 'critical'], $n->data['critical_items'] ?? []),
                    array_map(fn($i) => ['name' => $i['name'], 'dos' => $i['dos'], 'level' => 'warning'],  $n->data['warning_items']  ?? [])
                ),
                'time'       => $n->created_at->diffForHumans(),
                'created_at' => $n->created_at->toIso8601String(),
            ];
        });

        return response()->json([
            'count' => $user->unreadNotifications()->count(),
            'items' => $notifs,
        ]);
    }

    // ── Mark one or all as read ───────────────────────────────────────────────
    public function markRead(Request $request)
    {
        $user = auth()->user();
        if ($request->id) {
            $user->notifications()->where('id', $request->id)->update(['read_at' => now()]);
        } else {
            $user->unreadNotifications->markAsRead();
        }
        return response()->json(['ok' => true]);
    }

    // ── Generate / refresh low-stock notifications ────────────────────────────
    // Called on dashboard load (or can be scheduled).
    public function generateLowStock()
    {
        $user     = auth()->user();
        $storeIds = $user->accessibleStoreIds();
        if (empty($storeIds)) return response()->json(['generated' => 0]);

        $storeParLevels = Store::whereIn('id', $storeIds)
            ->get(['id', 'par_days'])
            ->mapWithKeys(fn($s) => [$s->id => $s->parLevelDays()]);

        $dosFrom   = now()->subDays(self::DOS_WINDOW - 1)->toDateString();
        $usageSums = DailyUsage::whereIn('store_id', $storeIds)
            ->where('usage_date', '>=', $dosFrom)
            ->where('qty_pack', '>', 0)
            ->groupBy(['store_id', 'ingredient_id'])
            ->selectRaw('store_id, ingredient_id, SUM(qty_pack) as total_pack')
            ->get()
            ->keyBy(fn($r) => $r->store_id . '-' . $r->ingredient_id);

        $stocks = StoreStock::with(['ingredient.packagings', 'store'])
            ->whereIn('store_id', $storeIds)
            ->where('stock_balance', '>=', 0)
            ->get()
            ->groupBy('store_id');

        $generated = 0;

        foreach ($stocks as $storeId => $storeStocks) {
            $criticalItems = [];
            $warningItems  = [];
            $parLevelDays  = $storeParLevels[$storeId] ?? null;

            foreach ($storeStocks as $ss) {
                $key   = $storeId . '-' . $ss->ingredient_id;
                $usage = $usageSums[$key] ?? null;
                if (!$usage) continue;

                $pkg          = $ss->ingredient->packagings->first();
                $ptb          = $pkg ? (float)$pkg->pack_to_base : 1;
                $avgDailyBase = ($usage->total_pack * $ptb) / self::DOS_WINDOW;
                if ($avgDailyBase < 0.001) continue;

                $dos    = round($ss->stock_balance / $avgDailyBase, 1);
                $status = $ss->dosStatus($dos, $parLevelDays);

                $entry = ['name' => $ss->ingredient->name, 'dos' => $dos];
                if ($status === 'critical') $criticalItems[] = $entry;
                elseif ($status === 'warning') $warningItems[]  = $entry;
            }

            if (empty($criticalItems) && empty($warningItems)) continue;

            // Only notify if no recent (today) notification for this store already
            $alreadyNotified = $user->notifications()
                ->where('created_at', '>=', now()->startOfDay())
                ->whereJsonContains('data->store_name', Store::find($storeId)?->name ?? '')
                ->exists();

            if (!$alreadyNotified) {
                $user->notify(new LowStockNotification(
                    storeName:     Store::find($storeId)?->name ?? "Store {$storeId}",
                    criticalItems: $criticalItems,
                    warningItems:  $warningItems,
                ));
                $generated++;
            }
        }

        return response()->json(['generated' => $generated]);
    }
}
