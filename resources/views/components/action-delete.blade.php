@props([
    'action',
    'label'   => 'Hapus',
    'confirm' => 'Yakin ingin menghapus data ini?',
    'method'  => 'DELETE',
])

<li>
    <form method="POST" action="{{ $action }}" onsubmit="return confirm('{{ $confirm }}')">
        @csrf
        @method($method)
        <button type="submit" class="dropdown-item text-danger">
            <i class="bi bi-trash"></i> {{ $label }}
        </button>
    </form>
</li>
