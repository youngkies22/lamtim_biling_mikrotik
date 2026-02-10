<!-- Horizontal Menu -->
<aside id="layout-menu" class="layout-menu-horizontal menu-horizontal menu bg-menu-theme flex-grow-0">
  <div class="container d-flex h-100">
    <ul class="menu-inner">

      <!-- Home -->
      <li class="menu-item">
        <a href="/" class="menu-link">
          <i class="menu-icon tf-icons mdi mdi-home-outline"></i>
          <div>HOME</div>
        </a>
      </li>

      <!-- Page 2 -->
      <li class="menu-item menu-dropdown">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
          <i class="menu-icon tf-icons mdi mdi-file-document-outline"></i>
          <div>SERVER</div>
        </a>
        <ul class="menu-sub">
          <li class="menu-item menu-dropdown">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
              <i class="menu-icon tf-icons mdi mdi-circle-medium mdi-20px"></i>
              <div>Mikrotik</div>
            </a>
            <ul class="menu-sub">
              <li class="menu-item">
                <a href="{{ route('mikrotik.index') }}" class="menu-link">
                  <i class="menu-icon tf-icons mdi mdi-circle-medium mdi-20px"></i>
                  <div>Mikrotik</div>
                </a>
              </li>
              <li class="menu-item">
                <a href="{{ route('mikrotik.connection-check') }}" class="menu-link">
                  <i class="menu-icon tf-icons mdi mdi-circle-medium mdi-20px"></i>
                  <div>Cek Koneksi</div>
                </a>
              </li>
              <li class="menu-item">
                <a href="{{ route('mikrotik.pppoe-manage') }}" class="menu-link">
                  <i class="menu-icon tf-icons mdi mdi-circle-medium mdi-20px"></i>
                  <div>Kelola PPPoE</div>
                </a>
              </li>
              <li class="menu-item">
                <a href="{{ route('mikrotik.kelola-isolir') }}" class="menu-link">
                  <i class="menu-icon tf-icons mdi mdi-circle-medium mdi-20px"></i>
                  <div>Kelola Isolir</div>
                </a>
              </li>
              <li class="menu-item">
                <a href="{{ route('mikrotik.sync-customers') }}" class="menu-link">
                  <i class="menu-icon tf-icons mdi mdi-circle-medium mdi-20px"></i>
                  <div>Sinkron Pelanggan</div>
                </a>
              </li>
            </ul>
          </li>
          <li class="menu-item">
            <a href="{{ route('olt.index') }}" class="menu-link">
              <i class="menu-icon tf-icons mdi mdi-circle-medium mdi-20px"></i>
              <div>OLT</div>
            </a>
          </li>
          <li class="menu-item">
            <a href="{{ route('odc.index') }}" class="menu-link">
              <i class="menu-icon tf-icons mdi mdi-circle-medium mdi-20px"></i>
              <div>ODC</div>
            </a>
          </li>
          <li class="menu-item">
            <a href="{{ route('odp.index') }}" class="menu-link">
              <i class="menu-icon tf-icons mdi mdi-circle-medium mdi-20px"></i>
              <div>ODP</div>
            </a>
          </li>
          <li class="menu-item">
            <a href="{{ route('google-map.standalone') }}" class="menu-link" target="_blank">
              <i class="menu-icon tf-icons mdi mdi-circle-medium mdi-20px"></i>
              <div>GOOGLE MAP</div>
            </a>
          </li>
        </ul>
      </li>
      <li class="menu-item menu-dropdown">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
          <i class="menu-icon tf-icons  mdi mdi-badge-account-outline"></i>
          <div>PELANGGAN</div>
        </a>
        <ul class="menu-sub">
          <li class="menu-item">
            <a href="{{ route('user.create') }}" class="menu-link">
              <i class="menu-icon tf-icons mdi mdi-account-multiple-plus mdi-20px"></i>
              <div>ADD</div>
            </a>
          </li>
          <li class="menu-item">
            <a href="{{ route('user.index') }}" class="menu-link">
              <i class="menu-icon tf-icons mdi mdi-account-group mdi-20px"></i>
              <div>LIST</div>
            </a>
          </li>
          <li class="menu-item">
            <a href="{{ route('user.mapping') }}" class="menu-link">
              <i class="menu-icon tf-icons mdi mdi-account-key mdi-20px"></i>
              <div>MAPPING</div>
            </a>
          </li>
          <li class="menu-item">
            <a href="{{ route('user.show.mapping') }}" class="menu-link">
              <i class="menu-icon tf-icons mdi mdi-account-key mdi-20px"></i>
              <div>LIHAT MAP</div>
            </a>
          </li>
          <li class="menu-item">
            <a href="{{ route('area.index') }}" class="menu-link">
              <i class="menu-icon tf-icons mdi mdi-map-marker-outline mdi-20px"></i>
              <div>AREA</div>
            </a>
          </li>

        </ul>
      </li>
      <li class="menu-item menu-dropdown">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
          <i class="menu-icon tf-icons mdi mdi-access-point-network"></i>
          <div>INTERNET</div>
        </a>
        <ul class="menu-sub">
          <li class="menu-item">
            <a href="{{ route('kategori.index') }}" class="menu-link">
              <i class="menu-icon tf-icons mdi mdi-book-information-variant mdi-20px"></i>
              <div>KATEGORI</div>
            </a>
          </li>
          <li class="menu-item">
            <a href="{{ route('paket.index') }}" class="menu-link">
              <i class="menu-icon tf-icons mdi mdi-list-box mdi-20px"></i>
              <div>PAKET</div>
            </a>
          </li>
        </ul>
      </li>
      <li class="menu-item menu-dropdown">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
          <i class="menu-icon tf-icons mdi mdi-access-point-network"></i>
          <div>TAGIHAN</div>
        </a>
        <ul class="menu-sub">
          <li class="menu-item">
            <a href="{{ route('tagihan.index') }}" class="menu-link">
              <i class="menu-icon tf-icons mdi mdi-book-information-variant mdi-20px"></i>
              <div>Tagihan</div>
            </a>
          </li>
          <li class="menu-item">
            <a href="{{ route('tagihan.lunas') }}" class="menu-link">
              <i class="menu-icon tf-icons mdi mdi-book-information-variant mdi-20px"></i>
              <div>Tagihan Lunas</div>
            </a>
          </li>
          <li class="menu-item">
            <a href="{{ route('tagihan.generate') }}" class="menu-link">
              <i class="menu-icon tf-icons mdi mdi-cash-multiple mdi-20px"></i>
              <div>Generate Tagihan</div>
            </a>
          </li>
        </ul>
      </li>



      <!-- Login -->
      {{-- <li class="menu-item">
        <a href="/auth/login-basic" class="menu-link" target="_blank">
          <i class="menu-icon tf-icons mdi mdi-login"></i>
          <div>Login</div>
        </a>
      </li>

      <!-- Register -->
      <li class="menu-item">
        <a href="/auth/register-basic" class="menu-link" target="_blank">
          <i class="menu-icon tf-icons mdi mdi-account-outline"></i>
          <div>Register</div>
        </a>
      </li> --}}

    </ul>
  </div>
</aside>
<!--/ Horizontal Menu -->
