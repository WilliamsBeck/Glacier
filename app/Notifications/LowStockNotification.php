<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    public function __construct(
        public string $storeName,
        public array  $criticalItems,  // [{name, dos}]
        public array  $warningItems,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'           => 'low_stock',
            'store_name'     => $this->storeName,
            'critical_count' => count($this->criticalItems),
            'warning_count'  => count($this->warningItems),
            'critical_items' => array_slice($this->criticalItems, 0, 5),
            'warning_items'  => array_slice($this->warningItems, 0, 5),
            'message'        => sprintf(
                '%s: %d bahan kritis, %d bahan perlu perhatian',
                $this->storeName,
                count($this->criticalItems),
                count($this->warningItems)
            ),
        ];
    }
}
