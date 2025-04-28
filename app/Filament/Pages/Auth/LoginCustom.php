<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Pages\Auth\Login;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use App\Models\Unit; // Pastikan Unit adalah model yang sudah ada

class LoginCustom extends Login
{
    /**
     * Mendapatkan form untuk login.
     *
     * @return array
     */
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getLoginFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getUnitFormComponent(), // Menambahkan pilihan unit
                        $this->getRememberFormComponent(),
                    ])
                    ->statePath('data')
            ),
        ];
    }

    /**
     * Menyimpan unit_id ke session setelah form berhasil disubmit.
     */
    public function authenticate(): ?\Filament\Http\Responses\Auth\Contracts\LoginResponse
    {
        $data = $this->form->getState();

        $login_type = filter_var($data['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        $authenticated = Auth::attempt([
            $login_type => $data['login'],
            'password' => $data['password'],
        ]);

        if (! $authenticated) {
            Notification::make()
                ->title('Login Gagal')
                ->body('Email/nama atau password salah.')
                ->danger()
                ->send();

            $this->throwFailureValidationException();
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Validasi akses unit: hanya owner yang boleh pilih unit bebas
        if ($user && $user->role !== 'owner') {
            if ($user->unit_id != $data['unit_id']) {
                Notification::make()
                    ->title('Akses Ditolak')
                    ->body('Kamu tidak memiliki akses ke unit yang dipilih.')
                    ->danger()
                    ->send();

                Auth::logout();
                $this->throwFailureValidationException();
            }
        }

        // Update unit_id jika user adalah owner dan memilih unit lain
        if ($user && $user->role === 'owner' && !empty($data['unit_id'])) {
            try {
                $user->unit_id = $data['unit_id'];
                $user->save();
            } catch (\Throwable $e) {
                Log::error('Gagal update unit_id pada user.', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->id ?? null,
                    'unit_id' => $data['unit_id'],
                ]);

                Notification::make()
                    ->title('Terjadi Kesalahan')
                    ->body('Gagal menyimpan unit yang dipilih.')
                    ->danger()
                    ->send();
            }
        }

        return parent::authenticate();
    }


    /**
     * Membuat komponen input untuk login (email atau nama).
     *
     * @return Component
     */
    protected function getLoginFormComponent(): Component
    {
        return TextInput::make('login')
            ->label(__('Nama / Email'))
            ->required()
            ->autocomplete('off')
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    /**
     * Menambahkan dropdown unit untuk dipilih oleh pengguna.
     *
     * @return Component
     */
    protected function getUnitFormComponent(): Component
    {
        return Select::make('unit_id')
            ->label(__('Pilih Unit'))
            ->options(Unit::all()->pluck('nama_unit', 'id')) // Mengambil data unit dari model Unit
            ->required()
            ->extraInputAttributes(['tabindex' => 2]);
    }

    /**
     * Mengambil kredensial dari data form.
     *
     * @param array $data
     * @return array
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        // Tentukan apakah input adalah email atau nama
        $login_type = filter_var($data['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        // Tambahkan unit_id ke kredensial untuk memverifikasi unit yang dipilih
        return [
            $login_type => $data['login'],
            'password' => $data['password'],
            // 'unit_id' => $data['unit_id'], // Pastikan unit_id disertakan dalam kredensial
        ];
    }

    /**
     * Menangani validasi yang gagal.
     *
     * @throws ValidationException
     */
    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.login' => __('filament-panels::pages/auth/login.messages.failed'),
        ]);
    }

    /**
     * Mengecek apakah pengguna memiliki akses ke unit yang dipilih.
     *
     * @param array $data
     * @return bool
     */
    protected function checkUnitAccess(array $data): bool
    {
        // Misalnya, Anda ingin memastikan bahwa user yang login hanya bisa memilih unit yang benar
        $user = User::where($this->getCredentialsFromFormData($data))->first();

        if ($user && $user->unit_id == $data['unit_id']) {
            return true;
        }

        return false;
    }
}
