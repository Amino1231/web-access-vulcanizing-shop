<form action="{{ route('customer.new_arrivals') }}" method="GET" class="relative w-full">
    <input type="text"
           name="search"
           id="{{ $id ?? 'header-search' }}"
           value="{{ request('search') }}"
           placeholder="Search services, tires, or shop location..."
           class="w-full px-4 py-2.5 pr-12 text-sm bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all" />

    <button type="submit"
            class="absolute right-1.5 top-1/2 -translate-y-1/2 p-2 bg-orange-500 hover:bg-orange-600 rounded-md transition-colors"
            aria-label="Search">
        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
    </button>
</form>