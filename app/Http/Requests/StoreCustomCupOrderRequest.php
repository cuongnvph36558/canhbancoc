<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreCustomCupOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['required', 'string', 'min:8', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'shipping_province' => ['required', 'string', 'max:120'],
            'shipping_ward' => ['required', 'string', 'max:120'],
            'shipping_address' => ['required', 'string', 'max:500'],
            'cup_variant' => ['nullable', 'string'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
            'design' => ['nullable', 'array'],
            'design_preview' => ['nullable', 'string'],
            'artwork' => ['nullable', 'array'],
            'artwork.*' => ['file', 'image', 'max:5120'], // 5MB mỗi file
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $items = $this->input('design.items', []);

            if (! is_array($items)) {
                return;
            }

            $requiresArtwork = collect($items)->contains(function ($item): bool {
                return is_array($item) && ($item['type'] ?? null) === 'image';
            });

            if (! $requiresArtwork) {
                return;
            }

            $files = $this->file('artwork');

            if ($files === null || (is_array($files) && count(array_filter($files)) === 0)) {
                $validator->errors()->add(
                    'artwork',
                    'Bạn đã thêm lớp hình ảnh vào thiết kế nhưng chưa tải lên tệp nào. Vui lòng chọn ít nhất 1 ảnh.'
                );
            }
        });
    }

    protected function failedValidation(Validator $validator)
    {
        // Giữ nguyên hành vi mặc định của Laravel (redirect về URL trước đó kèm errors).
        parent::failedValidation($validator);
    }
}
