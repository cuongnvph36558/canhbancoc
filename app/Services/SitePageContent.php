<?php

namespace App\Services;

use App\Models\SitePage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SitePageContent
{
    /**
     * @return array<string, mixed>
     */
    public function definition(string $slug): array
    {
        $definition = config('pages.'.$slug);

        abort_unless(is_array($definition), 404);

        return $definition;
    }

    /**
     * @return array<string, array{label: string, default: string, type?: string, max?: int}>
     */
    public function fields(string $slug): array
    {
        $fields = [];

        foreach ($this->definition($slug)['sections'] as $section) {
            $fields = array_merge($fields, $section['fields']);
        }

        return $fields;
    }

    /**
     * @return array<string, string>
     */
    public function get(string $slug): array
    {
        $defaults = [];

        foreach ($this->fields($slug) as $key => $field) {
            $defaults[$key] = $field['default'];
        }

        $page = SitePage::query()->where('slug', $slug)->first();
        $content = array_replace($defaults, $page?->content ?? []);

        foreach ($this->fields($slug) as $key => $field) {
            if (($field['type'] ?? 'text') === 'image' && Str::startsWith($content[$key], 'pages/')) {
                $content[$key] = Storage::disk('public')->url($content[$key]);
            }
        }

        return $content;
    }

    /**
     * @param  array<string, string>  $content
     * @return array<string, string>
     */
    public function save(string $slug, array $content): array
    {
        $page = SitePage::query()->firstOrNew(['slug' => $slug]);
        $previousContent = $page->content ?? [];
        $page->content = array_replace(
            $previousContent,
            array_intersect_key($content, $this->fields($slug)),
        );
        $page->save();

        return $previousContent;
    }
}
