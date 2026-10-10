<?php

use App\Models\SupportConversation;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function (?User $user, string $id): bool {
    return $user !== null
        && $user->is_active
        && (int) $user->id === (int) $id;
});

Broadcast::channel('admins', function (?User $user): bool {
    return $user !== null && $user->is_active && $user->isAdmin();
});

Broadcast::channel('support-conversation.{conversationId}', function (?User $user, string $conversationId): bool {
    return $user !== null && $user->is_active && ($user->isAdmin()
        || SupportConversation::query()
            ->whereKey($conversationId)
            ->where('user_id', $user->id)
            ->exists());
});

Broadcast::channel('storefront', function () {
    return true;
});
