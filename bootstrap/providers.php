<?php

use App\Infrastructure\Providers\DomainServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    DomainServiceProvider::class,
];
