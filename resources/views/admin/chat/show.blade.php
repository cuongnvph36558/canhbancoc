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
                    @if ($message->image_path)
                        <p class="chat-message-image">
                            <img src="{{ asset('chat-images/' . $message->image_path) }}" alt="Hình ảnh trong tin nhắn" loading="lazy">
                        </p>
                    @endif
                    @if ($message->body)
                        <p>{{ $message->body }}</p>
                    @endif
                    <time>{{ $message->created_at->format('H:i d/m/Y') }}</time>
                </article>
            @empty
                <p class="chat-empty" data-chat-empty>Chưa có tin nhắn trong hội thoại này.</p>
            @endforelse
        </div>
        <p class="chat-status" data-chat-status role="status" aria-live="polite">Tin nhắn cập nhật tức thì khi khách hàng phản hồi.</p>
        <form class="chat-composer" method="POST" action="{{ route('admin.chat.messages.store', $conversation) }}" enctype="multipart/form-data">
            @csrf
            <label class="sr-only" for="chat-message">Nội dung trả lời</label>
            <textarea id="chat-message" name="body" rows="2" maxlength="2000" placeholder="Nhập câu trả lời hoặc gửi ảnh...">{{ old('body') }}</textarea>
            <div class="chat-composer-actions">
                <label class="chat-image-upload" for="chat-image">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <circle cx="9" cy="9" r="2"></circle>
                        <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path>
                    </svg>
                    <span class="sr-only">Gửi ảnh</span>
                    <input type="file" id="chat-image" name="image" accept="image/jpeg,image/png,image/webp" data-chat-image-input hidden>
                </label>
                <button class="button button-dark" type="submit">Gửi trả lời <span aria-hidden="true">↗</span></button>
            </div>
            <p class="chat-image-preview" data-chat-image-preview hidden></p>
            @error('body')
                <p class="chat-form-error" role="alert">{{ $message }}</p>
            @enderror
            @error('image')
                <p class="chat-form-error" role="alert">{{ $message }}</p>
            @enderror
        </form>
    </section>
@endsection
