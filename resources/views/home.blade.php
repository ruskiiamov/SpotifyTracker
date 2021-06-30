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
        <h4>Admin tools:</h4>
        <a href="{{ route('admin::artists') }}">Update followed artists</a><br>
        <a href="{{ route('admin::albums') }}">Update albums from followed artists</a><br>
        <a href="{{ route('admin::genres-analyse') }}">Genres analyse</a><br>
        <a href="{{ route('admin::check-albums') }}">Check album list</a><br>
    </div>
</x-layout>
