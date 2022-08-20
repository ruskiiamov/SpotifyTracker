<x-layout>
    <x-slot name="header">
        @if(!empty(auth()->user()))
        <a href="{{ route('logout') }}" class="font-bold text-white text-xl">Logout</a>
        @endif
    </x-slot>

    <div>
        <p class="text-center pb-8 font-bold text-xl">Please choose genres</p>
        <form method="post" action="{{ route('subscription') }}" >
            @csrf
            <div class="grid grid-cols-2 gap-y-8 gap-x-16 pb-8">
                @foreach($allCategories as $category)
                    <div class="flex justify-center items-center">
                        <input type="hidden" name="{{ $category->id }}" value="0">
                        <input type="checkbox" name="{{ $category->id }}" id="{{ $category->id }}" value="1" class="peer appearance-none"
                            @if($userCategories->contains($category))
                            checked
                            @endif
                        >
                        <label for="{{ $category->id }}" class="border border-2 border-green rounded-full w-full min-w-fit h-10 select-none peer-checked:bg-green peer-checked:outline peer-checked:outline-green peer-checked:outline-2 peer-checked:outline-offset-2 flex justify-center items-center">
                            <p>{{ $category->name }}</p>
                        </label>
                    </div>
                @endforeach
            </div>
            <div class="flex justify-center">
                <button class="bg-green text-black font-bold py-2 px-4 rounded-full w-48 h-12">
                    <p>Hunt!</p>
                </button>
            </div>
        </form>
    </div>
</x-layout>
