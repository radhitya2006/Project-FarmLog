<section id="{{ $kind }}" class="dashboard-section mb-5" aria-labelledby="{{ $kind }}-heading">
    <div class="farmer-page-heading"><div><h2 class="fs-4" id="{{ $kind }}-heading">{{ $heading }}</h2><p>{{ $records->total() }} transaksi tercatat</p></div><a class="btn btn-success" href="{{ route('farmer.lands.planting-seasons.'.$kind.'.create', [$land, $season]) }}"><i class="bi bi-plus-lg me-2" aria-hidden="true"></i>Catat {{ $label }}</a></div>
    @if($records->isEmpty())
        <div class="farmer-panel">
            @if($records->total() > 0)
                <x-farmer-empty-state title="Tidak ada transaksi di halaman ini" description="Kembali ke halaman pertama untuk melihat catatan."><a class="btn btn-outline-success" href="{{ route('farmer.finance.show', [$land, $season]) }}#{{ $kind }}">Halaman pertama</a></x-farmer-empty-state>
            @else
                <x-farmer-empty-state :title="'Belum ada '.$label" :description="$kind === 'expenses' ? 'Mulai dari biaya bibit, pupuk, upah, atau kebutuhan lain yang sudah dibayar.' : 'Catat uang hasil penjualan yang sudah diterima untuk musim ini.'" />
            @endif
        </div>
    @else
        <div class="farmer-panel p-0 overflow-hidden"><div class="table-responsive"><table class="table activity-table mb-0">
            <caption class="visually-hidden">{{ $heading }} musim {{ $season->crop_name }}</caption>
            <thead><tr><th scope="col">Tanggal</th><th scope="col">Keterangan</th>@if($kind === 'expenses')<th scope="col">Kategori</th>@endif<th scope="col">Nominal</th><th scope="col">Aksi</th></tr></thead>
            <tbody>@foreach($records as $record)<tr>
                <td class="text-nowrap">{{ $record->{$dateField}->format('d/m/Y') }}</td>
                <td class="activity-name">{{ $record->name }}@if($record->notes)<details class="finance-notes"><summary>Catatan</summary><p class="land-notes mb-0">{{ $record->notes }}</p></details>@endif</td>
                @if($kind === 'expenses')<td>{{ $record->category->label() }}</td>@endif
                <td class="text-nowrap"><x-money :amount="$record->amount" /></td>
                <td><div class="d-flex justify-content-end gap-2"><a class="btn btn-sm btn-outline-success" href="{{ route('farmer.lands.planting-seasons.'.$kind.'.edit', [$land, $season, $record]) }}" aria-label="Edit {{ $record->name }}">Edit</a><button class="btn btn-sm btn-outline-danger" type="button" data-bs-toggle="modal" data-bs-target="#delete-{{ $kind }}-{{ $record->id }}" aria-label="Hapus {{ $record->name }}">Hapus</button></div></td>
            </tr>@endforeach</tbody>
        </table></div></div>
        <div class="farmer-pagination mt-3">{{ $records->links('pagination.farmer') }}</div>
        @foreach($records as $record)
            <x-confirm-action-modal :id="'delete-'.$kind.'-'.$record->id" :title="'Hapus '.$label.' ini?'" :action="route('farmer.lands.planting-seasons.'.$kind.'.destroy', [$land, $season, $record])" label="Ya, hapus transaksi">
                <strong class="text-break">{{ $record->name }}</strong> sebesar <x-money :amount="$record->amount" /> akan dihapus permanen. Ringkasan keuangan akan dihitung ulang.
            </x-confirm-action-modal>
        @endforeach
    @endif
</section>

