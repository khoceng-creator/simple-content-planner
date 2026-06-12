<?php

return [
    'logo_max_dimension' => (int) env('MEDIA_LOGO_MAX_DIMENSION', 512),
    'image_max_dimension' => (int) env('MEDIA_IMAGE_MAX_DIMENSION', 1920),
    'webp_quality' => (int) env('MEDIA_WEBP_QUALITY', 82),
    'browser_cache_seconds' => (int) env('MEDIA_BROWSER_CACHE_SECONDS', 31536000),
];
