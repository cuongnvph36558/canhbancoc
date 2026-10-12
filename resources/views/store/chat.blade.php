@extends('layouts.store')

@section('title', 'Tin nhắn với Cốc Mơ')
@section('description', 'Trò chuyện trực tiếp với đội ngũ Cốc Mơ Studio.')

@section('content')
    <section class="container chat-page">
        <div class="chat-heading">
            <span class="eyebrow">CỐC MƠ LUÔN Ở ĐÂY</span>
            <h1>Trò chuyện với <em>Cốc Mơ</em></h1>
            <p>Gửi câu hỏi của bạn, đội ngũ admin sẽ phản hồi sớm nhất có thể.</p>
        </div>

        @if (session('success'))
            <div class="form-success" role="status">{{ session('success') }}</div>
        @endif

        <section
            class="chat-panel"
            data-support-chat
            data-poll-url="{{ $pollUrl }}"
            data-current-user-id="{{ $currentUserId }}"
            data-support-conversation-id="{{ $conversation?->id ?? '' }}"
            data-viewer-role="{{ auth()->user()->isAdmin() ? 'admin' : 'client' }}"
            data-last-message-id="{{ $messages->last()?->id ?? 0 }}"
            aria-label="Cuộc trò chuyện với Cốc Mơ"
        >
            <div class="chat-panel-heading">
                <span class="chat-avatar" aria-hidden="true">M</span>
                <div>
                    <strong>Cốc Mơ Studio</strong>
                    <span>Admin hỗ trợ khách hàng</span>
                </div>
            </div>
            <div class="chat-transcript" data-chat-messages aria-live="polite" aria-relevant="additions">
                @forelse ($messages as $message)
                    <article
                        class="chat-message {{ $message->sender_id === $currentUserId ? 'is-own' : '' }}"
                        data-chat-message-id="{{ $message->id }}"
                    >
                        <span class="chat-message-sender">{{ $message->sender_id === $currentUserId ? 'Bạn' : ($message->sender_role === 'admin' ? 'Cốc Mơ' : 'Bạn') }}</span>
                        @if ($message->image_path)
                            <p class="chat-message-image">
                                <img src="{{ Storage::disk('public')->url($message->image_path) }}" alt="Hình ảnh trong tin nhắn" loading="lazy">
                            </p>
                        @endif
                        @if ($message->body)
                            <p>{{ $message->body }}</p>
                        @endif
                        <time>{{ $message->created_at->format('H:i d/m/Y') }}</time>
                    </article>
                @empty
                    <p class="chat-empty" data-chat-empty>Chào bạn! Hãy gửi lời nhắn đầu tiên để bắt đầu trò chuyện nhé.</p>
                @endforelse
            </div>
            <p class="chat-status" data-chat-status role="status" aria-live="polite">Tin nhắn cập nhật tức thì khi có phản hồi.</p>
            <form class="chat-composer" method="POST" action="{{ route('chat.messages.store') }}" enctype="multipart/form-data">
                @csrf
                <label class="sr-only" for="chat-message">Nội dung tin nhắn</label>
                <textarea id="chat-message" name="body" rows="2" maxlength="2000" placeholder="Nhập tin nhắn của bạn hoặc gửi ảnh...">{{ old('body') }}</textarea>
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
                    <button class="button button-dark" type="submit">Gửi <span aria-hidden="true">↗</span></button>
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
    </section>
@endsection
