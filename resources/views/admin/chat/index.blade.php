@extends('layouts.admin')

@section('title', 'Messenger')

@section('content')
    <div class="admin-page-heading">
        <div>
            <span class="admin-page-eyebrow">CHĂM SÓC KHÁCH HÀNG</span>
            <h1>Messenger</h1>
            <p>Các cuộc trò chuyện trực tiếp với khách hàng.</p>
        </div>
    </div>

    <section class="admin-products-panel" aria-labelledby="chat-inbox-heading">
        <div class="admin-panel-heading">
            <div>
                <h2 id="chat-inbox-heading">Hội thoại gần đây</h2>
                <p>Chọn một khách hàng để xem và trả lời tin nhắn.</p>
            </div>
        </div>
        <div class="chat-inbox-list">
            @forelse ($conversations as $conversation)
                <a class="chat-inbox-item" href="{{ route('admin.chat.show', $conversation) }}">
                    <span class="chat-avatar" aria-hidden="true">{{ mb_substr($conversation->user->name, 0, 1) }}</span>
                    <span class="chat-inbox-copy">
                        <strong>{{ $conversation->user->name }}</strong>
                        <small>{{ $conversation->user->email }}</small>
                        <span>{{ \Illuminate\Support\Str::limit($conversation->latestMessage?->body ?? '', 100) }}</span>
                    </span>
                    <time>{{ $conversation->last_message_at?->format('H:i d/m/Y') }}</time>
                </a>
            @empty
                <p class="admin-empty-state">Chưa có cuộc trò chuyện nào. Khách hàng có thể bắt đầu chat sau khi đăng nhập.</p>
            @endforelse
        </div>
        <div class="admin-pagination">{{ $conversations->links() }}</div>
    </section>
@endsection
