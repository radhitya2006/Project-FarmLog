<div class="row g-3 mb-4">
    @foreach(['expenses' => 'Total pengeluaran', 'sales' => 'Pemasukan penjualan', 'balance' => 'Selisih tercatat'] as $key => $title)
        <div class="{{ array_key_exists('cost_per_kg', $summary) ? 'col-sm-6 col-xl-3' : 'col-md-4' }}"><div class="farmer-panel finance-stat h-100">
            <span>{{ $title }}</span><strong @class(['text-danger' => $key === 'balance' && bccomp($summary['balance'], '0', 2) < 0])><x-money :amount="$summary[$key]" /></strong>
            @if($key === 'balance')<small>Pemasukan dikurangi pengeluaran.</small>@endif
        </div></div>
    @endforeach
    @if(array_key_exists('cost_per_kg', $summary))
        <div class="col-sm-6 col-xl-3"><div class="farmer-panel finance-stat h-100"><span>Biaya/kg panen</span>
            <strong>@if($summary['cost_per_kg'] !== null)<x-money :amount="$summary['cost_per_kg']" />@else — @endif</strong>
            <small>{{ bccomp($summary['weight_kg'], '0', 2) === 0 ? 'Belum ada berat panen tercatat.' : (bccomp($summary['expenses'], '0', 2) === 0 ? 'Belum ada pengeluaran tercatat.' : 'Total pengeluaran dibagi berat panen.') }}</small>
        </div></div>
    @endif
</div>
<p class="finance-explanation mb-4"><i class="bi bi-info-circle me-1" aria-hidden="true"></i>Selisih tercatat belum merupakan laba final jika biaya belum lengkap atau panen belum seluruhnya terjual. Catat hanya pengeluaran yang sudah dibayar dan penjualan yang uangnya sudah diterima.</p>

