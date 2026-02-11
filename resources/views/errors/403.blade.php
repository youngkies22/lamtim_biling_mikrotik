<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>403 - Akses Ditolak</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Public Sans', -apple-system, BlinkMacSystemFont, sans-serif;
      background: #f5f5f9;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      color: #566a7f;
    }
    .container {
      text-align: center;
      padding: 40px;
      max-width: 460px;
    }
    .icon-wrapper {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      background: #fff1f1;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 24px;
    }
    .icon-wrapper i {
      font-size: 48px;
      color: #ff3e1d;
    }
    h1 {
      font-size: 72px;
      font-weight: 700;
      color: #ff3e1d;
      line-height: 1;
      margin-bottom: 8px;
    }
    h2 {
      font-size: 22px;
      font-weight: 600;
      color: #566a7f;
      margin-bottom: 12px;
    }
    p {
      font-size: 14px;
      color: #697a8d;
      margin-bottom: 28px;
      line-height: 1.6;
    }
    .btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 10px 24px;
      border: none;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
      text-decoration: none;
      cursor: pointer;
      transition: all 0.2s;
    }
    .btn-primary {
      background: #696cff;
      color: #fff;
    }
    .btn-primary:hover {
      background: #5f61e6;
    }
    .btn-secondary {
      background: #e7e7ff;
      color: #696cff;
      margin-left: 8px;
    }
    .btn-secondary:hover {
      background: #d7d7ff;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="icon-wrapper">
      <i class="mdi mdi-shield-lock-outline"></i>
    </div>
    <h1>403</h1>
    <h2>Akses Ditolak</h2>
    <p>Anda tidak memiliki izin untuk mengakses halaman ini. Silakan hubungi administrator jika Anda merasa ini adalah kesalahan.</p>
    <a href="/" class="btn btn-primary"><i class="mdi mdi-home-outline"></i> Kembali ke Home</a>
    <a href="javascript:history.back()" class="btn btn-secondary"><i class="mdi mdi-arrow-left"></i> Kembali</a>
  </div>
</body>
</html>
