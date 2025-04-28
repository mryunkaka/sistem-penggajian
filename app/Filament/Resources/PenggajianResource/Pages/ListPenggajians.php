<?php

namespace App\Filament\Resources\PenggajianResource\Pages;

use Filament\Actions;
use App\Models\Absensi;
use Filament\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\PenggajianResource;
use App\Models\User; // Sesuaikan dengan namespace model User
use App\Models\Penggajian; // Sesuaikan dengan namespace model Penggajian

class ListPenggajians extends ListRecords
{
    protected static string $resource = PenggajianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-o-plus')
                ->label('Penggajian Manual'),

            Action::make('penggajianOtomatis')
                ->label('Penggajian Otomatis')
                ->color('info')
                ->icon('heroicon-o-cog')
                ->modalHeading('Buat Penggajian Otomatis')
                ->modalSubmitActionLabel('Proses Penggajian')
                ->form([
                    DatePicker::make('tanggal_awal')->required(),
                    DatePicker::make('tanggal_akhir')->required(),
                ])
                ->action(function (array $data) {
                    $tanggalAwal = $data['tanggal_awal'];
                    $tanggalAkhir = $data['tanggal_akhir'];
                    $unitId = Auth::user()->unit_id;

                    // Pertama, periksa apakah ada data absensi dalam rentang tanggal
                    $hasAbsensi = Absensi::whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir])
                        ->whereHas('user', function ($query) use ($unitId) {
                            $query->where('unit_id', $unitId);
                        })
                        ->exists();

                    // Jika tidak ada data absensi, tampilkan pesan error dan hentikan proses
                    if (!$hasAbsensi) {
                        Notification::make()
                            ->title('Data Absensi Tidak Ditemukan')
                            ->body('Tidak dapat membuat penggajian karena tidak ada data absensi dalam rentang tanggal yang dipilih.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $users = User::where('unit_id', $unitId)->get();
                    $dataProcessed = false;

                    foreach ($users as $user) {
                        // Skip jika role pengguna adalah "owner"
                        if ($user->role === 'owner') {
                            continue;
                        }

                        // Periksa apakah ada absensi khusus untuk user ini
                        $userHasAbsensi = Absensi::where('user_id', $user->id)
                            ->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir])
                            ->exists();

                        if (!$userHasAbsensi) {
                            // Skip jika tidak ada absensi untuk user ini
                            continue;
                        }

                        // Periksa apakah penggajian untuk user ini dan rentang tanggal sudah ada
                        $existing = Penggajian::where('user_id', $user->id)
                            ->where('tanggal_awal_gaji', $tanggalAwal)
                            ->where('tanggal_akhir_gaji', $tanggalAkhir)
                            ->exists();

                        if ($existing) {
                            // Skip jika data sudah ada
                            continue;
                        }

                        $values = [];

                        // Gunakan fungsi calculateSalary dari PenggajianResource
                        PenggajianResource::calculateSalary(
                            $user->id,
                            $tanggalAwal,
                            $tanggalAkhir,
                            function ($key, $value = null) use (&$values) {
                                $values[$key] = $value !== null && $value !== '' ? str_replace('.', '', $value) : 0;
                            },
                            fn($key) => $values[$key] ?? null
                        );

                        // Simpan data penggajian jika tidak ada duplikat
                        Penggajian::create([
                            'user_id' => $user->id,
                            'tanggal_awal_gaji' => $tanggalAwal,
                            'tanggal_akhir_gaji' => $tanggalAkhir,
                            ...$values,
                        ]);
                        $dataProcessed = true;
                    }

                    if (!$dataProcessed) {
                        Notification::make()
                            ->title('Data tidak ditemukan')
                            ->body('Tidak ada data penggajian baru untuk rentang tanggal tersebut atau semua karyawan sudah memiliki data penggajian untuk periode ini.')
                            ->danger()
                            ->send();
                        return;
                    }

                    // Kirim notifikasi sukses
                    Notification::make()
                        ->title('Penggajian berhasil dibuat')
                        ->body('Penggajian berhasil dibuat untuk semua karyawan yang memiliki data absensi pada rentang tanggal terpilih.')
                        ->success()
                        ->send();
                }),

            Action::make('generateLaporan')
                ->label('Buat Laporan')
                ->color('success')
                ->icon('heroicon-o-arrow-down-tray')
                ->modalHeading('Buat Laporan Penggajian')
                ->modalSubmitActionLabel('Unduh PDF')
                ->form([
                    DatePicker::make('tanggal_awal')->required(),
                    DatePicker::make('tanggal_akhir')->required(),
                ])
                ->action(function (array $data) {
                    // Simpan parameter di session untuk digunakan pada controller terpisah
                    session(['laporan_tanggal_awal' => $data['tanggal_awal']]);
                    session(['laporan_tanggal_akhir' => $data['tanggal_akhir']]);

                    // Redirect ke route controller khusus PDF
                    return redirect()->route('pdf.generate-laporan-penggajian');
                }),
        ];
    }
}
