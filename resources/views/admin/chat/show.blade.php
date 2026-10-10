@extends('layouts.admin')

@section('title', 'Chat với '.$conversation->user->name)

@section('content')
    <div class="admin-page-heading">
        <div>
            <span class="admin-page-eyebrow">MESSENGER</span>
            <h1>{{ $conversation->user->name }}</h1>
            <p>{{ $conversation->user->email }}</p>
        </div>
        <a class="admin-action-button" href="{{ route('admin.chat.index') }}">← Danh sách hội thoại</a>
    </div>

    <section
        class="chat-panel chat-panel-admin"
        data-support-chat
        data-poll-url="{{ $pollUrl }}"
        data-current-user-id="{{ $currentUserId }}"
        data-support-conversation-id="{{ $conversation->id }}"
        data-viewer-role="admin"
        data-contact-name="{{ $conversation->user->name }}"
        data-last-message-id="{{ $messages->last()?->id ?? 0 }}"
        aria-label="Cuộc trò chuyện với {{ $conversation->user->name }}"
    >
        <div class="chat-transcript" data-chat-messages aria-live="polite" aria-relevant="additions">
            @forelse ($messages as $message)
                <article
                    class="chat-message {{ $message->sender_id === $currentUserId ? 'is-own' : '' }}"
                    data-chat-message-id="{{ $message->id }}"
                >
                    <span class="chat-message-sender">{{ $message->sender_id === $currentUserId ? 'Bạn' : ($message->sender_role === 'admin' ? 'Admin' : $conversation->user->name) }}</span>
                    <p>{{ $message->body }}</p>
                    <time>{{ $message->created_at->format('H:i d/m/Y') }}</time>
                </article>
            @empty
                <p class="chat-empty" data-chat-empty>Chưa có tin nhắn trong hội thoại này.</p>
            @endforelse
        </div>
        <p class="chat-status" data-chat-status role="status" aria-live="polite">Tin nhắn cập nhật tức thì khi khách hàng phản hồi.</p>
        <form class="chat-composer" method="POST" action="{{ route('admin.chat.messages.store', $conversation) }}">
            @csrf
            <label class="sr-only" for="chat-message">Nội dung trả lời</label>
            <textarea id="chat-message" name="body" rows="2" required maxlength="2000" placeholder="Nhập câu trả lời...">{{ old('body') }}</textarea>
            <button class="button button-dark" type="submit">Gửi trả lời <span aria-hidden="true">↗</span></button>
            @error('body')
                <p class="chat-form-error" role="alert">{{ $message }}</p>
            @enderror
        </form>
    </section>
@endsection
