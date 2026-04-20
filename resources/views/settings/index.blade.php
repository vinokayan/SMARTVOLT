<h1>Settings</h1>

@if(session('status'))
    <div>{{ session('status') }}</div>
@endif

@if($errors->any())
    <div>{{ $errors->first() }}</div>
@endif

<h2>Update Profil</h2>
<form method="POST" action="{{ route('settings.profile.update') }}">
    @csrf
    @method('PUT')

    <input type="text" name="name" value="{{ old('name', $user->name) }}" placeholder="Nama">
    <input type="email" name="email" value="{{ old('email', $user->email) }}" placeholder="Email">
    <button type="submit">Simpan Profil</button>
</form>

<h2>Update Password</h2>
<form method="POST" action="{{ route('settings.password.update') }}">
    @csrf
    @method('PUT')

    <input type="password" name="current_password" placeholder="Password lama">
    <input type="password" name="password" placeholder="Password baru">
    <input type="password" name="password_confirmation" placeholder="Konfirmasi password baru">
    <button type="submit">Ubah Password</button>
</form>