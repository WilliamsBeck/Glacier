@props([
    'action',
    'label'   => 'Hapus',
    'confirm' => 'Yakin ingin menghapus data ini?',
    'method'  => 'DELETE',
])

<li>
    <form method="POST" action="{{ $action }}" data-confirm="{{ $confirm }}" data-confirm-type="error" data-confirm-danger="1" data-confirm-ok="Ya, hapus">
        @csrf
        @method($method)
        <button type="submit" class="dropdown-item text-danger">
            <i class="bi bi-trash"></i> {{ $label }}
        </button>
    </form>
</li>
