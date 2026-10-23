<?php

namespace App\Filament\Widgets;

use App\Domain\Sales\ValueObjects\OrderStatus;
use App\Infrastructure\Persistence\Eloquent\Catalog\Product;
use App\Infrastructure\Persistence\Eloquent\Sales\Order;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StoreStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalOrders = Order::count();
        $pendingOrders = Order::status(OrderStatus::Pending)->count();
        $revenue = (float) Order::whereNot('status', OrderStatus::Cancelled->value)->sum('total');
        $activeProducts = Product::active()->count();
        $lowStockProducts = Product::where('stock', '<', 5)->count();
        $customers = User::where('is_admin', false)->count();

        return [
            Stat::make('Órdenes', number_format($totalOrders))
                ->description("{$pendingOrders} pendientes")
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color($pendingOrders > 0 ? 'warning' : 'gray'),
            Stat::make('Ingresos', money($revenue))
                ->description('Sin órdenes canceladas')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('Productos activos', number_format($activeProducts))
                ->description($lowStockProducts > 0 ? "{$lowStockProducts} con stock bajo" : 'Stock saludable')
                ->descriptionIcon('heroicon-m-cube')
                ->color($lowStockProducts > 0 ? 'danger' : 'gray'),
            Stat::make('Clientes', number_format($customers))
                ->description('Usuarios registrados')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
        ];
    }
}
