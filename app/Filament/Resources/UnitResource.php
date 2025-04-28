<?php

namespace App\Filament\Resources;

use App\Models\Unit;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Filament\Resources\UnitResource\Pages;
use Filament\Tables\Columns\ImageColumn;

class UnitResource extends Resource
{
    protected static ?string $model = Unit::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Unit';
    protected static ?string $modelLabel = 'Unit';
    protected static ?string $pluralModelLabel = 'Data Unit';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nama_unit')
                    ->label('Nama Unit')
                    ->required(),

                TextInput::make('alamat_unit')
                    ->label('Alamat Unit')
                    ->required(),

                TextInput::make('no_hp_unit')
                    ->label('Nomor HP Unit')
                    ->tel(),

                FileUpload::make('logo_unit')
                    ->label('Logo Unit')
                    ->image()
                    ->directory('unit-logos')
                    ->preserveFilenames()
                    ->visibility('public')
                    ->disk('public'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_unit')->label('Nama Unit')->searchable(),
                TextColumn::make('alamat_unit')->label('Alamat')->searchable(),
                TextColumn::make('no_hp_unit')->label('Nomor HP')->searchable(),
                ImageColumn::make('logo_unit')
                    ->width(100)
                    ->height(100)
            ])->filters([
                //
            ])->actions([
                //
            ])->bulkActions([
                //
            ])->headerActions([
                //
            ])->emptyStateActions([
                //
            ])
            ->defaultSort('nama_unit')
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUnits::route('/'),
            'create' => Pages\CreateUnit::route('/create'),
            'edit' => Pages\EditUnit::route('/{record}/edit'),
        ];
    }
}
