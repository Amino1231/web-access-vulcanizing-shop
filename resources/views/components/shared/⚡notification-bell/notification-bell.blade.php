<div class="relative"
     x-data="{ open: false }"
     @click.outside="open = false"
     @keydown.escape.window="open = false">

    {{-- Bell button --}}
    <button @click="open = !open" type="button" aria-label="Notifications"
        class="relative flex h-10 w-10 items-center justify-center rounded-xl text-slate-600 transition hover:bg-slate-100 dark:text-gray-300 dark:hover:bg-gray-800">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>

        @if ($this->unreadCount > 0)
            <span class="absolute -right-0.5 -top-0.5 flex h-5 min-w-[20px] items-center justify-center rounded-full bg-[#FF5E14] px-1 text-[10px] font-bold text-white">
                {{ $this->unreadCount > 99 ? '99+' : $this->unreadCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown --}}
    <div x-show="open" x-cloak x-transition.origin.top.right
        class="fixed inset-x-3 top-16 z-50 mt-2 flex max-h-[80vh] flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-gray-800 dark:bg-gray-900
               sm:absolute sm:inset-x-auto sm:right-0 sm:top-full sm:w-96">

        {{-- Header --}}
        <div class="flex shrink-0 items-center justify-between gap-2 border-b border-slate-200 px-4 py-3 dark:border-gray-800">
            <div class="min-w-0">
                <p class="text-sm font-bold text-slate-900 dark:text-white">Notifications</p>
                <p class="text-[11px] text-slate-500 dark:text-gray-400">
                    {{ $this->unreadCount }} unread
                </p>
            </div>

            @if ($this->notifications->count() > 0)
                <div class="flex shrink-0 items-center gap-1">
                    @if ($this->unreadCount > 0)
                        <button wire:click="markAllAsRead" type="button"
                            class="whitespace-nowrap rounded-lg px-2 py-1 text-[11px] font-semibold text-[#FF5E14] transition hover:bg-orange-50 dark:hover:bg-orange-500/10">
                            Mark all read
                        </button>
                    @endif
                    <button wire:click="clearAll" type="button"
                        wire:confirm="Clear all notifications? This can't be undone."
                        class="whitespace-nowrap rounded-lg px-2 py-1 text-[11px] font-semibold text-slate-500 transition hover:bg-slate-100 dark:text-gray-400 dark:hover:bg-gray-800">
                        Clear
                    </button>
                </div>
            @endif
        </div>

        {{-- List --}}
        <div class="flex-1 overflow-y-auto overscroll-contain">
            @forelse ($this->notifications as $n)
                @php
                    $isUnread = is_null($n->read_at);
                    $data = is_array($n->data) ? $n->data : [];
                    $kind = $data['kind'] ?? '';
                @endphp

                <button wire:key="notif-{{ $n->id }}" wire:click="markAsRead('{{ $n->id }}')" type="button"
                    class="flex w-full items-start gap-3 border-b border-slate-100 px-4 py-3 text-left transition last:border-0 hover:bg-slate-50 dark:border-gray-800 dark:hover:bg-gray-800/50 {{ $isUnread ? 'bg-orange-50/40 dark:bg-orange-500/5' : '' }}">

                    <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg
                        {{ str_starts_with($kind, 'employee.') ? 'bg-orange-50 text-orange-600 dark:bg-orange-500/10 dark:text-orange-400'
                           : (str_starts_with($kind, 'owner.') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400'
                           : (str_starts_with($kind, 'shop.') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400'
                           : (str_starts_with($kind, 'appointment.') ? 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400'
                           : (str_starts_with($kind, 'roadside.') ? 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400'
                           : (str_starts_with($kind, 'order.') ? 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400'
                           : 'bg-purple-50 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400'))))) }}">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">
                            {{ $data['title'] ?? 'Notification' }}
                        </p>
                        <p class="mt-0.5 line-clamp-2 text-xs text-slate-600 dark:text-gray-400">
                            {{ $data['message'] ?? '' }}
                        </p>
                        <p class="mt-1 text-[10px] text-slate-400 dark:text-gray-500">
                            {{ $n->created_at->diffForHumans() }}
                        </p>
                    </div>

                    @if ($isUnread)
                        <span class="mt-2 h-2 w-2 shrink-0 rounded-full bg-[#FF5E14]"></span>
                    @endif
                </button>
            @empty
                <div class="px-4 py-12 text-center">
                    <p class="text-sm text-slate-500 dark:text-gray-400">No notifications yet.</p>
                </div>
            @endforelse
        </div>
    </div>

    @script
    <script>
        window.addEventListener('livewire-notification', () => {
            $wire.$refresh();
        });
    </script>
    @endscript
</div>