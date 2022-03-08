<x-layout>
    <x-slot name="header">
    </x-slot>

    <div>
        <p class="text-center pb-8 font-bold text-xl">Please sign in with your Spotify account to stay tuned for new releases!</p>
        <form method="post" action="{{ route('loginSpotify') }}" class="flex flex-col justify-center">
            @csrf
            <input type="hidden" name="remember" value="0">
            <div class="flex justify-center">
                <button class="bg-green text-black font-bold py-2 px-4 rounded-full h-12">Login with Spotify</button>
            </div>
            <div class="flex justify-center items-center pt-2">
                <input class="appearance-none h-4 w-4 border border-b-white rounded-sm bg-black checked:bg-green transition duration-200 mt-1 bg-no-repeat bg-center bg-contain float-left mr-2 cursor-pointer" type="checkbox" name="remember" value="1" id="flexCheckDefault">
                <label class="inline-block" for="flexCheckDefault">
                    remember me
                </label>
            </div>
        </form>
    </div>
</x-layout>
