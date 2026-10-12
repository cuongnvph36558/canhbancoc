<?php

namespace App\Http\Controllers;

use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ProductImageController extends Controller
{
    public function __invoke(ProductImage $productImage): Response
    {
        $imagePath = $productImage->image_url;

        abort_unless(
            preg_match('/\Aproducts\/[A-Za-z0-9._-]+\.(?:jpe?g|png|webp)\z/i', $imagePath) === 1,
            404
        );

        $disk = Storage::disk('public');
        abort_unless($disk->exists($imagePath), 404);

        return $disk->response(
            $imagePath,
            basename($imagePath),
            [
                'Cache-Control' => 'public, max-age=86400',
                'X-Content-Type-Options' => 'nosniff',
            ],
            'inline'
        );
    }
}
