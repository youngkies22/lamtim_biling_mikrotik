@extends('layouts/layoutMaster')

@section('title', 'Rekap Tagihan')

@section('page-style')
<style>
  .rekap-card {
    transition: transform 0.15s ease, box-shadow 0.15s ease;
  }
  .rekap-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0,0,0,.12) !important;
  }
  .bulan-header {
    font-size: 1rem;
    font-weight: 700;
    letter-spacing: .5px;
  }
  .stat-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 4px 0;
    font-size: 0.82rem;
  }
  .stat-row .label { color: #6c757d; }
  .stat-row .value { font-weight: 600; }
  .divider-thin { border-top: 1px dashed #dee2e6; margin: 8px 0; }
  .grand-total-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.88rem;
    font-weight: 700;
  }
  .empty-month { opacity: .55; }
  .badge-count {
    font-size: 0.7rem;
    padding: 2px 6px;
    border-radius: 10px;
  }
</style>
@endsection

@section('page-script')
<script>
  document.getElementById('filter-tahun').addEventListener('change', function() {
    const tahun = this.value;
    const url = new URL(window.location.href);
    url.searchParams.set('tahun', tahun);
    window.location.href = url.toString();
  });
</script>
@endsection

@section('content')

{{-- Header & Filter --}}
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h4 class="fw-bold mb-1">Rekap Tagihan {{ $tahunDipilih }}</h4>
    <p class="text-muted mb-0" style="font-size:.875rem;">Ringkasan tagihan per bulan — belum bayar, sudah bayar, dan total keseluruhan.</p>
  </div>
  <div class="d-flex align-items-center gap-2">
    <label for="filter-tahun" class="form-label mb-0 text-muted small fw-medium">Tahun:</label>
    <select id="filter-tahun" class="form-select form-select-sm" style="width: auto; min-width: 90px;">
      @foreach($listTahun as $t)
        <option value="{{ $t }}" {{ $t == $tahunDipilih ? 'selected' : '' }}>{{ $t }}</option>
      @endforeach
    </select>
  </div>
</div>

{{-- Summary Bar --}}
@php
  $totalSemua       = collect($rekapBulan)->sum('grand_total');
  $totalBelumBayar  = collect($rekapBulan)->sum('belum_bayar_total');
  $totalSudahBayar  = collect($rekapBulan)->sum('sudah_bayar_total');
  $totalTagihan     = collect($rekapBulan)->sum('total_tagihan');
@endphp
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body p-3 d-flex align-items-center gap-3">
        <div class="d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-2 flex-shrink-0" style="width:42px;height:42px;">
          <i class="mdi mdi-file-document-multiple fs-5"></i>
        </div>
        <div>
          <div class="text-muted small">Total Tagihan</div>
          <div class="fw-bold fs-6">{{ number_format($totalTagihan) }}</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body p-3 d-flex align-items-center gap-3">
        <div class="d-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger rounded-2 flex-shrink-0" style="width:42px;height:42px;">
          <i class="mdi mdi-cash-clock fs-5"></i>
        </div>
        <div>
          <div class="text-muted small">Belum Bayar</div>
          <div class="fw-bold fs-6">Rp {{ number_format($totalBelumBayar) }}</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body p-3 d-flex align-items-center gap-3">
        <div class="d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success rounded-2 flex-shrink-0" style="width:42px;height:42px;">
          <i class="mdi mdi-check-circle-outline fs-5"></i>
        </div>
        <div>
          <div class="text-muted small">Sudah Bayar</div>
          <div class="fw-bold fs-6">Rp {{ number_format($totalSudahBayar) }}</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body p-3 d-flex align-items-center gap-3">
        <div class="d-flex align-items-center justify-content-center bg-info bg-opacity-10 text-info rounded-2 flex-shrink-0" style="width:42px;height:42px;">
          <i class="mdi mdi-currency-usd fs-5"></i>
        </div>
        <div>
          <div class="text-muted small">Grand Total</div>
          <div class="fw-bold fs-6">Rp {{ number_format($totalSemua) }}</div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Grid 12 Bulan --}}
<div class="row g-3">
  @foreach($rekapBulan as $bulan)
  @php $isEmpty = $bulan['total_tagihan'] == 0; @endphp
  <div class="col-12 col-sm-6 col-md-4 col-xl-3">
    <div class="card border-0 shadow-sm rekap-card h-100 {{ $isEmpty ? 'empty-month' : '' }}">
      <div class="card-body p-3">

        {{-- Header Bulan --}}
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="bulan-header text-dark">{{ $bulan['nama'] }}</span>
          @if(!$isEmpty)
            <span class="badge bg-secondary badge-count">{{ $bulan['total_tagihan'] }} tagihan</span>
          @else
            <span class="badge bg-light text-muted badge-count">Kosong</span>
          @endif
        </div>

        <div class="divider-thin"></div>

        {{-- Belum Bayar --}}
        <div class="stat-row">
          <span class="label d-flex align-items-center gap-1">
            <i class="mdi mdi-circle text-danger" style="font-size:.55rem;"></i>
            Belum Bayar
            @if($bulan['belum_bayar_count'] > 0)
              <span class="badge bg-danger badge-count">{{ $bulan['belum_bayar_count'] }}</span>
            @endif
          </span>
          <span class="value text-danger">Rp {{ number_format($bulan['belum_bayar_total']) }}</span>
        </div>

        {{-- Sudah Bayar --}}
        <div class="stat-row">
          <span class="label d-flex align-items-center gap-1">
            <i class="mdi mdi-circle text-success" style="font-size:.55rem;"></i>
            Sudah Bayar
            @if($bulan['sudah_bayar_count'] > 0)
              <span class="badge bg-success badge-count">{{ $bulan['sudah_bayar_count'] }}</span>
            @endif
          </span>
          <span class="value text-success">Rp {{ number_format($bulan['sudah_bayar_total']) }}</span>
        </div>

        <div class="divider-thin"></div>

        {{-- Grand Total Bulan --}}
        <div class="grand-total-row">
          <span class="text-muted">Total</span>
          <span class="text-dark">Rp {{ number_format($bulan['grand_total']) }}</span>
        </div>

      </div>
    </div>
  </div>
  @endforeach
</div>

@endsection
