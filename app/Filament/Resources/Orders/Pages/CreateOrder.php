<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Application\Sales\Commands\PlaceOrder;
use App\Domain\Sales\ValueObjects\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Infrastructure\Persistence\Eloquent\Sales\Order;
use App\Infrastructure\Persistence\Eloquent\Store\Client;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function handleRecordCreation(array $data): Order
    {
        $client = Client::findOrFail($data['client_id']);

        $shippingAddress = [
            'recipient_name' => $data['recipient_name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'line1' => $data['line1'],
            'line2' => $data['line2'] ?? null,
            'city' => $data['city'],
            'state' => $data['state'],
            'postal_code' => $data['postal_code'],
            'country' => $data['country'] ?? 'BO',
        ];

        $order = app(PlaceOrder::class)->place($client, $data['items'], $shippingAddress);

        $status = $data['status'] ?? null;
        $status = $status instanceof OrderStatus
            ? $status
            : (OrderStatus::tryFrom((string) $status) ?? OrderStatus::Pending);

        if ($status !== OrderStatus::Pending) {
            $order->update(['status' => $status]);
        }

        return $order;
    }
}
