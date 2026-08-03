# Kode Lengkap SmartVolt UI V5 — Colored Feature Icons

Semua file di bawah ini adalah kode utuh dan dapat menggantikan file dengan lokasi yang sama pada proyek `C:\SMARTVOLT`.

## `public/assets/css/smartvolt-app.css`

```css
/* =========================================================
   SmartVolt Application UI
   Calm Energy Control — authenticated application pages
   ========================================================= */

:root {
    --sv-bg: #f5f7fa;
    --sv-surface: #ffffff;
    --sv-surface-soft: #f8fafc;
    --sv-surface-blue: #eef4ff;
    --sv-text: #172033;
    --sv-text-strong: #101828;
    --sv-muted: #667085;
    --sv-muted-light: #98a2b3;
    --sv-border: #dde3ea;
    --sv-border-strong: #c7d1dd;
    --sv-primary: #2563eb;
    --sv-primary-dark: #1d4ed8;
    --sv-primary-soft: #eaf2ff;
    --sv-navy: #173b66;
    --sv-success: #2e7d32;
    --sv-success-soft: #eaf6ec;
    --sv-warning: #b76e00;
    --sv-warning-soft: #fff4db;
    --sv-danger: #c83c3c;
    --sv-danger-soft: #fdecec;
    --sv-info: #1769aa;
    --sv-info-soft: #eaf4fc;
    --sv-sidebar-width: 236px;
    --sv-topbar-height: 68px;
    --sv-radius-xs: 7px;
    --sv-radius-sm: 9px;
    --sv-radius: 12px;
    --sv-radius-lg: 14px;
    --sv-shadow: 0 8px 24px rgba(23, 32, 51, 0.06);
    --sv-shadow-lg: 0 18px 48px rgba(23, 32, 51, 0.10);
    --sv-focus: 0 0 0 4px rgba(37, 99, 235, 0.13);
}

*,
*::before,
*::after {
    box-sizing: border-box;
}

html {
    min-width: 320px;
    min-height: 100%;
    background: var(--sv-bg);
}

body.sv-app-body {
    min-width: 320px;
    min-height: 100vh;
    min-height: 100svh;
    margin: 0;
    color: var(--sv-text);
    background: var(--sv-bg);
    font-family:
        Inter,
        "Segoe UI",
        Roboto,
        Helvetica,
        Arial,
        sans-serif;
    -webkit-font-smoothing: antialiased;
    text-rendering: optimizeLegibility;
}

body.sv-is-locked {
    overflow: hidden;
}

button,
input,
select,
textarea {
    font: inherit;
}

button,
a,
input,
select,
textarea,
summary {
    -webkit-tap-highlight-color: transparent;
}

button {
    color: inherit;
}

a {
    color: inherit;
    text-decoration: none;
}

svg {
    display: block;
}

img {
    max-width: 100%;
}

[hidden] {
    display: none !important;
}

.sv-icon {
    flex: 0 0 auto;
    fill: none;
    stroke: currentColor;
    stroke-width: 1.75;
    stroke-linecap: round;
    stroke-linejoin: round;
}

/* App shell */

.sv-app-layout {
    min-height: 100vh;
    min-height: 100svh;
}

.sv-app-sidebar {
    position: fixed;
    inset: 0 auto 0 0;
    z-index: 60;
    display: flex;
    width: var(--sv-sidebar-width);
    flex-direction: column;
    overflow-y: auto;
    border-right: 1px solid var(--sv-border);
    background: var(--sv-surface);
}

.sv-sidebar-overlay {
    display: none;
}

.sv-sidebar-header {
    display: flex;
    min-height: var(--sv-topbar-height);
    padding: 0 18px;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid var(--sv-border);
}

.sv-brand {
    display: inline-flex;
    min-width: 0;
    align-items: center;
    gap: 11px;
}

.sv-brand-mark {
    display: grid;
    width: 40px;
    height: 40px;
    flex: 0 0 40px;
    place-items: center;
    border-radius: 11px;
    color: #ffffff;
    background: var(--sv-navy);
}

.sv-brand-copy {
    min-width: 0;
}

.sv-brand-copy strong,
.sv-brand-copy small {
    display: block;
}

.sv-brand-copy strong {
    color: var(--sv-navy);
    font-size: 18px;
    font-weight: 760;
    letter-spacing: -0.025em;
}

.sv-brand-copy small {
    margin-top: 2px;
    overflow: hidden;
    color: var(--sv-muted);
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 0.02em;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.sv-sidebar-close {
    display: none !important;
}

.sv-sidebar-nav {
    display: flex;
    padding: 20px 14px;
    flex: 1;
    flex-direction: column;
    gap: 4px;
}

.sv-nav-label {
    margin: 0 10px 7px;
    color: var(--sv-muted-light);
    font-size: 10px;
    font-weight: 760;
    letter-spacing: 0.11em;
    text-transform: uppercase;
}

.sv-nav-label-spaced {
    margin-top: 20px;
}

.sv-nav-link {
    position: relative;
    display: flex;
    min-height: 43px;
    padding: 10px 12px;
    align-items: center;
    gap: 11px;
    border-radius: var(--sv-radius-sm);
    color: #475467;
    font-size: 13px;
    font-weight: 650;
    transition:
        background-color 150ms ease,
        color 150ms ease;
}

.sv-nav-link::before {
    position: absolute;
    top: 9px;
    bottom: 9px;
    left: 0;
    width: 3px;
    border-radius: 0 4px 4px 0;
    content: "";
    opacity: 0;
    background: var(--sv-primary);
}

.sv-nav-link:hover {
    color: var(--sv-primary-dark);
    background: #f3f6fa;
}

.sv-nav-link.is-active {
    color: var(--sv-primary-dark);
    background: var(--sv-primary-soft);
}

.sv-nav-link.is-active::before {
    opacity: 1;
}

.sv-sidebar-foot {
    padding: 14px;
    border-top: 1px solid var(--sv-border);
}

.sv-data-flow {
    padding: 14px;
    border: 1px solid #d7e3f1;
    border-radius: var(--sv-radius);
    background: #f7faff;
}

.sv-data-flow span,
.sv-data-flow strong,
.sv-data-flow small {
    display: block;
}

.sv-data-flow span {
    color: var(--sv-muted);
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.07em;
    text-transform: uppercase;
}

.sv-data-flow strong {
    margin-top: 7px;
    color: var(--sv-navy);
    font-size: 12px;
    line-height: 1.45;
}

.sv-data-flow small {
    margin-top: 5px;
    color: var(--sv-muted);
    font-size: 10px;
    line-height: 1.5;
}

.sv-app-main {
    min-height: 100vh;
    min-height: 100svh;
    margin-left: var(--sv-sidebar-width);
}

.sv-app-topbar {
    position: sticky;
    top: 0;
    z-index: 45;
    display: flex;
    min-height: var(--sv-topbar-height);
    padding: 0 24px;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    border-bottom: 1px solid var(--sv-border);
    background: rgba(255, 255, 255, 0.94);
    backdrop-filter: blur(12px);
}

.sv-topbar-left,
.sv-topbar-actions {
    display: flex;
    align-items: center;
}

.sv-topbar-left {
    min-width: 0;
    gap: 12px;
}

.sv-topbar-actions {
    flex: 0 0 auto;
    gap: 9px;
}

.sv-page-title {
    margin: 0;
    overflow: hidden;
    color: var(--sv-text-strong);
    font-size: 21px;
    font-weight: 760;
    letter-spacing: -0.03em;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.sv-page-subtitle {
    margin: 3px 0 0;
    overflow: hidden;
    color: var(--sv-muted);
    font-size: 12px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.sv-page-content {
    width: min(100%, 1500px);
    margin-inline: auto;
    padding: 24px 26px 96px;
}

.sv-mobile-menu {
    display: none !important;
}

.sv-icon-button {
    position: relative;
    display: inline-grid;
    width: 40px;
    height: 40px;
    padding: 0;
    place-items: center;
    border: 1px solid var(--sv-border);
    border-radius: var(--sv-radius-sm);
    color: #475467;
    background: var(--sv-surface);
    cursor: pointer;
    transition:
        border-color 150ms ease,
        color 150ms ease,
        background-color 150ms ease;
}

.sv-icon-button:hover {
    border-color: #bdc9d8;
    color: var(--sv-primary-dark);
    background: #f8fafc;
}

.sv-icon-button:focus-visible,
.sv-profile-trigger:focus-visible,
.sv-button:focus-visible,
.sv-tab-button:focus-visible,
.sv-device-switch:focus-visible,
.sv-form-control:focus-visible,
.sv-text-button:focus-visible,
.sv-dialog-close:focus-visible {
    outline: none;
    box-shadow: var(--sv-focus);
}

/* Profile and notifications */

.sv-profile-menu,
.sv-notification-menu {
    position: relative;
}

.sv-profile-trigger {
    display: flex;
    min-height: 43px;
    padding: 5px 8px 5px 5px;
    align-items: center;
    gap: 9px;
    border: 1px solid var(--sv-border);
    border-radius: 11px;
    background: var(--sv-surface);
    cursor: pointer;
}

.sv-avatar {
    display: grid;
    width: 32px;
    height: 32px;
    place-items: center;
    border-radius: 9px;
    color: #ffffff;
    background: var(--sv-navy);
    font-size: 12px;
    font-weight: 760;
}

.sv-profile-copy {
    max-width: 145px;
    min-width: 0;
    text-align: left;
}

.sv-profile-copy strong,
.sv-profile-copy small {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.sv-profile-copy strong {
    color: var(--sv-text-strong);
    font-size: 12px;
    font-weight: 700;
}

.sv-profile-copy small {
    margin-top: 2px;
    color: var(--sv-muted);
    font-size: 10px;
}

.sv-profile-dropdown,
.sv-notification-dropdown {
    position: absolute;
    top: calc(100% + 9px);
    right: 0;
    z-index: 80;
    visibility: hidden;
    border: 1px solid var(--sv-border);
    border-radius: var(--sv-radius);
    opacity: 0;
    background: var(--sv-surface);
    box-shadow: var(--sv-shadow-lg);
    transform: translateY(-5px);
    transition:
        opacity 140ms ease,
        transform 140ms ease,
        visibility 140ms ease;
}

.sv-profile-menu.is-open .sv-profile-dropdown,
.sv-notification-menu.is-open .sv-notification-dropdown {
    visibility: visible;
    opacity: 1;
    transform: translateY(0);
}

.sv-profile-dropdown {
    width: 245px;
    padding: 7px;
}

.sv-profile-dropdown-head {
    padding: 10px 11px 12px;
    border-bottom: 1px solid var(--sv-border);
}

.sv-profile-dropdown-head strong,
.sv-profile-dropdown-head span {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.sv-profile-dropdown-head strong {
    font-size: 13px;
}

.sv-profile-dropdown-head span {
    margin-top: 3px;
    color: var(--sv-muted);
    font-size: 11px;
}

.sv-profile-dropdown a,
.sv-profile-dropdown button {
    display: flex;
    width: 100%;
    min-height: 38px;
    padding: 8px 10px;
    align-items: center;
    gap: 9px;
    border: 0;
    border-radius: 8px;
    color: #475467;
    background: transparent;
    font-size: 12px;
    font-weight: 650;
    text-align: left;
    cursor: pointer;
}

.sv-profile-dropdown a:hover,
.sv-profile-dropdown button:hover {
    color: var(--sv-primary-dark);
    background: #f3f6fa;
}

.sv-notification-count {
    position: absolute;
    top: -5px;
    right: -5px;
    display: grid;
    min-width: 18px;
    height: 18px;
    padding: 0 4px;
    place-items: center;
    border: 2px solid #ffffff;
    border-radius: 999px;
    color: #ffffff;
    background: var(--sv-danger);
    font-size: 9px;
    font-weight: 760;
}

.sv-notification-dropdown {
    width: min(380px, calc(100vw - 28px));
    overflow: hidden;
}

.sv-notification-header {
    display: flex;
    padding: 14px 15px;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    border-bottom: 1px solid var(--sv-border);
}

.sv-notification-header strong,
.sv-notification-header span {
    display: block;
}

.sv-notification-header strong {
    color: var(--sv-text-strong);
    font-size: 14px;
}

.sv-notification-header span {
    margin-top: 2px;
    color: var(--sv-muted);
    font-size: 11px;
}

.sv-text-button {
    padding: 5px 7px;
    border: 0;
    border-radius: 6px;
    color: var(--sv-primary);
    background: transparent;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
}

.sv-text-button:hover {
    background: var(--sv-primary-soft);
}

.sv-notification-list {
    max-height: 390px;
    overflow-y: auto;
}

.sv-notification-list form + form {
    border-top: 1px solid #edf0f4;
}

.sv-notification-item {
    display: grid;
    width: 100%;
    padding: 13px 15px;
    grid-template-columns: 34px minmax(0, 1fr);
    align-items: start;
    gap: 10px;
    border: 0;
    background: #ffffff;
    text-align: left;
    cursor: pointer;
}

.sv-notification-item:hover {
    background: #f8fafc;
}

.sv-notification-item.is-unread {
    background: #f5f8ff;
}

.sv-notification-icon {
    display: grid;
    width: 34px;
    height: 34px;
    place-items: center;
    border-radius: 9px;
    color: var(--sv-info);
    background: var(--sv-info-soft);
}

.sv-notification-item.is-danger .sv-notification-icon {
    color: var(--sv-danger);
    background: var(--sv-danger-soft);
}

.sv-notification-copy {
    min-width: 0;
}

.sv-notification-copy strong,
.sv-notification-copy span,
.sv-notification-copy small {
    display: block;
}

.sv-notification-copy strong {
    color: var(--sv-text-strong);
    font-size: 12px;
    font-weight: 700;
}

.sv-notification-copy span {
    margin-top: 3px;
    color: var(--sv-muted);
    font-size: 11px;
    line-height: 1.5;
}

.sv-notification-copy small {
    margin-top: 5px;
    color: var(--sv-muted-light);
    font-size: 10px;
}

.sv-notification-empty {
    display: flex;
    padding: 28px 18px;
    align-items: center;
    flex-direction: column;
    color: var(--sv-muted);
    text-align: center;
}

.sv-notification-empty strong {
    margin-top: 10px;
    color: var(--sv-text-strong);
    font-size: 13px;
}

.sv-notification-empty span {
    margin-top: 4px;
    font-size: 11px;
}

/* Alerts, buttons, forms */

.sv-alert {
    display: grid;
    margin-bottom: 18px;
    padding: 12px 14px;
    grid-template-columns: 21px minmax(0, 1fr);
    align-items: start;
    gap: 10px;
    border: 1px solid;
    border-radius: var(--sv-radius);
    font-size: 12px;
    line-height: 1.55;
}

.sv-alert strong {
    display: block;
    margin-bottom: 2px;
}

.sv-alert p {
    margin: 0;
}

.sv-alert-success {
    border-color: #cce6cf;
    color: #285f2d;
    background: var(--sv-success-soft);
}

.sv-alert-warning {
    border-color: #edd49d;
    color: #75500e;
    background: var(--sv-warning-soft);
}

.sv-alert-danger {
    border-color: #f0cbcb;
    color: #8d3030;
    background: var(--sv-danger-soft);
}

.sv-alert-info {
    border-color: #c8deef;
    color: #285d82;
    background: var(--sv-info-soft);
}

.sv-button {
    display: inline-flex;
    min-height: 40px;
    padding: 9px 14px;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border: 1px solid transparent;
    border-radius: var(--sv-radius-sm);
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition:
        background-color 150ms ease,
        border-color 150ms ease,
        color 150ms ease,
        transform 150ms ease;
}

.sv-button:hover:not(:disabled) {
    transform: translateY(-1px);
}

.sv-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.sv-button-primary {
    color: #ffffff;
    border-color: var(--sv-primary);
    background: var(--sv-primary);
}

.sv-button-primary:hover:not(:disabled) {
    border-color: var(--sv-primary-dark);
    background: var(--sv-primary-dark);
}

.sv-button-secondary {
    color: #344054;
    border-color: var(--sv-border);
    background: #ffffff;
}

.sv-button-secondary:hover:not(:disabled) {
    border-color: #bac6d4;
    background: #f8fafc;
}

.sv-button-success {
    color: #25652b;
    border-color: #bddfc1;
    background: var(--sv-success-soft);
}

.sv-button-warning {
    color: #76510e;
    border-color: #e8ce94;
    background: var(--sv-warning-soft);
}

.sv-button-danger {
    color: #982f2f;
    border-color: #ebc3c3;
    background: var(--sv-danger-soft);
}

.sv-button-ghost {
    color: var(--sv-primary-dark);
    border-color: transparent;
    background: transparent;
}

.sv-button-sm {
    min-height: 34px;
    padding: 7px 10px;
    border-radius: 8px;
    font-size: 11px;
}

.sv-button-block {
    width: 100%;
}

.sv-button-spinner {
    display: none;
    width: 15px;
    height: 15px;
    border: 2px solid rgba(255, 255, 255, 0.45);
    border-top-color: currentColor;
    border-radius: 50%;
    animation: sv-spin 700ms linear infinite;
}

.sv-button.is-loading .sv-button-spinner {
    display: inline-block;
}

@keyframes sv-spin {
    to {
        transform: rotate(360deg);
    }
}

.sv-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.sv-form-grid-three {
    grid-template-columns: repeat(3, minmax(0, 1fr));
}

.sv-form-field {
    min-width: 0;
}

.sv-form-field-full {
    grid-column: 1 / -1;
}

.sv-form-label {
    display: block;
    margin-bottom: 7px;
    color: #344054;
    font-size: 11px;
    font-weight: 700;
}

.sv-form-control {
    width: 100%;
    min-height: 43px;
    padding: 9px 11px;
    border: 1px solid var(--sv-border);
    border-radius: var(--sv-radius-sm);
    outline: none;
    color: var(--sv-text);
    background: #ffffff;
    font-size: 12px;
    transition:
        border-color 150ms ease,
        box-shadow 150ms ease,
        background-color 150ms ease;
}

textarea.sv-form-control {
    min-height: 96px;
    resize: vertical;
}

.sv-form-control:hover {
    border-color: var(--sv-border-strong);
}

.sv-form-control:focus {
    border-color: var(--sv-primary);
    box-shadow: var(--sv-focus);
}

.sv-form-control[readonly],
.sv-form-control:disabled {
    color: #667085;
    background: #f2f4f7;
    cursor: not-allowed;
}

.sv-form-control.is-invalid {
    border-color: var(--sv-danger);
    background: #fffafa;
}

.sv-form-help,
.sv-form-error {
    margin: 6px 0 0;
    font-size: 10px;
    line-height: 1.5;
}

.sv-form-help {
    color: var(--sv-muted);
}

.sv-form-error {
    color: var(--sv-danger);
    font-weight: 650;
}

.sv-form-actions {
    display: flex;
    margin-top: 4px;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.sv-checkbox {
    display: inline-flex;
    min-height: 34px;
    align-items: center;
    gap: 8px;
    color: #475467;
    font-size: 11px;
    cursor: pointer;
}

.sv-checkbox input {
    width: 17px;
    height: 17px;
    accent-color: var(--sv-primary);
}

.sv-password-wrap {
    position: relative;
}

.sv-password-wrap .sv-form-control {
    padding-right: 45px;
}

.sv-password-toggle {
    position: absolute;
    top: 50%;
    right: 5px;
    display: grid;
    width: 34px;
    height: 34px;
    padding: 0;
    place-items: center;
    border: 0;
    border-radius: 7px;
    color: var(--sv-muted);
    background: transparent;
    transform: translateY(-50%);
    cursor: pointer;
}

.sv-password-toggle:hover {
    color: var(--sv-primary);
    background: #f2f6fc;
}

/* Shared cards and sections */

.sv-page-heading {
    display: flex;
    margin-bottom: 20px;
    align-items: flex-end;
    justify-content: space-between;
    gap: 18px;
}

.sv-page-heading-copy h2 {
    margin: 0;
    color: var(--sv-text-strong);
    font-size: 24px;
    font-weight: 760;
    letter-spacing: -0.035em;
}

.sv-page-heading-copy p {
    max-width: 760px;
    margin: 6px 0 0;
    color: var(--sv-muted);
    font-size: 12px;
    line-height: 1.6;
}

.sv-page-heading-actions {
    display: flex;
    align-items: center;
    gap: 9px;
    flex-wrap: wrap;
}

.sv-card {
    border: 1px solid var(--sv-border);
    border-radius: var(--sv-radius-lg);
    background: var(--sv-surface);
    box-shadow: var(--sv-shadow);
}

.sv-card-flat {
    box-shadow: none;
}

.sv-card-header {
    display: flex;
    padding: 18px 19px;
    align-items: flex-start;
    justify-content: space-between;
    gap: 14px;
    border-bottom: 1px solid #e8ecf1;
}

.sv-card-header h3,
.sv-card-header h4 {
    margin: 0;
    color: var(--sv-text-strong);
    font-size: 15px;
    font-weight: 740;
    letter-spacing: -0.02em;
}

.sv-card-header p {
    margin: 5px 0 0;
    color: var(--sv-muted);
    font-size: 11px;
    line-height: 1.5;
}

.sv-card-body {
    padding: 19px;
}

.sv-section-grid {
    display: grid;
    gap: 18px;
}

.sv-section-grid-two {
    grid-template-columns: minmax(0, 1.45fr) minmax(320px, 0.75fr);
}

.sv-section-grid-equal {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.sv-badge {
    display: inline-flex;
    min-height: 25px;
    padding: 4px 8px;
    align-items: center;
    gap: 6px;
    border: 1px solid;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 700;
    line-height: 1;
    white-space: nowrap;
}

.sv-badge::before {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    content: "";
    background: currentColor;
}

.sv-badge-success {
    border-color: #c3e1c7;
    color: var(--sv-success);
    background: var(--sv-success-soft);
}

.sv-badge-warning {
    border-color: #ead09a;
    color: var(--sv-warning);
    background: var(--sv-warning-soft);
}

.sv-badge-danger {
    border-color: #edc4c4;
    color: var(--sv-danger);
    background: var(--sv-danger-soft);
}

.sv-badge-neutral {
    border-color: var(--sv-border);
    color: #667085;
    background: #f8fafc;
}

.sv-empty-state {
    display: flex;
    min-height: 170px;
    padding: 28px;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    border: 1px dashed #cbd4df;
    border-radius: var(--sv-radius);
    color: var(--sv-muted);
    background: #fafbfc;
    text-align: center;
}

.sv-empty-state .sv-icon {
    color: #7d91a8;
}

.sv-empty-state strong {
    margin-top: 12px;
    color: var(--sv-text-strong);
    font-size: 13px;
}

.sv-empty-state p {
    max-width: 430px;
    margin: 6px 0 0;
    font-size: 11px;
    line-height: 1.55;
}

.sv-empty-state .sv-button {
    margin-top: 14px;
}

/* Dashboard */

.sv-dashboard-intro {
    display: flex;
    margin-bottom: 18px;
    padding: 17px 19px;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    border: 1px solid #d8e4f2;
    border-radius: var(--sv-radius-lg);
    background: #f8fbff;
}

.sv-dashboard-intro h2 {
    margin: 0;
    color: var(--sv-navy);
    font-size: 18px;
    font-weight: 750;
    letter-spacing: -0.025em;
}

.sv-dashboard-intro p {
    margin: 5px 0 0;
    color: var(--sv-muted);
    font-size: 11px;
    line-height: 1.55;
}

.sv-dashboard-intro-icon {
    display: grid;
    width: 44px;
    height: 44px;
    flex: 0 0 44px;
    place-items: center;
    border-radius: 12px;
    color: var(--sv-primary);
    background: var(--sv-primary-soft);
}

.sv-dashboard-metrics {
    display: grid;
    margin-bottom: 18px;
    grid-template-columns: minmax(0, 1.7fr) repeat(3, minmax(170px, 0.72fr));
    gap: 14px;
}

.sv-power-card {
    position: relative;
    display: grid;
    min-height: 200px;
    padding: 21px;
    grid-template-columns: minmax(0, 1fr) 118px;
    gap: 20px;
    overflow: hidden;
    border: 1px solid #cddbf0;
    border-radius: var(--sv-radius-lg);
    background: #ffffff;
    box-shadow: var(--sv-shadow);
}

.sv-power-card::after {
    position: absolute;
    right: -45px;
    bottom: -75px;
    width: 180px;
    height: 180px;
    border: 22px solid #eff5ff;
    border-radius: 50%;
    content: "";
    pointer-events: none;
}

.sv-power-card-copy {
    position: relative;
    z-index: 1;
    min-width: 0;
}

.sv-metric-label {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--sv-muted);
    font-size: 11px;
    font-weight: 700;
}

.sv-power-value {
    display: flex;
    margin-top: 13px;
    align-items: baseline;
    gap: 8px;
    color: var(--sv-text-strong);
    font-size: clamp(38px, 5vw, 56px);
    font-weight: 780;
    letter-spacing: -0.06em;
    line-height: 1;
}

.sv-power-value small {
    color: var(--sv-muted);
    font-size: 16px;
    font-weight: 650;
    letter-spacing: 0;
}

.sv-power-meta {
    display: flex;
    margin-top: 14px;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.sv-power-meta span {
    color: var(--sv-muted);
    font-size: 10px;
}

.sv-load-block {
    margin-top: 18px;
}

.sv-load-head {
    display: flex;
    margin-bottom: 7px;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    color: var(--sv-muted);
    font-size: 10px;
}

.sv-load-head strong {
    color: #344054;
    font-size: 11px;
}

.sv-progress {
    height: 8px;
    overflow: hidden;
    border-radius: 999px;
    background: #e9eef5;
}

.sv-progress > span {
    display: block;
    width: var(--sv-progress, 0%);
    max-width: 100%;
    height: 100%;
    border-radius: inherit;
    background: var(--sv-primary);
}

.sv-progress.is-warning > span {
    background: #d48a17;
}

.sv-progress.is-danger > span {
    background: var(--sv-danger);
}

.sv-power-visual {
    position: relative;
    z-index: 1;
    display: grid;
    place-items: center;
}

.sv-power-ring {
    display: grid;
    width: 106px;
    height: 106px;
    place-items: center;
    border: 10px solid #edf2f8;
    border-top-color: var(--sv-primary);
    border-radius: 50%;
    color: var(--sv-primary);
    background: #ffffff;
}

.sv-power-ring strong {
    display: block;
    color: var(--sv-text-strong);
    font-size: 17px;
    text-align: center;
}

.sv-power-ring small {
    display: block;
    margin-top: 2px;
    color: var(--sv-muted);
    font-size: 9px;
    text-align: center;
}

.sv-support-metric {
    display: flex;
    min-height: 200px;
    padding: 18px;
    flex-direction: column;
    border: 1px solid var(--sv-border);
    border-radius: var(--sv-radius-lg);
    background: var(--sv-surface);
    box-shadow: var(--sv-shadow);
}

.sv-support-metric-icon {
    display: grid;
    width: 38px;
    height: 38px;
    place-items: center;
    border-radius: 10px;
    color: var(--sv-primary);
    background: var(--sv-primary-soft);
}

.sv-support-metric.is-success .sv-support-metric-icon {
    color: var(--sv-success);
    background: var(--sv-success-soft);
}

.sv-support-metric.is-warning .sv-support-metric-icon {
    color: var(--sv-warning);
    background: var(--sv-warning-soft);
}

.sv-support-metric-label {
    margin-top: 17px;
    color: var(--sv-muted);
    font-size: 10px;
    font-weight: 700;
}

.sv-support-metric-value {
    display: flex;
    margin-top: 7px;
    align-items: baseline;
    gap: 6px;
    color: var(--sv-text-strong);
    font-size: 25px;
    font-weight: 760;
    letter-spacing: -0.035em;
}

.sv-support-metric-value small {
    color: var(--sv-muted);
    font-size: 11px;
    font-weight: 650;
    letter-spacing: 0;
}

.sv-support-metric-foot {
    margin-top: auto;
    padding-top: 14px;
    color: var(--sv-muted);
    font-size: 10px;
    line-height: 1.5;
}

.sv-system-strip {
    display: grid;
    margin-bottom: 18px;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    border: 1px solid var(--sv-border);
    border-radius: var(--sv-radius-lg);
    background: var(--sv-surface);
    box-shadow: var(--sv-shadow);
}

.sv-system-item {
    display: grid;
    min-width: 0;
    padding: 15px 17px;
    grid-template-columns: 34px minmax(0, 1fr);
    align-items: center;
    gap: 10px;
}

.sv-system-item + .sv-system-item {
    border-left: 1px solid var(--sv-border);
}

.sv-system-icon {
    display: grid;
    width: 34px;
    height: 34px;
    place-items: center;
    border-radius: 9px;
    color: var(--sv-primary);
    background: var(--sv-primary-soft);
}

.sv-system-item.is-success .sv-system-icon {
    color: var(--sv-success);
    background: var(--sv-success-soft);
}

.sv-system-item.is-warning .sv-system-icon {
    color: var(--sv-warning);
    background: var(--sv-warning-soft);
}

.sv-system-item.is-danger .sv-system-icon {
    color: var(--sv-danger);
    background: var(--sv-danger-soft);
}

.sv-system-copy {
    min-width: 0;
}

.sv-system-copy span,
.sv-system-copy strong {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.sv-system-copy span {
    color: var(--sv-muted);
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.sv-system-copy strong {
    margin-top: 4px;
    color: var(--sv-text-strong);
    font-size: 11px;
}

.sv-chart-container {
    position: relative;
    height: 310px;
}

.sv-chart-container canvas {
    width: 100% !important;
    height: 100% !important;
}

.sv-chart-summary {
    display: flex;
    gap: 18px;
    flex-wrap: wrap;
}

.sv-chart-summary div {
    min-width: 82px;
}

.sv-chart-summary span,
.sv-chart-summary strong {
    display: block;
}

.sv-chart-summary span {
    color: var(--sv-muted);
    font-size: 9px;
    text-transform: uppercase;
}

.sv-chart-summary strong {
    margin-top: 4px;
    color: var(--sv-text-strong);
    font-size: 13px;
}

.sv-room-control-list {
    display: grid;
    gap: 11px;
}

.sv-room-control {
    overflow: hidden;
    border: 1px solid var(--sv-border);
    border-radius: var(--sv-radius);
    background: #ffffff;
}

.sv-room-control summary {
    display: flex;
    min-height: 57px;
    padding: 12px 14px;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    list-style: none;
    cursor: pointer;
}

.sv-room-control summary::-webkit-details-marker {
    display: none;
}

.sv-room-summary-main {
    display: flex;
    min-width: 0;
    align-items: center;
    gap: 10px;
}

.sv-room-icon {
    display: grid;
    width: 35px;
    height: 35px;
    flex: 0 0 35px;
    place-items: center;
    border-radius: 9px;
    color: var(--sv-primary);
    background: var(--sv-primary-soft);
}

.sv-room-summary-copy {
    min-width: 0;
}

.sv-room-summary-copy strong,
.sv-room-summary-copy span {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.sv-room-summary-copy strong {
    color: var(--sv-text-strong);
    font-size: 12px;
}

.sv-room-summary-copy span {
    margin-top: 3px;
    color: var(--sv-muted);
    font-size: 10px;
}

.sv-room-chevron {
    color: var(--sv-muted);
    transition: transform 150ms ease;
}

.sv-room-control[open] .sv-room-chevron {
    transform: rotate(90deg);
}

.sv-room-devices {
    display: grid;
    padding: 0 13px 13px;
    gap: 8px;
}

.sv-device-row {
    display: grid;
    min-height: 53px;
    padding: 9px 10px;
    grid-template-columns: minmax(0, 1fr) auto;
    align-items: center;
    gap: 12px;
    border: 1px solid #e8ecf1;
    border-radius: 10px;
    background: #fbfcfd;
}

.sv-device-main {
    display: flex;
    min-width: 0;
    align-items: center;
    gap: 9px;
}

.sv-device-icon {
    display: grid;
    width: 32px;
    height: 32px;
    flex: 0 0 32px;
    place-items: center;
    border-radius: 8px;
    color: #526b86;
    background: #edf2f7;
}

.sv-device-copy {
    min-width: 0;
}

.sv-device-copy strong,
.sv-device-copy span {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.sv-device-copy strong {
    color: var(--sv-text-strong);
    font-size: 11px;
}

.sv-device-copy span {
    margin-top: 3px;
    color: var(--sv-muted);
    font-size: 9px;
}

.sv-device-switch {
    position: relative;
    display: inline-flex;
    width: 76px;
    min-height: 34px;
    padding: 4px 9px 4px 27px;
    align-items: center;
    justify-content: center;
    border: 1px solid #cbd4df;
    border-radius: 999px;
    color: #667085;
    background: #f2f4f7;
    font-size: 9px;
    font-weight: 760;
    cursor: pointer;
    transition:
        background-color 150ms ease,
        border-color 150ms ease,
        color 150ms ease;
}

.sv-device-switch::before {
    position: absolute;
    top: 50%;
    left: 5px;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    content: "";
    background: #ffffff;
    box-shadow: 0 1px 4px rgba(23, 32, 51, 0.20);
    transform: translateY(-50%);
    transition: left 150ms ease;
}

.sv-device-switch.is-on {
    padding-right: 27px;
    padding-left: 9px;
    border-color: #94cca0;
    color: #25652b;
    background: #dff2e3;
}

.sv-device-switch.is-on::before {
    left: calc(100% - 27px);
}

.sv-device-switch.is-loading {
    color: var(--sv-primary-dark);
    border-color: #b8ccef;
    background: #e8f0ff;
}

.sv-device-switch.is-offline {
    color: #8a5a0b;
    border-color: #e3c887;
    background: var(--sv-warning-soft);
}

.sv-device-switch:disabled {
    cursor: not-allowed;
    opacity: 0.78;
}

.sv-table-wrap {
    overflow-x: auto;
    border: 1px solid var(--sv-border);
    border-radius: var(--sv-radius);
}

.sv-table {
    width: 100%;
    min-width: 760px;
    border-collapse: collapse;
    background: #ffffff;
}

.sv-table th,
.sv-table td {
    padding: 11px 13px;
    border-bottom: 1px solid #e9edf2;
    color: #475467;
    font-size: 10px;
    text-align: left;
    vertical-align: middle;
    white-space: nowrap;
}

.sv-table th {
    position: sticky;
    top: 0;
    z-index: 1;
    color: #667085;
    background: #f8fafc;
    font-size: 9px;
    font-weight: 760;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.sv-table tr:last-child td {
    border-bottom: 0;
}

.sv-table tbody tr:hover {
    background: #fafcff;
}

.sv-table .is-numeric {
    text-align: right;
    font-variant-numeric: tabular-nums;
}

.sv-billing-list {
    display: grid;
    gap: 9px;
}

.sv-billing-item {
    overflow: hidden;
    border: 1px solid var(--sv-border);
    border-radius: 10px;
}

.sv-billing-item summary {
    display: flex;
    min-height: 48px;
    padding: 10px 12px;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    list-style: none;
    cursor: pointer;
}

.sv-billing-item summary::-webkit-details-marker {
    display: none;
}

.sv-billing-item summary strong {
    color: var(--sv-text-strong);
    font-size: 11px;
}

.sv-billing-item summary span {
    color: var(--sv-primary-dark);
    font-size: 12px;
    font-weight: 760;
}

.sv-billing-details {
    padding: 12px;
    border-top: 1px solid var(--sv-border);
    background: #fafbfc;
}

.sv-billing-details dl {
    display: grid;
    margin: 0;
    grid-template-columns: 1fr auto;
    gap: 8px 16px;
}

.sv-billing-details dt,
.sv-billing-details dd {
    margin: 0;
    font-size: 10px;
}

.sv-billing-details dt {
    color: var(--sv-muted);
}

.sv-billing-details dd {
    color: var(--sv-text-strong);
    font-weight: 650;
    text-align: right;
}

/* Rooms page */

.sv-room-summary-grid {
    display: grid;
    margin-bottom: 18px;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 13px;
}

.sv-summary-card {
    display: flex;
    min-height: 104px;
    padding: 16px;
    align-items: center;
    gap: 13px;
    border: 1px solid var(--sv-border);
    border-radius: var(--sv-radius);
    background: #ffffff;
}

.sv-summary-icon {
    display: grid;
    width: 40px;
    height: 40px;
    flex: 0 0 40px;
    place-items: center;
    border-radius: 10px;
    color: var(--sv-primary);
    background: var(--sv-primary-soft);
}

.sv-summary-copy span,
.sv-summary-copy strong {
    display: block;
}

.sv-summary-copy span {
    color: var(--sv-muted);
    font-size: 10px;
}

.sv-summary-copy strong {
    margin-top: 4px;
    color: var(--sv-text-strong);
    font-size: 21px;
    letter-spacing: -0.03em;
}

.sv-room-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 15px;
}

.sv-room-card {
    overflow: hidden;
    border: 1px solid var(--sv-border);
    border-radius: var(--sv-radius-lg);
    background: #ffffff;
    box-shadow: var(--sv-shadow);
}

.sv-room-card-head {
    display: flex;
    padding: 17px;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    border-bottom: 1px solid #e8ecf1;
}

.sv-room-card-title {
    display: flex;
    min-width: 0;
    align-items: center;
    gap: 11px;
}

.sv-room-card-title h3 {
    margin: 0;
    overflow: hidden;
    color: var(--sv-text-strong);
    font-size: 14px;
    font-weight: 740;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.sv-room-card-title p {
    margin: 4px 0 0;
    color: var(--sv-muted);
    font-size: 10px;
}

.sv-room-card-body {
    padding: 14px;
}

.sv-room-card .sv-device-row + .sv-device-row {
    margin-top: 8px;
}

.sv-room-card-foot {
    display: flex;
    padding: 11px 14px;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    border-top: 1px solid #e8ecf1;
    color: var(--sv-muted);
    background: #fafbfc;
    font-size: 9px;
}

/* Energy history */

.sv-filter-card {
    margin-bottom: 16px;
}

.sv-filter-grid {
    display: grid;
    grid-template-columns: minmax(190px, 1.2fr) repeat(2, minmax(150px, 0.8fr)) auto;
    align-items: end;
    gap: 12px;
}

.sv-quick-filters {
    display: flex;
    margin-top: 12px;
    gap: 7px;
    flex-wrap: wrap;
}

.sv-quick-filter {
    min-height: 31px;
    padding: 6px 10px;
    border: 1px solid var(--sv-border);
    border-radius: 999px;
    color: #475467;
    background: #ffffff;
    font-size: 10px;
    font-weight: 650;
    cursor: pointer;
}

.sv-quick-filter:hover {
    color: var(--sv-primary-dark);
    border-color: #b9c9dd;
    background: #f6f9fd;
}

.sv-history-metrics {
    display: grid;
    margin-bottom: 16px;
    grid-template-columns: 1.25fr repeat(3, minmax(0, 1fr));
    gap: 13px;
}

.sv-history-metric {
    padding: 16px;
    border: 1px solid var(--sv-border);
    border-radius: var(--sv-radius);
    background: #ffffff;
}

.sv-history-metric.is-primary {
    border-color: #cad9ef;
    background: #f8fbff;
}

.sv-history-metric span,
.sv-history-metric strong,
.sv-history-metric small {
    display: block;
}

.sv-history-metric span {
    color: var(--sv-muted);
    font-size: 10px;
    font-weight: 700;
}

.sv-history-metric strong {
    margin-top: 7px;
    color: var(--sv-text-strong);
    font-size: 23px;
    letter-spacing: -0.035em;
}

.sv-history-metric small {
    margin-top: 6px;
    color: var(--sv-muted);
    font-size: 9px;
    line-height: 1.45;
}

.sv-chart-tabs {
    display: inline-flex;
    padding: 3px;
    border: 1px solid var(--sv-border);
    border-radius: 9px;
    background: #f7f9fb;
}

.sv-chart-tab {
    min-height: 30px;
    padding: 6px 10px;
    border: 0;
    border-radius: 7px;
    color: var(--sv-muted);
    background: transparent;
    font-size: 10px;
    font-weight: 700;
    cursor: pointer;
}

.sv-chart-tab.is-active {
    color: var(--sv-primary-dark);
    background: #ffffff;
    box-shadow: 0 1px 4px rgba(23, 32, 51, 0.08);
}

.sv-pagination {
    margin-top: 15px;
}

.sv-pagination nav > div:first-child {
    display: none;
}

.sv-pagination nav > div:last-child {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.sv-pagination nav p {
    color: var(--sv-muted);
    font-size: 10px;
}

.sv-pagination nav span,
.sv-pagination nav a {
    border-color: var(--sv-border) !important;
    color: #475467 !important;
    background: #ffffff !important;
    font-size: 10px !important;
}

.sv-pagination nav span[aria-current="page"] span {
    color: #ffffff !important;
    border-color: var(--sv-primary) !important;
    background: var(--sv-primary) !important;
}

/* Settings */

.sv-settings-layout {
    display: grid;
    grid-template-columns: 210px minmax(0, 1fr);
    align-items: start;
    gap: 18px;
}

.sv-settings-nav {
    position: sticky;
    top: calc(var(--sv-topbar-height) + 18px);
    padding: 7px;
    border: 1px solid var(--sv-border);
    border-radius: var(--sv-radius);
    background: #ffffff;
    box-shadow: var(--sv-shadow);
}

.sv-tab-button {
    display: flex;
    width: 100%;
    min-height: 41px;
    padding: 9px 10px;
    align-items: center;
    gap: 9px;
    border: 0;
    border-radius: 8px;
    color: #475467;
    background: transparent;
    font-size: 11px;
    font-weight: 650;
    text-align: left;
    cursor: pointer;
}

.sv-tab-button:hover {
    color: var(--sv-primary-dark);
    background: #f5f8fc;
}

.sv-tab-button.is-active {
    color: var(--sv-primary-dark);
    background: var(--sv-primary-soft);
}

.sv-settings-panels {
    min-width: 0;
}

.sv-tab-panel {
    display: none;
}

.sv-tab-panel.is-active {
    display: block;
}

.sv-settings-card + .sv-settings-card {
    margin-top: 16px;
}

.sv-profile-summary {
    display: flex;
    padding: 18px 19px;
    align-items: center;
    gap: 14px;
    border-bottom: 1px solid var(--sv-border);
}

.sv-profile-avatar-large {
    display: grid;
    width: 54px;
    height: 54px;
    flex: 0 0 54px;
    place-items: center;
    border-radius: 15px;
    color: #ffffff;
    background: var(--sv-navy);
    font-size: 20px;
    font-weight: 760;
}

.sv-profile-summary h3 {
    margin: 0;
    color: var(--sv-text-strong);
    font-size: 15px;
}

.sv-profile-summary p {
    margin: 4px 0 0;
    color: var(--sv-muted);
    font-size: 11px;
}

.sv-security-note {
    display: grid;
    padding: 14px;
    grid-template-columns: 35px minmax(0, 1fr);
    gap: 10px;
    border: 1px solid #cbdced;
    border-radius: 10px;
    color: #345b7a;
    background: #f3f8fc;
}

.sv-security-note-icon {
    display: grid;
    width: 35px;
    height: 35px;
    place-items: center;
    border-radius: 9px;
    color: var(--sv-info);
    background: #e2f0fb;
}

.sv-security-note strong {
    display: block;
    font-size: 11px;
}

.sv-security-note p {
    margin: 4px 0 0;
    font-size: 10px;
    line-height: 1.5;
}

.sv-technician-lock {
    display: grid;
    min-height: 330px;
    padding: 38px;
    place-items: center;
    border: 1px solid var(--sv-border);
    border-radius: var(--sv-radius-lg);
    background: #ffffff;
    text-align: center;
}

.sv-technician-lock-icon {
    display: grid;
    width: 62px;
    height: 62px;
    place-items: center;
    border-radius: 17px;
    color: var(--sv-warning);
    background: var(--sv-warning-soft);
}

.sv-technician-lock h3 {
    margin: 16px 0 0;
    color: var(--sv-text-strong);
    font-size: 18px;
}

.sv-technician-lock p {
    max-width: 500px;
    margin: 8px auto 0;
    color: var(--sv-muted);
    font-size: 11px;
    line-height: 1.6;
}

.sv-technician-lock .sv-button {
    margin-top: 17px;
}

.sv-technician-toolbar {
    display: flex;
    margin-bottom: 15px;
    padding: 14px 16px;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    border: 1px solid #cbdced;
    border-radius: var(--sv-radius);
    background: #f7fbff;
}

.sv-technician-toolbar strong {
    display: block;
    color: var(--sv-navy);
    font-size: 12px;
}

.sv-technician-toolbar span {
    display: block;
    margin-top: 3px;
    color: var(--sv-muted);
    font-size: 10px;
}

.sv-technician-summary {
    display: grid;
    margin-bottom: 15px;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 11px;
}

.sv-technician-summary-card {
    padding: 14px;
    border: 1px solid var(--sv-border);
    border-radius: 10px;
    background: #ffffff;
}

.sv-technician-summary-card span,
.sv-technician-summary-card strong {
    display: block;
}

.sv-technician-summary-card span {
    color: var(--sv-muted);
    font-size: 9px;
}

.sv-technician-summary-card strong {
    margin-top: 5px;
    color: var(--sv-text-strong);
    font-size: 20px;
}

.sv-tech-room {
    overflow: hidden;
    border: 1px solid var(--sv-border);
    border-radius: var(--sv-radius);
    background: #ffffff;
}

.sv-tech-room + .sv-tech-room {
    margin-top: 11px;
}

.sv-tech-room > summary {
    display: flex;
    min-height: 59px;
    padding: 13px 15px;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    list-style: none;
    cursor: pointer;
}

.sv-tech-room > summary::-webkit-details-marker {
    display: none;
}

.sv-tech-room-heading {
    display: flex;
    min-width: 0;
    align-items: center;
    gap: 10px;
}

.sv-tech-room-heading strong,
.sv-tech-room-heading span {
    display: block;
}

.sv-tech-room-heading strong {
    color: var(--sv-text-strong);
    font-size: 12px;
}

.sv-tech-room-heading span {
    margin-top: 3px;
    color: var(--sv-muted);
    font-size: 9px;
}

.sv-tech-room-content {
    padding: 14px;
    border-top: 1px solid var(--sv-border);
    background: #fafbfc;
}

.sv-tech-subsection {
    padding: 14px;
    border: 1px solid #e2e7ed;
    border-radius: 10px;
    background: #ffffff;
}

.sv-tech-subsection + .sv-tech-subsection {
    margin-top: 11px;
}

.sv-tech-subsection-head {
    display: flex;
    margin-bottom: 12px;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.sv-tech-subsection-head h4 {
    margin: 0;
    color: var(--sv-text-strong);
    font-size: 12px;
}

.sv-tech-item-list {
    display: grid;
    gap: 8px;
}

.sv-tech-item {
    padding: 11px;
    border: 1px solid #e4e9ef;
    border-radius: 9px;
    background: #fbfcfd;
}

.sv-tech-item-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
}

.sv-tech-item-copy {
    min-width: 0;
}

.sv-tech-item-copy strong,
.sv-tech-item-copy span {
    display: block;
}

.sv-tech-item-copy strong {
    overflow: hidden;
    color: var(--sv-text-strong);
    font-size: 11px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.sv-tech-item-copy span {
    margin-top: 3px;
    color: var(--sv-muted);
    font-size: 9px;
    line-height: 1.45;
}

.sv-tech-actions {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.sv-tech-edit {
    margin-top: 10px;
    padding-top: 11px;
    border-top: 1px solid #e4e9ef;
}

.sv-tech-edit > summary {
    display: inline-flex;
    min-height: 31px;
    padding: 6px 9px;
    align-items: center;
    gap: 6px;
    border: 1px solid var(--sv-border);
    border-radius: 7px;
    color: #475467;
    background: #ffffff;
    font-size: 10px;
    font-weight: 650;
    list-style: none;
    cursor: pointer;
}

.sv-tech-edit > summary::-webkit-details-marker {
    display: none;
}

.sv-tech-edit[open] > summary {
    color: var(--sv-primary-dark);
    border-color: #bfd0e8;
    background: #f3f7fd;
}

.sv-tech-edit-body {
    margin-top: 11px;
}

.sv-relay-name-fields {
    display: grid;
    margin-top: 12px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
}

/* Dialogs */

.sv-dialog {
    width: min(520px, calc(100vw - 28px));
    max-height: calc(100vh - 36px);
    padding: 0;
    overflow: auto;
    border: 1px solid var(--sv-border);
    border-radius: var(--sv-radius-lg);
    color: var(--sv-text);
    background: #ffffff;
    box-shadow: var(--sv-shadow-lg);
}

.sv-dialog::backdrop {
    background: rgba(16, 24, 40, 0.46);
    backdrop-filter: blur(2px);
}

.sv-dialog-header {
    display: flex;
    padding: 17px 18px;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    border-bottom: 1px solid var(--sv-border);
}

.sv-dialog-header h3 {
    margin: 0;
    color: var(--sv-text-strong);
    font-size: 15px;
}

.sv-dialog-header p {
    margin: 5px 0 0;
    color: var(--sv-muted);
    font-size: 10px;
    line-height: 1.5;
}

.sv-dialog-close {
    display: grid;
    width: 34px;
    height: 34px;
    padding: 0;
    place-items: center;
    border: 1px solid var(--sv-border);
    border-radius: 8px;
    color: var(--sv-muted);
    background: #ffffff;
    cursor: pointer;
}

.sv-dialog-body {
    padding: 18px;
}

.sv-dialog-actions {
    display: flex;
    margin-top: 16px;
    align-items: center;
    justify-content: flex-end;
    gap: 9px;
}

/* Toasts */

.sv-toast-region {
    position: fixed;
    right: 18px;
    bottom: 18px;
    z-index: 120;
    display: grid;
    width: min(360px, calc(100vw - 36px));
    gap: 9px;
    pointer-events: none;
}

.sv-toast {
    display: grid;
    padding: 12px 13px;
    grid-template-columns: 20px minmax(0, 1fr) auto;
    align-items: start;
    gap: 9px;
    border: 1px solid var(--sv-border);
    border-radius: 10px;
    opacity: 0;
    background: #ffffff;
    box-shadow: var(--sv-shadow-lg);
    transform: translateY(8px);
    transition:
        opacity 160ms ease,
        transform 160ms ease;
    pointer-events: auto;
}

.sv-toast.is-visible {
    opacity: 1;
    transform: translateY(0);
}

.sv-toast.is-success {
    border-color: #c7e2ca;
}

.sv-toast.is-error {
    border-color: #edc7c7;
}

.sv-toast.is-warning {
    border-color: #ead19b;
}

.sv-toast-copy strong {
    display: block;
    color: var(--sv-text-strong);
    font-size: 11px;
}

.sv-toast-copy span {
    display: block;
    margin-top: 3px;
    color: var(--sv-muted);
    font-size: 10px;
    line-height: 1.45;
}

.sv-toast-close {
    display: grid;
    width: 25px;
    height: 25px;
    padding: 0;
    place-items: center;
    border: 0;
    border-radius: 6px;
    color: var(--sv-muted);
    background: transparent;
    cursor: pointer;
}

/* Bottom navigation */

.sv-bottom-navigation {
    display: none;
}

/* Utility */

.sv-text-muted {
    color: var(--sv-muted);
}

.sv-text-success {
    color: var(--sv-success);
}

.sv-text-warning {
    color: var(--sv-warning);
}

.sv-text-danger {
    color: var(--sv-danger);
}

.sv-text-right {
    text-align: right;
}

.sv-stack {
    display: grid;
    gap: 14px;
}

.sv-inline {
    display: flex;
    align-items: center;
    gap: 9px;
    flex-wrap: wrap;
}

.sv-divider {
    height: 1px;
    margin: 16px 0;
    background: var(--sv-border);
}

/* Responsive */

@media (max-width: 1220px) {
    .sv-dashboard-metrics {
        grid-template-columns: minmax(0, 1.5fr) repeat(2, minmax(180px, 0.75fr));
    }

    .sv-support-metric:last-child {
        grid-column: 2 / 4;
        min-height: 150px;
    }

    .sv-section-grid-two {
        grid-template-columns: 1fr;
    }

    .sv-room-summary-grid,
    .sv-history-metrics {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 980px) {
    .sv-app-sidebar {
        width: min(290px, calc(100vw - 54px));
        transform: translateX(-102%);
        transition: transform 180ms ease;
    }

    body.sv-sidebar-open .sv-app-sidebar {
        transform: translateX(0);
    }

    .sv-sidebar-overlay {
        position: fixed;
        inset: 0;
        z-index: 55;
        display: block;
        visibility: hidden;
        opacity: 0;
        background: rgba(16, 24, 40, 0.38);
        transition:
            opacity 180ms ease,
            visibility 180ms ease;
    }

    body.sv-sidebar-open .sv-sidebar-overlay {
        visibility: visible;
        opacity: 1;
    }

    .sv-sidebar-close,
    .sv-mobile-menu {
        display: inline-grid !important;
    }

    .sv-app-main {
        margin-left: 0;
    }

    .sv-app-topbar {
        padding-inline: 18px;
    }

    .sv-page-content {
        padding: 20px 18px 92px;
    }

    .sv-bottom-navigation {
        position: fixed;
        right: 0;
        bottom: 0;
        left: 0;
        z-index: 50;
        display: grid;
        min-height: 66px;
        padding: 7px max(8px, env(safe-area-inset-right)) max(7px, env(safe-area-inset-bottom)) max(8px, env(safe-area-inset-left));
        grid-template-columns: repeat(4, minmax(0, 1fr));
        border-top: 1px solid var(--sv-border);
        background: rgba(255, 255, 255, 0.97);
        backdrop-filter: blur(12px);
    }

    .sv-bottom-navigation a {
        display: flex;
        min-width: 0;
        padding: 6px 3px;
        align-items: center;
        justify-content: center;
        gap: 3px;
        flex-direction: column;
        border-radius: 8px;
        color: #7a8699;
        font-size: 9px;
        font-weight: 650;
    }

    .sv-bottom-navigation a.is-active {
        color: var(--sv-primary-dark);
        background: var(--sv-primary-soft);
    }

    .sv-settings-layout {
        grid-template-columns: 1fr;
    }

    .sv-settings-nav {
        position: static;
        display: flex;
        padding: 4px;
        overflow-x: auto;
    }

    .sv-tab-button {
        width: auto;
        flex: 0 0 auto;
    }

    .sv-room-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 820px) {
    .sv-dashboard-metrics {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .sv-power-card {
        grid-column: 1 / -1;
    }

    .sv-support-metric:last-child {
        grid-column: auto;
        min-height: 180px;
    }

    .sv-system-strip {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .sv-system-item:nth-child(3) {
        border-left: 0;
        border-top: 1px solid var(--sv-border);
    }

    .sv-system-item:nth-child(4) {
        border-top: 1px solid var(--sv-border);
    }

    .sv-filter-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .sv-filter-grid .sv-filter-actions {
        grid-column: 1 / -1;
    }

    .sv-technician-summary {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .sv-form-grid-three {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 620px) {
    .sv-app-topbar {
        min-height: 62px;
        padding-inline: 12px;
    }

    .sv-page-content {
        padding: 15px 12px 88px;
    }

    .sv-page-title {
        font-size: 17px;
    }

    .sv-page-subtitle {
        max-width: 190px;
        font-size: 10px;
    }

    .sv-profile-copy,
    .sv-profile-trigger > .sv-icon {
        display: none;
    }

    .sv-profile-trigger {
        min-height: 40px;
        padding: 3px;
        border: 0;
    }

    .sv-avatar {
        width: 36px;
        height: 36px;
    }

    .sv-topbar-status {
        display: none;
    }

    .sv-page-heading {
        align-items: flex-start;
        flex-direction: column;
    }

    .sv-page-heading-copy h2 {
        font-size: 20px;
    }

    .sv-dashboard-intro {
        align-items: flex-start;
    }

    .sv-dashboard-intro h2 {
        font-size: 16px;
    }

    .sv-dashboard-metrics {
        grid-template-columns: 1fr;
    }

    .sv-power-card {
        min-height: 220px;
        grid-template-columns: minmax(0, 1fr) 90px;
    }

    .sv-power-ring {
        width: 82px;
        height: 82px;
        border-width: 8px;
    }

    .sv-power-ring strong {
        font-size: 14px;
    }

    .sv-support-metric,
    .sv-support-metric:last-child {
        min-height: 135px;
    }

    .sv-system-strip,
    .sv-room-summary-grid,
    .sv-history-metrics {
        grid-template-columns: 1fr;
    }

    .sv-system-item + .sv-system-item,
    .sv-system-item:nth-child(3),
    .sv-system-item:nth-child(4) {
        border-top: 1px solid var(--sv-border);
        border-left: 0;
    }

    .sv-section-grid-equal {
        grid-template-columns: 1fr;
    }

    .sv-card-header {
        padding: 15px;
    }

    .sv-card-body {
        padding: 15px;
    }

    .sv-chart-container {
        height: 260px;
    }

    .sv-form-grid,
    .sv-form-grid-three,
    .sv-filter-grid,
    .sv-technician-summary,
    .sv-relay-name-fields {
        grid-template-columns: 1fr;
    }

    .sv-filter-grid .sv-filter-actions {
        grid-column: auto;
    }

    .sv-room-card-head,
    .sv-technician-toolbar {
        align-items: flex-start;
        flex-direction: column;
    }

    .sv-device-row {
        grid-template-columns: minmax(0, 1fr) auto;
    }

    .sv-settings-nav {
        margin-inline: -2px;
    }

    .sv-tab-button {
        min-height: 38px;
        padding: 8px 9px;
        font-size: 10px;
    }

    .sv-dialog-body,
    .sv-dialog-header {
        padding: 15px;
    }

    .sv-toast-region {
        right: 12px;
        bottom: 78px;
        width: calc(100vw - 24px);
    }
}

@media (max-width: 390px) {
    .sv-power-card {
        grid-template-columns: 1fr;
    }

    .sv-power-visual {
        position: absolute;
        top: 20px;
        right: 16px;
    }

    .sv-power-ring {
        width: 68px;
        height: 68px;
    }

    .sv-power-value {
        padding-right: 70px;
        font-size: 39px;
    }

    .sv-notification-dropdown {
        right: -55px;
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

.is-hidden { display: none !important; }

/* =========================================================
   SmartVolt Modern Responsive V2 — 29 July 2026
   Light, calm, professional and device-friendly interface.
   ========================================================= */

:root {
    --sv-bg: #f6f8fb;
    --sv-surface: #ffffff;
    --sv-surface-soft: #f9fbfd;
    --sv-surface-blue: #eef5ff;
    --sv-text: #17233a;
    --sv-text-strong: #0e1b33;
    --sv-muted: #68758a;
    --sv-muted-light: #98a2b3;
    --sv-border: #e3e9f1;
    --sv-border-strong: #d1dae6;
    --sv-primary: #1267e8;
    --sv-primary-dark: #0b54c5;
    --sv-primary-soft: #eaf3ff;
    --sv-navy: #102f5a;
    --sv-cyan: #0aa5b8;
    --sv-success: #159455;
    --sv-success-soft: #eaf8f0;
    --sv-warning: #d97706;
    --sv-warning-soft: #fff5e8;
    --sv-danger: #d14343;
    --sv-danger-soft: #fff0f0;
    --sv-info: #1770b8;
    --sv-info-soft: #ecf6ff;
    --sv-sidebar-width: 242px;
    --sv-topbar-height: 76px;
    --sv-radius-xs: 8px;
    --sv-radius-sm: 10px;
    --sv-radius: 14px;
    --sv-radius-lg: 17px;
    --sv-shadow: 0 5px 18px rgba(16, 47, 90, 0.055);
    --sv-shadow-lg: 0 18px 48px rgba(16, 47, 90, 0.11);
    --sv-focus: 0 0 0 4px rgba(18, 103, 232, 0.14);
}

html,
body.sv-app-body {
    background: var(--sv-bg);
}

body.sv-app-body {
    color: var(--sv-text);
    line-height: 1.45;
}

/* Application shell */
.sv-app-sidebar {
    width: var(--sv-sidebar-width);
    border-right: 1px solid var(--sv-border);
    background: rgba(255, 255, 255, 0.98);
    box-shadow: 8px 0 30px rgba(16, 47, 90, 0.035);
}

.sv-sidebar-header {
    min-height: var(--sv-topbar-height);
    padding: 0 19px;
    border-bottom: 0;
}

.sv-brand {
    gap: 10px;
}

.sv-brand-mark {
    width: 42px;
    height: 42px;
    flex-basis: 42px;
    color: #ffffff;
    border-radius: 13px;
    background: linear-gradient(145deg, #1267e8, #0aa5b8);
    box-shadow: 0 9px 21px rgba(18, 103, 232, 0.22);
}

.sv-brand-copy strong {
    color: #0f2445;
    font-size: 20px;
    font-weight: 800;
}

.sv-brand-copy strong span {
    color: var(--sv-primary);
}

.sv-brand-copy small {
    color: #8390a4;
    font-size: 10px;
    font-weight: 650;
}

.sv-sidebar-nav {
    padding: 22px 14px 14px;
    gap: 7px;
}

.sv-nav-link {
    min-height: 47px;
    padding: 11px 13px;
    gap: 12px;
    border: 1px solid transparent;
    border-radius: 11px;
    color: #536178;
    font-size: 13px;
    font-weight: 670;
}

.sv-nav-link::before {
    display: none;
}

.sv-nav-link:hover {
    color: var(--sv-primary);
    background: #f4f8ff;
}

.sv-nav-link.is-active {
    color: #075bd6;
    border-color: #cfe1ff;
    background: linear-gradient(90deg, #edf5ff, #f8fbff);
    box-shadow: inset 3px 0 0 #1267e8;
}

.sv-nav-link-technical > .sv-icon:last-child {
    margin-left: auto;
}

.sv-sidebar-divider {
    height: 1px;
    margin: 14px 7px 8px;
    background: var(--sv-border);
}

.sv-nav-label {
    margin: 0 12px 3px;
    color: #929db0;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 0.12em;
}

.sv-sidebar-foot {
    padding: 12px 14px 18px;
}

.sv-sidebar-energy-card {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 13px;
    border: 1px solid #dce9fb;
    border-radius: 14px;
    background: linear-gradient(145deg, #f2f7ff, #f7fcff);
}

.sv-sidebar-energy-icon {
    display: grid;
    width: 42px;
    height: 42px;
    flex: 0 0 42px;
    place-items: center;
    border-radius: 12px;
    color: #ffffff;
    background: linear-gradient(145deg, #1267e8, #10afbc);
}

.sv-sidebar-energy-card strong {
    display: block;
    color: #17355d;
    font-size: 13px;
}

.sv-sidebar-energy-card p {
    margin: 3px 0 0;
    color: #718097;
    font-size: 10px;
}

.sv-app-main {
    margin-left: var(--sv-sidebar-width);
}

.sv-app-topbar {
    min-height: var(--sv-topbar-height);
    padding: 0 clamp(18px, 2.6vw, 34px);
    border-bottom: 1px solid var(--sv-border);
    background: rgba(255, 255, 255, 0.93);
    box-shadow: 0 2px 12px rgba(16, 47, 90, 0.025);
    backdrop-filter: blur(14px);
}

.sv-topbar-left {
    gap: 12px;
}

.sv-topbar-heading {
    min-width: 0;
}

.sv-page-title {
    color: var(--sv-text-strong);
    font-size: 25px;
    font-weight: 800;
    letter-spacing: -0.035em;
}

.sv-page-subtitle {
    margin-top: 3px;
    color: var(--sv-muted);
    font-size: 12px;
}

.sv-topbar-actions {
    gap: 9px;
}

.sv-icon-button,
.sv-notification-trigger {
    width: 40px;
    height: 40px;
    border-color: transparent;
    color: #34435b;
    background: transparent;
}

.sv-icon-button:hover,
.sv-notification-trigger:hover {
    border-color: #dce7f4;
    color: var(--sv-primary);
    background: #f5f9ff;
}

.sv-profile-trigger {
    min-height: 48px;
    padding: 4px 7px 4px 5px;
    border: 0;
    border-radius: 12px;
    background: transparent;
}

.sv-profile-trigger:hover {
    background: #f5f8fc;
}

.sv-avatar {
    width: 39px;
    height: 39px;
    color: #ffffff;
    border: 2px solid #ffffff;
    background: linear-gradient(145deg, #1b75e8, #0aa5b8);
    box-shadow: 0 0 0 1px #dce5f0;
}

.sv-profile-copy strong {
    color: #17233a;
    font-size: 12px;
}

.sv-profile-copy small {
    color: #8390a4;
    font-size: 10px;
}

.sv-page-content {
    width: min(100%, 1540px);
    margin-inline: auto;
    padding: clamp(18px, 2.4vw, 32px);
}

/* Shared components */
.sv-card,
.sv-filter-card,
.sv-settings-card,
.sv-summary-card,
.sv-room-card,
.sv-history-metric,
.sv-technician-lock,
.sv-technician-summary-card {
    border: 1px solid var(--sv-border);
    border-radius: var(--sv-radius);
    background: var(--sv-surface);
    box-shadow: var(--sv-shadow);
}

.sv-card-header {
    padding: 18px 19px 14px;
    border-bottom: 0;
}

.sv-card-header h2 {
    color: #17233a;
    font-size: 15px;
    font-weight: 780;
}

.sv-card-header p {
    margin-top: 3px;
    color: var(--sv-muted);
    font-size: 11px;
}

.sv-card-body {
    padding: 16px 19px 19px;
}

.sv-card-body-table {
    padding-top: 3px;
}

.sv-button {
    min-height: 41px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 700;
}

.sv-button-primary {
    color: #ffffff;
    border-color: var(--sv-primary);
    background: var(--sv-primary);
    box-shadow: 0 8px 18px rgba(18, 103, 232, 0.18);
}

.sv-button-primary:hover {
    border-color: var(--sv-primary-dark);
    background: var(--sv-primary-dark);
}

.sv-button-secondary {
    color: #24405f;
    border-color: #dbe5f0;
    background: #ffffff;
}

.sv-button-secondary:hover {
    color: var(--sv-primary);
    border-color: #bdd5f5;
    background: #f7faff;
}

.sv-button-full {
    width: 100%;
}

.sv-form-control {
    min-height: 43px;
    border-color: #dce3ec;
    border-radius: 10px;
    color: var(--sv-text);
    background: #ffffff;
}

.sv-form-control:hover {
    border-color: #becbdb;
}

.sv-form-control:focus {
    border-color: #6fa6ef;
    box-shadow: var(--sv-focus);
}

.sv-table-wrap {
    border: 1px solid var(--sv-border);
    border-radius: 11px;
    background: #ffffff;
}

.sv-table {
    min-width: 690px;
    font-size: 11px;
}

.sv-table th {
    padding: 10px 12px;
    color: #435169;
    font-size: 10px;
    font-weight: 760;
    background: #f7f9fc;
}

.sv-table td {
    padding: 11px 12px;
    color: #4e5c72;
    border-color: #edf1f5;
}

.sv-table tbody tr:hover {
    background: #fbfdff;
}

.sv-table-room {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: #254b78;
    font-weight: 650;
}

.sv-text-button {
    color: var(--sv-primary);
    font-size: 11px;
    font-weight: 720;
}

.sv-text-button:hover {
    color: var(--sv-primary-dark);
}

.sv-badge {
    min-height: 31px;
    padding: 6px 10px;
    gap: 7px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 700;
}

.sv-status-dot {
    width: 7px;
    height: 7px;
    flex: 0 0 7px;
    border-radius: 50%;
    background: currentColor;
}

.sv-alert {
    margin-bottom: 16px;
    border-radius: 12px;
    box-shadow: none;
}

.sv-empty-state {
    min-height: 190px;
    padding: 26px 18px;
    color: #8290a4;
}

.sv-empty-state h3 {
    color: #35445c;
}

.sv-empty-state-compact {
    min-height: 70px;
    padding: 15px;
}

/* Dashboard hero */
.sv-dashboard-intro {
    position: relative;
    display: grid;
    grid-template-columns: auto minmax(0, 1fr) auto;
    min-height: 88px;
    margin-bottom: 16px;
    padding: 17px 20px;
    align-items: center;
    gap: 15px;
    overflow: hidden;
    border: 1px solid #cfe1fb;
    border-radius: 14px;
    background:
        radial-gradient(circle at 88% 110%, rgba(18, 103, 232, 0.13), transparent 28%),
        linear-gradient(105deg, #f5f9ff 0%, #eef6ff 55%, #f8fcff 100%);
    box-shadow: var(--sv-shadow);
}

.sv-dashboard-intro::after {
    position: absolute;
    right: 7%;
    bottom: -31px;
    width: 160px;
    height: 85px;
    border: 1px solid rgba(18, 103, 232, 0.10);
    border-radius: 50% 50% 0 0;
    content: "";
}

.sv-dashboard-intro-icon,
.sv-dashboard-intro-state {
    display: grid;
    place-items: center;
    border-radius: 13px;
}

.sv-dashboard-intro-icon {
    width: 49px;
    height: 49px;
    color: #1267e8;
    background: #ffffff;
    box-shadow: 0 7px 20px rgba(18, 103, 232, 0.13);
}

.sv-dashboard-intro-state {
    z-index: 1;
    width: 43px;
    height: 43px;
    color: #159455;
    border: 1px solid #c9eed9;
    background: rgba(255, 255, 255, 0.83);
}

.sv-dashboard-intro-state.is-waiting {
    color: var(--sv-warning);
    border-color: #f6d9ab;
}

.sv-dashboard-intro-copy {
    min-width: 0;
}

.sv-dashboard-intro h2 {
    margin: 0;
    color: #13233e;
    font-size: 19px;
    font-weight: 780;
    letter-spacing: -0.02em;
}

.sv-dashboard-intro p {
    margin: 4px 0 0;
    color: #63718a;
    font-size: 12px;
}

/* Dashboard metric cards */
.sv-dashboard-metrics {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
    margin-bottom: 16px;
}

.sv-metric-card {
    position: relative;
    min-width: 0;
    min-height: 137px;
    padding: 17px;
    overflow: hidden;
    border: 1px solid;
    border-radius: 14px;
    box-shadow: 0 7px 22px rgba(16, 47, 90, 0.055);
}

.sv-metric-card::after {
    position: absolute;
    right: -26px;
    bottom: -31px;
    width: 105px;
    height: 105px;
    border: 18px solid currentColor;
    border-radius: 50%;
    content: "";
    opacity: 0.035;
}

.sv-metric-card-blue {
    color: #1267e8;
    border-color: #bcd7ff;
    background: linear-gradient(145deg, #f4f8ff, #eaf3ff);
}

.sv-metric-card-green {
    color: #159455;
    border-color: #bce8cf;
    background: linear-gradient(145deg, #f4fcf7, #eaf8f0);
}

.sv-metric-card-orange {
    color: #e57800;
    border-color: #f4d1a5;
    background: linear-gradient(145deg, #fffaf2, #fff2df);
}

.sv-metric-card-cyan {
    color: #069bab;
    border-color: #b6e5ea;
    background: linear-gradient(145deg, #f2fcfd, #e7f8fa);
}

.sv-metric-card-top {
    display: grid;
    grid-template-columns: auto minmax(0, 1fr);
    align-items: center;
    gap: 13px;
}

.sv-metric-card-icon {
    display: grid;
    width: 46px;
    height: 46px;
    place-items: center;
    border-radius: 50%;
    color: #ffffff;
    background: currentColor;
    box-shadow: 0 8px 18px rgba(16, 47, 90, 0.14);
}

.sv-metric-label {
    display: block;
    min-height: 17px;
    color: #405069;
    font-size: 11px;
    font-weight: 690;
}

.sv-metric-value {
    display: flex;
    margin-top: 2px;
    align-items: baseline;
    flex-wrap: wrap;
    gap: 6px;
    color: #14223a;
}

.sv-metric-value strong {
    min-width: 0;
    overflow-wrap: anywhere;
    font-size: clamp(25px, 2vw, 31px);
    font-weight: 810;
    letter-spacing: -0.045em;
    line-height: 1.08;
}

.sv-metric-value small {
    color: #5e6c82;
    font-size: 12px;
    font-weight: 650;
}

.sv-metric-money {
    gap: 3px;
}

.sv-metric-money small {
    color: #14223a;
    font-size: 15px;
}

.sv-metric-foot,
.sv-metric-foot-text {
    position: relative;
    z-index: 1;
    margin: 18px 0 0;
    color: #63718a;
    font-size: 10px;
    line-height: 1.4;
}

.sv-metric-foot {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.sv-metric-status {
    color: var(--sv-primary);
    font-weight: 720;
}

.sv-metric-status.is-success { color: var(--sv-success); }
.sv-metric-status.is-warning { color: var(--sv-warning); }
.sv-metric-status.is-danger { color: var(--sv-danger); }

.sv-metric-card .sv-progress {
    height: 4px;
    margin-top: 8px;
    background: rgba(18, 103, 232, 0.10);
}

/* System strip */
.sv-system-strip {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    margin-bottom: 16px;
    overflow: hidden;
    border: 1px solid var(--sv-border);
    border-radius: 13px;
    background: #ffffff;
    box-shadow: var(--sv-shadow);
}

.sv-system-item {
    display: flex;
    min-width: 0;
    min-height: 67px;
    padding: 13px 16px;
    align-items: center;
    gap: 11px;
    border-left: 1px solid var(--sv-border);
}

.sv-system-item:first-child {
    border-left: 0;
}

.sv-system-icon {
    display: grid;
    width: 37px;
    height: 37px;
    flex: 0 0 37px;
    place-items: center;
    border-radius: 11px;
    color: var(--sv-primary);
    background: var(--sv-primary-soft);
}

.sv-system-item.is-success .sv-system-icon {
    color: var(--sv-success);
    background: var(--sv-success-soft);
}

.sv-system-item.is-warning .sv-system-icon {
    color: var(--sv-warning);
    background: var(--sv-warning-soft);
}

.sv-system-copy {
    min-width: 0;
}

.sv-system-copy strong,
.sv-system-copy span {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.sv-system-copy strong {
    color: #26364f;
    font-size: 11px;
    font-weight: 720;
}

.sv-system-copy span {
    margin-top: 3px;
    color: #8a95a7;
    font-size: 9px;
}

/* Main dashboard content */
.sv-dashboard-main-grid,
.sv-dashboard-bottom-grid {
    display: grid;
    align-items: start;
    gap: 16px;
}

.sv-dashboard-main-grid {
    grid-template-columns: minmax(0, 1.55fr) minmax(340px, 0.95fr);
    margin-bottom: 16px;
}

.sv-dashboard-bottom-grid {
    grid-template-columns: minmax(0, 1.55fr) minmax(310px, 0.95fr);
}

.sv-chart-card,
.sv-control-card {
    min-height: 400px;
}

.sv-chart-tabs {
    padding: 3px;
    border: 1px solid var(--sv-border);
    border-radius: 9px;
    background: #f8fafc;
}

.sv-chart-tab {
    min-height: 31px;
    padding: 6px 13px;
    border: 0;
    border-radius: 7px;
    color: #66758a;
    background: transparent;
    font-size: 10px;
    font-weight: 700;
    cursor: pointer;
}

.sv-chart-tab.is-active {
    color: #ffffff;
    background: var(--sv-primary);
    box-shadow: 0 5px 13px rgba(18, 103, 232, 0.19);
}

.sv-chart-summary {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
    margin-bottom: 10px;
}

.sv-chart-summary > div {
    padding: 8px 11px;
    border: 1px solid var(--sv-border);
    border-radius: 9px;
    background: #fbfcfe;
}

.sv-chart-summary span {
    color: #7b8799;
    font-size: 9px;
}

.sv-chart-summary strong {
    display: block;
    margin-top: 2px;
    color: #22324b;
    font-size: 13px;
}

.sv-chart-summary small {
    color: #7b8799;
    font-size: 9px;
}

.sv-chart-container {
    height: 285px;
}

/* Room controls */
.sv-room-control-list {
    display: grid;
    gap: 9px;
}

.sv-room-control {
    overflow: hidden;
    border: 1px solid var(--sv-border);
    border-radius: 11px;
    background: #ffffff;
}

.sv-room-control > summary {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto auto;
    min-height: 54px;
    padding: 10px 11px;
    align-items: center;
    gap: 9px;
    list-style: none;
    cursor: pointer;
}

.sv-room-control > summary::-webkit-details-marker {
    display: none;
}

.sv-room-summary-main {
    display: flex;
    min-width: 0;
    align-items: center;
    gap: 10px;
}

.sv-room-icon {
    display: grid;
    width: 35px;
    height: 35px;
    flex: 0 0 35px;
    place-items: center;
    border-radius: 10px;
    color: var(--sv-primary);
    background: var(--sv-primary-soft);
}

.sv-room-summary-copy {
    min-width: 0;
}

.sv-room-summary-copy strong,
.sv-room-summary-copy span {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.sv-room-summary-copy strong {
    color: #273750;
    font-size: 11px;
}

.sv-room-summary-copy span {
    margin-top: 2px;
    color: #8894a6;
    font-size: 9px;
}

.sv-room-power {
    color: #4d5c72;
    font-size: 10px;
    font-weight: 700;
}

.sv-room-chevron {
    color: #8d99aa;
    transition: transform 160ms ease;
}

.sv-room-control[open] .sv-room-chevron {
    transform: rotate(90deg);
}

.sv-room-devices {
    display: grid;
    gap: 1px;
    padding: 0 10px 9px;
}

.sv-device-row {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    min-height: 47px;
    padding: 7px 4px;
    align-items: center;
    gap: 10px;
    border-top: 1px solid #edf1f5;
}

.sv-device-main {
    display: flex;
    min-width: 0;
    align-items: center;
    gap: 9px;
}

.sv-device-icon {
    display: grid;
    width: 30px;
    height: 30px;
    flex: 0 0 30px;
    place-items: center;
    border-radius: 9px;
    color: #e57800;
    background: #fff5e8;
}

.sv-device-copy {
    min-width: 0;
}

.sv-device-copy strong,
.sv-device-copy span {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.sv-device-copy strong {
    color: #334158;
    font-size: 10px;
}

.sv-device-copy span {
    margin-top: 2px;
    color: #8d98a9;
    font-size: 8px;
}

.sv-device-switch {
    display: inline-flex;
    min-height: 32px;
    padding: 4px 7px;
    align-items: center;
    gap: 6px;
    border: 0;
    border-radius: 999px;
    color: #718096;
    background: transparent;
    font-size: 9px;
    font-weight: 700;
    cursor: pointer;
}

.sv-switch-track {
    display: flex;
    width: 36px;
    height: 20px;
    padding: 2px;
    align-items: center;
    border-radius: 999px;
    background: #cfd7e2;
    transition: background-color 160ms ease;
}

.sv-switch-track span {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #ffffff;
    box-shadow: 0 1px 4px rgba(16, 47, 90, 0.22);
    transition: transform 160ms ease;
}

.sv-device-switch.is-on {
    color: var(--sv-primary);
}

.sv-device-switch.is-on .sv-switch-track {
    background: var(--sv-primary);
}

.sv-device-switch.is-on .sv-switch-track span {
    transform: translateX(16px);
}

.sv-device-switch.is-offline {
    opacity: 0.58;
    cursor: not-allowed;
}

/* Billing */
.sv-billing-highlight {
    display: grid;
    min-height: 127px;
    padding: 18px;
    place-content: center;
    text-align: center;
    border: 1px solid #f2d7b6;
    border-radius: 12px;
    background: linear-gradient(145deg, #fffaf2, #fff4e4);
}

.sv-billing-highlight span {
    color: #8d6c43;
    font-size: 10px;
    font-weight: 700;
}

.sv-billing-highlight strong {
    margin-top: 3px;
    color: #1e2d45;
    font-size: clamp(24px, 2.2vw, 32px);
    font-weight: 810;
    letter-spacing: -0.04em;
}

.sv-billing-highlight small {
    margin-top: 2px;
    color: var(--sv-success);
    font-size: 10px;
}

.sv-billing-summary {
    display: grid;
    margin: 13px 0;
    gap: 0;
    border-top: 1px dashed #dce3ec;
}

.sv-billing-summary > div {
    display: flex;
    padding: 9px 2px;
    justify-content: space-between;
    gap: 10px;
    border-bottom: 1px solid #edf1f5;
}

.sv-billing-summary dt,
.sv-billing-summary dd {
    margin: 0;
    font-size: 10px;
}

.sv-billing-summary dt {
    color: #7d899b;
}

.sv-billing-summary dd {
    color: #35445b;
    font-weight: 680;
    text-align: right;
}

/* Other page alignment */
.sv-page-heading {
    margin-bottom: 15px;
}

.sv-page-heading-copy h2 {
    color: #15243c;
    font-size: 21px;
    font-weight: 790;
    letter-spacing: -0.025em;
}

.sv-page-heading-copy p {
    color: var(--sv-muted);
    font-size: 11px;
}

.sv-room-grid {
    gap: 14px;
}

.sv-room-card {
    overflow: hidden;
}

.sv-settings-layout {
    gap: 16px;
}

.sv-settings-nav {
    border-radius: 13px;
    box-shadow: var(--sv-shadow);
}

.sv-tab-button {
    border-radius: 9px;
}

.sv-tab-button.is-active {
    color: var(--sv-primary);
    background: var(--sv-primary-soft);
}

/* Notification and profile menus */
.sv-notification-dropdown,
.sv-profile-dropdown {
    border-color: var(--sv-border);
    border-radius: 13px;
    box-shadow: var(--sv-shadow-lg);
}

/* Responsive */
@media (max-width: 1240px) {
    .sv-dashboard-metrics {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .sv-dashboard-main-grid,
    .sv-dashboard-bottom-grid {
        grid-template-columns: 1fr;
    }

    .sv-chart-card,
    .sv-control-card {
        min-height: auto;
    }
}

@media (max-width: 1040px) {
    :root {
        --sv-sidebar-width: 228px;
    }

    .sv-system-strip {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .sv-system-item:nth-child(3) {
        border-left: 0;
    }

    .sv-system-item:nth-child(n + 3) {
        border-top: 1px solid var(--sv-border);
    }
}

@media (max-width: 820px) {
    .sv-app-main {
        margin-left: 0;
    }

    .sv-app-sidebar {
        width: min(86vw, 300px);
        transform: translateX(-104%);
        box-shadow: var(--sv-shadow-lg);
    }

    body.sv-sidebar-open .sv-app-sidebar {
        transform: translateX(0);
    }

    .sv-sidebar-overlay {
        position: fixed;
        inset: 0;
        z-index: 55;
        display: block;
        visibility: hidden;
        border: 0;
        opacity: 0;
        background: rgba(12, 28, 52, 0.42);
        transition: opacity 180ms ease, visibility 180ms ease;
    }

    body.sv-sidebar-open .sv-sidebar-overlay {
        visibility: visible;
        opacity: 1;
    }

    .sv-sidebar-close {
        display: inline-grid !important;
    }

    .sv-mobile-menu {
        display: inline-grid;
    }

    .sv-page-content {
        padding-bottom: 90px;
    }

    .sv-bottom-navigation {
        display: grid;
    }
}

@media (max-width: 680px) {
    .sv-app-topbar {
        min-height: 64px;
        padding-inline: 11px;
    }

    .sv-page-content {
        padding: 13px 11px 88px;
    }

    .sv-page-title {
        font-size: 18px;
    }

    .sv-page-subtitle {
        max-width: 230px;
        font-size: 10px;
    }

    .sv-topbar-status,
    .sv-profile-copy,
    .sv-profile-trigger > .sv-icon {
        display: none;
    }

    .sv-profile-trigger {
        min-height: 40px;
        padding: 0;
    }

    .sv-avatar {
        width: 36px;
        height: 36px;
    }

    .sv-dashboard-intro {
        min-height: 78px;
        padding: 14px;
        gap: 11px;
    }

    .sv-dashboard-intro-icon {
        width: 42px;
        height: 42px;
    }

    .sv-dashboard-intro-state {
        display: none;
    }

    .sv-dashboard-intro h2 {
        font-size: 16px;
    }

    .sv-dashboard-intro p {
        font-size: 10px;
    }

    .sv-dashboard-metrics,
    .sv-system-strip,
    .sv-chart-summary {
        grid-template-columns: 1fr;
    }

    .sv-metric-card {
        min-height: 128px;
    }

    .sv-system-item,
    .sv-system-item:nth-child(3) {
        border-top: 1px solid var(--sv-border);
        border-left: 0;
    }

    .sv-system-item:first-child {
        border-top: 0;
    }

    .sv-card-header {
        padding: 15px 14px 11px;
        align-items: flex-start;
    }

    .sv-card-body {
        padding: 13px 14px 15px;
    }

    .sv-chart-container {
        height: 235px;
    }

    .sv-room-control > summary {
        grid-template-columns: minmax(0, 1fr) auto;
    }

    .sv-room-power {
        display: none;
    }

    .sv-table {
        min-width: 660px;
    }
}

@media (max-width: 390px) {
    .sv-page-subtitle {
        display: none;
    }

    .sv-topbar-actions {
        gap: 2px;
    }

    .sv-dashboard-intro {
        grid-template-columns: auto minmax(0, 1fr);
    }

    .sv-metric-card {
        padding: 15px;
    }

    .sv-metric-card-icon {
        width: 42px;
        height: 42px;
    }

    .sv-metric-value strong {
        font-size: 26px;
    }

    .sv-chart-tabs {
        width: 100%;
    }

    .sv-chart-tab {
        flex: 1;
    }

    .sv-card-header {
        gap: 10px;
        flex-direction: column;
    }
}

/* SmartVolt V2 — perbaikan ikon kartu statistik */
.sv-metric-card-blue { --sv-metric-accent: #1267e8; }
.sv-metric-card-green { --sv-metric-accent: #159455; }
.sv-metric-card-orange { --sv-metric-accent: #e57800; }
.sv-metric-card-cyan { --sv-metric-accent: #069bab; }

.sv-metric-card-icon {
    color: #ffffff;
    background: var(--sv-metric-accent, #1267e8);
    box-shadow: 0 8px 18px color-mix(in srgb, var(--sv-metric-accent, #1267e8) 24%, transparent);
}

.sv-metric-card-icon .sv-icon {
    display: block;
    color: #ffffff;
    stroke-width: 2;
}

.sv-metric-card-icon .sv-icon path[fill="currentColor"] {
    fill: #ffffff;
}
/* =========================================================
   SmartVolt UI V5 — Colored Feature Icon System
   30 July 2026
   ========================================================= */

:root {
    --sv-tone-blue: #2563eb;
    --sv-tone-blue-soft: #eaf2ff;
    --sv-tone-green: #159455;
    --sv-tone-green-soft: #e9f8f0;
    --sv-tone-cyan: #0891b2;
    --sv-tone-cyan-soft: #e8f8fb;
    --sv-tone-violet: #7c3aed;
    --sv-tone-violet-soft: #f2ebff;
    --sv-tone-amber: #d97706;
    --sv-tone-amber-soft: #fff4e5;
    --sv-tone-rose: #dc4c64;
    --sv-tone-rose-soft: #fff0f3;
    --sv-tone-slate: #53637a;
    --sv-tone-slate-soft: #eef2f6;
}

/* Reusable icon container */
.sv-feature-icon {
    --sv-icon-color: var(--sv-tone-blue);
    --sv-icon-bg: var(--sv-tone-blue-soft);
    display: inline-grid;
    width: 40px;
    height: 40px;
    flex: 0 0 40px;
    place-items: center;
    border: 1px solid color-mix(in srgb, var(--sv-icon-color) 13%, transparent);
    border-radius: 12px;
    color: var(--sv-icon-color);
    background: var(--sv-icon-bg);
    box-shadow: 0 6px 16px color-mix(in srgb, var(--sv-icon-color) 9%, transparent);
}

.sv-feature-icon .sv-icon {
    display: block;
    color: inherit;
    stroke-width: 1.9;
}

.sv-feature-icon--blue { --sv-icon-color: var(--sv-tone-blue); --sv-icon-bg: var(--sv-tone-blue-soft); }
.sv-feature-icon--green { --sv-icon-color: var(--sv-tone-green); --sv-icon-bg: var(--sv-tone-green-soft); }
.sv-feature-icon--cyan { --sv-icon-color: var(--sv-tone-cyan); --sv-icon-bg: var(--sv-tone-cyan-soft); }
.sv-feature-icon--violet { --sv-icon-color: var(--sv-tone-violet); --sv-icon-bg: var(--sv-tone-violet-soft); }
.sv-feature-icon--amber { --sv-icon-color: var(--sv-tone-amber); --sv-icon-bg: var(--sv-tone-amber-soft); }
.sv-feature-icon--rose { --sv-icon-color: var(--sv-tone-rose); --sv-icon-bg: var(--sv-tone-rose-soft); }
.sv-feature-icon--slate { --sv-icon-color: var(--sv-tone-slate); --sv-icon-bg: var(--sv-tone-slate-soft); }

.sv-feature-icon--flat {
    width: 1.25em;
    height: 1.25em;
    flex-basis: 1.25em;
    border: 0;
    border-radius: 0;
    background: transparent;
    box-shadow: none;
}

.sv-feature-icon--solid {
    color: #ffffff;
    border-color: transparent;
    background: var(--sv-icon-color);
    box-shadow: 0 9px 20px color-mix(in srgb, var(--sv-icon-color) 23%, transparent);
}

.sv-feature-icon--solid .sv-icon path[fill="currentColor"] {
    fill: #ffffff;
}

/* Consistent page alignment and readable type */
.sv-app-topbar,
.sv-page-content {
    padding-inline: clamp(18px, 2.4vw, 34px);
}

.sv-page-content {
    width: 100%;
    max-width: none;
}

.sv-page-heading {
    align-items: center;
}

.sv-page-heading-copy h2 {
    font-size: clamp(20px, 1.55vw, 24px);
}

.sv-page-heading-copy p,
.sv-page-subtitle,
.sv-card-header p,
.sv-form-help,
.sv-device-copy span,
.sv-room-card-title p,
.sv-tech-item-copy span {
    font-size: 13px;
    line-height: 1.55;
}

.sv-form-label {
    font-size: 13px;
    font-weight: 700;
}

.sv-form-control {
    min-height: 44px;
    padding: 10px 12px;
    font-size: 14px;
}

textarea.sv-form-control {
    min-height: 96px;
}

.sv-button {
    min-height: 40px;
    font-size: 13px;
}

.sv-button-sm {
    min-height: 34px;
    font-size: 12px;
}

/* Colored page title icon */
.sv-page-heading-icon {
    width: 40px;
    height: 40px;
    flex-basis: 40px;
    border-radius: 12px;
}

/* Sidebar icons: color is stable, active state stays calm */
.sv-nav-feature-icon {
    width: 31px;
    height: 31px;
    flex-basis: 31px;
    border: 1px solid color-mix(in srgb, var(--sv-icon-color) 13%, transparent);
    border-radius: 9px;
    background: var(--sv-icon-bg);
    box-shadow: none;
}

.sv-nav-link {
    padding-block: 8px;
}

.sv-nav-link.is-active .sv-nav-feature-icon {
    color: #ffffff;
    border-color: transparent;
    background: var(--sv-icon-color);
    box-shadow: 0 7px 16px color-mix(in srgb, var(--sv-icon-color) 20%, transparent);
}

.sv-nav-link--green.is-active { color: #087747; border-color: #c9ead8; background: #f1fbf5; box-shadow: inset 3px 0 0 var(--sv-tone-green); }
.sv-nav-link--violet.is-active { color: #6d28d9; border-color: #dfd1ff; background: #faf7ff; box-shadow: inset 3px 0 0 var(--sv-tone-violet); }
.sv-nav-link--slate.is-active { color: #3f4e63; border-color: #dce3ea; background: #f7f9fb; box-shadow: inset 3px 0 0 var(--sv-tone-slate); }
.sv-nav-link--amber.is-active { color: #a85605; border-color: #f3ddbd; background: #fffaf2; box-shadow: inset 3px 0 0 var(--sv-tone-amber); }

.sv-sidebar-energy-card > .sv-feature-icon {
    width: 42px;
    height: 42px;
    flex-basis: 42px;
}

/* Bottom navigation */
.sv-bottom-navigation .sv-feature-icon {
    width: 28px;
    height: 28px;
    flex-basis: 28px;
    border-radius: 8px;
}

.sv-bottom-navigation a.is-active .sv-feature-icon {
    color: #ffffff;
    background: var(--sv-icon-color);
}

/* Shared cards */
.sv-card,
.sv-summary-card,
.sv-room-card,
.sv-history-metric,
.sv-technician-summary-card {
    border-color: #e4eaf1;
    box-shadow: 0 5px 18px rgba(16, 47, 90, 0.05);
}

.sv-card-header--with-icon {
    display: grid;
    grid-template-columns: auto minmax(0, 1fr) auto;
    align-items: center;
    column-gap: 12px;
}

.sv-card-header--with-icon > .sv-feature-icon {
    width: 40px;
    height: 40px;
    flex-basis: 40px;
}

.sv-card-header-copy {
    min-width: 0;
}

/* Dashboard icon normalization */
.sv-dashboard-intro-icon,
.sv-metric-card-icon,
.sv-system-icon,
.sv-summary-icon,
.sv-room-icon,
.sv-device-icon,
.sv-security-note-icon,
.sv-technician-lock-icon {
    display: inline-grid;
}

.sv-dashboard-intro-icon {
    width: 46px;
    height: 46px;
    flex-basis: 46px;
}

.sv-dashboard-intro-state {
    width: 42px;
    height: 42px;
    flex-basis: 42px;
}

.sv-metric-card-icon {
    width: 46px;
    height: 46px;
    flex-basis: 46px;
}

.sv-system-icon {
    width: 38px;
    height: 38px;
    flex-basis: 38px;
}

/* History cards use the same soft-color pattern */
.sv-history-metric {
    position: relative;
    min-height: 142px;
    padding: 17px;
}

.sv-history-metric > .sv-feature-icon {
    width: 38px;
    height: 38px;
    margin-bottom: 12px;
}

.sv-history-metric > span:not(.sv-feature-icon) {
    font-size: 12px;
}

.sv-history-metric strong {
    font-size: clamp(21px, 1.7vw, 27px);
}

.sv-history-metric small {
    font-size: 12px;
}

.sv-history-metric--green { background: linear-gradient(145deg, #f3fbf6, #ffffff); border-color: #d6edde; }
.sv-history-metric--amber { background: linear-gradient(145deg, #fff8ed, #ffffff); border-color: #f3dfc2; }
.sv-history-metric--blue { background: linear-gradient(145deg, #f2f7ff, #ffffff); border-color: #d7e4f8; }
.sv-history-metric--violet { background: linear-gradient(145deg, #f8f4ff, #ffffff); border-color: #e4d8f8; }

/* Room summaries and room cards */
.sv-summary-card {
    min-height: 112px;
}

.sv-summary-icon {
    width: 42px;
    height: 42px;
    flex-basis: 42px;
}

.sv-summary-copy span {
    font-size: 12px;
}

.sv-summary-copy strong {
    font-size: 24px;
}

.sv-summary-card--violet { background: linear-gradient(145deg, #faf7ff, #ffffff); border-color: #e5dbf7; }
.sv-summary-card--cyan { background: linear-gradient(145deg, #f1fbfd, #ffffff); border-color: #d5edf1; }
.sv-summary-card--green { background: linear-gradient(145deg, #f3fbf6, #ffffff); border-color: #d7eddf; }
.sv-summary-card--blue { background: linear-gradient(145deg, #f3f7ff, #ffffff); border-color: #d8e4f7; }

.sv-room-card {
    border-top: 3px solid color-mix(in srgb, var(--sv-room-accent, var(--sv-tone-violet)) 72%, white);
}

.sv-room-card--violet { --sv-room-accent: var(--sv-tone-violet); }
.sv-room-card--blue { --sv-room-accent: var(--sv-tone-blue); }
.sv-room-card--green { --sv-room-accent: var(--sv-tone-green); }
.sv-room-card--amber { --sv-room-accent: var(--sv-tone-amber); }
.sv-room-card--cyan { --sv-room-accent: var(--sv-tone-cyan); }
.sv-room-card--rose { --sv-room-accent: var(--sv-tone-rose); }

.sv-room-icon {
    width: 40px;
    height: 40px;
    flex-basis: 40px;
}

.sv-device-icon {
    width: 34px;
    height: 34px;
    flex-basis: 34px;
    border-radius: 10px;
}

.sv-room-card-title h3 {
    font-size: 15px;
}

.sv-room-card-foot {
    font-size: 12px;
}

.sv-room-card-foot .sv-text-button {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.sv-empty-state--compact {
    min-height: 160px;
    padding-block: 22px;
}

/* Device list */
.sv-table-feature {
    display: inline-flex;
    align-items: center;
    gap: 9px;
}

.sv-table-feature > .sv-feature-icon {
    width: 32px;
    height: 32px;
    flex-basis: 32px;
    border-radius: 9px;
}

.sv-device-mobile-list {
    display: none;
    gap: 10px;
}

.sv-device-mobile-card {
    padding: 14px;
    border: 1px solid var(--sv-border);
    border-radius: 12px;
    background: #ffffff;
}

.sv-device-mobile-card header {
    display: grid;
    grid-template-columns: auto minmax(0, 1fr) auto;
    align-items: center;
    gap: 10px;
}

.sv-device-mobile-card header > .sv-feature-icon {
    width: 38px;
    height: 38px;
    flex-basis: 38px;
}

.sv-device-mobile-card header strong,
.sv-device-mobile-card header span {
    display: block;
}

.sv-device-mobile-card header strong {
    font-size: 14px;
}

.sv-device-mobile-card header div > span {
    margin-top: 3px;
    color: var(--sv-muted);
    font-size: 12px;
}

.sv-device-mobile-card dl {
    display: grid;
    margin: 12px 0 0;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px;
}

.sv-device-mobile-card dl div {
    padding: 9px 10px;
    border-radius: 9px;
    background: #f7f9fc;
}

.sv-device-mobile-card dt,
.sv-device-mobile-card dd {
    margin: 0;
}

.sv-device-mobile-card dt {
    color: var(--sv-muted);
    font-size: 11px;
}

.sv-device-mobile-card dd {
    margin-top: 3px;
    color: var(--sv-text-strong);
    font-size: 13px;
    font-weight: 700;
}

/* Settings and technician */
.sv-tab-button {
    min-height: 48px;
    padding: 8px 10px;
    font-size: 13px;
}

.sv-tab-button > .sv-feature-icon {
    width: 32px;
    height: 32px;
    flex-basis: 32px;
    border-radius: 9px;
}

.sv-tab-button.is-active > .sv-feature-icon {
    color: #ffffff;
    background: var(--sv-icon-color);
}

.sv-profile-avatar-large {
    background: linear-gradient(145deg, var(--sv-tone-blue), var(--sv-tone-cyan));
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.18);
}

.sv-security-note-icon {
    width: 38px;
    height: 38px;
    flex-basis: 38px;
}

.sv-technician-toolbar {
    justify-content: flex-start;
}

.sv-technician-toolbar > form {
    margin-left: auto;
}

.sv-technician-toolbar > .sv-feature-icon {
    width: 40px;
    height: 40px;
    flex-basis: 40px;
}

.sv-technician-summary-card {
    display: grid;
    min-height: 108px;
    grid-template-columns: auto minmax(0, 1fr);
    align-items: center;
    column-gap: 11px;
}

.sv-technician-summary-card > .sv-feature-icon {
    width: 40px;
    height: 40px;
    grid-row: 1 / span 2;
}

.sv-technician-summary-card span,
.sv-technician-summary-card strong {
    margin: 0;
}

.sv-technician-summary-card span {
    font-size: 12px;
}

.sv-technician-summary-card strong {
    font-size: 24px;
}

.sv-technician-summary-card--violet { background: #faf7ff; border-color: #e6ddf5; }
.sv-technician-summary-card--green { background: #f3fbf6; border-color: #d7eddf; }
.sv-technician-summary-card--cyan { background: #f1fbfd; border-color: #d5edf1; }

.sv-tech-room-content {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    align-items: start;
    gap: 12px;
}

.sv-tech-subsection + .sv-tech-subsection {
    margin-top: 0;
}

.sv-tech-subsection-head {
    justify-content: flex-start;
}

.sv-tech-subsection-head > .sv-feature-icon {
    width: 31px;
    height: 31px;
    flex-basis: 31px;
    border-radius: 9px;
}

.sv-tech-subsection-head h4 {
    font-size: 13px;
}

.sv-tech-room-content .sv-form-grid-three {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.sv-tech-item-copy strong {
    font-size: 13px;
}

.sv-tech-edit > summary {
    font-size: 12px;
}

.sv-icon-rotate-180 {
    transform: rotate(180deg);
}

.sv-device-detail-item {
    display: flex;
    padding: 12px 0;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    border-bottom: 1px solid var(--sv-border);
}

.sv-device-detail-item:last-child {
    border-bottom: 0;
}

/* Notifications */
.sv-notification-trigger > .sv-feature-icon {
    width: 24px;
    height: 24px;
}

.sv-notification-icon {
    width: 35px;
    height: 35px;
    flex-basis: 35px;
}

/* Responsive */
@media (min-width: 1440px) {
    .sv-room-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (max-width: 1180px) {
    .sv-tech-room-content {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 920px) {
    .sv-room-summary-grid,
    .sv-history-metrics {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .sv-settings-layout {
        grid-template-columns: 1fr;
    }

    .sv-settings-nav {
        position: static;
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 6px;
    }

    .sv-tab-button {
        justify-content: center;
    }
}

@media (max-width: 760px) {
    .sv-page-heading-icon {
        width: 36px;
        height: 36px;
        flex-basis: 36px;
    }

    .sv-page-subtitle {
        max-width: 310px;
        font-size: 12px;
    }

    .sv-card-header--with-icon {
        grid-template-columns: auto minmax(0, 1fr);
    }

    .sv-card-header--with-icon > :last-child:not(div) {
        grid-column: 1 / -1;
        justify-self: stretch;
        margin-top: 4px;
    }

    .sv-device-table-desktop {
        display: none;
    }

    .sv-device-mobile-list {
        display: grid;
    }

    .sv-settings-nav {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .sv-tab-button {
        justify-content: flex-start;
    }

    .sv-tech-room-content .sv-form-grid-three,
    .sv-relay-name-fields,
    .sv-room-detail-grid {
        grid-template-columns: 1fr;
    }

    .sv-device-detail-item {
        align-items: stretch;
        flex-direction: column;
    }
}

@media (max-width: 560px) {
    .sv-app-topbar,
    .sv-page-content {
        padding-inline: 12px;
    }

    .sv-page-heading-icon {
        display: none;
    }

    .sv-room-summary-grid,
    .sv-history-metrics,
    .sv-technician-summary {
        grid-template-columns: 1fr;
    }

    .sv-summary-card,
    .sv-history-metric,
    .sv-technician-summary-card {
        min-height: 104px;
    }

    .sv-room-grid {
        grid-template-columns: 1fr;
    }

    .sv-settings-nav {
        display: flex;
        overflow-x: auto;
        scrollbar-width: none;
    }

    .sv-settings-nav::-webkit-scrollbar {
        display: none;
    }

    .sv-tab-button {
        width: auto;
        min-width: max-content;
    }

    .sv-tab-button > .sv-feature-icon {
        width: 29px;
        height: 29px;
        flex-basis: 29px;
    }

    .sv-technician-toolbar {
        align-items: flex-start;
        flex-wrap: wrap;
    }

    .sv-technician-toolbar > form {
        width: 100%;
        margin-left: 0;
    }

    .sv-technician-toolbar > form .sv-button {
        width: 100%;
    }
}
```

## `public/assets/css/smartvolt-auth.css`

```css
:root {
    --sv-bg: #f5f7fa;
    --sv-surface: #ffffff;
    --sv-text: #172033;
    --sv-muted: #667085;
    --sv-border: #dde3ea;
    --sv-border-strong: #b8c5d4;
    --sv-primary: #2563eb;
    --sv-primary-dark: #1d4ed8;
    --sv-navy: #173b66;
    --sv-navy-dark: #102f54;
    --sv-success: #2e7d32;
    --sv-success-bg: #eaf6ec;
    --sv-danger: #c83c3c;
    --sv-danger-bg: #fdecec;
    --sv-warning: #b76e00;
    --sv-warning-bg: #fff4e5;
    --sv-shadow: 0 24px 60px rgba(23, 59, 102, 0.12);
    --sv-radius: 10px;
    --sv-radius-lg: 14px;
}

*,
*::before,
*::after {
    box-sizing: border-box;
}

html {
    min-width: 320px;
    min-height: 100%;
    background: var(--sv-bg);
}

body.sv-auth-page {
    min-width: 320px;
    min-height: 100vh;
    min-height: 100svh;
    margin: 0;
    color: var(--sv-text);
    background: var(--sv-bg);
    font-family:
        Inter,
        "Segoe UI",
        Roboto,
        Helvetica,
        Arial,
        sans-serif;
    -webkit-font-smoothing: antialiased;
    text-rendering: optimizeLegibility;
}

button,
input {
    font: inherit;
}

button,
a,
input {
    -webkit-tap-highlight-color: transparent;
}

a {
    color: inherit;
    text-decoration: none;
}

svg {
    display: block;
}

/* =========================================================
   Layout utama
   ========================================================= */

.sv-auth-shell {
    display: grid;
    min-height: 100vh;
    min-height: 100svh;
    grid-template-columns: minmax(0, 55fr) minmax(420px, 45fr);
}

/* =========================================================
   Panel informasi (kiri)
   ========================================================= */

.sv-auth-showcase {
    position: relative;
    isolation: isolate;
    display: flex;
    min-height: 100vh;
    min-height: 100svh;
    overflow: hidden;
    color: #ffffff;
    background: var(--sv-navy);
}

.sv-auth-network {
    position: absolute;
    z-index: -1;
    right: -14%;
    bottom: -20%;
    width: min(760px, 82vw);
    color: rgba(255, 255, 255, 0.08);
    transform: rotate(-4deg);
    pointer-events: none;
}

.sv-auth-showcase::after {
    position: absolute;
    z-index: -1;
    right: -120px;
    bottom: -190px;
    width: 480px;
    height: 480px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 50%;
    content: "";
}

.sv-auth-showcase-content {
    display: flex;
    width: min(100%, 820px);
    min-height: 100%;
    margin-inline: auto;
    padding: clamp(38px, 5vw, 78px);
    flex-direction: column;
}

.sv-auth-brand {
    display: inline-flex;
    width: fit-content;
    align-items: center;
    gap: 13px;
    color: #ffffff;
}

.sv-auth-brand-icon {
    display: grid;
    width: 48px;
    height: 48px;
    flex: 0 0 48px;
    place-items: center;
    border: 1px solid rgba(255, 255, 255, 0.18);
    border-radius: 13px;
    color: #ffffff;
    background: rgba(255, 255, 255, 0.1);
}

.sv-auth-brand-icon svg {
    width: 25px;
    height: 25px;
}

.sv-auth-brand strong {
    display: block;
    font-size: 21px;
    font-weight: 750;
    letter-spacing: -0.02em;
}

.sv-auth-brand small {
    display: block;
    margin-top: 2px;
    color: rgba(255, 255, 255, 0.68);
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 0.02em;
}

.sv-auth-copy {
    max-width: 690px;
    margin-top: clamp(72px, 10vh, 132px);
}

.sv-auth-eyebrow {
    margin: 0 0 16px;
    color: #bfdbfe;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.09em;
    text-transform: uppercase;
}

.sv-auth-copy h1 {
    max-width: 650px;
    margin: 0;
    font-size: clamp(38px, 4.5vw, 66px);
    font-weight: 720;
    line-height: 1.06;
    letter-spacing: -0.045em;
}

.sv-auth-description {
    max-width: 620px;
    margin: 22px 0 0;
    color: rgba(255, 255, 255, 0.76);
    font-size: clamp(16px, 1.4vw, 19px);
    line-height: 1.72;
}

.sv-auth-benefits {
    display: grid;
    max-width: 650px;
    margin-top: 40px;
    gap: 18px;
}

.sv-auth-benefit {
    display: grid;
    grid-template-columns: 42px minmax(0, 1fr);
    align-items: start;
    gap: 14px;
}

.sv-auth-benefit-icon {
    display: grid;
    width: 42px;
    height: 42px;
    place-items: center;
    border: 1px solid rgba(255, 255, 255, 0.14);
    border-radius: 12px;
    color: #dbeafe;
    background: rgba(255, 255, 255, 0.08);
}

.sv-auth-benefit-icon svg {
    width: 21px;
    height: 21px;
    fill: none;
    stroke: currentColor;
    stroke-linecap: round;
    stroke-linejoin: round;
    stroke-width: 1.8;
}

.sv-auth-benefit h2 {
    margin: 1px 0 5px;
    font-size: 15px;
    font-weight: 700;
    line-height: 1.35;
}

.sv-auth-benefit p {
    max-width: 560px;
    margin: 0;
    color: rgba(255, 255, 255, 0.65);
    font-size: 13px;
    line-height: 1.62;
}

.sv-auth-flow {
    width: min(100%, 650px);
    margin-top: auto;
    padding-top: 42px;
}

.sv-auth-flow-label {
    margin-bottom: 10px;
    color: rgba(255, 255, 255, 0.48);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.sv-auth-flow-items {
    display: grid;
    padding: 14px 16px;
    grid-template-columns: 1fr auto 1fr auto 1fr;
    align-items: center;
    gap: 14px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 14px;
    background: rgba(7, 30, 55, 0.28);
}

.sv-auth-flow-items span {
    min-width: 0;
}

.sv-auth-flow-items strong,
.sv-auth-flow-items small {
    display: block;
}

.sv-auth-flow-items strong {
    font-size: 13px;
    font-weight: 700;
}

.sv-auth-flow-items small {
    margin-top: 3px;
    overflow: hidden;
    color: rgba(255, 255, 255, 0.55);
    font-size: 11px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.sv-auth-flow-items i {
    color: rgba(255, 255, 255, 0.45);
    font-size: 15px;
    font-style: normal;
}

/* =========================================================
   Panel formulir (kanan)
   ========================================================= */

.sv-auth-panel {
    display: flex;
    min-height: 100vh;
    min-height: 100svh;
    align-items: center;
    justify-content: center;
    padding: clamp(30px, 5vw, 72px);
    background: var(--sv-surface);
}

.sv-auth-panel-inner {
    width: min(100%, 470px);
}

.sv-auth-mobile-brand {
    display: none;
}

.sv-auth-status-chip {
    display: inline-flex;
    min-height: 30px;
    padding: 6px 10px;
    align-items: center;
    gap: 8px;
    border: 1px solid #cfe0f5;
    border-radius: 999px;
    color: #255488;
    background: #f2f7fd;
    font-size: 12px;
    font-weight: 650;
}

.sv-auth-status-chip > span {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--sv-success);
    box-shadow: 0 0 0 4px rgba(46, 125, 50, 0.1);
}

.sv-auth-heading {
    margin-bottom: 30px;
}

.sv-auth-heading h2 {
    margin: 19px 0 10px;
    color: var(--sv-text);
    font-size: clamp(31px, 3.2vw, 42px);
    font-weight: 740;
    line-height: 1.08;
    letter-spacing: -0.04em;
}

.sv-auth-heading p {
    max-width: 430px;
    margin: 0;
    color: var(--sv-muted);
    font-size: 15px;
    line-height: 1.7;
}

/* =========================================================
   Alert
   ========================================================= */

.sv-auth-alert {
    display: grid;
    margin-bottom: 22px;
    padding: 13px 14px;
    grid-template-columns: 22px minmax(0, 1fr);
    align-items: start;
    gap: 10px;
    border: 1px solid;
    border-radius: var(--sv-radius);
    font-size: 13px;
    line-height: 1.55;
}

.sv-auth-alert > span {
    margin-top: 1px;
}

.sv-auth-alert svg {
    width: 19px;
    height: 19px;
    fill: none;
    stroke: currentColor;
    stroke-linecap: round;
    stroke-linejoin: round;
    stroke-width: 1.9;
}

.sv-auth-alert strong {
    display: block;
    margin-bottom: 2px;
    font-weight: 700;
}

.sv-auth-alert p {
    margin: 0;
}

.sv-auth-alert-success {
    border-color: #cce6cf;
    color: #285f2d;
    background: var(--sv-success-bg);
}

.sv-auth-alert-error {
    border-color: #f1cccc;
    color: #8f2e2e;
    background: var(--sv-danger-bg);
}

/* =========================================================
   Form
   ========================================================= */

.sv-auth-form {
    display: grid;
    gap: 20px;
}

.sv-auth-field {
    min-width: 0;
}

.sv-auth-field > label {
    display: block;
    margin-bottom: 8px;
    color: #344054;
    font-size: 13px;
    font-weight: 680;
}

.sv-auth-input-group {
    position: relative;
}

.sv-auth-input-icon {
    position: absolute;
    z-index: 1;
    top: 50%;
    left: 15px;
    color: #7a8ca3;
    transform: translateY(-50%);
    pointer-events: none;
}

.sv-auth-input-icon svg {
    width: 20px;
    height: 20px;
    fill: none;
    stroke: currentColor;
    stroke-linecap: round;
    stroke-linejoin: round;
    stroke-width: 1.65;
}

.sv-auth-input-group input {
    width: 100%;
    height: 50px;
    padding: 0 48px 0 47px;
    border: 1px solid var(--sv-border);
    border-radius: var(--sv-radius);
    outline: none;
    color: var(--sv-text);
    background: #ffffff;
    font-size: 15px;
    transition:
        border-color 150ms ease,
        box-shadow 150ms ease,
        background-color 150ms ease;
}

.sv-auth-input-group input::placeholder {
    color: #98a2b3;
}

.sv-auth-input-group input:hover {
    border-color: var(--sv-border-strong);
}

.sv-auth-input-group input:focus {
    border-color: var(--sv-primary);
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
}

.sv-auth-input-group.is-invalid input {
    border-color: var(--sv-danger);
    background: #fffafa;
}

.sv-auth-input-group.is-invalid input:focus {
    box-shadow: 0 0 0 4px rgba(200, 60, 60, 0.1);
}

.sv-auth-password-toggle {
    position: absolute;
    top: 50%;
    right: 8px;
    display: grid;
    width: 36px;
    height: 36px;
    padding: 0;
    place-items: center;
    border: 0;
    border-radius: 8px;
    color: #667085;
    background: transparent;
    transform: translateY(-50%);
    cursor: pointer;
}

.sv-auth-password-toggle:hover {
    color: var(--sv-primary);
    background: #f2f6fc;
}

.sv-auth-password-toggle:focus-visible {
    outline: 3px solid rgba(37, 99, 235, 0.18);
    outline-offset: 1px;
}

.sv-auth-password-toggle svg {
    width: 20px;
    height: 20px;
    fill: none;
    stroke: currentColor;
    stroke-linecap: round;
    stroke-linejoin: round;
    stroke-width: 1.7;
}

.sv-auth-eye-hide,
.sv-auth-password-toggle.is-visible .sv-auth-eye-show {
    display: none;
}

.sv-auth-password-toggle.is-visible .sv-auth-eye-hide {
    display: block;
}

.sv-auth-field-help,
.sv-auth-field-error {
    margin: 7px 0 0;
    font-size: 12px;
    line-height: 1.5;
}

.sv-auth-field-help {
    color: #7a8699;
}

.sv-auth-field-error {
    color: var(--sv-danger);
    font-weight: 600;
}

/* =========================================================
   Password strength indicator
   ========================================================= */

.sv-auth-password-strength {
    display: grid;
    margin-top: 8px;
    gap: 4px;
}

.sv-auth-strength-bar {
    display: flex;
    gap: 4px;
}

.sv-auth-strength-bar span {
    flex: 1;
    height: 4px;
    border-radius: 4px;
    background: #e0e5ec;
    transition: background-color 200ms ease;
}

.sv-auth-strength-bar span.is-active.weak {
    background: var(--sv-danger);
}

.sv-auth-strength-bar span.is-active.medium {
    background: var(--sv-warning);
}

.sv-auth-strength-bar span.is-active.strong {
    background: var(--sv-success);
}

.sv-auth-strength-text {
    font-size: 11px;
    color: var(--sv-muted);
}

.sv-auth-strength-text.is-weak {
    color: var(--sv-danger);
}

.sv-auth-strength-text.is-medium {
    color: var(--sv-warning);
}

.sv-auth-strength-text.is-strong {
    color: var(--sv-success);
}

/* =========================================================
   Options row (remember me + forgot password)
   ========================================================= */

.sv-auth-options {
    display: flex;
    margin-top: -2px;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}

/* =========================================================
   Checkbox
   ========================================================= */

.sv-auth-checkbox {
    display: inline-flex;
    min-height: 32px;
    align-items: center;
    gap: 9px;
    color: #475467;
    cursor: pointer;
    user-select: none;
}

.sv-auth-checkbox input {
    position: absolute;
    width: 1px;
    height: 1px;
    overflow: hidden;
    opacity: 0;
    pointer-events: none;
}

.sv-auth-checkbox > span {
    position: relative;
    width: 18px;
    height: 18px;
    flex: 0 0 18px;
    border: 1px solid #b8c5d4;
    border-radius: 5px;
    background: #ffffff;
    transition:
        border-color 150ms ease,
        background-color 150ms ease,
        box-shadow 150ms ease;
}

.sv-auth-checkbox > span::after {
    position: absolute;
    top: 3px;
    left: 5px;
    width: 4px;
    height: 8px;
    border-right: 2px solid #ffffff;
    border-bottom: 2px solid #ffffff;
    content: "";
    opacity: 0;
    transform: rotate(45deg) scale(0.7);
    transition:
        opacity 120ms ease,
        transform 120ms ease;
}

.sv-auth-checkbox input:checked + span {
    border-color: var(--sv-primary);
    background: var(--sv-primary);
}

.sv-auth-checkbox input:checked + span::after {
    opacity: 1;
    transform: rotate(45deg) scale(1);
}

.sv-auth-checkbox input:focus-visible + span {
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
}

.sv-auth-checkbox em {
    font-size: 13px;
    font-style: normal;
}

/* =========================================================
   Links
   ========================================================= */

.sv-auth-link {
    color: var(--sv-primary);
    font-size: 13px;
    font-weight: 680;
    text-underline-offset: 3px;
}

.sv-auth-link:hover {
    color: var(--sv-primary-dark);
    text-decoration: underline;
}

.sv-auth-link:focus-visible {
    border-radius: 4px;
    outline: 3px solid rgba(37, 99, 235, 0.15);
    outline-offset: 3px;
}

.sv-auth-link-back {
    display: inline-flex;
    align-items: center;
    gap: 7px;
}

/* =========================================================
   Submit button
   ========================================================= */

.sv-auth-submit {
    position: relative;
    display: inline-flex;
    width: 100%;
    min-height: 50px;
    margin-top: 2px;
    padding: 12px 18px;
    align-items: center;
    justify-content: center;
    gap: 10px;
    border: 1px solid var(--sv-primary);
    border-radius: var(--sv-radius);
    color: #ffffff;
    background: var(--sv-primary);
    box-shadow: 0 10px 24px rgba(37, 99, 235, 0.18);
    font-size: 14px;
    font-weight: 720;
    cursor: pointer;
    transition:
        transform 150ms ease,
        background-color 150ms ease,
        border-color 150ms ease,
        box-shadow 150ms ease;
}

.sv-auth-submit:hover:not(:disabled) {
    border-color: var(--sv-primary-dark);
    background: var(--sv-primary-dark);
    box-shadow: 0 12px 28px rgba(37, 99, 235, 0.22);
    transform: translateY(-1px);
}

.sv-auth-submit:focus-visible {
    outline: 4px solid rgba(37, 99, 235, 0.18);
    outline-offset: 2px;
}

.sv-auth-submit:disabled {
    cursor: wait;
    opacity: 0.75;
    transform: none;
}

.sv-auth-button-spinner {
    display: none;
    width: 17px;
    height: 17px;
    border: 2px solid rgba(255, 255, 255, 0.45);
    border-top-color: #ffffff;
    border-radius: 50%;
    animation: sv-auth-spin 700ms linear infinite;
}

.sv-auth-submit.is-loading .sv-auth-button-spinner {
    display: inline-block;
}

@keyframes sv-auth-spin {
    to {
        transform: rotate(360deg);
    }
}

/* =========================================================
   Footer links
   ========================================================= */

.sv-auth-footer {
    margin: 23px 0 0;
    color: var(--sv-muted);
    font-size: 13px;
    text-align: center;
}

.sv-auth-security-note {
    display: grid;
    margin-top: 32px;
    padding-top: 22px;
    grid-template-columns: 20px minmax(0, 1fr);
    align-items: start;
    gap: 10px;
    border-top: 1px solid #e7ebf0;
    color: #7a8699;
}

.sv-auth-security-note svg {
    width: 19px;
    height: 19px;
    fill: none;
    stroke: #54718f;
    stroke-linecap: round;
    stroke-linejoin: round;
    stroke-width: 1.7;
}

.sv-auth-security-note p {
    margin: 0;
    font-size: 11px;
    line-height: 1.55;
}

/* =========================================================
   Responsif
   ========================================================= */

@media (max-width: 1180px) {
    .sv-auth-shell {
        grid-template-columns: minmax(0, 52fr) minmax(400px, 48fr);
    }

    .sv-auth-showcase-content {
        padding: 44px;
    }

    .sv-auth-copy {
        margin-top: 72px;
    }

    .sv-auth-copy h1 {
        font-size: clamp(38px, 5vw, 54px);
    }
}

@media (max-width: 920px) {
    .sv-auth-shell {
        display: block;
        min-height: 100vh;
        min-height: 100svh;
    }

    .sv-auth-showcase {
        display: none;
    }

    .sv-auth-panel {
        min-height: 100vh;
        min-height: 100svh;
        padding: 40px 24px;
        align-items: center;
    }

    .sv-auth-panel-inner {
        width: min(100%, 500px);
    }

    .sv-auth-mobile-brand {
        display: inline-flex;
        margin-bottom: 44px;
        align-items: center;
        gap: 12px;
        color: var(--sv-navy);
    }

    .sv-auth-mobile-brand .sv-auth-brand-icon {
        width: 44px;
        height: 44px;
        flex-basis: 44px;
        border-color: #c8d6e6;
        color: #ffffff;
        background: var(--sv-navy);
    }

    .sv-auth-mobile-brand small {
        color: #718096;
    }
}

@media (max-width: 560px) {
    .sv-auth-panel {
        padding:
            max(24px, env(safe-area-inset-top))
            18px
            max(24px, env(safe-area-inset-bottom));
        align-items: flex-start;
    }

    .sv-auth-mobile-brand {
        margin-top: 4px;
        margin-bottom: 36px;
    }

    .sv-auth-heading {
        margin-bottom: 25px;
    }

    .sv-auth-heading h2 {
        margin-top: 16px;
        font-size: 31px;
    }

    .sv-auth-heading p {
        font-size: 14px;
    }

    .sv-auth-submit {
        min-height: 52px;
    }

    .sv-auth-security-note {
        margin-top: 28px;
    }
}

@media (max-width: 380px) {
    .sv-auth-footer {
        text-align: left;
    }

    .sv-auth-options {
        align-items: stretch;
        flex-direction: column;
        gap: 5px;
    }

    .sv-auth-link {
        min-height: 34px;
        display: inline-flex;
        align-items: center;
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

/* =========================================================
   SmartVolt Auth V5 — colored icon refinement
   ========================================================= */
.sv-auth-benefit:nth-child(1) .sv-auth-benefit-icon {
    color: #bfdbfe;
    background: rgba(37, 99, 235, 0.18);
    border-color: rgba(147, 197, 253, 0.24);
}

.sv-auth-benefit:nth-child(2) .sv-auth-benefit-icon {
    color: #fde68a;
    background: rgba(217, 119, 6, 0.18);
    border-color: rgba(253, 230, 138, 0.24);
}

.sv-auth-benefit:nth-child(3) .sv-auth-benefit-icon {
    color: #a7f3d0;
    background: rgba(21, 148, 85, 0.18);
    border-color: rgba(167, 243, 208, 0.24);
}

.sv-auth-input-icon {
    color: #2563eb;
    background: #eef5ff;
    border-radius: 8px;
}

.sv-auth-field:nth-of-type(2) .sv-auth-input-icon {
    color: #7c3aed;
    background: #f4efff;
}

.sv-auth-alert-success > span:first-child {
    color: #159455;
    background: #eaf8f0;
}

.sv-auth-alert-error > span:first-child {
    color: #dc4c64;
    background: #fff0f3;
}
```

## `resources/views/auth/energy-history.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Pemakaian Listrik')
@section('page-title', 'Pemakaian Listrik')
@section('page-subtitle', 'Lihat riwayat, grafik, dan estimasi biaya listrik.')
@section('body-class', 'sv-energy-history-page')

@php
    $meterCollection = collect($devices ?? []);
    $paymentCollection = collect($paymentEstimations ?? []);
    $chartData = $chart ?? ['labels' => [], 'power' => [], 'energy' => []];
    $selectedMeterId = $filters['meter_id'] ?? null;
    $fromDate = $filters['date_from'] ?? now()->toDateString();
    $toDate = $filters['date_to'] ?? now()->toDateString();
@endphp

@section('system-status')
    <span class="sv-badge {{ ($summary['total_logs'] ?? 0) > 0 ? 'sv-badge-success' : 'sv-badge-neutral' }}">
        <span class="sv-status-dot" aria-hidden="true"></span>
        {{ ($summary['total_logs'] ?? 0) > 0 ? number_format((int) $summary['total_logs'], 0, ',', '.') . ' telemetry' : 'Belum ada data' }}
    </span>
@endsection

@section('content')
    <div class="sv-page-heading">
        <div class="sv-page-heading-copy">
            <h2>Riwayat dan analisis</h2>
            <p>Menampilkan data periode {{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }} sampai {{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }}.</p>
        </div>
        <div class="sv-page-heading-actions">
            <button type="button" class="sv-button sv-button-secondary" data-export-url="{{ route('energy.history.export', request()->query()) }}" data-energy-export>
                <x-feature-icon name="download" tone="blue" :size="16" variant="flat" />
                <span data-export-label>Unduh CSV</span>
            </button>
        </div>
    </div>

    <section class="sv-card sv-filter-card">
        <div class="sv-card-body">
            <form action="{{ route('energy.history') }}" method="GET" class="sv-filter-grid">
                <div class="sv-form-field">
                    <label for="device_id" class="sv-form-label">Meter listrik</label>
                    <select id="device_id" name="device_id" class="sv-form-control">
                        <option value="">Semua meter</option>
                        @foreach($meterCollection as $meter)
                            <option value="{{ $meter->id }}" {{ (string) $selectedMeterId === (string) $meter->id ? 'selected' : '' }}>
                                {{ $meter->room_name ?? $meter->room?->name ?? '-' }} · {{ $meter->meter_name ?? $meter->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="sv-form-field">
                    <label for="date_from" class="sv-form-label">Tanggal mulai</label>
                    <input type="date" id="date_from" name="date_from" class="sv-form-control" value="{{ $fromDate }}" required>
                </div>
                <div class="sv-form-field">
                    <label for="date_to" class="sv-form-label">Tanggal selesai</label>
                    <input type="date" id="date_to" name="date_to" class="sv-form-control" value="{{ $toDate }}" required>
                </div>
                <div class="sv-filter-actions sv-inline">
                    <button type="submit" class="sv-button sv-button-primary"><x-feature-icon name="filter" tone="blue" :size="15" variant="flat" /> Terapkan</button>
                    <a href="{{ route('energy.history') }}" class="sv-button sv-button-ghost">Atur ulang</a>
                </div>
            </form>

            <div class="sv-quick-filters" aria-label="Pilihan periode cepat">
                <button type="button" class="sv-quick-filter" data-date-range="today">Hari ini</button>
                <button type="button" class="sv-quick-filter" data-date-range="7days">7 hari</button>
                <button type="button" class="sv-quick-filter" data-date-range="month">Bulan ini</button>
                <button type="button" class="sv-quick-filter" data-date-range="last-month">Bulan lalu</button>
            </div>
        </div>
    </section>

    <section class="sv-history-metrics" aria-label="Ringkasan pemakaian listrik">
        <article class="sv-history-metric is-primary sv-history-metric--green">
            <x-feature-icon name="energy" tone="green" :size="20" variant="soft" />
            <span>Total Pemakaian</span>
            <strong>{{ number_format((float) ($summary['usage_kwh'] ?? 0), 4, ',', '.') }} kWh</strong>
            <small>Pemakaian pada periode yang dipilih.</small>
        </article>
        <article class="sv-history-metric sv-history-metric--amber">
            <x-feature-icon name="money" tone="amber" :size="20" variant="soft" />
            <span>Estimasi Pembayaran</span>
            @php
                $selectedEstimation = $paymentCollection->get('selected') ?? $paymentCollection->get('today') ?? [];
            @endphp
            <strong>Rp{{ number_format((float) ($selectedEstimation['estimated_cost'] ?? (($summary['usage_kwh'] ?? 0) * ($electricityTariff ?? 0))), 0, ',', '.') }}</strong>
            <small>Tarif Rp{{ number_format((float) ($electricityTariff ?? 0), 0, ',', '.') }}/kWh.</small>
        </article>
        <article class="sv-history-metric sv-history-metric--blue">
            <x-feature-icon name="bolt" tone="blue" :size="20" variant="soft" />
            <span>Daya Tertinggi</span>
            <strong>{{ number_format((float) ($summary['max_power'] ?? 0), 1, ',', '.') }} W</strong>
            <small>Rata-rata {{ number_format((float) ($summary['avg_power'] ?? 0), 1, ',', '.') }} W.</small>
        </article>
        <article class="sv-history-metric sv-history-metric--violet">
            <x-feature-icon name="gauge" tone="violet" :size="20" variant="soft" />
            <span>Rata-rata Tegangan</span>
            <strong>{{ number_format((float) ($summary['avg_voltage'] ?? 0), 1, ',', '.') }} V</strong>
            <small>Data terakhir {{ $summary['latest_time'] ?? 'belum tersedia' }}.</small>
        </article>
    </section>

    <section class="sv-section-grid sv-section-grid-two">
        <article class="sv-card">
            <header class="sv-card-header sv-card-header--with-icon">
                <x-feature-icon name="chart" tone="green" :size="19" variant="soft" />
                <div>
                    <h2>Analisis Pemakaian</h2>
                    <p>Data sesuai meter dan periode yang dipilih.</p>
                </div>
                <div class="sv-chart-tabs" role="tablist" aria-label="Jenis grafik">
                    <button type="button" class="sv-chart-tab is-active" data-history-chart-mode="power" aria-selected="true">Daya</button>
                    <button type="button" class="sv-chart-tab" data-history-chart-mode="energy" aria-selected="false">Energi</button>
                </div>
            </header>
            <div class="sv-card-body">
                @if(collect($chartData['labels'] ?? [])->isEmpty())
                    <div class="sv-empty-state" data-history-chart-empty>
                        <x-feature-icon name="chart" tone="green" :size="24" variant="soft" />
                        <h3>Belum ada data pada periode ini</h3>
                        <p>Pilih periode lain atau periksa koneksi perangkat.</p>
                    </div>
                @endif
                <div class="sv-chart-container {{ collect($chartData['labels'] ?? [])->isEmpty() ? 'is-hidden' : '' }}" data-history-chart-container>
                    <canvas id="energyHistoryChart" aria-label="Grafik riwayat pemakaian listrik"></canvas>
                </div>
            </div>
        </article>

        <article class="sv-card">
            <header class="sv-card-header sv-card-header--with-icon">
                <x-feature-icon name="money" tone="amber" :size="19" variant="soft" />
                <div>
                    <h2>Estimasi Pembayaran</h2>
                    <p>Menggunakan tarif yang tersimpan di Pengaturan.</p>
                </div>
            </header>
            <div class="sv-card-body">
                <div class="sv-billing-list">
                    @forelse($paymentCollection as $key => $estimation)
                        <details class="sv-billing-item" {{ $loop->first ? 'open' : '' }}>
                            <summary>
                                <strong>{{ $estimation['label'] ?? ucfirst((string) $key) }}</strong>
                                <span>Rp{{ number_format((float) ($estimation['estimated_cost'] ?? 0), 0, ',', '.') }}</span>
                            </summary>
                            <div class="sv-billing-details">
                                <dl>
                                    <dt>Periode</dt><dd>{{ $estimation['period'] ?? '-' }}</dd>
                                    <dt>Pemakaian</dt><dd>{{ number_format((float) ($estimation['usage_kwh'] ?? 0), 4, ',', '.') }} kWh</dd>
                                    <dt>Tarif</dt><dd>Rp{{ number_format((float) ($estimation['tariff'] ?? $electricityTariff ?? 0), 0, ',', '.') }}/kWh</dd>
                                    <dt>Rumus</dt><dd>{{ $estimation['formula'] ?? '-' }}</dd>
                                </dl>
                            </div>
                        </details>
                    @empty
                        <div class="sv-empty-state"><p>Estimasi pembayaran belum tersedia.</p></div>
                    @endforelse
                </div>
            </div>
        </article>
    </section>

    <section class="sv-card">
        <header class="sv-card-header sv-card-header--with-icon">
            <x-feature-icon name="document" tone="blue" :size="19" variant="soft" />
            <div>
                <h2>Riwayat Pemakaian Listrik</h2>
                <p>Satu pembacaan terbaru untuk setiap meter pada periode filter.</p>
            </div>
            <span class="sv-badge sv-badge-neutral">{{ $logs->total() }} meter</span>
        </header>
        <div class="sv-card-body">
            @if($logs->isEmpty())
                <div class="sv-empty-state">
                    <x-feature-icon name="document" tone="blue" :size="24" variant="soft" />
                    <h3>Data riwayat tidak ditemukan</h3>
                    <p>Tidak ada pembacaan meter yang sesuai dengan filter saat ini.</p>
                </div>
            @else
                <div class="sv-table-wrap">
                    <table class="sv-table">
                        <thead>
                            <tr>
                                <th>Ruangan</th>
                                <th>Meter</th>
                                <th>Waktu Data</th>
                                <th class="is-numeric">Tegangan</th>
                                <th class="is-numeric">Arus</th>
                                <th class="is-numeric">Daya</th>
                                <th class="is-numeric">Energi</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td>{{ $log->room_name ?? '-' }}</td>
                                    <td>{{ $log->meter_name ?? $log->device_name ?? '-' }}</td>
                                    <td>{{ $log->observed_at?->format('d/m/Y H:i:s') ?? '-' }}</td>
                                    <td class="is-numeric">{{ number_format((float) ($log->voltage ?? 0), 1, ',', '.') }} V</td>
                                    <td class="is-numeric">{{ number_format((float) ($log->current ?? 0), 3, ',', '.') }} A</td>
                                    <td class="is-numeric">{{ number_format((float) ($log->power ?? 0), 1, ',', '.') }} W</td>
                                    <td class="is-numeric">{{ number_format((float) ($log->energy ?? 0), 4, ',', '.') }} kWh</td>
                                    <td><span class="sv-badge sv-badge-success">Terbaru</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="sv-pagination">{{ $logs->links() }}</div>
            @endif
        </div>
    </section>

    <script type="application/json" id="smartvolt-history-data">{!! json_encode([
        'chart' => $chartData,
        'exportUrl' => route('energy.history.export', request()->query()),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/chart.umd.min.js') }}?v=4.5.1" defer></script>
    <script src="{{ asset('assets/js/smartvolt-energy-history.js') }}?v=20260730-v5" defer></script>
@endpush
```

## `resources/views/auth/forgot-password.blade.php`

```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#173B66">

    <title>Lupa Kata Sandi | SmartVolt</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/smartvolt-auth.css') }}?v=20260730-v5">
</head>

<body class="sv-auth-page">
    <main class="sv-auth-shell">
        <section
            class="sv-auth-showcase"
            aria-label="Informasi SmartVolt"
        >
            <svg
                class="sv-auth-network"
                viewBox="0 0 900 900"
                aria-hidden="true"
                focusable="false"
            >
                <g fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M70 130H280L360 210H590L690 110H840"/>
                    <path d="M40 390H190L290 290H470L560 380H820"/>
                    <path d="M110 680H300L400 580H620L720 680H860"/>
                    <path d="M240 80V260L160 340V520"/>
                    <path d="M650 80V250L740 340V520L650 610V820"/>
                    <path d="M470 210V470L390 550V790"/>
                </g>

                <g fill="currentColor">
                    <circle cx="70" cy="130" r="5"/>
                    <circle cx="360" cy="210" r="5"/>
                    <circle cx="690" cy="110" r="5"/>
                    <circle cx="190" cy="390" r="5"/>
                    <circle cx="470" cy="290" r="5"/>
                    <circle cx="560" cy="380" r="5"/>
                    <circle cx="300" cy="680" r="5"/>
                    <circle cx="620" cy="580" r="5"/>
                    <circle cx="740" cy="340" r="5"/>
                    <circle cx="390" cy="550" r="5"/>
                </g>
            </svg>

            <div class="sv-auth-showcase-content">
                <a
                    href="{{ url('/') }}"
                    class="sv-auth-brand"
                    aria-label="SmartVolt"
                >
                    <span class="sv-auth-brand-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path
                                d="M13.2 2.5 5.8 13h5.1l-.7 8.5L18.4 10h-5.1l-.1-7.5Z"
                                fill="currentColor"
                            />
                        </svg>
                    </span>

                    <span>
                        <strong>SmartVolt</strong>
                        <small>Calm Energy Control</small>
                    </span>
                </a>

                <div class="sv-auth-copy">
                    <p class="sv-auth-eyebrow">
                        Monitoring dan kontrol energi berbasis IoT
                    </p>

                    <h1>
                        Kendalikan energi rumah dengan lebih tenang.
                    </h1>

                    <p class="sv-auth-description">
                        Pantau daya, energi, estimasi biaya, dan perangkat
                        listrik melalui satu sistem yang jelas dan mudah
                        dipahami.
                    </p>
                </div>

                <div class="sv-auth-benefits">
                    <article class="sv-auth-benefit">
                        <span class="sv-auth-benefit-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M4 19V9m5 10V5m5 14v-7m5 7V3"/>
                            </svg>
                        </span>

                        <div>
                            <h2>Data listrik yang mudah dibaca</h2>
                            <p>
                                Lihat tegangan, arus, daya, energi, dan
                                perubahan penggunaan secara berkala.
                            </p>
                        </div>
                    </article>

                    <article class="sv-auth-benefit">
                        <span class="sv-auth-benefit-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <rect x="5" y="3" width="14" height="18" rx="3"/>
                                <path d="M9 8h6M9 12h6M9 16h3"/>
                            </svg>
                        </span>

                        <div>
                            <h2>Estimasi biaya yang transparan</h2>
                            <p>
                                Pemakaian kWh dihitung berdasarkan data meter
                                dan dikalikan dengan tarif listrik.
                            </p>
                        </div>
                    </article>

                    <article class="sv-auth-benefit">
                        <span class="sv-auth-benefit-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M8 12h8"/>
                                <rect x="3" y="7" width="6" height="10" rx="2"/>
                                <rect x="15" y="7" width="6" height="10" rx="2"/>
                            </svg>
                        </span>

                        <div>
                            <h2>Kontrol perangkat per ruangan</h2>
                            <p>
                                Nyalakan atau matikan perangkat melalui relay
                                ketika ESP32 dan MQTT terhubung.
                            </p>
                        </div>
                    </article>
                </div>

                <div class="sv-auth-flow" aria-label="Alur data SmartVolt">
                    <div class="sv-auth-flow-label">Alur sistem</div>

                    <div class="sv-auth-flow-items">
                        <span>
                            <strong>PZEM</strong>
                            <small>Membaca listrik</small>
                        </span>

                        <i aria-hidden="true">→</i>

                        <span>
                            <strong>ESP32</strong>
                            <small>Mengirim data</small>
                        </span>

                        <i aria-hidden="true">→</i>

                        <span>
                            <strong>SmartVolt</strong>
                            <small>Menampilkan hasil</small>
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <section class="sv-auth-panel">
            <div class="sv-auth-panel-inner">
                <div class="sv-auth-mobile-brand">
                    <span class="sv-auth-brand-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path
                                d="M13.2 2.5 5.8 13h5.1l-.7 8.5L18.4 10h-5.1l-.1-7.5Z"
                                fill="currentColor"
                            />
                        </svg>
                    </span>

                    <span>
                        <strong>SmartVolt</strong>
                        <small>Monitoring energi rumah</small>
                    </span>
                </div>

                <div class="sv-auth-heading">
                    <span class="sv-auth-status-chip">
                        <span aria-hidden="true"></span>
                        Akses aman SmartVolt
                    </span>

                    <h2>Lupa kata sandi</h2>

                    <p>
                        Masukkan alamat email akun SmartVolt. Tautan
                        pengaturan ulang akan dikirim ke email tersebut.
                    </p>
                </div>

                @if (session('status'))
                    <div
                        class="sv-auth-alert sv-auth-alert-success"
                        role="status"
                        aria-live="polite"
                    >
                        <span aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="m5 12 4 4L19 6"/>
                            </svg>
                        </span>

                        <div>{{ session('status') }}</div>
                    </div>
                @endif

                @if ($errors->any())
                    <div
                        class="sv-auth-alert sv-auth-alert-error"
                        role="alert"
                        aria-live="assertive"
                    >
                        <span aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M12 7v6m0 4h.01"/>
                            </svg>
                        </span>

                        <div>
                            <strong>Periksa kembali data yang dimasukkan.</strong>
                            <p>{{ $errors->first() }}</p>
                        </div>
                    </div>
                @endif

                <form
                    id="forgotForm"
                    class="sv-auth-form"
                    action="{{ route('password.email') }}"
                    method="POST"
                    novalidate
                >
                    @csrf

                    <div class="sv-auth-field">
                        <label for="forgot_email">Alamat email</label>

                        <div
                            class="sv-auth-input-group
                                @error('email') is-invalid @enderror"
                        >
                            <span class="sv-auth-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <rect x="3" y="5" width="18" height="14" rx="3"/>
                                    <path d="m5 8 7 5 7-5"/>
                                </svg>
                            </span>

                            <input
                                type="email"
                                id="forgot_email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="nama@email.com"
                                autocomplete="email"
                                inputmode="email"
                                aria-describedby="forgotEmailHelp forgotEmailError"
                                @error('email') aria-invalid="true" @enderror
                                required
                                autofocus
                            >
                        </div>

                        @error('email')
                            <p
                                id="forgotEmailError"
                                class="sv-auth-field-error"
                            >
                                {{ $message }}
                            </p>
                        @else
                            <p
                                id="forgotEmailHelp"
                                class="sv-auth-field-help"
                            >
                                Pastikan email sesuai dengan akun yang
                                terdaftar.
                            </p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        id="forgotButton"
                        class="sv-auth-submit"
                    >
                        <span
                            class="sv-auth-button-spinner"
                            aria-hidden="true"
                        ></span>

                        <span id="forgotButtonText">
                            Kirim tautan pengaturan ulang
                        </span>
                    </button>
                </form>

                <p class="sv-auth-footer">
                    <a
                        href="{{ route('login') }}"
                        class="sv-auth-link sv-auth-link-back"
                    >
                        <span aria-hidden="true">←</span>
                        Kembali ke halaman masuk
                    </a>
                </p>

                <div class="sv-auth-security-note">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 3 5 6v5c0 4.6 2.7 8 7 10 4.3-2 7-5.4 7-10V6l-7-3Z"/>
                        <path d="m9 12 2 2 4-5"/>
                    </svg>

                    <p>
                        Akses akun dilindungi oleh sesi Laravel dan token
                        keamanan formulir.
                    </p>
                </div>
            </div>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('forgotForm');
            const button = document.getElementById('forgotButton');
            const buttonText = document.getElementById('forgotButtonText');

            if (form && button && buttonText) {
                form.addEventListener('submit', function (event) {
                    if (! form.checkValidity()) {
                        event.preventDefault();
                        form.reportValidity();
                        return;
                    }

                    button.disabled = true;
                    button.classList.add('is-loading');
                    buttonText.textContent = 'Mengirim tautan...';
                    button.setAttribute('aria-busy', 'true');
                });
            }
        });
    </script>
</body>
</html>
```

## `resources/views/auth/login.blade.php`

```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#173B66">

    <title>Masuk | SmartVolt</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/smartvolt-auth.css') }}?v=20260730-v5">
</head>

<body class="sv-auth-page">

    <main class="sv-auth-shell">
        <section
            class="sv-auth-showcase"
            aria-label="Informasi SmartVolt"
        >
            <svg
                class="sv-auth-network"
                viewBox="0 0 900 900"
                aria-hidden="true"
                focusable="false"
            >
                <g fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M70 130H280L360 210H590L690 110H840"/>
                    <path d="M40 390H190L290 290H470L560 380H820"/>
                    <path d="M110 680H300L400 580H620L720 680H860"/>
                    <path d="M240 80V260L160 340V520"/>
                    <path d="M650 80V250L740 340V520L650 610V820"/>
                    <path d="M470 210V470L390 550V790"/>
                </g>

                <g fill="currentColor">
                    <circle cx="70" cy="130" r="5"/>
                    <circle cx="360" cy="210" r="5"/>
                    <circle cx="690" cy="110" r="5"/>
                    <circle cx="190" cy="390" r="5"/>
                    <circle cx="470" cy="290" r="5"/>
                    <circle cx="560" cy="380" r="5"/>
                    <circle cx="300" cy="680" r="5"/>
                    <circle cx="620" cy="580" r="5"/>
                    <circle cx="740" cy="340" r="5"/>
                    <circle cx="390" cy="550" r="5"/>
                </g>
            </svg>

            <div class="sv-auth-showcase-content">
                <a
                    href="{{ url('/') }}"
                    class="sv-auth-brand"
                    aria-label="SmartVolt"
                >
                    <span class="sv-auth-brand-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path
                                d="M13.2 2.5 5.8 13h5.1l-.7 8.5L18.4 10h-5.1l-.1-7.5Z"
                                fill="currentColor"
                            />
                        </svg>
                    </span>

                    <span>
                        <strong>SmartVolt</strong>
                        <small>Calm Energy Control</small>
                    </span>
                </a>

                <div class="sv-auth-copy">
                    <p class="sv-auth-eyebrow">
                        Monitoring dan kontrol energi berbasis IoT
                    </p>

                    <h1>
                        Kendalikan energi rumah dengan lebih tenang.
                    </h1>

                    <p class="sv-auth-description">
                        Pantau daya, energi, estimasi biaya, dan perangkat
                        listrik melalui satu sistem yang jelas dan mudah
                        dipahami.
                    </p>
                </div>

                <div class="sv-auth-benefits">
                    <article class="sv-auth-benefit">
                        <span class="sv-auth-benefit-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M4 19V9m5 10V5m5 14v-7m5 7V3"/>
                            </svg>
                        </span>

                        <div>
                            <h2>Data listrik yang mudah dibaca</h2>
                            <p>
                                Lihat tegangan, arus, daya, energi, dan
                                perubahan penggunaan secara berkala.
                            </p>
                        </div>
                    </article>

                    <article class="sv-auth-benefit">
                        <span class="sv-auth-benefit-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <rect x="5" y="3" width="14" height="18" rx="3"/>
                                <path d="M9 8h6M9 12h6M9 16h3"/>
                            </svg>
                        </span>

                        <div>
                            <h2>Estimasi biaya yang transparan</h2>
                            <p>
                                Pemakaian kWh dihitung berdasarkan data meter
                                dan dikalikan dengan tarif listrik.
                            </p>
                        </div>
                    </article>

                    <article class="sv-auth-benefit">
                        <span class="sv-auth-benefit-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M8 12h8"/>
                                <rect x="3" y="7" width="6" height="10" rx="2"/>
                                <rect x="15" y="7" width="6" height="10" rx="2"/>
                            </svg>
                        </span>

                        <div>
                            <h2>Kontrol perangkat per ruangan</h2>
                            <p>
                                Nyalakan atau matikan perangkat melalui relay
                                ketika ESP32 dan MQTT terhubung.
                            </p>
                        </div>
                    </article>
                </div>

                <div class="sv-auth-flow" aria-label="Alur data SmartVolt">
                    <div class="sv-auth-flow-label">Alur sistem</div>

                    <div class="sv-auth-flow-items">
                        <span>
                            <strong>PZEM</strong>
                            <small>Membaca listrik</small>
                        </span>

                        <i aria-hidden="true">→</i>

                        <span>
                            <strong>ESP32</strong>
                            <small>Mengirim data</small>
                        </span>

                        <i aria-hidden="true">→</i>

                        <span>
                            <strong>SmartVolt</strong>
                            <small>Menampilkan hasil</small>
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <section class="sv-auth-panel">
            <div class="sv-auth-panel-inner">
                <div class="sv-auth-mobile-brand">
                    <span class="sv-auth-brand-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path
                                d="M13.2 2.5 5.8 13h5.1l-.7 8.5L18.4 10h-5.1l-.1-7.5Z"
                                fill="currentColor"
                            />
                        </svg>
                    </span>

                    <span>
                        <strong>SmartVolt</strong>
                        <small>Monitoring energi rumah</small>
                    </span>
                </div>

                <div class="sv-auth-heading">
                    <span class="sv-auth-status-chip">
                        <span aria-hidden="true"></span>
                        Akses aman SmartVolt
                    </span>

                    <h2>Selamat datang kembali</h2>

                    <p>
                        Masuk untuk memantau dan mengontrol listrik rumah
                        Anda.
                    </p>
                </div>

                @if (session('status'))
                    <div
                        class="sv-auth-alert sv-auth-alert-success"
                        role="status"
                        aria-live="polite"
                    >
                        <span aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="m5 12 4 4L19 6"/>
                            </svg>
                        </span>

                        <div>{{ session('status') }}</div>
                    </div>
                @endif

                @if ($errors->any())
                    <div
                        class="sv-auth-alert sv-auth-alert-error"
                        role="alert"
                        aria-live="assertive"
                    >
                        <span aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M12 7v6m0 4h.01"/>
                            </svg>
                        </span>

                        <div>
                            <strong>Periksa kembali data yang dimasukkan.</strong>
                            <p>{{ $errors->first() }}</p>
                        </div>
                    </div>
                @endif

                <form
                    id="loginForm"
                    class="sv-auth-form"
                    action="{{ route('login.process') }}"
                    method="POST"
                    novalidate
                >
                    @csrf

                    <div class="sv-auth-field">
                        <label for="email">Alamat email</label>

                        <div
                            class="sv-auth-input-group
                                @error('email') is-invalid @enderror"
                        >
                            <span class="sv-auth-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <rect x="3" y="5" width="18" height="14" rx="3"/>
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
                            <p
                                id="emailError"
                                class="sv-auth-field-error"
                            >
                                {{ $message }}
                            </p>
                        @else
                            <p
                                id="emailHelp"
                                class="sv-auth-field-help"
                            >
                                Gunakan email yang terdaftar pada akun
                                SmartVolt.
                            </p>
                        @enderror
                    </div>

                    <div class="sv-auth-field">
                        <label for="password">Kata sandi</label>

                        <div
                            class="sv-auth-input-group
                                @error('password') is-invalid @enderror"
                        >
                            <span class="sv-auth-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <rect x="5" y="10" width="14" height="10" rx="3"/>
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
                                class="sv-auth-password-toggle"
                                aria-label="Tampilkan kata sandi"
                                aria-pressed="false"
                            >
                                <svg
                                    class="sv-auth-eye-show"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6S2.5 12 2.5 12Z"/>
                                    <circle cx="12" cy="12" r="2.5"/>
                                </svg>

                                <svg
                                    class="sv-auth-eye-hide"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path d="m4 4 16 16"/>
                                    <path d="M10.6 6.2A10 10 0 0 1 12 6c6 0 9.5 6 9.5 6a16 16 0 0 1-2.4 3.1M6.2 7.3A15.7 15.7 0 0 0 2.5 12S6 18 12 18a9.8 9.8 0 0 0 2.1-.2"/>
                                </svg>
                            </button>
                        </div>

                        @error('password')
                            <p
                                id="passwordError"
                                class="sv-auth-field-error"
                            >
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="sv-auth-options">
                        <label class="sv-auth-checkbox">
                            <input
                                type="checkbox"
                                name="remember"
                                value="1"
                                {{ old('remember') ? 'checked' : '' }}
                            >
                            <span aria-hidden="true"></span>
                            <em>Ingat saya</em>
                        </label>

                        <a
                            href="{{ route('password.request') }}"
                            class="sv-auth-link"
                        >
                            Lupa kata sandi?
                        </a>
                    </div>

                    <button
                        type="submit"
                        id="loginButton"
                        class="sv-auth-submit"
                    >
                        <span
                            class="sv-auth-button-spinner"
                            aria-hidden="true"
                        ></span>

                        <span id="loginButtonText">Masuk</span>
                    </button>
                </form>

                <p class="sv-auth-footer">
                    Belum memiliki akun?
                    <a
                        href="{{ route('register') }}"
                        class="sv-auth-link"
                    >
                        Daftar akun
                    </a>
                </p>

                <div class="sv-auth-security-note">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 3 5 6v5c0 4.6 2.7 8 7 10 4.3-2 7-5.4 7-10V6l-7-3Z"/>
                        <path d="m9 12 2 2 4-5"/>
                    </svg>

                    <p>
                        Akses akun dilindungi oleh sesi Laravel dan token
                        keamanan formulir.
                    </p>
                </div>
            </div>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const passwordInput = document.getElementById('password');
            const togglePassword = document.getElementById('togglePassword');

            if (passwordInput && togglePassword) {
                togglePassword.addEventListener('click', function () {
                    const shouldShow = passwordInput.type === 'password';

                    passwordInput.type = shouldShow ? 'text' : 'password';
                    togglePassword.classList.toggle('is-visible', shouldShow);
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

            const form = document.getElementById('loginForm');
            const button = document.getElementById('loginButton');
            const buttonText = document.getElementById('loginButtonText');

            if (form && button && buttonText) {
                form.addEventListener('submit', function (event) {
                    if (! form.checkValidity()) {
                        event.preventDefault();
                        form.reportValidity();
                        return;
                    }

                    button.disabled = true;
                    button.classList.add('is-loading');
                    buttonText.textContent = 'Memeriksa akun...';
                    button.setAttribute('aria-busy', 'true');
                });
            }
        });
    </script>
</body>
</html>
```

## `resources/views/auth/logout.blade.php`

```blade
<form action="{{ route('logout') }}" method="POST" data-loading-form>
    @csrf
    <button type="submit" class="sv-button sv-button-ghost sv-button-sm" data-loading-text="Keluar...">
        <x-icon name="logout" :size="16" />
        <span data-button-label>Keluar</span>
    </button>
</form>
```

## `resources/views/auth/register.blade.php`

```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#173B66">

    <title>Daftar Akun | SmartVolt</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/smartvolt-auth.css') }}?v=20260730-v5">
</head>

<body class="sv-auth-page">
    <main class="sv-auth-shell">
        <section
            class="sv-auth-showcase"
            aria-label="Informasi SmartVolt"
        >
            <svg
                class="sv-auth-network"
                viewBox="0 0 900 900"
                aria-hidden="true"
                focusable="false"
            >
                <g fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M70 130H280L360 210H590L690 110H840"/>
                    <path d="M40 390H190L290 290H470L560 380H820"/>
                    <path d="M110 680H300L400 580H620L720 680H860"/>
                    <path d="M240 80V260L160 340V520"/>
                    <path d="M650 80V250L740 340V520L650 610V820"/>
                    <path d="M470 210V470L390 550V790"/>
                </g>

                <g fill="currentColor">
                    <circle cx="70" cy="130" r="5"/>
                    <circle cx="360" cy="210" r="5"/>
                    <circle cx="690" cy="110" r="5"/>
                    <circle cx="190" cy="390" r="5"/>
                    <circle cx="470" cy="290" r="5"/>
                    <circle cx="560" cy="380" r="5"/>
                    <circle cx="300" cy="680" r="5"/>
                    <circle cx="620" cy="580" r="5"/>
                    <circle cx="740" cy="340" r="5"/>
                    <circle cx="390" cy="550" r="5"/>
                </g>
            </svg>

            <div class="sv-auth-showcase-content">
                <a
                    href="{{ url('/') }}"
                    class="sv-auth-brand"
                    aria-label="SmartVolt"
                >
                    <span class="sv-auth-brand-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path
                                d="M13.2 2.5 5.8 13h5.1l-.7 8.5L18.4 10h-5.1l-.1-7.5Z"
                                fill="currentColor"
                            />
                        </svg>
                    </span>

                    <span>
                        <strong>SmartVolt</strong>
                        <small>Calm Energy Control</small>
                    </span>
                </a>

                <div class="sv-auth-copy">
                    <p class="sv-auth-eyebrow">
                        Monitoring dan kontrol energi berbasis IoT
                    </p>

                    <h1>
                        Kendalikan energi rumah dengan lebih tenang.
                    </h1>

                    <p class="sv-auth-description">
                        Pantau daya, energi, estimasi biaya, dan perangkat
                        listrik melalui satu sistem yang jelas dan mudah
                        dipahami.
                    </p>
                </div>

                <div class="sv-auth-benefits">
                    <article class="sv-auth-benefit">
                        <span class="sv-auth-benefit-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M4 19V9m5 10V5m5 14v-7m5 7V3"/>
                            </svg>
                        </span>

                        <div>
                            <h2>Data listrik yang mudah dibaca</h2>
                            <p>
                                Lihat tegangan, arus, daya, energi, dan
                                perubahan penggunaan secara berkala.
                            </p>
                        </div>
                    </article>

                    <article class="sv-auth-benefit">
                        <span class="sv-auth-benefit-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <rect x="5" y="3" width="14" height="18" rx="3"/>
                                <path d="M9 8h6M9 12h6M9 16h3"/>
                            </svg>
                        </span>

                        <div>
                            <h2>Estimasi biaya yang transparan</h2>
                            <p>
                                Pemakaian kWh dihitung berdasarkan data meter
                                dan dikalikan dengan tarif listrik.
                            </p>
                        </div>
                    </article>

                    <article class="sv-auth-benefit">
                        <span class="sv-auth-benefit-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M8 12h8"/>
                                <rect x="3" y="7" width="6" height="10" rx="2"/>
                                <rect x="15" y="7" width="6" height="10" rx="2"/>
                            </svg>
                        </span>

                        <div>
                            <h2>Kontrol perangkat per ruangan</h2>
                            <p>
                                Nyalakan atau matikan perangkat melalui relay
                                ketika ESP32 dan MQTT terhubung.
                            </p>
                        </div>
                    </article>
                </div>

                <div class="sv-auth-flow" aria-label="Alur data SmartVolt">
                    <div class="sv-auth-flow-label">Alur sistem</div>

                    <div class="sv-auth-flow-items">
                        <span>
                            <strong>PZEM</strong>
                            <small>Membaca listrik</small>
                        </span>

                        <i aria-hidden="true">→</i>

                        <span>
                            <strong>ESP32</strong>
                            <small>Mengirim data</small>
                        </span>

                        <i aria-hidden="true">→</i>

                        <span>
                            <strong>SmartVolt</strong>
                            <small>Menampilkan hasil</small>
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <section class="sv-auth-panel">
            <div class="sv-auth-panel-inner">
                <div class="sv-auth-mobile-brand">
                    <span class="sv-auth-brand-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path
                                d="M13.2 2.5 5.8 13h5.1l-.7 8.5L18.4 10h-5.1l-.1-7.5Z"
                                fill="currentColor"
                            />
                        </svg>
                    </span>

                    <span>
                        <strong>SmartVolt</strong>
                        <small>Monitoring energi rumah</small>
                    </span>
                </div>

                <div class="sv-auth-heading">
                    <span class="sv-auth-status-chip">
                        <span aria-hidden="true"></span>
                        Akses aman SmartVolt
                    </span>

                    <h2>Buat akun baru</h2>

                    <p>
                        Daftar untuk mulai memantau dan mengontrol listrik
                        rumah Anda.
                    </p>
                </div>

                @if (session('status'))
                    <div
                        class="sv-auth-alert sv-auth-alert-success"
                        role="status"
                        aria-live="polite"
                    >
                        <span aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="m5 12 4 4L19 6"/>
                            </svg>
                        </span>

                        <div>{{ session('status') }}</div>
                    </div>
                @endif

                @if ($errors->any())
                    <div
                        class="sv-auth-alert sv-auth-alert-error"
                        role="alert"
                        aria-live="assertive"
                    >
                        <span aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M12 7v6m0 4h.01"/>
                            </svg>
                        </span>

                        <div>
                            <strong>Periksa kembali data yang dimasukkan.</strong>
                            <p>{{ $errors->first() }}</p>
                        </div>
                    </div>
                @endif

                <form
                    id="registerForm"
                    class="sv-auth-form"
                    action="{{ route('register.process') }}"
                    method="POST"
                    novalidate
                >
                    @csrf

                    <div class="sv-auth-field">
                        <label for="name">Nama lengkap</label>

                        <div
                            class="sv-auth-input-group
                                @error('name') is-invalid @enderror"
                        >
                            <span class="sv-auth-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                            </span>

                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="{{ old('name') }}"
                                placeholder="Nama lengkap Anda"
                                autocomplete="name"
                                aria-describedby="nameHelp nameError"
                                @error('name') aria-invalid="true" @enderror
                                required
                                maxlength="100"
                                autofocus
                            >
                        </div>

                        @error('name')
                            <p
                                id="nameError"
                                class="sv-auth-field-error"
                            >
                                {{ $message }}
                            </p>
                        @else
                            <p
                                id="nameHelp"
                                class="sv-auth-field-help"
                            >
                                Masukkan nama lengkap Anda.
                            </p>
                        @enderror
                    </div>

                    <div class="sv-auth-field">
                        <label for="email">Alamat email</label>

                        <div
                            class="sv-auth-input-group
                                @error('email') is-invalid @enderror"
                        >
                            <span class="sv-auth-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <rect x="3" y="5" width="18" height="14" rx="3"/>
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
                            >
                        </div>

                        @error('email')
                            <p
                                id="emailError"
                                class="sv-auth-field-error"
                            >
                                {{ $message }}
                            </p>
                        @else
                            <p
                                id="emailHelp"
                                class="sv-auth-field-help"
                            >
                                Gunakan email aktif untuk menerima notifikasi
                                dan tautan reset password.
                            </p>
                        @enderror
                    </div>

                    <div class="sv-auth-field">
                        <label for="password">Kata sandi</label>

                        <div
                            class="sv-auth-input-group
                                @error('password') is-invalid @enderror"
                        >
                            <span class="sv-auth-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <rect x="5" y="10" width="14" height="10" rx="3"/>
                                    <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                                </svg>
                            </span>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Minimal 8 karakter"
                                autocomplete="new-password"
                                aria-describedby="passwordHelp passwordError"
                                @error('password') aria-invalid="true" @enderror
                                required
                                minlength="8"
                            >

                            <button
                                type="button"
                                id="togglePassword"
                                class="sv-auth-password-toggle"
                                aria-label="Tampilkan kata sandi"
                                aria-pressed="false"
                            >
                                <svg
                                    class="sv-auth-eye-show"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6S2.5 12 2.5 12Z"/>
                                    <circle cx="12" cy="12" r="2.5"/>
                                </svg>

                                <svg
                                    class="sv-auth-eye-hide"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path d="m4 4 16 16"/>
                                    <path d="M10.6 6.2A10 10 0 0 1 12 6c6 0 9.5 6 9.5 6a16 16 0 0 1-2.4 3.1M6.2 7.3A15.7 15.7 0 0 0 2.5 12S6 18 12 18a9.8 9.8 0 0 0 2.1-.2"/>
                                </svg>
                            </button>
                        </div>

                        <div class="sv-auth-password-strength" id="passwordStrength">
                            <div class="sv-auth-strength-bar">
                                <span data-strength="1"></span>
                                <span data-strength="2"></span>
                                <span data-strength="3"></span>
                                <span data-strength="4"></span>
                            </div>
                            <span class="sv-auth-strength-text" id="strengthText">
                                Minimal 6 karakter
                            </span>
                        </div>

                        @error('password')
                            <p
                                id="passwordError"
                                class="sv-auth-field-error"
                            >
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="sv-auth-field">
                        <label for="password_confirmation">Konfirmasi kata sandi</label>

                        <div
                            class="sv-auth-input-group
                                @error('password') is-invalid @enderror"
                        >
                            <span class="sv-auth-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <rect x="5" y="10" width="14" height="10" rx="3"/>
                                    <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                                </svg>
                            </span>

                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                placeholder="Ulangi kata sandi"
                                autocomplete="new-password"
                                aria-describedby="passwordConfirmationHelp"
                                required
                                minlength="8"
                            >

                            <button
                                type="button"
                                id="togglePasswordConfirm"
                                class="sv-auth-password-toggle"
                                aria-label="Tampilkan konfirmasi kata sandi"
                                aria-pressed="false"
                            >
                                <svg
                                    class="sv-auth-eye-show"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6S2.5 12 2.5 12Z"/>
                                    <circle cx="12" cy="12" r="2.5"/>
                                </svg>

                                <svg
                                    class="sv-auth-eye-hide"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path d="m4 4 16 16"/>
                                    <path d="M10.6 6.2A10 10 0 0 1 12 6c6 0 9.5 6 9.5 6a16 16 0 0 1-2.4 3.1M6.2 7.3A15.7 15.7 0 0 0 2.5 12S6 18 12 18a9.8 9.8 0 0 0 2.1-.2"/>
                                </svg>
                            </button>
                        </div>

                        <p
                            id="passwordConfirmationHelp"
                            class="sv-auth-field-help"
                        >
                            Ketik ulang kata sandi yang sama.
                        </p>
                    </div>

                    <button
                        type="submit"
                        id="registerButton"
                        class="sv-auth-submit"
                    >
                        <span
                            class="sv-auth-button-spinner"
                            aria-hidden="true"
                        ></span>

                        <span id="registerButtonText">Daftar Akun</span>
                    </button>
                </form>

                <p class="sv-auth-footer">
                    Sudah memiliki akun?
                    <a
                        href="{{ route('login') }}"
                        class="sv-auth-link"
                    >
                        Masuk
                    </a>
                </p>

                <div class="sv-auth-security-note">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 3 5 6v5c0 4.6 2.7 8 7 10 4.3-2 7-5.4 7-10V6l-7-3Z"/>
                        <path d="m9 12 2 2 4-5"/>
                    </svg>

                    <p>
                        Data Anda dilindungi dan tidak akan dibagikan kepada
                        pihak ketiga tanpa izin Anda.
                    </p>
                </div>
            </div>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // =========================================================
            // Toggle password visibility
            // =========================================================

            const setupToggle = function (inputId, toggleId) {
                const input = document.getElementById(inputId);
                const toggle = document.getElementById(toggleId);

                if (! input || ! toggle) {
                    return;
                }

                toggle.addEventListener('click', function () {
                    const shouldShow = input.type === 'password';

                    input.type = shouldShow ? 'text' : 'password';
                    toggle.classList.toggle('is-visible', shouldShow);
                    toggle.setAttribute(
                        'aria-label',
                        shouldShow
                            ? 'Sembunyikan kata sandi'
                            : 'Tampilkan kata sandi'
                    );
                    toggle.setAttribute(
                        'aria-pressed',
                        shouldShow ? 'true' : 'false'
                    );

                    input.focus();
                });
            };

            setupToggle('password', 'togglePassword');
            setupToggle('password_confirmation', 'togglePasswordConfirm');

            // =========================================================
            // Password strength indicator
            // =========================================================

            const passwordInput = document.getElementById('password');
            const strengthBars = document.querySelectorAll(
                '#passwordStrength [data-strength]'
            );
            const strengthText = document.getElementById('strengthText');

            if (passwordInput && strengthBars.length && strengthText) {
                passwordInput.addEventListener('input', function () {
                    const value = passwordInput.value;
                    const strength = calculateStrength(value);

                    strengthBars.forEach(function (bar, index) {
                        const level = parseInt(
                            bar.getAttribute('data-strength'),
                            10
                        );

                        bar.classList.remove(
                            'is-active',
                            'weak',
                            'medium',
                            'strong'
                        );

                        if (level <= strength.level) {
                            bar.classList.add(
                                'is-active',
                                strength.class
                            );
                        }
                    });

                    strengthText.textContent = strength.label;
                    strengthText.className =
                        'sv-auth-strength-text ' + strength.class;
                });
            }

            function calculateStrength (password) {
                if (! password) {
                    return {
                        level: 0,
                        class: '',
                        label: 'Minimal 8 karakter'
                    };
                }

                let score = 0;

                if (password.length >= 6) {
                    score += 1;
                }

                if (password.length >= 10) {
                    score += 1;
                }

                if (/[A-Z]/.test(password) && /[a-z]/.test(password)) {
                    score += 1;
                }

                if (/\d/.test(password)) {
                    score += 1;
                }

                if (/[^A-Za-z0-9]/.test(password)) {
                    score += 1;
                }

                if (score <= 2) {
                    return {
                        level: 1,
                        class: 'is-weak',
                        label: 'Lemah — tambahkan variasi karakter'
                    };
                }

                if (score <= 3) {
                    return {
                        level: 2,
                        class: 'is-medium',
                        label: 'Sedang — tambahkan kombinasi huruf besar, angka, atau simbol'
                    };
                }

                if (score <= 4) {
                    return {
                        level: 3,
                        class: 'is-strong',
                        label: 'Kuat — kata sandi yang baik'
                    };
                }

                return {
                    level: 4,
                    class: 'is-strong',
                    label: 'Sangat kuat'
                };
            }

            // =========================================================
            // Submit handling
            // =========================================================

            const form = document.getElementById('registerForm');
            const button = document.getElementById('registerButton');
            const buttonText = document.getElementById('registerButtonText');

            if (form && button && buttonText) {
                form.addEventListener('submit', function (event) {
                    if (! form.checkValidity()) {
                        event.preventDefault();
                        form.reportValidity();
                        return;
                    }

                    button.disabled = true;
                    button.classList.add('is-loading');
                    buttonText.textContent = 'Mendaftarkan akun...';
                    button.setAttribute('aria-busy', 'true');
                });
            }
        });
    </script>
</body>
</html>
```

## `resources/views/auth/reset_password.blade.php`

```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#173B66">

    <title>Atur Ulang Kata Sandi | SmartVolt</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/smartvolt-auth.css') }}?v=20260730-v5">
</head>

<body class="sv-auth-page">
    <main class="sv-auth-shell">
        <section
            class="sv-auth-showcase"
            aria-label="Informasi SmartVolt"
        >
            <svg
                class="sv-auth-network"
                viewBox="0 0 900 900"
                aria-hidden="true"
                focusable="false"
            >
                <g fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M70 130H280L360 210H590L690 110H840"/>
                    <path d="M40 390H190L290 290H470L560 380H820"/>
                    <path d="M110 680H300L400 580H620L720 680H860"/>
                    <path d="M240 80V260L160 340V520"/>
                    <path d="M650 80V250L740 340V520L650 610V820"/>
                    <path d="M470 210V470L390 550V790"/>
                </g>

                <g fill="currentColor">
                    <circle cx="70" cy="130" r="5"/>
                    <circle cx="360" cy="210" r="5"/>
                    <circle cx="690" cy="110" r="5"/>
                    <circle cx="190" cy="390" r="5"/>
                    <circle cx="470" cy="290" r="5"/>
                    <circle cx="560" cy="380" r="5"/>
                    <circle cx="300" cy="680" r="5"/>
                    <circle cx="620" cy="580" r="5"/>
                    <circle cx="740" cy="340" r="5"/>
                    <circle cx="390" cy="550" r="5"/>
                </g>
            </svg>

            <div class="sv-auth-showcase-content">
                <a
                    href="{{ url('/') }}"
                    class="sv-auth-brand"
                    aria-label="SmartVolt"
                >
                    <span class="sv-auth-brand-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path
                                d="M13.2 2.5 5.8 13h5.1l-.7 8.5L18.4 10h-5.1l-.1-7.5Z"
                                fill="currentColor"
                            />
                        </svg>
                    </span>

                    <span>
                        <strong>SmartVolt</strong>
                        <small>Calm Energy Control</small>
                    </span>
                </a>

                <div class="sv-auth-copy">
                    <p class="sv-auth-eyebrow">
                        Monitoring dan kontrol energi berbasis IoT
                    </p>

                    <h1>
                        Kendalikan energi rumah dengan lebih tenang.
                    </h1>

                    <p class="sv-auth-description">
                        Pantau daya, energi, estimasi biaya, dan perangkat
                        listrik melalui satu sistem yang jelas dan mudah
                        dipahami.
                    </p>
                </div>

                <div class="sv-auth-benefits">
                    <article class="sv-auth-benefit">
                        <span class="sv-auth-benefit-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M4 19V9m5 10V5m5 14v-7m5 7V3"/>
                            </svg>
                        </span>

                        <div>
                            <h2>Data listrik yang mudah dibaca</h2>
                            <p>
                                Lihat tegangan, arus, daya, energi, dan
                                perubahan penggunaan secara berkala.
                            </p>
                        </div>
                    </article>

                    <article class="sv-auth-benefit">
                        <span class="sv-auth-benefit-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <rect x="5" y="3" width="14" height="18" rx="3"/>
                                <path d="M9 8h6M9 12h6M9 16h3"/>
                            </svg>
                        </span>

                        <div>
                            <h2>Estimasi biaya yang transparan</h2>
                            <p>
                                Pemakaian kWh dihitung berdasarkan data meter
                                dan dikalikan dengan tarif listrik.
                            </p>
                        </div>
                    </article>

                    <article class="sv-auth-benefit">
                        <span class="sv-auth-benefit-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M8 12h8"/>
                                <rect x="3" y="7" width="6" height="10" rx="2"/>
                                <rect x="15" y="7" width="6" height="10" rx="2"/>
                            </svg>
                        </span>

                        <div>
                            <h2>Kontrol perangkat per ruangan</h2>
                            <p>
                                Nyalakan atau matikan perangkat melalui relay
                                ketika ESP32 dan MQTT terhubung.
                            </p>
                        </div>
                    </article>
                </div>

                <div class="sv-auth-flow" aria-label="Alur data SmartVolt">
                    <div class="sv-auth-flow-label">Alur sistem</div>

                    <div class="sv-auth-flow-items">
                        <span>
                            <strong>PZEM</strong>
                            <small>Membaca listrik</small>
                        </span>

                        <i aria-hidden="true">→</i>

                        <span>
                            <strong>ESP32</strong>
                            <small>Mengirim data</small>
                        </span>

                        <i aria-hidden="true">→</i>

                        <span>
                            <strong>SmartVolt</strong>
                            <small>Menampilkan hasil</small>
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <section class="sv-auth-panel">
            <div class="sv-auth-panel-inner">
                <div class="sv-auth-mobile-brand">
                    <span class="sv-auth-brand-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path
                                d="M13.2 2.5 5.8 13h5.1l-.7 8.5L18.4 10h-5.1l-.1-7.5Z"
                                fill="currentColor"
                            />
                        </svg>
                    </span>

                    <span>
                        <strong>SmartVolt</strong>
                        <small>Monitoring energi rumah</small>
                    </span>
                </div>

                <div class="sv-auth-heading">
                    <span class="sv-auth-status-chip">
                        <span aria-hidden="true"></span>
                        Akses aman SmartVolt
                    </span>

                    <h2>Buat kata sandi baru</h2>

                    <p>
                        Masukkan kata sandi baru untuk akun SmartVolt Anda.
                    </p>
                </div>

                @if (session('status'))
                    <div
                        class="sv-auth-alert sv-auth-alert-success"
                        role="status"
                        aria-live="polite"
                    >
                        <span aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="m5 12 4 4L19 6"/>
                            </svg>
                        </span>

                        <div>{{ session('status') }}</div>
                    </div>
                @endif

                @if ($errors->any())
                    <div
                        class="sv-auth-alert sv-auth-alert-error"
                        role="alert"
                        aria-live="assertive"
                    >
                        <span aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M12 7v6m0 4h.01"/>
                            </svg>
                        </span>

                        <div>
                            <strong>Periksa kembali data yang dimasukkan.</strong>
                            <p>{{ $errors->first() }}</p>
                        </div>
                    </div>
                @endif

                <form
                    id="resetForm"
                    class="sv-auth-form"
                    action="{{ route('password.update') }}"
                    method="POST"
                    novalidate
                >
                    @csrf

                    <input
                        type="hidden"
                        name="token"
                        value="{{ $token }}"
                    >

                    <div class="sv-auth-field">
                        <label for="email">Alamat email</label>

                        <div
                            class="sv-auth-input-group
                                @error('email') is-invalid @enderror"
                        >
                            <span class="sv-auth-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <rect x="3" y="5" width="18" height="14" rx="3"/>
                                    <path d="m5 8 7 5 7-5"/>
                                </svg>
                            </span>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email', $email ?? '') }}"
                                placeholder="nama@email.com"
                                autocomplete="email"
                                inputmode="email"
                                aria-describedby="emailHelp emailError"
                                @error('email') aria-invalid="true" @enderror
                                required
                                readonly
                            >
                        </div>

                        @error('email')
                            <p
                                id="emailError"
                                class="sv-auth-field-error"
                            >
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="sv-auth-field">
                        <label for="password">Kata sandi baru</label>

                        <div
                            class="sv-auth-input-group
                                @error('password') is-invalid @enderror"
                        >
                            <span class="sv-auth-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <rect x="5" y="10" width="14" height="10" rx="3"/>
                                    <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                                </svg>
                            </span>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Minimal 8 karakter"
                                autocomplete="new-password"
                                aria-describedby="passwordHelp passwordError"
                                @error('password') aria-invalid="true" @enderror
                                required
                                minlength="8"
                            >

                            <button
                                type="button"
                                id="togglePassword"
                                class="sv-auth-password-toggle"
                                aria-label="Tampilkan kata sandi"
                                aria-pressed="false"
                            >
                                <svg
                                    class="sv-auth-eye-show"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6S2.5 12 2.5 12Z"/>
                                    <circle cx="12" cy="12" r="2.5"/>
                                </svg>

                                <svg
                                    class="sv-auth-eye-hide"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path d="m4 4 16 16"/>
                                    <path d="M10.6 6.2A10 10 0 0 1 12 6c6 0 9.5 6 9.5 6a16 16 0 0 1-2.4 3.1M6.2 7.3A15.7 15.7 0 0 0 2.5 12S6 18 12 18a9.8 9.8 0 0 0 2.1-.2"/>
                                </svg>
                            </button>
                        </div>

                        <div class="sv-auth-password-strength" id="passwordStrength">
                            <div class="sv-auth-strength-bar">
                                <span data-strength="1"></span>
                                <span data-strength="2"></span>
                                <span data-strength="3"></span>
                                <span data-strength="4"></span>
                            </div>
                            <span class="sv-auth-strength-text" id="strengthText">
                                Minimal 8 karakter
                            </span>
                        </div>

                        @error('password')
                            <p
                                id="passwordError"
                                class="sv-auth-field-error"
                            >
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="sv-auth-field">
                        <label for="password_confirmation">Konfirmasi kata sandi</label>

                        <div
                            class="sv-auth-input-group"
                        >
                            <span class="sv-auth-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <rect x="5" y="10" width="14" height="10" rx="3"/>
                                    <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                                </svg>
                            </span>

                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                placeholder="Ulangi kata sandi baru"
                                autocomplete="new-password"
                                aria-describedby="passwordConfirmationHelp"
                                required
                                minlength="8"
                            >

                            <button
                                type="button"
                                id="togglePasswordConfirm"
                                class="sv-auth-password-toggle"
                                aria-label="Tampilkan konfirmasi kata sandi"
                                aria-pressed="false"
                            >
                                <svg
                                    class="sv-auth-eye-show"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6S2.5 12 2.5 12Z"/>
                                    <circle cx="12" cy="12" r="2.5"/>
                                </svg>

                                <svg
                                    class="sv-auth-eye-hide"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path d="m4 4 16 16"/>
                                    <path d="M10.6 6.2A10 10 0 0 1 12 6c6 0 9.5 6 9.5 6a16 16 0 0 1-2.4 3.1M6.2 7.3A15.7 15.7 0 0 0 2.5 12S6 18 12 18a9.8 9.8 0 0 0 2.1-.2"/>
                                </svg>
                            </button>
                        </div>

                        <p
                            id="passwordConfirmationHelp"
                            class="sv-auth-field-help"
                        >
                            Ketik ulang kata sandi baru yang sama.
                        </p>
                    </div>

                    <button
                        type="submit"
                        id="resetButton"
                        class="sv-auth-submit"
                    >
                        <span
                            class="sv-auth-button-spinner"
                            aria-hidden="true"
                        ></span>

                        <span id="resetButtonText">Simpan Kata Sandi Baru</span>
                    </button>
                </form>

                <p class="sv-auth-footer">
                    <a
                        href="{{ route('login') }}"
                        class="sv-auth-link sv-auth-link-back"
                    >
                        <span aria-hidden="true">←</span>
                        Kembali ke halaman masuk
                    </a>
                </p>

                <div class="sv-auth-security-note">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 3 5 6v5c0 4.6 2.7 8 7 10 4.3-2 7-5.4 7-10V6l-7-3Z"/>
                        <path d="m9 12 2 2 4-5"/>
                    </svg>

                    <p>
                        Akses akun dilindungi oleh sesi Laravel dan token
                        keamanan formulir.
                    </p>
                </div>
            </div>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // =========================================================
            // Toggle password visibility
            // =========================================================

            const setupToggle = function (inputId, toggleId) {
                const input = document.getElementById(inputId);
                const toggle = document.getElementById(toggleId);

                if (! input || ! toggle) {
                    return;
                }

                toggle.addEventListener('click', function () {
                    const shouldShow = input.type === 'password';

                    input.type = shouldShow ? 'text' : 'password';
                    toggle.classList.toggle('is-visible', shouldShow);
                    toggle.setAttribute(
                        'aria-label',
                        shouldShow
                            ? 'Sembunyikan kata sandi'
                            : 'Tampilkan kata sandi'
                    );
                    toggle.setAttribute(
                        'aria-pressed',
                        shouldShow ? 'true' : 'false'
                    );

                    input.focus();
                });
            };

            setupToggle('password', 'togglePassword');
            setupToggle('password_confirmation', 'togglePasswordConfirm');

            // =========================================================
            // Password strength indicator
            // =========================================================

            const passwordInput = document.getElementById('password');
            const strengthBars = document.querySelectorAll(
                '#passwordStrength [data-strength]'
            );
            const strengthText = document.getElementById('strengthText');

            if (passwordInput && strengthBars.length && strengthText) {
                passwordInput.addEventListener('input', function () {
                    const value = passwordInput.value;
                    const strength = calculateStrength(value);

                    strengthBars.forEach(function (bar, index) {
                        const level = parseInt(
                            bar.getAttribute('data-strength'),
                            10
                        );

                        bar.classList.remove(
                            'is-active',
                            'weak',
                            'medium',
                            'strong'
                        );

                        if (level <= strength.level) {
                            bar.classList.add(
                                'is-active',
                                strength.class
                            );
                        }
                    });

                    strengthText.textContent = strength.label;
                    strengthText.className =
                        'sv-auth-strength-text ' + strength.class;
                });
            }

            function calculateStrength (password) {
                if (! password) {
                    return {
                        level: 0,
                        class: '',
                        label: 'Minimal 8 karakter'
                    };
                }

                let score = 0;

                if (password.length >= 6) {
                    score += 1;
                }

                if (password.length >= 10) {
                    score += 1;
                }

                if (/[A-Z]/.test(password) && /[a-z]/.test(password)) {
                    score += 1;
                }

                if (/\d/.test(password)) {
                    score += 1;
                }

                if (/[^A-Za-z0-9]/.test(password)) {
                    score += 1;
                }

                if (score <= 2) {
                    return {
                        level: 1,
                        class: 'is-weak',
                        label: 'Lemah — tambahkan variasi karakter'
                    };
                }

                if (score <= 3) {
                    return {
                        level: 2,
                        class: 'is-medium',
                        label: 'Sedang — tambahkan kombinasi huruf besar, angka, atau simbol'
                    };
                }

                if (score <= 4) {
                    return {
                        level: 3,
                        class: 'is-strong',
                        label: 'Kuat — kata sandi yang baik'
                    };
                }

                return {
                    level: 4,
                    class: 'is-strong',
                    label: 'Sangat kuat'
                };
            }

            // =========================================================
            // Submit handling
            // =========================================================

            const form = document.getElementById('resetForm');
            const button = document.getElementById('resetButton');
            const buttonText = document.getElementById('resetButtonText');

            if (form && button && buttonText) {
                form.addEventListener('submit', function (event) {
                    if (! form.checkValidity()) {
                        event.preventDefault();
                        form.reportValidity();
                        return;
                    }

                    button.disabled = true;
                    button.classList.add('is-loading');
                    buttonText.textContent = 'Menyimpan kata sandi baru...';
                    button.setAttribute('aria-busy', 'true');
                });
            }
        });
    </script>
</body>
</html>
```

## `resources/views/components/feature-icon.blade.php`

```blade
@props([
    'name',
    'tone' => 'blue',
    'size' => 20,
    'variant' => 'soft',
    'label' => null,
])

<span
    {{ $attributes->class([
        'sv-feature-icon',
        'sv-feature-icon--' . $tone,
        'sv-feature-icon--' . $variant,
    ]) }}
    @if($label) role="img" aria-label="{{ $label }}" @else aria-hidden="true" @endif
>
    <x-icon :name="$name" :size="$size" />
</span>
```

## `resources/views/components/icon.blade.php`

```blade
@props([
    'name',
    'size' => 20,
])

<svg
    {{ $attributes->merge(['class' => 'sv-icon']) }}
    width="{{ $size }}"
    height="{{ $size }}"
    viewBox="0 0 24 24"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
    aria-hidden="true"
    focusable="false"
>
    @switch($name)
        @case('bolt')
            <path d="M13.2 2.5 5.8 13h5.1l-.7 8.5L18.4 10h-5.1l-.1-7.5Z" fill="currentColor"/>
            @break
        @case('home')
            <path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1V10Z"/>
            @break
        @case('chart')
            <path d="M4 20V10m5 10V4m5 16v-7m5 7V7"/>
            @break
        @case('rooms')
            <rect x="3" y="3" width="8" height="8" rx="2"/>
            <rect x="13" y="3" width="8" height="8" rx="2"/>
            <rect x="3" y="13" width="8" height="8" rx="2"/>
            <rect x="13" y="13" width="8" height="8" rx="2"/>
            @break
        @case('settings')
            <circle cx="12" cy="12" r="3"/>
            <path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1-2.8 2.8-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.6v.2h-4V21a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1L4.2 17l.1-.1A1.7 1.7 0 0 0 4.6 15 1.7 1.7 0 0 0 3 14H2.8v-4H3a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9L4.2 7 7 4.2l.1.1A1.7 1.7 0 0 0 9 4.6 1.7 1.7 0 0 0 10 3V2.8h4V3a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l.1-.1L19.8 7l-.1.1a1.7 1.7 0 0 0-.3 1.9 1.7 1.7 0 0 0 1.6 1h.2v4H21a1.7 1.7 0 0 0-1.6 1Z"/>
            @break
        @case('tools')
            <path d="M14.7 6.3a4 4 0 0 0-5-5l2.2 2.2-2.4 2.4-2.2-2.2a4 4 0 0 0 5 5L20 16.4a2.5 2.5 0 1 1-3.6 3.6l-7.7-7.7"/>
            <path d="m5 14-3 3 5 5 3-3"/>
            @break
        @case('bell')
            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
            <path d="M10 21h4"/>
            @break
        @case('user')
            <circle cx="12" cy="8" r="4"/>
            <path d="M4 21a8 8 0 0 1 16 0"/>
            @break
        @case('logout')
            <path d="M10 17l5-5-5-5"/>
            <path d="M15 12H3"/>
            <path d="M14 3h5a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-5"/>
            @break
        @case('menu')
            <path d="M4 7h16M4 12h16M4 17h16"/>
            @break
        @case('close')
            <path d="m6 6 12 12M18 6 6 18"/>
            @break
        @case('wifi')
            <path d="M5 12.5a10 10 0 0 1 14 0"/>
            <path d="M8.5 16a5 5 0 0 1 7 0"/>
            <circle cx="12" cy="20" r="1"/>
            @break
        @case('wifi-off')
            <path d="m3 3 18 18"/>
            <path d="M8.5 16a5 5 0 0 1 4.5-1.4M5 12.5a10 10 0 0 1 4.4-2.4M14.8 10.4A10 10 0 0 1 19 12.5"/>
            @break
        @case('sensor')
            <rect x="4" y="4" width="16" height="16" rx="4"/>
            <path d="M8 8h8v8H8zM12 1v3M12 20v3M1 12h3M20 12h3"/>
            @break
        @case('mqtt')
            <path d="M4 19a15 15 0 0 1 15-15"/>
            <path d="M4 13a9 9 0 0 1 9-9"/>
            <path d="M4 7a3 3 0 0 1 3-3"/>
            <circle cx="5" cy="19" r="1.5" fill="currentColor" stroke="none"/>
            @break
        @case('clock')
            <circle cx="12" cy="12" r="9"/>
            <path d="M12 7v5l3 2"/>
            @break
        @case('plug')
            <path d="M8 2v6M16 2v6M6 8h12v2a6 6 0 0 1-12 0V8ZM12 16v6"/>
            @break
        @case('energy')
            <path d="M3 12h4l2-6 4 12 2-6h6"/>
            @break
        @case('money')
            <rect x="3" y="5" width="18" height="14" rx="3"/>
            <path d="M7 9h.01M17 15h.01M12 9v6M10 11h3a1 1 0 0 1 0 2h-3"/>
            @break
        @case('devices')
            <rect x="3" y="4" width="8" height="16" rx="2"/>
            <rect x="13" y="4" width="8" height="16" rx="2"/>
            <path d="M7 8h.01M17 8h.01M7 16h.01M17 16h.01"/>
            @break
        @case('chevron-down')
            <path d="m6 9 6 6 6-6"/>
            @break
        @case('chevron-right')
            <path d="m9 6 6 6-6 6"/>
            @break
        @case('plus')
            <path d="M12 5v14M5 12h14"/>
            @break
        @case('edit')
            <path d="M12 20h9"/>
            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4 11.5-11.5Z"/>
            @break
        @case('trash')
            <path d="M4 7h16M9 7V4h6v3M6 7l1 14h10l1-14M10 11v6M14 11v6"/>
            @break
        @case('check')
            <path d="m5 12 4 4L19 6"/>
            @break
        @case('warning')
            <path d="M12 3 2.5 20h19L12 3Z"/>
            <path d="M12 9v5M12 17h.01"/>
            @break
        @case('info')
            <circle cx="12" cy="12" r="9"/>
            <path d="M12 11v5M12 8h.01"/>
            @break
        @case('eye')
            <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6S2.5 12 2.5 12Z"/>
            <circle cx="12" cy="12" r="2.5"/>
            @break
        @case('eye-off')
            <path d="m4 4 16 16"/>
            <path d="M10.6 6.2A10 10 0 0 1 12 6c6 0 9.5 6 9.5 6a16 16 0 0 1-2.4 3.1M6.2 7.3A15.7 15.7 0 0 0 2.5 12S6 18 12 18a9.8 9.8 0 0 0 2.1-.2"/>
            @break
        @case('lock')
            <rect x="5" y="10" width="14" height="10" rx="3"/>
            <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
            @break
        @case('mail')
            <rect x="3" y="5" width="18" height="14" rx="3"/>
            <path d="m5 8 7 5 7-5"/>
            @break
        @case('calendar')
            <rect x="3" y="5" width="18" height="16" rx="3"/>
            <path d="M8 3v4M16 3v4M3 10h18"/>
            @break
        @case('filter')
            <path d="M4 5h16M7 12h10M10 19h4"/>
            @break
        @case('download')
            <path d="M12 3v12m0 0 5-5m-5 5-5-5"/>
            <path d="M5 21h14"/>
            @break
        @case('search')
            <circle cx="11" cy="11" r="7"/>
            <path d="m20 20-4-4"/>
            @break
        @case('shield')
            <path d="M12 3 5 6v5c0 4.6 2.7 8 7 10 4.3-2 7-5.4 7-10V6l-7-3Z"/>
            <path d="m9 12 2 2 4-5"/>
            @break
        @case('database')
            <ellipse cx="12" cy="5" rx="8" ry="3"/>
            <path d="M4 5v6c0 1.7 3.6 3 8 3s8-1.3 8-3V5M4 11v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"/>
            @break
        @case('server')
            <rect x="3" y="4" width="18" height="6" rx="2"/>
            <rect x="3" y="14" width="18" height="6" rx="2"/>
            <path d="M7 7h.01M7 17h.01M11 7h6M11 17h6"/>
            @break
        @case('power')
            <path d="M12 2v10"/>
            <path d="M6.3 5.7a8 8 0 1 0 11.4 0"/>
            @break
        @case('lightbulb')
            <path d="M9 18h6M10 22h4"/>
            <path d="M8.5 15.5A7 7 0 1 1 15.5 15.5c-.9.7-1.5 1.5-1.5 2.5h-4c0-1-.6-1.8-1.5-2.5Z"/>
            @break
        @case('fan')
            <circle cx="12" cy="12" r="2"/>
            <path d="M12 10c-1-6 3-8 5-5 2 3-1 6-5 7M14 12c6-1 8 3 5 5-3 2-6-1-7-5M12 14c1 6-3 8-5 5-2-3 1-6 5-7M10 12c-6 1-8-3-5-5 3-2 6 1 7 5"/>
            @break
        @case('refresh')
            <path d="M20 6v5h-5M4 18v-5h5"/>
            <path d="M18.5 9A7 7 0 0 0 6 6.5L4 9M5.5 15A7 7 0 0 0 18 17.5l2-2.5"/>
            @break
        @case('document')
            <path d="M6 3h8l4 4v14H6V3Z"/>
            <path d="M14 3v5h5M9 13h6M9 17h6"/>
            @break
        @case('activity')
            <path d="M3 12h4l2-6 4 12 2-6h6"/>
            @break
        @case('gauge')
            <path d="M4 18a8 8 0 1 1 16 0"/>
            <path d="m12 14 4-4"/>
            <path d="M7 18h10"/>
            @break
        @case('kitchen')
            <path d="M5 3v18M19 3v18"/>
            <path d="M8 3v7a4 4 0 0 0 8 0V3"/>
            <path d="M9 14h6M8 21h8"/>
            @break
        @case('bed')
            <path d="M3 18V8M21 18v-6a3 3 0 0 0-3-3H8a5 5 0 0 0-5 5v4"/>
            <path d="M3 15h18M7 9V6h5a3 3 0 0 1 3 3"/>
            <path d="M5 18v3M19 18v3"/>
            @break
        @case('sofa')
            <path d="M5 12V8a3 3 0 0 1 3-3h8a3 3 0 0 1 3 3v4"/>
            <path d="M5 10a3 3 0 0 0-3 3v5h20v-5a3 3 0 0 0-3-3"/>
            <path d="M6 18v3M18 18v3"/>
            @break
        @case('garage')
            <path d="M3 10 12 3l9 7v11H3V10Z"/>
            <path d="M6 13h12v8H6v-8ZM8 16h8M8 19h8"/>
            @break
        @case('key')
            <circle cx="8" cy="15" r="4"/>
            <path d="m11 12 9-9M15 8l2 2M17 6l2 2"/>
            @break
        @default
            <circle cx="12" cy="12" r="9"/>
    @endswitch
</svg>
```

## `resources/views/components/notification-bell.blade.php`

```blade
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
        <x-feature-icon name="bell" tone="blue" :size="18" variant="flat" />

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
                        <x-feature-icon
                            :name="$notification->severity === 'danger' ? 'warning' : 'info'"
                            :tone="$notification->severity === 'danger' ? 'rose' : ($notification->severity === 'warning' ? 'amber' : 'blue')"
                            :size="16"
                            variant="soft"
                            class="sv-notification-icon"
                        />

                        <span class="sv-notification-copy">
                            <strong>{{ $notification->title }}</strong>
                            <span>{{ $notification->message }}</span>
                            <small>{{ optional($notification->created_at)->diffForHumans() }}</small>
                        </span>
                    </button>
                </form>
            @empty
                <div class="sv-notification-empty">
                    <x-feature-icon name="bell" tone="blue" :size="21" variant="soft" />
                    <strong>Belum ada notifikasi</strong>
                    <span>Pemberitahuan sistem akan tampil di sini.</span>
                </div>
            @endforelse
        </div>
    </div>
</div>
```

## `resources/views/dashboard.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Beranda')
@section('page-title', 'Beranda')
@section('page-subtitle', 'Pantau dan kontrol penggunaan listrik rumah secara real-time.')
@section('body-class', 'sv-dashboard-page')

@php
    $stats = $dashboardData['stats'] ?? [];
    $system = $dashboardData['system'] ?? [];
    $chart = $dashboardData['chart'] ?? ['labels' => [], 'power' => [], 'energy' => []];
    $recentReadings = collect($dashboardData['recent_readings'] ?? []);
    $dashboardRooms = collect($dashboardData['rooms'] ?? []);
    $monthly = $dashboardData['monthly_estimation'] ?? [];

    $hour = now()->hour;
    $greeting = match (true) {
        $hour < 11 => 'Selamat pagi',
        $hour < 15 => 'Selamat siang',
        $hour < 18 => 'Selamat sore',
        default => 'Selamat malam',
    };

    $loadStatus = $stats['load_status'] ?? 'normal';
    $loadPercentage = min(100, max(0, (float) ($stats['load_percentage'] ?? 0)));
    $energyComparison = $stats['energy_comparison_percent'] ?? null;

    $statusBadgeClass = ($system['has_fresh_data'] ?? false)
        ? 'sv-badge-success'
        : (($system['has_data'] ?? false) ? 'sv-badge-warning' : 'sv-badge-neutral');

    $systemStatusLabel = ($system['has_fresh_data'] ?? false)
        ? 'Sistem Terhubung'
        : (($system['has_data'] ?? false) ? 'Data Terlambat' : 'Belum Terhubung');

    $introMessage = match ($loadStatus) {
        'danger' => 'Beban listrik tinggi. Matikan perangkat yang tidak diperlukan.',
        'warning' => 'Beban listrik mulai mendekati batas rumah.',
        default => (($system['has_fresh_data'] ?? false)
            ? 'Sistem kelistrikan rumah Anda dalam kondisi normal.'
            : 'Menunggu data terbaru dari perangkat SmartVolt.'),
    };
@endphp

@section('system-status')
    <span class="sv-badge {{ $statusBadgeClass }}" data-dashboard-system-badge>
        <span class="sv-status-dot" aria-hidden="true"></span>
        <span data-dashboard-system-label>{{ $systemStatusLabel }}</span>
    </span>
@endsection

@section('content')
    <section class="sv-dashboard-intro" aria-labelledby="dashboard-greeting">
        <x-feature-icon name="home" tone="blue" :size="23" variant="solid" class="sv-dashboard-intro-icon" />
        <div class="sv-dashboard-intro-copy">
            <h2 id="dashboard-greeting">{{ $greeting }}, {{ auth()->user()->name ?? 'Pengguna' }}</h2>
            <p data-dashboard-intro-message>{{ $introMessage }}</p>
        </div>
        <x-feature-icon
            :name="($system['has_fresh_data'] ?? false) ? 'shield' : 'clock'"
            :tone="($system['has_fresh_data'] ?? false) ? 'green' : 'amber'"
            :size="20"
            variant="soft"
            class="sv-dashboard-intro-state {{ ($system['has_fresh_data'] ?? false) ? 'is-online' : 'is-waiting' }}"
        />
    </section>

    <section class="sv-dashboard-metrics" aria-label="Ringkasan penggunaan listrik">
        <article class="sv-metric-card sv-metric-card-blue">
            <div class="sv-metric-card-top">
                <x-feature-icon name="bolt" tone="blue" :size="22" variant="solid" class="sv-metric-card-icon" />
                <div>
                    <span class="sv-metric-label">Daya Saat Ini</span>
                    <div class="sv-metric-value">
                        <strong data-stat-current-power>{{ number_format((float) ($stats['current_power'] ?? 0), 1, ',', '.') }}</strong>
                        <small>W</small>
                    </div>
                </div>
            </div>
            <div class="sv-metric-foot">
                <span class="sv-metric-status {{ $loadStatus === 'danger' ? 'is-danger' : ($loadStatus === 'warning' ? 'is-warning' : 'is-success') }}" data-load-status-badge>
                    <span data-load-status-label>{{ $loadStatus === 'danger' ? 'Beban tinggi' : ($loadStatus === 'warning' ? 'Mendekati batas' : 'Penggunaan normal') }}</span>
                </span>
                <span><strong data-stat-load-percentage>{{ number_format($loadPercentage, 0, ',', '.') }}</strong>% batas daya</span>
            </div>
            <div class="sv-progress {{ $loadStatus === 'danger' ? 'is-danger' : ($loadStatus === 'warning' ? 'is-warning' : '') }}" data-load-progress style="--sv-progress: {{ $loadPercentage }}%"><span></span></div>
        </article>

        <article class="sv-metric-card sv-metric-card-green">
            <div class="sv-metric-card-top">
                <x-feature-icon name="energy" tone="green" :size="22" variant="solid" class="sv-metric-card-icon" />
                <div>
                    <span class="sv-metric-label">Energi Hari Ini</span>
                    <div class="sv-metric-value">
                        <strong data-stat-energy-today>{{ number_format((float) ($stats['total_energy_today'] ?? 0), 3, ',', '.') }}</strong>
                        <small>kWh</small>
                    </div>
                </div>
            </div>
            <p class="sv-metric-foot-text" data-energy-comparison>
                @if(is_numeric($energyComparison))
                    {{ $energyComparison > 0 ? 'Lebih tinggi' : ($energyComparison < 0 ? 'Lebih hemat' : 'Sama') }}
                    {{ number_format(abs((float) $energyComparison), 1, ',', '.') }}% dari kemarin
                @else
                    Perbandingan belum tersedia
                @endif
            </p>
        </article>

        <article class="sv-metric-card sv-metric-card-orange">
            <div class="sv-metric-card-top">
                <x-feature-icon name="money" tone="amber" :size="22" variant="solid" class="sv-metric-card-icon" />
                <div>
                    <span class="sv-metric-label">Estimasi Tagihan Bulan Ini</span>
                    <div class="sv-metric-value sv-metric-money">
                        <small>Rp</small>
                        <strong data-stat-monthly-cost>{{ number_format((float) ($stats['monthly_estimated_cost'] ?? 0), 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
            <p class="sv-metric-foot-text">
                <span data-stat-monthly-energy>{{ number_format((float) ($stats['monthly_energy_usage'] ?? 0), 3, ',', '.') }}</span> kWh · {{ (int) ($stats['active_meters'] ?? 0) }} meter aktif
            </p>
        </article>

        <article class="sv-metric-card sv-metric-card-cyan">
            <div class="sv-metric-card-top">
                <x-feature-icon name="plug" tone="cyan" :size="22" variant="solid" class="sv-metric-card-icon" />
                <div>
                    <span class="sv-metric-label">Perangkat Aktif</span>
                    <div class="sv-metric-value">
                        <strong data-stat-active-devices>{{ (int) ($stats['active_devices'] ?? 0) }}</strong>
                        <small>dari <span data-stat-total-devices>{{ (int) ($stats['total_devices'] ?? 0) }}</span></small>
                    </div>
                </div>
            </div>
            <p class="sv-metric-foot-text">
                <span data-stat-online-active-devices>{{ (int) ($stats['online_active_devices'] ?? 0) }}</span> perangkat aktif pada ESP32 online
            </p>
        </article>
    </section>

    <section class="sv-system-strip" aria-label="Status sistem SmartVolt">
        <article class="sv-system-item {{ ($system['esp_online'] ?? false) ? 'is-success' : 'is-warning' }}" data-system-esp>
            <x-feature-icon name="sensor" :tone="($system['esp_online'] ?? false) ? 'green' : 'amber'" :size="18" variant="soft" class="sv-system-icon" />
            <div class="sv-system-copy">
                <strong data-system-esp-label>{{ ($system['esp_online'] ?? false) ? (($system['online_esp_count'] ?? 0) . ' ESP32 Terhubung') : 'ESP32 Belum Terhubung' }}</strong>
                <span>Mikrokontroler</span>
            </div>
        </article>

        <article class="sv-system-item {{ ($system['has_fresh_data'] ?? false) ? 'is-success' : 'is-warning' }}" data-system-pzem>
            <x-feature-icon name="activity" :tone="($system['has_fresh_data'] ?? false) ? 'green' : 'amber'" :size="18" variant="soft" class="sv-system-icon" />
            <div class="sv-system-copy">
                <strong>Sensor PZEM <span data-system-pzem-label>{{ $system['pzem_status'] ?? 'Belum tersedia' }}</span></strong>
                <span>Pembacaan listrik</span>
            </div>
        </article>

        <article class="sv-system-item {{ ($system['esp_online'] ?? false) ? 'is-success' : 'is-warning' }}" data-system-control>
            <x-feature-icon name="mqtt" :tone="($system['esp_online'] ?? false) ? 'cyan' : 'amber'" :size="18" variant="soft" class="sv-system-icon" />
            <div class="sv-system-copy">
                <strong>MQTT <span data-system-control-label>{{ $system['control_channel_status'] ?? 'Menunggu perangkat' }}</span></strong>
                <span>Kontrol perangkat</span>
            </div>
        </article>

        <article class="sv-system-item {{ ($system['has_data'] ?? false) ? 'is-success' : 'is-warning' }}" data-system-latest>
            <x-feature-icon name="clock" :tone="($system['has_data'] ?? false) ? 'blue' : 'amber'" :size="18" variant="soft" class="sv-system-icon" />
            <div class="sv-system-copy">
                <strong data-system-latest-label>{{ $system['latest_received_human'] ?? 'Belum ada data' }}</strong>
                <span>Data terakhir</span>
            </div>
        </article>
    </section>

    <section class="sv-dashboard-main-grid">
        <article class="sv-card sv-chart-card">
            <header class="sv-card-header">
                <div>
                    <h2>Penggunaan Daya</h2>
                    <p>Grafik pembacaan listrik hari ini.</p>
                </div>
                <div class="sv-chart-tabs" role="tablist" aria-label="Jenis grafik">
                    <button type="button" class="sv-chart-tab is-active" data-dashboard-chart-mode="power" aria-selected="true">Daya</button>
                    <button type="button" class="sv-chart-tab" data-dashboard-chart-mode="energy" aria-selected="false">Energi</button>
                </div>
            </header>

            <div class="sv-card-body">
                <div class="sv-chart-summary">
                    <div><span>Saat ini</span><strong><span data-chart-current>{{ number_format((float) ($stats['current_power'] ?? 0), 1, ',', '.') }}</span> <small data-chart-unit>W</small></strong></div>
                    <div><span>Rata-rata</span><strong><span data-chart-average>0</span> <small data-chart-average-unit>W</small></strong></div>
                    <div><span>Tertinggi</span><strong><span data-chart-maximum>0</span> <small data-chart-maximum-unit>W</small></strong></div>
                </div>

                <div class="sv-empty-state {{ collect($chart['labels'] ?? [])->isNotEmpty() ? 'is-hidden' : '' }}" data-dashboard-chart-empty>
                    <x-feature-icon name="chart" tone="green" :size="24" variant="soft" />
                    <h3>Belum ada data grafik</h3>
                    <p>Grafik muncul setelah ESP32 mengirim data PZEM.</p>
                </div>

                <div class="sv-chart-container {{ collect($chart['labels'] ?? [])->isEmpty() ? 'is-hidden' : '' }}" data-dashboard-chart-container>
                    <canvas id="dashboardEnergyChart" aria-label="Grafik penggunaan listrik SmartVolt"></canvas>
                </div>
            </div>
        </article>

        <article class="sv-card sv-control-card">
            <header class="sv-card-header">
                <div>
                    <h2>Kontrol Ruangan</h2>
                    <p>Nyalakan atau matikan perangkat.</p>
                </div>
                <a href="{{ route('rooms') }}" class="sv-text-button">Lihat semua</a>
            </header>

            <div class="sv-card-body">
                @if($dashboardRooms->isEmpty())
                    <div class="sv-empty-state">
                        <x-feature-icon name="rooms" tone="violet" :size="24" variant="soft" />
                        <h3>Belum ada ruangan</h3>
                        <p>Tambahkan ruangan dari menu Pengaturan.</p>
                    </div>
                @else
                    <div class="sv-room-control-list">
                        @foreach($dashboardRooms->take(4) as $room)
                            <details class="sv-room-control" data-room-id="{{ $room['id'] }}" {{ $loop->first ? 'open' : '' }}>
                                <summary>
                                    <span class="sv-room-summary-main">
                                        <x-feature-icon name="rooms" tone="violet" :size="17" variant="soft" class="sv-room-icon" />
                                        <span class="sv-room-summary-copy">
                                            <strong>{{ $room['name'] }}</strong>
                                            <span><span data-room-active-count="{{ $room['id'] }}">{{ $room['active_devices'] }}</span> aktif dari {{ $room['total_devices'] }}</span>
                                        </span>
                                    </span>
                                    <span class="sv-room-power">{{ number_format((float) ($room['current_power'] ?? 0), 1, ',', '.') }} W</span>
                                    <span class="sv-room-chevron"><x-icon name="chevron-right" :size="16" /></span>
                                </summary>

                                <div class="sv-room-devices">
                                    @forelse(collect($room['devices'] ?? []) as $device)
                                        @php
                                            $deviceOn = (bool) ($device['is_on'] ?? false);
                                            $espOnline = (bool) ($device['esp_online'] ?? false);
                                        @endphp
                                        <div class="sv-device-row" data-device-row="{{ $device['id'] }}">
                                            <div class="sv-device-main">
                                                <x-feature-icon name="lightbulb" tone="amber" :size="16" variant="soft" class="sv-device-icon" />
                                                <div class="sv-device-copy">
                                                    <strong>{{ $device['name'] }}</strong>
                                                    <span>Relay {{ $device['relay_code'] ?: '-' }} · ESP {{ $device['esp_unit_id'] ?: '-' }}</span>
                                                </div>
                                            </div>

                                            <button
                                                type="button"
                                                class="sv-device-switch {{ $deviceOn ? 'is-on' : '' }} {{ ! $espOnline ? 'is-offline' : '' }}"
                                                data-device-toggle
                                                data-device-id="{{ $device['id'] }}"
                                                data-room-id="{{ $room['id'] }}"
                                                data-url="{{ route('devices.toggle', $device['id']) }}"
                                                data-current-state="{{ $deviceOn ? 'on' : 'off' }}"
                                                aria-pressed="{{ $deviceOn ? 'true' : 'false' }}"
                                                {{ ! $espOnline ? 'disabled' : '' }}
                                            >
                                                <span class="sv-switch-track"><span></span></span>
                                                <span data-switch-label>{{ $espOnline ? ($deviceOn ? 'Nyala' : 'Mati') : 'Offline' }}</span>
                                            </button>
                                        </div>
                                    @empty
                                        <div class="sv-empty-state sv-empty-state-compact"><p>Belum ada perangkat.</p></div>
                                    @endforelse
                                </div>
                            </details>
                        @endforeach
                    </div>
                @endif
            </div>
        </article>
    </section>

    <section class="sv-dashboard-bottom-grid">
        <article class="sv-card">
            <header class="sv-card-header">
                <div>
                    <h2>Riwayat Pemakaian Terakhir</h2>
                    <p>Pembacaan terakhir dari setiap meter.</p>
                </div>
                <a href="{{ route('energy.history') }}" class="sv-text-button">Lihat riwayat</a>
            </header>

            <div class="sv-card-body sv-card-body-table">
                @if($recentReadings->isEmpty())
                    <div class="sv-empty-state">
                        <x-feature-icon name="document" tone="blue" :size="24" variant="soft" />
                        <h3>Belum ada pembacaan</h3>
                        <p>Data akan muncul setelah telemetry diterima.</p>
                    </div>
                @else
                    <div class="sv-table-wrap">
                        <table class="sv-table">
                            <thead>
                                <tr>
                                    <th>Ruangan</th>
                                    <th>Meter</th>
                                    <th>Waktu</th>
                                    <th class="is-numeric">Tegangan</th>
                                    <th class="is-numeric">Arus</th>
                                    <th class="is-numeric">Daya</th>
                                    <th class="is-numeric">Energi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentReadings as $reading)
                                    <tr>
                                        <td><span class="sv-table-room"><x-feature-icon name="home" tone="violet" :size="14" variant="flat" />{{ $reading['room_name'] ?? '-' }}</span></td>
                                        <td>{{ $reading['meter_name'] ?? '-' }}</td>
                                        <td>{{ $reading['observed_at'] ?? '-' }}</td>
                                        <td class="is-numeric">{{ number_format((float) ($reading['voltage'] ?? 0), 1, ',', '.') }} V</td>
                                        <td class="is-numeric">{{ number_format((float) ($reading['current'] ?? 0), 3, ',', '.') }} A</td>
                                        <td class="is-numeric">{{ number_format((float) ($reading['power'] ?? 0), 1, ',', '.') }} W</td>
                                        <td class="is-numeric">{{ number_format((float) ($reading['energy'] ?? 0), 4, ',', '.') }} kWh</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </article>

        <article class="sv-card sv-billing-card">
            <header class="sv-card-header">
                <div>
                    <h2>Estimasi Pembayaran Listrik</h2>
                    <p>Berdasarkan tarif yang tersimpan.</p>
                </div>
            </header>

            <div class="sv-card-body">
                <div class="sv-billing-highlight">
                    <span>Bulan Ini</span>
                    <strong>Rp{{ number_format((float) ($monthly['estimated_cost'] ?? 0), 0, ',', '.') }}</strong>
                    <small>{{ number_format((float) ($monthly['usage_kwh'] ?? 0), 4, ',', '.') }} kWh</small>
                </div>

                <dl class="sv-billing-summary">
                    <div><dt>Periode</dt><dd>{{ $monthly['period'] ?? '-' }}</dd></div>
                    <div><dt>Tarif</dt><dd>Rp{{ number_format((float) ($monthly['tariff'] ?? 0), 0, ',', '.') }}/kWh</dd></div>
                    <div><dt>Meter aktif</dt><dd>{{ (int) ($monthly['meter_count'] ?? 0) }}</dd></div>
                </dl>

                <a href="{{ route('energy.history') }}" class="sv-button sv-button-secondary sv-button-full">
                    Lihat rincian pemakaian
                    <x-icon name="chevron-right" :size="17" />
                </a>
            </div>
        </article>
    </section>

    <script type="application/json" id="smartvolt-dashboard-data">{!! json_encode([
        'endpoint' => route('dashboard.data'),
        'refreshInterval' => (int) ($dashboardData['settings']['refresh_interval'] ?? 30),
        'chart' => $chart,
        'stats' => $stats,
        'system' => $system,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/chart.umd.min.js') }}?v=4.5.1" defer></script>
    <script src="{{ asset('assets/js/smartvolt-dashboard.js') }}?v=20260730-v5" defer></script>
@endpush
```

## `resources/views/devices.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Daftar Perangkat')
@section('page-title', 'Daftar Perangkat')
@section('page-subtitle', 'Informasi relay yang terdaftar pada seluruh ruangan.')
@section('body-class', 'sv-devices-page')

@php
    $deviceCollection = collect($devices ?? []);
@endphp

@section('content')
    <div class="sv-page-heading">
        <div class="sv-page-heading-copy">
            <h2>Perangkat SmartVolt</h2>
            <p>{{ $deviceCollection->count() }} perangkat terdaftar pada sistem.</p>
        </div>
        <div class="sv-page-heading-actions">
            <a href="{{ route('rooms') }}" class="sv-button sv-button-primary">
                <x-feature-icon name="rooms" tone="violet" :size="16" variant="flat" />
                Buka kontrol ruangan
            </a>
        </div>
    </div>

    <section class="sv-card">
        <header class="sv-card-header sv-card-header--with-icon">
            <x-feature-icon name="devices" tone="cyan" :size="19" variant="soft" />
            <div class="sv-card-header-copy">
                <h2>Daftar Relay</h2>
                <p>Informasi perangkat, ruangan, ESP32, dan relay.</p>
            </div>
            <a href="{{ route('settings', ['tab' => 'technician']) }}" class="sv-button sv-button-secondary sv-button-sm">
                <x-feature-icon name="tools" tone="amber" :size="14" variant="flat" />
                Konfigurasi
            </a>
        </header>

        <div class="sv-card-body">
            @if($deviceCollection->isEmpty())
                <div class="sv-empty-state">
                    <x-feature-icon name="devices" tone="cyan" :size="27" variant="soft" />
                    <h3>Belum ada perangkat</h3>
                    <p>Daftarkan relay melalui Mode Teknisi.</p>
                    <a href="{{ route('settings', ['tab' => 'technician']) }}" class="sv-button sv-button-primary">
                        <x-feature-icon name="tools" tone="amber" :size="15" variant="flat" />
                        Buka Mode Teknisi
                    </a>
                </div>
            @else
                <div class="sv-table-wrap sv-device-table-desktop">
                    <table class="sv-table">
                        <thead>
                            <tr>
                                <th>Perangkat</th>
                                <th>Ruangan</th>
                                <th>ESP Unit ID</th>
                                <th>Relay</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deviceCollection as $device)
                                @php
                                    $deviceLabel = strtolower(($device->type ?? '') . ' ' . ($device->name ?? ''));
                                    $deviceIcon = str_contains($deviceLabel, 'kipas') || str_contains($deviceLabel, 'fan') ? 'fan'
                                        : (str_contains($deviceLabel, 'lampu') || str_contains($deviceLabel, 'light') ? 'lightbulb' : 'plug');
                                    $deviceTone = $device->status ? 'green' : ($deviceIcon === 'fan' ? 'cyan' : ($deviceIcon === 'lightbulb' ? 'amber' : 'blue'));
                                @endphp
                                <tr>
                                    <td>
                                        <span class="sv-table-feature">
                                            <x-feature-icon :name="$deviceIcon" :tone="$deviceTone" :size="15" variant="soft" />
                                            <strong>{{ $device->name }}</strong>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="sv-table-feature">
                                            <x-feature-icon name="rooms" tone="violet" :size="14" variant="flat" />
                                            {{ $device->room?->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td>{{ $device->esp_unit_id ?: $device->esp32_device_id ?: '-' }}</td>
                                    <td>{{ $device->relay_code ?: '-' }}</td>
                                    <td>
                                        <span class="sv-badge {{ $device->status ? 'sv-badge-success' : 'sv-badge-neutral' }}">
                                            <span class="sv-status-dot" aria-hidden="true"></span>
                                            {{ $device->status ? 'Nyala' : 'Mati' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="sv-device-mobile-list">
                    @foreach($deviceCollection as $device)
                        @php
                            $deviceLabel = strtolower(($device->type ?? '') . ' ' . ($device->name ?? ''));
                            $deviceIcon = str_contains($deviceLabel, 'kipas') || str_contains($deviceLabel, 'fan') ? 'fan'
                                : (str_contains($deviceLabel, 'lampu') || str_contains($deviceLabel, 'light') ? 'lightbulb' : 'plug');
                            $deviceTone = $device->status ? 'green' : ($deviceIcon === 'fan' ? 'cyan' : ($deviceIcon === 'lightbulb' ? 'amber' : 'blue'));
                        @endphp
                        <article class="sv-device-mobile-card">
                            <header>
                                <x-feature-icon :name="$deviceIcon" :tone="$deviceTone" :size="18" variant="soft" />
                                <div><strong>{{ $device->name }}</strong><span>{{ $device->room?->name ?? 'Tanpa ruangan' }}</span></div>
                                <span class="sv-badge {{ $device->status ? 'sv-badge-success' : 'sv-badge-neutral' }}">{{ $device->status ? 'Nyala' : 'Mati' }}</span>
                            </header>
                            <dl>
                                <div><dt>ESP Unit ID</dt><dd>{{ $device->esp_unit_id ?: $device->esp32_device_id ?: '-' }}</dd></div>
                                <div><dt>Relay</dt><dd>{{ $device->relay_code ?: '-' }}</dd></div>
                            </dl>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
```

## `resources/views/layouts/app.blade.php`

```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#ffffff">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'SmartVolt') | SmartVolt</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/smartvolt-app.css') }}?v=20260730-v5">

    @stack('styles')
    @stack('head-scripts')
</head>
@php
    $routeName = request()->route()?->getName() ?? '';
    $isTechnicianPage = request()->routeIs('settings*') && request('tab') === 'technician';

    [$pageIcon, $pageIconTone] = match (true) {
        request()->routeIs('dashboard*') => ['home', 'blue'],
        request()->routeIs('energy.history*') => ['chart', 'green'],
        request()->routeIs('rooms*') => ['rooms', 'violet'],
        request()->routeIs('devices*') => ['devices', 'cyan'],
        $isTechnicianPage => ['tools', 'amber'],
        request()->routeIs('settings*') => ['settings', 'slate'],
        default => ['bolt', 'blue'],
    };
@endphp
<body class="sv-app-body @yield('body-class')">
    <div class="sv-app-layout">
        <button type="button" class="sv-sidebar-overlay" data-sidebar-close aria-label="Tutup menu"></button>

        <aside class="sv-app-sidebar" data-sidebar>
            <div class="sv-sidebar-header">
                <a href="{{ route('dashboard') }}" class="sv-brand" aria-label="Beranda SmartVolt">
                    <span class="sv-brand-mark" aria-hidden="true">
                        <x-icon name="bolt" :size="25" />
                    </span>
                    <span class="sv-brand-copy">
                        <strong>Smart<span>Volt</span></strong>
                        <small>Energi Cerdas</small>
                    </span>
                </a>

                <button type="button" class="sv-icon-button sv-sidebar-close" data-sidebar-close aria-label="Tutup menu">
                    <x-icon name="close" :size="20" />
                </button>
            </div>

            <nav class="sv-sidebar-nav" aria-label="Navigasi utama">
                <a href="{{ route('dashboard') }}" class="sv-nav-link sv-nav-link--blue {{ request()->routeIs('dashboard*') ? 'is-active' : '' }}">
                    <x-feature-icon name="home" tone="blue" :size="18" variant="flat" class="sv-nav-feature-icon" />
                    <span>Beranda</span>
                </a>

                <a href="{{ route('energy.history') }}" class="sv-nav-link sv-nav-link--green {{ request()->routeIs('energy.history*') ? 'is-active' : '' }}">
                    <x-feature-icon name="chart" tone="green" :size="18" variant="flat" class="sv-nav-feature-icon" />
                    <span>Pemakaian Listrik</span>
                </a>

                <a href="{{ route('rooms') }}" class="sv-nav-link sv-nav-link--violet {{ request()->routeIs('rooms*') || request()->routeIs('devices*') ? 'is-active' : '' }}">
                    <x-feature-icon name="rooms" tone="violet" :size="18" variant="flat" class="sv-nav-feature-icon" />
                    <span>Ruangan & Perangkat</span>
                </a>

                <a href="{{ route('settings') }}" class="sv-nav-link sv-nav-link--slate {{ request()->routeIs('settings*') && !$isTechnicianPage ? 'is-active' : '' }}">
                    <x-feature-icon name="settings" tone="slate" :size="18" variant="flat" class="sv-nav-feature-icon" />
                    <span>Pengaturan</span>
                </a>

                <div class="sv-sidebar-divider"></div>
                <p class="sv-nav-label">Akses teknis</p>

                <a href="{{ route('settings', ['tab' => 'technician']) }}#technician" class="sv-nav-link sv-nav-link-technical sv-nav-link--amber {{ $isTechnicianPage ? 'is-active' : '' }}">
                    <x-feature-icon name="tools" tone="amber" :size="18" variant="flat" class="sv-nav-feature-icon" />
                    <span>Mode Teknisi</span>
                    <x-icon name="chevron-right" :size="16" class="sv-nav-chevron" />
                </a>
            </nav>

            <div class="sv-sidebar-foot">
                <div class="sv-sidebar-energy-card">
                    <x-feature-icon name="energy" tone="cyan" :size="22" variant="solid" />
                    <div>
                        <strong>SmartVolt</strong>
                        <p>Energi cerdas, hidup nyaman.</p>
                    </div>
                </div>
            </div>
        </aside>

        <main class="sv-app-main">
            <header class="sv-app-topbar">
                <div class="sv-topbar-left">
                    <button type="button" class="sv-icon-button sv-mobile-menu" data-sidebar-open aria-label="Buka menu">
                        <x-icon name="menu" :size="22" />
                    </button>

                    <x-feature-icon :name="$pageIcon" :tone="$pageIconTone" :size="20" class="sv-page-heading-icon" />

                    <div class="sv-topbar-heading">
                        <h1 class="sv-page-title">@yield('page-title', 'SmartVolt')</h1>
                        <p class="sv-page-subtitle">@yield('page-subtitle')</p>
                    </div>
                </div>

                <div class="sv-topbar-actions">
                    @hasSection('system-status')
                        <div class="sv-topbar-status">
                            @yield('system-status')
                        </div>
                    @endif

                    @include('components.notification-bell')

                    <div class="sv-profile-menu" data-profile-menu>
                        <button type="button" class="sv-profile-trigger" data-profile-trigger aria-expanded="false">
                            <span class="sv-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
                            <span class="sv-profile-copy">
                                <strong>{{ auth()->user()->name ?? 'Pengguna' }}</strong>
                                <small>Pemilik Rumah</small>
                            </span>
                            <x-icon name="chevron-down" :size="16" />
                        </button>

                        <div class="sv-profile-dropdown" data-profile-dropdown>
                            <div class="sv-profile-dropdown-head">
                                <strong>{{ auth()->user()->name ?? 'Pengguna' }}</strong>
                                <span>{{ auth()->user()->email ?? '' }}</span>
                            </div>

                            <a href="{{ route('settings') }}">
                                <x-feature-icon name="settings" tone="slate" :size="16" variant="flat" />
                                <span>Pengaturan akun</span>
                            </a>

                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit">
                                    <x-feature-icon name="logout" tone="rose" :size="16" variant="flat" />
                                    <span>Keluar</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <div class="sv-page-content">
                @if(session('status') || session('success'))
                    <div class="sv-alert sv-alert-success" role="status">
                        <x-feature-icon name="check" tone="green" :size="18" variant="flat" />
                        <div>{{ session('status') ?? session('success') }}</div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="sv-alert sv-alert-danger" role="alert">
                        <x-feature-icon name="warning" tone="rose" :size="18" variant="flat" />
                        <div>
                            <strong>Periksa kembali data yang dimasukkan.</strong>
                            <p>{{ $errors->first() }}</p>
                        </div>
                    </div>
                @endif

                @yield('content')
            </div>

            <nav class="sv-bottom-navigation" aria-label="Navigasi mobile">
                <a href="{{ route('dashboard') }}" class="sv-bottom-link--blue {{ request()->routeIs('dashboard*') ? 'is-active' : '' }}">
                    <x-feature-icon name="home" tone="blue" :size="18" variant="flat" />
                    <span>Beranda</span>
                </a>
                <a href="{{ route('energy.history') }}" class="sv-bottom-link--green {{ request()->routeIs('energy.history*') ? 'is-active' : '' }}">
                    <x-feature-icon name="chart" tone="green" :size="18" variant="flat" />
                    <span>Pemakaian</span>
                </a>
                <a href="{{ route('rooms') }}" class="sv-bottom-link--violet {{ request()->routeIs('rooms*') || request()->routeIs('devices*') ? 'is-active' : '' }}">
                    <x-feature-icon name="rooms" tone="violet" :size="18" variant="flat" />
                    <span>Perangkat</span>
                </a>
                <a href="{{ route('settings') }}" class="sv-bottom-link--slate {{ request()->routeIs('settings*') ? 'is-active' : '' }}">
                    <x-feature-icon name="settings" tone="slate" :size="18" variant="flat" />
                    <span>Pengaturan</span>
                </a>
            </nav>
        </main>
    </div>

    <div class="sv-toast-region" data-toast-region aria-live="polite" aria-atomic="true"></div>

    <script src="{{ asset('assets/js/smartvolt-app.js') }}?v=20260730-v5" defer></script>
    @stack('scripts')
</body>
</html>
```

## `resources/views/rooms-show.blade.php`

```blade
@extends('layouts.app')

@section('title', $room->name ?? 'Detail Ruangan')
@section('page-title', $room->name ?? 'Detail Ruangan')
@section('page-subtitle', 'Kelola perangkat yang terhubung pada ruangan ini.')
@section('body-class', 'sv-room-detail-page')

@php
    $deviceCollection = collect($devices ?? $room->devices ?? []);
@endphp

@section('content')
    <div class="sv-page-heading">
        <div class="sv-page-heading-copy">
            <h2>Detail Ruangan</h2>
            <p>{{ $deviceCollection->count() }} perangkat terdaftar.</p>
        </div>
        <div class="sv-page-heading-actions">
            <a href="{{ route('rooms') }}" class="sv-button sv-button-secondary">
                <x-feature-icon name="chevron-right" tone="slate" :size="15" variant="flat" class="sv-icon-rotate-180" />
                Kembali
            </a>
        </div>
    </div>

    <section class="sv-section-grid sv-section-grid-two sv-room-detail-grid">
        <article class="sv-card">
            <header class="sv-card-header sv-card-header--with-icon">
                <x-feature-icon name="plus" tone="blue" :size="18" variant="soft" />
                <div><h2>Tambah Perangkat</h2><p>Daftarkan relay baru pada {{ $room->name }}.</p></div>
            </header>
            <div class="sv-card-body">
                <form action="{{ route('devices.store', $room) }}" method="POST" class="sv-stack" data-loading-form>
                    @csrf
                    <input type="hidden" name="room_id" value="{{ $room->id }}">
                    <div class="sv-form-grid">
                        <div class="sv-form-field">
                            <label class="sv-form-label" for="device_name">Nama perangkat</label>
                            <input id="device_name" type="text" name="name" class="sv-form-control" placeholder="Contoh: Lampu utama" required>
                        </div>
                        <div class="sv-form-field">
                            <label class="sv-form-label" for="device_type">Jenis perangkat</label>
                            <input id="device_type" type="text" name="type" class="sv-form-control" placeholder="lampu / fan / perangkat">
                        </div>
                        <div class="sv-form-field">
                            <label class="sv-form-label" for="esp_unit_id">ESP Unit ID</label>
                            <input id="esp_unit_id" type="text" name="esp_unit_id" class="sv-form-control" placeholder="Contoh: 2" required>
                        </div>
                        <div class="sv-form-field">
                            <label class="sv-form-label" for="relay_code">Relay channel</label>
                            <input id="relay_code" type="text" name="relay_code" class="sv-form-control" placeholder="Contoh: 1" required>
                        </div>
                    </div>
                    <div class="sv-form-actions">
                        <button type="submit" class="sv-button sv-button-primary" data-loading-text="Menyimpan...">
                            <x-feature-icon name="plus" tone="blue" :size="15" variant="flat" />
                            <span data-button-label>Tambah perangkat</span>
                        </button>
                    </div>
                </form>
            </div>
        </article>

        <article class="sv-card">
            <header class="sv-card-header sv-card-header--with-icon">
                <x-feature-icon name="devices" tone="cyan" :size="18" variant="soft" />
                <div><h2>Perangkat Ruangan</h2><p>Status tersimpan pada sistem.</p></div>
            </header>
            <div class="sv-card-body">
                @forelse($deviceCollection as $device)
                    @php
                        $label = strtolower(($device->type ?? '') . ' ' . ($device->name ?? ''));
                        $icon = str_contains($label, 'kipas') || str_contains($label, 'fan') ? 'fan' : (str_contains($label, 'lampu') ? 'lightbulb' : 'plug');
                    @endphp
                    <article class="sv-device-detail-item">
                        <div class="sv-device-main">
                            <x-feature-icon :name="$icon" :tone="$device->status ? 'green' : 'cyan'" :size="17" variant="soft" />
                            <div class="sv-device-copy">
                                <strong>{{ $device->name }}</strong>
                                <span>ESP {{ $device->esp_unit_id ?: $device->esp32_device_id ?: '-' }} · Relay {{ $device->relay_code ?: '-' }}</span>
                            </div>
                        </div>
                        <div class="sv-inline">
                            <form action="{{ route('devices.toggle', $device) }}" method="POST">
                                @csrf
                                <button type="submit" class="sv-button {{ $device->status ? 'sv-button-secondary' : 'sv-button-primary' }} sv-button-sm">
                                    <x-feature-icon name="power" :tone="$device->status ? 'rose' : 'green'" :size="14" variant="flat" />
                                    {{ $device->status ? 'Matikan' : 'Nyalakan' }}
                                </button>
                            </form>
                            <form action="{{ route('devices.destroy', $device) }}" method="POST" data-confirm="Hapus perangkat {{ $device->name }}?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="sv-button sv-button-danger sv-button-sm">
                                    <x-feature-icon name="trash" tone="rose" :size="14" variant="flat" />
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="sv-empty-state">
                        <x-feature-icon name="plug" tone="cyan" :size="25" variant="soft" />
                        <h3>Belum ada perangkat</h3>
                        <p>Tambahkan perangkat melalui formulir di samping.</p>
                    </div>
                @endforelse
            </div>
        </article>
    </section>
@endsection
```

## `resources/views/rooms.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Ruangan & Perangkat')
@section('page-title', 'Ruangan & Perangkat')
@section('page-subtitle', 'Pantau dan kontrol perangkat listrik berdasarkan ruangan.')
@section('body-class', 'sv-rooms-page')

@php
    $roomCollection = collect($rooms ?? []);
    $onlineIds = collect($onlineEspUnitIds ?? [])->map(fn ($value) => (string) $value);
    $allDevices = $roomCollection->pluck('devices')->flatten();
    $isOn = function ($status): bool {
        if (is_bool($status)) return $status;
        if (is_numeric($status)) return (int) $status === 1;
        return in_array(strtolower((string) $status), ['on', 'nyala', 'active', 'aktif', 'true', '1'], true);
    };
    $activeDevices = $allDevices->filter(fn ($device) => $isOn($device->status ?? null))->count();
    $onlineDevices = $allDevices->filter(function ($device) use ($onlineIds) {
        $espId = trim((string) ($device->esp_unit_id ?: $device->esp32_device_id));
        return $espId !== '' && $onlineIds->contains($espId);
    })->count();
    $roomTones = ['violet', 'blue', 'green', 'amber', 'cyan', 'rose'];
@endphp

@section('system-status')
    <span class="sv-badge {{ $onlineIds->isNotEmpty() ? 'sv-badge-success' : 'sv-badge-warning' }}">
        <span class="sv-status-dot" aria-hidden="true"></span>
        {{ $onlineIds->isNotEmpty() ? $onlineIds->count() . ' ESP32 online' : 'Perangkat offline' }}
    </span>
@endsection

@section('content')
    <div class="sv-page-heading">
        <div class="sv-page-heading-copy">
            <h2>Kontrol perangkat per ruangan</h2>
            <p>Switch aktif ketika ESP32 perangkat sedang mengirim data terbaru.</p>
        </div>
        <div class="sv-page-heading-actions">
            <a href="{{ route('settings', ['tab' => 'technician']) }}#technician" class="sv-button sv-button-primary">
                <x-feature-icon name="tools" tone="amber" :size="16" variant="flat" />
                Kelola konfigurasi
            </a>
        </div>
    </div>

    <section class="sv-room-summary-grid" aria-label="Ringkasan ruangan dan perangkat">
        <article class="sv-summary-card sv-summary-card--violet">
            <x-feature-icon name="rooms" tone="violet" :size="19" variant="soft" class="sv-summary-icon" />
            <div class="sv-summary-copy"><span>Total Ruangan</span><strong>{{ $roomCollection->count() }}</strong></div>
        </article>
        <article class="sv-summary-card sv-summary-card--cyan">
            <x-feature-icon name="devices" tone="cyan" :size="19" variant="soft" class="sv-summary-icon" />
            <div class="sv-summary-copy"><span>Total Perangkat</span><strong data-total-devices>{{ $allDevices->count() }}</strong></div>
        </article>
        <article class="sv-summary-card sv-summary-card--green">
            <x-feature-icon name="power" tone="green" :size="19" variant="soft" class="sv-summary-icon" />
            <div class="sv-summary-copy"><span>Perangkat Aktif</span><strong data-active-devices>{{ $activeDevices }}</strong></div>
        </article>
        <article class="sv-summary-card sv-summary-card--blue">
            <x-feature-icon name="wifi" tone="blue" :size="19" variant="soft" class="sv-summary-icon" />
            <div class="sv-summary-copy"><span>Perangkat Online</span><strong>{{ $onlineDevices }}</strong></div>
        </article>
    </section>

    @if($roomCollection->isEmpty())
        <section class="sv-card">
            <div class="sv-card-body">
                <div class="sv-empty-state">
                    <x-feature-icon name="rooms" tone="violet" :size="27" variant="soft" />
                    <h3>Belum ada ruangan</h3>
                    <p>Tambahkan ruangan melalui Mode Teknisi.</p>
                    <a href="{{ route('settings', ['tab' => 'technician']) }}#technician" class="sv-button sv-button-primary">
                        <x-feature-icon name="tools" tone="amber" :size="15" variant="flat" />
                        Buka Mode Teknisi
                    </a>
                </div>
            </div>
        </section>
    @else
        <section class="sv-room-grid" aria-label="Daftar ruangan">
            @foreach($roomCollection as $room)
                @php
                    $devices = collect($room->devices ?? []);
                    $roomActiveCount = $devices->filter(fn ($device) => $isOn($device->status ?? null))->count();
                    $roomOnlineCount = $devices->filter(function ($device) use ($onlineIds) {
                        $espId = trim((string) ($device->esp_unit_id ?: $device->esp32_device_id));
                        return $espId !== '' && $onlineIds->contains($espId);
                    })->count();
                    $power = (float) (($roomPower ?? collect())[$room->id] ?? 0);
                    $roomNameLower = strtolower((string) $room->name);
                    $roomIcon = str_contains($roomNameLower, 'dapur') ? 'kitchen'
                        : (str_contains($roomNameLower, 'kamar') ? 'bed'
                        : (str_contains($roomNameLower, 'tamu') ? 'sofa'
                        : (str_contains($roomNameLower, 'garasi') ? 'garage' : 'rooms')));
                    $roomTone = $roomTones[$loop->index % count($roomTones)];
                @endphp

                <article class="sv-room-card sv-room-card--{{ $roomTone }}" data-room-card="{{ $room->id }}">
                    <header class="sv-room-card-head">
                        <div class="sv-room-card-title">
                            <x-feature-icon :name="$roomIcon" :tone="$roomTone" :size="18" variant="soft" class="sv-room-icon" />
                            <div>
                                <h3>{{ $room->name }}</h3>
                                <p>
                                    {{ $devices->count() }} perangkat ·
                                    <span data-room-active-count="{{ $room->id }}">{{ $roomActiveCount }}</span> aktif ·
                                    {{ number_format($power, 1, ',', '.') }} W
                                </p>
                            </div>
                        </div>
                        <span class="sv-badge {{ $roomOnlineCount > 0 ? 'sv-badge-success' : 'sv-badge-warning' }}">
                            <span class="sv-status-dot" aria-hidden="true"></span>
                            {{ $roomOnlineCount > 0 ? $roomOnlineCount . ' online' : 'Offline' }}
                        </span>
                    </header>

                    <div class="sv-room-card-body">
                        @forelse($devices as $device)
                            @php
                                $deviceOn = $isOn($device->status ?? null);
                                $espId = trim((string) ($device->esp_unit_id ?: $device->esp32_device_id));
                                $espOnline = $espId !== '' && $onlineIds->contains($espId);
                                $deviceLabel = strtolower(($device->type ?? '') . ' ' . ($device->name ?? ''));
                                $deviceIcon = str_contains($deviceLabel, 'kipas') || str_contains($deviceLabel, 'fan') ? 'fan'
                                    : (str_contains($deviceLabel, 'lampu') || str_contains($deviceLabel, 'light') ? 'lightbulb' : 'plug');
                                $deviceTone = $deviceOn ? 'green' : ($deviceIcon === 'fan' ? 'cyan' : ($deviceIcon === 'lightbulb' ? 'amber' : 'blue'));
                            @endphp
                            <div class="sv-device-row" data-device-row="{{ $device->id }}">
                                <div class="sv-device-main">
                                    <x-feature-icon :name="$deviceIcon" :tone="$deviceTone" :size="16" variant="soft" class="sv-device-icon" />
                                    <div class="sv-device-copy">
                                        <strong>{{ $device->name }}</strong>
                                        <span>Relay {{ $device->relay_code ?: '-' }} · ESP {{ $espId !== '' ? $espId : '-' }} · {{ $espOnline ? 'Siap dikontrol' : 'ESP32 offline' }}</span>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    class="sv-device-switch {{ $deviceOn ? 'is-on' : '' }} {{ ! $espOnline ? 'is-offline' : '' }}"
                                    data-device-toggle
                                    data-device-id="{{ $device->id }}"
                                    data-room-id="{{ $room->id }}"
                                    data-url="{{ route('devices.toggle', $device) }}"
                                    data-current-state="{{ $deviceOn ? 'on' : 'off' }}"
                                    aria-pressed="{{ $deviceOn ? 'true' : 'false' }}"
                                    {{ ! $espOnline ? 'disabled' : '' }}
                                    title="{{ $espOnline ? 'Ubah status perangkat' : 'ESP32 belum online' }}"
                                >
                                    <span data-switch-label>{{ $espOnline ? ($deviceOn ? 'Nyala' : 'Mati') : 'Offline' }}</span>
                                </button>
                            </div>
                        @empty
                            <div class="sv-empty-state sv-empty-state--compact">
                                <x-feature-icon name="plug" tone="cyan" :size="23" variant="soft" />
                                <h3>Belum ada perangkat</h3>
                                <p>Daftarkan sensor dan relay melalui Mode Teknisi.</p>
                            </div>
                        @endforelse
                    </div>

                    <footer class="sv-room-card-foot">
                        <span>{{ $roomOnlineCount > 0 ? 'Perangkat dapat dikontrol' : 'Menunggu telemetry ESP32' }}</span>
                        <a href="{{ route('settings', ['tab' => 'technician']) }}#room-{{ $room->id }}" class="sv-text-button">
                            <x-feature-icon name="settings" tone="slate" :size="14" variant="flat" />
                            Konfigurasi
                        </a>
                    </footer>
                </article>
            @endforeach
        </section>
    @endif
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/smartvolt-rooms.js') }}?v=20260730-v5" defer></script>
@endpush
```

## `resources/views/settings/index.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Pengaturan')
@section('page-title', request('tab') === 'technician' ? 'Mode Teknisi' : 'Pengaturan')
@section('page-subtitle', request('tab') === 'technician' ? 'Konfigurasi ruangan, ESP32, sensor PZEM, dan relay.' : 'Kelola akun, keamanan, tarif listrik, dan preferensi sistem.')
@section('body-class', 'sv-settings-page')

@php
    $advancedMode = (bool) session('advanced_mode');
    $requestedTab = request('tab');
    $activeTab = in_array($requestedTab, ['profile', 'security', 'system', 'technician'], true)
        ? $requestedTab
        : (session('open_advanced_panel') ? 'technician' : 'profile');

    if ($errors->has('advanced_mode')) {
        $activeTab = 'technician';
    } elseif ($errors->has('current_password') || $errors->has('password')) {
        $activeTab = 'security';
    } elseif ($errors->has('electricity_tariff') || $errors->has('power_limit') || $errors->has('refresh_interval')) {
        $activeTab = 'system';
    }

    $roomCollection = collect($rooms ?? []);
    $meterCollection = collect($energyMeters ?? []);
    $deviceCollection = collect($devices ?? []);
    $selectedRoomId = (int) (session('selected_room_id') ?? 0);
@endphp

@section('system-status')
    <span class="sv-badge {{ $advancedMode ? 'sv-badge-warning' : 'sv-badge-success' }}">
        <span class="sv-status-dot" aria-hidden="true"></span>
        {{ $advancedMode ? 'Mode Teknisi aktif' : 'Mode pengguna' }}
    </span>
@endsection

@section('content')
    <div class="sv-settings-layout">
        <nav class="sv-settings-nav" data-tabs data-panels-root="settingsPanels" aria-label="Bagian pengaturan">
            <button type="button" class="sv-tab-button {{ $activeTab === 'profile' ? 'is-active' : '' }}" data-tab-target="tab-profile" data-tab-name="profile" aria-selected="{{ $activeTab === 'profile' ? 'true' : 'false' }}">
                <x-feature-icon name="user" tone="blue" :size="16" variant="soft" /> <span>Profil</span>
            </button>
            <button type="button" class="sv-tab-button {{ $activeTab === 'security' ? 'is-active' : '' }}" data-tab-target="tab-security" data-tab-name="security" aria-selected="{{ $activeTab === 'security' ? 'true' : 'false' }}">
                <x-feature-icon name="shield" tone="green" :size="16" variant="soft" /> <span>Keamanan</span>
            </button>
            <button type="button" class="sv-tab-button {{ $activeTab === 'system' ? 'is-active' : '' }}" data-tab-target="tab-system" data-tab-name="system" aria-selected="{{ $activeTab === 'system' ? 'true' : 'false' }}">
                <x-feature-icon name="energy" tone="cyan" :size="16" variant="soft" /> <span>Energi & Sistem</span>
            </button>
            <button type="button" class="sv-tab-button {{ $activeTab === 'technician' ? 'is-active' : '' }}" data-tab-target="tab-technician" data-tab-name="technician" aria-selected="{{ $activeTab === 'technician' ? 'true' : 'false' }}">
                <x-feature-icon name="tools" tone="amber" :size="16" variant="soft" /> <span>Mode Teknisi</span>
            </button>
        </nav>

        <div class="sv-settings-panels" id="settingsPanels">
            <section id="tab-profile" class="sv-tab-panel {{ $activeTab === 'profile' ? 'is-active' : '' }}" data-tab-panel>
                <article class="sv-card sv-settings-card">
                    <header class="sv-profile-summary">
                        <span class="sv-profile-avatar-large">{{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}</span>
                        <div>
                            <h3>{{ $user->name ?? 'Pengguna SmartVolt' }}</h3>
                            <p>{{ $user->email ?? '-' }} · Akun pengguna SmartVolt</p>
                        </div>
                    </header>

                    <div class="sv-card-body">
                        <form action="{{ route('settings.profile.update') }}" method="POST" class="sv-stack" data-loading-form>
                            @csrf
                            @method('PUT')

                            <div class="sv-form-grid">
                                <div class="sv-form-field">
                                    <label for="profile_name" class="sv-form-label">Nama lengkap</label>
                                    <input type="text" id="profile_name" name="name" class="sv-form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" maxlength="255" autocomplete="name" required>
                                    @error('name')<p class="sv-form-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="sv-form-field">
                                    <label for="profile_email" class="sv-form-label">Alamat email</label>
                                    <input type="email" id="profile_email" name="email" class="sv-form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" maxlength="255" autocomplete="email" required>
                                    @error('email')<p class="sv-form-error">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div class="sv-form-actions">
                                <button type="submit" class="sv-button sv-button-primary" data-loading-text="Menyimpan...">
                                    <span class="sv-button-spinner" aria-hidden="true"></span>
                                    <x-feature-icon name="check" tone="blue" :size="14" variant="flat" /><span data-button-label>Simpan profil</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </article>
            </section>

            <section id="tab-security" class="sv-tab-panel {{ $activeTab === 'security' ? 'is-active' : '' }}" data-tab-panel>
                <article class="sv-card sv-settings-card">
                    <header class="sv-card-header sv-card-header--with-icon">
                        <x-feature-icon name="shield" tone="green" :size="19" variant="soft" />
                        <div>
                            <h2>Keamanan Akun</h2>
                            <p>Perbarui kata sandi akun SmartVolt.</p>
                        </div>
                    </header>
                    <div class="sv-card-body">
                        <div class="sv-security-note">
                            <x-feature-icon name="shield" tone="green" :size="18" variant="soft" class="sv-security-note-icon" />
                            <div>
                                <strong>Masukkan kata sandi saat ini untuk melanjutkan</strong>
                                <p>Gunakan kata sandi baru minimal delapan karakter.</p>
                            </div>
                        </div>

                        <form action="{{ route('settings.password.update') }}" method="POST" class="sv-stack" data-loading-form style="margin-top: 16px;">
                            @csrf
                            @method('PUT')

                            <div class="sv-form-field">
                                <label for="current_password" class="sv-form-label">Kata sandi saat ini</label>
                                <div class="sv-password-wrap">
                                    <input type="password" id="current_password" name="current_password" class="sv-form-control @error('current_password') is-invalid @enderror" autocomplete="current-password" required>
                                    <button type="button" class="sv-password-toggle" data-password-toggle="current_password" aria-label="Tampilkan kata sandi"><x-feature-icon name="eye" tone="slate" :size="16" variant="flat" /></button>
                                </div>
                                @error('current_password')<p class="sv-form-error">{{ $message }}</p>@enderror
                            </div>

                            <div class="sv-form-grid">
                                <div class="sv-form-field">
                                    <label for="new_password" class="sv-form-label">Kata sandi baru</label>
                                    <div class="sv-password-wrap">
                                        <input type="password" id="new_password" name="password" class="sv-form-control @error('password') is-invalid @enderror" minlength="8" autocomplete="new-password" required>
                                        <button type="button" class="sv-password-toggle" data-password-toggle="new_password" aria-label="Tampilkan kata sandi"><x-feature-icon name="eye" tone="slate" :size="16" variant="flat" /></button>
                                    </div>
                                    @error('password')<p class="sv-form-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="sv-form-field">
                                    <label for="new_password_confirmation" class="sv-form-label">Konfirmasi kata sandi</label>
                                    <div class="sv-password-wrap">
                                        <input type="password" id="new_password_confirmation" name="password_confirmation" class="sv-form-control" minlength="8" autocomplete="new-password" required>
                                        <button type="button" class="sv-password-toggle" data-password-toggle="new_password_confirmation" aria-label="Tampilkan kata sandi"><x-feature-icon name="eye" tone="slate" :size="16" variant="flat" /></button>
                                    </div>
                                </div>
                            </div>

                            <div class="sv-form-actions">
                                <button type="submit" class="sv-button sv-button-primary" data-loading-text="Memperbarui...">
                                    <span class="sv-button-spinner" aria-hidden="true"></span>
                                    <x-feature-icon name="shield" tone="green" :size="14" variant="flat" /><span data-button-label>Perbarui kata sandi</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </article>
            </section>

            <section id="tab-system" class="sv-tab-panel {{ $activeTab === 'system' ? 'is-active' : '' }}" data-tab-panel>
                <article class="sv-card sv-settings-card">
                    <header class="sv-card-header sv-card-header--with-icon">
                        <x-feature-icon name="energy" tone="cyan" :size="19" variant="soft" />
                        <div>
                            <h2>Energi dan Sistem</h2>
                            <p>Atur tarif listrik, batas daya, dan interval pembaruan.</p>
                        </div>
                        @if(!$advancedMode)
                            <span class="sv-badge sv-badge-warning"><x-feature-icon name="lock" tone="amber" :size="13" variant="flat" /> Terkunci</span>
                        @endif
                    </header>
                    <div class="sv-card-body">
                        @if(!$advancedMode)
                            <div class="sv-security-note">
                                <x-feature-icon name="lock" tone="amber" :size="18" variant="soft" class="sv-security-note-icon" />
                                <div>
                                    <strong>Aktifkan Mode Teknisi untuk mengubah konfigurasi sistem</strong>
                                    <p>Nilai tetap dapat dilihat, tetapi perubahan tarif, batas daya, dan interval refresh dibatasi dengan PIN teknisi.</p>
                                </div>
                            </div>
                        @endif

                        <form action="{{ route('settings.system.update') }}" method="POST" class="sv-stack" data-loading-form style="margin-top: 16px;">
                            @csrf
                            @method('PUT')

                            <div class="sv-form-grid-three">
                                <div class="sv-form-field">
                                    <label for="electricity_tariff" class="sv-form-label">Tarif listrik per kWh</label>
                                    <input type="number" step="0.01" min="0" id="electricity_tariff" name="electricity_tariff" class="sv-form-control @error('electricity_tariff') is-invalid @enderror" value="{{ old('electricity_tariff', $systemSetting->electricity_tariff) }}" {{ !$advancedMode ? 'disabled' : '' }} required>
                                    <p class="sv-form-help">Contoh: 1444 untuk Rp1.444/kWh.</p>
                                    @error('electricity_tariff')<p class="sv-form-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="sv-form-field">
                                    <label for="power_limit" class="sv-form-label">Batas daya rumah</label>
                                    <input type="number" min="1" id="power_limit" name="power_limit" class="sv-form-control @error('power_limit') is-invalid @enderror" value="{{ old('power_limit', $systemSetting->power_limit) }}" {{ !$advancedMode ? 'disabled' : '' }} required>
                                    <p class="sv-form-help">Satuan Watt, misalnya 900 atau 1300.</p>
                                    @error('power_limit')<p class="sv-form-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="sv-form-field">
                                    <label for="refresh_interval" class="sv-form-label">Interval pembaruan</label>
                                    <input type="number" min="1" max="60" id="refresh_interval" name="refresh_interval" class="sv-form-control @error('refresh_interval') is-invalid @enderror" value="{{ old('refresh_interval', $systemSetting->refresh_interval) }}" {{ !$advancedMode ? 'disabled' : '' }} required>
                                    <p class="sv-form-help">1–60 detik. Dashboard membatasi refresh minimum yang aman.</p>
                                    @error('refresh_interval')<p class="sv-form-error">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div class="sv-form-actions">
                                @if($advancedMode)
                                    <button type="submit" class="sv-button sv-button-primary" data-loading-text="Menyimpan...">
                                        <span class="sv-button-spinner" aria-hidden="true"></span>
                                        <x-feature-icon name="check" tone="cyan" :size="14" variant="flat" /><span data-button-label>Simpan pengaturan sistem</span>
                                    </button>
                                @else
                                    <a href="{{ route('settings', ['tab' => 'technician']) }}#technician" class="sv-button sv-button-warning">Aktifkan Mode Teknisi</a>
                                @endif
                            </div>
                        </form>
                    </div>
                </article>
            </section>

            <section id="tab-technician" class="sv-tab-panel {{ $activeTab === 'technician' ? 'is-active' : '' }}" data-tab-panel>
                @if(!$advancedMode)
                    <div class="sv-technician-lock" id="technician">
                        <div>
                            <x-feature-icon name="lock" tone="amber" :size="24" variant="soft" class="sv-technician-lock-icon" />
                            <h3>Akses Mode Teknisi</h3>
                            <p>Mode ini memungkinkan perubahan ruangan, ESP32, sensor PZEM, relay, dan parameter sistem. Masukkan PIN teknisi untuk melanjutkan.</p>
                            <button type="button" class="sv-button sv-button-warning" data-dialog-open="technicianPinDialog">
                                <x-feature-icon name="key" tone="amber" :size="16" variant="flat" /> Verifikasi PIN
                            </button>
                        </div>
                    </div>
                @else
                    <div class="sv-technician-toolbar" id="technician">
                        <x-feature-icon name="tools" tone="amber" :size="20" variant="soft" />
                        <div>
                            <strong>Mode Teknisi sedang aktif</strong>
                            <span>Perubahan konfigurasi akan langsung memengaruhi perangkat SmartVolt.</span>
                        </div>
                        <form action="{{ route('advanced-mode.disable') }}" method="POST" data-loading-form>
                            @csrf
                            <button type="submit" class="sv-button sv-button-secondary sv-button-sm" data-loading-text="Menonaktifkan...">
                                <span class="sv-button-spinner"></span><span data-button-label>Nonaktifkan</span>
                            </button>
                        </form>
                    </div>

                    <div class="sv-technician-summary">
                        <article class="sv-technician-summary-card sv-technician-summary-card--violet"><x-feature-icon name="rooms" tone="violet" :size="18" variant="soft" /><span>Ruangan</span><strong>{{ $roomCollection->count() }}</strong></article>
                        <article class="sv-technician-summary-card sv-technician-summary-card--green"><x-feature-icon name="sensor" tone="green" :size="18" variant="soft" /><span>Sensor PZEM</span><strong>{{ $meterCollection->count() }}</strong></article>
                        <article class="sv-technician-summary-card sv-technician-summary-card--cyan"><x-feature-icon name="plug" tone="cyan" :size="18" variant="soft" /><span>Relay</span><strong>{{ $deviceCollection->count() }}</strong></article>
                    </div>

                    <article class="sv-card sv-settings-card">
                        <header class="sv-card-header sv-card-header--with-icon">
                            <x-feature-icon name="server" tone="blue" :size="19" variant="soft" />
                            <div>
                                <h2>Konfigurasi Perangkat IoT</h2>
                                <p>Tambahkan ruangan, sensor, dan relay tanpa mengubah kode firmware dari halaman ini.</p>
                            </div>
                            <button type="button" class="sv-button sv-button-primary sv-button-sm" data-dialog-open="addRoomDialog">
                                <x-feature-icon name="plus" tone="blue" :size="15" variant="flat" /> Tambah ruangan
                            </button>
                        </header>
                        <div class="sv-card-body">
                            @if($roomCollection->isEmpty())
                                <div class="sv-empty-state">
                                    <x-feature-icon name="rooms" tone="violet" :size="26" variant="soft" />
                                    <h3>Belum ada ruangan</h3>
                                    <p>Tambahkan ruangan sebagai wadah untuk sensor listrik dan relay.</p>
                                    <button type="button" class="sv-button sv-button-primary" data-dialog-open="addRoomDialog">Tambah Ruangan</button>
                                </div>
                            @else
                                @foreach($roomCollection as $room)
                                    @php
                                        $roomMeters = collect($room->energyMeters ?? []);
                                        $roomDevices = collect($room->devices ?? []);
                                        $roomEspIds = $roomMeters->pluck('esp_unit_id')->filter()->unique()->values();
                                    @endphp
                                    <details class="sv-tech-room" id="room-{{ $room->id }}" {{ $selectedRoomId === $room->id || $loop->first ? 'open' : '' }}>
                                        <summary>
                                            <span class="sv-tech-room-heading">
                                                <x-feature-icon name="rooms" tone="violet" :size="17" variant="soft" class="sv-room-icon" />
                                                <span>
                                                    <strong>{{ $room->name }}</strong>
                                                    <span>{{ $roomMeters->count() }} sensor · {{ $roomDevices->count() }} relay</span>
                                                </span>
                                            </span>
                                            <span class="sv-room-chevron"><x-icon name="chevron-right" :size="17" /></span>
                                        </summary>

                                        <div class="sv-tech-room-content">
                                            <section class="sv-tech-subsection">
                                                <div class="sv-tech-subsection-head">
                                                    <h4>Pengaturan ruangan</h4>
                                                </div>
                                                <form action="{{ route('rooms.update', $room) }}" method="POST" class="sv-form-grid" data-loading-form>
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="return_to" value="settings">
                                                    <div class="sv-form-field">
                                                        <label for="room_name_{{ $room->id }}" class="sv-form-label">Nama ruangan</label>
                                                        <input type="text" id="room_name_{{ $room->id }}" name="name" class="sv-form-control" value="{{ $room->name }}" maxlength="100" required>
                                                    </div>
                                                    <div class="sv-form-actions">
                                                        <button type="submit" class="sv-button sv-button-secondary sv-button-sm" data-loading-text="Menyimpan..."><span data-button-label>Simpan nama</span></button>
                                                    </div>
                                                </form>
                                                <form action="{{ route('rooms.destroy', $room) }}" method="POST" data-confirm="Hapus ruangan {{ $room->name }}? Relay akan dihapus dan sensor yang memiliki riwayat akan dinonaktifkan." style="margin-top: 10px;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="return_to" value="settings">
                                                    <button type="submit" class="sv-button sv-button-danger sv-button-sm"><x-feature-icon name="trash" tone="rose" :size="14" variant="flat" /> Hapus ruangan</button>
                                                </form>
                                            </section>

                                            <section class="sv-tech-subsection">
                                                <div class="sv-tech-subsection-head">
                                                    <h4>Tambah ESP32, sensor PZEM, dan relay</h4>
                                                </div>
                                                <form action="{{ route('technician.rooms.sensor.store', $room) }}" method="POST" class="sv-stack" data-loading-form data-sensor-form>
                                                    @csrf
                                                    <input type="hidden" name="return_to" value="settings">
                                                    <div class="sv-form-grid-three">
                                                        <div class="sv-form-field">
                                                            <label class="sv-form-label" for="sensor_name_{{ $room->id }}">Nama sensor</label>
                                                            <input type="text" id="sensor_name_{{ $room->id }}" name="sensor_name" class="sv-form-control" placeholder="Meter {{ $room->name }}" maxlength="100" required>
                                                        </div>
                                                        <div class="sv-form-field">
                                                            <label class="sv-form-label" for="esp_unit_{{ $room->id }}">ESP Unit ID</label>
                                                            <input type="text" id="esp_unit_{{ $room->id }}" name="esp_unit_id" class="sv-form-control" placeholder="2" maxlength="100" required>
                                                        </div>
                                                        <div class="sv-form-field">
                                                            <label class="sv-form-label" for="meter_code_{{ $room->id }}">Meter code</label>
                                                            <input type="text" id="meter_code_{{ $room->id }}" name="meter_code" class="sv-form-control" value="main" maxlength="50" required>
                                                        </div>
                                                        <div class="sv-form-field">
                                                            <label class="sv-form-label" for="sensor_type_{{ $room->id }}">Jenis sensor</label>
                                                            <input type="text" id="sensor_type_{{ $room->id }}" name="sensor_type" class="sv-form-control" value="PZEM004T" maxlength="50">
                                                        </div>
                                                        <div class="sv-form-field">
                                                            <label class="sv-form-label" for="relay_count_{{ $room->id }}">Jumlah relay</label>
                                                            <select id="relay_count_{{ $room->id }}" name="relay_count" class="sv-form-control" data-relay-count required>
                                                                @for($relayCount = 1; $relayCount <= 8; $relayCount++)
                                                                    <option value="{{ $relayCount }}" {{ $relayCount === 2 ? 'selected' : '' }}>{{ $relayCount }} relay</option>
                                                                @endfor
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="sv-relay-name-fields" data-relay-names>
                                                        @for($relayIndex = 1; $relayIndex <= 8; $relayIndex++)
                                                            <div class="sv-form-field" data-relay-name-item="{{ $relayIndex }}" {{ $relayIndex > 2 ? 'hidden' : '' }}>
                                                                <label class="sv-form-label">Nama Relay {{ $relayIndex }}</label>
                                                                <input type="text" name="relay_names[{{ $relayIndex }}]" class="sv-form-control" placeholder="Relay {{ $relayIndex }} {{ $room->name }}" maxlength="100" {{ $relayIndex <= 2 ? 'required' : '' }}>
                                                            </div>
                                                        @endfor
                                                    </div>
                                                    <div class="sv-form-actions">
                                                        <button type="submit" class="sv-button sv-button-primary" data-loading-text="Menyimpan konfigurasi..."><span class="sv-button-spinner"></span><span data-button-label>Tambah konfigurasi</span></button>
                                                    </div>
                                                </form>
                                            </section>

                                            @if($roomEspIds->isNotEmpty())
                                                <section class="sv-tech-subsection">
                                                    <div class="sv-tech-subsection-head"><x-feature-icon name="plug" tone="cyan" :size="15" variant="soft" /><h4>Tambah relay ke ESP32 yang ada</h4></div>
                                                    <form action="{{ route('technician.rooms.relay.store', $room) }}" method="POST" class="sv-form-grid-three" data-loading-form>
                                                        @csrf
                                                        <input type="hidden" name="return_to" value="settings">
                                                        <div class="sv-form-field">
                                                            <label class="sv-form-label">ESP Unit ID</label>
                                                            <select name="esp_unit_id" class="sv-form-control" required>
                                                                @foreach($roomEspIds as $espId)<option value="{{ $espId }}">{{ $espId }}</option>@endforeach
                                                            </select>
                                                        </div>
                                                        <div class="sv-form-field">
                                                            <label class="sv-form-label">Relay channel</label>
                                                            <input type="text" name="relay_code" class="sv-form-control" placeholder="3" maxlength="50" required>
                                                        </div>
                                                        <div class="sv-form-field">
                                                            <label class="sv-form-label">Nama perangkat</label>
                                                            <input type="text" name="name" class="sv-form-control" placeholder="Lampu meja" maxlength="100" required>
                                                        </div>
                                                        <div class="sv-form-actions">
                                                            <button type="submit" class="sv-button sv-button-secondary" data-loading-text="Menambahkan..."><span data-button-label>Tambah relay</span></button>
                                                        </div>
                                                    </form>
                                                </section>
                                            @endif

                                            <section class="sv-tech-subsection">
                                                <div class="sv-tech-subsection-head"><x-feature-icon name="sensor" tone="green" :size="15" variant="soft" /><h4>Sensor listrik</h4></div>
                                                <div class="sv-tech-item-list">
                                                    @forelse($roomMeters as $meter)
                                                        <article class="sv-tech-item">
                                                            <div class="sv-tech-item-head">
                                                                <div class="sv-tech-item-copy">
                                                                    <strong>{{ $meter->name }}</strong>
                                                                    <span>ESP {{ $meter->esp_unit_id }} · {{ $meter->meter_code }} · {{ $meter->sensor_type ?: 'PZEM004T' }} · {{ $meter->readings_count ?? 0 }} pembacaan</span>
                                                                </div>
                                                                <span class="sv-badge {{ $meter->is_active ? 'sv-badge-success' : 'sv-badge-neutral' }}">{{ $meter->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                                            </div>

                                                            <div class="sv-tech-actions" style="margin-top: 10px;">
                                                                <form action="{{ route('technician.sensors.toggle', $meter) }}" method="POST" data-loading-form>
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <button type="submit" class="sv-button sv-button-secondary sv-button-sm" data-loading-text="Memproses..."><span data-button-label>{{ $meter->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</span></button>
                                                                </form>
                                                                <form action="{{ route('technician.sensors.destroy', $meter) }}" method="POST" data-confirm="Hapus sensor {{ $meter->name }}? Sensor yang memiliki riwayat akan dinonaktifkan, bukan dihapus permanen.">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="sv-button sv-button-danger sv-button-sm"><x-feature-icon name="trash" tone="rose" :size="13" variant="flat" /> Hapus</button>
                                                                </form>
                                                            </div>

                                                            <details class="sv-tech-edit">
                                                                <summary><x-feature-icon name="edit" tone="blue" :size="13" variant="flat" /> Ubah sensor</summary>
                                                                <div class="sv-tech-edit-body">
                                                                    <form action="{{ route('technician.sensors.update', $meter) }}" method="POST" class="sv-form-grid-three" data-loading-form>
                                                                        @csrf
                                                                        @method('PUT')
                                                                        <div class="sv-form-field"><label class="sv-form-label">Nama sensor</label><input type="text" name="name" value="{{ $meter->name }}" class="sv-form-control" maxlength="100" required></div>
                                                                        <div class="sv-form-field"><label class="sv-form-label">Meter code</label><input type="text" name="meter_code" value="{{ $meter->meter_code }}" class="sv-form-control" maxlength="50" required></div>
                                                                        <div class="sv-form-field"><label class="sv-form-label">Jenis sensor</label><input type="text" name="sensor_type" value="{{ $meter->sensor_type ?: 'PZEM004T' }}" class="sv-form-control" maxlength="50"></div>
                                                                        <label class="sv-checkbox"><input type="checkbox" name="is_active" value="1" {{ $meter->is_active ? 'checked' : '' }}><span></span><em>Sensor aktif</em></label>
                                                                        <div class="sv-form-actions"><button type="submit" class="sv-button sv-button-primary sv-button-sm" data-loading-text="Menyimpan..."><span data-button-label>Simpan sensor</span></button></div>
                                                                    </form>
                                                                </div>
                                                            </details>
                                                        </article>
                                                    @empty
                                                        <div class="sv-empty-state"><p>Belum ada sensor listrik pada ruangan ini.</p></div>
                                                    @endforelse
                                                </div>
                                            </section>

                                            <section class="sv-tech-subsection">
                                                <div class="sv-tech-subsection-head"><x-feature-icon name="devices" tone="cyan" :size="15" variant="soft" /><h4>Relay dan perangkat</h4></div>
                                                <div class="sv-tech-item-list">
                                                    @forelse($roomDevices as $device)
                                                        <article class="sv-tech-item">
                                                            <div class="sv-tech-item-head">
                                                                <div class="sv-tech-item-copy">
                                                                    <strong>{{ $device->name }}</strong>
                                                                    <span>ESP {{ $device->esp_unit_id ?: $device->esp32_device_id ?: '-' }} · Relay {{ $device->relay_code ?: '-' }} · {{ $device->status ? 'Nyala' : 'Mati' }}</span>
                                                                </div>
                                                                <span class="sv-badge {{ $device->status ? 'sv-badge-success' : 'sv-badge-neutral' }}">{{ $device->status ? 'Nyala' : 'Mati' }}</span>
                                                            </div>

                                                            <div class="sv-tech-actions" style="margin-top: 10px;">
                                                                <form action="{{ route('devices.destroy', $device) }}" method="POST" data-confirm="Hapus relay {{ $device->name }} dari konfigurasi?">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <input type="hidden" name="return_to" value="settings">
                                                                    <button type="submit" class="sv-button sv-button-danger sv-button-sm"><x-feature-icon name="trash" tone="rose" :size="13" variant="flat" /> Hapus</button>
                                                                </form>
                                                            </div>

                                                            <details class="sv-tech-edit">
                                                                <summary><x-feature-icon name="edit" tone="blue" :size="13" variant="flat" /> Ubah relay</summary>
                                                                <div class="sv-tech-edit-body">
                                                                    <form action="{{ route('devices.update', $device) }}" method="POST" class="sv-form-grid-three" data-loading-form>
                                                                        @csrf
                                                                        @method('PUT')
                                                                        <input type="hidden" name="return_to" value="settings">
                                                                        <div class="sv-form-field"><label class="sv-form-label">Nama perangkat</label><input type="text" name="name" value="{{ $device->name }}" class="sv-form-control" maxlength="100" required></div>
                                                                        <div class="sv-form-field"><label class="sv-form-label">ESP Unit ID</label><input type="text" name="esp_unit_id" value="{{ $device->esp_unit_id ?: $device->esp32_device_id }}" class="sv-form-control" maxlength="100" required></div>
                                                                        <div class="sv-form-field"><label class="sv-form-label">Relay channel</label><input type="text" name="relay_code" value="{{ $device->relay_code }}" class="sv-form-control" maxlength="100" required></div>
                                                                        <div class="sv-form-field"><label class="sv-form-label">Device key (opsional)</label><input type="text" name="device_key" value="{{ $device->device_key }}" class="sv-form-control" maxlength="100"></div>
                                                                        <div class="sv-form-actions"><button type="submit" class="sv-button sv-button-primary sv-button-sm" data-loading-text="Menyimpan..."><span data-button-label>Simpan relay</span></button></div>
                                                                    </form>
                                                                </div>
                                                            </details>
                                                        </article>
                                                    @empty
                                                        <div class="sv-empty-state"><p>Belum ada relay pada ruangan ini.</p></div>
                                                    @endforelse
                                                </div>
                                            </section>
                                        </div>
                                    </details>
                                @endforeach
                            @endif
                        </div>
                    </article>
                @endif
            </section>
        </div>
    </div>

    <dialog class="sv-dialog" id="technicianPinDialog" {{ $errors->has('advanced_mode') ? 'data-auto-open-dialog=technicianPinDialog' : '' }}>
        <header class="sv-dialog-header">
            <div><h3>Verifikasi PIN Teknisi</h3><p>PIN diperlukan sebelum konfigurasi perangkat dapat diubah.</p></div>
            <button type="button" class="sv-dialog-close" data-dialog-close aria-label="Tutup"><x-feature-icon name="close" tone="slate" :size="16" variant="flat" /></button>
        </header>
        <div class="sv-dialog-body">
            <form action="{{ route('advanced-mode.enable') }}" method="POST" data-loading-form>
                @csrf
                <div class="sv-form-field">
                    <label for="technician_pin" class="sv-form-label">PIN Teknisi</label>
                    <div class="sv-password-wrap">
                        <input type="password" id="technician_pin" name="pin" class="sv-form-control @error('advanced_mode') is-invalid @enderror" inputmode="numeric" autocomplete="off" required autofocus>
                        <button type="button" class="sv-password-toggle" data-password-toggle="technician_pin" aria-label="Tampilkan PIN"><x-feature-icon name="eye" tone="slate" :size="16" variant="flat" /></button>
                    </div>
                    @error('advanced_mode')<p class="sv-form-error">{{ $message }}</p>@enderror
                </div>
                <div class="sv-dialog-actions">
                    <button type="button" class="sv-button sv-button-secondary" data-dialog-close>Batal</button>
                    <button type="submit" class="sv-button sv-button-warning" data-loading-text="Memverifikasi..."><span class="sv-button-spinner"></span><span data-button-label>Verifikasi</span></button>
                </div>
            </form>
        </div>
    </dialog>

    <dialog class="sv-dialog" id="addRoomDialog">
        <header class="sv-dialog-header">
            <div><h3>Tambah Ruangan</h3><p>Ruangan menjadi kelompok untuk sensor listrik dan perangkat relay.</p></div>
            <button type="button" class="sv-dialog-close" data-dialog-close aria-label="Tutup"><x-feature-icon name="close" tone="slate" :size="16" variant="flat" /></button>
        </header>
        <div class="sv-dialog-body">
            <form action="{{ route('rooms.store') }}" method="POST" data-loading-form>
                @csrf
                <input type="hidden" name="return_to" value="settings">
                <div class="sv-form-field">
                    <label for="new_room_name" class="sv-form-label">Nama ruangan</label>
                    <input type="text" id="new_room_name" name="name" class="sv-form-control" maxlength="100" placeholder="Contoh: Ruang Tamu" required>
                </div>
                <div class="sv-dialog-actions">
                    <button type="button" class="sv-button sv-button-secondary" data-dialog-close>Batal</button>
                    <button type="submit" class="sv-button sv-button-primary" data-loading-text="Menyimpan..."><span class="sv-button-spinner"></span><span data-button-label>Simpan ruangan</span></button>
                </div>
            </form>
        </div>
    </dialog>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/smartvolt-settings.js') }}?v=20260730-v5" defer></script>
@endpush
```

