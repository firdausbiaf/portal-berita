<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Site Name & Description
    |--------------------------------------------------------------------------
    |
    | Used across the public portal for header branding, SEO title suffixes,
    | default meta descriptions, and OpenGraph/Twitter card tags.
    |
    */
    'name' => env('SITE_NAME', env('APP_NAME', 'Portal Berita')),

    'description' => env(
        'SITE_DESCRIPTION',
        'Portal berita independen, cerdas, dan terpercaya menyajikan kabar terkini, mendalam, dan berimbang dari seluruh nusantara.'
    ),

    /*
    |--------------------------------------------------------------------------
    | Display Timezone
    |--------------------------------------------------------------------------
    |
    | Public portal date and time representations are converted to this
    | timezone before formatting and applying the WIB label.
    |
    */
    'display_timezone' => env('DISPLAY_TIMEZONE', 'Asia/Jakarta'),
];
