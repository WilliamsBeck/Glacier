@props(['href', 'label' => 'Lihat Detail'])

<li>
    <a class="dropdown-item" href="{{ $href }}">
        <i class="bi bi-eye"></i> {{ $label }}
    </a>
</li>
