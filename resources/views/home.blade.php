<x-layout>
    <x-slot name="header">
        <h2>
            {{ __('Hello') }}
        </h2>
    </x-slot>

    <div>
        <a href="{{ route('logout') }}">Logout</a>
        <p>Hello {{ \Illuminate\Support\Facades\Auth::user()->name }}. Please choose the tracking mode:</p>
        <a href="{{ route('followed') }}">Followed artists</a><br>
        <a href="{{ route('genres') }}">Genres</a><br>
    </div>
</x-layout>
