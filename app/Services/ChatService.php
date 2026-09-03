<?php

namespace App\Services;

use App\Events\ChatMessageSent;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class ChatService
{
    public function listRooms(User $user): Collection
    {
        $rooms = ChatRoom::query()
            ->where(function ($query) use ($user) {
                $query->where('buyer_id', $user->id)
                    ->orWhere('seller_id', $user->id);
            })
            ->with([
                'buyer:id,name,role,avatar',
                'seller:id,name,role,avatar',
                'seller.sellerProfile:id,user_id,shop_name,logo',
                'messages' => function ($query) {
                    $query->latest('id')->limit(1);
                },
            ])
            ->withCount([
                'messages as unread_count' => function ($query) use ($user) {
                    $query->where('is_read', false)
                        ->where('sender_id', '!=', $user->id);
                },
            ])
            ->latest('updated_at')
            ->get();

        return $rooms->map(fn (ChatRoom $room) => $this->serializeRoom($room, $user));
    }

    public function openRoom(User $buyer, int $sellerId): ChatRoom
    {
        if ($buyer->status === 'blocked') {
            throw new AuthorizationException('Tài khoản của bạn đã bị khóa.');
        }

        if (! in_array($buyer->role, ['customer', 'seller'], true)) {
            throw new AuthorizationException('Chỉ khách hàng hoặc người bán mới có thể bắt đầu cuộc trò chuyện với gian hàng.');
        }

        if ($buyer->id === $sellerId) {
            throw new InvalidArgumentException('Bạn không thể chat với chính mình.');
        }

        $seller = User::query()->find($sellerId);
        if (! $seller || $seller->role !== 'seller') {
            throw new InvalidArgumentException('Gian hàng không tồn tại.');
        }

        if ($seller->status === 'blocked') {
            throw new InvalidArgumentException('Gian hàng này hiện không thể nhận tin nhắn.');
        }

        return ChatRoom::query()->firstOrCreate([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);
    }

    public function assertParticipant(ChatRoom $room, User $user): void
    {
        if ($user->status === 'blocked') {
            throw new AuthorizationException('Tài khoản của bạn đã bị khóa.');
        }

        if ($room->buyer_id !== $user->id && $room->seller_id !== $user->id) {
            throw new AuthorizationException('Bạn không có quyền truy cập phòng chat này.');
        }
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function getMessages(ChatRoom $room, User $user, ?int $afterId = null): Collection
    {
        $this->assertParticipant($room, $user);

        $query = $room->messages()
            ->with(['sender:id,name', 'chatRoom:id,buyer_id,seller_id'])
            ->orderBy('id');

        if ($afterId !== null && $afterId > 0) {
            $messages = $query->where('id', '>', $afterId)->get();
        } else {
            $messages = $room->messages()
                ->with(['sender:id,name', 'chatRoom:id,buyer_id,seller_id'])
                ->orderByDesc('id')
                ->limit(50)
                ->get()
                ->reverse()
                ->values();
        }

        return $messages->map(fn (ChatMessage $message) => $this->serializeMessage($message, $user));
    }

    public function sendMessage(ChatRoom $room, User $user, string $body): ChatMessage
    {
        $this->assertParticipant($room, $user);

        $message = ChatMessage::query()->create([
            'chat_room_id' => $room->id,
            'sender_id' => $user->id,
            'message' => $body,
            'is_read' => false,
            'created_at' => now(),
        ]);

        $room->touch();

        $message->load(['sender:id,name', 'chatRoom:id,buyer_id,seller_id']);

        ChatMessageSent::dispatch($message);

        return $message;
    }

    public function markRead(ChatRoom $room, User $user): void
    {
        $this->assertParticipant($room, $user);

        $room->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeRoom(ChatRoom $room, User $user): array
    {
        $isUserBuyer = ($room->buyer_id === $user->id);
        $other = $this->otherParty($room, $user);
        $lastMessage = $room->messages->first();

        if ($isUserBuyer) {
            // Đối phương là Người bán / Gian hàng
            $otherRole = 'seller';
            $otherRoleLabel = 'Gian hàng';
            $myRole = 'buyer';
            $myRoleLabel = 'Khách mua';
            $displayName = $other?->sellerProfile?->shop_name ?: ($other?->name ?? 'Gian hàng');
            $rawLogo = $other?->sellerProfile?->logo ?? $other?->avatar;
            $avatarUrl = $rawLogo
                ? (str_starts_with($rawLogo, 'http') ? $rawLogo : asset('storage/'.ltrim($rawLogo, '/')))
                : null;
            $shopId = $other?->sellerProfile?->id;
        } else {
            // Đối phương là Khách mua hàng liên hệ tới shop của tôi
            $otherRole = 'buyer';
            $otherRoleLabel = 'Khách mua';
            $myRole = 'seller';
            $myRoleLabel = 'Gian hàng';
            $displayName = $other?->name ?? 'Khách hàng';
            $rawAvatar = $other?->avatar;
            $avatarUrl = $rawAvatar
                ? (str_starts_with($rawAvatar, 'http') ? $rawAvatar : asset('storage/'.ltrim($rawAvatar, '/')))
                : null;
            $shopId = null;
        }

        return [
            'id' => $room->id,
            'name' => $displayName,
            'preview' => $lastMessage?->message ?: 'Bắt đầu cuộc trò chuyện',
            'date' => ($lastMessage?->created_at ?? $room->updated_at)?->format('d/m'),
            'avatar' => mb_strtoupper(mb_substr($displayName, 0, 1)),
            'avatar_url' => $avatarUrl,
            'unread_count' => (int) ($room->unread_count ?? 0),
            'other_user_id' => $other?->id,
            'other_role' => $otherRole,
            'other_role_label' => $otherRoleLabel,
            'my_role' => $myRole,
            'my_role_label' => $myRoleLabel,
            'shop_id' => $shopId,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeMessage(ChatMessage $message, User $user): array
    {
        $room = $message->chatRoom;
        $isSenderSeller = $room && $message->sender_id === $room->seller_id;
        $senderRole = $isSenderSeller ? 'seller' : 'buyer';
        $senderRoleLabel = $isSenderSeller ? 'Gian hàng' : 'Khách mua';

        return [
            'id' => $message->id,
            'message' => $message->message,
            'sender_id' => $message->sender_id,
            'sender_name' => $message->sender?->name,
            'sender_role' => $senderRole,
            'sender_role_label' => $senderRoleLabel,
            'is_mine' => $message->sender_id === $user->id,
            'created_at' => $message->created_at?->format('H:i'),
        ];
    }

    public function otherParty(ChatRoom $room, User $user): ?User
    {
        if ($room->buyer_id === $user->id) {
            return $room->seller;
        }

        if ($room->seller_id === $user->id) {
            return $room->buyer;
        }

        return null;
    }
}
