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
];
