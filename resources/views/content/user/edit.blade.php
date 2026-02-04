@extends('layouts/layoutMaster')

@section('title', 'Add User')

@section("vendor-style")
@endsection


@section("vendor-script")

@endsection

@section('page-script')
<script>
  $(document).ready(function() {
    $('#area-select').select2({
      placeholder: "Pilih Area",
      allowClear: true,
      dropdownParent: $('#area-select').closest('.form-floating') // agar tidak overlap label
    });

    $('#form-add-user').on('submit', function (e) {
        e.preventDefault(); // Mencegah form untuk submit secara normal

        const form = this;
        const formData = new FormData(form); // Ambil data dari form
        console.log(formData);

        $.ajax({
            url: "{{ route('user.update', ['id' => $id]) }}", // Endpoint untuk update data
            method: 'POST', // Gunakan PUT request
            data: formData, // Kirimkan data form
            processData: false, // Biarkan data tetap sebagai FormData
            contentType: false, // Agar data dikirim dalam format multipart/form-data
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // CSRF token untuk keamanan
            },
            success: function (response) {
                //form.reset(); // Reset form setelah berhasil
                toastr.success(response.message || 'Data berhasil disimpan.'); // Tampilkan pesan sukses

                // Optional: reload tabel atau redirect
                // $('#table-user').DataTable().ajax.reload();
                // window.location.href = '/user'; // Jika ingin redirect
            },
            error: function (xhr) {
                let message = 'Terjadi kesalahan.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                toastr.error(message); // Tampilkan pesan error
            }
        });
    });


  });
</script>
@endsection

@section('content')
<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Form Tambah Pelanggan</h5>
    <a href="{{ url()->previous() }}" class="btn btn-sm btn-secondary">
      <i class="mdi mdi-arrow-left-bold"></i> Kembali
    </a>
  </div>
  <form id="form-add-user" class="card-body" enctype="multipart/form-data">
    @csrf
    <h6>1. Akun Pengguna</h6>
    <div class="row g-4">
      <div class="col-md-2">
        <div class="form-floating form-floating-outline">
          <input type="text" name="nama" class="form-control" autocomplete="off" placeholder="Nama Lengkap"
            value="{{ $query->name }}" required />
          <label for="nama">Nama User</label>
        </div>
      </div>
      <div class="col-md-2">
        <div class="form-floating form-floating-outline">
          <input value="{{ $query->julukan }}" type="text" name="julukan" class="form-control" autocomplete="off"
            placeholder="nama julukan" />
          <label for="julukan">Julukan</label>
        </div>
      </div>
      <div class="col-md-2">
        <div class="form-floating form-floating-outline">
          <input value="{{ $query->wa }}" type="text" name="wa" class="form-control" autocomplete="off"
            placeholder="nomor WA" required />
          <label for="nama">WA User</label>
        </div>
      </div>

      {{-- <div class="col-md-6">
        <div class="form-floating form-floating-outline">
          <input type="email" name="email" class="form-control" placeholder="Email" required />
          <label for="email">Email</label>
        </div>
      </div> --}}

      <div class="col-md-3">
        <div class="form-floating form-floating-outline">
          <input type="password" name="password" class="form-control" autocomplete="new-password"
            placeholder="Password" />
          <label for="password">Password</label>
        </div>
        <span class="text-muted">kosongkan jika tidak mau update password</span>
      </div>
      <div class="col-md-3">
        <div class="form-floating form-floating-outline">
          <select id="status-user" name="status_user" class="form-control" required>
            <option {{ $query->isActive == 1 ? 'selected' : '' }} value="1">AKTIF</option>
            <option {{ $query->isActive == 0 ? 'selected' : '' }} value="0">OFF</option>
          </select>
          <label for="area-select">Statu Pelanggan</label>
        </div>
      </div>
    </div>

    <hr class="my-4 mx-n4" />
    <h6>2. Informasi Tambahan</h6>
    <div class="row g-4">

      <div class="col-md-6">
        <div class="form-floating form-floating-outline">
          <select name="area" id="area-select" class="form-select" required>
            <option value="" disabled>Pilih Area</option>
            @foreach (Helper::getArea() as $val)
            <option value="{{ $val->id }}" {{ !empty($query->user_detail?->idArea) && $query->user_detail->idArea == $val->getRawOriginal('id') ? 'selected' : '' }}>
              {{ $val->name }}
            </option>
            @endforeach
            <!-- Tambah opsi lain jika perlu -->
          </select>
          <label for="area-select">Area</label>
        </div>
      </div>


      <div class="col-md-6">
        <div class="form-floating form-floating-outline">
          <select name="status_isolir" class="form-select" required>
            <option {{ $query->user_detail?->statusIsolir == 1 ? 'selected' : '' }} value="1">Iya</option>
            <option {{ $query->user_detail?->statusIsolir == 0 ? 'selected' : '' }} value="0">Tidak</option>
          </select>
          <label for="status_isolir">Status Isolir</label>
        </div>
      </div>

      <div class="col-md-6">
        <div class="form-floating form-floating-outline">
          <input type="date" name="tgl_daftar" class="form-control" value="{{ $query->user_detail?->tglDafatar }}" />
          <label for="tgl_daftar">Tanggal Daftar </label>
        </div>
      </div>

      <div class="col-md-6">
        <div class="form-floating form-floating-outline">
          <input type="date" name="jatuh_tempo" value="{{ $query->user_detail?->tglJatuhTempo }}" class="form-control"
            required />
          <label for="jatuh_tempo">Tanggal Jatuh Tempo</label>
        </div>
      </div>

      <div class="col-md-6">
        <div class="form-floating form-floating-outline">
          <select name="status_ppn" class="form-select">
            <option @selected($query->user_detail?->statusPpn == 1) value="1">Aktif</option>
            <option @selected($query->user_detail?->statusPpn == 0) value="0">Tidak</option>
          </select>
          <label for="status_ppn">Status PPN</label>
        </div>
      </div>

      <div class="col-md-6">
        <div class="form-floating form-floating-outline">
          <select name="status_tagihan" class="form-select">
            <option @selected($query->user_detail?->statusTagihan == 1) value="1">Aktif</option>
            <option @selected($query->user_detail?->statusTagihan == 0) value="0">Tidak</option>
          </select>
          <label for="status_tagihan">Status Tagihan</label>
        </div>
      </div>

      <div class="col-md-6">
        <div class="form-floating form-floating-outline">
          <select name="jenis_bayar" id="jenis_bayar" class="form-select" required>
            <option @selected($query->user_detail?->jenisBayar == 1) value="1">Pascabayar</option>
            <option @selected($query->user_detail?->jenisBayar == 2) value="2">Prabayar</option>
          </select>
          <label for="jenis_bayar">Jenis Bayar</label>
        </div>
      </div>

      <div class="col-md-6">
        <div class="form-floating form-floating-outline">
          <input value="{{ $query->user_detail?->googleMap }}" type="url" name="google_map_url" class="form-control"
            placeholder="https://..." />
          <label for="google_map_url">URL Google Map</label>
        </div>
      </div>

      <div class="col-md-6">
        <div class="form-floating form-floating-outline">
          <select name="jenis_kelamin" class="form-select">
            <option @selected($query->user_detail?->js == 'L') value="L">Laki-laki</option>
            <option @selected($query->user_detail?->js == 'P') value="P">Perempuan</option>
          </select>
          <label for="jenis_kelamin">Jenis Kelamin</label>
        </div>
      </div>

      {{-- <div class="col-md-6">
        <div class="form-floating form-floating-outline">
          <input type="text" name="telegram" class="form-control" />
          <label for="telegram">Telegram</label>
        </div>
      </div> --}}
      {{--
      <div class="col-md-6">
        <div class="form-floating form-floating-outline">
          <input type="file" name="foto_profile" class="form-control" accept="image/*" />
          <label for="foto_profile">Foto Profil</label>
        </div>
      </div> --}}

      <div class="col-md-6">
        @php
        $listIdentitas = [
        'KTP' => 'KTP',
        'KK' => 'Kartu Keluarga',
        'SIM' => 'SIM',
        'PASPOR' => 'Paspor',
        'KARTU_PELAJAR' => 'Kartu Pelajar',
        'KARTU_MAHASISWA' => 'Kartu Mahasiswa',
        'NPWP' => 'NPWP',
        'BPJS' => 'BPJS',
        'LAINNYA' => 'Lainnya',
        ];
        @endphp
        <div class="form-floating form-floating-outline">
          <select name="jenis_identitas" class="form-select">
            @foreach ($listIdentitas as $value => $label)
            <option value="{{ $value }}" @selected($query->user_detail?->identitas ==$value) >
              {{ $label }}
            </option>
            @endforeach
          </select>
          <label for="jenis_identitas">Jenis Identitas</label>
        </div>
      </div>

      <div class="col-md-6">
        <div class="form-floating form-floating-outline">
          <input value="{{ $query->user_detail?->noIdentitas }}" type="text" name="no_identitas" class="form-control"
            placeholder="Nomor Identitas" />
          <label for="no_identitas">Nomor Identitas</label>
        </div>
      </div>

      <div class="col-12">
        <div class="form-floating form-floating-outline">
          <textarea name="alamat" class="form-control" style="height: 100px;"
            placeholder="Alamat lengkap">{{ $query->user_detail?->alamat }}</textarea>
          <label for="alamat">Alamat</label>
        </div>
      </div>
      <div class="col-12">
        <div class="form-floating form-floating-outline">
          <textarea name="keterangan" class="form-control" style="height: 100px;"
            placeholder="Keterangan Tambahan Jika ada ">{{ $query->user_detail?->keterangan }}</textarea>
          <label for="alamat">Keterangan Tambahan</label>
        </div>
      </div>
    </div>

    <div class="pt-4">
      <button type="submit" class="btn btn-primary me-sm-3 me-1">Update</button>
    </div>
  </form>
</div>

@endsection