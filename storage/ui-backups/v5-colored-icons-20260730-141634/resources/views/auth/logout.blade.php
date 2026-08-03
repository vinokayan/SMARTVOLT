<form action="{{ route('logout') }}" method="POST" data-loading-form>
    @csrf
    <button type="submit" class="sv-button sv-button-ghost sv-button-sm" data-loading-text="Keluar...">
        <x-icon name="logout" :size="16" />
        <span data-button-label>Keluar</span>
    </button>
</form>
