{{--
    Dropdown aksi titik-3 (kebab) — konsisten untuk semua tabel index.
    Pemakaian:
      <x-action-menu>
          <a class="dropdown-item" href="...">
              <i class="bi bi-eye"></i> Lihat Detail
          </a>
          <a class="dropdown-item" href="...">
              <i class="bi bi-pencil"></i> Edit
          </a>
          <x-action-delete :action="route('...')" />
      </x-action-menu>
--}}
<div class="dropdown action-menu">
    <button class="btn btn-sm btn-icon" type="button" data-bs-toggle="dropdown"
            data-bs-display="static" aria-expanded="false" title="Aksi">
        <i class="bi bi-three-dots-vertical"></i>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        {{ $slot }}
    </ul>
</div>
