<x-layout>
    <x-slot name="header">
        @if(!empty(auth()->user()))
        <a href="{{ route('logout') }}" class="font-bold text-white text-xl">Logout</a>
        @endif
    </x-slot>

    <div>
        <div class="flex flex-wrap justify-center mb-6">
            @foreach($categories as $category)
            <span class="mr-2 my-1 px-2 text-base uppercase text-white bg-green border border-green rounded-full inline-block">
                {{ $category->name }}
            </span>
            @endforeach
        </div>
        @if($albums->hasPages())
        <div class="flex justify-center mb-6">
            @if(!$albums->onFirstPage())
            <a href="{{ $albums->previousPageUrl() }}" class="flex bg-green text-black font-bold py-2 px-4 mx-2 rounded-full w-24 h-12 flex justify-center items-center">
                <p>Prev</p>
            </a>
            @endif
            @if($albums->hasMorePages())
            <a href="{{ $albums->nextPageUrl() }}" class="flex bg-green text-black font-bold py-2 px-4 mx-2 rounded-full w-24 h-12 flex justify-center items-center">
                <p>Next</p>
            </a>
            @endif
        </div>
        @endif
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
                                    @foreach($newRelease->artist->genres->unique() as $genre)<span class="mr-2 my-1 px-1 text-base text-white bg-green border border-green rounded-full inline-block">{{ $genre->name }}</span>@endforeach
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
        @if($albums->hasPages())
            <div class="flex justify-center mb-6">
                @if(!$albums->onFirstPage())
                <a href="{{ $albums->previousPageUrl() }}" class="flex bg-green text-black font-bold py-2 px-4 mx-2 rounded-full w-24 h-12 flex justify-center items-center">
                    <p>Prev</p>
                </a>
                @endif
                @if($albums->hasMorePages())
                <a href="{{ $albums->nextPageUrl() }}" class="flex bg-green text-black font-bold py-2 px-4 mx-2 rounded-full w-24 h-12 flex justify-center items-center">
                    <p>Next</p>
                </a>
                @endif
            </div>
        @endif
    </div>
</x-layout>
