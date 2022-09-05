<?php

const ROCK = 'Rock/Metal/Punk';
const POP = 'Pop';
const HIP_HOP = 'Hip hop';
const ELECTRONIC = 'Electronic';
const FOLK = 'Folk/Country';
const BLUES_JAZZ = 'Blues/Jazz/Soul/Funk';
const CLASSICAL = 'Classical';
const WORLD = 'World';
const OTHER = 'Other';

return [
    'other' => OTHER,

    'categories' => [
        ROCK,
        POP,
        HIP_HOP,
        ELECTRONIC,
        FOLK,
        BLUES_JAZZ,
        CLASSICAL,
        WORLD,
        OTHER,
    ],

    //Genre has several categories
    'regularKeyWords' => [
        ROCK => ['rock', 'surf', 'punk', 'metal', 'djent', 'pixie'],
        POP => ['pop', 'disco'],
        HIP_HOP => ['hip hop', 'hip-hop', 'rap', 'phonk', 'drill', 'boom bap', 'chillhop'],
        ELECTRONIC => ['trance', 'edm', 'house', 'techno', 'dnb', 'synth', 'electro', 'tronica', 'amapiano', 'club',
            'bass', 'dub', 'beat', 'glitch', 'dance', 'rave'],
        FOLK => ['folk', 'country', 'bluegrass', 'roots', 'americana'],
        BLUES_JAZZ => ['jazz', 'blues', 'funk', 'soul', 'r&b', 'gospel'],
        CLASSICAL => ['classical', 'orchestra', 'romantic'],
        WORLD => ['samba', 'rumba', 'cumbia', 'tango', 'norteno', 'bossa nova', 'indigenous', 'reggae', 'ska'],
    ],

    //Genre has only one category
    'specialKeyWords' => [
        ROCK => ['pop punk', 'rap rock', 'ska punk', 'dance-punk', 'dance rock', 'straight edge', 'britpop', 'ponk',
            'ukhc', 'screamo', 'beatlesque', 'funk metal'],
        POP => ['singer-songwriter', 'diva house', 'laulaja-lauluntekija', 'francoton', 'schlager', 'chanson'],
        HIP_HOP => ['funk mtg', 'funk consciente', 'funk ostentacao', 'funk paulista', 'drain', 'psychokore',
            'lo-fi beat', 'japanese beats', 'lo-fi product', 'zxc', 'zhenskiy rep', 'hip house', 'rave funk'],
        WORLD => ['carioca', 'rocksteady', 'dancehall', 'azontobeats', 'manguebeat', 'afrobeat', 'axe', 'forro', 'mpb',
            'cuarteto', 'pagode', 'mexicana', 'ranchera', 'grupera', 'corrido', 'sungura', 'perreo', 'bhajan', 'ghazal',
            'enka', 'contemporanea', 'manele', 'cantautor'],
        ELECTRONIC => ['cyberpunk', 'funky tech house', 'disco house', 'funky house', 'drum and bass', 'future bass',
            'neurofunk']
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
        'j-acoustic', 'acoustic', 'dublin indie', 'kindermusik'],
];
