<?php

namespace App\Services;

use App\Models\CustomCupOrder;
use Illuminate\Support\Facades\Storage;

class CustomCupOrderPreview
{
    public function render(CustomCupOrder $order, ?int $widthMm = null, ?int $heightMm = null): string
    {
        $widthMm = $widthMm ?? 220;
        $heightMm = $heightMm ?? 90;
        $scaleX = $widthMm / 100;
        $scaleY = $heightMm / 100;

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$widthMm.' '.$heightMm.'" width="'.$widthMm.'mm" height="'.$heightMm.'mm">'
            .'<defs>'
            .'<clipPath id="print-area"><rect x="0" y="0" width="'.$widthMm.'" height="'.$heightMm.'"/></clipPath>'
            .'</defs>'
            .'<rect width="'.$widthMm.'" height="'.$heightMm.'" fill="#fff"/>'
            .'<g clip-path="url(#print-area)">'.$this->renderDesignItems($order, $scaleX, $scaleY).'</g>'
            .'</svg>';
    }

    private function renderDesignItems(CustomCupOrder $order, float $scaleX = 2.20, float $scaleY = 0.90): string
    {
        $layers = [];

        foreach ($order->design['items'] ?? [] as $item) {
            if (! is_array($item)) {
                continue;
            }

            $x = max(0, min(100, (float) ($item['x'] ?? 0))) * $scaleX;
            $y = max(0, min(100, (float) ($item['y'] ?? 0))) * $scaleY;
            $width = max(12, min(80, (float) ($item['width'] ?? 12))) * $scaleX;

            if (($item['type'] ?? null) === 'image') {
                $image = $this->resolveImageDataUri($item, $order);

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
            $fontSize = max(6, min($width * .52, 28)) * $fontScale;
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

    private function resolveImageDataUri(array $item, CustomCupOrder $order): ?string
    {
        $artworkPaths = $order->artwork_paths ?? ($order->artwork_path ? [$order->artwork_path] : []);

        $index = $item['artwork_index'] ?? $item['image_index'] ?? null;

        if ($index !== null) {
            $index = (int) $index;
            if (isset($artworkPaths[$index])) {
                return $this->artworkDataUri($artworkPaths[$index]);
            }
        }

        foreach ($artworkPaths as $path) {
            $uri = $this->artworkDataUri($path);
            if ($uri !== null) {
                return $uri;
            }
        }

        return null;
    }

    private function artworkDataUri(string $path): ?string
    {
        $disk = Storage::disk('local');

        if (! $disk->exists($path)) {
            return null;
        }

        $mimeType = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => null,
        };

        if ($mimeType === null) {
            return null;
        }

        return 'data:'.$mimeType.';base64,'.base64_encode($disk->get($path));
    }
}
