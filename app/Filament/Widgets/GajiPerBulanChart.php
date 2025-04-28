<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Penggajian;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Filament\Widgets\BarChartWidget;
use Filament\Forms\Components\Select;

class GajiPerBulanChart extends BarChartWidget
{
    protected static ?string $heading = 'Gaji Bersih per Bulan';

    public ?string $selectedYear = null;

    protected function getFormSchema(): array
    {
        $userUnitId = Auth::user()->unit_id;

        $years = Penggajian::where('unit_id', $userUnitId)
            ->selectRaw('YEAR(tanggal_awal_gaji) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year', 'year');

        $options = $years->isEmpty() ? [Carbon::now()->year => Carbon::now()->year] : $years->toArray();

        return [
            Select::make('selectedYear')
                ->label('Pilih Tahun')
                ->options($options)
                ->default(Carbon::now()->year)
                ->reactive()
                ->afterStateUpdated(fn() => $this->updateChartData()),
        ];
    }

    protected function getData(): array
    {
        $tahunDipilih = $this->selectedYear ?? Carbon::now()->year;
        $userUnitId = Auth::user()->unit_id;

        $data = Penggajian::join('users', 'penggajian.user_id', '=', 'users.id')
            ->where('users.unit_id', $userUnitId)
            ->whereYear('tanggal_awal_gaji', $tahunDipilih)
            ->selectRaw('MONTH(tanggal_awal_gaji) as bulan, SUM(gaji_bersih) as total')
            ->groupBy(DB::raw('MONTH(tanggal_awal_gaji)'))
            ->orderBy(DB::raw('MONTH(tanggal_awal_gaji)'))
            ->get();


        $labels = [];
        $values = [];

        foreach ($data as $row) {
            $labels[] = strtoupper(Carbon::create()->month($row->bulan)->translatedFormat('F')); // Bulan huruf besar
            $values[] = $row->total;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Gaji Bersih',
                    'data' => $values,
                    'backgroundColor' => '#10b981',
                ],
            ],
            'labels' => $labels,
        ];
    }
}
