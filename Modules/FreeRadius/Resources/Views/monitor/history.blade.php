@extends('layouts/layoutMaster')

@section('title', 'Riwayat Sesi')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
@endsection

@section('page-script')
<script>
  $(document).ready(function() {
    // Client-side search filter
    $('#searchHistory').on('keyup', function() {
      var value = $(this).val().toLowerCase();
      $('.history-item').each(function() {
        var text = $(this).text().toLowerCase();
        $(this).toggle(text.indexOf(value) > -1);
      });
    });
  });
</script>
@endsection

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">FreeRADIUS /</span> Riwayat Sesi
</h4>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <h5 class="mb-0"><i class="mdi mdi-history me-1"></i> Session History</h5>
        <div class="input-group" style="max-width: 250px;">
            <span class="input-group-text"><i class="mdi mdi-magnify"></i></span>
            <input type="text" id="searchHistory" class="form-control" placeholder="Cari session...">
        </div>
    </div>
    <div class="card-datatable table-responsive">
        <table class="table table-hover border-top">
            <thead class="table-light">
                <tr>
                    <th>Username</th>
                    <th>IP Address</th>
                    <th>Start Time</th>
                    <th>Stop Time</th>
                    <th>Total Time</th>
                    <th>Upload</th>
                    <th>Download</th>
                    <th>Cause</th>
                </tr>
            </thead>
            <tbody>
                @forelse($history as $session)
                <tr class="history-item">
                    <td><b>{{ $session->username }}</b></td>
                    <td><code>{{ $session->framedipaddress }}</code></td>
                    <td><small>{{ $session->acctstarttime }}</small></td>
                    <td><small>{{ $session->acctstoptime }}</small></td>
                    <td>
                        @php
                            $mins = $session->acctsessiontime ? round($session->acctsessiontime / 60, 1) : 0;
                            echo $mins . ' Min';
                        @endphp
                    </td>
                    <td>
                        @php
                            $u = $session->acctinputoctets ?? 0;
                            echo $u > 0 ? round($u / 1024 / 1024, 2) . ' MB' : '0 B';
                        @endphp
                    </td>
                    <td>
                        @php
                            $d = $session->acctoutputoctets ?? 0;
                            echo $d > 0 ? round($d / 1024 / 1024, 2) . ' MB' : '0 B';
                        @endphp
                    </td>
                    <td><small class="text-muted">{{ $session->acctterminatecause ?? '-' }}</small></td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">Belum ada riwayat sesi.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($history->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">
            Menampilkan {{ $history->firstItem() }} - {{ $history->lastItem() }} dari {{ $history->total() }} data
        </small>
        {{ $history->links() }}
    </div>
    @endif
</div>
@endsection
