<?php

namespace Tests\Feature;

use App\Events\SupportMessageCreated;
use App\Models\SupportConversation;
use App\Models\User;
use Illuminate\Broadcasting\BroadcastEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SupportChatControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_before_opening_chat(): void
    {
        $this->get(route('chat.index'))
            ->assertRedirect(route('login'));

        $this->post(route('chat.messages.store'), ['body' => 'Xin chào'])
            ->assertRedirect(route('login'));
    }

    public function test_customer_can_send_and_poll_messages_in_their_conversation(): void
    {
        $this->withoutVite();
        $customer = User::factory()->create(['role' => 'client']);
        Queue::fake();

        $this->actingAs($customer)
            ->post(route('chat.messages.store'), ['body' => 'Mình cần tư vấn một chiếc cốc.'])
            ->assertRedirect(route('chat.index'));

        $conversation = SupportConversation::whereBelongsTo($customer)->firstOrFail();
        Queue::assertPushed(
            BroadcastEvent::class,
            fn (BroadcastEvent $job): bool => $job->event instanceof SupportMessageCreated
                && $job->event->message->body === 'Mình cần tư vấn một chiếc cốc.'
                && $job->event->customerId === $customer->id
                && $job->event->url === route('admin.chat.show', $conversation),
        );
        $this->assertDatabaseHas('support_messages', [
            'support_conversation_id' => $conversation->id,
            'sender_id' => $customer->id,
            'sender_role' => 'client',
            'body' => 'Mình cần tư vấn một chiếc cốc.',
        ]);
        $this->assertNotNull($conversation->fresh()->last_message_at);

        $this->get(route('chat.index'))
            ->assertOk()
            ->assertSee('Mình cần tư vấn một chiếc cốc.');

        $this->getJson(route('chat.messages.index', ['after' => 0]))
            ->assertOk()
            ->assertJsonPath('messages.0.body', 'Mình cần tư vấn một chiếc cốc.')
            ->assertJsonPath('messages.0.sender_role', 'client');
    }

    public function test_customer_cannot_receive_another_customers_messages(): void
    {
        $customer = User::factory()->create(['role' => 'client']);
        $otherCustomer = User::factory()->create(['role' => 'client']);
        $conversation = SupportConversation::create(['user_id' => $otherCustomer->id]);
        $conversation->messages()->create([
            'sender_id' => $otherCustomer->id,
            'sender_role' => 'client',
            'body' => 'Tin nhắn riêng tư của khách khác.',
        ]);

        $this->actingAs($customer)
            ->getJson(route('chat.messages.index'))
            ->assertOk()
            ->assertExactJson(['messages' => []]);
    }

    public function test_private_realtime_channels_restrict_messages_to_the_customer_and_admins(): void
    {
        $customer = User::factory()->create(['role' => 'client']);
        $otherCustomer = User::factory()->create(['role' => 'client']);
        $admin = User::factory()->create(['role' => 'admin']);
        $inactiveAdmin = User::factory()->create(['role' => 'admin', 'is_active' => false]);
        $conversation = SupportConversation::create(['user_id' => $customer->id]);
        $channels = Broadcast::connection('null')->getChannels();
        $conversationChannel = $channels->get('support-conversation.{conversationId}');
        $adminChannel = $channels->get('admins');

        $this->assertTrue($conversationChannel($customer, (string) $conversation->id));
        $this->assertFalse($conversationChannel($otherCustomer, (string) $conversation->id));
        $this->assertTrue($conversationChannel($admin, (string) $conversation->id));
        $this->assertTrue($adminChannel($admin));
        $this->assertFalse($adminChannel($customer));
        $this->assertFalse($adminChannel($inactiveAdmin));
        $this->assertFalse($conversationChannel(null, (string) $conversation->id));
    }

    public function test_admin_can_reply_and_only_admins_can_open_the_support_inbox(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'client']);
        $conversation = SupportConversation::create(['user_id' => $customer->id]);
        $conversation->messages()->create([
            'sender_id' => $customer->id,
            'sender_role' => 'client',
            'body' => 'Mình cần hỗ trợ.',
        ]);
        $conversation->update(['last_message_at' => now()]);

        $this->actingAs($admin)
            ->get(route('admin.chat.index'))
            ->assertOk()
            ->assertSee($customer->email);

        $this->get(route('admin.chat.show', $conversation))
            ->assertOk()
            ->assertSee('Mình cần hỗ trợ.');

        $this->post(route('admin.chat.messages.store', $conversation), [
            'body' => 'Cốc Mơ sẵn sàng hỗ trợ bạn.',
        ])->assertRedirect(route('admin.chat.show', $conversation));

        $this->assertDatabaseHas('support_messages', [
            'support_conversation_id' => $conversation->id,
            'sender_id' => $admin->id,
            'sender_role' => 'admin',
            'body' => 'Cốc Mơ sẵn sàng hỗ trợ bạn.',
        ]);

        $this->actingAs($customer)
            ->get(route('admin.chat.index'))
            ->assertForbidden();

        $this->getJson(route('chat.messages.index', ['after' => 1]))
            ->assertOk()
            ->assertJsonPath('messages.0.body', 'Cốc Mơ sẵn sàng hỗ trợ bạn.');
    }

    public function test_chat_rejects_empty_messages_without_creating_a_conversation(): void
    {
        $customer = User::factory()->create(['role' => 'client']);

        $this->actingAs($customer)
            ->from(route('chat.index'))
            ->post(route('chat.messages.store'), ['body' => ''])
            ->assertRedirect(route('chat.index'))
            ->assertSessionHasErrors('body');

        $this->assertDatabaseCount('support_conversations', 0);
        $this->assertDatabaseCount('support_messages', 0);
    }
}
