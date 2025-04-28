<?php

namespace App\Filament\Resources;

use App\Models\User;
use Filament\Tables;
use App\Models\Absensi;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use Filament\Actions\Action;
use App\Imports\AbsensiImport;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TimePicker;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\AbsensiResource\Pages;

class AbsensiResource extends Resource
{
    protected static ?string $model = Absensi::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Manajemen Karyawan';
    protected static ?string $navigationLabel = 'Absensi';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make(2)->schema([
                Select::make('user_id')
                    ->label('Karyawan')
                    ->options(function () {
                        return \App\Models\User::where('unit_id', Auth::user()->unit_id)
                            ->where('role', '!=', 'owner')
                            ->get()
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $user = \App\Models\User::find($state);
                        $set('jabatan', $user?->jabatan);
                        // Ambil data master_gaji berdasarkan user_id
                        $masterGaji = \App\Models\MasterGaji::where('user_id', $state)->first();
                        $potonganTerlambat = $masterGaji?->potongan_terlambat ?? 1000;

                        // Set nilai potongan terlambat ke state form
                        $set('potongan_terlambat', $potonganTerlambat);
                    })
                    ->required(),

                Hidden::make('jabatan')->default(''),

                DatePicker::make('tanggal')->label('Tanggal')->required(),

                Select::make('shift')
                    ->label('Shift')
                    ->options([
                        '1' => 'Shift 1',
                        '2' => 'Shift 2',
                        '3' => 'Shift 3',
                    ])
                    ->reactive(),

                TimePicker::make('jam_masuk')
                    ->label('Jam Masuk')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        $shift = $get('shift');
                        $jabatan = strtolower($get('jabatan'));
                        $jamMasukSah = match (true) {
                            $jabatan !== 'security' && $shift === '1' => '08:00',
                            $jabatan !== 'security' && $shift === '2' => '16:00',
                            $jabatan !== 'security' && $shift === '3' => '23:00',
                            $jabatan === 'security' && $shift === '1' => '07:00',
                            $jabatan === 'security' && $shift === '2' => '15:00',
                            $jabatan === 'security' && $shift === '3' => '00:00',
                            default => null,
                        };

                        if ($state && $jamMasukSah) {
                            try {
                                $jamMasukTime = \Carbon\Carbon::parse($state);
                                $jamMasukSahTime = \Carbon\Carbon::parse($jamMasukSah);
                                $totalMenitTerlambat = $jamMasukTime->greaterThan($jamMasukSahTime)
                                    ? round(abs($jamMasukTime->diffInMinutes($jamMasukSahTime)))
                                    : 0;

                                $set('total_menit_terlambat', $totalMenitTerlambat);
                                $userId = $get('user_id');
                                $masterGaji = \App\Models\MasterGaji::where('user_id', $userId)->first();
                                $potonganTerlambat = $masterGaji?->potongan_terlambat ?? 1000;

                                $set('jumlah_potongan', $totalMenitTerlambat * $potonganTerlambat);
                            } catch (\Exception $e) {
                                $set('total_menit_terlambat', 0);
                                $set('jumlah_potongan', 0);
                            }
                        }
                    }),

                TimePicker::make('jam_keluar')->label('Jam Keluar'),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'hadir' => 'Hadir',
                        'alfa' => 'Alfa',
                        'ijin' => 'Ijin',
                        'sakit' => 'Sakit',
                        'cuti' => 'Cuti',
                        'wfh' => 'WFH',
                    ])
                    ->required(),

                TextInput::make('total_menit_terlambat')
                    ->label('Total Menit Terlambat')
                    ->readOnly()
                    ->numeric()
                    ->default(0)
                    ->suffix('Menit')
                    ->afterStateUpdated(function ($state, callable $set) {
                        $potonganTerlambat = \App\Models\MasterGaji::where('nama', 'potongan_terlambat')->first()->nilai ?? 1000;
                        $set('jumlah_potongan', $state * $potonganTerlambat);
                    }),

                TextInput::make('jumlah_potongan')
                    ->label('Jumlah Potongan (Rupiah)')
                    ->prefix('Rp')
                    ->required()
                    ->reactive()
                    ->live()
                    ->mask(RawJs::make(<<<'JS'
                    $input.replace(/\D/g, '')
                        .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
                JS))
                    ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state))
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.'))
                    ->numeric(),

                TextInput::make('lembur')
                    ->label('Lembur')
                    ->numeric()
                    ->prefix('Rp')
                    ->mask(RawJs::make(<<<'JS'
                    $input.replace(/\D/g, '')
                        .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
                JS))
                    ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state)),

                TextInput::make('potongan_kehadiran')
                    ->label('Potongan Kehadiran')
                    ->numeric()
                    ->prefix('Rp')
                    ->mask(RawJs::make(<<<'JS'
                    $input.replace(/\D/g, '')
                        .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
                JS))
                    ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state)),

                TextInput::make('potongan_ijin')
                    ->label('Potongan Ijin')
                    ->numeric()
                    ->prefix('Rp')
                    ->mask(RawJs::make(<<<'JS'
                    $input.replace(/\D/g, '')
                        .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
                JS))
                    ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state)),

                TextInput::make('potongan_khusus')
                    ->label('Potongan Khusus')
                    ->numeric()
                    ->prefix('Rp')
                    ->mask(RawJs::make(<<<'JS'
                    $input.replace(/\D/g, '')
                        .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
                JS))
                    ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state)),
            ]),

            Grid::make(1)->schema([
                Textarea::make('keterangan_potongan')
                    ->label('Ketrangan Potongan Terlambat')
                    ->rows(2),

                Textarea::make('keterangan_lembur')
                    ->label('Keterangan Lembur')
                    ->rows(2),

                Textarea::make('keterangan_kehadiran')
                    ->label('Keterangan Potongan Kehadiran')
                    ->rows(2),

                Textarea::make('keterangan_ijin')
                    ->label('Keterangan Ijin')
                    ->rows(2),

                Textarea::make('keterangan_khusus')
                    ->label('Keterangan Potongan Khusus')
                    ->rows(2),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Karyawan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),

                TextColumn::make('shift')
                    ->label('Shift')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status Absensi')
                    ->sortable(),

                TextColumn::make('jam_masuk')
                    ->label('Jam Masuk')
                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->format('H:i'))
                    ->sortable(),

                TextColumn::make('jam_keluar')
                    ->label('Jam Keluar')
                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->format('H:i'))
                    ->sortable(),

                TextColumn::make('total_menit_terlambat')
                    ->label('Total Menit Terlambat')
                    ->sortable(),

                TextColumn::make('jumlah_potongan')
                    ->label('Total Potongan (Rp)')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable(),

                TextColumn::make('keterangan_potongan')
                    ->label('Keterangan Potongan')
                    ->limit(30)
                    ->wrap(),

                // Kolom tambahan
                TextColumn::make('lembur')
                    ->label('Lembur (Rp)')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable(),

                TextColumn::make('keterangan_lembur')
                    ->label('Keterangan Lembur')
                    ->limit(30)
                    ->wrap(),

                TextColumn::make('potongan_kehadiran')
                    ->label('Potongan Kehadiran (Rp)')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable(),

                TextColumn::make('keterangan_kehadiran')
                    ->label('Keterangan Kehadiran')
                    ->limit(30)
                    ->wrap(),

                TextColumn::make('potongan_ijin')
                    ->label('Potongan Ijin (Rp)')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable(),

                TextColumn::make('keterangan_ijin')
                    ->label('Keterangan Ijin')
                    ->limit(30),

                TextColumn::make('potongan_khusus')
                    ->label('Potongan Khusus (Rp)')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable(),

                TextColumn::make('keterangan_khusus')
                    ->label('Keterangan Khusus')
                    ->limit(30)
                    ->wrap(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAbsensis::route('/'),
            'create' => Pages\CreateAbsensi::route('/create'),
            'edit' => Pages\EditAbsensi::route('/{record}/edit'),
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
