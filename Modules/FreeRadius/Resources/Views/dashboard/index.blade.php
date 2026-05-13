@extends('layouts/layoutMaster')

@section('title', 'Dashboard FreeRADIUS')

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">FreeRADIUS /</span> Dashboard
</h4>

{{-- Info Banner --}}
<div class="alert alert-success d-flex align-items-center mb-4" role="alert">
    <i class="mdi mdi-check-circle-outline me-2 mdi-24px"></i>
    <div>
        <b>Sistem Integrasi Billing & RADIUS Aktif</b><br>
        <small class="text-muted">Setiap perubahan pada Pelanggan (Mapping, Status Aktif, Isolir, maupun Hapus Data) akan langsung diteruskan ke server FreeRADIUS secara <b>Real-Time</b>.</small>
    </div>
</div>

{{-- Stats Widgets --}}
<div class="row">
    <div class="col-sm-6 col-xl-3 mb-4">
        <div class="card h-100 bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 text-white-alpha-75">NAS Clients</h6>
                        <h2 class="mb-0 mt-2">{{ $stats['nas_count'] }}</h2>
                        <small class="text-white-alpha-50">MikroTik Terdaftar</small>
                    </div>
                    <i class="mdi mdi-router mdi-48px opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3 mb-4">
        <div class="card h-100 bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 text-white-alpha-75">Groups</h6>
                        <h2 class="mb-0 mt-2">{{ $stats['group_count'] }}</h2>
                        <small class="text-white-alpha-50">Paket / Profile</small>
                    </div>
                    <i class="mdi mdi-package-variant-closed mdi-48px opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3 mb-4">
        <div class="card h-100 bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 text-white-alpha-75">RADIUS Users</h6>
                        <h2 class="mb-0 mt-2">{{ $stats['user_count'] }}</h2>
                        <small class="text-white-alpha-50">Total Terdaftar</small>
                    </div>
                    <i class="mdi mdi-account-group mdi-48px opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3 mb-4">
        <div class="card h-100 bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 text-white-alpha-75">Online</h6>
                        <h2 class="mb-0 mt-2">{{ $stats['online_count'] }}</h2>
                        <small class="text-white-alpha-50">User Aktif</small>
                    </div>
                    <i class="mdi mdi-wifi mdi-48px opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Quick Navigation --}}
<div class="row">
    <div class="col-md-8 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="mdi mdi-navigation-outline me-1"></i> Navigasi Cepat</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <a href="{{ route('radius.nas.index') }}" class="btn btn-outline-primary w-100 p-3 d-flex flex-column align-items-center">
                            <i class="mdi mdi-router mdi-36px mb-1"></i>
                            <span>NAS List</span>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('radius.profile.index') }}" class="btn btn-outline-warning w-100 p-3 d-flex flex-column align-items-center">
                            <i class="mdi mdi-package-variant-closed mdi-36px mb-1"></i>
                            <span>Paket / Profile</span>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('radius.user.index') }}" class="btn btn-outline-success w-100 p-3 d-flex flex-column align-items-center">
                            <i class="mdi mdi-account-group mdi-36px mb-1"></i>
                            <span>Pelanggan RADIUS</span>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('radius.sync.index') }}" class="btn btn-outline-info w-100 p-3 d-flex flex-column align-items-center">
                            <i class="mdi mdi-sync mdi-36px mb-1"></i>
                            <span>Sinkronisasi</span>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('radius.monitor.index') }}" class="btn btn-outline-secondary w-100 p-3 d-flex flex-column align-items-center">
                            <i class="mdi mdi-wifi mdi-36px mb-1"></i>
                            <span>Monitoring</span>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('radius.monitor.history') }}" class="btn btn-outline-dark w-100 p-3 d-flex flex-column align-items-center">
                            <i class="mdi mdi-history mdi-36px mb-1"></i>
                            <span>Riwayat Sesi</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="mdi mdi-information-outline me-1"></i> Status RADIUS</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-3 d-flex align-items-center">
                        <i class="mdi mdi-check-circle text-success me-2 mdi-24px"></i>
                        <div>
                            <small class="text-muted">Server</small><br>
                            <b>Aktif</b>
                        </div>
                    </li>
                    <li class="mb-3 d-flex align-items-center">
                        <i class="mdi mdi-check-circle text-success me-2 mdi-24px"></i>
                        <div>
                            <small class="text-muted">Database</small><br>
                            <b>Terhubung</b>
                        </div>
                    </li>
                    <li class="d-flex align-items-center">
                        <i class="mdi mdi-information text-info me-2 mdi-24px"></i>
                        <div>
                            <small class="text-muted">Interim Update</small><br>
                            <b>600 detik</b>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
