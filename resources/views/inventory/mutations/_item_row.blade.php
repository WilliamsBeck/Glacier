{{-- Bahan --}}
<td>
    <select name="items[{{ $idx }}][ingredient_id]" class="form-select form-select-sm" required onchange="onIngredientChange(this.value, {{ $idx }})">
        <option value="">— Pilih Bahan —</option>
        @php
            $catOrder  = \App\Models\IngredientCategory::orderedNames();
            $catLabels = \App\Models\IngredientCategory::labelsMap();
        @endphp
        @foreach($catOrder as $cat)
            @php $group = $ingredients->filter(fn($i) => $i->type === 'raw' && $i->category === $cat); @endphp
            @if($group->isNotEmpty())
                <optgroup label="{{ $catLabels[$cat] }}">
                    @foreach($group as $ing)
                        <option value="{{ $ing->id }}" data-unit="{{ $ing->unit_base }}">{{ $ing->name }}</option>
                    @endforeach
                </optgroup>
            @endif
        @endforeach
        @php $nocat = $ingredients->filter(fn($i) => $i->type === 'raw' && !$i->category); @endphp
        @if($nocat->isNotEmpty())
            <optgroup label="Lainnya">
                @foreach($nocat as $ing)
                    <option value="{{ $ing->id }}" data-unit="{{ $ing->unit_base }}">{{ $ing->name }}</option>
                @endforeach
            </optgroup>
        @endif
        @php $semi = $ingredients->filter(fn($i) => $i->type === 'semi_finished'); @endphp
        @if($semi->isNotEmpty())
            <optgroup label="Setengah Jadi">
                @foreach($semi as $ing)
                    <option value="{{ $ing->id }}" data-unit="{{ $ing->unit_base }}">{{ $ing->name }}</option>
                @endforeach
            </optgroup>
        @endif
    </select>
    {{-- Info text di bawah bahan (qty available, stock price) --}}
    <div class="qty-available-info d-none small text-muted mt-1"></div>
    <div class="stock-price-info d-none small text-info mt-1"></div>
</td>

{{-- Kemasan --}}
<td>
    <div class="wrap-packaging d-none">
        <select name="items[{{ $idx }}][packaging_id]" class="form-select form-select-sm packaging-select" onchange="onPackagingChange({{ $idx }})">
            <option value="">— Pilih Kemasan —</option>
        </select>
    </div>
    <span class="text-muted small no-packaging-label">—</span>
</td>

{{-- Dus --}}
<td>
    <input type="number" name="items[{{ $idx }}][qty_crate]" class="form-control form-control-sm qty-input" min="0" placeholder="0" oninput="checkRowStock({{ $idx }})">
</td>

{{-- Pack --}}
<td>
    <input type="number" name="items[{{ $idx }}][qty_pack]" class="form-control form-control-sm qty-input" min="0" placeholder="0" oninput="checkRowStock({{ $idx }})">
</td>

{{-- Satuan --}}
<td>
    <input type="number" name="items[{{ $idx }}][qty_base]" class="form-control form-control-sm qty-input" step="0.01" min="0" placeholder="0" oninput="checkRowStock({{ $idx }})">
    <span class="d-none label-qty-base"></span>
</td>

{{-- Harga --}}
<td>
    {{-- Harga per Dus (saat ada packaging) --}}
    <div class="wrap-price-crate d-none">
        <div class="input-group input-group-sm">
            <span class="input-group-text">Rp</span>
            <input type="text" class="form-control form-control-sm price-crate-input num-fmt" placeholder="0" oninput="onPriceCrateChange({{ $idx }})">
        </div>
        <div class="form-text price-info d-none text-primary"></div>
    </div>
    {{-- Harga per satuan (fallback) --}}
    <div class="wrap-price-direct d-none">
        <div class="input-group input-group-sm">
            <span class="input-group-text">Rp</span>
            <input type="number" class="form-control form-control-sm" step="0.01" min="0" placeholder="0"
                   oninput="document.querySelector('#row-{{ $idx }} .price-per-base-hidden').value = this.value">
        </div>
    </div>
    {{-- Harga jual (sale_internal/external) --}}
    <div class="sale-price d-none mt-1">
        <div class="input-group input-group-sm">
            <span class="input-group-text">Jual</span>
            <span class="input-group-text">Rp</span>
            <input type="number" name="items[{{ $idx }}][selling_price_per_base]" class="form-control form-control-sm" step="0.01" min="0" placeholder="0">
        </div>
    </div>
    <span class="text-muted small no-price-label">—</span>
    {{-- Hidden price_per_base --}}
    <input type="hidden" name="items[{{ $idx }}][price_per_base]" class="price-per-base-hidden" value="0">
</td>

{{-- Hapus --}}
<td class="text-center">
    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" onclick="removeRow({{ $idx }})" style="display:none" title="Hapus">
        <i class="bi bi-x-lg"></i>
    </button>
</td>
