<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#d9eafe">

    <title>Masuk | SmartVolt</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <style>
        :root {
            --blue-700: #0b3f86;
            --blue-650: #0e4a9a;
            --blue-600: #1460d7;
            --blue-500: #2563eb;
            --blue-100: #dbeafe;
            --blue-50: #eff6ff;

            --navy-900: #10213e;
            --navy-800: #17355f;
            --slate-700: #415678;
            --slate-600: #61718c;
            --slate-500: #7d8ca4;
            --slate-300: #cfd9e8;
            --slate-200: #e2e9f2;
            --slate-100: #f4f7fb;

            --green-600: #14915a;
            --green-100: #e8f8ef;
            --orange-500: #ff9d1c;
            --orange-100: #fff4df;
            --purple-500: #7c3aed;
            --purple-100: #f1eafd;

            --danger-600: #dc2626;
            --danger-100: #fff1f2;

            --white: #ffffff;
            --panel-left: #d9eafe;
            --panel-left-strong: #c8dfff;

            --radius-sm: 10px;
            --radius-md: 14px;
            --radius-lg: 24px;

            --shadow-soft: 0 24px 70px rgba(35, 72, 128, 0.10);
            --shadow-button: 0 12px 24px rgba(37, 99, 235, 0.22);
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            min-width: 320px;
            min-height: 100%;
            margin: 0;
        }

        body {
            min-height: 100vh;
            min-height: 100svh;
            overflow-x: hidden;
            color: var(--navy-900);
            background: #eef4fb;
            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
        }

        button,
        input {
            font: inherit;
        }

        button,
        a {
            -webkit-tap-highlight-color: transparent;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        svg {
            display: block;
        }

        .login-page {
            min-height: 100vh;
            min-height: 100svh;
            padding: 0;
        }

        .login-shell {
            display: grid;
            width: 100%;
            min-height: 100vh;
            min-height: 100svh;
            grid-template-columns: minmax(0, 1.04fr) minmax(470px, 0.96fr);
            overflow: hidden;
            background: var(--white);
        }

        /* =========================================================
           PANEL KIRI
           ========================================================= */

        .login-showcase {
            position: relative;
            display: flex;
            min-height: 100vh;
            min-height: 100svh;
            overflow: hidden;
            background:
                radial-gradient(
                    circle at 94% 12%,
                    rgba(255, 255, 255, 0.58),
                    transparent 27%
                ),
                linear-gradient(
                    150deg,
                    #e6f1ff 0%,
                    #d9eafe 45%,
                    #cfe3ff 100%
                );
        }

        .login-showcase::before {
            position: absolute;
            inset: 0;
            z-index: 0;
            background-image:
                radial-gradient(circle, rgba(255, 255, 255, 0.85) 1.5px, transparent 1.5px);
            background-position: calc(100% - 42px) 0;
            background-size: 14px 14px;
            background-repeat: no-repeat;
            content: "";
            opacity: 0.72;
            pointer-events: none;
        }

        .login-showcase::after {
            position: absolute;
            top: 150px;
            right: -230px;
            width: 520px;
            height: 520px;
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 50%;
            content: "";
            pointer-events: none;
        }

        .showcase-content {
            position: relative;
            z-index: 3;
            display: flex;
            width: min(100%, 790px);
            min-height: 100%;
            margin-inline: auto;
            padding:
                clamp(34px, 5vh, 58px)
                clamp(38px, 5.5vw, 72px)
                clamp(38px, 4.5vh, 52px);
            flex-direction: column;
        }

        .brand {
            display: inline-flex;
            width: fit-content;
            align-items: center;
            gap: 14px;
        }

        .brand-mark {
            display: grid;
            width: 56px;
            height: 56px;
            flex: 0 0 56px;
            place-items: center;
            border-radius: 15px;
            color: #ffffff;
            background:
                linear-gradient(145deg, #2474ed 0%, #145ccf 100%);
            box-shadow:
                0 12px 26px rgba(20, 96, 215, 0.23);
        }

        .brand-mark svg {
            width: 29px;
            height: 29px;
        }

        .brand-copy strong {
            display: block;
            color: var(--navy-900);
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.035em;
            line-height: 1.1;
        }

        .brand-copy small {
            display: block;
            margin-top: 4px;
            color: var(--navy-800);
            font-size: 13px;
            font-weight: 500;
        }

        .showcase-main {
            position: relative;
            z-index: 3;
            max-width: 600px;
            margin-top: clamp(58px, 8vh, 96px);
        }

        .eyebrow {
            display: inline-flex;
            min-height: 36px;
            margin: 0 0 24px;
            padding: 8px 14px;
            align-items: center;
            gap: 9px;
            border-radius: 999px;
            color: var(--blue-600);
            background: rgba(255, 255, 255, 0.48);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.65);
            font-size: 13px;
            font-weight: 780;
            letter-spacing: 0.025em;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .eyebrow::before {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--blue-500);
            content: "";
        }

        .showcase-main h1 {
            max-width: 620px;
            margin: 0;
            color: var(--blue-700);
            font-size: clamp(48px, 5.4vw, 68px);
            font-weight: 800;
            line-height: 1.04;
            letter-spacing: -0.055em;
        }

        .showcase-description {
            max-width: 520px;
            margin: 26px 0 0;
            color: var(--navy-800);
            font-size: clamp(16px, 1.5vw, 18px);
            line-height: 1.72;
        }

        .benefits {
            position: relative;
            z-index: 3;
            display: grid;
            width: min(100%, 530px);
            margin-top: 34px;
            gap: 18px;
        }

        .benefit {
            display: grid;
            grid-template-columns: 46px minmax(0, 1fr);
            align-items: center;
            gap: 15px;
        }

        .benefit-icon {
            display: grid;
            width: 46px;
            height: 46px;
            place-items: center;
            border-radius: 13px;
            background: rgba(255, 255, 255, 0.68);
            box-shadow:
                0 10px 22px rgba(43, 83, 139, 0.08),
                inset 0 0 0 1px rgba(255, 255, 255, 0.82);
        }

        .benefit:nth-child(1) .benefit-icon {
            color: var(--blue-600);
        }

        .benefit:nth-child(2) .benefit-icon {
            color: var(--orange-500);
        }

        .benefit:nth-child(3) .benefit-icon {
            color: var(--green-600);
        }

        .benefit-icon svg {
            width: 23px;
            height: 23px;
            fill: none;
            stroke: currentColor;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-width: 1.9;
        }

        .benefit h2 {
            margin: 0 0 5px;
            color: var(--navy-900);
            font-size: 14px;
            font-weight: 780;
            line-height: 1.35;
        }

        .benefit p {
            margin: 0;
            color: var(--navy-800);
            font-size: 12px;
            line-height: 1.55;
        }

        /* =========================================================
           ILUSTRASI RUMAH
           ========================================================= */

        .house-scene {
            position: absolute;
            z-index: 1;
            right: -26px;
            bottom: 90px;
            width: min(55%, 420px);
            min-width: 310px;
            pointer-events: none;
        }

        .house-scene svg {
            width: 100%;
            height: auto;
        }

        .city-silhouette {
            position: absolute;
            z-index: 0;
            right: 0;
            bottom: 145px;
            width: 53%;
            height: 330px;
            opacity: 0.38;
            pointer-events: none;
        }

        .city-silhouette span {
            position: absolute;
            bottom: 0;
            display: block;
            border-radius: 9px 9px 0 0;
            background: rgba(124, 175, 239, 0.29);
        }

        .city-silhouette span:nth-child(1) {
            left: 4%;
            width: 42px;
            height: 112px;
        }

        .city-silhouette span:nth-child(2) {
            left: 14%;
            width: 54px;
            height: 166px;
        }

        .city-silhouette span:nth-child(3) {
            left: 27%;
            width: 48px;
            height: 132px;
        }

        .city-silhouette span:nth-child(4) {
            left: 41%;
            width: 68px;
            height: 212px;
        }

        .city-silhouette span:nth-child(5) {
            left: 58%;
            width: 52px;
            height: 164px;
        }

        .city-silhouette span:nth-child(6) {
            left: 72%;
            width: 76px;
            height: 232px;
        }

        .quick-flow {
            position: relative;
            z-index: 4;
            display: grid;
            width: min(100%, 680px);
            margin-top: auto;
            padding: 16px 20px;
            grid-template-columns: 1fr auto 1fr auto 1fr;
            align-items: center;
            gap: 14px;
            border: 1px solid rgba(255, 255, 255, 0.92);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.82);
            box-shadow:
                0 18px 38px rgba(54, 91, 142, 0.12);
            backdrop-filter: blur(10px);
        }

        .flow-item {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: 12px;
        }

        .flow-icon {
            display: grid;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            place-items: center;
            border-radius: 12px;
        }

        .flow-item:nth-child(1) .flow-icon {
            color: var(--blue-600);
            background: var(--blue-50);
        }

        .flow-item:nth-child(3) .flow-icon {
            color: var(--orange-500);
            background: var(--orange-100);
        }

        .flow-item:nth-child(5) .flow-icon {
            color: var(--green-600);
            background: var(--green-100);
        }

        .flow-icon svg {
            width: 21px;
            height: 21px;
            fill: none;
            stroke: currentColor;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-width: 1.9;
        }

        .flow-item strong {
            overflow: hidden;
            color: var(--navy-900);
            font-size: 13px;
            font-weight: 760;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .flow-arrow {
            color: var(--slate-600);
            font-size: 20px;
            font-weight: 400;
        }

        /* =========================================================
           PANEL KANAN
           ========================================================= */

        .login-panel {
            display: flex;
            min-height: 100vh;
            min-height: 100svh;
            align-items: center;
            justify-content: center;
            padding:
                clamp(38px, 5vw, 72px)
                clamp(32px, 6vw, 86px);
            background:
                radial-gradient(
                    circle at 100% 0%,
                    rgba(37, 99, 235, 0.04),
                    transparent 34%
                ),
                #ffffff;
        }

        .login-card {
            width: min(100%, 520px);
        }

        .mobile-brand {
            display: none;
            margin-bottom: 34px;
            align-items: center;
            gap: 12px;
        }

        .mobile-brand .brand-mark {
            width: 46px;
            height: 46px;
            flex-basis: 46px;
        }

        .secure-chip {
            display: inline-flex;
            min-height: 34px;
            padding: 8px 13px;
            align-items: center;
            gap: 9px;
            border-radius: 999px;
            color: #284f79;
            background: #eef3f8;
            font-size: 12px;
            font-weight: 720;
        }

        .secure-chip svg {
            width: 16px;
            height: 16px;
            fill: none;
            stroke: var(--green-600);
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-width: 2;
        }

        .login-heading {
            margin-bottom: 34px;
        }

        .login-heading h2 {
            margin: 21px 0 10px;
            color: var(--navy-900);
            font-size: clamp(36px, 4.2vw, 48px);
            font-weight: 800;
            line-height: 1.08;
            letter-spacing: -0.05em;
        }

        .login-heading p {
            margin: 0;
            color: var(--slate-600);
            font-size: 15px;
            line-height: 1.65;
        }

        /* =========================================================
           ALERT
           ========================================================= */

        .alert {
            display: grid;
            margin-bottom: 24px;
            padding: 14px 16px;
            grid-template-columns: 22px minmax(0, 1fr);
            align-items: start;
            gap: 10px;
            border: 1px solid;
            border-radius: 12px;
            font-size: 13px;
            line-height: 1.5;
        }

        .alert svg {
            width: 20px;
            height: 20px;
            fill: none;
            stroke: currentColor;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-width: 2;
        }

        .alert-success {
            border-color: #c7ead2;
            color: #23754a;
            background: #edf8f0;
        }

        .alert-error {
            border-color: #fecdd3;
            color: var(--danger-600);
            background: var(--danger-100);
        }

        .alert strong {
            display: block;
            margin-bottom: 2px;
            font-weight: 760;
        }

        .alert p {
            margin: 0;
        }

        /* =========================================================
           FORM
           ========================================================= */

        .login-form {
            display: grid;
            gap: 22px;
        }

        .field {
            display: grid;
            gap: 9px;
        }

        .field label {
            color: var(--navy-900);
            font-size: 13px;
            font-weight: 730;
        }

        .input-group {
            position: relative;
            display: flex;
            min-height: 60px;
            align-items: center;
            border: 1px solid var(--slate-300);
            border-radius: 12px;
            background: #ffffff;
            transition:
                border-color 150ms ease,
                box-shadow 150ms ease,
                background 150ms ease;
        }

        .input-group:focus-within {
            border-color: #7caaf6;
            box-shadow:
                0 0 0 4px rgba(37, 99, 235, 0.09);
        }

        .input-group.is-invalid {
            border-color: #fca5a5;
            background: #fffafb;
        }

        .input-icon {
            position: absolute;
            top: 50%;
            left: 12px;
            z-index: 2;
            display: grid;
            width: 40px;
            height: 40px;
            place-items: center;
            border-radius: 10px;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .field-email .input-icon {
            color: var(--blue-600);
            background: var(--blue-50);
        }

        .field-password .input-icon {
            color: var(--purple-500);
            background: var(--purple-100);
        }

        .input-icon svg {
            width: 20px;
            height: 20px;
            fill: none;
            stroke: currentColor;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-width: 1.9;
        }

        .input-group input {
            width: 100%;
            min-width: 0;
            height: 58px;
            padding: 0 16px 0 66px;
            border: 0;
            outline: 0;
            color: var(--navy-900);
            background: transparent;
            font-size: 15px;
        }

        .field-password .input-group input {
            padding-right: 58px;
        }

        .input-group input::placeholder {
            color: #94a3b8;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 8px;
            z-index: 3;
            display: grid;
            width: 42px;
            height: 42px;
            padding: 0;
            place-items: center;
            border: 0;
            border-radius: 10px;
            color: #60708b;
            background: transparent;
            cursor: pointer;
            transform: translateY(-50%);
            transition:
                color 150ms ease,
                background 150ms ease;
        }

        .password-toggle:hover {
            color: var(--blue-600);
            background: var(--blue-50);
        }

        .password-toggle:focus-visible {
            outline: 3px solid rgba(37, 99, 235, 0.16);
            outline-offset: -2px;
        }

        .password-toggle svg {
            width: 20px;
            height: 20px;
            fill: none;
            stroke: currentColor;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-width: 1.9;
        }

        .password-toggle .eye-off {
            display: none;
        }

        .password-toggle.is-visible .eye {
            display: none;
        }

        .password-toggle.is-visible .eye-off {
            display: block;
        }

        .field-help,
        .field-error {
            margin: 0;
            font-size: 12px;
            line-height: 1.5;
        }

        .field-help {
            color: var(--slate-500);
        }

        .field-error {
            color: var(--danger-600);
            font-weight: 600;
        }

        .login-options {
            display: flex;
            margin-top: -1px;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .checkbox {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            color: var(--slate-600);
            font-size: 13px;
            cursor: pointer;
            user-select: none;
        }

        .checkbox input {
            position: absolute;
            width: 1px;
            height: 1px;
            overflow: hidden;
            opacity: 0;
        }

        .checkbox-box {
            position: relative;
            display: grid;
            width: 20px;
            height: 20px;
            flex: 0 0 20px;
            place-items: center;
            border: 1px solid #cbd6e4;
            border-radius: 6px;
            background: #ffffff;
            transition:
                border-color 150ms ease,
                background 150ms ease,
                box-shadow 150ms ease;
        }

        .checkbox-box::after {
            width: 8px;
            height: 4px;
            margin-top: -2px;
            border-bottom: 2px solid #ffffff;
            border-left: 2px solid #ffffff;
            content: "";
            opacity: 0;
            transform: rotate(-45deg);
        }

        .checkbox input:checked + .checkbox-box {
            border-color: var(--blue-500);
            background: var(--blue-500);
        }

        .checkbox input:checked + .checkbox-box::after {
            opacity: 1;
        }

        .checkbox input:focus-visible + .checkbox-box {
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        .link {
            color: var(--blue-600);
            font-size: 13px;
            font-weight: 730;
        }

        .link:hover {
            color: var(--blue-700);
            text-decoration: underline;
            text-underline-offset: 3px;
        }

        .submit-button {
            display: inline-flex;
            width: 100%;
            min-height: 58px;
            margin-top: 2px;
            padding: 0 20px;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border: 0;
            border-radius: 12px;
            color: #ffffff;
            background:
                linear-gradient(
                    135deg,
                    #2f6ce8 0%,
                    #1f5fdd 100%
                );
            box-shadow: var(--shadow-button);
            font-size: 15px;
            font-weight: 780;
            cursor: pointer;
            transition:
                transform 150ms ease,
                box-shadow 150ms ease,
                opacity 150ms ease;
        }

        .submit-button:hover:not(:disabled) {
            box-shadow:
                0 15px 30px rgba(37, 99, 235, 0.28);
            transform: translateY(-1px);
        }

        .submit-button:focus-visible {
            outline: 4px solid rgba(37, 99, 235, 0.18);
            outline-offset: 3px;
        }

        .submit-button:disabled {
            cursor: wait;
            opacity: 0.75;
        }

        .spinner {
            display: none;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.38);
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: spin 700ms linear infinite;
        }

        .submit-button.is-loading .spinner {
            display: inline-block;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .divider {
            display: grid;
            margin: 4px 0 0;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            gap: 14px;
            color: var(--slate-600);
            font-size: 12px;
        }

        .divider::before,
        .divider::after {
            height: 1px;
            background: var(--slate-200);
            content: "";
        }

        .google-button {
            display: inline-flex;
            width: 100%;
            min-height: 58px;
            padding: 0 20px;
            align-items: center;
            justify-content: center;
            gap: 12px;
            border: 1px solid var(--slate-300);
            border-radius: 12px;
            color: var(--navy-900);
            background: #ffffff;
            font-size: 14px;
            font-weight: 740;
            cursor: pointer;
            transition:
                border-color 150ms ease,
                background 150ms ease,
                box-shadow 150ms ease,
                transform 150ms ease;
        }

        .google-button:hover {
            border-color: #b8c6d9;
            background: #f9fbfe;
            box-shadow:
                0 9px 20px rgba(31, 63, 110, 0.08);
            transform: translateY(-1px);
        }

        .google-button:focus-visible {
            outline: 4px solid rgba(37, 99, 235, 0.13);
            outline-offset: 3px;
        }

        .google-logo {
            width: 21px;
            height: 21px;
            flex: 0 0 21px;
        }

        .register-note {
            margin: 24px 0 0;
            color: var(--slate-600);
            font-size: 13px;
            line-height: 1.6;
            text-align: center;
        }

        .security-note {
            display: flex;
            margin-top: 28px;
            padding-top: 24px;
            align-items: flex-start;
            gap: 10px;
            border-top: 1px solid var(--slate-200);
            color: var(--slate-500);
        }

        .security-note svg {
            width: 18px;
            height: 18px;
            flex: 0 0 18px;
            margin-top: 1px;
            fill: none;
            stroke: #53749d;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-width: 1.8;
        }

        .security-note p {
            margin: 0;
            font-size: 12px;
            line-height: 1.55;
        }

        /* =========================================================
           RESPONSIVE
           ========================================================= */

        @media (max-width: 1250px) {
            .login-shell {
                grid-template-columns: minmax(0, 1fr) minmax(440px, 0.94fr);
            }

            .showcase-main {
                margin-top: 60px;
            }

            .showcase-main h1 {
                font-size: clamp(45px, 5vw, 58px);
            }

            .house-scene {
                right: -70px;
                width: 390px;
                opacity: 0.9;
            }

            .quick-flow {
                max-width: 620px;
            }
        }

        @media (max-width: 1020px) {
            .login-shell {
                grid-template-columns: 1fr;
            }

            .login-showcase {
                min-height: 560px;
            }

            .showcase-content {
                min-height: 560px;
                padding: 30px 38px 34px;
            }

            .showcase-main {
                max-width: 560px;
                margin-top: 52px;
            }

            .showcase-main h1 {
                max-width: 540px;
                font-size: clamp(40px, 7vw, 54px);
            }

            .showcase-description {
                max-width: 520px;
            }

            .benefits {
                display: none;
            }

            .house-scene {
                right: -25px;
                bottom: 70px;
                width: 390px;
            }

            .city-silhouette {
                bottom: 110px;
            }

            .quick-flow {
                width: min(100%, 640px);
                margin-top: auto;
            }

            .login-panel {
                min-height: auto;
                padding: 64px 28px 76px;
            }
        }

        @media (max-width: 720px) {
            .login-showcase {
                display: none;
            }

            .login-panel {
                min-height: 100vh;
                min-height: 100svh;
                align-items: flex-start;
                padding:
                    max(28px, env(safe-area-inset-top))
                    20px
                    max(36px, env(safe-area-inset-bottom));
            }

            .login-card {
                width: 100%;
            }

            .mobile-brand {
                display: flex;
            }

            .login-heading {
                margin-top: 34px;
                margin-bottom: 28px;
            }

            .login-heading h2 {
                margin-top: 18px;
                font-size: 34px;
            }

            .login-heading p {
                font-size: 14px;
            }

            .login-options {
                align-items: flex-start;
            }
        }

        @media (max-width: 430px) {
            .login-panel {
                padding-inline: 16px;
            }

            .mobile-brand {
                margin-bottom: 27px;
            }

            .login-heading h2 {
                font-size: 30px;
            }

            .login-options {
                gap: 12px;
            }

            .checkbox,
            .link {
                font-size: 12px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                scroll-behavior: auto !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>

<body>
    <main class="login-page">
        <div class="login-shell">

            {{-- =====================================================
                 PANEL KIRI
                 ===================================================== --}}
            <section class="login-showcase" aria-label="Tentang SmartVolt">
                <div class="city-silhouette" aria-hidden="true">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>

                <div class="house-scene" aria-hidden="true">
                    <svg viewBox="0 0 520 420" role="img">
                        <defs>
                            <linearGradient id="skyGlow" x1="0" y1="0" x2="1" y2="1">
                                <stop offset="0%" stop-color="#dcecff" stop-opacity="0.2"/>
                                <stop offset="100%" stop-color="#82b7f4" stop-opacity="0.55"/>
                            </linearGradient>

                            <linearGradient id="groundGradient" x1="0" y1="0" x2="1" y2="1">
                                <stop offset="0%" stop-color="#5eb8a5"/>
                                <stop offset="100%" stop-color="#2b7a75"/>
                            </linearGradient>

                            <linearGradient id="roofGradient" x1="0" y1="0" x2="1" y2="1">
                                <stop offset="0%" stop-color="#1f5fa8"/>
                                <stop offset="100%" stop-color="#123d79"/>
                            </linearGradient>

                            <linearGradient id="wallGradient" x1="0" y1="0" x2="1" y2="1">
                                <stop offset="0%" stop-color="#ffffff"/>
                                <stop offset="100%" stop-color="#d7e7f6"/>
                            </linearGradient>

                            <filter id="houseShadow" x="-30%" y="-30%" width="160%" height="160%">
                                <feDropShadow
                                    dx="0"
                                    dy="16"
                                    stdDeviation="13"
                                    flood-color="#164d83"
                                    flood-opacity="0.17"
                                />
                            </filter>
                        </defs>

                        <ellipse
                            cx="258"
                            cy="372"
                            rx="208"
                            ry="31"
                            fill="#8dbce9"
                            opacity="0.24"
                        />

                        <path
                            d="M34 356c72-52 138-48 200-20 73 33 153 28 251-28v83H34Z"
                            fill="url(#groundGradient)"
                            opacity="0.96"
                        />

                        <g filter="url(#houseShadow)">
                            <path
                                d="M112 218 254 112l145 104-25 20-120-84-116 86Z"
                                fill="url(#roofGradient)"
                            />

                            <path
                                d="M144 222h224v142H144Z"
                                fill="url(#wallGradient)"
                            />

                            <path
                                d="m222 246 74-58 79 60-19 15-60-43-58 44Z"
                                fill="#2a6db5"
                            />

                            <path
                                d="M236 250h117v114H236Z"
                                fill="#f5fbff"
                            />

                            <rect
                                x="267"
                                y="290"
                                width="48"
                                height="74"
                                rx="3"
                                fill="#173d68"
                            />

                            <rect
                                x="161"
                                y="250"
                                width="54"
                                height="49"
                                rx="3"
                                fill="#244f7f"
                            />

                            <rect
                                x="171"
                                y="260"
                                width="34"
                                height="29"
                                rx="2"
                                fill="#ffd778"
                            />

                            <path
                                d="M188 260v29M171 274h34"
                                stroke="#a66c22"
                                stroke-width="3"
                                opacity="0.7"
                            />

                            <rect
                                x="322"
                                y="257"
                                width="37"
                                height="43"
                                rx="3"
                                fill="#244f7f"
                            />

                            <rect
                                x="331"
                                y="266"
                                width="19"
                                height="25"
                                rx="2"
                                fill="#ffdc83"
                            />

                            <rect
                                x="253"
                                y="154"
                                width="44"
                                height="44"
                                rx="22"
                                fill="#f8fbff"
                                stroke="#174a83"
                                stroke-width="5"
                            />

                            <path
                                d="M275 154v44M253 176h44"
                                stroke="#174a83"
                                stroke-width="4"
                            />

                            <rect
                                x="120"
                                y="352"
                                width="279"
                                height="17"
                                rx="8"
                                fill="#2a6ba5"
                                opacity="0.78"
                            />
                        </g>

                        <g>
                            <rect
                                x="67"
                                y="286"
                                width="10"
                                height="74"
                                rx="4"
                                fill="#2f6b66"
                            />

                            <ellipse
                                cx="72"
                                cy="270"
                                rx="30"
                                ry="49"
                                fill="#3b9587"
                            />

                            <rect
                                x="421"
                                y="282"
                                width="9"
                                height="83"
                                rx="4"
                                fill="#31575f"
                            />

                            <ellipse
                                cx="425"
                                cy="260"
                                rx="31"
                                ry="58"
                                fill="#419c8b"
                            />
                        </g>

                        <g transform="translate(414 289)">
                            <rect
                                x="14"
                                y="26"
                                width="6"
                                height="75"
                                rx="3"
                                fill="#1d3657"
                            />

                            <path
                                d="M0 28h34L29 5H5Z"
                                fill="#254f7b"
                            />

                            <rect
                                x="7"
                                y="10"
                                width="20"
                                height="18"
                                rx="2"
                                fill="#ffd878"
                            />
                        </g>

                        <g fill="#2f8d79">
                            <ellipse cx="135" cy="342" rx="39" ry="24"/>
                            <ellipse cx="184" cy="347" rx="46" ry="24"/>
                            <ellipse cx="350" cy="346" rx="47" ry="25"/>
                        </g>
                    </svg>
                </div>

                <div class="showcase-content">
                    <a
                        href="{{ url('/') }}"
                        class="brand"
                        aria-label="SmartVolt"
                    >
                        <span class="brand-mark" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path
                                    d="M13.3 2.2 5.8 13h5.3l-.8 8.8L18.5 10h-5.2V2.2Z"
                                    fill="currentColor"
                                />
                            </svg>
                        </span>

                        <span class="brand-copy">
                            <strong>SmartVolt</strong>
                            <small>Solusi listrik rumah Anda</small>
                        </span>
                    </a>

                    <div class="showcase-main">
                        <p class="eyebrow">
                            Pantau dan atur listrik rumah dengan mudah
                        </p>

                        <h1>
                            Kendalikan listrik rumah dengan lebih tenang
                        </h1>

                        <p class="showcase-description">
                            Pantau pemakaian listrik, lihat perkiraan biaya,
                            dan atur perangkat rumah dari satu tempat yang
                            mudah dipahami.
                        </p>
                    </div>

                    <div class="benefits">
                        <article class="benefit">
                            <span class="benefit-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="M4 19V9"/>
                                    <path d="M9 19V5"/>
                                    <path d="M14 19v-7"/>
                                    <path d="M19 19V3"/>
                                </svg>
                            </span>

                            <div>
                                <h2>Data listrik mudah dipahami</h2>
                                <p>
                                    Lihat pemakaian listrik dan perubahan
                                    penggunaan secara lebih jelas.
                                </p>
                            </div>
                        </article>

                        <article class="benefit">
                            <span class="benefit-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <rect x="5" y="3" width="14" height="18" rx="3"/>
                                    <path d="M9 8h6"/>
                                    <path d="M9 12h6"/>
                                    <path d="M9 16h3"/>
                                </svg>
                            </span>

                            <div>
                                <h2>Perkiraan biaya lebih jelas</h2>
                                <p>
                                    Pantau perkiraan biaya listrik agar
                                    pengeluaran lebih mudah dikontrol.
                                </p>
                            </div>
                        </article>

                        <article class="benefit">
                            <span class="benefit-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="M9 3v6"/>
                                    <path d="M15 3v6"/>
                                    <path d="M7 8h10v3a5 5 0 0 1-10 0V8Z"/>
                                    <path d="M12 16v5"/>
                                </svg>
                            </span>

                            <div>
                                <h2>Kontrol perangkat lebih praktis</h2>
                                <p>
                                    Nyalakan atau matikan perangkat rumah
                                    langsung dari satu halaman.
                                </p>
                            </div>
                        </article>
                    </div>

                    <div class="quick-flow" aria-label="Alur penggunaan SmartVolt">
                        <span class="flow-item">
                            <i class="flow-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <rect x="3" y="4" width="18" height="14" rx="3"/>
                                    <path d="M8 21h8"/>
                                    <path d="M12 18v3"/>
                                </svg>
                            </i>

                            <strong>Pantau listrik</strong>
                        </span>

                        <b class="flow-arrow" aria-hidden="true">→</b>

                        <span class="flow-item">
                            <i class="flow-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="M7 3h10a2 2 0 0 1 2 2v16H5V5a2 2 0 0 1 2-2Z"/>
                                    <path d="M8 8h8"/>
                                    <path d="M8 12h8"/>
                                    <path d="M8 16h5"/>
                                </svg>
                            </i>

                            <strong>Lihat biaya</strong>
                        </span>

                        <b class="flow-arrow" aria-hidden="true">→</b>

                        <span class="flow-item">
                            <i class="flow-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="M9 3v6"/>
                                    <path d="M15 3v6"/>
                                    <path d="M7 8h10v3a5 5 0 0 1-10 0V8Z"/>
                                    <path d="M12 16v5"/>
                                </svg>
                            </i>

                            <strong>Atur perangkat</strong>
                        </span>
                    </div>
                </div>
            </section>

            {{-- =====================================================
                 PANEL KANAN
                 ===================================================== --}}
            <section class="login-panel">
                <div class="login-card">

                    <div class="mobile-brand">
                        <span class="brand-mark" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path
                                    d="M13.3 2.2 5.8 13h5.3l-.8 8.8L18.5 10h-5.2V2.2Z"
                                    fill="currentColor"
                                />
                            </svg>
                        </span>

                        <span class="brand-copy">
                            <strong>SmartVolt</strong>
                            <small>Solusi listrik rumah Anda</small>
                        </span>
                    </div>

                    <div class="login-heading">
                        <span class="secure-chip">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 3 5 6v5c0 4.6 2.7 8 7 10 4.3-2 7-5.4 7-10V6l-7-3Z"/>
                                <path d="m9 12 2 2 4-5"/>
                            </svg>

                            Akses aman SmartVolt
                        </span>

                        <h2>Selamat datang kembali</h2>

                        <p>
                            Masuk untuk memantau dan mengontrol listrik rumah Anda.
                        </p>
                    </div>

                    @if (session('status'))
                        <div
                            class="alert alert-success"
                            role="status"
                            aria-live="polite"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m5 12 4 4L19 6"/>
                            </svg>

                            <div>{{ session('status') }}</div>
                        </div>
                    @endif

                    @if (session('success'))
                        <div
                            class="alert alert-success"
                            role="status"
                            aria-live="polite"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m5 12 4 4L19 6"/>
                            </svg>

                            <div>{{ session('success') }}</div>
                        </div>
                    @endif

                    @if (session('error'))
                        <div
                            class="alert alert-error"
                            role="alert"
                            aria-live="assertive"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M12 7v6"/>
                                <path d="M12 17h.01"/>
                            </svg>

                            <div>{{ session('error') }}</div>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div
                            class="alert alert-error"
                            role="alert"
                            aria-live="assertive"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M12 7v6"/>
                                <path d="M12 17h.01"/>
                            </svg>

                            <div>
                                <strong>Belum dapat masuk.</strong>
                                <p>{{ $errors->first() }}</p>
                            </div>
                        </div>
                    @endif

                    <form
                        id="loginForm"
                        class="login-form"
                        action="{{ route('login.process') }}"
                        method="POST"
                    >
                        @csrf

                        <div class="field field-email">
                            <label for="email">
                                Alamat email
                            </label>

                            <div
                                class="input-group
                                    @error('email') is-invalid @enderror"
                            >
                                <span class="input-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24">
                                        <rect
                                            x="3"
                                            y="5"
                                            width="18"
                                            height="14"
                                            rx="3"
                                        />
                                        <path d="m5 8 7 5 7-5"/>
                                    </svg>
                                </span>

                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    placeholder="nama@email.com"
                                    autocomplete="email"
                                    inputmode="email"
                                    aria-describedby="emailHelp emailError"
                                    @error('email') aria-invalid="true" @enderror
                                    required
                                    autofocus
                                >
                            </div>

                            @error('email')
                                <p id="emailError" class="field-error">
                                    {{ $message }}
                                </p>
                            @else
                                <p id="emailHelp" class="field-help">
                                    Gunakan email yang sudah terdaftar di SmartVolt.
                                </p>
                            @enderror
                        </div>

                        <div class="field field-password">
                            <label for="password">
                                Kata sandi
                            </label>

                            <div
                                class="input-group
                                    @error('password') is-invalid @enderror"
                            >
                                <span class="input-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24">
                                        <rect
                                            x="5"
                                            y="10"
                                            width="14"
                                            height="10"
                                            rx="3"
                                        />
                                        <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                                    </svg>
                                </span>

                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    placeholder="Masukkan kata sandi"
                                    autocomplete="current-password"
                                    aria-describedby="passwordError"
                                    @error('password') aria-invalid="true" @enderror
                                    required
                                >

                                <button
                                    type="button"
                                    id="togglePassword"
                                    class="password-toggle"
                                    aria-label="Tampilkan kata sandi"
                                    aria-pressed="false"
                                >
                                    <svg
                                        class="eye"
                                        viewBox="0 0 24 24"
                                        aria-hidden="true"
                                    >
                                        <path d="M2.5 12S6 6 12 6s9.5 6 9.5 6-3.5 6-9.5 6S2.5 12 2.5 12Z"/>
                                        <circle cx="12" cy="12" r="2.5"/>
                                    </svg>

                                    <svg
                                        class="eye-off"
                                        viewBox="0 0 24 24"
                                        aria-hidden="true"
                                    >
                                        <path d="m4 4 16 16"/>
                                        <path d="M10.6 6.2A10 10 0 0 1 12 6c6 0 9.5 6 9.5 6a16 16 0 0 1-2.4 3.1"/>
                                        <path d="M6.2 7.3A15.7 15.7 0 0 0 2.5 12S6 18 12 18a9.8 9.8 0 0 0 2.1-.2"/>
                                    </svg>
                                </button>
                            </div>

                            @error('password')
                                <p id="passwordError" class="field-error">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="login-options">
                            <label class="checkbox">
                                <input
                                    type="checkbox"
                                    name="remember"
                                    value="1"
                                    {{ old('remember') ? 'checked' : '' }}
                                >

                                <span class="checkbox-box" aria-hidden="true"></span>
                                <span>Ingat saya</span>
                            </label>

                            <a
                                href="{{ route('password.request') }}"
                                class="link"
                            >
                                Lupa kata sandi?
                            </a>
                        </div>

                        <button
                            type="submit"
                            id="loginButton"
                            class="submit-button"
                        >
                            <span class="spinner" aria-hidden="true"></span>
                            <span id="loginButtonText">Masuk</span>
                        </button>

                        <div class="divider">
                            <span>atau lanjutkan dengan</span>
                        </div>

                        @if (Route::has('google.redirect'))
                            <a
                                href="{{ route('google.redirect') }}"
                                class="google-button"
                            >
                                <svg
                                    class="google-logo"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path
                                        fill="#4285F4"
                                        d="M21.6 12.23c0-.71-.06-1.4-.2-2.07H12v3.92h5.37a4.59 4.59 0 0 1-1.99 3.01v2.51h3.23c1.89-1.74 2.99-4.31 2.99-7.37Z"
                                    />
                                    <path
                                        fill="#34A853"
                                        d="M12 22c2.7 0 4.96-.9 6.61-2.4l-3.23-2.51c-.9.6-2.04.95-3.38.95-2.61 0-4.82-1.76-5.61-4.13H3.05v2.59A9.99 9.99 0 0 0 12 22Z"
                                    />
                                    <path
                                        fill="#FBBC05"
                                        d="M6.39 13.91A5.98 5.98 0 0 1 6.08 12c0-.66.11-1.3.31-1.91V7.5H3.05A10 10 0 0 0 2 12c0 1.61.38 3.13 1.05 4.5l3.34-2.59Z"
                                    />
                                    <path
                                        fill="#EA4335"
                                        d="M12 5.96c1.47 0 2.78.5 3.82 1.49l2.86-2.86C16.95 2.98 14.7 2 12 2a9.99 9.99 0 0 0-8.95 5.5l3.34 2.59C7.18 7.72 9.39 5.96 12 5.96Z"
                                    />
                                </svg>

                                <span>Masuk dengan Google</span>
                            </a>
                        @else
                            <button
                                type="button"
                                class="google-button"
                                disabled
                                title="Route Google Login belum tersedia"
                            >
                                <svg
                                    class="google-logo"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path
                                        fill="#4285F4"
                                        d="M21.6 12.23c0-.71-.06-1.4-.2-2.07H12v3.92h5.37a4.59 4.59 0 0 1-1.99 3.01v2.51h3.23c1.89-1.74 2.99-4.31 2.99-7.37Z"
                                    />
                                    <path
                                        fill="#34A853"
                                        d="M12 22c2.7 0 4.96-.9 6.61-2.4l-3.23-2.51c-.9.6-2.04.95-3.38.95-2.61 0-4.82-1.76-5.61-4.13H3.05v2.59A9.99 9.99 0 0 0 12 22Z"
                                    />
                                    <path
                                        fill="#FBBC05"
                                        d="M6.39 13.91A5.98 5.98 0 0 1 6.08 12c0-.66.11-1.3.31-1.91V7.5H3.05A10 10 0 0 0 2 12c0 1.61.38 3.13 1.05 4.5l3.34-2.59Z"
                                    />
                                    <path
                                        fill="#EA4335"
                                        d="M12 5.96c1.47 0 2.78.5 3.82 1.49l2.86-2.86C16.95 2.98 14.7 2 12 2a9.99 9.99 0 0 0-8.95 5.5l3.34 2.59C7.18 7.72 9.39 5.96 12 5.96Z"
                                    />
                                </svg>

                                <span>Masuk dengan Google</span>
                            </button>
                        @endif
                    </form>

                    <p class="register-note">
                        Belum memiliki akun?

                        <a href="{{ route('register') }}" class="link">
                            Daftar akun
                        </a>
                    </p>

                    <div class="security-note">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 3 5 6v5c0 4.6 2.7 8 7 10 4.3-2 7-5.4 7-10V6l-7-3Z"/>
                            <path d="m9 12 2 2 4-5"/>
                        </svg>

                        <p>
                            Akun Anda dilindungi untuk menjaga keamanan
                            dan privasi data.
                        </p>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const passwordInput =
                document.getElementById('password');

            const togglePassword =
                document.getElementById('togglePassword');

            if (passwordInput && togglePassword) {
                togglePassword.addEventListener('click', function () {
                    const shouldShow =
                        passwordInput.type === 'password';

                    passwordInput.type =
                        shouldShow ? 'text' : 'password';

                    togglePassword.classList.toggle(
                        'is-visible',
                        shouldShow
                    );

                    togglePassword.setAttribute(
                        'aria-label',
                        shouldShow
                            ? 'Sembunyikan kata sandi'
                            : 'Tampilkan kata sandi'
                    );

                    togglePassword.setAttribute(
                        'aria-pressed',
                        shouldShow ? 'true' : 'false'
                    );

                    passwordInput.focus();
                });
            }

            const form =
                document.getElementById('loginForm');

            const submitButton =
                document.getElementById('loginButton');

            const submitText =
                document.getElementById('loginButtonText');

            if (form && submitButton && submitText) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        form.reportValidity();
                        return;
                    }

                    submitButton.disabled = true;
                    submitButton.classList.add('is-loading');
                    submitButton.setAttribute('aria-busy', 'true');
                    submitText.textContent = 'Memeriksa akun...';
                });
            }
        });
    </script>
</body>
</html>
