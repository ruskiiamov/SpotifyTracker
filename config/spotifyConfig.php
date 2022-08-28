<?php

return [
    'requestRateLimitAttempts' => 100,
    'requestRateLimitDecay' => 10, //seconds

    'releaseAge' => 14, //days
    'checkAge' => 6, //hours

    'pagination' => 20,

    'genreCategories' => [
        //genre category => key words
        'Electronic' => ['trance', 'edm', 'dance', 'house', 'lounge', 'chillout', 'techno', 'drum and bass', 'dnb',
            'ambient', 'synth', 'tronica', 'electro', 'amapiano', 'chill', 'beat', 'clubbing', 'rave'],
        'Hip hop' => ['hip hop', 'rap', 'phonk', 'drain', 'drill', 'urbano', 'psychokore', 'lo-fi', 'zxc'],
        'Pop' => ['pop', 'singer-songwriter', 'j-pixie', 'j-acoustic', 'anime', 'schlager', 'chanson', 'contemporanea',
            'manele', 'cantautora'],
        'Jazz' => ['jazz'],
        'Blues' => ['blues'],
        'Funk' => ['funk'],
        'Soul/R&B' => ['soul', 'r&b'],
        'Classical' => ['classical', 'orchestra'],
        'Folk/Country' => ['folk', 'country', 'bluegrass', 'roots', 'americana'],
        'Reggae/Ska' => ['reggae', 'ska'],
        'Latin/Afro-Cuban' => ['latin', 'axe', 'forro', 'mpb', 'cuarteto', 'pagode', 'samba', 'mexicana', 'ranchera',
            'rumba', 'grupera', 'corrido', 'sungura', 'perreo', 'cumbia', 'tango'],
        'World' => ['indigenous', 'bhajan', 'ghazal', 'enka'],
        'Kids' => ['children', 'detskie', 'para ninos', 'cartoon', 'kindermusik', 'bornesange', 'kinderliedje',
            'bambini', 'kodomo', 'cocuk', 'infantil'],
        'Punk' => ['punk', 'straight edge', 'screamo'],
        'Metal' => ['metal', 'djent', 'ukhc'],
        'Rock' => ['rock', 'indie', 'garage', 'surf'],
        'Other' => ['other'],
    ],

    //if artist has only banned genres it won't get to the DB
    'bannedGenreNames' => ['sleep', 'white noise', 'rain', 'world meditation', 'russian chanson', 'kleine hoerspiel',
        'hoerspiel', 'writing', 'musica de fondo', 'shush', 'british soundtrack', 'epicore', 'orchestral soundtrack',
        'soundtrack', 'video game music', 'anime score', 'japanese soundtrack', 'ocean', 'kabarett', 'environmental',
        'genshin', 'asmr', 'sped up', 'pet calming', 'workout product', 'lullaby', 'water', 'bgm', 'neru', 'vocaloid',
        'japanese instrumental', 'piano cover', 'mollywood', 'sandalwood', 'spa', 'sound', 'yoga', 'pianissimo',
        'icelandic experimental', 'japanese guitar', 'japanese vgm', 'instrumental worship', 'piano worship', 'reiki',
        'german soundtrack', 'brain waves', 'korean instrumental', 'dinner jazz', 'music box', 'slowed and reverb',
        'binaural', 'bornehistorier', 'jirai kei', 'erotic product', 'puirt-a-beul', 'massage', 'zen', 'mindfulness',
        'classic bollywood', 'filmi', 'modern bollywood', 'meditation'],

    //banned words in album names
    'exceptions' => ['live', 'remix', 'anniversary', 'deluxe', 'expanded', 'instrumentals', 'best', 'soundtrack',
        'demos', 'edition', 'remastered'],

    'artistIdExceptions' => [
        '5aIqB5nVVvmFsvSdExz408', //Johann Sebastian Bach
        '4NJhFmfw43RLBLjQvxDuRS', //Wolfgang Amadeus Mozart
        '7y97mc3bZRFXzT2szRM4L4', //Frédéric Chopin
        '2wOqMjp9TyABvtHdOSOTUS', //Ludwig van Beethoven
        '1Uff91EOsvd99rtAupatMP', //Claude Debussy
        '3MKCzCnpzw3TjUYs2v7vDA', //Pyotr Ilyich Tchaikovsky
        '0Kekt6CKSo0m5mivKcoH51', //Sergei Rachmaninoff
        '2QOIawHpSlOwXDvSqQ9YJR', //Antonio Vivaldi
        '1RdlqiArFMbLBLQTPg3EGW', //Java Jazz Cafe
        '099Fz1rpYJ7sZxdyXzIf6s', //Java Jazz Cafe
        '2p0UyoPfYfI76PCStuXfOP', //Franz Schubert
        '5wTAi7QkpP6kp8a54lmTOq', //Johannes Brahms
        '2hHUcumhJFUHQKg5h3jI1Y', //Jazz Lounge Bar
        '3jmd2RL8vGnluearHualn2', //Coffee Shop Jazz Piano Chilling
        '21p1cEg5BT8TCSYIlV3k7M', //Easy Listening Background Music
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
