<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Absensi;
use Filament\Forms\Form;
use App\Models\MasterGaji;
use App\Models\Penggajian;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use Illuminate\Support\Carbon;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\LinkAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\PenggajianResource\Pages;

class PenggajianResource extends Resource
{
    protected static ?string $model = Penggajian::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Manajemen Data';

    public static function form(Form $form): Form
    {
        $isEdit = $form->getOperation() === 'edit';

        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->label('Karyawan')
                ->options(function () {
                    return User::where('unit_id', Auth::user()->unit_id)
                        ->where('role', '!=', 'owner')
                        ->pluck('name', 'id')
                        ->toArray();
                })
                ->searchable()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) {
                    Log::info('Memulai proses kalkulasi gaji untuk karyawan', ['user_id' => $state]);

                    $user = User::find($state);
                    Log::info('Data karyawan ditemukan', ['user_id' => $state, 'jabatan' => $user?->jabatan]);

                    Log::info('Unit ID dari pengguna yang login', ['user_id' => $state]);

                    // Cek apakah master gaji ditemukan
                    $masterGaji = MasterGaji::where('user_id', $state)->first();

                    if ($masterGaji) {
                        Log::info('Master Gaji ditemukan', [
                            'master_gaji_id' => $masterGaji->id,
                            'gaji_pokok' => $masterGaji->gaji_pokok,
                            'user_id' => $state
                        ]);

                        // Set nilai yang diambil dari MasterGaji
                        $set('gaji_pokok', $masterGaji->gaji_pokok);
                        $set('tunjangan_bbm', $masterGaji->tunjangan_bbm);
                        $set('tunjangan_lainnya', $masterGaji->tunjangan_lainnya);
                        $set('tunjangan_makan', $masterGaji->tunjangan_makan);
                        $set('tunjangan_jabatan', $masterGaji->tunjangan_jabatan);
                        $set('tunjangan_kehadiran', $masterGaji->tunjangan_kehadiran);
                        $set('tunj_bpjs_jht', $masterGaji->tunj_bpjs_jht);
                        $set('tunj_bpjs_kes', $masterGaji->tunj_bpjs_kes);
                        $set('pot_bpjs_jht', $masterGaji->pot_bpjs_jht);
                        $set('pot_bpjs_kes', $masterGaji->pot_bpjs_kes);
                    } else {
                        Log::warning('Master Gaji tidak ditemukan untuk user_id: ' . $state);
                    }
                })
                ->required(),

            Forms\Components\DatePicker::make('tanggal_awal_gaji')
                ->default(Carbon::now())
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set, $get) {
                    $user_id = $get('user_id');
                    if ($user_id && $state && $get('tanggal_akhir_gaji')) {
                        self::calculateSalary($user_id, $state, $get('tanggal_akhir_gaji'), $set, $get);
                    }
                })
                ->required(),

            Forms\Components\DatePicker::make('tanggal_akhir_gaji')
                ->default(Carbon::now()->addMonth())
                // ->reactive()
                ->lazy()
                ->afterStateUpdated(function ($state, callable $set, $get) {
                    $user_id = $get('user_id');
                    if ($user_id && $state && $get('tanggal_awal_gaji')) {
                        self::calculateSalary($user_id, $get('tanggal_awal_gaji'), $state, $set, $get);
                    }
                    Log::info('Tanggal akhir diperbarui', ['state' => $state]);
                })
                ->required(),

            Forms\Components\TextInput::make('gaji_pokok')
                ->label('Gaji Pokok')
                ->prefix('Rp')
                ->reactive()
                ->live()
                ->mask(RawJs::make(<<<'JS'
        $input.replace(/\D/g, '')
            .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
    JS))
                ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state))
                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),

            Forms\Components\TextInput::make('tunjangan_bbm')
                ->label('Tunjangan BBM')
                ->prefix('Rp')
                ->reactive()
                ->live()
                ->mask(RawJs::make(<<<'JS'
        $input.replace(/\D/g, '')
            .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
    JS))
                ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state))
                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),

            Forms\Components\TextInput::make('tunjangan_lainnya')
                ->label('Tunjangan Lainnya')
                ->prefix('Rp')
                ->reactive()
                ->live()
                ->mask(RawJs::make(<<<'JS'
        $input.replace(/\D/g, '')
            .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
    JS))
                ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state))
                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),

            Forms\Components\TextInput::make('tunjangan_makan')
                ->label('Tunjangan Makan')
                ->prefix('Rp')
                ->reactive()
                ->live()
                ->mask(RawJs::make(<<<'JS'
        $input.replace(/\D/g, '')
            .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
    JS))
                ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state))
                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),

            Forms\Components\TextInput::make('tunjangan_jabatan')
                ->label('Tunjangan Jabatan')
                ->prefix('Rp')
                ->reactive()
                ->live()
                ->mask(RawJs::make(<<<'JS'
        $input.replace(/\D/g, '')
            .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
    JS))
                ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state))
                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),

            Forms\Components\TextInput::make('tunjangan_kehadiran')
                ->label('Tunjangan Kehadiran')
                ->prefix('Rp')
                ->reactive()
                ->live()
                ->mask(RawJs::make(<<<'JS'
        $input.replace(/\D/g, '')
            .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
    JS))
                ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state))
                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),

            Forms\Components\TextInput::make('tunj_bpjs_jht')
                ->label('Tunjangan BPJS JHT')
                ->prefix('Rp')
                ->reactive()
                ->live()
                ->mask(RawJs::make(<<<'JS'
        $input.replace(/\D/g, '')
            .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
    JS))
                ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state))
                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),

            Forms\Components\TextInput::make('tunj_bpjs_kes')
                ->label('Tunjangan BPJS Kesehatan')
                ->prefix('Rp')
                ->reactive()
                ->live()
                ->mask(RawJs::make(<<<'JS'
        $input.replace(/\D/g, '')
            .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
    JS))
                ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state))
                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),

            Forms\Components\TextInput::make('lembur')
                ->label('Lembur')
                ->prefix('Rp')
                ->reactive()
                ->live()
                ->default(0)
                ->mask(RawJs::make(<<<'JS'
        $input.replace(/\D/g, '')
            .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
    JS))
                ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state))
                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),

            Forms\Components\TextInput::make('potongan_kehadiran')
                ->label('Potongan Kehadiran')
                ->prefix('Rp')
                ->reactive()
                ->live()
                ->default(0)
                ->mask(RawJs::make(<<<'JS'
        $input.replace(/\D/g, '')
            .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
    JS))
                ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state))
                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),

            Forms\Components\TextInput::make('potongan_ijin')
                ->label('Potongan Ijin')
                ->prefix('Rp')
                ->reactive()
                ->live()
                ->default(0)
                ->mask(RawJs::make(<<<'JS'
        $input.replace(/\D/g, '')
            .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
    JS))
                ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state))
                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),

            Forms\Components\TextInput::make('potongan_khusus')
                ->label('Potongan Ijin')
                ->prefix('Rp')
                ->reactive()
                ->live()
                ->default(0)
                ->mask(RawJs::make(<<<'JS'
        $input.replace(/\D/g, '')
            .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
    JS))
                ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state))
                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),

            Forms\Components\TextInput::make('potongan_terlambat')
                ->label('Potongan Terlambat')
                ->prefix('Rp')
                ->reactive()
                ->live()
                ->default(0)
                ->mask(RawJs::make(<<<'JS'
        $input.replace(/\D/g, '')
            .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
    JS))
                ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state))
                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),

            Forms\Components\TextInput::make('pot_bpjs_jht')
                ->label('Potongan BPJS JHT')
                ->prefix('Rp')
                ->reactive()
                ->live()
                ->default(0)
                ->mask(RawJs::make(<<<'JS'
        $input.replace(/\D/g, '')
            .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
    JS))
                ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state))
                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),

            Forms\Components\TextInput::make('pot_bpjs_kes')
                ->label('Potongan BPJS Kesehatan')
                ->prefix('Rp')
                ->reactive()
                ->live()
                ->default(0)
                ->mask(RawJs::make(<<<'JS'
        $input.replace(/\D/g, '')
            .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
    JS))
                ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state))
                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),

            Forms\Components\TextInput::make('total_potongan')
                ->label('Total Potongan')
                ->prefix('Rp')
                ->disabled()
                ->reactive()
                ->mask(RawJs::make(<<<'JS'
                        $input.replace(/\D/g, '')
                            .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
                    JS))
                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.'))
                ->afterStateHydrated(function ($state, callable $set, $get) {
                    $total_potongan =
                        (float)(str_replace('.', '', $get('potongan_kehadiran')) ?? 0) +
                        (float)(str_replace('.', '', $get('potongan_ijin')) ?? 0) +
                        (float)(str_replace('.', '', $get('potongan_khusus')) ?? 0) +
                        (float)(str_replace('.', '', $get('potongan_terlambat')) ?? 0) +
                        (float)(str_replace('.', '', $get('pot_bpjs_jht')) ?? 0) +
                        (float)(str_replace('.', '', $get('pot_bpjs_kes')) ?? 0);

                    $set('total_potongan', number_format($total_potongan, 0, ',', '.'));
                }),

            Forms\Components\TextInput::make('gaji_kotor')
                ->label('Gaji Kotor')
                ->prefix('Rp')
                ->reactive()
                ->disabled()
                ->live()
                ->default(0)
                ->mask(RawJs::make(<<<'JS'
        $input.replace(/\D/g, '')
            .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
    JS))
                ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state))
                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),

            // Input tampilan yang disable + tidak dikirim ke DB
            Forms\Components\TextInput::make('gaji_bersih')
                ->label('Gaji Bersih')
                ->prefix('Rp')
                ->disabled()
                ->dehydrated(false)
                ->reactive()
                ->mask(RawJs::make(<<<'JS'
                    $input.replace(/\D/g, '')
                        .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
                JS))
                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.'))
                ->afterStateUpdated(function (callable $set, callable $get) {
                    $gaji_kotor =
                        (int) str_replace('.', '', $get('gaji_pokok') ?? 0) +
                        (int) str_replace('.', '', $get('tunjangan_bbm') ?? 0) +
                        (int) str_replace('.', '', $get('tunjangan_lainnya') ?? 0) +
                        (int) str_replace('.', '', $get('tunjangan_makan') ?? 0) +
                        (int) str_replace('.', '', $get('tunjangan_jabatan') ?? 0) +
                        (int) str_replace('.', '', $get('tunjangan_kehadiran') ?? 0) +
                        (int) str_replace('.', '', $get('tunj_bpjs_jht') ?? 0) +
                        (int) str_replace('.', '', $get('tunj_bpjs_kes') ?? 0) +
                        (int) str_replace('.', '', $get('lembur') ?? 0);

                    $total_potongan =
                        (int) str_replace('.', '', $get('potongan_kehadiran') ?? 0) +
                        (int) str_replace('.', '', $get('potongan_ijin') ?? 0) +
                        (int) str_replace('.', '', $get('potongan_khusus') ?? 0) +
                        (int) str_replace('.', '', $get('potongan_terlambat') ?? 0) +
                        (int) str_replace('.', '', $get('pot_bpjs_jht') ?? 0) +
                        (int) str_replace('.', '', $get('pot_bpjs_kes') ?? 0);

                    $gaji_bersih = $gaji_kotor - $total_potongan;

                    $set('gaji_bersih_display', $gaji_bersih);
                    $set('gaji_bersih', $gaji_bersih);
                }),

            // Input tersembunyi untuk dikirim ke DB (tanpa titik ribuan)
            Forms\Components\Hidden::make('gaji_bersih')
                ->dehydrated()
                ->default(0)
                ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state)) // PENTING!
                ->afterStateHydrated(function (callable $set, callable $get) {
                    $gaji_kotor =
                        (int) str_replace('.', '', $get('gaji_pokok') ?? 0) +
                        (int) str_replace('.', '', $get('tunjangan_bbm') ?? 0) +
                        (int) str_replace('.', '', $get('tunjangan_lainnya') ?? 0) +
                        (int) str_replace('.', '', $get('tunjangan_makan') ?? 0) +
                        (int) str_replace('.', '', $get('tunjangan_jabatan') ?? 0) +
                        (int) str_replace('.', '', $get('tunjangan_kehadiran') ?? 0) +
                        (int) str_replace('.', '', $get('tunj_bpjs_jht') ?? 0) +
                        (int) str_replace('.', '', $get('tunj_bpjs_kes') ?? 0) +
                        (int) str_replace('.', '', $get('lembur') ?? 0);

                    $total_potongan =
                        (int) str_replace('.', '', $get('potongan_kehadiran') ?? 0) +
                        (int) str_replace('.', '', $get('potongan_ijin') ?? 0) +
                        (int) str_replace('.', '', $get('potongan_khusus') ?? 0) +
                        (int) str_replace('.', '', $get('potongan_terlambat') ?? 0) +
                        (int) str_replace('.', '', $get('pot_bpjs_jht') ?? 0) +
                        (int) str_replace('.', '', $get('pot_bpjs_kes') ?? 0);

                    $gaji_bersih = $gaji_kotor - $total_potongan;

                    $set('gaji_bersih', $gaji_bersih); // jangan pakai number_format
                }),

            Forms\Components\Hidden::make('gaji_kotor')
                ->dehydrated()
                ->default(0)
                ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state)),

            Forms\Components\Hidden::make('total_potongan')
                ->dehydrated()
                ->default(0)
                ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state)),
        ]);
    }

    public static function calculateSalary($user_id, $tanggal_awal, $tanggal_akhir, callable $set, callable $get)
    {
        try {
            Log::info('Memulai kalkulasi gaji dengan parameter', [
                'user_id' => $user_id,
                'tanggal_awal' => $tanggal_awal,
                'tanggal_akhir' => $tanggal_akhir
            ]);

            $user = User::find($user_id);
            if (!$user) {
                Log::error('User tidak ditemukan', ['user_id' => $user_id]);
                return;
            }

            // Mengambil master gaji
            $masterGaji = MasterGaji::where('user_id', $user->id)->first();
            if (!$masterGaji) {
                Log::error('Master gaji tidak ditemukan untuk unit', ['user_id' => $user->id]);
                return;
            }

            // Hitung jumlah hari antara tanggal awal dan akhir
            $tanggal_awal_parsed = Carbon::parse($tanggal_awal);
            $tanggal_akhir_parsed = Carbon::parse($tanggal_akhir);
            $jumlah_hari = $tanggal_awal_parsed->diffInDays($tanggal_akhir_parsed) + 1; // +1 untuk menghitung hari terakhir
            Log::info('Jumlah hari dalam periode', ['jumlah_hari' => $jumlah_hari]);

            // Menghitung status Hadir
            $hadir = Absensi::where('user_id', $user_id)
                ->whereBetween('tanggal', [$tanggal_awal, $tanggal_akhir])
                ->where('status', 'hadir')
                ->count();
            Log::info('Jumlah kehadiran', ['hadir' => $hadir]);

            // Hitung Total Absen
            $total_absen = Absensi::where('user_id', $user_id)
                ->whereBetween('tanggal', [$tanggal_awal, $tanggal_akhir])
                ->where('status', 'alfa')
                ->count();
            Log::info('Jumlah absen', ['total_absen' => $total_absen]);

            // Hitung total menit terlambat
            $totalTerlambat = Absensi::where('user_id', $user_id)
                ->whereBetween('tanggal', [$tanggal_awal, $tanggal_akhir])
                ->where('status', 'hadir')
                ->sum('total_menit_terlambat');
            Log::info('Total menit terlambat', ['total_menit_terlambat' => $totalTerlambat]);

            // Mengambil master gaji berdasarkan unit
            $masterGaji = \App\Models\MasterGaji::where('user_id', $user_id)->first();
            $potonganTerlambat = $masterGaji ? $masterGaji->potongan_terlambat : 1000;

            $denda_terlambat = $totalTerlambat * $potonganTerlambat;
            Log::info('Denda terlambat', ['denda_terlambat' => $denda_terlambat]);

            // Hitung uang makan berdasarkan kehadiran
            $tunjangan_makan_per_hari = $masterGaji->tunjangan_makan;
            $uang_makan = $tunjangan_makan_per_hari * $hadir;
            Log::info('Tunjangan makan', ['tunjangan_makan_per_hari' => $tunjangan_makan_per_hari, 'total' => $uang_makan]);

            // Hitung gaji pokok dan tunjangan
            $gaji_pokok = $masterGaji->gaji_pokok;
            $tunjangan_jabatan = $masterGaji->tunjangan_jabatan;
            $tunjangan_bbm = $masterGaji->tunjangan_bbm;
            $tunjangan_lainnya = $masterGaji->tunjangan_lainnya;
            $tunjangan_kehadiran = $masterGaji->tunjangan_kehadiran;
            $tunj_bpjs_jht = $masterGaji->tunj_bpjs_jht;
            $tunj_bpjs_kes = $masterGaji->tunj_bpjs_kes;

            // Hitung potongan per hari tidak hadir
            // $hitung_absen = $gaji_pokok / $jumlah_hari;
            $hitung_absen = $jumlah_hari != 0 ? $gaji_pokok / $jumlah_hari : 0;

            $potongan_kehadiran = $total_absen * $hitung_absen;
            Log::info('Potongan kehadiran', ['hitung_absen' => $hitung_absen, 'potongan_kehadiran' => $potongan_kehadiran]);

            // Hitung potongan BPJS
            $pot_bpjs_jht = $masterGaji->pot_bpjs_jht;
            $pot_bpjs_kes = $masterGaji->pot_bpjs_kes;

            // Hitung potongan ijin (belum ada implementasi untuk penggajian, anggap 0)
            $potongan_ijin = \App\Models\Absensi::where('user_id', $user_id)
                ->whereBetween('tanggal', [$tanggal_awal, $tanggal_akhir])
                ->sum('potongan_ijin');
            Log::info('potongan_ijin', ['potongan_ijin' => $potongan_ijin]);

            $potongan_khusus = \App\Models\Absensi::where('user_id', $user_id)
                ->whereBetween('tanggal', [$tanggal_awal, $tanggal_akhir])
                ->sum('potongan_khusus');
            Log::info('potongan_khusus', ['potongan_khusus' => $potongan_khusus]);

            // Hitung total potongan
            $total_potongan = $potongan_kehadiran + $pot_bpjs_jht + $pot_bpjs_kes + $potongan_ijin + $potongan_khusus + $denda_terlambat;
            Log::info('Total potongan', ['total_potongan' => $total_potongan]);

            // Hitung lembur (belum ada implementasi untuk lembur, anggap 0)

            $lembur = \App\Models\Absensi::where('user_id', $user_id)
                ->whereBetween('tanggal', [$tanggal_awal, $tanggal_akhir])
                ->sum('lembur');
            Log::info('Lembur', ['lembur' => $lembur]);

            // Hitung gaji kotor
            $gaji_kotor = $gaji_pokok + $tunjangan_jabatan + $tunjangan_bbm + $tunjangan_lainnya + $uang_makan + $tunjangan_kehadiran + $tunj_bpjs_jht + $tunj_bpjs_kes + $lembur;
            Log::info('Gaji kotor', ['gaji_kotor' => $gaji_kotor]);

            // Hitung gaji bersih
            $gaji_bersih = $gaji_kotor - $total_potongan;
            Log::info('Gaji bersih', ['gaji_bersih' => $gaji_bersih]);

            $set('gaji_pokok', number_format($gaji_pokok, 0, ',', '.'));
            $set('tunjangan_bbm', number_format($tunjangan_bbm, 0, ',', '.'));
            $set('tunjangan_lainnya', number_format($tunjangan_lainnya, 0, ',', '.'));
            $set('tunjangan_makan', number_format($uang_makan, 0, ',', '.'));
            $set('tunjangan_jabatan', number_format($tunjangan_jabatan, 0, ',', '.'));
            $set('tunjangan_kehadiran', number_format($tunjangan_kehadiran, 0, ',', '.'));
            $set('tunj_bpjs_jht', number_format($tunj_bpjs_jht, 0, ',', '.'));
            $set('tunj_bpjs_kes', number_format($tunj_bpjs_kes, 0, ',', '.'));
            $set('lembur', number_format($lembur, 0, ',', '.'));
            $set('potongan_kehadiran', number_format($potongan_kehadiran, 0, ',', '.'));
            $set('potongan_ijin', number_format($potongan_ijin, 0, ',', '.'));
            $set('potongan_khusus', number_format($potongan_khusus, 0, ',', '.'));
            $set('potongan_terlambat', number_format($denda_terlambat, 0, ',', '.'));
            $set('pot_bpjs_jht', number_format($pot_bpjs_jht, 0, ',', '.'));
            $set('pot_bpjs_kes', number_format($pot_bpjs_kes, 0, ',', '.'));
            $set('total_potongan', number_format($total_potongan, 0, ',', '.'));
            $set('gaji_bersih', number_format($gaji_bersih, 0, ',', '.'));
            $set('gaji_kotor', number_format($gaji_kotor, 0, ',', '.'));

            Log::info('Kalkulasi gaji selesai', ['user_id' => $user_id]);
        } catch (\Exception $e) {
            Log::error('Error pada kalkulasi gaji', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Karyawan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.jabatan')
                    ->label('Jabatan')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_awal_gaji')
                    ->label('Awal Gaji')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_akhir_gaji')
                    ->label('Akhir Gaji')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('gaji_pokok')
                    ->label('Gaji Pokok')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('tunjangan_bbm')
                    ->label('Tunjangan BBM')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('tunjangan_lainnya')
                    ->label('Tunjangan Lainnya')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('tunjangan_makan')
                    ->label('Tunjangan Makan')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('tunjangan_jabatan')
                    ->label('Tunjangan Jabatan')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('tunjangan_kehadiran')
                    ->label('Tunjangan Kehadiran')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('tunj_bpjs_jht')
                    ->label('Tunjangan BPJS JHT')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('tunj_bpjs_kes')
                    ->label('Tunjangan BPJS Kesehatan')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('lembur')
                    ->label('Lembur')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('potongan_ijin')
                    ->label('Potongan Ijin')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('potongan_khusus')
                    ->label('Potongan Kehadiran')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('potongan_kehadiran')
                    ->label('Potongan Kehadiran')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('pot_bpjs_jht')
                    ->label('Potongan BPJS JHT')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('pot_bpjs_kes')
                    ->label('Potongan BPJS Kesehatan')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('potongan_terlambat')
                    ->label('Potongan Terlambat')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('gaji_bersih')
                    ->label('Gaji Bersih')
                    ->money('IDR'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                LinkAction::make('slip-gaji')
                    ->label('Slip Gaji') // Nama tombol
                    ->url(fn($record) => route('slip-gaji', ['id' => $record->id])) // URL ke route slip gaji
                    ->openUrlInNewTab() // Buka di tab baru (opsional)
                    ->icon('heroicon-o-document-text') // Icon tombol (opsional)
                    ->color('success'), // Warna tombol (opsional)
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPenggajians::route('/'),
            'create' => Pages\CreatePenggajian::route('/create'),
            'edit' => Pages\EditPenggajian::route('/{record}/edit'),
        ];
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('user', function ($query) {
                $query->where('unit_id', Auth::user()->unit_id);
            });
    }
}
