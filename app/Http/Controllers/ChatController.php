<?php

namespace App\Http\Controllers;

use App\Models\ChatRequest;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ChatController extends Controller
{
    /**
     * Display the chat index page with all chat partners and pending requests.
     */
    public function index(): View
    {
        $user = Auth::user();

        // Get all accepted chat requests
        $acceptedRequests = ChatRequest::accepted()
            ->forUser($user->id)
            ->with(['sender', 'receiver'])
            ->get();

        $chatPartners = $acceptedRequests->map(function ($request) use ($user) {
            $partner = $request->sender_id === $user->id ? $request->receiver : $request->sender;

            $lastMessage = Chat::betweenUsers($user->id, $partner->id)
                ->latest()
                ->first();

            return [
                'user' => $partner,
                'last_message' => $lastMessage,
                'request' => $request,
            ];
        })->sortByDesc(fn ($partner) => $partner['last_message']?->created_at ?? now())
         ->values()
         ->all();

        return view('chat.index', compact('chatPartners'));
    }

    /**
     * Display a chat with a specific user.
     */
    public function show(int $userId): View|RedirectResponse
    {
        $user = Auth::user();
        $partner = User::findOrFail($userId);

        if (!$this->hasActiveChatRequest($user->id, $partner->id)) {
            return redirect()
                ->route('chat.index')
                ->with('error', 'شما اجازه چت با این کاربر را ندارید');
        }

        $messages = Chat::betweenUsers($user->id, $partner->id)
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark messages as read
        Chat::betweenUsers($user->id, $partner->id)
            ->unreadFor($user->id)
            ->each(fn ($message) => $message->markAsRead());

        return view('chat.show', compact('partner', 'messages'));
    }

    /**
     * Send a message to a user.
     */
    public function sendMessage(Request $request, int $userId): JsonResponse
    {
        $user = Auth::user();
        $partner = User::findOrFail($userId);

        if (!$this->hasActiveChatRequest($user->id, $partner->id)) {
            return response()->json([
                'success' => false,
                'message' => 'شما اجازه چت با این کاربر را ندارید',
            ], 403);
        }

        $validated = $request->validate([
            'message' => 'nullable|string|max:1000',
            'file' => 'nullable|file|max:10240', // 10MB max
        ]);

        $filePath = null;
        $fileType = null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $validationResult = $this->validateFile($file);

            if ($validationResult['error']) {
                return response()->json([
                    'success' => false,
                    'message' => $validationResult['error'],
                ], 400);
            }

            $fileType = $validationResult['type'];
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('chats/' . date('Y/m'), $fileName, 'public');
        }

        if (empty($validated['message']) && !$filePath) {
            return response()->json([
                'success' => false,
                'message' => 'پیام یا فایل الزامی است',
            ], 400);
        }

        $chat = Chat::create([
            'user_id' => $user->id,
            'receiver_id' => $partner->id,
            'message' => $validated['message'] ?? null,
            'file_path' => $filePath,
            'file_type' => $fileType,
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => $chat->load(['sender', 'receiver']),
        ]);
    }

    /**
     * Send a chat request to a user.
     */
    public function sendRequest(Request $request, int $userId): JsonResponse
    {
        $user = Auth::user();

        if ($user->id === $userId) {
            return response()->json([
                'success' => false,
                'message' => 'نمیتوانید به خودتان درخواست دهید',
            ], 400);
        }

        $existingRequest = ChatRequest::betweenUsers($user->id, $userId)->first();

        if ($existingRequest) {
            return match ($existingRequest->status) {
                ChatRequest::STATUS_ACCEPTED => response()->json([
                    'success' => false,
                    'message' => 'شما قبلاً با این کاربر چت دارید',
                ], 400),
                ChatRequest::STATUS_PENDING => response()->json([
                    'success' => false,
                    'message' => 'درخواست شما در انتظار بررسی است',
                ], 400),
                default => null,
            };
        }

        ChatRequest::create([
            'sender_id' => $user->id,
            'receiver_id' => $userId,
            'status' => ChatRequest::STATUS_PENDING,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'درخواست چت ارسال شد',
        ]);
    }

    /**
     * Get pending chat requests for the authenticated user.
     */
    public function getRequests(): JsonResponse
    {
        $user = Auth::user();

        $requests = ChatRequest::pending()
            ->where('receiver_id', $user->id)
            ->with('sender')
            ->get();

        return response()->json(['requests' => $requests]);
    }

    /**
     * Respond to a chat request (accept or reject).
     */
    public function respondToRequest(Request $request, int $requestId): JsonResponse
    {
        $user = Auth::user();
        $chatRequest = ChatRequest::findOrFail($requestId);

        if ($chatRequest->receiver_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'دسترسی ندارید',
            ], 403);
        }

        $validated = $request->validate([
            'action' => 'required|in:accept,reject',
        ]);

        $action = $validated['action'] === 'accept' ? 'accept' : 'reject';
        $chatRequest->$action();

        return response()->json(['success' => true]);
    }

    /**
     * API endpoint to fetch new messages for a chat.
     */
    public function getNewMessages(int $userId, Request $request): JsonResponse
    {
        $user = Auth::user();
        $partner = User::findOrFail($userId);
        $lastId = $request->query('last_id');

        if (!$this->hasActiveChatRequest($user->id, $partner->id)) {
            return response()->json([
                'success' => false,
                'message' => 'شما اجازه چت با این کاربر را ندارید',
            ], 403);
        }

        $query = Chat::betweenUsers($user->id, $partner->id);

        if ($lastId) {
            $query->where('id', '>', $lastId);
        }

        $messages = $query->orderBy('created_at', 'asc')->get();

        return response()->json([
            'success' => true,
            'messages' => $messages,
        ]);
    }

    /**
     * API endpoint to handle typing status notifications.
     */
    public function typingStatus(int $userId, Request $request): JsonResponse
    {
        $user = Auth::user();
        $isTyping = $request->input('is_typing', false);
        
        // ذخیره وضعیت تایپ در کش برای ۳ ثانیه
        if ($isTyping) {
            cache()->set("typing_{$userId}_{$user->id}", true, 3);
        } else {
            cache()->forget("typing_{$userId}_{$user->id}");
        }
        
        return response()->json(['success' => true]);
    }
    
    /**
     * API endpoint to get typing status.
     */
    public function getTypingStatus(int $userId): JsonResponse
    {
        $user = Auth::user();
        $isTyping = cache()->get("typing_{$userId}_{$user->id}", false);
        
        return response()->json([
            'success' => true,
            'is_typing' => $isTyping
        ]);
    }

    /**
     * Check if two users have an active (accepted) chat request.
     */
    private function hasActiveChatRequest(int $userId1, int $userId2): bool
    {
        return ChatRequest::betweenUsers($userId1, $userId2)
            ->accepted()
            ->exists();
    }

    /**
     * Validate uploaded file type and size.
     *
     * @return array{error: string|null, type: string|null}
     */
    private function validateFile($file): array
    {
        $allowedMimes = Chat::ALLOWED_MIME_TYPES;
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm', 'mp3', 'wav', 'webm', 'ogg', 'pdf'];

        if (!in_array($file->getClientOriginalExtension(), $allowedExtensions)) {
            return ['error' => 'نوع فایل مجاز نیست', 'type' => null];
        }

        if (!in_array($file->getMimeType(), $allowedMimes)) {
            return ['error' => 'نوع فایل مجاز نیست', 'type' => null];
        }

        $fileType = match (true) {
            str_starts_with($file->getMimeType(), 'image/') => Chat::TYPE_IMAGE,
            str_starts_with($file->getMimeType(), 'video/') => Chat::TYPE_VIDEO,
            str_starts_with($file->getMimeType(), 'audio/') => Chat::TYPE_AUDIO,
            default => Chat::TYPE_FILE,
        };

        return ['error' => null, 'type' => $fileType];
    }
}
