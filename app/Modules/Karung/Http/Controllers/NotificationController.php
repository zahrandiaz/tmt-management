<?php

namespace App\Modules\Karung\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function getLatest()
    {
        return response()->json(
            Auth::user()->unreadNotifications
        );
    }
    
    /**
     * Menandai notifikasi sebagai sudah dibaca.
     */
    public function markAsRead(Request $request, $notificationId)
    {
        $user = Auth::user();
        $notification = $user->notifications()->where('id', $notificationId)->first();

        if ($notification) {
            $notification->markAsRead();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }
}