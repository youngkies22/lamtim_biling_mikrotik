@extends('layouts/layoutMaster')

@section('title', 'Panduan Instalasi FreeRADIUS')

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">FreeRADIUS /</span> Panduan Instalasi
</h4>

<div class="alert alert-info d-flex align-items-center mb-4" role="alert">
  <i class="mdi mdi-information-outline me-2 mdi-24px"></i>
  <div>
    <strong>PENTING:</strong> Karena tabel database (nas, radcheck, dll) sudah dibuat melalui <b>Migration Laravel</b>, Anda <u>TIDAK PERLU</u> mengimpor file schema.sql secara manual. Cukup lakukan langkah aktivasi di bawah ini.
  </div>
</div>

<div class="row">
  <div class="col-md-6 mb-4">
    <div class="card h-100 border-top border-warning border-3">
      <div class="card-header bg-warning text-white">
        <h5 class="mb-0"><i class="mdi mdi-file-document-outline me-1"></i> 1. Aktifkan Modul SQL</h5>
      </div>
      <div class="card-body">
        <p class="text-muted">Buat symlink modul SQL agar FreeRADIUS bisa terhubung ke database MySQL.</p>
        <pre class="bg-dark text-success p-3 rounded" style="font-size: 0.85rem;">
# Jalankan perintah ini di terminal server:
sudo ln -s /etc/freeradius/3.0/mods-available/sql \
  /etc/freeradius/3.0/mods-enabled/</pre>
      </div>
    </div>
  </div>

  <div class="col-md-6 mb-4">
    <div class="card h-100 border-top border-info border-3">
      <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="mdi mdi-file-document-edit-outline me-1"></i> 2. Konfigurasi File Sites</h5>
      </div>
      <div class="card-body">
        <p class="text-muted">Edit file <code>/etc/freeradius/3.0/sites-available/default</code> dan <code>inner-tunnel</code>.</p>
        <pre class="bg-dark text-success p-3 rounded" style="font-size: 0.85rem;">
# Cari dan pastikan kata 'sql' AKTIF (tanpa tanda #)
# di bagian berikut:

authorize {
    ...
    sql
}
accounting {
    ...
    sql
}
session {
    ...
    sql
}
post-auth {
    ...
    sql
}</pre>
      </div>
    </div>
  </div>

  <div class="col-md-6 mb-4">
    <div class="card h-100 border-top border-primary border-3">
      <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="mdi mdi-database-cog-outline me-1"></i> 3. Setting Koneksi Database</h5>
      </div>
      <div class="card-body">
        <p class="text-muted">Edit file <code>/etc/freeradius/3.0/mods-available/sql</code></p>
        <pre class="bg-dark text-success p-3 rounded" style="font-size: 0.85rem; max-height: 300px; overflow-y: auto;">
sql {
    dialect = "mysql"
    driver = "rlm_sql_mysql"

    # Koneksi ke Database Lokal
    server = "127.0.0.1"
    port = 3306
    login = "radius"
    password = "password_radius"
    radius_db = "radius"

    # Matikan SSL (koneksi lokal)
    # ssl { ... }

    # Aktifkan pencarian NAS di DB
    read_clients = yes
    client_table = "nas"
}</pre>
      </div>
    </div>
  </div>

  <div class="col-md-6 mb-4">
    <div class="card h-100 border-top border-success border-3">
      <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="mdi mdi-play-circle-outline me-1"></i> 4. Start & Enable Service</h5>
      </div>
      <div class="card-body">
        <p class="text-muted">Jalankan dan aktifkan FreeRADIUS agar berjalan otomatis.</p>
        <pre class="bg-dark text-success p-3 rounded" style="font-size: 0.85rem;">
# Mulai FreeRADIUS:
systemctl start freeradius

# Aktifkan auto-start:
systemctl enable freeradius

# Cek status:
systemctl status freeradius</pre>
        <div class="alert alert-warning d-flex align-items-center mt-3 mb-0" role="alert">
          <i class="mdi mdi-alert-circle-outline me-2 mdi-24px"></i>
          <div>Setiap kali mengubah file konfigurasi, restart FreeRADIUS: <code>systemctl restart freeradius</code></div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 mb-4">
    <div class="card border-top border-secondary border-3">
      <div class="card-header bg-secondary text-white">
        <h5 class="mb-0"><i class="mdi mdi-database-import-outline me-1"></i> 5. Import Schema Database (Opsional - Jika Tabel Belum Ada)</h5>
      </div>
      <div class="card-body">
        <div class="alert alert-info d-flex align-items-center mb-3" role="alert">
          <i class="mdi mdi-information-outline me-2 mdi-24px"></i>
          <div>Perintah ini digunakan untuk mengimpor struktur tabel dari FreeRADIUS ke database MySQL. Akan membuat tabel: nas, radcheck, radreply, radgroupcheck, radgroupreply, radusergroup, radacct, dll.</div>
        </div>
        <pre class="bg-dark text-success p-3 rounded" style="font-size: 0.85rem;">
# Import schema FreeRADIUS:
mysql -u <span class="text-warning">[USERNAME_DB]</span> -p <span class="text-info">[NAMA_DATABASE]</span> < /etc/freeradius/3.0/mods-config/sql/main/mysql/schema.sql

# Contoh:
mysql -u <span class="text-warning">root</span> -p <span class="text-info">radius</span> < /etc/freeradius/3.0/mods-config/sql/main/mysql/schema.sql

# Setelah import, jalankan migration Laravel:
php artisan migrate</pre>
        <div class="alert alert-success d-flex align-items-center mt-3 mb-0" role="alert">
          <i class="mdi mdi-check-circle-outline me-2 mdi-24px"></i>
          <div>Setelah semua langkah selesai, cek dengan perintah: <code>radtest user password localhost 0 testing123</code></div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="card bg-dark text-white">
      <div class="card-header">
        <h5 class="mb-0"><i class="mdi mdi-checkbox-marked-circle-outline me-1"></i> Verifikasi Instalasi</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <h6 class="text-warning">Cek Service</h6>
            <pre class="bg-black text-success p-3 rounded" style="font-size: 0.85rem;">
# Status service
systemctl status freeradius

# Cek port (1812 auth, 1813 acct)
ss -tlnp | grep 1812
ss -tlnp | grep 1813</pre>
          </div>
          <div class="col-md-6">
            <h6 class="text-warning">Test Autentikasi</h6>
            <pre class="bg-black text-success p-3 rounded" style="font-size: 0.85rem;">
# Test dengan radtest (dari server lokal)
radtest testuser testpass localhost 0 testing123

# Cek log realtime
tail -f /var/log/freeradius/radius.log
tail -f /var/log/freeradius/radacct/localhost/detail-*</pre>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
