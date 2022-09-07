<x-layout>
    <x-slot name="info">
    </x-slot>

    <x-slot name="auth">
    </x-slot>

    <div>
        <p class="text-center font-bold text-xl">Please sign in with your Spotify account</p>
        <p class="text-center pb-8 font-bold text-xl">to stay tuned for your library artists new releases!</p>
        <form method="post" action="{{ route('loginSpotify') }}" class="flex flex-col justify-center">
            @csrf
            <input type="hidden" name="remember" value="0">
            <div class="flex justify-center">
                <button class="bg-green text-black font-bold py-2 px-4 rounded-full w-48 h-12">Login with Spotify</button>
            </div>
            <div class="flex justify-center items-center pt-2">
                <input class="appearance-none h-4 w-4 border border-b-white rounded-sm bg-black checked:bg-green transition duration-200 mt-1 bg-no-repeat bg-center bg-contain float-left mr-2 cursor-pointer" type="checkbox" name="remember" value="1" id="flexCheckDefault">
                <label class="inline-block" for="flexCheckDefault">
                    remember me
                </label>
            </div>
        </form>
        <p class="text-center pt-16 pb-8 font-bold text-xl">or try ReleaseHunter to browse new Spotify releases by genre:</p>
        <div class="flex flex-col items-center">
            <a href="{{ route('genres') }}" class="flex bg-green text-black font-bold py-2 px-4 rounded-full w-48 h-12 flex justify-center items-center">
                <p>Check it out!</p>
            </a>
        </div>
    </div>
</x-layout>
