@extends('layouts/layoutMaster')

@section('title', 'Foto Pelanggan')

@section("vendor-style")
<style>
  .foto-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
  }
  
  .foto-card {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }
  
  .foto-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.2);
  }
  
  .foto-card img {
    width: 100%;
    height: 250px;
    object-fit: cover;
    cursor: pointer;
  }
  
  .foto-info {
    padding: 1rem;
    background: white;
  }
  
  .foto-info .foto-nama {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #333;
  }
  
  .foto-info .foto-meta {
    font-size: 0.875rem;
    color: #6c757d;
  }
  
  .empty-state {
    text-align: center;
    padding: 3rem;
    color: #6c757d;
  }
  
  .empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.5;
  }
</style>
@endsection

@section("vendor-script")
@endsection

@section('page-script')
<script>
  $(document).ready(function() {
    // Lightbox untuk melihat foto
    $('.foto-img').on('click', function() {
      const imgSrc = $(this).attr('src');
      const imgAlt = $(this).attr('alt');
      
      // Buat modal lightbox
      const lightbox = `
        <div class="modal fade" id="lightboxModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-transparent border-0">
              <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close" style="z-index: 1051;"></button>
              <img src="${imgSrc}" alt="${imgAlt}" class="img-fluid rounded">
            </div>
          </div>
        </div>
      `;
      
      $('body').append(lightbox);
      $('#lightboxModal').modal('show');
      
      $('#lightboxModal').on('hidden.bs.modal', function() {
        $(this).remove();
      });
    });

    // Delete foto
    $(document).on('click', '.btn-delete-foto', function() {
      const fotoId = $(this).data('id');
      const fotoCard = $(this).closest('.foto-card');
      
      Swal.fire({
        title: "Yakin ingin menghapus?",
        text: "Foto yang dihapus tidak dapat dikembalikan!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "YA, HAPUS!",
        cancelButtonText: "BATAL",
        buttonsStyling: false,
        customClass: {
          confirmButton: "btn btn-primary me-2",
          cancelButton: "btn btn-outline-danger"
        }
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: "{{ route('foto.destroy', ':id') }}".replace(':id', fotoId),
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
              toastr.success(response.message || 'Foto berhasil dihapus!');
              fotoCard.fadeOut(300, function() {
                $(this).remove();
                // Cek jika tidak ada foto lagi, reload halaman
                if ($('.foto-card').length === 0) {
                  setTimeout(() => {
                    window.location.href = "{{ route('user.index') }}";
                  }, 1000);
                }
              });
            },
            error: function(xhr) {
              let message = 'Gagal menghapus foto.';
              if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
              }
              toastr.error(message);
            }
          });
        }
      });
    });
  });
</script>
@endsection

@section('content')

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">
      <i class="mdi mdi-camera me-2"></i>Foto Pelanggan: {{ $query->name }}
    </h5>
    <div>
      <button class="btn btn-primary btn-upload-foto" data-id="{{ $id }}">
        <i class="mdi mdi-upload me-1"></i> Upload Foto
      </button>
      <a href="{{ route('user.index') }}" class="btn btn-outline-secondary">
        <i class="mdi mdi-arrow-left me-1"></i> Kembali
      </a>
    </div>
  </div>
  <div class="card-body">
    @if($query->fotos && $query->fotos->count() > 0)
      <div class="foto-grid">
        @foreach($query->fotos as $foto)
          <div class="foto-card">
            <img src="{{ Storage::url($foto->foto) }}" alt="{{ basename($foto->foto) }}" class="foto-img">
            <div class="foto-info">
              <div class="foto-nama">{{ basename($foto->foto) }}</div>
              <div class="foto-meta">
                <div><i class="mdi mdi-file-outline me-1"></i>{{ strtoupper($foto->extensi ?? 'N/A') }}</div>
                @if($foto->ukuran)
                <div><i class="mdi mdi-weight me-1"></i>{{ $foto->ukuran }}</div>
                @endif
                @if($foto->created_at)
                <div><i class="mdi mdi-calendar me-1"></i>{{ $foto->created_at->format('d-m-Y H:i') }}</div>
                @endif
              </div>
              @if(auth()->user()->hasRole(1,2,4))
              <div class="mt-2">
                <button class="btn btn-sm btn-danger btn-delete-foto" data-id="{{ encrypt($foto->id) }}">
                  <i class="mdi mdi-delete"></i> Hapus
                </button>
              </div>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    @else
      <div class="empty-state">
        <i class="mdi mdi-camera-off"></i>
        <h5>Belum ada foto</h5>
        <p>Klik tombol "Upload Foto" untuk menambahkan foto pelanggan.</p>
      </div>
    @endif
  </div>
</div>

<!-- Modal Upload Foto -->
<div class="modal fade" id="uploadFotoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Upload Foto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="formUploadFoto">
          <div class="mb-3">
            <label for="fotos" class="form-label">Pilih Foto (Bisa lebih dari satu)</label>
            <input type="file" class="form-control" id="fotos" name="fotos[]" multiple accept="image/*">
            <small class="text-muted">Format: JPG, PNG, GIF. Maksimal 5MB per file.</small>
          </div>
          <button type="submit" class="btn btn-primary">
            <i class="mdi mdi-upload"></i> Upload Foto
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  let currentUserId = "{{ $id }}";
  
  $(document).on('click', '.btn-upload-foto', function() {
    currentUserId = $(this).data('id') || currentUserId;
    $('#uploadFotoModal').modal('show');
    $('#fotos').val('');
  });

  $('#formUploadFoto').on('submit', function(e) {
    e.preventDefault();
    
    if (!$('#fotos').val()) {
      toastr.error('Pilih foto terlebih dahulu!');
      return;
    }

    const formData = new FormData();
    formData.append('idUser', currentUserId);
    
    const files = $('#fotos')[0].files;
    for (let i = 0; i < files.length; i++) {
      formData.append('fotos[]', files[i]);
    }

    $.ajax({
      url: "{{ route('foto.store') }}",
      method: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function(response) {
        toastr.success(response.message || 'Foto berhasil diupload!');
        $('#fotos').val('');
        $('#uploadFotoModal').modal('hide');
        setTimeout(() => {
          location.reload();
        }, 500);
      },
      error: function(xhr) {
        let message = 'Terjadi kesalahan saat upload foto.';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          message = xhr.responseJSON.message;
        }
        toastr.error(message);
      }
    });
  });
</script>

@endsection
