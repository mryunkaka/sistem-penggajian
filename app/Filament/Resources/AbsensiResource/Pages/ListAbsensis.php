<?php

namespace App\Filament\Resources\AbsensiResource\Pages;

use App\Filament\Resources\AbsensiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\FileUpload;
use Filament\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AbsensiImport;
use Illuminate\Support\Facades\Storage;

class ListAbsensis extends ListRecords
{
    protected static string $resource = AbsensiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Action::make('import')
                ->label('Import Excel')
                ->icon('heroicon-o-document-arrow-up')
                ->form([
                    FileUpload::make('file')
                        ->label('Pilih File Excel')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'text/csv',
                            '.xlsx',
                            '.csv',
                        ])
                        ->maxSize(5120)
                        ->disk('local')
                        ->directory('imports/absensi')
                        ->preserveFilenames()
                        ->visibility('private')
                        ->required(),
                ])
                ->modalWidth('md')
                ->modalHeading('Impor Data Absensi')
                ->modalButton('Import')
                ->action(function (array $data): void {
                    logger()->info('Mulai proses import absensi.', ['data' => $data]);

                    if (!isset($data['file']) || empty($data['file'])) {
                        logger()->error('File tidak tersedia atau kosong.');
                        return;
                    }

                    try {
                        $filePath = Storage::disk('local')->path($data['file']);
                        logger()->info('Path file terdeteksi.', ['filePath' => $filePath]);

                        if (!file_exists($filePath)) {
                            logger()->error('File tidak ditemukan di path yang diberikan.', ['filePath' => $filePath]);
                            return;
                        }

                        logger()->info('Memulai proses import dengan Maatwebsite Excel.', ['filePath' => $filePath]);
                        Excel::import(new AbsensiImport, $filePath);
                        logger()->info('Proses import selesai.');
                    } catch (\Exception $e) {
                        logger()->error('Terjadi kesalahan saat import absensi.', [
                            'message' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                }),
        ];
    }
}
