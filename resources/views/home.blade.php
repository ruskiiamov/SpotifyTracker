<x-layout>
    <x-slot name="header">
        <h2>
            {{ __('Hello') }}
        </h2>
    </x-slot>

    <div>
        <a href="{{ route('logout') }}">Logout</a>
        <p>Hello {{ \Illuminate\Support\Facades\Auth::user()->name }}. Please choose the tracking mode:</p>
        <h4>User tools:</h4>
        <a href="{{ route('followed') }}">Show Followed Artists New Releases</a><br>
        <a href="{{ route('genres') }}">Set Genres Subscription</a><br>
        <a href="{{ route('subscribed') }}">Show Subscribed Genres New Releases</a><br>
        <h4>Admin tools:</h4>
        <a href="{{ route('admin::artists') }}">Update followed artists</a><br>
        <a href="{{ route('admin::albums') }}">Update albums from followed artists</a><br>
        <a href="{{ route('admin::genres-analyse') }}">Genres analyse</a><br>
        <a href="{{ route('admin::check-albums') }}">Check album list</a><br>
        <a href="{{ route('admin::check-artists') }}">Check artist list</a><br>
        <a href="{{ route('admin::add-releases') }}">Add new releases by genres</a><br>
        <a href="{{ route('admin::test') }}">TEST</a><br>
    </div>
</x-layout>
