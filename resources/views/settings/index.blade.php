<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - SmartVolt</title>

    <link rel="stylesheet" href="{{ asset('assets/css/smartvolt-brand.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        .sv-settings-tabs {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
            padding: 8px;
            border-radius: 24px;
            margin-bottom: 24px;
            max-width: 940px;
            margin-left: auto;
            margin-right: auto;
        }

        .sv-settings-tab {
            border: none;
            border-radius: 18px;
            padding: 16px 18px;
            color: #ffffff;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: linear-gradient(135deg, rgba(62, 167, 255, 0.95), rgba(95, 124, 255, 0.95));
        }

        .sv-settings-content {
            max-width: 940px;
            margin: 0 auto;
        }

        .sv-settings-panel {
            padding: 22px;
            border-radius: 28px;
        }

        .sv-settings-title-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
        }

        .sv-settings-title-row h3 {
            margin: 0;
            font-size: 18px;
            letter-spacing: -0.02em;
        }

        .sv-settings-icon {
            width: 52px;
            height: 52px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            background: linear-gradient(135deg, rgba(66, 196, 255, 0.18), rgba(94, 255, 209, 0.14));
            color: #bff4ff;
            flex-shrink: 0;
        }

        .sv-form-stack {
            display: grid;
            gap: 14px;
        }

        .sv-form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
        }

        .sv-form-group {
            display: grid;
            gap: 8px;
        }

        .sv-form-label {
            color: #b5c8e5;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .sv-form-input {
            width: 100%;
            border: 1px solid rgba(255,255,255,0.08);
            background: rgba(255,255,255,0.06);
            color: #eef5ff;
            border-radius: 18px;
            padding: 14px 16px;
            font-size: 15px;
            outline: none;
        }

        .sv-form-input::placeholder {
            color: #8ca4c8;
        }

        .sv-form-input:focus {
            border-color: rgba(90, 198, 255, 0.45);
            box-shadow: 0 0 0 4px rgba(90, 198, 255, 0.10);
        }

        .sv-form-divider {
            height: 1px;
            background: rgba(255,255,255,0.07);
            margin: 4px 0;
        }

        .sv-primary-btn {
            border: none;
            border-radius: 16px;
            padding: 13px 18px;
            font-size: 14px;
            font-weight: 800;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: 0.2s ease;
            background: linear-gradient(135deg, #3ea7ff, #5f7cff);
            color: #fff;
        }

        .sv-primary-btn:hover {
            transform: translateY(-1px);
        }

        .sv-status-banner,
        .sv-error-banner {
            margin-bottom: 18px;
            border-radius: 18px;
            padding: 14px 16px;
            font-size: 14px;
            font-weight: 600;
        }

        .sv-status-banner {
            background: rgba(73, 212, 155, 0.14);
            color: #c8ffe7;
            border: 1px solid rgba(73, 212, 155, 0.18);
        }

        .sv-error-banner {
            background: rgba(255, 97, 97, 0.14);
            color: #ffd7d7;
            border: 1px solid rgba(255, 97, 97, 0.18);
        }

        @media (min-width: 860px) {
            .sv-form-grid {
                grid-template-columns: 1fr 1fr;
            }

            .sv-span-2 {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 720px) {
            .sv-settings-title-row {
                flex-direction: column-reverse;
            }
        }
    </style>
</head>

<body class="sv-dashboard-body">
    <div class="sv-app">
        <aside class="sv-sidebar">
            <div class="brand">
                <div class="icon">
                    <i class="bi bi-lightning-charge-fill"></i>
                </div>
                <span>SmartVolt</span>
            </div>

            <p>Your smart energy control center for real-time home electricity monitoring.</p>

            <nav class="sv-nav">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-house-door-fill"></i>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('rooms') }}" class="{{ request()->routeIs('rooms*') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Rooms</span>
                </a>

                <a href="{{ route('energy.history') }}" class="{{ request()->routeIs('energy.history') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart-fill"></i>
                    <span>Energy History</span>
                </a>

                <a href="{{ route('settings') }}" class="{{ request()->routeIs('settings*') ? 'active' : '' }}">
                    <i class="bi bi-gear-fill"></i>
                    <span>Settings</span>
                </a>
            </nav>
        </aside>

        <main class="sv-main">
            <header class="sv-topbar">
    <div class="sv-topbar-inner">
        <div class="sv-topbar-left">
            <div>
                <h1 class="sv-page-title">Settings</h1>
                <p class="sv-page-sub">Hello, {{ auth()->user()->name ?? 'User' }}</p>
            </div>
        </div>
    </div>
</header>

            <section class="sv-shell">
                @if(session('status'))
                    <div class="sv-status-banner">
                        <i class="bi bi-check-circle-fill"></i>
                        {{ session('status') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="sv-error-banner">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="sv-hero">
                    <div class="sv-hero-card sv-glass" style="padding: 28px;">
                        <div>
                            <div class="sv-live-chip">
                                <span class="sv-live-dot"></span>
                                Account Configuration
                            </div>

                            <h1 style="margin-bottom: 10px;">Personalize Your SmartVolt Experience</h1>
                            <p style="color: #cfe3ff; line-height: 1.7; max-width: 760px;">
                                Keep your account secure, update your profile, and make sure your SmartVolt workspace is ready to support smarter energy monitoring.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="sv-settings-tabs sv-glass">
                    <button type="button" class="sv-settings-tab">
                        <i class="bi bi-person-fill-gear"></i>
                        Account Settings
                    </button>
                </div>

                <div class="sv-settings-content">
                    <div class="sv-settings-panel sv-glass">
                        <div class="sv-settings-title-row">
                            <div>
                                <h3>Account Settings</h3>
                                <div class="sv-panel-sub">
                                    Manage your name, email address, and password to keep your SmartVolt account secure and up to date.
                                </div>
                            </div>

                            <div class="sv-settings-icon">
                                <i class="bi bi-person-fill-gear"></i>
                            </div>
                        </div>

                        <div class="sv-form-stack">
                            <form method="POST" action="{{ route('settings.profile.update') }}" class="sv-form-stack">
                                @csrf
                                @method('PUT')

                                <div class="sv-form-grid">
                                    <div class="sv-form-group">
                                        <label class="sv-form-label">Name</label>
                                        <input
                                            type="text"
                                            name="name"
                                            class="sv-form-input"
                                            value="{{ old('name', $user->name) }}"
                                            placeholder="Enter your name"
                                            required
                                        >
                                    </div>

                                    <div class="sv-form-group">
                                        <label class="sv-form-label">Email</label>
                                        <input
                                            type="email"
                                            name="email"
                                            class="sv-form-input"
                                            value="{{ old('email', $user->email) }}"
                                            placeholder="Enter your email address"
                                            required
                                        >
                                    </div>
                                </div>

                                <div>
                                    <button type="submit" class="sv-primary-btn">
                                        <i class="bi bi-check-circle-fill"></i>
                                        Save Account
                                    </button>
                                </div>
                            </form>

                            <div class="sv-form-divider"></div>

                            <form method="POST" action="{{ route('settings.password.update') }}" class="sv-form-stack">
                                @csrf
                                @method('PUT')

                                <div class="sv-form-grid">
                                    <div class="sv-form-group sv-span-2">
                                        <label class="sv-form-label">Current Password</label>
                                        <input
                                            type="password"
                                            name="current_password"
                                            class="sv-form-input"
                                            placeholder="Enter your current password"
                                        >
                                    </div>

                                    <div class="sv-form-group">
                                        <label class="sv-form-label">New Password</label>
                                        <input
                                            type="password"
                                            name="password"
                                            class="sv-form-input"
                                            placeholder="Create a new password"
                                        >
                                    </div>

                                    <div class="sv-form-group">
                                        <label class="sv-form-label">Confirm Password</label>
                                        <input
                                            type="password"
                                            name="password_confirmation"
                                            class="sv-form-input"
                                            placeholder="Confirm your new password"
                                        >
                                    </div>
                                </div>

                                <div>
                                    <button type="submit" class="sv-primary-btn">
                                        <i class="bi bi-shield-lock-fill"></i>
                                        Update Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>