<?php

use App\Services\GenreCategorizer;

return [
    'other' => GenreCategorizer::OTHER,

    'categories' => [
        GenreCategorizer::ROCK,
        GenreCategorizer::POP,
        GenreCategorizer::HIP_HOP,
        GenreCategorizer::ELECTRONIC,
        GenreCategorizer::FOLK,
        GenreCategorizer::BLUES_JAZZ,
        GenreCategorizer::CLASSICAL,
        GenreCategorizer::WORLD,
        GenreCategorizer::OTHER,
    ],

    //Genre has several categories
    'regularKeyWords' => [
        GenreCategorizer::ROCK => ['rock', 'surf', 'punk', 'metal', 'djent', 'pixie'],
        GenreCategorizer::POP => ['pop', 'disco'],
        GenreCategorizer::HIP_HOP => ['hip hop', 'hip-hop', 'rap', 'phonk', 'drill', 'boom bap', 'chillhop'],
        GenreCategorizer::ELECTRONIC => ['trance', 'edm', 'house', 'techno', 'dnb', 'synth', 'electro', 'tronica', 'amapiano', 'club',
            'bass', 'dub', 'beat', 'glitch', 'dance', 'rave'],
        GenreCategorizer::FOLK => ['folk', 'country', 'bluegrass', 'roots', 'americana'],
        GenreCategorizer::BLUES_JAZZ => ['jazz', 'blues', 'funk', 'soul', 'r&b', 'gospel'],
        GenreCategorizer::CLASSICAL => ['classical', 'orchestra', 'romantic'],
        GenreCategorizer::WORLD => ['samba', 'rumba', 'cumbia', 'tango', 'norteno', 'bossa nova', 'indigenous', 'reggae', 'ska',
            'flamenco', 'salsa'],
    ],

    //Genre has only one category
    'specialKeyWords' => [
        GenreCategorizer::ROCK => ['pop punk', 'rap rock', 'ska punk', 'dance-punk', 'dance rock', 'straight edge', 'britpop', 'ponk',
            'ukhc', 'screamo', 'beatlesque', 'funk metal', 'deathcore'],
        GenreCategorizer::POP => ['singer-songwriter', 'diva house', 'laulaja-lauluntekija', 'francoton', 'schlager', 'chanson',
            'j-division', 'canzone napoletana'],
        GenreCategorizer::HIP_HOP => ['funk mtg', 'funk consciente', 'funk ostentacao', 'funk paulista', 'drain', 'psychokore',
            'lo-fi beat', 'japanese beats', 'lo-fi product', 'zxc', 'zhenskiy rep', 'hip house', 'rave funk'],
        GenreCategorizer::WORLD => ['carioca', 'rocksteady', 'dancehall', 'azontobeats', 'manguebeat', 'afrobeat', 'axe', 'forro', 'mpb',
            'cuarteto', 'pagode', 'mexicana', 'ranchera', 'grupera', 'corrido', 'sungura', 'perreo', 'bhajan', 'ghazal',
            'enka', 'contemporanea', 'manele', 'cantautor', 'bachata', 'caliente'],
        GenreCategorizer::ELECTRONIC => ['cyberpunk', 'funky tech house', 'disco house', 'funky house', 'drum and bass', 'future bass',
            'neurofunk', 'hjemmesnekk', 'new french touch', 'hardstyle', 'rawstyle', 'jungle']
    ],

    'bannedGenreNames' => ['sleep', 'white noise', 'rain', 'world meditation', 'russian chanson', 'kleine hoerspiel',
        'hoerspiel', 'writing', 'musica de fondo', 'shush', 'british soundtrack', 'epicore', 'orchestral soundtrack',
        'soundtrack', 'video game music', 'anime score', 'japanese soundtrack', 'ocean', 'kabarett', 'environmental',
        'genshin', 'asmr', 'sped up', 'pet calming', 'workout product', 'lullaby', 'water', 'bgm', 'neru', 'vocaloid',
        'japanese instrumental', 'piano cover', 'mollywood', 'sandalwood', 'spa', 'sound', 'yoga', 'pianissimo',
        'icelandic experimental', 'japanese guitar', 'japanese vgm', 'instrumental worship', 'piano worship', 'reiki',
        'german soundtrack', 'brain waves', 'korean instrumental', 'dinner jazz', 'music box', 'slowed and reverb',
        'binaural', 'bornehistorier', 'jirai kei', 'erotic product', 'puirt-a-beul', 'massage', 'zen', 'mindfulness',
        'classic bollywood', 'filmi', 'modern bollywood', 'meditation', 'ilahiler', 'lesen', 'liedermacher',
        'disney piano', 'easy listening', 'lounge', 'therapy', 'chill out', 'cancion infantil latinoamericana', 'anime',
        'j-acoustic', 'acoustic', 'dublin indie', 'kindermusik', 'ensemble stars', 'hypnosis mic', 'cartoon', 'nursery',
        'polish alternative', 'polish indie', 'theremin', 'musica per bambini', "children's choir", "children's music",
        'background music', 'classic soundtrack', 'italian soundtrack', 'vintage italian soundtrack', 'anime piano',
        'hardcore'],
];
