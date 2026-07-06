<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Konversi lokal
    |--------------------------------------------------------------------------
    |
    | Bila aktif, halaman /convert menampilkan form konversi di server memakai
    | CLI @playcanvas/splat-transform (butuh Node.js). Matikan bila hosting
    | tidak menyediakan Node — halaman akan tetap menampilkan alternatif
    | konversi eksternal via superspl.at/convert.
    |
    */

    'local_convert' => (bool) env('FEATURE_LOCAL_CONVERT', false),

    // Perintah CLI splat-transform. Bisa diganti mis. "splat-transform"
    // bila sudah di-install global (npm i -g @playcanvas/splat-transform).
    'splat_transform_command' => env('SPLAT_TRANSFORM_COMMAND', 'npx --yes @playcanvas/splat-transform'),

    // Batas ukuran file (MB)
    'convert_max_mb' => (int) env('CONVERT_MAX_MB', 200),
    'upload_max_mb' => (int) env('UPLOAD_MAX_MB', 500),

];
