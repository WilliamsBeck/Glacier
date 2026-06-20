{{-- View ini di-render sebagai file .xls (HTML table dibaca Excel) --}}
<?php
$monthNames = ['','Januari','Februari','Maret','April','Mei','Juni',
               'Juli','Agustus','September','Oktober','November','Desember'];
?>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        h2   { font-size: 13px; margin-bottom: 4px; }
        p    { font-size: 10px; color: #555; margin: 0 0 8px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 4px 8px; text-align: center; }
        th { background: #2c2c2c; color: white; }
        .left { text-align: left; }
        .green { background: #d4edda; font-weight: bold; }
        .blue  { background: #cce5ff; font-weight: bold; }
        .cyan  { background: #d1ecf1; font-weight: bold; }
        .warn  { background: #fff3cd; font-size: 9px; }
        .muted { color: #888; }
        .total-row td { background: #2c2c2c; color: white; font-weight: bold; }
    </style>
</head>
<body>

<h2>RENCANA ORDER ZHISHENG — {{ strtoupper($store->name) }}</h2>
<p>
    Referensi konsumsi: {{ $monthNames[$refMonth] }} {{ $refYear }} ({{ $daysInRef }} hari) &nbsp;|&nbsp;
    Barang tiba: {{ $deliveryDate->isoFormat('D MMMM Y') }} &nbsp;|&nbsp;
    Coverage s/d: {{ $coverageEnd->isoFormat('D MMMM Y') }} ({{ $daysToCover }} hari) &nbsp;|&nbsp;
    Buffer: {{ $bufferPct }}% &nbsp;|&nbsp;
    Diekspor: {{ now()->isoFormat('D MMMM Y, HH:mm') }}
</p>

<table>
    <thead>
        <tr>
            <th class="left">Bahan Baku</th>
            <th>Satuan<br>(Packaging)</th>
            <th>Konsumsi {{ $monthNames[$refMonth] }}<br>(Dus)</th>
            <th>Stok Sekarang<br>(Dus)</th>
            <th>Kebutuhan<br>(Dus)</th>
            @if($bufferPct > 0)
            <th>Buffer<br>(Dus)</th>
            @endif
            <th>BELI (Dus) ▲</th>
        </tr>
    </thead>
    <tbody>
    @php $totalDus = 0; $fmt = fn($v) => number_format($v, 2, ',', '.'); @endphp
    @foreach($tableData as $row)
    @php $totalDus += $row->net_dus; @endphp
    <tr>
        <td class="left">
            {{ $row->ingredient->name }}
            @if($row->active_days < $daysInRef * 0.5) *) @endif
        </td>
        <td>{{ $row->packaging->packaging_name }}</td>
        <td>{{ $fmt($row->ref_total_dus) }}</td>
        <td class="muted">{{ $fmt($row->stock_dus) }}</td>
        <td>{{ $fmt($row->gross_dus) }}</td>
        @if($bufferPct > 0)
        <td class="muted">+{{ $fmt($row->buffer_dus) }}</td>
        @endif
        <td class="{{ $row->net_dus > 0 ? 'green' : '' }}">
            {{ $row->net_dus > 0 ? $row->net_dus : '—' }}
        </td>
    </tr>
    @endforeach
    </tbody>
    <tfoot>
        <tr class="total-row">
            <td colspan="{{ 4 + ($bufferPct > 0 ? 1 : 0) }}" style="text-align:right">TOTAL</td>
            <td>{{ $totalDus }} Dus</td>
        </tr>
    </tfoot>
</table>

<p style="margin-top:8px">
    ▲ Jumlah Dus dibulatkan ke atas (ceiling). Stok saat ini sudah dikurangkan.<br>
    @if(collect($tableData)->where('active_days', '<', 7)->count() > 0)
    *) Data konsumsi kurang dari 7 hari — estimasi kurang akurat, perlu diverifikasi manual.
    @endif
</p>

</body>
</html>
