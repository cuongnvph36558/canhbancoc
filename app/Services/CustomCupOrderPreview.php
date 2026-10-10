<?php

namespace App\Services;

use App\Models\CustomCupOrder;
use Illuminate\Support\Facades\Storage;

class CustomCupOrderPreview
{
    public function render(CustomCupOrder $order): string
    {
        $colors = match ($order->cup_variant) {
            'pink' => ['handle' => '#e4a7b5', 'interior' => '#d990a1'],
            'white' => ['handle' => '#fdfdfb', 'interior' => '#ecebe7'],
            default => ['handle' => '#242322', 'interior' => '#171717'],
        };

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 650">'
            .'<defs>'
            .'<linearGradient id="body" x1="0" x2="1" y1="0" y2=".3">'
            .'<stop offset="0" stop-color="#d1d1d1"/><stop offset=".16" stop-color="#fbfbfb"/>'
            .'<stop offset=".78" stop-color="#fff"/><stop offset="1" stop-color="#d2d2d2"/>'
            .'</linearGradient>'
            .'<linearGradient id="handle" x1="0" x2="1">'
            .'<stop offset="0" stop-color="#d1d1d1"/>'
            .'<stop offset=".42" stop-color="'.$colors['handle'].'"/>'
            .'<stop offset="1" stop-color="#c7c7c7"/>'
            .'</linearGradient>'
            .'<clipPath id="print-area"><rect x="280" y="273" width="296" height="227"/></clipPath>'
            .'</defs>'
            .'<rect width="800" height="650" fill="#fff"/>'
            .'<ellipse cx="415" cy="596" rx="240" ry="28" fill="#2d2823" opacity=".12"/>'
            .'<path d="M607 251h47c91 0 100 216 0 216h-56v-47h52c45 0 42-122 0-122h-43z" fill="url(#handle)" stroke="#504e4c" stroke-opacity=".22" stroke-width="3"/>'
            .'<path d="M223 155h398l-37 378c-4 43-29 68-72 68H334c-43 0-68-25-72-68z" fill="url(#body)"/>'
            .'<ellipse cx="422" cy="156" rx="199" ry="31" fill="#fff"/>'
            .'<ellipse cx="422" cy="156" rx="185" ry="22" fill="'.$colors['interior'].'"/>'
            .'<path d="M272 198h19l35 340h-17z" fill="#fff" opacity=".38"/>'
            .'<g clip-path="url(#print-area)">'.$this->renderDesignItems($order).'</g>'
            .'</svg>';
    }

    private function renderDesignItems(CustomCupOrder $order): string
    {
        $layers = [];

        foreach ($order->design['items'] ?? [] as $item) {
            if (! is_array($item)) {
                continue;
            }

            $x = 280 + max(0, min(100, (float) ($item['x'] ?? 0))) * 2.96;
            $y = 273 + max(0, min(100, (float) ($item['y'] ?? 0))) * 2.27;
            $width = max(12, min(80, (float) ($item['width'] ?? 12))) * 2.96;

            if (($item['type'] ?? null) === 'image') {
                $image = $this->artworkDataUri($order);

                if ($image !== null) {
                    $layers[] = sprintf(
                        '<image x="%s" y="%s" width="%s" height="%s" preserveAspectRatio="xMidYMid meet" href="%s"/>',
                        $x,
                        $y,
                        $width,
                        $width,
                        $image,
                    );
                }

                continue;
            }

            $content = htmlspecialchars((string) ($item['content'] ?? ''), ENT_QUOTES | ENT_XML1, 'UTF-8');
            $fontScale = ($item['type'] ?? null) === 'text'
                ? max(50, min(200, (float) ($item['font_size'] ?? 100))) / 100
                : 1;
            $fontSize = max(18, min($width * .52, 72)) * $fontScale;
            $fontFamily = ($item['type'] ?? null) === 'icon' ? 'serif' : 'sans-serif';

            $layers[] = sprintf(
                '<text x="%s" y="%s" text-anchor="middle" dominant-baseline="middle" font-family="%s" font-size="%s" font-weight="700" fill="#24211f">%s</text>',
                $x + $width / 2,
                $y + $width / 2,
                $fontFamily,
                $fontSize,
                $content,
            );
        }

        return implode('', $layers);
    }

    private function artworkDataUri(CustomCupOrder $order): ?string
    {
        if ($order->artwork_path === null) {
            return null;
        }

        $disk = Storage::disk('local');

        if (! $disk->exists($order->artwork_path)) {
            return null;
        }

        $mimeType = match (strtolower(pathinfo($order->artwork_path, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => null,
        };

        if ($mimeType === null) {
            return null;
        }

        return 'data:'.$mimeType.';base64,'.base64_encode($disk->get($order->artwork_path));
    }
}
