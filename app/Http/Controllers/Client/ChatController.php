<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreChatMessageRequest;
use App\Http\Requests\Client\StoreChatRoomRequest;
use App\Models\ChatRoom;
use App\Services\ChatService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ChatController extends Controller
{
    public function __construct(private readonly ChatService $chatService) {}

    public function index(Request $request): JsonResponse
    {
        $rooms = $this->chatService->listRooms($request->user());

        return response()->json([
            'data' => $rooms,
            'unread_count' => $rooms->sum('unread_count'),
        ]);
    }

    public function store(StoreChatRoomRequest $request): JsonResponse
    {
        try {
            $room = $this->chatService->openRoom(
                $request->user(),
                (int) $request->validated('seller_id')
            );
        } catch (AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $room->load([
            'buyer:id,name,role,avatar',
            'seller:id,name,role,avatar',
            'seller.sellerProfile:id,user_id,shop_name,logo',
            'messages' => function ($query) {
                $query->latest('id')->limit(1);
            },
        ]);
        $room->loadCount([
            'messages as unread_count' => function ($query) use ($request) {
                $query->where('is_read', false)
                    ->where('sender_id', '!=', $request->user()->id);
            },
        ]);

        return response()->json([
            'data' => $this->chatService->serializeRoom($room, $request->user()),
        ], 201);
    }

    public function messages(Request $request, ChatRoom $chatRoom): JsonResponse
    {
        try {
            $afterId = $request->integer('after_id') ?: null;
            $messages = $this->chatService->getMessages($chatRoom, $request->user(), $afterId);
            $this->chatService->markRead($chatRoom, $request->user());
        } catch (AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }

        return response()->json([
            'data' => $messages,
        ]);
    }

    public function send(StoreChatMessageRequest $request, ChatRoom $chatRoom): JsonResponse
    {
        try {
            $message = $this->chatService->sendMessage(
                $chatRoom,
                $request->user(),
                $request->validated('message')
            );
        } catch (AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }

        return response()->json([
            'data' => $this->chatService->serializeMessage($message, $request->user()),
        ], 201);
    }

    public function markRead(Request $request, ChatRoom $chatRoom): JsonResponse
    {
        try {
            $this->chatService->markRead($chatRoom, $request->user());
        } catch (AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }

        return response()->json([
            'message' => 'Đã đánh dấu đã đọc.',
        ]);
    }
}
