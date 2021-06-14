<x-layout>
    <x-slot name="header">
        <h2>
            {{ __('Hello') }}
        </h2>
    </x-slot>

    <div>
        <a href="{{ route('logout') }}">Logout</a><br>
        <p>Hello {{ \Illuminate\Support\Facades\Auth::user()->name }}. Please choose the tracking mode:</p>
    </div>
</x-layout>
