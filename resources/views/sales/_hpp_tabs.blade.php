@php $currentHppTab = $currentHppTab ?? 'periode'; @endphp
<div class="page-header mb-2">
    <h4 class="page-title mb-2">Analisa HPP</h4>
    <ul class="nav nav-pills">
        <li class="nav-item">
            <a class="nav-link {{ $currentHppTab === 'periode' ? 'active' : '' }}" href="{{ route('sales.hpp.index') }}">
                <i class="bi bi-calendar3 me-1"></i>Per Periode
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $currentHppTab === 'tren' ? 'active' : '' }}" href="{{ route('sales.hpp.trend') }}">
                <i class="bi bi-graph-up-arrow me-1"></i>Tren
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $currentHppTab === 'compare' ? 'active' : '' }}" href="{{ route('sales.hpp.compare') }}">
                <i class="bi bi-bar-chart-steps me-1"></i>Perbandingan Toko
            </a>
        </li>
    </ul>
</div>
