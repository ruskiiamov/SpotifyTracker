<?php

return [
    'client_id' => env('SPOTIFY_CLIENT_ID'),
    'client_secret' => env('SPOTIFY_CLIENT_SECRET'),

    'requestRateLimitAttempts' => 100,
    'requestRateLimitDecay' => 10, //seconds

    'getSeveralAlbumsLimit' => 20,
    'getSeveralArtistsLimit' => 50,

    'releaseAge' => 14, //days
    'checkAge' => 6, //hours

    'pagination' => 20,

    'cache_ttl' => 18 * 60 * 60,
    'cache_lock_ttl' => 30 * 60,

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
        '5ihY290YPGc3aY2xTyx7Gy', //Edvard Grieg
        '7iUy9ctasMRtp5jwWs95Fm', //Lo-Fi Beats
        '6iGoMyoSIjyTxbCRyHREtI', //Sol y Lluvia
        '10gzBoINW3cLJfZUka8Zoe', //Above & Beyond
        '459INk8vcC0ebEef82WjIK', //Erik Satie
        '6s1pCNXcbdtQJlsnM1hRIA', //Dmitri Shostakovich
        '2UqjDAXnDxejEyE0CzfUrZ', //Robert Schumann
        '7jzR5qj8vFnSu5JHaXgFEr', //Jean Sibelius
        '430byzy0c5bPn5opiu0SRd', //Edward Elgar
        '1385hLNbrnbCJGokfH2ac2', //Franz Liszt
        '1QL7yTHrdahRMpvNtn6rI2', //George Frideric Handel
        '39FC9x5PaTNYHp5hwlaY4q', //Niccolo Paganini
        '0roWUeP7Ac4yK4VN6L2gF4', //Gioachino Rossini
        '2gClsBep1tt1rv1CN210SO', //Gabriel Faure
        '656RXuyw7CE0dtjdPgjJV6', //Joseph Haydn
        '6MF58APd3YV72Ln2eVg710', //Felix Mendelssohn
        '1C1x4MVkql8AiABuTw6DgE', //Richard Wagner
        '2ANtgfhQkKpsW6EYSDqldz', //Gustav Mahler
        '3tMLo1k3iUo82coMLWXzxq', //Henry Purcell
        '6n7nd5iceYpXVwcx8VPpxF', //Antonin Dvorak
        '436sYg6CZhNefQJogaXeK0', //Camille Saint-Saens
        '17hR0sYHpx7VYTMRfFUOmY', //Maurice Ravel
        '0OzxPXyowUEQ532c9AmHUR', //Giacomo Puccini
        '5goS0v24Fc1ydjCKQRwtjM', //Johann Strauss II
        '1JOQXgYdQV2yfrhewqx96o', //Giuseppe Verdi

    ],

    'markets' => [
        'BY',
        'CA',
        'DE', 'DK',
        'ES',
        'FR',
        'GB',
        'IE', 'IT',
        'JP',
        'KR',
        'NO', 'NZ',
        'RU',
        'SE',
        'UA', 'US',
        'ZA'
    ],

    'default_market' => 'US',
];
