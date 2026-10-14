<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public bool $open = false;

    #[Computed]
    public function unreadCount(): int
    {
        return Auth::user()?->unreadNotifications()->count() ?? 0;
    }

    #[Computed]
    public function notifications()
    {
        return Auth::user()
            ?->notifications()
            ->latest()
            ->limit(15)
            ->get() ?? collect();
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function markAsRead(string $id): void
    {
        $notification = Auth::user()?->notifications()->find($id);
        if (! $notification) return;

        $notification->markAsRead();

        unset($this->unreadCount, $this->notifications);

        $url = $notification->data['url'] ?? null;
        if ($url) {
            $this->redirect($url, navigate: true);
        }
    }

    public function markAllAsRead(): void
    {
        Auth::user()?->unreadNotifications->markAsRead();
        unset($this->unreadCount, $this->notifications);
    }

    public function clearAll(): void
    {
        Auth::user()?->notifications()->delete();
        unset($this->unreadCount, $this->notifications);
    }

    #[On('notification-received')]
    public function onNotificationReceived(): void
    {
        unset($this->unreadCount, $this->notifications);
    }
};