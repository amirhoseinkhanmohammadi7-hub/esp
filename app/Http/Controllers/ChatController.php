<?php

namespace App\Http\Controllers;

use App\Models\ChatRequest;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get all chat partners (users who have accepted chat requests)
        $acceptedRequests = ChatRequest::where('status', 'accepted')
            ->where(function($q) use ($user) {
                $q->where('sender_id', $user->id)
                  ->orWhere('receiver_id', $user->id);
            })
            ->with(['sender', 'receiver'])
            ->get();
        
        $chatPartners = [];
        foreach ($acceptedRequests as $request) {
            $partner = $request->sender_id === $user->id ? $request->receiver : $request->sender;
            $lastMessage = Chat::where(function($q) use ($user, $partner) {
                $q->where(function($q2) use ($user, $partner) {
                    $q2->where('user_id', $user->id)->where('receiver_id', $partner->id);
                })->orWhere(function($q2) use ($user, $partner) {
                    $q2->where('user_id', $partner->id)->where('receiver_id', $user->id);
                });
            })->latest()->first();
            
            $chatPartners[] = [
                'user' => $partner,
                'last_message' => $lastMessage,
                'request' => $request
            ];
        }
        
        return view('chat.index', compact('chatPartners'));
    }
    
    public function show($userId)
    {
        $user = Auth::user();
        $partner = User::findOrFail($userId);
        
        // Check if chat request is accepted
        $chatRequest = ChatRequest::where(function($q) use ($user, $partner) {
            $q->where('sender_id', $user->id)->where('receiver_id', $partner->id);
        })->orWhere(function($q) use ($user, $partner) {
            $q->where('sender_id', $partner->id)->where('receiver_id', $user->id);
        })->where('status', 'accepted')->first();
        
        if (!$chatRequest) {
            return redirect()->route('chat.index')->with('error', 'شما اجازه چت با این کاربر را ندارید');
        }
        
        $messages = Chat::where(function($q) use ($user, $partner) {
            $q->where(function($q2) use ($user, $partner) {
                $q2->where('user_id', $user->id)->where('receiver_id', $partner->id);
            })->orWhere(function($q2) use ($user, $partner) {
                $q2->where('user_id', $partner->id)->where('receiver_id', $user->id);
            });
        })->orderBy('created_at', 'asc')->get();
        
        return view('chat.show', compact('partner', 'messages'));
    }
    
    public function sendMessage(Request $request, $userId)
    {
        $user = Auth::user();
        $partner = User::findOrFail($userId);
        
        // Validate chat request
        $chatRequest = ChatRequest::where(function($q) use ($user, $partner) {
            $q->where('sender_id', $user->id)->where('receiver_id', $partner->id);
        })->orWhere(function($q) use ($user, $partner) {
            $q->where('sender_id', $partner->id)->where('receiver_id', $user->id);
        })->where('status', 'accepted')->first();
        
        if (!$chatRequest) {
            return response()->json(['success' => false, 'message' => 'شما اجازه چت با این کاربر را ندارید'], 403);
        }
        
        $validated = $request->validate([
            'message' => 'nullable|string|max:1000',
            'file' => 'nullable|file|max:10240', // 10MB max
        ]);
        
        $filePath = null;
        $fileType = null;
        
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            
            // Security: Validate file type
            $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'video/webm', 'audio/mpeg', 'audio/wav', 'application/pdf'];
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm', 'mp3', 'wav', 'pdf'];
            
            if (!in_array($file->getClientOriginalExtension(), $allowedExtensions)) {
                return response()->json(['success' => false, 'message' => 'نوع فایل مجاز نیست'], 400);
            }
            
            if (!in_array($file->getMimeType(), $allowedMimes)) {
                return response()->json(['success' => false, 'message' => 'نوع فایل مجاز نیست'], 400);
            }
            
            // Determine file type
            if (str_starts_with($file->getMimeType(), 'image/')) {
                $fileType = 'image';
            } elseif (str_starts_with($file->getMimeType(), 'video/')) {
                $fileType = 'video';
            } elseif (str_starts_with($file->getMimeType(), 'audio/')) {
                $fileType = 'audio';
            } else {
                $fileType = 'file';
            }
            
            // Store file with sanitized name
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('chats/' . date('Y/m'), $fileName, 'public');
        }
        
        if (empty($validated['message']) && !$filePath) {
            return response()->json(['success' => false, 'message' => 'پیام یا فایل الزامی است'], 400);
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
            'message' => $chat,
        ]);
    }
    
    public function sendRequest(Request $request, $userId)
    {
        $user = Auth::user();
        
        if ($user->id == $userId) {
            return response()->json(['success' => false, 'message' => 'نمیتوانید به خودتان درخواست دهید'], 400);
        }
        
        // Check if request already exists
        $existingRequest = ChatRequest::where(function($q) use ($user, $userId) {
            $q->where('sender_id', $user->id)->where('receiver_id', $userId);
        })->orWhere(function($q) use ($user, $userId) {
            $q->where('sender_id', $userId)->where('receiver_id', $user->id);
        })->first();
        
        if ($existingRequest) {
            if ($existingRequest->status === 'accepted') {
                return response()->json(['success' => false, 'message' => 'شما قبلاً با این کاربر چت دارید'], 400);
            } elseif ($existingRequest->status === 'pending') {
                return response()->json(['success' => false, 'message' => 'درخواست شما در انتظار بررسی است'], 400);
            }
        }
        
        ChatRequest::create([
            'sender_id' => $user->id,
            'receiver_id' => $userId,
            'status' => 'pending',
        ]);
        
        return response()->json(['success' => true, 'message' => 'درخواست چت ارسال شد']);
    }
    
    public function getRequests()
    {
        $user = Auth::user();
        $requests = ChatRequest::where('receiver_id', $user->id)
            ->where('status', 'pending')
            ->with('sender')
            ->get();
        
        return response()->json(['requests' => $requests]);
    }
    
    public function respondToRequest(Request $request, $requestId)
    {
        $user = Auth::user();
        $chatRequest = ChatRequest::findOrFail($requestId);
        
        if ($chatRequest->receiver_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'دسترسی ندارید'], 403);
        }
        
        $validated = $request->validate([
            'action' => 'required|in:accept,reject',
        ]);
        
        $chatRequest->update([
            'status' => $validated['action'] === 'accept' ? 'accepted' : 'rejected',
        ]);
        
        return response()->json(['success' => true]);
    }
}
