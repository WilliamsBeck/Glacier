@props(['href', 'label' => 'Edit'])

<li>
    <a class="dropdown-item" href="{{ $href }}">
        <i class="bi bi-pencil"></i> {{ $label }}
    </a>
</li>
