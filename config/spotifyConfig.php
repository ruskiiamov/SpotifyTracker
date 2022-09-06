<?php

return [
    'requestRateLimitAttempts' => 100,
    'requestRateLimitDecay' => 10, //seconds

    'getSeveralAlbumsLimit' => 20,
    'getSeveralArtistsLimit' => 50,

    'releaseAge' => 14, //days
    'checkAge' => 6, //hours

    'pagination' => 20,

    //banned words in album names
    'exceptions' => ['live', 'remix', 'anniversary', 'deluxe', 'expanded', 'instrumentals', 'best', 'soundtrack',
        'demos', 'edition', 'remastered'],

    'artistIdExceptions' => [
//        '5aIqB5nVVvmFsvSdExz408', //Johann Sebastian Bach
//        '4NJhFmfw43RLBLjQvxDuRS', //Wolfgang Amadeus Mozart
//        '7y97mc3bZRFXzT2szRM4L4', //Frédéric Chopin
//        '2wOqMjp9TyABvtHdOSOTUS', //Ludwig van Beethoven
//        '1Uff91EOsvd99rtAupatMP', //Claude Debussy
//        '3MKCzCnpzw3TjUYs2v7vDA', //Pyotr Ilyich Tchaikovsky
//        '0Kekt6CKSo0m5mivKcoH51', //Sergei Rachmaninoff
//        '2QOIawHpSlOwXDvSqQ9YJR', //Antonio Vivaldi
        '1RdlqiArFMbLBLQTPg3EGW', //Java Jazz Cafe
        '099Fz1rpYJ7sZxdyXzIf6s', //Java Jazz Cafe
//        '2p0UyoPfYfI76PCStuXfOP', //Franz Schubert
//        '5wTAi7QkpP6kp8a54lmTOq', //Johannes Brahms
        '2hHUcumhJFUHQKg5h3jI1Y', //Jazz Lounge Bar
        '3jmd2RL8vGnluearHualn2', //Coffee Shop Jazz Piano Chilling
        '21p1cEg5BT8TCSYIlV3k7M', //Easy Listening Background Music
//        '5ihY290YPGc3aY2xTyx7Gy', //Edvard Grieg
        '7iUy9ctasMRtp5jwWs95Fm', //Lo-Fi Beats
        '6iGoMyoSIjyTxbCRyHREtI', //Sol y Lluvia
        '10gzBoINW3cLJfZUka8Zoe', //Above & Beyond
    ],

    'markets' => [
        'AL', 'AM', 'AR', 'AT', 'AU', 'AZ',
        'BA', 'BE', 'BG', 'BR', 'BY',
        'CA', 'CH', 'CL', 'CY', 'CZ',
        'DE', 'DK',
        'EE', 'EG', 'ES',
        'FI', 'FR',
        'GB', 'GE', 'GR',
        'HK', 'HU',
        'IE', 'IL', 'IN', 'IS', 'IT',
        'JM', 'JP',
        'KZ', 'KR', 'KG',
        'LI', 'LV', 'LT', 'LU',
        'MT', 'MX', 'MD', 'MC', 'MN',
        'NO', 'NP', 'NL', 'NZ',
        'PK', 'PE', 'PL', 'PT',
        'RU', 'RO', 'RS',
        'SE', 'SG', 'SK', 'SI',
        'TJ', 'TR',
        'UA', 'US', 'UZ',
        'VN',
        'ZA'
    ],

    'default_market' => 'US',
];
