<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationBellController extends Controller
{
    public function markRead(Request $request, UserNotification $userNotification): RedirectResponse
    {
        abort_unless($userNotification->user_id === $request->user()->id, 403);
        $userNotification->markRead();

        if (is_string($userNotification->action_route) && $userNotification->action_route !== '') {
            try {
                return redirect()->route(
                    $userNotification->action_route,
                    $userNotification->action_params ?? []
                );
            } catch (\Throwable) {
                // route tidak valid — tetap kembali
            }
        }

        return back();
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        UserNotification::query()
            ->where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back();
    }
}
