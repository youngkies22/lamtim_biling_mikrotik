@php
$containerFooter = ($configData['contentLayout'] === 'compact') ? 'container-xxl' : 'container-fluid';
@endphp

<!-- Footer-->
<footer class="content-footer footer bg-footer-theme">
  <div class="{{ $containerFooter }}">
    <div class="footer-container d-flex align-items-center justify-content-between py-3 flex-md-row flex-column">
      <div class="mb-2 mb-md-0">
        &copy; <script>document.write(new Date().getFullYear())</script>
        <span class="fw-medium">Lamtim Billing</span> — Sistem Manajemen ISP
      </div>
    </div>
  </div>
</footer>
<!--/ Footer-->
