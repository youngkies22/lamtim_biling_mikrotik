@php
$width = $width ?? '25';
$withbg = $withbg ?? '#666cff';
@endphp
<span>
  <svg width="{{ $width }}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M12 21L15.6 16.2C14.6 15.45 13.35 15 12 15C10.65 15 9.4 15.45 8.4 16.2L12 21Z" fill="{{ $withbg }}"/>
    <path d="M2.1 6.6L4.2 8.7C6.3 6.6 9.05 5.4 12 5.4C14.95 5.4 17.7 6.6 19.8 8.7L21.9 6.6C19.2 3.9 15.75 2.4 12 2.4C8.25 2.4 4.8 3.9 2.1 6.6Z" fill="{{ $withbg }}" fill-opacity="0.5"/>
    <path d="M5.3 9.8L7.4 11.9C8.65 10.65 10.25 10 12 10C13.75 10 15.35 10.65 16.6 11.9L18.7 9.8C16.85 7.95 14.5 7 12 7C9.5 7 7.15 7.95 5.3 9.8Z" fill="{{ $withbg }}" fill-opacity="0.75"/>
  </svg>
</span>
