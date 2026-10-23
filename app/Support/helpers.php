<?php

if (! function_exists('currency')) {
    /**
     * ISO 4217 code of the currency used by the store.
     */
    function currency(): string
    {
        return config('tienda.currency', 'BOB');
    }
}

if (! function_exists('currency_symbol')) {
    /**
     * Short symbol of the currency used by the store.
     */
    function currency_symbol(): string
    {
        return config('tienda.currency_symbol', 'Bs');
    }
}

if (! function_exists('money')) {
    /**
     * Format an amount using the store currency.
     */
    function money(float|int|string $amount): string
    {
        return currency_symbol().' '.number_format((float) $amount, 2);
    }
}
