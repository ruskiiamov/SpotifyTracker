<x-layout>
    <x-slot name="auth">
        @if(!empty(auth()->user()))
        <a href="{{ route('logout') }}" class="font-bold text-white text-xl">Logout</a>
        @endif
    </x-slot>

    <div>
        @if(!empty($categories))
            <div class="flex flex-wrap justify-center mb-6">
                @foreach($categories as $category)
                <span class="mr-2 my-1 px-2 text-base text-white bg-green border border-green rounded-full inline-block">
                    {{ $category->name }}
                </span>
                @endforeach
            </div>
        @endif
        @if(!empty($title))
            <div class="flex flex-wrap justify-center mb-6">
                <span class="mr-2 my-1 px-2 text-base text-white bg-green border border-green rounded-full inline-block">
                    {{ $title }}
                </span>
            </div>
        @endif
        <div class="flex justify-center mb-6">
            @if($onlyAlbums)
                <div class="flex bg-green outline outline-green outline-2 outline-offset-2 text-white py-2 px-4 mx-2 rounded-full w-60 h-8 flex min-w-max justify-center items-center">
                    <p>Only albums</p>
                </div>
                <a href="{{ route($current_route, ['only_albums' => 0]) }}" class="flex text-white border border-green py-2 px-4 mx-2 rounded-full w-60 h-8 flex min-w-max justify-center items-center">
                    <p>All releases</p>
                </a>
            @else
                <a href="{{ route($current_route, ['only_albums' => 1]) }}" class="flex text-white border border-green py-2 px-4 mx-2 rounded-full w-60 h-8 flex min-w-max justify-center items-center">
                    <p>Only albums</p>
                </a>
                <a class="flex bg-green outline outline-green outline-2 outline-offset-2 text-white py-2 px-4 mx-2 rounded-full w-60 h-8 flex min-w-max justify-center items-center">
                    <p>All releases</p>
                </a>
            @endif
        </div>
        @if($albums->hasPages())
            <div class="flex justify-center mb-6">
                @if(!$albums->onFirstPage())
                    <a href="{{ route($current_route, ['page' => $albums->currentPage() - 1]) }}" class="flex bg-green text-black font-bold py-2 px-4 mx-2 rounded-full w-24 h-12 flex min-w-max justify-center items-center">
                        <p>Prev</p>
                    </a>
                @else
                    <div class="flex bg-black text-green border border-green font-bold py-2 px-4 mx-2 rounded-full w-24 h-12 flex min-w-max justify-center items-center">
                        <p>Prev</p>
                    </div>
                @endif
                @if($albums->hasMorePages())
                    <a href="{{ route($current_route, ['page' => $albums->currentPage() + 1]) }}" class="flex bg-green text-black font-bold py-2 px-4 mx-2 rounded-full w-24 h-12 flex min-w-max justify-center items-center">
                        <p>Next</p>
                    </a>
                @else
                    <div class="flex bg-black text-green border border-green font-bold py-2 px-4 mx-2 rounded-full w-24 h-12 flex min-w-max justify-center items-center">
                        <p>Next</p>
                    </div>
                @endif
            </div>
        @endif
        @foreach($newReleases as $date => $dateNewReleases)
            <div>
                <div class="min-w-max">
                    <span class="px-2 py-1.5 bg-green border border-green rounded-full">{{ date("F d", strtotime($date)) }}</span>
                </div>
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
                                    <span class="mr-2 my-2 px-2 text-lg text-white bg-black border border-white rounded-full inline-block">{{ $newRelease->type }}</span>
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
        @if($albums->hasPages())
            <div class="flex justify-center mb-6">
                @if(!$albums->onFirstPage())
                    <a href="{{ route($current_route, ['page' => $albums->currentPage() - 1]) }}" class="flex bg-green text-black font-bold py-2 px-4 mx-2 rounded-full w-24 h-12 flex justify-center items-center">
                        <p>Prev</p>
                    </a>
                @else
                    <div class="flex bg-black text-green border border-green font-bold py-2 px-4 mx-2 rounded-full w-24 h-12 flex justify-center items-center">
                        <p>Prev</p>
                    </div>
                @endif
                @if($albums->hasMorePages())
                    <a href="{{ route($current_route, ['page' => $albums->currentPage() + 1]) }}" class="flex bg-green text-black font-bold py-2 px-4 mx-2 rounded-full w-24 h-12 flex justify-center items-center">
                        <p>Next</p>
                    </a>
                @else
                    <div class="flex bg-black text-green border border-green font-bold py-2 px-4 mx-2 rounded-full w-24 h-12 flex justify-center items-center">
                        <p>Next</p>
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-layout>
