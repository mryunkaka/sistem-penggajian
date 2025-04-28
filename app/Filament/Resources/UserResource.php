<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use App\Models\MasterGaji;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Filament\Resources\UserResource\Pages;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationGroup = 'Manajemen Karyawan';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('unit_id')
                    ->default(Auth::user()->unit_id) // Mengambil unit_id dari user yang terlogin
                    ->required(),

                Forms\Components\TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('no_hp')
                    ->label('Nomor HP')
                    ->tel(),

                Forms\Components\Textarea::make('alamat')
                    ->rows(3),

                Forms\Components\TextInput::make('tempat_lahir'),

                Forms\Components\DatePicker::make('tanggal_lahir')
                    ->label('Tanggal Lahir'),

                Forms\Components\Select::make('jenis_kelamin')
                    ->options([
                        'Laki-laki' => 'Laki-laki',
                        'Perempuan' => 'Perempuan',
                    ]),

                Forms\Components\Select::make('agama')
                    ->options([
                        'Islam' => 'Islam',
                        'Kristen' => 'Kristen',
                        'Katolik' => 'Katolik',
                        'Hindu' => 'Hindu',
                        'Buddha' => 'Buddha',
                        'Konghucu' => 'Konghucu',
                    ]),

                Forms\Components\Select::make('status_perkawinan')
                    ->options([
                        'Belum Menikah' => 'Belum Menikah',
                        'Menikah' => 'Menikah',
                        'Cerai' => 'Cerai',
                    ]),

                Forms\Components\TextInput::make('nik')
                    ->maxLength(20),

                Forms\Components\TextInput::make('npwp')
                    ->maxLength(20),

                Forms\Components\TextInput::make('jabatan')
                    ->maxLength(100),

                Forms\Components\Select::make('role')
                    ->options([
                        'owner' => 'Owner',
                        'karyawan' => 'Karyawan',
                    ])
                    ->required(),

                Forms\Components\DatePicker::make('tanggal_bergabung')
                    ->label('Tanggal Bergabung'),

                Forms\Components\FileUpload::make('foto')
                    ->image()
                    ->directory('foto-users')
                    ->imageEditor()
                    ->maxSize(2048),

                Forms\Components\TextInput::make('password')
                    ->password()
                    ->label('Password')
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->required(fn(string $context): bool => $context === 'create')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('unit.nama_unit')->label('Unit')->searchable(),
                Tables\Columns\TextColumn::make('no_hp')->label('No. HP'),
                Tables\Columns\TextColumn::make('jabatan'),
                Tables\Columns\TextColumn::make('role'),
                Tables\Columns\TextColumn::make('tanggal_bergabung')->date(),
            ])
            ->filters([
                // Tambahkan filter jika diperlukan
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
