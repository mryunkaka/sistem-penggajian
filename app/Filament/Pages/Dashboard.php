<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\GajiPerBulanChart;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $title = 'Dashboard';

    protected static array $widgets = [
        StatsOverview::class,
        GajiPerBulanChart::class,
    ];

    // ✅ Ini yang penting untuk HILANGKAN Welcome + Filament Info
    protected function getHeaderWidgets(): array
    {
        return [];
    }
}
