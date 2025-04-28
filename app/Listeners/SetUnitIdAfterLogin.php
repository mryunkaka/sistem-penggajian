<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\User;

class SetUnitIdAfterLogin
{
    public function handle(Login $event): void
    {
        /** @var User $user */
        $user = $event->user;

        if (session()->has('unit_id_selected')) {
            $user->update([
                'unit_id' => session('unit_id_selected'),
            ]);

            session()->forget('unit_id_selected');
        }
    }
}
