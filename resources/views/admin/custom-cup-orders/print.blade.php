<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thiết kế cốc custom — {{ $order->order_number }}</title>
    <style>
        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: #f5f5f4;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #1c1917;
        }

        .page {
            padding: 24px 20px 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .toolbar h1 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #44403c;
        }

        .toolbar-actions {
            display: flex;
            gap: 8px;
        }

        .toolbar-actions button {
            padding: 9px 18px;
            border: 1px solid #d6d3d1;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            background: #fff;
            color: #1c1917;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .toolbar-actions button:hover { background: #fafaf9; }

        .toolbar-actions button.primary {
            background: #1c1917;
            color: #fafaf9;
            border-color: #1c1917;
        }

        .toolbar-actions button.primary:hover { background: #292524; }

        .stage {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .06);
        }

        .stage svg {
            display: block;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="toolbar">
            <h1>Hình in trên cốc — {{ $order->order_number }} (220mm × 90mm, tỉ lệ 1:1)</h1>
            <div class="toolbar-actions">
                <button type="button" id="downloadSvg" class="primary">⬇ Tải SVG</button>
                <button type="button" id="downloadPng">⬇ Tải PNG (4×)</button>
                <button type="button" onclick="window.close()">Đóng</button>
            </div>
        </div>

        <div class="stage" id="stage">
            {!! $previewSvg !!}
        </div>
    </div>

    <script>
        (function () {
            const svg = document.querySelector('#stage svg');
            if (!svg) return;

            // Chuẩn hoá XML để lưu file SVG độc lập.
            function serializeSvg() {
                const clone = svg.cloneNode(true);
                clone.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
                clone.setAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');
                const xml = new XMLSerializer().serializeToString(clone);
                return '<?xml version="1.0" encoding="UTF-8"?>\n' + xml;
            }

            function downloadBlob(blob, filename) {
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                a.remove();
                setTimeout(() => URL.revokeObjectURL(url), 1000);
            }

            document.getElementById('downloadSvg').addEventListener('click', function () {
                const blob = new Blob([serializeSvg()], { type: 'image/svg+xml;charset=utf-8' });
                downloadBlob(blob, @json($order->order_number) + '.svg');
            });

            document.getElementById('downloadPng').addEventListener('click', function () {
                const widthMm = {{ (int) $printAreaWidthMm }};
                const heightMm = {{ (int) $printAreaHeightMm }};
                const dpi = 4 * 96; // 4× mặc định để in nét.
                const widthPx = Math.round((widthMm / 25.4) * dpi);
                const heightPx = Math.round((heightMm / 25.4) * dpi);

                const svgString = serializeSvg();
                const blob = new Blob([svgString], { type: 'image/svg+xml;charset=utf-8' });
                const url = URL.createObjectURL(blob);

                const img = new Image();
                img.onload = function () {
                    const canvas = document.createElement('canvas');
                    canvas.width = widthPx;
                    canvas.height = heightPx;
                    const ctx = canvas.getContext('2d');
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, widthPx, heightPx);
                    ctx.drawImage(img, 0, 0, widthPx, heightPx);
                    URL.revokeObjectURL(url);

                    canvas.toBlob(function (pngBlob) {
                        if (pngBlob) {
                            downloadBlob(pngBlob, @json($order->order_number) + '-4x.png');
                        }
                    }, 'image/png');
                };
                img.onerror = function () {
                    URL.revokeObjectURL(url);
                    alert('Không thể xuất PNG. Vui lòng dùng SVG.');
                };
                img.src = url;
            });
        })();
    </script>
</body>
</html>
