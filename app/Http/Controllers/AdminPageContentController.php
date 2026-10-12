<?php

namespace App\Http\Controllers;

use App\Services\SitePageContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
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

            $directory = public_path('uploads/pages/'.$page);
            File::ensureDirectoryExists($directory);

            $filename = $image->hashName();
            $image->move($directory, $filename);

            $url = asset('uploads/pages/'.$page.'/'.$filename);
            $content[$key] = $url;
            $uploadedPaths[] = $url;
        }

        try {
            $previousContent = $sitePageContent->save($page, $content);
        } catch (Throwable $exception) {
            $this->deleteUploadedImages($uploadedPaths);

            throw $exception;
        }

        $this->deleteUploadedImages($previousContent, $page);

        return to_route('admin.pages.edit', $page)
            ->with('success', 'Nội dung trang đã được cập nhật.');
    }

    /**
     * @param  array<int, string>  $paths
     */
    private function deleteUploadedImages(array $paths, string $page): void
    {
        foreach ($paths as $path) {
            if (str_starts_with($path, 'http')) {
                $relativePath = parse_url($path, PHP_URL_PATH);
                $filePath = public_path(ltrim($relativePath, '/'));

                if (File::exists($filePath)) {
                    File::delete($filePath);
                }
            }
        }
    }
}
