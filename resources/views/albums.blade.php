<x-layout>
    <x-slot name="header">
        <h2>
            The {{ $title }} releases for the last two weeks
        </h2>
    </x-slot>

    <div>
        @foreach($newReleases as $date => $dateNewReleases)
            <div>
                <p style="text-decoration: underline">{{ $date }}</p>
                <div>
                    @foreach($dateNewReleases as $newRelease)
                        <div style="display: flex">
                            <div>
                                <a href="{{ 'https://open.spotify.com/album/' . $newRelease->spotify_id }}" target="_blank">
                                    <img src="{{ $newRelease->image }}" height="220" width="220" style="margin-right: 16px">
                                </a>
                            </div>
                            <div>
                                <span style="font-weight: bold">{{ $newRelease->artist->name }}</span><br>
                                <span>{{ $newRelease->name }}</span><br><br>
                                <span>| @foreach($newRelease->artist->genres as $genre){{ $genre->name }} | @endforeach</span><br><br>
                                <span>Album popularity on Spotify: {{$newRelease->popularity}}/100</span>
                            </div>
                        </div>
                    @endforeach
                    <hr>
                </div>
            </div>
        @endforeach

    </div>
</x-layout>
