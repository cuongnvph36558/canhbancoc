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
                        <p>{{ $message->body }}</p>
                        <time>{{ $message->created_at->format('H:i d/m/Y') }}</time>
                    </article>
                @empty
                    <p class="chat-empty" data-chat-empty>Chào bạn! Hãy gửi lời nhắn đầu tiên để bắt đầu trò chuyện nhé.</p>
                @endforelse
            </div>
            <p class="chat-status" data-chat-status role="status" aria-live="polite">Tin nhắn cập nhật tức thì khi có phản hồi.</p>
            <form class="chat-composer" method="POST" action="{{ route('chat.messages.store') }}">
                @csrf
                <label class="sr-only" for="chat-message">Nội dung tin nhắn</label>
                <textarea id="chat-message" name="body" rows="2" required maxlength="2000" placeholder="Nhập tin nhắn của bạn...">{{ old('body') }}</textarea>
                <button class="button button-dark" type="submit">Gửi tin nhắn <span aria-hidden="true">↗</span></button>
                @error('body')
                    <p class="chat-form-error" role="alert">{{ $message }}</p>
                @enderror
            </form>
        </section>
    </section>
@endsection
