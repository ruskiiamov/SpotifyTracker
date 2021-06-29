<?php

return [
    'releaseAge' => 30, //days

    'genreCategories' => [
        //genre category => key words
        'rock' => ['rock', 'grunge', 'britpop', 'surf', 'new wave'],
        'metal' => ['metal', 'thrash', 'djent', 'doom'],
        'punk' => ['punk', 'hardcore', 'emo', 'oi'],
        'pop' => ['pop'],
        'jazz' => ['jazz', 'bop'],
        'blues' => ['blues'],
        'hip hop' => ['hip hop', 'rap'],
        'funk' => ['funk'],
        'soul/r&b' => ['soul', 'r&b'],
        'classical' => ['classical'],
        'folk/country' => ['folk', 'country', 'bluegrass'],
        'reggae/ska' => ['reggae', 'ska'],
        'electronic' => ['electronic', 'rave', 'downtempo', 'trip hop', 'breakbeat',
            'big beat', 'trance', 'intelligent dance music', 'ambient'],
        'latin' => ['latin'],
        'other' => ['other'],
    ],

    'exceptions' => ['live', 'remix', 'anniversary', 'deluxe', 'expanded'],
];
