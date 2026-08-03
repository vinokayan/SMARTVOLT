@php
    $notifications = $smartvoltNotifications ?? collect();
    $unreadCount = (int) ($smartvoltUnreadNotificationsCount ?? 0);
@endphp

<div class="sv-notification-menu" data-notification-menu>
    <button
        type="button"
        class="sv-icon-button sv-notification-trigger"
        aria-label="Buka notifikasi"
        aria-expanded="false"
        data-notification-trigger
    >
        <x-icon name="bell" :size="20" />

        @if($unreadCount > 0)
            <span class="sv-notification-count">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
        @endif
    </button>

    <div class="sv-notification-dropdown" data-notification-dropdown>
        <div class="sv-notification-header">
            <div>
                <strong>Notifikasi</strong>
                <span>{{ $unreadCount }} belum dibaca</span>
            </div>

            @if($unreadCount > 0)
                <form action="{{ route('notifications.read-all') }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="sv-text-button">Tandai semua</button>
                </form>
            @endif
        </div>

        <div class="sv-notification-list">
            @forelse($notifications as $notification)
                <form action="{{ route('notifications.read', $notification) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <button
                        type="submit"
                        class="sv-notification-item {{ $notification->read_at ? '' : 'is-unread' }} is-{{ $notification->severity }}"
                    >
                        <span class="sv-notification-icon">
                            <x-icon :name="$notification->severity === 'danger' ? 'warning' : 'info'" :size="18" />
                        </span>

                        <span class="sv-notification-copy">
                            <strong>{{ $notification->title }}</strong>
                            <span>{{ $notification->message }}</span>
                            <small>{{ optional($notification->created_at)->diffForHumans() }}</small>
                        </span>
                    </button>
                </form>
            @empty
                <div class="sv-notification-empty">
                    <x-icon name="bell" :size="24" />
                    <strong>Belum ada notifikasi</strong>
                    <span>Pemberitahuan sistem akan tampil di sini.</span>
                </div>
            @endforelse
        </div>
    </div>
</div>
