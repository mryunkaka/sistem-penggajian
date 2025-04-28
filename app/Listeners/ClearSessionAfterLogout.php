<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Log;

class ClearSessionAfterLogout
{
    public function handle(Logout $event): void
    {
        session()->forget('unit_id_selected');
        session()->save();

        Log::info('Session unit_id_selected telah dihapus saat logout.', [
            'user_id' => $event->user->id ?? null,
        ]);
    }
}
