<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function unread()
    {
        $user = auth()->user();
        $query = $user->unreadNotifications()->where('type', '!=', 'App\\Notifications\\ReimbursementUpdated');
        $notifs = (clone $query)->take(8)->get();

        return response()->json([
            'count' => $query->count(),
            'items' => $notifs->map(fn($n) => [
                'id'      => $n->id,
                'title'   => $n->data['title'] ?? 'Notifikasi',
                'message' => $n->data['message'] ?? '',
                'url'     => $n->data['url'] ?? '#',
                'time'    => $n->created_at->diffForHumans(),
            ]),
        ]);
    }

    public function readAll()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    }

    public function read($id)
    {
        $n = auth()->user()->notifications()->findOrFail($id);
        $n->markAsRead();
        return response()->json(['success' => true]);
    }
}
