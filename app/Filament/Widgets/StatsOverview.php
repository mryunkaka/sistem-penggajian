<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Penggajian;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsOverview extends BaseWidget
{
    protected ?string $heading = 'Dashboard Ringkasan';
    protected ?string $description = 'Ringkasan pengeluaran dan karyawan';
    public ?string $selectedMonth = null;

    public function mount(): void
    {
        $this->selectedMonth = now()->format('Y-m'); // Default bulan ini
    }

    public function getFormSchema(): array
    {
        return [
            Select::make('selectedMonth')
                ->label('Pilih Bulan')
                ->options([
                    '2025-01' => 'Januari 2025',
                    '2025-02' => 'Februari 2025',
                    '2025-03' => 'Maret 2025',
                    '2025-04' => 'April 2025',
                    // dan seterusnya...
                ])
                ->default($this->selectedMonth)
                ->reactive()
                ->afterStateUpdated(fn() => $this->dispatch('refreshWidget')),
        ];
    }

    protected function getStats(): array
    {
        $bulan = $this->selectedMonth ?? now()->format('Y-m');
        $userUnitId = Auth::user()->unit_id;

        $total = Penggajian::join('users', 'penggajian.user_id', '=', 'users.id')
            ->where('users.unit_id', $userUnitId)
            ->whereMonth('penggajian.created_at', Carbon::parse($bulan)->month)
            ->whereYear('penggajian.created_at', Carbon::parse($bulan)->year)
            ->sum('gaji_bersih');

        $totalKaryawan = User::where('unit_id', $userUnitId)
            ->where('role', '!=', 'owner')
            ->count();

        return [
            Stat::make('Total Pengeluaran Gaji', number_format($total))
                ->description('Total gaji bersih yang sudah dibayarkan')
                ->color('success'),

            Stat::make('Total Karyawan', $totalKaryawan)
                ->description('Aktif')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
        ];
    }
}
