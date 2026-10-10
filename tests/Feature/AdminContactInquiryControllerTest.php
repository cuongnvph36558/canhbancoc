<?php

namespace Tests\Feature;

use App\Models\ContactInquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminContactInquiryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admins_can_access_contact_inquiries(): void
    {
        $this->get(route('admin.contact-inquiries.index'))
            ->assertRedirect(route('login'));

        $client = User::factory()->create(['role' => 'client']);

        $this->actingAs($client)
            ->get(route('admin.contact-inquiries.index'))
            ->assertForbidden();
    }

    public function test_admin_can_filter_inquiries_and_view_escaped_details(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['role' => 'admin']);
        $inquiry = ContactInquiry::create([
            'name' => '<script>alert(1)</script>',
            'email' => 'customer@example.com',
            'subject' => 'Tư vấn sản phẩm',
            'message' => '<script>alert("xss")</script>',
        ]);
        ContactInquiry::create([
            'name' => 'Khách khác',
            'email' => 'other@example.com',
            'subject' => 'Lời nhắn đã đóng',
            'message' => 'Nội dung',
            'status' => ContactInquiry::STATUS_CLOSED,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.contact-inquiries.index', ['status' => ContactInquiry::STATUS_NEW]))
            ->assertOk()
            ->assertSee($inquiry->subject)
            ->assertDontSee('Lời nhắn đã đóng');

        $this->get(route('admin.contact-inquiries.show', $inquiry))
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('mailto:customer@example.com', false);
    }

    public function test_admin_can_update_inquiry_status_and_invalid_status_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $inquiry = ContactInquiry::create([
            'name' => 'Nguyễn An',
            'email' => 'an@example.com',
            'subject' => 'Hỏi về đơn hàng',
            'message' => 'Mong được hỗ trợ.',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.contact-inquiries.update-status', $inquiry), [
                'status' => ContactInquiry::STATUS_IN_PROGRESS,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(ContactInquiry::STATUS_IN_PROGRESS, $inquiry->fresh()->status);

        $this->patch(route('admin.contact-inquiries.update-status', $inquiry), [
            'status' => 'unexpected',
        ])->assertSessionHasErrors('status');

        $this->assertSame(ContactInquiry::STATUS_IN_PROGRESS, $inquiry->fresh()->status);
    }
}
