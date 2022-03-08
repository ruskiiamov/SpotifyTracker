<x-layout>
    <x-slot name="header">
        <a href="{{ route('logout') }}" class="font-bold text-white text-xl">Logout</a>
    </x-slot>

    <div>
        @foreach($newReleases as $date => $dateNewReleases)
            <div>
                <span class="p-2 bg-green border border-green rounded-full">{{ date("F d", strtotime($date)) }}</span>
                <div class="my-4 grid xl:grid-cols-2 lg:grid-cols-2 md:grid-cols-1 sm:grid-cols-1">
                    @foreach($dateNewReleases as $newRelease)
                        <div class="mb-16 grid gap-x-2 xl:grid-cols-2 lg:grid-cols-2 md:grid-cols-2 sm:grid-cols-1">
                            <div class="pl-1">
                                <a href="{{ 'https://open.spotify.com/album/' . $newRelease->spotify_id }}" target="_blank">
                                    <img src="{{ $newRelease->image }}" class="border border-2 border-white">
                                </a>
                            </div>
                            <div class="pr-1 flex flex-col justify-between">
                                <div>
                                    <p class="font-bold text-2xl">{{ $newRelease->artist->name }}</p>
                                    <p class="text-xl">{{ $newRelease->name }}</p>
                                </div>
                                <div>
                                    @foreach($newRelease->artist->genres as $genre)<span class="mr-2 my-1 px-1 text-base text-white bg-green border border-green rounded-full inline-block">{{ $genre->name }}</span>@endforeach
                                </div>
                                <div>
                                    <span>popularity: {{$newRelease->popularity}}/100</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

    </div>
</x-layout>
