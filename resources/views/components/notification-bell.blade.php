@php
    $notifications = $smartvoltNotifications ?? collect();
    $unreadCount = $smartvoltUnreadNotificationsCount ?? 0;
@endphp

<div class="sv-notification-menu" data-notification-menu>
    <button
        class="sv-btn sv-notify-btn sv-notification-trigger"
        type="button"
        aria-label="Notifikasi"
        aria-expanded="false"
        data-notification-trigger
    >
        <i class="bi bi-bell"></i>

        @if($unreadCount > 0)
            <span class="sv-notification-count">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
        @endif
    </button>

    <div class="sv-notification-dropdown" data-notification-dropdown>
        <div class="sv-notification-head">
            <div>
                <strong>Notifikasi</strong>
                <span>{{ $unreadCount }} belum dibaca</span>
            </div>

            @if($unreadCount > 0)
                <form action="{{ route('notifications.read-all') }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit">Tandai semua</button>
                </form>
            @endif
        </div>

        <div class="sv-notification-list">
            @forelse($notifications as $notification)
                <form
                    action="{{ route('notifications.read', $notification) }}"
                    method="POST"
                    class="sv-notification-item {{ $notification->read_at ? '' : 'is-unread' }} {{ $notification->severity }}"
                >
                    @csrf
                    @method('PATCH')

                    <button type="submit">
                        <span class="sv-notification-icon">
                            <i class="bi {{ $notification->severity === 'danger' ? 'bi-exclamation-triangle-fill' : 'bi-info-circle-fill' }}"></i>
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
                    Belum ada notifikasi.
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
    if (! window.smartvoltNotificationBellReady) {
        window.smartvoltNotificationBellReady = true;

        document.addEventListener('click', function (event) {
            const trigger = event.target.closest('[data-notification-trigger]');
            const activeMenu = document.querySelector('[data-notification-menu].is-open');

            if (trigger) {
                const menu = trigger.closest('[data-notification-menu]');
                const isOpen = menu.classList.contains('is-open');

                document.querySelectorAll('[data-notification-menu].is-open').forEach(function (item) {
                    item.classList.remove('is-open');
                    item.querySelector('[data-notification-trigger]')?.setAttribute('aria-expanded', 'false');
                });

                if (! isOpen) {
                    menu.classList.add('is-open');
                    trigger.setAttribute('aria-expanded', 'true');
                }

                return;
            }

            if (activeMenu && ! event.target.closest('[data-notification-menu]')) {
                activeMenu.classList.remove('is-open');
                activeMenu.querySelector('[data-notification-trigger]')?.setAttribute('aria-expanded', 'false');
            }
        });
    }
</script>
