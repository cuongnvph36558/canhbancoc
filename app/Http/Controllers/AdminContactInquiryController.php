<?php

namespace App\Http\Controllers;

use App\Models\ContactInquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminContactInquiryController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:new,in_progress,closed'],
        ]);

        $inquiries = ContactInquiry::query()
            ->when(
                $validated['status'] ?? null,
                fn ($query, string $status) => $query->where('status', $status),
            )
            ->latest()
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.contact-inquiries.index', [
            'inquiries' => $inquiries,
            'selectedStatus' => $validated['status'] ?? '',
            'statusLabels' => [
                ContactInquiry::STATUS_NEW => 'Mới',
                ContactInquiry::STATUS_IN_PROGRESS => 'Đang xử lý',
                ContactInquiry::STATUS_CLOSED => 'Đã đóng',
            ],
            'newCount' => ContactInquiry::where('status', ContactInquiry::STATUS_NEW)->count(),
            'inProgressCount' => ContactInquiry::where('status', ContactInquiry::STATUS_IN_PROGRESS)->count(),
            'closedCount' => ContactInquiry::where('status', ContactInquiry::STATUS_CLOSED)->count(),
        ]);
    }

    public function show(ContactInquiry $inquiry): View
    {
        return view('admin.contact-inquiries.show', [
            'inquiry' => $inquiry,
            'statusLabels' => [
                ContactInquiry::STATUS_NEW => 'Mới',
                ContactInquiry::STATUS_IN_PROGRESS => 'Đang xử lý',
                ContactInquiry::STATUS_CLOSED => 'Đã đóng',
            ],
        ]);
    }

    public function updateStatus(Request $request, ContactInquiry $inquiry): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:new,in_progress,closed'],
        ]);

        $inquiry->update($validated);

        return back()->with('success', 'Trạng thái lời nhắn đã được cập nhật.');
    }
}
