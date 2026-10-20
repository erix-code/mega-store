<?php

namespace Database\Seeders;

use App\Domain\Sales\ValueObjects\OrderStatus;
use App\Infrastructure\Persistence\Eloquent\Catalog\Product;
use App\Infrastructure\Persistence\Eloquent\Sales\Order;
use App\Infrastructure\Persistence\Eloquent\Sales\OrderItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class SalesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::active()->inStock()->get();

        User::where('email', '!=', 'admin@megatienda.test')->get()->each(function (User $user) use ($products) {
            $order = Order::factory()->recycle($user)->create();

            $products->random(rand(1, 3))->each(
                fn (Product $product) => OrderItem::factory()->forProduct($product)->for($order)->create()
            );

            $order->update(['total' => (float) $order->items()->sum('subtotal')]);
        });

        $guestOrder = Order::factory()->guest()->status(OrderStatus::Paid)->create();

        $products->random(2)->each(
            fn (Product $product) => OrderItem::factory()->forProduct($product)->for($guestOrder)->create()
        );

        $guestOrder->update(['total' => (float) $guestOrder->items()->sum('subtotal')]);
    }
}
