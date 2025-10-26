{{-- filepath: e:\1.WEB_SERVER\1.LARAVELKU\1.projek\lamtim_biling_mikrotik\resources\views\content\pages\home.blade.php
--}}
@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Dashboard - Lamtim Billing')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />
@endsection

@section('vendor-script')
<script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
@endsection

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function() {
  // Revenue Chart
  const revenueChartEl = document.querySelector('#revenueChart');
  if (revenueChartEl) {
    const revenueData = @json($monthlyRevenue);
    const months = revenueData.map(item => item.month);
    const revenues = revenueData.map(item => item.revenue);

    const revenueChart = new ApexCharts(revenueChartEl, {
      chart: {
        height: 300,
        type: 'line',
        toolbar: { show: false }
      },
      series: [{
        name: 'Revenue',
        data: revenues
      }],
      colors: ['#696cff'],
      stroke: {
        curve: 'smooth',
        width: 3
      },
      xaxis: {
        categories: months
      },
      yaxis: {
        labels: {
          formatter: function (val) {
            return 'Rp ' + val.toLocaleString('id-ID');
          }
        }
      },
      tooltip: {
        y: {
          formatter: function (val) {
            return 'Rp ' + val.toLocaleString('id-ID');
          }
        }
      },
      grid: {
        strokeDashArray: 4
      }
    });
    revenueChart.render();
  }

  // Bills Status Donut Chart
  const billsChartEl = document.querySelector('#billsChart');
  if (billsChartEl) {
    const billsChart = new ApexCharts(billsChartEl, {
      chart: {
        height: 250,
        type: 'donut'
      },
      series: [{{ $stats['paid_bills'] }}, {{ $stats['unpaid_bills'] }}],
      labels: ['Terbayar', 'Belum Bayar'],
      colors: ['#28c76f', '#ff4757'],
      legend: {
        position: 'bottom'
      },
      plotOptions: {
        pie: {
          donut: {
            size: '70%'
          }
        }
      }
    });
    billsChart.render();
  }
});
</script>
@endsection

@section('content')
<!-- Dashboard Header -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card bg-primary text-white">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-md-8">
            <h4 class="card-title text-white mb-2">
              <i class="mdi mdi-view-dashboard me-2"></i>Dashboard Lamtim Billing
            </h4>
            <p class="card-text mb-0">Selamat datang di sistem billing mikrotik RT/RW Net</p>
          </div>
          <div class="col-md-4 text-end">
            <h6 class="text-white-50 mb-1">{{ Carbon\Carbon::now()->format('d F Y') }}</h6>
            <small class="text-white-50">{{ Carbon\Carbon::now()->format('H:i') }} WIB</small>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
  <!-- Total Users -->
  <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
    <div class="card h-100">
      <div class="card-body d-flex align-items-center">
        <div class="flex-shrink-0 me-3">
          <div class="bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center"
            style="width: 60px; height: 60px;">
            <i class="mdi mdi-account-group mdi-24px"></i>
          </div>
        </div>
        <div class="flex-grow-1">
          <h5 class="mb-1 text-dark fw-bold">{{ number_format($stats['total_users']) }}</h5>
          <small class="text-muted">Total Pelanggan</small>
        </div>
      </div>
    </div>
  </div>

  <!-- Active Mikrotik -->
  <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
    <div class="card h-100">
      <div class="card-body d-flex align-items-center">
        <div class="flex-shrink-0 me-3">
          <div class="bg-success bg-opacity-10 text-success rounded-3 d-flex align-items-center justify-content-center"
            style="width: 60px; height: 60px;">
            <i class="mdi mdi-router-wireless mdi-24px"></i>
          </div>
        </div>
        <div class="flex-grow-1">
          <h5 class="mb-1 text-dark fw-bold">{{ $stats['active_mikrotik'] }}/{{ $stats['total_mikrotik'] }}</h5>
          <small class="text-muted">Server Aktif</small>
        </div>
      </div>
    </div>
  </div>

  <!-- Monthly Revenue -->
  <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
    <div class="card h-100">
      <div class="card-body d-flex align-items-center">
        <div class="flex-shrink-0 me-3">
          <div class="bg-info bg-opacity-10 text-info rounded-3 d-flex align-items-center justify-content-center"
            style="width: 60px; height: 60px;">
            <i class="mdi mdi-cash-multiple mdi-24px"></i>
          </div>
        </div>
        <div class="flex-grow-1">
          <h5 class="mb-1 text-dark fw-bold">Rp {{ number_format($stats['monthly_revenue']) }}</h5>
          <small class="text-muted">Pendapatan Bulan Ini</small>
        </div>
      </div>
    </div>
  </div>

  <!-- Pending Revenue -->
  <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
    <div class="card h-100">
      <div class="card-body d-flex align-items-center">
        <div class="flex-shrink-0 me-3">
          <div class="bg-warning bg-opacity-10 text-warning rounded-3 d-flex align-items-center justify-content-center"
            style="width: 60px; height: 60px;">
            <i class="mdi mdi-clock-alert-outline mdi-24px"></i>
          </div>
        </div>
        <div class="flex-grow-1">
          <h5 class="mb-1 text-dark fw-bold">Rp {{ number_format($stats['pending_revenue']) }}</h5>
          <small class="text-muted">Tagihan Tertunda</small>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Charts and Recent Activities -->
<div class="row">
  <!-- Revenue Chart -->
  <div class="col-lg-8 col-md-12 mb-4">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div>
          <h5 class="card-title mb-0">Trend Pendapatan (6 Bulan Terakhir)</h5>
          <small class="text-muted">Grafik pendapatan bulanan yang sudah terbayar</small>
        </div>
        <div class="dropdown">
          <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
            <i class="mdi mdi-calendar me-1"></i> 6 Bulan
          </button>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="javascript:void(0);">3 Bulan</a></li>
            <li><a class="dropdown-item" href="javascript:void(0);">6 Bulan</a></li>
            <li><a class="dropdown-item" href="javascript:void(0);">1 Tahun</a></li>
          </ul>
        </div>
      </div>
      <div class="card-body">
        <div id="revenueChart"></div>
      </div>
    </div>
  </div>

  <!-- Bills Status & Quick Stats -->
  <div class="col-lg-4 col-md-12 mb-4">
    <!-- Bills Status Chart -->
    <div class="card mb-3">
      <div class="card-header">
        <h5 class="card-title mb-0">Status Tagihan Bulan Ini</h5>
        <small class="text-muted">{{ Carbon\Carbon::now()->format('F Y') }}</small>
      </div>
      <div class="card-body">
        <div id="billsChart"></div>
        <div class="row text-center mt-3">
          <div class="col-6">
            <h6 class="text-success fw-bold">{{ $stats['paid_bills'] }}</h6>
            <small class="text-muted">Terbayar</small>
          </div>
          <div class="col-6">
            <h6 class="text-danger fw-bold">{{ $stats['unpaid_bills'] }}</h6>
            <small class="text-muted">Belum Bayar</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">Statistik Cepat</h5>
      </div>
      <div class="card-body">
        <div class="d-flex align-items-center mb-3">
          <div class="flex-shrink-0 me-3">
            <div
              class="bg-primary bg-opacity-10 text-primary rounded-2 d-flex align-items-center justify-content-center"
              style="width: 40px; height: 40px;">
              <i class="mdi mdi-package-variant mdi-20px"></i>
            </div>
          </div>
          <div class="flex-grow-1">
            <h6 class="mb-0">{{ $stats['total_packages'] }}</h6>
            <small class="text-muted">Total Paket</small>
          </div>
        </div>

        <div class="d-flex align-items-center mb-3">
          <div class="flex-shrink-0 me-3">
            <div
              class="bg-success bg-opacity-10 text-success rounded-2 d-flex align-items-center justify-content-center"
              style="width: 40px; height: 40px;">
              <i class="mdi mdi-file-document-multiple mdi-20px"></i>
            </div>
          </div>
          <div class="flex-grow-1">
            <h6 class="mb-0">{{ $stats['monthly_bills'] }}</h6>
            <small class="text-muted">Total Tagihan Bulan Ini</small>
          </div>
        </div>

        <div class="d-flex align-items-center">
          <div class="flex-shrink-0 me-3">
            <div class="bg-info bg-opacity-10 text-info rounded-2 d-flex align-items-center justify-content-center"
              style="width: 40px; height: 40px;">
              <i class="mdi mdi-percent mdi-20px"></i>
            </div>
          </div>
          <div class="flex-grow-1">
            <h6 class="mb-0">
              @if($stats['monthly_bills'] > 0)
              {{ number_format(($stats['paid_bills'] / $stats['monthly_bills']) * 100, 1) }}%
              @else
              0%
              @endif
            </h6>
            <small class="text-muted">Tingkat Pembayaran</small>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Recent Transactions -->
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div>
          <h5 class="card-title mb-0">
            <i class="mdi mdi-history me-2"></i>Transaksi Terbaru
          </h5>
          <small class="text-muted">5 pembayaran terakhir yang berhasil</small>
        </div>
        <a href="#" class="btn btn-sm btn-outline-primary">
          <i class="mdi mdi-eye me-1"></i>Lihat Semua
        </a>
      </div>
      <div class="card-body">
        @if($recent_transactions->count() > 0)
        <div class="table-responsive">
          <table class="table table-hover">
            <thead class="table-light">
              <tr>
                <th>No. Tagihan</th>
                <th>Pelanggan</th>
                <th>Paket</th>
                <th>Total</th>
                <th>Tanggal Bayar</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @foreach($recent_transactions as $transaction)
              <tr>
                <td>
                  <span class="fw-bold">#{{ $transaction->noTagihan }}</span>
                </td>
                <td>
                  <div class="d-flex align-items-center">
                    <div
                      class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-2"
                      style="width: 32px; height: 32px;">
                      <i class="mdi mdi-account mdi-16px"></i>
                    </div>
                    <div>
                      <div class="fw-medium">{{ $transaction->user->name ?? 'N/A' }}</div>
                      <small class="text-muted">{{ Carbon\Carbon::parse($transaction->tglBayar)->format('M Y')
                        }}</small>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="badge bg-info">{{ $transaction->paket->nama ?? 'N/A' }}</span>
                </td>
                <td>
                  <span class="fw-bold text-success">Rp {{ number_format($transaction->total) }}</span>
                </td>
                <td>
                  <span class="text-muted">{{ Carbon\Carbon::parse($transaction->tglBayar)->format('d M Y H:i')
                    }}</span>
                </td>
                <td>
                  <span class="badge bg-success">
                    <i class="mdi mdi-check me-1"></i>Lunas
                  </span>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @else
        <div class="text-center py-4">
          <div class="mb-3">
            <i class="mdi mdi-file-document-outline mdi-48px text-muted"></i>
          </div>
          <h6 class="text-muted">Belum ada transaksi</h6>
          <p class="text-muted mb-0">Transaksi yang berhasil akan ditampilkan di sini</p>
        </div>
        @endif
      </div>
    </div>
  </div>
</div>

<!-- Quick Action Cards -->
<div class="row mt-4">
  <div class="col-lg-3 col-md-6 mb-3">
    <div class="card bg-primary text-white h-100">
      <div class="card-body text-center">
        <i class="mdi mdi-account-plus mdi-36px mb-3"></i>
        <h6 class="card-title text-white">Tambah Pelanggan</h6>
        <p class="card-text small">Daftarkan pelanggan baru</p>
        <a href="#" class="btn btn-light btn-sm">
          <i class="mdi mdi-plus me-1"></i>Tambah
        </a>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 mb-3">
    <div class="card bg-success text-white h-100">
      <div class="card-body text-center">
        <i class="mdi mdi-cash-multiple mdi-36px mb-3"></i>
        <h6 class="card-title text-white">Generate Tagihan</h6>
        <p class="card-text small">Buat tagihan bulanan</p>
        <a href="#" class="btn btn-light btn-sm">
          <i class="mdi mdi-file-plus me-1"></i>Generate
        </a>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 mb-3">
    <div class="card bg-info text-white h-100">
      <div class="card-body text-center">
        <i class="mdi mdi-router-wireless mdi-36px mb-3"></i>
        <h6 class="card-title text-white">Monitor Server</h6>
        <p class="card-text small">Kelola server mikrotik</p>
        <a href="{{ route('mikrotik.index') }}" class="btn btn-light btn-sm">
          <i class="mdi mdi-eye me-1"></i>Monitor
        </a>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 mb-3">
    <div class="card bg-warning text-white h-100">
      <div class="card-body text-center">
        <i class="mdi mdi-chart-line mdi-36px mb-3"></i>
        <h6 class="card-title text-white">Laporan</h6>
        <p class="card-text small">Lihat laporan keuangan</p>
        <a href="#" class="btn btn-light btn-sm">
          <i class="mdi mdi-file-chart me-1"></i>Laporan
        </a>
      </div>
    </div>
  </div>
</div>
@endsection
