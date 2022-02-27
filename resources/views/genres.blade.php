<x-layout>
    <x-slot name="header">
        <h2>
            Set genres subscription
        </h2>
    </x-slot>

    <div>
        <form method="post" action="{{ route('subscription') }}">
            @csrf
            @foreach($categories as $category)
                <input type="hidden" name="{{ $category->id }}" value="0">
                <input type="checkbox" name="{{ $category->id }}" value="1"
                       @if (!is_null($subscriptions->where('category_id', $category->id)->first()))
                       checked
                       @endif
                >
                <span>{{ $category->name }}</span><br>
            @endforeach
            <br><input type="submit" value="Save and Show">
        </form>
    </div>
</x-layout>
