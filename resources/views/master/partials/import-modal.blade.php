{{-- Modal upload untuk satu entitas. Param: $entity, $label. --}}
@php $modalId = 'importModal_' . str_replace('-', '_', $entity); @endphp
<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('master.import.preview', $entity) }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Impor {{ $label ?? 'Data' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-2">
                        Unduh template lebih dulu, isi datanya, lalu unggah di sini. Anda akan melihat
                        pratinjau sebelum data tersimpan.
                    </p>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i> Pratinjau</button>
                </div>
            </div>
        </form>
    </div>
</div>
