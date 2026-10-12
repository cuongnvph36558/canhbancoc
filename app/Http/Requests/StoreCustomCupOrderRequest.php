<?php

namespace App\Http\Requests;

use App\Services\CustomCupConfig;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCustomCupOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $variants = array_keys(app(CustomCupConfig::class)->all()['variants']);

        return [
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['required', 'string', 'min:8', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'shipping_province' => ['required', 'string', 'max:120'],
            'shipping_ward' => ['required', 'string', 'max:120'],
            'shipping_address' => ['required', 'string', 'max:500'],
            'cup_variant' => ['required', Rule::in($variants)],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'design' => ['required', 'array'],
            'design.items' => ['required', 'array', 'min:1', 'max:20'],
            'design.items.*.type' => ['required', Rule::in(['text', 'icon', 'image'])],
            'design_preview' => ['nullable', 'string', 'max:400000'],
            'design.items.*.content' => ['required', 'string', 'max:80'],
            'design.items.*.x' => ['required', 'numeric', 'min:0', 'max:100'],
            'design.items.*.y' => ['required', 'numeric', 'min:0', 'max:100'],
            'design.items.*.width' => ['required', 'numeric', 'min:12', 'max:80'],
            'design.items.*.font_size' => ['sometimes', 'numeric', 'min:50', 'max:200'],
            'artwork' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=4000,max_height=4000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $items = $this->input('design.items', []);
                $allowedIcons = app(CustomCupConfig::class)->all()['icons'];
                $containsArtwork = false;

                if (! is_array($items)) {
                    return;
                }

                foreach ($items as $index => $item) {
                    if (! is_array($item)) {
                        continue;
                    }

                    if (($item['type'] ?? null) === 'icon' && ! in_array($item['content'] ?? null, $allowedIcons, true)) {
                        $validator->errors()->add("design.items.{$index}.content", 'Biểu tượng này không được hỗ trợ.');
                    }

                    if (($item['type'] ?? null) === 'image') {
                        $containsArtwork = true;

                        if (($item['content'] ?? null) !== 'artwork') {
                            $validator->errors()->add("design.items.{$index}.content", 'Hình in được chọn không hợp lệ.');
                        }
                    }
                }

                if ($containsArtwork && ! $this->hasFile('artwork')) {
                    $validator->errors()->add('artwork', 'Vui lòng tải lên hình ảnh bạn muốn in trên cốc.');
                }
            },
        ];
    }
}
