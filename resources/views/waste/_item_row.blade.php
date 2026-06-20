{{-- Waste item row partial — row pertama (Blade static) --}}
<td>
    <select name="items[{{ $idx }}][ingredient_id]" class="form-select form-select-sm ing-select"
            required onchange="onIngredientChange({{ $idx }})">
        <option value="">— Pilih Bahan —</option>
        @if($ingredients->where('type','semi_finished')->isNotEmpty())
        <optgroup label="Setengah Jadi">
            @foreach($ingredients->where('type','semi_finished') as $i)
                <option value="{{ $i->id }}" data-type="semi_finished">{{ $i->name }}</option>
            @endforeach
        </optgroup>
        @endif
        @if($ingredients->where('type','raw')->isNotEmpty())
        <optgroup label="Bahan Baku">
            @foreach($ingredients->where('type','raw') as $i)
                <option value="{{ $i->id }}" data-type="raw">{{ $i->name }}</option>
            @endforeach
        </optgroup>
        @endif
    </select>
</td>
<td>
    <div class="wrap-packaging d-none">
        <select name="items[{{ $idx }}][packaging_id]" class="form-select form-select-sm pkg-select"
                onchange="onPackagingChange({{ $idx }})">
            <option value="">— Pilih Kemasan —</option>
        </select>
    </div>
</td>
<td>
    <div class="wrap-crate d-none">
        <input type="number" name="items[{{ $idx }}][qty_crate]"
               class="form-control form-control-sm" min="0" placeholder="0">
    </div>
</td>
<td>
    <div class="wrap-pack d-none">
        <input type="number" name="items[{{ $idx }}][qty_pack]"
               class="form-control form-control-sm" min="0" placeholder="0">
    </div>
</td>
<td>
    <input type="number" name="items[{{ $idx }}][qty_base]"
           class="form-control form-control-sm" step="0.01" min="0" placeholder="0">
    <span class="label-unit text-muted small d-block" style="font-size:.7rem"></span>
</td>
<td>
    {{-- Manual input (hidden, tidak dipakai) --}}
    <div class="wrap-nominal" style="display:none"></div>
    {{-- Auto display untuk semua tipe --}}
    <div class="wrap-raw-loss">
        <span class="loss-display text-muted small fw-semibold">—</span>
        <input type="hidden" name="items[{{ $idx }}][nominal_loss]" class="hidden-raw-loss" value="0">
    </div>
</td>
<td class="text-center">
    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"
            onclick="removeWasteRow({{ $idx }})" title="Hapus" style="display:none">
        <i class="bi bi-x-lg"></i>
    </button>
</td>
