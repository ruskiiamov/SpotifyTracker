<x-layout>
    <x-slot name="header">
        <h2>
            Set genres subscription
        </h2>
    </x-slot>

    <div>
        <form method="post" action="{{ route('subscription') }}">
            @foreach($categories as $category)
                <input type="checkbox" name="{{ $category->id }}">{{ $category->name }}<br>
            @endforeach
            <br><input type="submit" value="Save">
        </form>
    </div>
</x-layout>
