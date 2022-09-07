<x-layout>
    <x-slot name="info">
    </x-slot>

    <x-slot name="auth">
        <a href="{{ route('logout') }}" class="font-bold text-white text-xl">Logout</a>
    </x-slot>

    <div>
        <p class="text-center font-bold text-xl">Hello {{ \Illuminate\Support\Facades\Auth::user()->name }}!</p>
        <p class="text-center pb-8 text-xl">What do you want to hunt?</p>
        <div class="flex flex-col items-center">
            <a id="button" href="{{ route('followed') }}" class="mb-4 bg-green text-black font-bold py-2 px-4 rounded-full w-48 h-12 flex justify-center items-center">
                <p>Followed Artists</p>
            </a>
            <a href="{{ route('genres') }}" class="flex bg-green text-black font-bold py-2 px-4 rounded-full w-48 h-12 flex justify-center items-center">
                <p>Releases by Genre</p>
            </a>
        </div>
    </div>
</x-layout>
