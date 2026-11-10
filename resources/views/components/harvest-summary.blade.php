@props(['season'])
<div class="row g-3">
    <div class="col-sm-6"><div class="harvest-total"><span>Total kilogram</span><strong><x-harvest-quantity :quantity="$season->total_kg" unit="kg" /></strong></div></div>
    <div class="col-sm-6"><div class="harvest-total"><span>Total ton</span><strong><x-harvest-quantity :quantity="$season->total_ton" unit="ton" /></strong></div></div>
</div>
