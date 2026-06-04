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
            <th rowspan="2" class="left">Bahan</th>
            <th rowspan="2">Satuan<br>(Packaging)</th>
            <th colspan="2">Konsumsi {{ $monthNames[$refMonth] }}</th>
            <th colspan="2">Stok Sekarang</th>
            <th rowspan="2">Hari<br>Cover</th>
            <th rowspan="2">Kebutuhan<br>(Pack)</th>
            @if($bufferPct > 0)
            <th rowspan="2">Buffer<br>(Pack)</th>
            @endif
            @if($splitOrder)
            <th>Order 1 (Dus)</th>
            <th>Order 2 (Dus)</th>
            @else
            <th rowspan="2">BELI (Dus) ▲</th>
            @endif
        </tr>
        <tr>
            <th>Total Pack</th>
            <th>Rata²/Hari</th>
            <th>Stok Pack</th>
            <th>Stok Dus</th>
            @if($splitOrder)
            <th>50%</th>
            <th>50%</th>
            @endif
        </tr>
    </thead>
    <tbody>
    @php $totalDus = 0; $totalO1 = 0; $totalO2 = 0; @endphp
    @foreach($tableData as $row)
    @php $totalDus += $row->net_dus; $totalO1 += $row->order1_dus; $totalO2 += $row->order2_dus; @endphp
    <tr>
        <td class="left">
            {{ $row->ingredient->name }}
            @if($row->active_days < 7) *) @endif
        </td>
        <td>{{ $row->packaging->packaging_name }}</td>
        <td>{{ $row->ref_total_pack }}</td>
        <td>{{ $row->avg_daily_pack }}</td>
        <td>{{ $row->stock_pack }}</td>
        <td class="muted">{{ number_format($row->stock_dus, 1, ',', '.') }}</td>
        <td>{{ $row->days_cover }}</td>
        <td>{{ $row->gross_pack }}</td>
        @if($bufferPct > 0)
        <td class="muted">+{{ $row->buffer_pack }}</td>
        @endif
        @if($splitOrder)
        <td class="{{ $row->order1_dus > 0 ? 'blue' : '' }}">
            {{ $row->order1_dus > 0 ? $row->order1_dus : '—' }}
        </td>
        <td class="{{ $row->order2_dus > 0 ? 'cyan' : '' }}">
            {{ $row->order2_dus > 0 ? $row->order2_dus : '—' }}
        </td>
        @else
        <td class="{{ $row->net_dus > 0 ? 'green' : '' }}">
            {{ $row->net_dus > 0 ? $row->net_dus : '—' }}
        </td>
        @endif
    </tr>
    @endforeach
    </tbody>
    <tfoot>
        <tr class="total-row">
            <td colspan="{{ 7 + ($bufferPct > 0 ? 1 : 0) }}" style="text-align:right">TOTAL</td>
            @if($splitOrder)
            <td>{{ $totalO1 }} Dus</td>
            <td>{{ $totalO2 }} Dus</td>
            @else
            <td>{{ $totalDus }} Dus</td>
            @endif
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
