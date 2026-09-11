<?php
if (!function_exists('to_base64_image')) {
    function to_base64_image(?string $relativePath): ?string
    {
        if (!$relativePath) {
            return null;
        }

        $fullPath = storage_path('app/public/' . $relativePath);

        if (!is_file($fullPath)) {
            return null;
        }

        $mime = mime_content_type($fullPath) ?: 'image/jpeg';

        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fullPath));
    }
}
