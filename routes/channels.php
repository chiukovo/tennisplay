<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('instant-room.{slug}', function ($user, $slug) {
    if ($user) {
        // State Reconciliation: Mark user's current room EXCLUSIVELY
        \Illuminate\Support\Facades\Redis::connection('echo')->set('user_location:'.$user->id, $slug, 'EX', 3600);
        
        return [
            'id' => $user->id,
            'name' => $user->name,
            'avatar' => $user->line_picture_url,
            'uid' => $user->uid
        ];
    }
});

Broadcast::channel('instant-lobby', function ($user) {
    if ($user) {
        // Mark as lobby to clear room ghosts
        \Illuminate\Support\Facades\Redis::connection('echo')->set('user_location:'.$user->id, 'lobby', 'EX', 3600);
        
        return [
            'id' => $user->id,
            'name' => $user->name,
            'avatar' => $user->line_picture_url,
            'uid' => $user->uid
        ];
    }
});
