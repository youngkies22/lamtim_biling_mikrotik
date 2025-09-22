<?php


return [
  /*
    |--------------------------------------------------------------------------
    | Konfigurasi Billing Mikrotik
    |--------------------------------------------------------------------------
    |
    | File ini berisi semua konfigurasi yang berkaitan dengan
    | sistem billing Mikrotik yang diambil dari file .env
    |
    */
  'ppn' => env('PPN', 11),
  // Konfigurasi Aplikasi
  // 'app_name' => env('APP_NAME', 'Lamtim Billing Mikrotik'),
  // 'app_version' => env('APP_VERSION', '1.0.0'),
  // 'app_owner' => env('APP_OWNER', 'Lamtim'),
  // 'app_contact' => env('APP_CONTACT', 'admin@lamtim.com'),

  // // Konfigurasi Mikrotik
  // 'mikrotik_host' => env('MIKROTIK_HOST', '127.0.0.1'),
  // 'mikrotik_user' => env('MIKROTIK_USER', 'admin'),
  // 'mikrotik_password' => env('MIKROTIK_PASSWORD', ''),
  // 'mikrotik_port' => env('MIKROTIK_PORT', 8728),
  // 'mikrotik_api_port' => env('MIKROTIK_API_PORT', 8729),
  // 'mikrotik_ssl' => env('MIKROTIK_SSL', false),

  // // Konfigurasi Tagihan
  // 'ppn_percentage' => env('PPN_PERCENTAGE', 11), // dalam persen
  // 'default_due_days' => env('DEFAULT_DUE_DAYS', 30), // Jatuh tempo dalam hari
  // 'default_active_days' => env('DEFAULT_ACTIVE_DAYS', 30), // Masa aktif dalam hari

  // // Konfigurasi Notifikasi
  // 'enable_email_notification' => env('ENABLE_EMAIL_NOTIFICATION', false),
  // 'enable_sms_notification' => env('ENABLE_SMS_NOTIFICATION', false),
  // 'enable_whatsapp_notification' => env('ENABLE_WHATSAPP_NOTIFICATION', false),

  // // Konfigurasi WhatsApp
  // 'whatsapp_api_url' => env('WHATSAPP_API_URL', ''),
  // 'whatsapp_api_key' => env('WHATSAPP_API_KEY', ''),
  // 'whatsapp_admin_number' => env('WHATSAPP_ADMIN_NUMBER', ''),

  // // Konfigurasi SMS
  // 'sms_api_url' => env('SMS_API_URL', ''),
  // 'sms_api_key' => env('SMS_API_KEY', ''),

  // // Konfigurasi Pembayaran
  // 'payment_gateway' => env('PAYMENT_GATEWAY', 'manual'), // manual, midtrans, duitku, dll
  // 'payment_gateway_url' => env('PAYMENT_GATEWAY_URL', ''),
  // 'payment_gateway_key' => env('PAYMENT_GATEWAY_KEY', ''),

  // // Konfigurasi Path
  // 'invoice_path' => env('INVOICE_PATH', 'invoices'),
  // 'profile_photo_path' => env('PROFILE_PHOTO_PATH', 'profile-photos'),

  // // Konfigurasi Lain
  // 'currency_symbol' => env('CURRENCY_SYMBOL', 'Rp'),
  // 'pagination_limit' => env('PAGINATION_LIMIT', 10),
];
