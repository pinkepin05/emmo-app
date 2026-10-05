<h2>Form Edit Master Role</h2>

<form action="{{ route('roles.update', $role->id) }}" method="POST">
    @csrf
    
    @method('PUT') 

    <div>
        <label>Role Code:</label><br>
        <input type="text" name="code" value="{{ $role->code }}" readonly>
    </div>

    <div>
        <label>nama:</label><br>
        <textarea name="nama" required>{{ $role->nama }}</textarea>
    </div>

    <div>
        <label>
            <input type="checkbox" name="is_active" value="1" {{ $role->is_active ? 'checked' : '' }}>
            Set Aktif (Is Active)
        </label>
    </div>

    <button type="submit">Update Master Role</button>
</form>