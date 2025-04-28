<?php

namespace App\Filament\Resources;

use Filament\Tables;
use Filament\Forms\Form;
use App\Models\MasterGaji;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use App\Filament\Resources\MasterGajiResource\Pages;

class MasterGajiResource extends Resource
{
    protected static ?string $model = MasterGaji::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Master Data';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('user_id')
                ->label('Karyawan')
                ->options(function () {
                    // Ambil semua user_id yang sudah ada di master_gaji
                    $existingUserIds = \App\Models\MasterGaji::pluck('user_id')->toArray();

                    // Filter user yang belum ada di master_gaji
                    return \App\Models\User::where('unit_id', Auth::user()->unit_id)
                        ->where('role', '!=', 'owner')
                        ->whereNotIn('id', $existingUserIds) // Hanya ambil user yang belum ada di master_gaji
                        ->get()
                        ->pluck('name', 'id')
                        ->toArray();
                })
                ->searchable()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) {
                    $user = \App\Models\User::find($state);
                    $set('jabatan', $user?->jabatan); // Set jabatan berdasarkan user yang dipilih
                })
                ->getOptionLabelUsing(fn($value): ?string => \App\Models\User::find($value)?->name) // Tampilkan nama user saat edit
                ->required(),

            TextInput::make('gaji_pokok')
                ->label('Gaji Pokok')
                ->prefix('Rp')
                ->required()
                ->reactive()
                ->live()
                ->mask(RawJs::make(<<<'JS'
                    $input.replace(/\D/g, '')
                        .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
                JS))
                ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state)) // Mengubah 1.500.000 menjadi 1500000
                ->formatStateUsing(function ($state) {
                    // Hanya format jika $state memiliki nilai
                    if ($state) {
                        return number_format((int)$state, 0, ',', '.');
                    }
                    return $state;
                }),

            TextInput::make('tunjangan_bbm')
                ->label('Tunjangan BBM')
                ->prefix('Rp')
                ->reactive()
                ->live()
                ->mask(RawJs::make(<<<'JS'
                    $input.replace(/\D/g, '')
                        .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
                JS))
                ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state)) // Menghapus titik sebelum menyimpan
                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')), // Menampilkan format dengan titik

            TextInput::make('tunjangan_makan')
                ->label('Tunjangan Makan')
                ->prefix('Rp')
                ->reactive()
                ->live()
                ->mask(RawJs::make(<<<'JS'
                    $input.replace(/\D/g, '')
                        .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
                JS))
                ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state)) // Menghapus titik sebelum menyimpan
                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')), // Menampilkan format dengan titik

            TextInput::make('tunjangan_jabatan')
                ->label('Tunjangan Jabatan')
                ->prefix('Rp')
                ->reactive()
                ->live()
                ->mask(RawJs::make(<<<'JS'
                    $input.replace(/\D/g, '')
                        .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
                JS))
                ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state)) // Menghapus titik sebelum menyimpan
                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')), // Menampilkan format dengan titik

            TextInput::make('tunjangan_kehadiran')
                ->label('Tunjangan Kehadiran')
                // ->helperText('Dihitung berdasarkan Kehadiran.')
                ->prefix('Rp')
                ->reactive()
                ->live()
                ->mask(RawJs::make(<<<'JS'
                    $input.replace(/\D/g, '')
                        .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
                JS))
                ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state)) // Menghapus titik sebelum menyimpan
                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')), // Menampilkan format dengan titik

            TextInput::make('tunjangan_lainnya')
                ->label('Tunjangan Lainnya')
                ->prefix('Rp')
                ->reactive()
                ->live()
                ->mask(RawJs::make(<<<'JS'
                    $input.replace(/\D/g, '')
                        .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
                JS))
                ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state)) // Menghapus titik sebelum menyimpan
                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')), // Menampilkan format dengan titik

            TextInput::make('tunj_bpjs_jht')
                ->label('Tunjangan BPJS JHT')
                ->prefix('Rp')
                ->reactive()
                ->live()
                ->mask(RawJs::make(<<<'JS'
                    $input.replace(/\D/g, '')
                        .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
                JS))
                ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state)) // Menghapus titik sebelum menyimpan
                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')), // Menampilkan format dengan titik

            TextInput::make('tunj_bpjs_kes')
                ->label('Tunjangan BPJS Kesehatan')
                ->prefix('Rp')
                ->reactive()
                ->live()
                ->mask(RawJs::make(<<<'JS'
                    $input.replace(/\D/g, '')
                        .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
                JS))
                ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state)) // Menghapus titik sebelum menyimpan
                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')), // Menampilkan format dengan titik

            TextInput::make('potongan_terlambat')
                ->label('Potongan Terlambat')
                ->helperText('Dihitung berdasarkan menit terlambat.')
                ->prefix('Rp')
                ->reactive()
                ->live()
                ->mask(RawJs::make(<<<'JS'
                    $input.replace(/\D/g, '')
                        .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
                JS))
                ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state)) // Menghapus titik sebelum menyimpan
                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')), // Menampilkan format dengan titik

            TextInput::make('pot_bpjs_jht')
                ->label('Potongan BPJS JHT')
                ->prefix('Rp')
                ->reactive()
                ->live()
                ->mask(RawJs::make(<<<'JS'
                    $input.replace(/\D/g, '')
                        .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
                JS))
                ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state)) // Menghapus titik sebelum menyimpan
                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')), // Menampilkan format dengan titik

            TextInput::make('pot_bpjs_kes')
                ->label('Potongan BPJS Kesehatan')
                ->prefix('Rp')
                ->reactive()
                ->live()
                ->mask(RawJs::make(<<<'JS'
                    $input.replace(/\D/g, '')
                        .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
                JS))
                ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state)) // Menghapus titik sebelum menyimpan
                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')), // Menampilkan format dengan titik

            Hidden::make('unit_id')
                ->default(fn() => Auth::user()->unit_id),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('user.name')->label('Nama')->searchable(),
            Tables\Columns\TextColumn::make('gaji_pokok')->money('IDR'),
            Tables\Columns\TextColumn::make('tunjangan_bbm')->money('IDR'),
            Tables\Columns\TextColumn::make('tunjangan_makan')->money('IDR'),
            Tables\Columns\TextColumn::make('tunjangan_jabatan')->money('IDR'),
            Tables\Columns\TextColumn::make('tunjangan_kehadiran')->money('IDR'),
            Tables\Columns\TextColumn::make('tunjangan_lainnya')->money('IDR'),
            Tables\Columns\TextColumn::make('tunj_bpjs_jht')->money('IDR'),
            Tables\Columns\TextColumn::make('tunj_bpjs_kes')->money('IDR'),
            Tables\Columns\TextColumn::make('potongan_terlambat')->money('IDR'),
            Tables\Columns\TextColumn::make('pot_bpjs_jht')->money('IDR'),
            Tables\Columns\TextColumn::make('pot_bpjs_kes')->money('IDR'),
        ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    // public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    // {
    //     return parent::getEloquentQuery()
    //         ->where('unit_id', Auth::user()->unit_id);
    // }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMasterGajis::route('/'),
            'create' => Pages\CreateMasterGaji::route('/create'),
            'edit' => Pages\EditMasterGaji::route('/{record}/edit'),
        ];
    }
}
