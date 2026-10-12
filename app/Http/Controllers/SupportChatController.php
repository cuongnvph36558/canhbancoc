<?php

namespace App\Http\Controllers;

use App\Events\SupportMessageCreated;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SupportChatController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        $conversation = $user->supportConversation;
        $messages = $conversation
            ? $conversation->messages()->latest('id')->limit(100)->get()->reverse()->values()
            : collect();

        return view('store.chat', [
            'conversation' => $conversation,
            'messages' => $messages,
            'currentUserId' => $user->id,
            'pollUrl' => route('chat.messages.index'),
        ]);
    }

    public function messages(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'after' => ['nullable', 'integer', 'min:0'],
        ]);
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        return $this->messagesAfter(
            $user->supportConversation,
            isset($validated['after']) ? (int) $validated['after'] : null,
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['required_without:image', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        $conversation = $user->supportConversation()->firstOrCreate();
        $this->persistMessage($conversation, $user, $validated['body'] ?? null, $request->file('image'));

        return to_route('chat.index');
    }

    public function adminIndex(): View
    {
        $conversations = SupportConversation::query()
            ->whereNotNull('last_message_at')
            ->with([
                'user:id,name,email',
                'latestMessage',
            ])
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->paginate(15);

        return view('admin.chat.index', [
            'conversations' => $conversations,
        ]);
    }

    public function adminShow(SupportConversation $supportConversation): View
    {
        $supportConversation->load('user:id,name,email');
        $messages = $supportConversation->messages()
            ->latest('id')
            ->limit(100)
            ->get()
            ->reverse()
            ->values();

        return view('admin.chat.show', [
            'conversation' => $supportConversation,
            'messages' => $messages,
            'currentUserId' => auth()->id(),
            'pollUrl' => route('admin.chat.messages.index', $supportConversation),
        ]);
    }

    public function adminMessages(Request $request, SupportConversation $supportConversation): JsonResponse
    {
        $validated = $request->validate([
            'after' => ['nullable', 'integer', 'min:0'],
        ]);

        return $this->messagesAfter(
            $supportConversation,
            isset($validated['after']) ? (int) $validated['after'] : null,
        );
    }

    public function adminStore(Request $request, SupportConversation $supportConversation): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['required_without:image', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
        $admin = $request->user();
        abort_unless($admin instanceof User, 401);

        $this->persistMessage($supportConversation, $admin, $validated['body'] ?? null, $request->file('image'));

        return to_route('admin.chat.show', $supportConversation);
    }

    private function messagesAfter(?SupportConversation $conversation, ?int $after): JsonResponse
    {
        $messages = $conversation
            ? $conversation->messages()
                ->when($after !== null, fn ($query) => $query->where('id', '>', $after))
                ->orderBy('id')
                ->limit(100)
                ->get()
                ->map(fn (SupportMessage $message): array => $this->messagePayload($message))
            : collect();

        return response()->json([
            'messages' => $messages->values(),
        ]);
    }

    private function persistMessage(SupportConversation $conversation, User $sender, ?string $body, $image = null): void
    {
        $imagePath = null;

        if ($image) {
            $imagePath = $image->store('chat-images', 'public');
        }

        $message = DB::transaction(function () use ($conversation, $sender, $body, $imagePath): SupportMessage {
            $message = $conversation->messages()->create([
                'sender_id' => $sender->id,
                'sender_role' => $sender->isAdmin() ? 'admin' : 'client',
                'body' => $body ?? '',
                'image_path' => $imagePath,
            ]);

            $conversation->update([
                'last_message_at' => $message->created_at,
            ]);

            return $message;
        });

        $customer = $conversation->user;

        SupportMessageCreated::dispatch(
            $message,
            $customer->id,
            $customer->name,
            $sender->isAdmin()
                ? route('chat.index')
                : route('admin.chat.show', $conversation),
        );
    }

    /**
     * @return array{id: int, sender_id: int|null, sender_role: string, body: string, image_url: string|null, time: string}
     */
    private function messagePayload(SupportMessage $message): array
    {
        $imageUrl = null;

        if ($message->image_path) {
            $imageUrl = Storage::disk('public')->url($message->image_path);
        }

        return [
            'id' => $message->id,
            'sender_id' => $message->sender_id,
            'sender_role' => $message->sender_role,
            'body' => $message->body,
            'image_url' => $imageUrl,
            'time' => $message->created_at->format('H:i d/m/Y'),
        ];
    }
}
