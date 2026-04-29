<form action="{{ route('logout') }}" method="POST" style="display:inline;">
    @csrf
    <button type="submit" class="sv-btn sv-logout-btn">
        <i class="bi bi-box-arrow-right"></i>
        <span>Logout</span>
    </button>
</form>