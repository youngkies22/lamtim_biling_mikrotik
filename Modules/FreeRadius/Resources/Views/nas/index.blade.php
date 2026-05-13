@extends('layouts/layoutMaster')

@section('title', 'NAS Clients')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
@endsection

@section('page-script')
<script>
  function copyToClipboard(text) {
    var elem = document.createElement("textarea");
    document.body.appendChild(elem);
    elem.value = text;
    elem.select();
    document.execCommand("copy");
    document.body.removeChild(elem);
    toastr.success('Secret berhasil disalin ke clipboard!');
  }

  function regenerateSecret(id) {
    Swal.fire({
      title: 'Generate Ulang Secret?',
      text: "Secret lama akan diganti dengan yang baru (Random). Pastikan Anda juga mengubah secret di MikroTik klien!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, Generate!',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: '{{ route("radius.nas.regenerate", ":id") }}'.replace(':id', id),
          type: 'POST',
          headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
          success: function(response) {
            if (response.status) {
              $('#secret-' + id).text(response.new_secret);
              Swal.fire('Berhasil!', response.message, 'success');
            } else {
              Swal.fire('Gagal', response.message, 'error');
            }
          },
          error: function() {
            Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
          }
        });
      }
    });
  }
</script>
@endsection

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">FreeRADIUS /</span> Daftar NAS / Clients
</h4>

<div class="alert alert-info alert-dismissible d-flex align-items-center mb-4" role="alert">
    <i class="mdi mdi-information-outline me-2 mdi-24px"></i>
    <div>
        Halaman ini menampilkan daftar router/klien yang terdaftar pada tabel <code>nas</code> di database FreeRADIUS. Hanya perangkat yang terdaftar di bawah ini yang diizinkan untuk melakukan autentikasi ke server FreeRADIUS.
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>

<div class="row">
    @forelse($nas as $item)
    <div class="col-md-4 mb-4">
        <div class="card h-100 border-start border-primary border-3">
            <div class="card-body">
                <h5 class="card-title d-flex align-items-center mb-3">
                    <i class="mdi mdi-router text-primary me-2 mdi-24px"></i>
                    <span class="fw-bold">{{ $item->shortname }}</span>
                </h5>

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-muted">IP / DNS Address</small>
                    </div>
                    <span class="fw-bold">{{ $item->nasname }}</span>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-muted">RADIUS Secret</small>
                        <div>
                            <a href="javascript:void(0)" onclick="copyToClipboard(document.getElementById('secret-{{ $item->id }}').innerText)" class="me-1" title="Copy Secret">
                                <i class="mdi mdi-content-copy text-muted"></i>
                            </a>
                            <a href="javascript:void(0)" onclick="regenerateSecret('{{ $item->id }}')" title="Regenerate">
                                <i class="mdi mdi-refresh text-danger"></i>
                            </a>
                        </div>
                    </div>
                    <code id="secret-{{ $item->id }}" class="bg-label-dark p-1 rounded">@if($item->secret){{ $item->secret }}@else<small class="text-muted">(belum diset)</small>@endif</code>
                </div>

                @if($item->description)
                <div class="mt-3 pt-2 border-top">
                    <small class="text-muted"><i class="mdi mdi-information-outline me-1"></i>{{ $item->description }}</small>
                </div>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center py-5">
        <i class="mdi mdi-router mdi-48px text-muted mb-3"></i>
        <h5 class="text-muted">Tidak ada data NAS ditemukan.</h5>
        <p class="text-muted">Silakan lakukan sinkronisasi NAS dari menu Sinkronisasi.</p>
    </div>
    @endforelse
</div>
@endsection
