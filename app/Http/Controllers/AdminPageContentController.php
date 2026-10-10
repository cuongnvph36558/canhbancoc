<?php

namespace App\Http\Controllers;

use App\Services\SitePageContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class AdminPageContentController extends Controller
{
    public function edit(string $page, SitePageContent $sitePageContent): View
    {
        return view('admin.pages.edit', [
            'page' => $page,
            'definition' => $sitePageContent->definition($page),
            'content' => $sitePageContent->get($page),
        ]);
    }

    public function update(Request $request, string $page, SitePageContent $sitePageContent): RedirectResponse
    {
        $fields = $sitePageContent->fields($page);
        $rules = [
            'content' => ['required', 'array:'.implode(',', array_keys($fields))],
        ];

        foreach ($fields as $key => $field) {
            if (($field['type'] ?? 'text') === 'image') {
                $rules['content.'.$key] = ['sometimes', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];

                continue;
            }

            $rules['content.'.$key] = ['required', 'string'];

            $rules['content.'.$key][] = 'max:'.($field['max'] ?? 5000);
        }

        $validated = $request->validate($rules);
        $content = $validated['content'];
        $uploadedPaths = [];

        foreach ($fields as $key => $field) {
            if (($field['type'] ?? 'text') !== 'image') {
                continue;
            }

            unset($content[$key]);

            $image = $request->file('content.'.$key);

            if ($image === null) {
                continue;
            }

            $path = $image->store('pages/'.$page, 'public');

            if (! is_string($path)) {
                $this->deleteUploadedImages($uploadedPaths);

                throw new RuntimeException('Không thể lưu ảnh trang.');
            }

            $content[$key] = $path;
            $uploadedPaths[] = $path;
        }

        try {
            $previousContent = $sitePageContent->save($page, $content);
        } catch (Throwable $exception) {
            $this->deleteUploadedImages($uploadedPaths);

            throw $exception;
        }

        $replacedPaths = [];

        foreach ($fields as $key => $field) {
            if (
                ($field['type'] ?? 'text') === 'image'
                && isset($content[$key], $previousContent[$key])
                && $content[$key] !== $previousContent[$key]
                && str_starts_with($previousContent[$key], 'pages/'.$page.'/')
            ) {
                $replacedPaths[] = $previousContent[$key];
            }
        }

        $this->deleteUploadedImages($replacedPaths);

        return to_route('admin.pages.edit', $page)
            ->with('success', 'Nội dung trang đã được cập nhật.');
    }

    /**
     * @param  array<int, string>  $paths
     */
    private function deleteUploadedImages(array $paths): void
    {
        if ($paths !== [] && ! Storage::disk('public')->delete($paths)) {
            throw new RuntimeException('Không thể xóa ảnh cũ khỏi bộ nhớ.');
        }
    }
}
