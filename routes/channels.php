<?php

use App\Models\ChatRoom;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.room.{roomId}', function (User $user, int $roomId) {
    $room = ChatRoom::query()->find($roomId);

    if (! $room) {
        return false;
    }

    return $room->buyer_id === $user->id || $room->seller_id === $user->id;
});
