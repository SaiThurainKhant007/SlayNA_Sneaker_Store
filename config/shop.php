<?php

/*
| The store's public contact details, shown in the site footer and on the Contact page.
| Change them here, or override any of them in .env (SHOP_EMAIL, SHOP_PHONE, SHOP_ADDRESS, SHOP_HOURS).
*/

return [
    'address' => env('SHOP_ADDRESS', 'Yangon, Myanmar'),
    'phone' => env('SHOP_PHONE', '09788985027'),
    'email' => env('SHOP_EMAIL', 'info@slayna.com'),
    'hours' => env('SHOP_HOURS', 'Mon – Sat, 9:00 AM – 6:00 PM'),

    // Weekly opening hours for the Contact page schedule (24h "HH:MM"; null = closed).
    // Keep in step with 'hours' above, which is the one-line version used in the footer.
    'week' => [
        'Monday' => ['09:00', '18:00'],
        'Tuesday' => ['09:00', '18:00'],
        'Wednesday' => ['09:00', '18:00'],
        'Thursday' => ['09:00', '18:00'],
        'Friday' => ['09:00', '18:00'],
        'Saturday' => ['09:00', '18:00'],
        'Sunday' => null,
    ],

    // Social media links in the footer (icon = Bootstrap Icons name).
    // PLACEHOLDERS: replace each url with the store's real page, here or in .env (SHOP_FACEBOOK_URL etc.).
    'social' => [
        ['name' => 'Facebook', 'icon' => 'facebook', 'url' => env('SHOP_FACEBOOK_URL', 'https://www.facebook.com/')],
        ['name' => 'Instagram', 'icon' => 'instagram', 'url' => env('SHOP_INSTAGRAM_URL', 'https://www.instagram.com/')],
        ['name' => 'TikTok', 'icon' => 'tiktok', 'url' => env('SHOP_TIKTOK_URL', 'https://www.tiktok.com/')],
        ['name' => 'WeChat', 'icon' => 'wechat', 'url' => env('SHOP_WECHAT_URL', 'https://www.wechat.com/')],
        ['name' => 'Telegram', 'icon' => 'telegram', 'url' => env('SHOP_TELEGRAM_URL', 'https://t.me/')],
    ],

    // Map pin for the Contact page (search text shown by Google Maps)
    'map_query' => env('SHOP_MAP_QUERY', 'Yangon, Myanmar'),

    // Brand video ads shown in the home page slider (YouTube video ids).
    // 'credit' names the channel that published the video (fan-made "spec" ads are not official brand ads).
    'videos' => [
        ['id' => 'TYwrNRF9u-M', 'brand' => 'Converse', 'title' => 'Make. Move. Repeat.', 'credit' => 'Converse'],
        ['id' => '864B4rzoPog', 'brand' => 'adidas', 'title' => 'Shoe Commercial Concept', 'credit' => 'Concept film by Tobias Schäll'],
        ['id' => 'freRkr00otY', 'brand' => 'Nike', 'title' => 'Just Do It', 'credit' => 'Spec ad by Tyler Vandervort (VV Media)'],
        ['id' => '7V07PFYDonM', 'brand' => 'New Balance', 'title' => 'We Got Now', 'credit' => 'New Balance · 2026'],
    ],

    // Sneaker Showcase on the home page (below Newest Arrivals): a cut-out photo in public/img/showcase,
    // linked to the active product with this exact name. Entries whose product is missing or inactive are skipped.
    'showcase' => [
        ['image' => 'img/showcase/skechers-dlites.png', 'product' => "D'Lites"],
        ['image' => 'img/showcase/nike-air-force-1-07.png', 'product' => "Air Force 1 '07"],
        ['image' => 'img/showcase/converse-chuck-taylor-hi.png', 'product' => 'Chuck Taylor All Star Hi'],
        ['image' => 'img/showcase/puma-speedcat-og.png', 'product' => 'Speedcat OG'],
        ['image' => 'img/showcase/new-balance-9060.png', 'product' => '9060'],
        ['image' => 'img/showcase/balenciaga-runner.png', 'product' => 'Runner'],
        ['image' => 'img/showcase/converse-chuck-70-shenron.png', 'product' => 'Chuck 70 Hi Shenron'],
        ['image' => 'img/showcase/converse-chuck-taylor-vegeta.png', 'product' => 'Chuck Taylor All Star Hi Vegeta'],
        ['image' => 'img/showcase/jordan-air-jordan-3-retro.png', 'product' => 'Air Jordan 3 Retro'],
        ['image' => 'img/showcase/nike-air-monarch-iv.png', 'product' => 'Air Monarch IV'],
    ],
];
