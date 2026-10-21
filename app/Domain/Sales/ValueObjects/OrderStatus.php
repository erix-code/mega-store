<?php

namespace App\Domain\Sales\ValueObjects;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::Paid => 'Pagada',
            self::Shipped => 'Enviada',
            self::Delivered => 'Entregada',
            self::Cancelled => 'Cancelada',
        };
    }
}
