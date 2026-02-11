@extends('layouts/contentNavbarLayout')

@section('title', 'Network Topology Map')

@section('vendor-style')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
@endsection

@section('page-style')
<style>
  html, body {
    width: 100% !important;
    height: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    overflow: hidden !important;
  }

  .layout-navbar, .layout-menu, .layout-footer, .layout-overlay,
  nav.layout-navbar, aside.layout-menu {
    display: none !important;
    visibility: hidden !important;
    pointer-events: none !important;
  }

  .layout-wrapper, .layout-page, .content-wrapper,
  .container-xxl, .container-fluid {
    padding: 0 !important;
    margin: 0 !important;
    max-width: 100% !important;
    width: 100% !important;
  }

  .template-customizer, .layout-customizer, .customizer-toggle, .buy-now,
  #template-customizer, .template-customizer-open-btn, .template-customizer-toggler,
  [class*="customizer"], [id*="customizer"] {
    display: none !important;
    opacity: 0 !important;
    visibility: hidden !important;
    pointer-events: none !important;
    width: 0 !important;
    height: 0 !important;
  }

  #map-container {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
  }

  #map { width: 100%; height: 100%; }

  /* ========== FILTER PANEL ========== */
  .filter-panel {
    position: fixed;
    top: 12px;
    right: 12px;
    z-index: 1000;
    display: flex;
    gap: 6px;
    align-items: flex-start;
  }
  .filter-btn {
    padding: 8px 16px;
    border: none;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    background: #fff;
    color: #697a8d;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    display: flex;
    align-items: center;
    gap: 5px;
    user-select: none;
    transition: all 0.2s;
  }
  .filter-btn:hover { background: #f5f5f9; }
  .filter-btn .filter-count {
    background: #696cff;
    color: #fff;
    border-radius: 10px;
    padding: 1px 7px;
    font-size: 10px;
    min-width: 18px;
    text-align: center;
  }
  .filter-dropdown {
    position: fixed;
    top: 48px;
    right: 12px;
    z-index: 1001;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.18);
    padding: 8px 0;
    min-width: 210px;
    display: none;
    animation: filterSlide 0.15s ease-out;
  }
  .filter-dropdown.show { display: block; }
  @keyframes filterSlide { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }

  .filter-dropdown .filter-section {
    padding: 4px 14px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    color: #a1acb8;
    letter-spacing: 0.5px;
  }
  .filter-dropdown .filter-item {
    padding: 7px 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    transition: background 0.15s;
    font-size: 13px;
    user-select: none;
  }
  .filter-dropdown .filter-item:hover { background: #f5f5f9; }
  .filter-item .fi-dot {
    width: 10px; height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
    border: 2px solid #ccc;
    transition: all 0.2s;
  }
  .filter-item.active .fi-dot { border-color: transparent; }
  .filter-item .fi-icon { font-size: 16px; width: 20px; text-align: center; }
  .filter-item .fi-label { flex: 1; }
  .filter-item .fi-check {
    font-size: 14px;
    color: #4caf50;
    opacity: 0;
    transition: opacity 0.15s;
  }
  .filter-item.active .fi-check { opacity: 1; }

  .filter-item[data-layer="olt"].active .fi-dot { background: #e91e63; }
  .filter-item[data-layer="odc"].active .fi-dot { background: #ff9800; }
  .filter-item[data-layer="odp"].active .fi-dot { background: #4caf50; }
  .filter-item[data-layer="client"].active .fi-dot { background: #2196f3; }
  .filter-item[data-layer="routes"].active .fi-dot { background: #9c27b0; }
  .filter-item[data-layer="label-olt"].active .fi-dot { background: #e91e63; }
  .filter-item[data-layer="label-odc"].active .fi-dot { background: #ff9800; }
  .filter-item[data-layer="label-odp"].active .fi-dot { background: #4caf50; }
  .filter-item[data-layer="label-client"].active .fi-dot { background: #2196f3; }
  .filter-item[data-layer="area"].active .fi-dot { background: #9c27b0; }
  .filter-item[data-layer="distance"].active .fi-dot { background: #795548; }

  .filter-dropdown .filter-actions {
    border-top: 1px solid #eee;
    padding: 6px 14px 2px;
    display: flex;
    gap: 6px;
  }
  .filter-dropdown .filter-actions button {
    flex: 1;
    padding: 5px 0;
    border: none;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
  }
  .filter-actions .fa-all { background: #696cff; color: #fff; }
  .filter-actions .fa-all:hover { background: #5f61e6; }
  .filter-actions .fa-none { background: #f5f5f9; color: #697a8d; }
  .filter-actions .fa-none:hover { background: #eee; }

  /* ========== TOP LEFT CONTROLS ========== */
  .top-controls {
    position: fixed;
    top: 12px;
    left: 55px;
    z-index: 1000;
    display: flex;
    gap: 8px;
  }

  /* ========== CUSTOM TOAST ========== */
  .toast-container {
    position: fixed;
    top: 16px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 8px;
    align-items: center;
    pointer-events: none;
  }

  .custom-toast {
    padding: 8px 18px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    color: #fff;
    box-shadow: 0 3px 12px rgba(0,0,0,0.2);
    pointer-events: auto;
    animation: toastIn 0.3s ease, toastOut 0.3s ease 2.7s forwards;
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
  }

  .custom-toast.toast-success { background: #4caf50; }
  .custom-toast.toast-error { background: #ff3e1d; }
  .custom-toast.toast-info { background: #03c3ec; }
  .custom-toast.toast-warning { background: #ffab00; color: #333; }

  @keyframes toastIn { from { opacity: 0; transform: translateY(-12px); } to { opacity: 1; transform: translateY(0); } }
  @keyframes toastOut { from { opacity: 1; } to { opacity: 0; transform: translateY(-12px); } }

  .control-btn {
    padding: 8px 14px;
    background: #fff;
    border: 1px solid #d9dee3;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 6px;
    color: #566a7f;
  }

  .control-btn:hover { background: #f5f5f9; border-color: #696cff; color: #696cff; }

  /* ========== FAB ========== */
  .fab-container {
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 1000;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 12px;
  }

  .fab-main {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #696cff, #5f61e6);
    color: #fff;
    border: none;
    box-shadow: 0 4px 16px rgba(105,108,255,0.4);
    cursor: pointer;
    font-size: 24px;
    transition: all 0.3s cubic-bezier(0.68,-0.55,0.27,1.55);
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .fab-main:hover { transform: scale(1.1); box-shadow: 0 6px 24px rgba(105,108,255,0.6); }
  .fab-main.active { transform: rotate(135deg); background: linear-gradient(135deg, #ff3e1d, #e3360e); }

  .fab-menu {
    display: flex;
    flex-direction: column;
    gap: 10px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(10px);
    transition: all 0.3s;
  }

  .fab-menu.show { opacity: 1; visibility: visible; transform: translateY(0); }

  .fab-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 18px;
    background: #fff;
    border: none;
    border-radius: 24px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.15);
    cursor: pointer;
    font-size: 13px;
    font-weight: 500;
    color: #566a7f;
    transition: all 0.2s;
    white-space: nowrap;
  }

  .fab-item i { font-size: 18px; width: 20px; text-align: center; }
  .fab-item[data-type="olt"] { color: #e91e63; }
  .fab-item[data-type="olt"]:hover { background: #e91e63; color: #fff; }
  .fab-item[data-type="odc"] { color: #ff9800; }
  .fab-item[data-type="odc"]:hover { background: #ff9800; color: #fff; }
  .fab-item[data-type="odp"] { color: #4caf50; }
  .fab-item[data-type="odp"]:hover { background: #4caf50; color: #fff; }
  .fab-item[data-type="client"] { color: #2196f3; }
  .fab-item[data-type="client"]:hover { background: #2196f3; color: #fff; }

  /* ========== IMPORT FAB ========== */
  .import-fab {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #03c3ec, #02a8cc);
    color: #fff;
    border: none;
    box-shadow: 0 4px 14px rgba(3,195,236,0.4);
    cursor: pointer;
    font-size: 22px;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .import-fab:hover { transform: scale(1.1); box-shadow: 0 6px 20px rgba(3,195,236,0.6); }

  /* ========== MAP MARKERS ========== */
  .marker-icon {
    border-radius: 50% 50% 50% 0;
    transform: rotate(-45deg);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 3px 10px rgba(0,0,0,0.3);
    border: 2px solid rgba(255,255,255,0.9);
  }

  .marker-icon i { transform: rotate(45deg); color: #fff; }

  /* Server marker - professional building pin */
  .marker-server {
    background: linear-gradient(135deg, #696cff, #5f61e6);
    width: 44px; height: 44px;
    border-radius: 12px 12px 12px 2px;
    transform: none;
    border: 3px solid #fff;
    box-shadow: 0 0 14px rgba(105,108,255,0.5), 0 4px 12px rgba(0,0,0,0.35);
    position: relative;
  }
  .marker-server i { font-size: 20px; transform: none; }
  .marker-server::after {
    content: '';
    position: absolute;
    top: -5px; right: -5px;
    width: 12px; height: 12px;
    border-radius: 50%;
    background: #4caf50;
    border: 2px solid #fff;
    box-shadow: 0 0 6px rgba(76,175,80,0.7);
    animation: server-pulse 2s ease-out infinite;
  }
  @keyframes server-pulse {
    0% { box-shadow: 0 0 0 0 rgba(76,175,80,0.6); }
    70% { box-shadow: 0 0 0 8px rgba(76,175,80,0); }
    100% { box-shadow: 0 0 0 0 rgba(76,175,80,0); }
  }
  /* OLT markers - rounded square with glow */
  .marker-olt {
    background: linear-gradient(135deg, #e91e63, #c2185b);
    width: 32px; height: 32px;
    border-radius: 8px;
    transform: none;
    border: 2.5px solid #fff;
    box-shadow: 0 0 10px rgba(233,30,99,0.5), 0 3px 8px rgba(0,0,0,0.3);
  }
  .marker-olt i { font-size: 15px; transform: none; }
  /* ODC markers - hexagon shape */
  .marker-odc {
    background: linear-gradient(135deg, #ff9800, #f57c00);
    width: 30px; height: 30px;
    transform: none;
    border: none;
    clip-path: polygon(50% 0%, 93% 25%, 93% 75%, 50% 100%, 7% 75%, 7% 25%);
    box-shadow: none;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.4));
  }
  .marker-odc i { font-size: 13px; transform: none; }
  .marker-odc-full {
    background: linear-gradient(135deg, #f44336, #c62828);
    width: 30px; height: 30px;
    transform: none;
    border: none;
    clip-path: polygon(50% 0%, 93% 25%, 93% 75%, 50% 100%, 7% 75%, 7% 25%);
    box-shadow: none;
    animation: pulse-red 2s infinite;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.4));
  }
  .marker-odc-full i { font-size: 13px; transform: none; }

  /* ODP markers - diamond/rotated square shape */
  .marker-odp {
    background: linear-gradient(135deg, #4caf50, #388e3c);
    width: 24px; height: 24px;
    border-radius: 4px;
    transform: rotate(45deg);
    border: 2.5px solid #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
  }
  .marker-odp i { font-size: 12px; transform: rotate(-45deg); }
  .marker-odp-full {
    background: linear-gradient(135deg, #f44336, #c62828);
    width: 24px; height: 24px;
    border-radius: 4px;
    transform: rotate(45deg);
    border: 2.5px solid #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    animation: pulse-red 2s infinite;
  }
  .marker-odp-full i { font-size: 12px; transform: rotate(-45deg); }

  @keyframes pulse-red {
    0%, 100% { box-shadow: 0 0 0 0 rgba(244,67,54,0.5); }
    50% { box-shadow: 0 0 0 8px rgba(244,67,54,0); }
  }
  /* Client markers - circle style with pulse */
  .marker-client-online {
    background: linear-gradient(135deg, #2196f3, #1565c0);
    width: 22px; height: 22px;
    border-radius: 50%;
    transform: none;
    border: 2.5px solid #fff;
    box-shadow: 0 0 8px rgba(33,150,243,0.6), 0 2px 6px rgba(0,0,0,0.3);
  }
  .marker-client-online i { font-size: 10px; transform: none; }
  .marker-client-online::after {
    content: '';
    position: absolute;
    top: -4px; left: -4px;
    width: calc(100% + 8px); height: calc(100% + 8px);
    border-radius: 50%;
    border: 2px solid rgba(33,150,243,0.5);
    animation: client-pulse 2s ease-out infinite;
  }

  .marker-client-offline {
    background: linear-gradient(135deg, #f44336, #c62828);
    width: 22px; height: 22px;
    border-radius: 50%;
    transform: none;
    border: 2.5px solid rgba(255,255,255,0.7);
    box-shadow: 0 0 6px rgba(244,67,54,0.4), 0 2px 6px rgba(0,0,0,0.3);
    opacity: 0.8;
  }
  .marker-client-offline i { font-size: 10px; transform: none; }

  .marker-client-isolir {
    background: linear-gradient(135deg, #ff9800, #e65100);
    width: 22px; height: 22px;
    border-radius: 50%;
    transform: none;
    border: 2.5px solid #fff;
    box-shadow: 0 0 8px rgba(255,152,0,0.5), 0 2px 6px rgba(0,0,0,0.3);
  }
  .marker-client-isolir i { font-size: 10px; transform: none; }

  @keyframes client-pulse {
    0% { transform: scale(1); opacity: 1; }
    100% { transform: scale(2.2); opacity: 0; }
  }

  .marker-label {
    background: rgba(0,0,0,0.75) !important;
    border: none !important;
    color: #fff !important;
    font-size: 11px !important;
    font-weight: 500 !important;
    padding: 2px 8px !important;
    border-radius: 4px !important;
    white-space: nowrap !important;
  }

  .marker-label::before { border-top-color: rgba(0,0,0,0.75) !important; }

  /* ========== POPUP ========== */
  .popup-actions {
    display: flex;
    gap: 8px;
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #e9ecef;
  }

  .popup-btn {
    flex: 1;
    padding: 6px 10px;
    border: none;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    color: #fff;
  }

  .popup-btn-edit { background: #696cff; }
  .popup-btn-edit:hover { background: #5f61e6; }
  .popup-btn-delete { background: #ff3e1d; }
  .popup-btn-delete:hover { background: #e3360e; }

  /* ========== ADD MODE INDICATOR ========== */
  .add-mode-indicator {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(0,0,0,0.85);
    color: #fff;
    padding: 16px 28px;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 500;
    z-index: 9999;
    pointer-events: none;
    animation: pulse-indicator 2s infinite;
  }

  @keyframes pulse-indicator { 0%,100% { opacity: 0.7; } 50% { opacity: 1; } }

  #map.adding-marker { cursor: crosshair !important; }

  /* ========== SWAL BOOTSTRAP OVERRIDES ========== */
  .swal2-popup {
    border-radius: 0.5rem !important;
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
  }

  .swal2-title {
    font-weight: 600 !important;
    color: #566a7f !important;
    font-size: 1.125rem !important;
    padding: 1.25rem 1.5rem 0.5rem !important;
  }

  .swal2-html-container {
    padding: 0 1.5rem 1rem !important;
    margin: 0 !important;
    overflow: visible !important;
    text-align: left !important;
  }

  .swal2-html-container .form-control,
  .swal2-html-container .form-select {
    display: block !important;
    width: 100% !important;
    padding: 0.4375rem 0.875rem !important;
    font-size: 0.9375rem !important;
    color: #697a8d !important;
    background-color: #fff !important;
    border: 1px solid #d9dee3 !important;
    border-radius: 0.375rem !important;
    transition: border-color 0.15s, box-shadow 0.15s !important;
  }

  .swal2-html-container .form-control:focus,
  .swal2-html-container .form-select:focus {
    border-color: #696cff !important;
    box-shadow: 0 0.125rem 0.25rem rgba(105,108,255,0.4) !important;
    outline: 0 !important;
  }

  .swal2-html-container .form-label {
    margin-bottom: 0.375rem !important;
    font-size: 0.8125rem !important;
    font-weight: 500 !important;
    color: #566a7f !important;
  }

  .swal2-actions {
    gap: 0.5rem !important;
    padding: 0 1.5rem 1.25rem !important;
  }

  /* ========== SEARCHABLE SECRET DROPDOWN ========== */
  .secret-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 9999;
    background: #fff;
    border: 1px solid #d9dee3;
    border-top: none;
    border-radius: 0 0 0.375rem 0.375rem;
    max-height: 220px;
    overflow-y: auto;
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
  }
  .secret-dropdown .secret-item {
    padding: 8px 12px;
    cursor: pointer;
    font-size: 13px;
    border-bottom: 1px solid #f0f0f0;
    transition: background 0.15s;
  }
  .secret-dropdown .secret-item:hover,
  .secret-dropdown .secret-item.active {
    background: #e7e7ff;
  }
  .secret-dropdown .secret-item .secret-name { font-weight: 600; color: #566a7f; }
  .secret-dropdown .secret-item .secret-info { font-size: 11px; color: #a1acb8; }
  .secret-dropdown .secret-empty {
    padding: 12px;
    text-align: center;
    color: #a1acb8;
    font-size: 13px;
  }

  /* ========== IMPORT MODAL ========== */
  .import-list { max-height: 350px; overflow-y: auto; }

  .import-item {
    background: #f5f5f9;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 10px 14px;
    margin-bottom: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.2s;
  }

  .import-item:hover { border-color: #696cff; background: #f0f0ff; }

  .import-item-name { font-weight: 600; color: #566a7f; font-size: 13px; }
  .import-item-detail { font-size: 11px; color: #a1acb8; }

  /* ========== ANIMATED ROUTE LINES ========== */
  @keyframes dash-flow {
    to { stroke-dashoffset: -24; }
  }
  @keyframes glow-flow {
    to { stroke-dashoffset: -185; }
  }
  @keyframes traffic-packet {
    to { stroke-dashoffset: -120; }
  }
  @keyframes traffic-trail {
    to { stroke-dashoffset: -40; }
  }

  .route-dash-overlay {
    animation: dash-flow 0.7s linear infinite;
    stroke: #fff !important;
    stroke-opacity: 1 !important;
  }

  .route-glow-dot {
    animation: glow-flow 2.5s linear infinite;
    filter: drop-shadow(0 0 6px #fff) drop-shadow(0 0 12px #fff) brightness(2);
    stroke-opacity: 1 !important;
    stroke-linecap: round !important;
  }

  /* Traffic mode - packets + trail */
  .route-traffic-packet {
    animation: traffic-packet 1s linear infinite;
    stroke-opacity: 1 !important;
    filter: drop-shadow(0 0 3px currentColor) drop-shadow(0 0 6px currentColor);
  }
  .route-traffic-trail {
    animation: traffic-trail 0.6s linear infinite;
    stroke-opacity: 0.5 !important;
  }

  /* ========== LINE MODE TOGGLE ========== */
  .line-mode-btn {
    padding: 6px 14px;
    border: none;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.25s;
    display: flex;
    align-items: center;
    gap: 5px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    user-select: none;
    background: #9c27b0;
    color: #fff;
  }
  .line-mode-btn:hover { opacity: 0.85; }

  /* ========== DISTANCE LABELS ========== */
  .distance-label {
    background: rgba(0,0,0,0.7) !important;
    border: none !important;
    color: #fff !important;
    font-size: 10px !important;
    font-weight: 500 !important;
    padding: 2px 6px !important;
    border-radius: 3px !important;
    white-space: nowrap !important;
  }

  .distance-label::before { display: none !important; }

  /* ========== ROUTE EDIT ========== */
  .route-edit-btn {
    position: fixed;
    top: 60px;
    right: 12px;
    z-index: 1001;
    padding: 8px 16px;
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 2px 10px rgba(0,0,0,0.15);
    display: none;
  }

  /* ========== AREA POLYGON ========== */
  .fab-item[data-type="area"] { color: #9c27b0; }
  .fab-item[data-type="area"]:hover { background: #9c27b0; color: #fff; }
  .area-label {
    background: none !important;
    border: none !important;
    box-shadow: none !important;
    font-size: 12px;
    font-weight: 600;
    color: #333;
    text-shadow: 0 0 3px #fff, 0 0 6px #fff;
    text-align: center;
    white-space: nowrap;
  }
  .leaflet-draw-toolbar { display: none !important; }

  /* ========== INFO PANEL ========== */
  .info-toggle-btn {
    position: fixed;
    top: 80px;
    left: 12px;
    z-index: 1001;
    width: 36px;
    height: 36px;
    border: none;
    border-radius: 10px;
    background: #fff;
    color: #697a8d;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    transition: all 0.2s;
  }
  .info-toggle-btn:hover { background: #f5f5f9; }
  .info-toggle-btn.active { background: #696cff; color: #fff; }

  .info-panel {
    position: fixed;
    top: 122px;
    left: 12px;
    z-index: 1001;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.18);
    width: 240px;
    padding: 14px 16px;
    display: none;
    animation: infoSlide 0.18s ease-out;
  }
  .info-panel.show { display: block; }
  @keyframes infoSlide { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }

  .info-panel-title {
    font-size: 13px;
    font-weight: 700;
    color: #566a7f;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
  }
  .info-panel-title i { font-size: 16px; color: #696cff; }

  .info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px;
  }
  .info-card {
    background: #f5f5f9;
    border-radius: 8px;
    padding: 8px 10px;
    text-align: center;
  }
  .info-card.full-width { grid-column: 1 / -1; }
  .info-card-value {
    font-size: 20px;
    font-weight: 700;
    line-height: 1.2;
  }
  .info-card-label {
    font-size: 10px;
    font-weight: 600;
    color: #697a8d;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-top: 2px;
  }
  .info-card.olt .info-card-value { color: #e91e63; }
  .info-card.odc .info-card-value { color: #ff9800; }
  .info-card.odp .info-card-value { color: #4caf50; }
  .info-card.client .info-card-value { color: #2196f3; }
  .info-card.online .info-card-value { color: #4caf50; }
  .info-card.offline .info-card-value { color: #ff3e1d; }
  .info-card.full-odc .info-card-value { color: #ff9800; }
  .info-card.full-odp .info-card-value { color: #e91e63; }

  body.dark-mode .info-toggle-btn { background: rgba(30,30,46,0.9); color: #ccc; }
  body.dark-mode .info-toggle-btn:hover { background: rgba(40,40,60,0.95); }
  body.dark-mode .info-toggle-btn.active { background: #696cff; color: #fff; }
  body.dark-mode .info-panel { background: #2b2c40; }
  body.dark-mode .info-panel-title { color: #ccc; }
  body.dark-mode .info-card { background: rgba(255,255,255,0.06); }
  body.dark-mode .info-card-label { color: #8888a0; }

  /* ========== BRANDING ========== */
  .brand-label {
    position: fixed;
    bottom: 12px;
    left: 12px;
    z-index: 1000;
    display: flex;
    flex-direction: column;
    line-height: 1.2;
    pointer-events: none;
  }
  .brand-label .brand-name {
    font-size: 15px;
    font-weight: 700;
    color: #fff;
    letter-spacing: 0.5px;
    text-shadow: 0 1px 4px rgba(0,0,0,0.5);
  }
  .brand-label .brand-team {
    font-size: 11px;
    font-weight: 500;
    color: rgba(255,255,255,0.85);
    letter-spacing: 0.3px;
    text-shadow: 0 1px 4px rgba(0,0,0,0.5);
  }

  /* ========== DARK MODE (hanya tile peta yang gelap, marker/garis/UI tetap terang) ========== */
  body.dark-mode .leaflet-tile-pane {
    filter: brightness(0.4) saturate(0.6);
    transition: filter 0.3s;
  }
  body.dark-mode .marker-label { background: rgba(30,30,46,0.9) !important; }
  body.dark-mode .distance-label { background: rgba(30,30,46,0.85) !important; }
  body.dark-mode .leaflet-control-zoom a { background: rgba(30,30,46,0.85) !important; color: #ccc !important; border-color: #444564 !important; }
  body.dark-mode .leaflet-control-attribution { background: rgba(30,30,46,0.7) !important; color: #888 !important; }
  body.dark-mode .leaflet-popup-content-wrapper { background: #2b2c40 !important; color: #d0d0d0 !important; }
  body.dark-mode .leaflet-popup-tip { background: #2b2c40 !important; }
  body.dark-mode .leaflet-popup-content .table { color: #d0d0d0 !important; }
  body.dark-mode .leaflet-popup-content .text-muted { color: #8888a0 !important; }
  body.dark-mode .popup-btn-edit { background: #3b3c56; color: #8be9fd; border-color: #555577; }
  body.dark-mode .popup-btn-delete { background: #3b3c56; color: #ff6b81; border-color: #555577; }
</style>
@endsection

@section('content')
<script>
(function() {
  document.addEventListener('DOMContentLoaded', function() {
    ['.layout-navbar','.layout-menu','.layout-footer','.layout-overlay','nav.layout-navbar','aside.layout-menu','#layout-menu'].forEach(s => {
      document.querySelectorAll(s).forEach(el => { if (el && el.parentNode) el.parentNode.removeChild(el); });
    });
  });
  setTimeout(function() {
    ['layout-menu','layout-navbar','layout-footer'].forEach(c => {
      const el = document.querySelector('.' + c);
      if (el && el.parentNode) el.parentNode.removeChild(el);
    });
  }, 0);
})();
</script>

<div id="map-container">
  <!-- Filter Panel -->
  <div class="filter-panel">
    <button class="filter-btn" id="filterToggleBtn">
      <i class="mdi mdi-filter-outline"></i> Filter <span class="filter-count" id="filterCount">10</span>
    </button>
    <button class="line-mode-btn" id="btnLineMode"><i class="mdi mdi-chart-timeline-variant"></i> <span id="lineModeLabel">Normal</span></button>
  </div>

  <div class="filter-dropdown" id="filterDropdown">
    <div class="filter-section">Perangkat</div>
    <div class="filter-item active" data-layer="olt">
      <span class="fi-dot"></span>
      <i class="mdi mdi-access-point-network fi-icon"></i>
      <span class="fi-label">OLT</span>
      <i class="mdi mdi-check fi-check"></i>
    </div>
    <div class="filter-item active" data-layer="odc">
      <span class="fi-dot"></span>
      <i class="mdi mdi-router-network fi-icon"></i>
      <span class="fi-label">ODC</span>
      <i class="mdi mdi-check fi-check"></i>
    </div>
    <div class="filter-item active" data-layer="odp">
      <span class="fi-dot"></span>
      <i class="mdi mdi-cube-outline fi-icon"></i>
      <span class="fi-label">ODP</span>
      <i class="mdi mdi-check fi-check"></i>
    </div>
    <div class="filter-item active" data-layer="client">
      <span class="fi-dot"></span>
      <i class="mdi mdi-account-outline fi-icon"></i>
      <span class="fi-label">Pelanggan</span>
      <i class="mdi mdi-check fi-check"></i>
    </div>

    <div class="filter-section">Tampilan</div>
    <div class="filter-item active" data-layer="routes">
      <span class="fi-dot"></span>
      <i class="mdi mdi-vector-polyline fi-icon"></i>
      <span class="fi-label">Garis Rute</span>
      <i class="mdi mdi-check fi-check"></i>
    </div>
    <div class="filter-section">Label</div>
    <div class="filter-item active" data-layer="label-olt">
      <span class="fi-dot"></span>
      <i class="mdi mdi-label-outline fi-icon"></i>
      <span class="fi-label">Label OLT</span>
      <i class="mdi mdi-check fi-check"></i>
    </div>
    <div class="filter-item active" data-layer="label-odc">
      <span class="fi-dot"></span>
      <i class="mdi mdi-label-outline fi-icon"></i>
      <span class="fi-label">Label ODC</span>
      <i class="mdi mdi-check fi-check"></i>
    </div>
    <div class="filter-item active" data-layer="label-odp">
      <span class="fi-dot"></span>
      <i class="mdi mdi-label-outline fi-icon"></i>
      <span class="fi-label">Label ODP</span>
      <i class="mdi mdi-check fi-check"></i>
    </div>
    <div class="filter-item active" data-layer="label-client">
      <span class="fi-dot"></span>
      <i class="mdi mdi-label-outline fi-icon"></i>
      <span class="fi-label">Label Pelanggan</span>
      <i class="mdi mdi-check fi-check"></i>
    </div>
    <div class="filter-item active" data-layer="area">
      <span class="fi-dot"></span>
      <i class="mdi mdi-vector-polygon fi-icon"></i>
      <span class="fi-label">Area Polygon</span>
      <i class="mdi mdi-check fi-check"></i>
    </div>
    <div class="filter-item" data-layer="distance">
      <span class="fi-dot"></span>
      <i class="mdi mdi-ruler fi-icon"></i>
      <span class="fi-label">Jarak</span>
      <i class="mdi mdi-check fi-check"></i>
    </div>

    <div class="filter-actions">
      <button class="fa-all" id="filterSelectAll">Semua</button>
      <button class="fa-none" id="filterSelectNone">Kosongkan</button>
    </div>
  </div>

  <!-- Info Panel -->
  <button class="info-toggle-btn" id="infoToggleBtn" title="Informasi Jaringan">
    <i class="mdi mdi-information-outline"></i>
  </button>
  <div class="info-panel" id="infoPanel">
    <div class="info-panel-title"><i class="mdi mdi-chart-box-outline"></i> Statistik Jaringan</div>
    <div class="info-grid">
      <div class="info-card olt">
        <div class="info-card-value" id="statOlt">0</div>
        <div class="info-card-label">OLT</div>
      </div>
      <div class="info-card odc">
        <div class="info-card-value" id="statOdc">0</div>
        <div class="info-card-label">ODC</div>
      </div>
      <div class="info-card odp">
        <div class="info-card-value" id="statOdp">0</div>
        <div class="info-card-label">ODP</div>
      </div>
      <div class="info-card client">
        <div class="info-card-value" id="statClient">0</div>
        <div class="info-card-label">Client</div>
      </div>
      <div class="info-card online">
        <div class="info-card-value" id="statOnline">0</div>
        <div class="info-card-label">Client On</div>
      </div>
      <div class="info-card offline">
        <div class="info-card-value" id="statOffline">0</div>
        <div class="info-card-label">Client Off</div>
      </div>
      <div class="info-card full-odc">
        <div class="info-card-value" id="statFullOdc">0</div>
        <div class="info-card-label">ODC Penuh</div>
      </div>
      <div class="info-card full-odp">
        <div class="info-card-value" id="statFullOdp">0</div>
        <div class="info-card-label">ODP Penuh</div>
      </div>
    </div>
  </div>

  <!-- Branding -->
  <div class="brand-label">
    <span class="brand-name">LAMTIM-APP</span>
    <span class="brand-team">codeteam.id</span>
  </div>

  <!-- Top Controls -->
  <div class="top-controls">
    <button class="control-btn" id="toggleMapType"><i class="mdi mdi-satellite-variant"></i> G-SAT</button>
    <button class="control-btn" id="toggleDarkMode"><i class="mdi mdi-weather-night"></i> Malam</button>
  </div>

  <!-- FAB -->
  <div class="fab-container">
    <div class="fab-menu" id="fabMenu">
      <button class="fab-item" data-type="olt"><i class="mdi mdi-access-point-network"></i> OLT</button>
      <button class="fab-item" data-type="odc"><i class="mdi mdi-router-network"></i> ODC</button>
      <button class="fab-item" data-type="odp"><i class="mdi mdi-cube-outline"></i> ODP</button>
      <button class="fab-item" data-type="client"><i class="mdi mdi-account-outline"></i> Client</button>
      <button class="fab-item" data-type="area"><i class="mdi mdi-vector-polygon"></i> Area</button>
    </div>
    <div style="display:flex;gap:12px;align-items:center;">
      <button class="import-fab" id="btnImport" title="Import Device"><i class="mdi mdi-database-import"></i></button>
      <button class="fab-main" id="fabMain" title="Tambah Device"><i class="mdi mdi-plus"></i></button>
    </div>
  </div>

  <div id="map"></div>
</div>
@endsection

@section('vendor-script')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('page-script')
<script>
// =============== SUPPRESS MENU.JS ERRORS ===============
(function() {
  const origErr = console.error;
  console.error = function(...a) {
    if (a.join(' ').match(/insertBefore|menu\.js/)) return;
    origErr.apply(console, a);
  };
  window.addEventListener('error', function(e) {
    if (e.filename && (e.filename.includes('menu.js') || e.filename.includes('main.js'))) {
      e.preventDefault(); e.stopPropagation(); return true;
    }
  }, true);
})();

// =============== GLOBALS ===============
const CSRF = '{{ csrf_token() }}';
const BASE = '/google-map';
let map;
let tileLayers = {};
let currentLayer = 'google';

// LayerGroups for toggle
let layerGroups = {
  olt: L.layerGroup(),
  odc: L.layerGroup(),
  odp: L.layerGroup(),
  client: L.layerGroup(),
  area: L.layerGroup()
};
let routeLines = [];
let routeGlowLines = []; // secondary glow layers for mode 3
let distanceLabels = [];
let labelVisibility = { olt: true, odc: true, odp: true, client: true };
let distanceVisible = false;
let allMarkers = []; // flat list of all markers for label toggle
let lineMode = localStorage.getItem('lineMode') || 'normal'; // 'normal', 'dash', 'glow'

let addingMarkerMode = null; // null or 'olt','odc','odp','client'
let placingImport = null; // null or { type, id }
let selectOptions = { olts: [], odcs: [], odps: [], kategoris: [], pakets: [], mikrotiks: [] };

// =============== INIT ===============
function initMap() {
  const saved = localStorage.getItem('mapState');
  let center = [-5.1477, 105.2611], zoom = 14;
  if (saved) { try { const s = JSON.parse(saved); center = [s.lat, s.lng]; zoom = s.zoom; } catch(e){} }

  map = L.map('map', { center, zoom, zoomControl: true });
  map.on('moveend zoomend', () => {
    const c = map.getCenter();
    localStorage.setItem('mapState', JSON.stringify({ lat: c.lat, lng: c.lng, zoom: map.getZoom() }));
  });

  tileLayers.openstreetmap = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap', maxZoom: 19 });
  tileLayers.satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { attribution: '&copy; Esri', maxZoom: 19 });
  tileLayers.google = L.tileLayer('https://mt{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', { attribution: '&copy; Google', maxZoom: 20, subdomains: ['0','1','2','3'] });
  tileLayers.googleSatellite = L.tileLayer('https://mt{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', { attribution: '&copy; Google', maxZoom: 20, subdomains: ['0','1','2','3'] });
  // Restore saved map layer
  const savedLayer = localStorage.getItem('mapLayerType') || 'google';
  currentLayer = tileLayers[savedLayer] ? savedLayer : 'google';
  tileLayers[currentLayer].addTo(map);
  updateLayerButton();

  // Apply dark mode if saved (hanya tile peta yang gelap via CSS filter)
  const isDark = localStorage.getItem('mapDarkMode') === '1';
  if (isDark) {
    document.body.classList.add('dark-mode');
  }
  updateDarkModeButton();

  // Add layer groups to map
  Object.values(layerGroups).forEach(lg => lg.addTo(map));

  // Map click handler for add/import mode
  map.on('click', onMapClick);

  loadMapData();
  loadSelectOptions();
}

function updateLayerButton() {
  const btn = document.getElementById('toggleMapType');
  const labels = { google: 'G-SAT', googleSatellite: 'OSM', openstreetmap: 'SATELIT', satellite: 'GOOGLE' };
  const icons = { google: 'satellite-variant', googleSatellite: 'map', openstreetmap: 'satellite-variant', satellite: 'google' };
  btn.innerHTML = `<i class="mdi mdi-${icons[currentLayer] || 'map'}"></i> ${labels[currentLayer] || 'MAP'}`;
}

// =============== DATA LOADING ===============
function loadMapData() {
  fetch(`${BASE}/data`)
    .then(r => r.json())
    .then(res => {
      if (!res.status) return;
      const d = res.data;
      clearMap();

      // Server marker + center map ke server saat pertama buka
      if (d.server) {
        addServerMarker(d.server);
        if (!localStorage.getItem('mapState')) {
          map.setView([d.server.lat, d.server.lng], 15);
        }
      }

      // Device markers
      (d.olts || []).forEach(o => addDeviceMarker('olt', o));
      (d.odcs || []).forEach(o => addDeviceMarker('odc', o));
      (d.odps || []).forEach(o => addDeviceMarker('odp', o));
      (d.clients || []).forEach(o => addDeviceMarker('client', o));

      // Areas
      (d.areas || []).forEach(a => addAreaPolygon(a));

      // Routes
      (d.routes || []).forEach(r => addRouteLine(r));

      // Apply saved toggle states after data loads
      applyToggleStates();

      // Update info panel stats
      updateInfoStats(d);
    })
    .catch(e => console.error('Error loading map data:', e));
}

function loadSelectOptions() {
  fetch(`${BASE}/select-options`)
    .then(r => r.json())
    .then(res => { if (res.success) selectOptions = res.data; })
    .catch(e => console.error('Error loading select options:', e));
}

function clearMap() {
  Object.values(layerGroups).forEach(lg => lg.clearLayers());
  routeLines.forEach(rl => map.removeLayer(rl));
  routeLines = [];
  routeGlowLines.forEach(gl => map.removeLayer(gl));
  routeGlowLines = [];
  distanceLabels.forEach(dl => map.removeLayer(dl));
  distanceLabels = [];
  allMarkers = [];
  if (window._serverMarker) { map.removeLayer(window._serverMarker); window._serverMarker = null; }
}

// =============== SERVER MARKER ===============
function addServerMarker(d) {
  const icon = L.divIcon({
    className: 'custom-marker',
    html: '<div class="marker-icon marker-server"><i class="mdi mdi-office-building"></i></div>',
    iconSize: [44, 44], iconAnchor: [22, 44]
  });
  const m = L.marker([d.lat, d.lng], { icon, draggable: false });
  m.bindTooltip(d.nama, { permanent: true, direction: 'top', className: 'marker-label', offset: [0, -18] });
  m.bindPopup(`<div><h6 class="mb-1"><i class="mdi mdi-office-building me-1" style="color:#696cff"></i>${d.nama}</h6><small class="text-muted">Lokasi Server Utama</small></div>`);
  m.addTo(map);
  m._isLabel = true;
  allMarkers.push(m);
  window._serverMarker = m;
}

// =============== DEVICE MARKERS ===============
function addDeviceMarker(type, d) {
  const odcFull = (type === 'odc' && d.port > 0 && (d.portSisa || 0) <= 0);
  const odpFull = (type === 'odp' && d.port > 0 && (d.portSisa || 0) <= 0);
  const configs = {
    olt:    { cls: 'marker-olt', icon: 'mdi-access-point-network', size: [32,32], anchor: [16,16], tOffset: [0,-18] },
    odc:    { cls: odcFull ? 'marker-odc-full' : 'marker-odc', icon: odcFull ? 'mdi-close-network' : 'mdi-router-network', size: [30,30], anchor: [15,15], tOffset: [0,-18] },
    odp:    { cls: odpFull ? 'marker-odp-full' : 'marker-odp', icon: odpFull ? 'mdi-close-network' : 'mdi-cube-outline', size: [24,24], anchor: [12,12], tOffset: [0,-15] },
    client: { cls: d.status_client === 'online' ? 'marker-client-online' : (d.status_client === 'isolir' ? 'marker-client-isolir' : 'marker-client-offline'), icon: 'mdi-account', size: [22,22], anchor: [11,11], tOffset: [0,-14] }
  };
  const cfg = configs[type];
  const leafIcon = L.divIcon({
    className: 'custom-marker',
    html: `<div class="marker-icon ${cfg.cls}"><i class="mdi ${cfg.icon}"></i></div>`,
    iconSize: cfg.size, iconAnchor: cfg.anchor
  });

  const m = L.marker([d.lat, d.lng], { icon: leafIcon, draggable: true });

  // Tooltip (label)
  m.bindTooltip(d.nama, { permanent: true, direction: 'top', className: 'marker-label', offset: cfg.tOffset });

  // Popup
  m.bindPopup(buildPopupContent(type, d), { maxWidth: 280 });

  // Drag to update position + redraw lines
  m.on('dragend', function(e) {
    const p = e.target.getLatLng();
    apiFetch(`${BASE}/position/${type}/${d.id}`, 'PUT', { latitude: p.lat, longitude: p.lng })
      .then(() => { showToast('Posisi diperbarui', 'success'); loadMapData(); })
      .catch(() => showToast('Gagal update posisi', 'error'));
  });

  m._dtype = type;
  m._did = d.id;
  m._ddata = d;
  m._isLabel = true;

  layerGroups[type].addLayer(m);
  allMarkers.push(m);
}

function buildPopupContent(type, d) {
  let html = '<div style="min-width:200px;">';
  if (type === 'olt') {
    html += `<h6 class="mb-2"><i class="mdi mdi-access-point-network me-1" style="color:#e91e63"></i>${d.nama}</h6>`;
    html += `<table class="table table-sm mb-0" style="font-size:12px;">`;
    html += row('Kode', d.kode);
    html += row('IP', d.ip || '-');
    html += row('Teknologi', d.teknologi || '-');
    html += row('Port PON', d.port_pon || '-');
    html += row('Port Uplink', d.port_uplink || '-');
    html += `</table>`;
  } else if (type === 'odc') {
    const used = (d.port || 0) - (d.portSisa || 0);
    const isFull = d.port > 0 && (d.portSisa || 0) <= 0;
    html += `<h6 class="mb-2"><i class="mdi ${isFull ? 'mdi-close-network' : 'mdi-router-network'} me-1" style="color:${isFull ? '#f44336' : '#ff9800'}"></i>${d.nama} ${isFull ? '<span class="badge bg-danger" style="font-size:10px;">PORT PENUH</span>' : ''}</h6>`;
    html += `<table class="table table-sm mb-0" style="font-size:12px;">`;
    html += row('Kode', d.kode);
    if (d.odc_parent_nama) html += row('Parent ODC', `<span class="badge bg-label-warning">${d.odc_parent_nama}</span>`);
    html += row('Parent OLT', d.olt_nama || '-');
    html += row('Total Port', d.port || 0);
    html += row('Terpakai', used);
    html += row('Tersedia', isFull ? '<span class="text-danger fw-bold">0 (PENUH)</span>' : (d.portSisa || 0));
    html += `</table>`;
  } else if (type === 'odp') {
    const used = (d.port || 0) - (d.portSisa || 0);
    const isFull = d.port > 0 && (d.portSisa || 0) <= 0;
    html += `<h6 class="mb-2"><i class="mdi ${isFull ? 'mdi-close-network' : 'mdi-cube-outline'} me-1" style="color:${isFull ? '#f44336' : '#4caf50'}"></i>${d.nama} ${isFull ? '<span class="badge bg-danger" style="font-size:10px;">PORT PENUH</span>' : ''}</h6>`;
    html += `<table class="table table-sm mb-0" style="font-size:12px;">`;
    html += row('Kode', d.kode);
    html += row('Tipe', d.tipe || 'HTB');
    if (d.odp_parent_nama) html += row('Parent ODP', `<span class="badge bg-label-purple">${d.odp_parent_nama}</span>`);
    html += row('Parent ODC', d.odc_nama || '-');
    html += row('Total Port', d.port || 0);
    html += row('Terpakai', used);
    html += row('Tersedia', isFull ? '<span class="text-danger fw-bold">0 (PENUH)</span>' : (d.portSisa || 0));
    html += row('Kabel', d.kabel || '-');
    html += `</table>`;
  } else if (type === 'client') {
    const st = d.status_client || 'offline';
    const stColor = st === 'online' ? 'success' : (st === 'isolir' ? 'warning' : 'secondary');
    const stLabel = st.toUpperCase();
    html += `<h6 class="mb-2"><i class="mdi mdi-account me-1" style="color:#2196f3"></i>${d.nama}</h6>`;
    html += `<table class="table table-sm mb-0" style="font-size:12px;">`;
    html += row('IP', d.ip || '-');
    html += row('Parent ODP', d.odp_nama || '-');
    html += row('Status', `<span class="badge bg-${stColor}">${stLabel}</span>`);
    html += `</table>`;
  }
  html += `<div class="popup-actions">`;
  html += `<button onclick="editDevice('${type}',${d.id})" class="popup-btn popup-btn-edit"><i class="mdi mdi-pencil"></i> Edit</button>`;
  html += `<button onclick="deleteDevice('${type}',${d.id})" class="popup-btn popup-btn-delete"><i class="mdi mdi-delete"></i> Hapus</button>`;
  html += `</div></div>`;
  return html;
}

function row(label, val) {
  return `<tr><td class="text-muted" style="white-space:nowrap;"><strong>${label}</strong></td><td>${val}</td></tr>`;
}

// =============== ROUTE LINES ===============
function getLineStyle(color) {
  const c = color || '#9c27b0';
  if (lineMode === 'dash') {
    // Solid base line (warna asli, lebih tebal dari dash putih)
    return { color: c, weight: 7, opacity: 1, dashArray: null, className: '' };
  } else if (lineMode === 'glow') {
    // Solid base line for glow
    return { color: c, weight: 3, opacity: 0.9, dashArray: null, className: '' };
  } else if (lineMode === 'traffic') {
    // Dark semi-transparent base (seperti kabel)
    return { color: c, weight: 5, opacity: 0.35, dashArray: null, className: '' };
  }
  // normal
  return { color: c, weight: 3, opacity: 0.7, dashArray: null, className: '' };
}

function getDashOverlayStyle() {
  // White dashes running on top of solid base
  return { color: '#ffffff', weight: 6, opacity: 1, dashArray: '12, 12', lineCap: 'round', className: 'route-dash-overlay' };
}

function getGlowOverlayStyle(color) {
  const c = color || '#9c27b0';
  // Multiple dots chasing each other, spaced out
  return { color: '#ffffff', weight: 6, opacity: 1, dashArray: '5, 180', lineCap: 'round', className: 'route-glow-dot' };
}

function getTrafficOverlayStyles(color) {
  const c = color || '#9c27b0';
  return [
    // Layer 1: small trail dots (lalu lintas kecil)
    { color: c, weight: 3, opacity: 0.5, dashArray: '4, 36', lineCap: 'round', className: 'route-traffic-trail' },
    // Layer 2: bright packets (paket data berjalan)
    { color: '#fff', weight: 5, opacity: 1, dashArray: '20, 100', lineCap: 'round', className: 'route-traffic-packet' },
  ];
}

function addRouteLine(r) {
  if (!r.coords || r.coords.length < 2) return;
  const latlngs = r.coords.map(c => [c.lat, c.lng]);

  // Client offline/isolir = garis merah putus-putus statis, tanpa animasi
  const isDisconnected = r.client_status && r.client_status !== 'online';

  const style = isDisconnected
    ? { color: '#f44336', weight: 3, opacity: 0.8, dashArray: null, className: '' }
    : getLineStyle(r.color);
  const line = L.polyline(latlngs, style);

  // Hover effect
  line.on('mouseover', function() { this.setStyle({ weight: this.options.weight + 2, opacity: 1 }); });
  line.on('mouseout', function() {
    if (isDisconnected) {
      this.setStyle({ weight: 3, opacity: 0.8 });
    } else {
      const s = getLineStyle(r.color);
      this.setStyle({ weight: s.weight, opacity: s.opacity });
    }
  });

  // Click to edit waypoints
  line.on('click', function(e) {
    L.DomEvent.stopPropagation(e);
    if (r.to_type && r.to_id) {
      enableRouteEdit(line, r.to_type, r.to_id, r.color);
    }
  });

  line._routeData = r;
  // Base line dulu
  line.addTo(map);
  routeLines.push(line);

  // Overlay di atas base line (HANYA untuk client online / route non-client)
  if (!isDisconnected) {
    if (lineMode === 'dash') {
      const dashOverlay = L.polyline(latlngs, getDashOverlayStyle());
      dashOverlay.addTo(map);
      routeGlowLines.push(dashOverlay);
    } else if (lineMode === 'glow') {
      const glowLine = L.polyline(latlngs, getGlowOverlayStyle(r.color));
      glowLine.addTo(map);
      routeGlowLines.push(glowLine);
    } else if (lineMode === 'traffic') {
      getTrafficOverlayStyles(r.color).forEach(s => {
        const overlay = L.polyline(latlngs, s);
        overlay.addTo(map);
        routeGlowLines.push(overlay);
      });
    }
  }

  // Hitung jarak total dan tampilkan label
  let totalDist = 0;
  for (let i = 0; i < latlngs.length - 1; i++) {
    totalDist += map.distance(L.latLng(latlngs[i][0], latlngs[i][1]), L.latLng(latlngs[i+1][0], latlngs[i+1][1]));
  }
  const midIdx = Math.floor(latlngs.length / 2);
  const midA = latlngs[midIdx - 1] || latlngs[0];
  const midB = latlngs[midIdx];
  const midLat = (midA[0] + midB[0]) / 2;
  const midLng = (midA[1] + midB[1]) / 2;
  const distStr = totalDist >= 1000 ? (totalDist / 1000).toFixed(2) + ' km' : Math.round(totalDist) + ' m';
  const distLabel = L.tooltip({ permanent: true, direction: 'center', className: 'distance-label' })
    .setLatLng([midLat, midLng])
    .setContent(distStr);
  if (distanceVisible) distLabel.addTo(map);
  distanceLabels.push(distLabel);
}

// =============== ROUTE WAYPOINT EDITING ===============
let editingRoute = null;
let editMarkers = [];

function enableRouteEdit(line, toType, toId, color) {
  if (editingRoute) disableRouteEdit(false);

  editingRoute = { line, toType, toId, color };
  line.setStyle({ color: '#ff9800', weight: 5, opacity: 1 });

  const latlngs = line.getLatLngs();
  editMarkers = [];

  latlngs.forEach((ll, idx) => {
    const isEndpoint = idx === 0 || idx === latlngs.length - 1;
    const cm = L.circleMarker(ll, {
      radius: isEndpoint ? 5 : 7,
      fillColor: isEndpoint ? '#666' : '#ff9800',
      color: '#fff',
      weight: 2,
      fillOpacity: 1
    }).addTo(map);

    cm._idx = idx;
    cm._isEndpoint = isEndpoint;

    if (!isEndpoint) {
      // Drag waypoint
      cm.on('mousedown', function(e) {
        L.DomEvent.stopPropagation(e);
        map.dragging.disable();
        const onMove = (ev) => { cm.setLatLng(ev.latlng); updateRouteFromMarkers(); };
        const onUp = () => { map.off('mousemove', onMove); map.off('mouseup', onUp); map.dragging.enable(); };
        map.on('mousemove', onMove);
        map.on('mouseup', onUp);
      });

      // Right click to remove
      cm.on('contextmenu', function(e) {
        L.DomEvent.preventDefault(e);
        if (editMarkers.length > 2) {
          map.removeLayer(cm);
          editMarkers = editMarkers.filter(m => m !== cm);
          updateRouteFromMarkers();
        }
      });
    }

    editMarkers.push(cm);
  });

  // Add mid-point markers (ghost) for adding new waypoints
  addGhostMarkers();

  // Show save/cancel buttons
  showRouteEditButtons();

  showToast('Mode edit garis aktif. Drag titik oranye untuk bengkokkan.', 'info');
}

function addGhostMarkers() {
  // Remove old ghosts
  if (window._ghostMarkers) window._ghostMarkers.forEach(g => map.removeLayer(g));
  window._ghostMarkers = [];

  for (let i = 0; i < editMarkers.length - 1; i++) {
    const a = editMarkers[i].getLatLng();
    const b = editMarkers[i + 1].getLatLng();
    const mid = L.latLng((a.lat + b.lat) / 2, (a.lng + b.lng) / 2);

    const ghost = L.circleMarker(mid, {
      radius: 5, fillColor: '#ff9800', color: '#fff', weight: 1, fillOpacity: 0.4
    }).addTo(map);

    const insertAfter = i;
    ghost.on('mousedown', function(e) {
      L.DomEvent.stopPropagation(e);
      // Convert ghost to real marker
      map.removeLayer(ghost);
      const newM = L.circleMarker(mid, {
        radius: 7, fillColor: '#ff9800', color: '#fff', weight: 2, fillOpacity: 1
      }).addTo(map);

      newM._isEndpoint = false;
      newM.on('mousedown', function(ev) {
        L.DomEvent.stopPropagation(ev);
        map.dragging.disable();
        const onMove = (evv) => { newM.setLatLng(evv.latlng); updateRouteFromMarkers(); };
        const onUp = () => { map.off('mousemove', onMove); map.off('mouseup', onUp); map.dragging.enable(); };
        map.on('mousemove', onMove);
        map.on('mouseup', onUp);
      });
      newM.on('contextmenu', function(ev) {
        L.DomEvent.preventDefault(ev);
        map.removeLayer(newM);
        editMarkers = editMarkers.filter(m => m !== newM);
        updateRouteFromMarkers();
        addGhostMarkers();
      });

      editMarkers.splice(insertAfter + 1, 0, newM);
      updateRouteFromMarkers();
      addGhostMarkers();

      // Start drag immediately
      map.dragging.disable();
      const onMove = (evv) => { newM.setLatLng(evv.latlng); updateRouteFromMarkers(); };
      const onUp = () => { map.off('mousemove', onMove); map.off('mouseup', onUp); map.dragging.enable(); };
      map.on('mousemove', onMove);
      map.on('mouseup', onUp);
    });

    window._ghostMarkers.push(ghost);
  }
}

function updateRouteFromMarkers() {
  if (!editingRoute) return;
  const latlngs = editMarkers.map(m => m.getLatLng());
  editingRoute.line.setLatLngs(latlngs);
}

function showRouteEditButtons() {
  let el = document.getElementById('routeEditBtns');
  if (el) el.remove();
  el = document.createElement('div');
  el.id = 'routeEditBtns';
  el.style.cssText = 'position:fixed;top:60px;right:12px;z-index:1001;display:flex;gap:8px;';
  el.innerHTML = `
    <button onclick="saveRouteEdit()" style="padding:8px 16px;border:none;border-radius:8px;background:#4caf50;color:#fff;font-weight:600;font-size:13px;cursor:pointer;box-shadow:0 2px 10px rgba(0,0,0,0.15);"><i class="mdi mdi-check"></i> Simpan</button>
    <button onclick="resetRouteEdit()" style="padding:8px 16px;border:none;border-radius:8px;background:#ff9800;color:#fff;font-weight:600;font-size:13px;cursor:pointer;box-shadow:0 2px 10px rgba(0,0,0,0.15);"><i class="mdi mdi-restore"></i> Reset</button>
    <button onclick="disableRouteEdit(true)" style="padding:8px 16px;border:none;border-radius:8px;background:#ff3e1d;color:#fff;font-weight:600;font-size:13px;cursor:pointer;box-shadow:0 2px 10px rgba(0,0,0,0.15);"><i class="mdi mdi-close"></i> Batal</button>
  `;
  document.body.appendChild(el);
}

function resetRouteEdit() {
  if (!editingRoute) return;
  // Hapus semua waypoint markers (kecuali endpoint pertama dan terakhir)
  const firstLL = editMarkers[0].getLatLng();
  const lastLL = editMarkers[editMarkers.length - 1].getLatLng();

  editMarkers.forEach(m => map.removeLayer(m));
  editMarkers = [];
  if (window._ghostMarkers) { window._ghostMarkers.forEach(g => map.removeLayer(g)); window._ghostMarkers = []; }

  // Buat ulang hanya 2 endpoint
  [firstLL, lastLL].forEach((ll, idx) => {
    const cm = L.circleMarker(ll, {
      radius: 5, fillColor: '#666', color: '#fff', weight: 2, fillOpacity: 1
    }).addTo(map);
    cm._idx = idx;
    cm._isEndpoint = true;
    editMarkers.push(cm);
  });

  // Update garis jadi lurus
  editingRoute.line.setLatLngs([firstLL, lastLL]);
  addGhostMarkers();
  showToast('Waypoints direset ke garis lurus', 'info');
}

function saveRouteEdit() {
  if (!editingRoute) return;
  const { toType, toId } = editingRoute;
  const latlngs = editMarkers.map(m => m.getLatLng());

  // Waypoints = all except first and last
  const waypoints = latlngs.slice(1, -1).map(ll => ({ lat: ll.lat, lng: ll.lng }));

  apiFetch(`${BASE}/route-waypoints/${toType}/${toId}`, 'PUT', { route_waypoints: waypoints.length ? waypoints : null })
    .then(() => {
      showToast('Waypoints berhasil disimpan', 'success');
      disableRouteEdit(false);
      loadMapData();
    })
    .catch(e => showToast('Gagal simpan waypoints: ' + e.message, 'error'));
}

function disableRouteEdit(reload) {
  if (!editingRoute) return;
  const restoreStyle = getLineStyle(editingRoute.color);
  editingRoute.line.setStyle(restoreStyle);
  editMarkers.forEach(m => map.removeLayer(m));
  editMarkers = [];
  if (window._ghostMarkers) { window._ghostMarkers.forEach(g => map.removeLayer(g)); window._ghostMarkers = []; }
  editingRoute = null;
  const el = document.getElementById('routeEditBtns');
  if (el) el.remove();
  if (reload) loadMapData();
}

// =============== TOGGLE VISIBILITY (Filter Dropdown) ===============
function saveToggleStates() {
  const states = {};
  document.querySelectorAll('.filter-item').forEach(item => {
    states[item.dataset.layer] = item.classList.contains('active');
  });
  localStorage.setItem('mapToggleStates', JSON.stringify(states));
}

function restoreToggleStates() {
  const saved = localStorage.getItem('mapToggleStates');
  if (!saved) return;
  try {
    const states = JSON.parse(saved);
    document.querySelectorAll('.filter-item').forEach(item => {
      const layer = item.dataset.layer;
      if (states[layer] !== undefined) {
        if (states[layer]) item.classList.add('active');
        else item.classList.remove('active');
      }
    });
  } catch(e) {}
}

function applyToggleStates() {
  document.querySelectorAll('.filter-item').forEach(item => {
    const layer = item.dataset.layer;
    const active = item.classList.contains('active');
    applyToggle(layer, active);
  });
  updateFilterCount();
}

function applyToggle(layer, active) {
  // Per-type label toggles: label-olt, label-odc, label-odp, label-client
  const labelMatch = layer.match(/^label-(olt|odc|odp|client)$/);
  if (labelMatch) {
    const dtype = labelMatch[1];
    labelVisibility[dtype] = active;
    allMarkers.forEach(m => {
      if (m._dtype === dtype && m.getTooltip()) {
        if (active) m.openTooltip();
        else m.closeTooltip();
      }
    });
  } else if (layer === 'routes') {
    routeLines.forEach(rl => {
      if (active) { if (!map.hasLayer(rl)) rl.addTo(map); }
      else map.removeLayer(rl);
    });
    routeGlowLines.forEach(gl => {
      if (active) { if (!map.hasLayer(gl)) gl.addTo(map); }
      else map.removeLayer(gl);
    });
  } else if (layer === 'distance') {
    distanceVisible = active;
    distanceLabels.forEach(dl => {
      if (active) { if (!map.hasLayer(dl)) dl.addTo(map); }
      else map.removeLayer(dl);
    });
  } else if (layerGroups[layer]) {
    if (active) { if (!map.hasLayer(layerGroups[layer])) layerGroups[layer].addTo(map); }
    else map.removeLayer(layerGroups[layer]);
  }
}

function updateFilterCount() {
  const total = document.querySelectorAll('.filter-item').length;
  const activeCount = document.querySelectorAll('.filter-item.active').length;
  const badge = document.getElementById('filterCount');
  if (badge) {
    badge.textContent = activeCount;
    badge.style.display = activeCount === total ? 'none' : 'inline-flex';
  }
}

document.addEventListener('DOMContentLoaded', function() {
  restoreToggleStates();

  // Filter item click handlers
  document.querySelectorAll('.filter-item').forEach(item => {
    item.addEventListener('click', function() {
      this.classList.toggle('active');
      const layer = this.dataset.layer;
      const active = this.classList.contains('active');
      applyToggle(layer, active);
      saveToggleStates();
      updateFilterCount();
    });
  });

  // Toggle dropdown open/close
  const filterBtn = document.getElementById('filterToggleBtn');
  const filterDropdown = document.getElementById('filterDropdown');
  if (filterBtn && filterDropdown) {
    filterBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      filterDropdown.classList.toggle('show');
    });

    // Close dropdown on click outside
    document.addEventListener('click', function(e) {
      if (!filterDropdown.contains(e.target) && !filterBtn.contains(e.target)) {
        filterDropdown.classList.remove('show');
      }
    });
  }

  // Select All button
  const btnAll = document.getElementById('filterSelectAll');
  if (btnAll) {
    btnAll.addEventListener('click', function() {
      document.querySelectorAll('.filter-item').forEach(item => item.classList.add('active'));
      applyToggleStates();
      saveToggleStates();
    });
  }

  // Select None button
  const btnNone = document.getElementById('filterSelectNone');
  if (btnNone) {
    btnNone.addEventListener('click', function() {
      document.querySelectorAll('.filter-item').forEach(item => item.classList.remove('active'));
      applyToggleStates();
      saveToggleStates();
    });
  }

  updateFilterCount();
});

// =============== INFO PANEL ===============
function updateInfoStats(data) {
  const olts = data.olts || [];
  const odcs = data.odcs || [];
  const odps = data.odps || [];
  const clients = data.clients || [];

  document.getElementById('statOlt').textContent = olts.length;
  document.getElementById('statOdc').textContent = odcs.length;
  document.getElementById('statOdp').textContent = odps.length;
  document.getElementById('statClient').textContent = clients.length;
  document.getElementById('statOnline').textContent = clients.filter(c => c.status_client === 'online').length;
  document.getElementById('statOffline').textContent = clients.filter(c => c.status_client !== 'online').length;
  document.getElementById('statFullOdc').textContent = odcs.filter(o => o.port > 0 && (o.portSisa || 0) <= 0).length;
  document.getElementById('statFullOdp').textContent = odps.filter(o => o.port > 0 && (o.portSisa || 0) <= 0).length;
}

document.addEventListener('DOMContentLoaded', function() {
  const infoBtn = document.getElementById('infoToggleBtn');
  const infoPanel = document.getElementById('infoPanel');
  if (!infoBtn || !infoPanel) return;

  // Restore state from localStorage
  if (localStorage.getItem('infoPanelOpen') === 'true') {
    infoPanel.classList.add('show');
    infoBtn.classList.add('active');
  }

  infoBtn.addEventListener('click', function(e) {
    e.stopPropagation();
    const open = infoPanel.classList.toggle('show');
    infoBtn.classList.toggle('active', open);
    localStorage.setItem('infoPanelOpen', open);
  });

  document.addEventListener('click', function(e) {
    if (!infoPanel.contains(e.target) && !infoBtn.contains(e.target)) {
      infoPanel.classList.remove('show');
      infoBtn.classList.remove('active');
      localStorage.setItem('infoPanelOpen', 'false');
    }
  });
});

// =============== MAP TYPE TOGGLE ===============
document.addEventListener('DOMContentLoaded', function() {
  document.getElementById('toggleMapType').addEventListener('click', function() {
    const order = ['google', 'googleSatellite', 'openstreetmap', 'satellite'];
    const idx = order.indexOf(currentLayer);
    const next = order[(idx + 1) % order.length];
    map.removeLayer(tileLayers[currentLayer]);
    tileLayers[next].addTo(map);
    currentLayer = next;
    localStorage.setItem('mapLayerType', next);
    updateLayerButton();
  });
});

// =============== DARK MODE TOGGLE ===============
function updateDarkModeButton() {
  const btn = document.getElementById('toggleDarkMode');
  const isDark = document.body.classList.contains('dark-mode');
  btn.innerHTML = isDark
    ? '<i class="mdi mdi-white-balance-sunny"></i> Terang'
    : '<i class="mdi mdi-weather-night"></i> Malam';
}

document.addEventListener('DOMContentLoaded', function() {
  document.getElementById('toggleDarkMode').addEventListener('click', function() {
    const isDark = document.body.classList.contains('dark-mode');
    if (isDark) {
      document.body.classList.remove('dark-mode');
      localStorage.setItem('mapDarkMode', '0');
    } else {
      document.body.classList.add('dark-mode');
      localStorage.setItem('mapDarkMode', '1');
    }
    updateDarkModeButton();
    showToast(document.body.classList.contains('dark-mode') ? 'Mode Malam' : 'Mode Terang', 'info');
  });
});

// =============== LINE MODE TOGGLE ===============
const lineModes = ['normal', 'dash', 'glow', 'traffic'];
const lineModeLabels = { normal: 'Normal', dash: 'Dash', glow: 'Glow', traffic: 'Traffic' };
const lineModeIcons = { normal: 'mdi-chart-timeline-variant', dash: 'mdi-dots-horizontal', glow: 'mdi-blur-linear', traffic: 'mdi-swap-horizontal' };

function updateLineModeButton() {
  const label = document.getElementById('lineModeLabel');
  const btn = document.getElementById('btnLineMode');
  if (label) label.textContent = lineModeLabels[lineMode] || 'Normal';
  if (btn) {
    const icon = btn.querySelector('i');
    if (icon) icon.className = 'mdi ' + (lineModeIcons[lineMode] || 'mdi-chart-timeline-variant');
  }
}

document.addEventListener('DOMContentLoaded', function() {
  updateLineModeButton();
  document.getElementById('btnLineMode').addEventListener('click', function() {
    const idx = lineModes.indexOf(lineMode);
    lineMode = lineModes[(idx + 1) % lineModes.length];
    localStorage.setItem('lineMode', lineMode);
    updateLineModeButton();
    loadMapData(); // reload lines with new style
    showToast(`Mode garis: ${lineModeLabels[lineMode]}`, 'info');
  });
});

// =============== FAB MENU ===============
document.addEventListener('DOMContentLoaded', function() {
  const fabMain = document.getElementById('fabMain');
  const fabMenu = document.getElementById('fabMenu');

  fabMain.addEventListener('click', function() {
    this.classList.toggle('active');
    fabMenu.classList.toggle('show');
  });

  document.querySelectorAll('.fab-item').forEach(item => {
    item.addEventListener('click', function() {
      const type = this.dataset.type;
      fabMain.classList.remove('active');
      fabMenu.classList.remove('show');
      if (type === 'area') {
        startAreaDrawMode();
      } else {
        startAddMode(type);
      }
    });
  });
});

// =============== ADD DEVICE MODE ===============
function startAddMode(type) {
  addingMarkerMode = type;
  document.getElementById('map').classList.add('adding-marker');

  // Show indicator
  let ind = document.getElementById('addModeIndicator');
  if (!ind) {
    ind = document.createElement('div');
    ind.id = 'addModeIndicator';
    ind.className = 'add-mode-indicator';
    document.body.appendChild(ind);
  }
  ind.innerHTML = `<i class="mdi mdi-map-marker-plus me-2"></i>Klik peta untuk menempatkan ${type.toUpperCase()}`;
  ind.style.display = 'block';

  showToast(`Mode tambah ${type.toUpperCase()} aktif. Klik pada peta.`, 'info');
}

function cancelAddMode() {
  addingMarkerMode = null;
  placingImport = null;
  document.getElementById('map').classList.remove('adding-marker');
  const ind = document.getElementById('addModeIndicator');
  if (ind) ind.style.display = 'none';
}

function onMapClick(e) {
  if (addingMarkerMode) {
    const type = addingMarkerMode;
    cancelAddMode();
    showAddModal(type, e.latlng.lat, e.latlng.lng);
  } else if (placingImport) {
    const { type, id } = placingImport;
    cancelAddMode();
    apiFetch(`${BASE}/set-coordinates`, 'POST', { type, id, latitude: e.latlng.lat, longitude: e.latlng.lng })
      .then(() => { showToast('Koordinat berhasil disimpan', 'success'); loadMapData(); })
      .catch(err => showToast('Gagal: ' + err.message, 'error'));
  }
}

// =============== ADD MODALS (SweetAlert2 + Bootstrap) ===============
function showAddModal(type, lat, lng) {
  if (type === 'olt') showOLTModal(null, lat, lng);
  else if (type === 'odc') showODCModal(null, lat, lng);
  else if (type === 'odp') showODPModal(null, lat, lng);
  else if (type === 'client') showClientModal(null, lat, lng);
}

function showOLTModal(editData, lat, lng) {
  const isEdit = !!editData;
  Swal.fire({
    title: `<i class="mdi mdi-access-point-network me-2" style="color:#e91e63"></i>${isEdit ? 'Edit' : 'Tambah'} OLT`,
    html: `
      <div class="text-start">
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label">Nama <span class="text-danger">*</span></label><input type="text" id="s-nama" class="form-control" value="${isEdit ? editData.nama : ''}"></div>
          <div class="col-md-6"><label class="form-label">Kode <span class="text-danger">*</span></label><input type="text" id="s-kode" class="form-control" value="${isEdit ? editData.kode : ''}" placeholder="OLT-01"></div>
          <div class="col-md-6"><label class="form-label">IP Address</label><input type="text" id="s-ip" class="form-control" value="${isEdit ? (editData.ip || '') : ''}" placeholder="192.168.x.x"></div>
          <div class="col-md-6"><label class="form-label">Teknologi</label>
            <select id="s-teknologi" class="form-select">
              <option value="EPON" ${isEdit && editData.teknologi==='EPON' ? 'selected' : ''}>EPON</option>
              <option value="GPON" ${isEdit && editData.teknologi==='GPON' ? 'selected' : ''}>GPON</option>
              <option value="XG-PON" ${isEdit && editData.teknologi==='XG-PON' ? 'selected' : ''}>XG-PON</option>
            </select>
          </div>
          <div class="col-md-6"><label class="form-label">Port PON</label><input type="number" id="s-port_pon" class="form-control" min="0" value="${isEdit ? (editData.port_pon || '') : ''}"></div>
          <div class="col-md-6"><label class="form-label">Port Uplink</label><input type="number" id="s-port_uplink" class="form-control" min="0" value="${isEdit ? (editData.port_uplink || '') : ''}"></div>
          <div class="col-md-6"><label class="form-label">Latitude</label><input type="text" id="s-lat" class="form-control" value="${lat || (isEdit ? editData.lat : '')}" readonly></div>
          <div class="col-md-6"><label class="form-label">Longitude</label><input type="text" id="s-lng" class="form-control" value="${lng || (isEdit ? editData.lng : '')}" readonly></div>
          <div class="col-12"><label class="form-label">Keterangan</label><textarea id="s-keterangan" class="form-control" rows="2">${isEdit ? (editData.keterangan || '') : ''}</textarea></div>
        </div>
      </div>
    `,
    width: 550,
    showCancelButton: true,
    confirmButtonText: `<i class="mdi mdi-content-save me-1"></i>${isEdit ? 'Update' : 'Simpan'}`,
    cancelButtonText: '<i class="mdi mdi-close me-1"></i>Batal',
    customClass: { confirmButton: 'btn btn-primary me-2', cancelButton: 'btn btn-label-secondary' },
    buttonsStyling: false,
    preConfirm: () => {
      const nama = document.getElementById('s-nama').value.trim();
      const kode = document.getElementById('s-kode').value.trim();
      if (!nama || !kode) { Swal.showValidationMessage('Nama dan Kode wajib diisi'); return false; }
      return {
        nama, kode,
        ip: document.getElementById('s-ip').value.trim() || null,
        teknologi: document.getElementById('s-teknologi').value,
        port_pon: parseInt(document.getElementById('s-port_pon').value) || null,
        port_uplink: parseInt(document.getElementById('s-port_uplink').value) || null,
        latitude: parseFloat(document.getElementById('s-lat').value),
        longitude: parseFloat(document.getElementById('s-lng').value),
        keterangan: document.getElementById('s-keterangan').value.trim() || null,
      };
    }
  }).then(result => {
    if (!result.isConfirmed) return;
    const url = isEdit ? `${BASE}/olt/${editData.id}` : `${BASE}/olt`;
    const method = isEdit ? 'PUT' : 'POST';
    apiFetch(url, method, result.value)
      .then(() => { showToast(`OLT berhasil ${isEdit ? 'diupdate' : 'ditambahkan'}`, 'success'); loadMapData(); loadSelectOptions(); })
      .catch(e => showToast('Gagal: ' + e.message, 'error'));
  });
}

function showODCModal(editData, lat, lng) {
  const isEdit = !!editData;
  const oltOpts = selectOptions.olts.map(o => `<option value="${o.id}" ${isEdit && editData.idOlt==o.id ? 'selected' : ''}>${o.nama} (${o.kode})</option>`).join('');
  // Parent ODC options: exclude self when editing
  const odcParentOpts = selectOptions.odcs
    .filter(o => !isEdit || o.id != editData.id)
    .map(o => `<option value="${o.id}" ${isEdit && editData.idOdc==o.id ? 'selected' : ''}>${o.nama} (${o.kode})</option>`).join('');
  const hasParentOdc = isEdit && editData.idOdc;
  Swal.fire({
    title: `<i class="mdi mdi-router-network me-2" style="color:#ff9800"></i>${isEdit ? 'Edit' : 'Tambah'} ODC`,
    html: `
      <div class="text-start">
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label">Nama <span class="text-danger">*</span></label><input type="text" id="s-nama" class="form-control" value="${isEdit ? editData.nama : ''}"></div>
          <div class="col-md-6"><label class="form-label">Kode <span class="text-danger">*</span></label><input type="text" id="s-kode" class="form-control" value="${isEdit ? editData.kode : ''}" placeholder="ODC-01"></div>
          <div class="col-md-6"><label class="form-label">Total Port</label><input type="number" id="s-port" class="form-control" min="0" value="${isEdit ? (editData.port || '') : ''}"></div>
          <div class="col-md-6"><label class="form-label">Port OLT</label><input type="number" id="s-portOlt" class="form-control" min="0" value="${isEdit ? (editData.portOlt || '') : ''}"></div>
          <div class="col-md-6"><label class="form-label">Parent ODC</label>
            <select id="s-idOdc" class="form-select"><option value="">-- Langsung ke OLT --</option>${odcParentOpts}</select>
          </div>
          <div class="col-md-6"><label class="form-label">Parent OLT</label>
            <select id="s-idOlt" class="form-select" ${hasParentOdc ? 'disabled' : ''}><option value="">-- Pilih OLT --</option>${oltOpts}</select>
          </div>
          <div class="col-md-6"><label class="form-label">Latitude</label><input type="text" id="s-lat" class="form-control" value="${lat || (isEdit ? editData.lat : '')}" readonly></div>
          <div class="col-md-6"><label class="form-label">Longitude</label><input type="text" id="s-lng" class="form-control" value="${lng || (isEdit ? editData.lng : '')}" readonly></div>
          <div class="col-12"><label class="form-label">Keterangan</label><textarea id="s-keterangan" class="form-control" rows="2">${isEdit ? (editData.keterangan || '') : ''}</textarea></div>
        </div>
      </div>
    `,
    width: 550,
    showCancelButton: true,
    confirmButtonText: `<i class="mdi mdi-content-save me-1"></i>${isEdit ? 'Update' : 'Simpan'}`,
    cancelButtonText: '<i class="mdi mdi-close me-1"></i>Batal',
    customClass: { confirmButton: 'btn btn-primary me-2', cancelButton: 'btn btn-label-secondary' },
    buttonsStyling: false,
    didOpen: () => {
      // When parent ODC selected, disable OLT dropdown (connection via parent ODC)
      const odcSel = document.getElementById('s-idOdc');
      const oltSel = document.getElementById('s-idOlt');
      odcSel.addEventListener('change', function() {
        if (this.value) {
          oltSel.disabled = true;
          oltSel.value = '';
        } else {
          oltSel.disabled = false;
        }
      });
    },
    preConfirm: () => {
      const nama = document.getElementById('s-nama').value.trim();
      const kode = document.getElementById('s-kode').value.trim();
      if (!nama || !kode) { Swal.showValidationMessage('Nama dan Kode wajib diisi'); return false; }
      const idOdc = document.getElementById('s-idOdc').value || null;
      return {
        nama, kode,
        port: parseInt(document.getElementById('s-port').value) || 0,
        portOlt: parseInt(document.getElementById('s-portOlt').value) || null,
        idOlt: document.getElementById('s-idOlt').value || null,
        idOdc: idOdc ? parseInt(idOdc) : null,
        latitude: parseFloat(document.getElementById('s-lat').value),
        longitude: parseFloat(document.getElementById('s-lng').value),
        keterangan: document.getElementById('s-keterangan').value.trim() || null,
      };
    }
  }).then(result => {
    if (!result.isConfirmed) return;
    const url = isEdit ? `${BASE}/odc/${editData.id}` : `${BASE}/odc`;
    const method = isEdit ? 'PUT' : 'POST';
    apiFetch(url, method, result.value)
      .then(() => { showToast(`ODC berhasil ${isEdit ? 'diupdate' : 'ditambahkan'}`, 'success'); loadMapData(); loadSelectOptions(); })
      .catch(e => showToast('Gagal: ' + e.message, 'error'));
  });
}

function showODPModal(editData, lat, lng) {
  const isEdit = !!editData;
  const odcOpts = selectOptions.odcs.map(o => `<option value="${o.id}" ${isEdit && editData.idOdc==o.id ? 'selected' : ''}>${o.nama} (${o.kode}) - sisa: ${o.portSisa || 0}</option>`).join('');
  const odpOpts = selectOptions.odps.filter(o => !isEdit || o.id != editData.id).map(o => `<option value="${o.id}" ${isEdit && editData.idOdp==o.id ? 'selected' : ''}>${o.nama} (${o.kode}) - sisa: ${o.portSisa || 0}</option>`).join('');
  Swal.fire({
    title: `<i class="mdi mdi-cube-outline me-2" style="color:#4caf50"></i>${isEdit ? 'Edit' : 'Tambah'} ODP`,
    html: `
      <div class="text-start">
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label">Nama <span class="text-danger">*</span></label><input type="text" id="s-nama" class="form-control" value="${isEdit ? editData.nama : ''}"></div>
          <div class="col-md-6"><label class="form-label">Kode <span class="text-danger">*</span></label><input type="text" id="s-kode" class="form-control" value="${isEdit ? editData.kode : ''}" placeholder="ODP-01"></div>
          <div class="col-md-6"><label class="form-label">Tipe</label>
            <select id="s-tipe" class="form-select">
              <option value="Splitter" ${!isEdit || editData.tipe==='Splitter' ? 'selected' : ''}>Splitter</option>
              <option value="HTB" ${isEdit && editData.tipe==='HTB' ? 'selected' : ''}>HTB</option>
            </select>
          </div>
          <div class="col-md-6"><label class="form-label">Total Port</label><input type="number" id="s-port" class="form-control" min="0" value="${isEdit ? (editData.port || '') : ''}"></div>
          <div class="col-md-6"><label class="form-label">Port ODC</label><input type="number" id="s-portOdc" class="form-control" min="0" value="${isEdit ? (editData.portOdc || '') : ''}"></div>
          <div class="col-md-6"><label class="form-label">Kabel</label><input type="text" id="s-kabel" class="form-control" value="${isEdit ? (editData.kabel || '') : ''}" placeholder="36m"></div>
          <div class="col-md-6"><label class="form-label">FO A</label><input type="number" id="s-fo_a" class="form-control" min="0" value="${isEdit ? (editData.fo_a || '') : ''}"></div>
          <div class="col-md-6"><label class="form-label">FO B</label><input type="number" id="s-fo_b" class="form-control" min="0" value="${isEdit ? (editData.fo_b || '') : ''}"></div>
          <div class="col-12"><small class="text-warning"><i class="mdi mdi-alert-outline me-1"></i>Pilih salah satu: Parent ODC atau Parent ODP (Estafet), tidak boleh keduanya.</small></div>
          <div class="col-md-6"><label class="form-label">Parent ODC</label>
            <select id="s-idOdc" class="form-select"><option value="">-- Pilih ODC --</option>${odcOpts}</select>
          </div>
          <div class="col-md-6"><label class="form-label">Parent ODP (Estafet)</label>
            <select id="s-idOdp" class="form-select"><option value="">-- Pilih ODP --</option>${odpOpts}</select>
          </div>
          <div class="col-md-6"><label class="form-label">Latitude</label><input type="text" id="s-lat" class="form-control" value="${lat || (isEdit ? editData.lat : '')}" readonly></div>
          <div class="col-md-6"><label class="form-label">Longitude</label><input type="text" id="s-lng" class="form-control" value="${lng || (isEdit ? editData.lng : '')}" readonly></div>
          <div class="col-12"><label class="form-label">Keterangan</label><textarea id="s-keterangan" class="form-control" rows="2">${isEdit ? (editData.keterangan || '') : ''}</textarea></div>
        </div>
      </div>
    `,
    width: 600,
    showCancelButton: true,
    confirmButtonText: `<i class="mdi mdi-content-save me-1"></i>${isEdit ? 'Update' : 'Simpan'}`,
    cancelButtonText: '<i class="mdi mdi-close me-1"></i>Batal',
    customClass: { confirmButton: 'btn btn-primary me-2', cancelButton: 'btn btn-label-secondary' },
    buttonsStyling: false,
    didOpen: () => {
      const selOdc = document.getElementById('s-idOdc');
      const selOdp = document.getElementById('s-idOdp');
      selOdc.addEventListener('change', () => { if (selOdc.value) { selOdp.value = ''; selOdp.disabled = true; } else { selOdp.disabled = false; } });
      selOdp.addEventListener('change', () => { if (selOdp.value) { selOdc.value = ''; selOdc.disabled = true; } else { selOdc.disabled = false; } });
      // Set initial disabled state for edit
      if (isEdit && editData.idOdc) selOdp.disabled = true;
      if (isEdit && editData.idOdp) selOdc.disabled = true;
    },
    preConfirm: () => {
      const nama = document.getElementById('s-nama').value.trim();
      const kode = document.getElementById('s-kode').value.trim();
      if (!nama || !kode) { Swal.showValidationMessage('Nama dan Kode wajib diisi'); return false; }
      const idOdc = document.getElementById('s-idOdc').value || null;
      const idOdp = document.getElementById('s-idOdp').value || null;
      if (idOdc && idOdp) { Swal.showValidationMessage('Pilih salah satu: Parent ODC atau Parent ODP, tidak boleh keduanya'); return false; }
      return {
        nama, kode,
        tipe: document.getElementById('s-tipe').value,
        port: parseInt(document.getElementById('s-port').value) || 0,
        portOdc: parseInt(document.getElementById('s-portOdc').value) || null,
        kabel: document.getElementById('s-kabel').value.trim() || null,
        fo_a: parseInt(document.getElementById('s-fo_a').value) || 0,
        fo_b: parseInt(document.getElementById('s-fo_b').value) || 0,
        idOdc, idOdp,
        latitude: parseFloat(document.getElementById('s-lat').value),
        longitude: parseFloat(document.getElementById('s-lng').value),
        keterangan: document.getElementById('s-keterangan').value.trim() || null,
      };
    }
  }).then(result => {
    if (!result.isConfirmed) return;
    const url = isEdit ? `${BASE}/odp/${editData.id}` : `${BASE}/odp`;
    const method = isEdit ? 'PUT' : 'POST';
    apiFetch(url, method, result.value)
      .then(() => { showToast(`ODP berhasil ${isEdit ? 'diupdate' : 'ditambahkan'}`, 'success'); loadMapData(); loadSelectOptions(); })
      .catch(e => showToast('Gagal: ' + e.message, 'error'));
  });
}

function showClientModal(editData, lat, lng) {
  const isEdit = !!editData;
  const odpOpts = selectOptions.odps.map(o => `<option value="${o.id}" ${isEdit && editData.idOdp==o.id ? 'selected' : ''}>${o.nama} (${o.kode}) - sisa: ${o.portSisa || 0}</option>`).join('');
  const mkOpts = selectOptions.mikrotiks.map(o => `<option value="${o.id}" ${isEdit && editData.idMikrotik==o.id ? 'selected' : ''}>${o.nama} (${o.ip})</option>`).join('');
  const katOpts = selectOptions.kategoris.map(o => `<option value="${o.id}" ${isEdit && editData.idKategori==o.id ? 'selected' : ''}>${o.nama}</option>`).join('');
  const areaOpts = (selectOptions.areas || []).map(o => `<option value="${o.id}" ${isEdit && editData.idArea==o.id ? 'selected' : ''}>${o.name} (${o.code_area || '-'})</option>`).join('');
  const paketAll = selectOptions.pakets || [];

  Swal.fire({
    title: `<i class="mdi mdi-account-outline me-2" style="color:#2196f3"></i>${isEdit ? 'Edit' : 'Tambah'} Client`,
    html: `
      <div class="text-start">
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label">Nama Client <span class="text-danger">*</span></label><input type="text" id="s-nama" class="form-control" value="${isEdit ? editData.nama : ''}"></div>
          <div class="col-md-6"><label class="form-label">No. WA</label><input type="text" id="s-wa" class="form-control" value="${isEdit ? (editData.wa || '') : ''}" placeholder="08xxxxxxxxxx"></div>

          <div class="col-md-6"><label class="form-label">Mikrotik Server</label>
            <select id="s-idMikrotik" class="form-select"><option value="">-- Pilih Mikrotik --</option>${mkOpts}</select>
          </div>
          <div class="col-md-6"><label class="form-label">Secret PPPoE</label>
            <div class="position-relative" id="s-secret-wrapper">
              <input type="text" id="s-secret-search" class="form-control" placeholder="Pilih Mikrotik dulu" disabled autocomplete="off">
              <input type="hidden" id="s-secret-value">
              <div id="s-secret-dropdown" class="secret-dropdown" style="display:none;"></div>
            </div>
            <div id="s-secret-loading" class="text-muted small mt-1" style="display:none;"><i class="mdi mdi-loading mdi-spin"></i> Memuat secrets...</div>
          </div>

          <div class="col-md-6"><label class="form-label">Kategori</label>
            <select id="s-idKategori" class="form-select"><option value="">-- Pilih Kategori --</option>${katOpts}</select>
          </div>
          <div class="col-md-6"><label class="form-label">Paket</label>
            <select id="s-idPaket" class="form-select"><option value="">-- Pilih Kategori dulu --</option></select>
          </div>

          <div class="col-md-6"><label class="form-label">Parent ODP</label>
            <select id="s-idOdp" class="form-select"><option value="">-- Pilih ODP --</option>${odpOpts}</select>
          </div>
          <div class="col-md-6"><label class="form-label">Port ODP</label><input type="number" id="s-portOdp" class="form-control" value="${isEdit ? (editData.portOdp || '') : ''}" min="0" placeholder="Nomor port"></div>

          <div class="col-md-6"><label class="form-label">IP Address</label><input type="text" id="s-ip" class="form-control" value="${isEdit ? (editData.ip || '') : ''}" placeholder="Otomatis dari secret / isi manual"></div>
          <div class="col-md-6"><label class="form-label">Status</label>
            <select id="s-status" class="form-select">
              <option value="online" ${isEdit && editData.status === 'online' ? 'selected' : (!isEdit ? 'selected' : '')}>Online</option>
              <option value="offline" ${isEdit && editData.status === 'offline' ? 'selected' : ''}>Offline</option>
              <option value="isolir" ${isEdit && editData.status === 'isolir' ? 'selected' : ''}>Isolir</option>
            </select>
          </div>

          <div class="col-md-6"><label class="form-label">Area</label>
            <select id="s-idArea" class="form-select"><option value="">-- Pilih Area --</option>${areaOpts}</select>
          </div>

          <div class="col-12"><hr class="my-1"><small class="text-muted fw-bold"><i class="mdi mdi-api me-1"></i>Detail API Mikrotik (otomatis dari secret)</small></div>
          <div class="col-md-3"><label class="form-label">Id Api</label><input type="text" id="s-secretId" class="form-control form-control-sm bg-light" value="${isEdit ? (editData.idMikrotikUser || '') : ''}" readonly></div>
          <div class="col-md-3"><label class="form-label">Service</label><input type="text" id="s-secretService" class="form-control form-control-sm bg-light" value="${isEdit ? (editData.serviceMikrotikUser || '') : ''}" readonly></div>
          <div class="col-md-3"><label class="form-label">Profile</label><input type="text" id="s-secretProfile" class="form-control form-control-sm bg-light" value="${isEdit ? (editData.profileMikrotikUser || '') : ''}" readonly></div>
          <div class="col-md-3"><label class="form-label">Password</label><input type="text" id="s-secretPassword" class="form-control form-control-sm bg-light" value="${isEdit ? (editData.password || '') : ''}" readonly></div>
          <input type="hidden" id="s-secretName" value="${isEdit ? (editData.namaMikrotikUser || '') : ''}">

          <div class="col-12"><hr class="my-1"><small class="text-muted fw-bold"><i class="mdi mdi-calendar me-1"></i>Info Langganan</small></div>
          <div class="col-md-6"><label class="form-label">Tgl Daftar</label><input type="date" id="s-tglDaftar" class="form-control form-control-sm" value="${isEdit ? (editData.tglDaftar || '') : new Date().toISOString().slice(0,10)}"></div>
          <div class="col-md-6"><label class="form-label">Jatuh Tempo</label><input type="date" id="s-tglJatuhTempo" class="form-control form-control-sm" value="${isEdit ? (editData.tglJatuhTempo || '') : (() => { const d = new Date(); d.setMonth(d.getMonth()+1); return d.toISOString().slice(0,10); })()}"></div>

          <div class="col-md-6"><label class="form-label">Latitude</label><input type="text" id="s-lat" class="form-control" value="${lat || (isEdit ? editData.lat : '')}" readonly></div>
          <div class="col-md-6"><label class="form-label">Longitude</label><input type="text" id="s-lng" class="form-control" value="${lng || (isEdit ? editData.lng : '')}" readonly></div>
          <div class="col-12"><label class="form-label">Keterangan</label><textarea id="s-keterangan" class="form-control" rows="2">${isEdit ? (editData.keterangan || '') : ''}</textarea></div>
        </div>
      </div>
    `,
    width: 650,
    showCancelButton: true,
    confirmButtonText: `<i class="mdi mdi-content-save me-1"></i>${isEdit ? 'Update' : 'Simpan'}`,
    cancelButtonText: '<i class="mdi mdi-close me-1"></i>Batal',
    customClass: { confirmButton: 'btn btn-primary me-2', cancelButton: 'btn btn-label-secondary' },
    buttonsStyling: false,
    didOpen: () => {
      const selMk = document.getElementById('s-idMikrotik');
      const searchInput = document.getElementById('s-secret-search');
      const secretDropdown = document.getElementById('s-secret-dropdown');
      const selKat = document.getElementById('s-idKategori');
      const selPaket = document.getElementById('s-idPaket');
      let allSecrets = []; // store loaded secrets

      function clearSecretFields() {
        document.getElementById('s-ip').value = '';
        document.getElementById('s-secretId').value = '';
        document.getElementById('s-secretName').value = '';
        document.getElementById('s-secretService').value = '';
        document.getElementById('s-secretProfile').value = '';
        document.getElementById('s-secretPassword').value = '';
        document.getElementById('s-secret-value').value = '';
      }

      function getSecretIp(s) {
        return s['remote-address'] || s['local-address'] || '';
      }

      function selectSecret(s) {
        searchInput.value = `${s.name} (${s.profile || '-'})`;
        document.getElementById('s-ip').value = getSecretIp(s);
        document.getElementById('s-secretId').value = s['.id'] || '';
        document.getElementById('s-secretName').value = s.name || '';
        document.getElementById('s-secretService').value = s.service || '';
        document.getElementById('s-secretProfile').value = s.profile || '';
        document.getElementById('s-secretPassword').value = s.password || '';
        document.getElementById('s-secret-value').value = s['.id'] || '';
        secretDropdown.style.display = 'none';
      }

      function renderSecretDropdown(filter) {
        const q = (filter || '').toLowerCase();
        const filtered = q ? allSecrets.filter(s => (s.name || '').toLowerCase().includes(q) || (s.profile || '').toLowerCase().includes(q) || getSecretIp(s).toLowerCase().includes(q)) : allSecrets;
        if (!filtered.length) {
          secretDropdown.innerHTML = '<div class="secret-empty">Tidak ditemukan</div>';
        } else {
          secretDropdown.innerHTML = filtered.slice(0, 50).map((s, i) =>
            `<div class="secret-item" data-idx="${allSecrets.indexOf(s)}">
              <span class="secret-name">${s.name || '-'}</span>
              <span class="secret-info"> | ${s.profile || '-'} | IP: ${getSecretIp(s) || '-'}</span>
            </div>`
          ).join('') + (filtered.length > 50 ? `<div class="secret-empty">...dan ${filtered.length - 50} lainnya, ketik untuk filter</div>` : '');
        }
        secretDropdown.style.display = 'block';

        // Attach click handlers
        secretDropdown.querySelectorAll('.secret-item').forEach(el => {
          el.addEventListener('click', () => {
            const idx = parseInt(el.dataset.idx);
            selectSecret(allSecrets[idx]);
          });
        });
      }

      // --- Mikrotik -> Load Secrets ---
      selMk.addEventListener('change', () => {
        const mkId = selMk.value;
        allSecrets = [];
        searchInput.value = '';
        searchInput.placeholder = mkId ? 'Memuat...' : 'Pilih Mikrotik dulu';
        searchInput.disabled = !mkId;
        secretDropdown.style.display = 'none';
        clearSecretFields();
        if (!mkId) return;
        document.getElementById('s-secret-loading').style.display = 'block';
        fetch(`/select/secret-api/${mkId}`, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } })
          .then(r => r.json())
          .then(res => {
            document.getElementById('s-secret-loading').style.display = 'none';
            if (res.status && res.data && res.data.length) {
              allSecrets = res.data;
              searchInput.placeholder = `Ketik untuk cari (${allSecrets.length} secret)`;
              searchInput.disabled = false;
            } else {
              searchInput.placeholder = 'Tidak ada secret';
            }
          })
          .catch(() => {
            document.getElementById('s-secret-loading').style.display = 'none';
            searchInput.placeholder = 'Gagal memuat secret';
          });
      });

      // --- Search input events ---
      searchInput.addEventListener('focus', () => {
        // Jika secrets belum di-load tapi mikrotik sudah dipilih, load sekarang
        const mkId = selMk.value;
        if (!allSecrets.length && mkId) {
          searchInput.placeholder = 'Memuat...';
          document.getElementById('s-secret-loading').style.display = 'block';
          fetch(`/select/secret-api/${mkId}`, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } })
            .then(r => r.json())
            .then(res => {
              document.getElementById('s-secret-loading').style.display = 'none';
              if (res.status && res.data && res.data.length) {
                allSecrets = res.data;
                searchInput.placeholder = `Ketik untuk cari (${allSecrets.length} secret)`;
                renderSecretDropdown(searchInput.value);
              } else {
                searchInput.placeholder = 'Tidak ada secret';
              }
            })
            .catch(() => {
              document.getElementById('s-secret-loading').style.display = 'none';
              searchInput.placeholder = 'Gagal memuat secret';
            });
        } else if (allSecrets.length) {
          renderSecretDropdown(searchInput.value);
        }
      });
      searchInput.addEventListener('input', () => {
        clearSecretFields();
        if (allSecrets.length) renderSecretDropdown(searchInput.value);
      });
      // Close dropdown on click outside
      document.addEventListener('click', (e) => {
        if (!document.getElementById('s-secret-wrapper').contains(e.target)) {
          secretDropdown.style.display = 'none';
        }
      });

      // --- Kategori -> Filter Paket ---
      function updatePaketOptions() {
        const katId = selKat.value;
        let opts = '<option value="">-- Pilih Paket --</option>';
        if (katId) {
          paketAll.filter(p => p.idKategori == katId).forEach(p => {
            opts += `<option value="${p.id}" ${isEdit && editData.idPaket == p.id ? 'selected' : ''}>${p.nama} (${p.kode}) - Rp ${Number(p.price || 0).toLocaleString('id-ID')}</option>`;
          });
        }
        selPaket.innerHTML = opts;
      }
      selKat.addEventListener('change', updatePaketOptions);

      // --- Pre-fill on edit (tampilkan data dari DB, tanpa auto-load secrets) ---
      if (isEdit) {
        if (editData.idKategori) updatePaketOptions();
        if (editData.idMikrotik) {
          // Tampilkan nama secret dari DB tanpa trigger reload
          if (editData.namaMikrotikUser) {
            searchInput.value = `${editData.namaMikrotikUser} (${editData.profileMikrotikUser || '-'})`;
            searchInput.disabled = false;
            searchInput.placeholder = 'Klik untuk ganti, atau ganti Mikrotik Server';
          }
        }
      }
    },
    preConfirm: () => {
      const nama = document.getElementById('s-nama').value.trim();
      if (!nama) { Swal.showValidationMessage('Nama wajib diisi'); return false; }
      return {
        nama,
        wa: document.getElementById('s-wa').value.trim() || null,
        idMikrotik: document.getElementById('s-idMikrotik').value || null,
        secretId: document.getElementById('s-secretId').value || null,
        secretName: document.getElementById('s-secretName').value || null,
        secretService: document.getElementById('s-secretService').value || null,
        secretProfile: document.getElementById('s-secretProfile').value || null,
        secretPassword: document.getElementById('s-secretPassword').value || null,
        ip: document.getElementById('s-ip').value.trim() || null,
        idKategori: document.getElementById('s-idKategori').value || null,
        idPaket: document.getElementById('s-idPaket').value || null,
        idOdp: document.getElementById('s-idOdp').value || null,
        portOdp: parseInt(document.getElementById('s-portOdp').value) || null,
        latitude: parseFloat(document.getElementById('s-lat').value),
        longitude: parseFloat(document.getElementById('s-lng').value),
        keterangan: document.getElementById('s-keterangan').value.trim() || null,
        status: document.getElementById('s-status').value || 'online',
        idArea: document.getElementById('s-idArea').value || null,
        tglDaftar: document.getElementById('s-tglDaftar').value || null,
        tglJatuhTempo: document.getElementById('s-tglJatuhTempo').value || null,
      };
    }
  }).then(result => {
    if (!result.isConfirmed) return;
    const url = isEdit ? `${BASE}/client/${editData.id}` : `${BASE}/client`;
    const method = isEdit ? 'PUT' : 'POST';
    apiFetch(url, method, result.value)
      .then(() => { showToast(`Client berhasil ${isEdit ? 'diupdate' : 'ditambahkan'}`, 'success'); loadMapData(); loadSelectOptions(); })
      .catch(e => showToast('Gagal: ' + e.message, 'error'));
  });
}

// =============== AREA POLYGONS ===============
function addAreaPolygon(area) {
  if (!area.coordinates || area.coordinates.length < 3) return;
  const latlngs = area.coordinates.map(c => [c.lat, c.lng]);
  const color = area.color || '#696cff';

  const polygon = L.polygon(latlngs, {
    color: color, weight: 2, fillColor: color, fillOpacity: 0.15, dashArray: '6, 4'
  });

  const center = polygon.getBounds().getCenter();
  const label = L.marker(center, {
    icon: L.divIcon({ className: 'area-label', html: `<span>${area.name}</span>`, iconSize: [120, 20], iconAnchor: [60, 10] }),
    interactive: false
  });

  polygon.bindPopup(`
    <div style="min-width:200px;">
      <h6 class="mb-2"><i class="mdi mdi-vector-polygon me-1" style="color:${color}"></i>${area.name}</h6>
      <table class="table table-sm mb-0" style="font-size:12px;">
        <tr><td class="text-muted"><strong>Kode</strong></td><td>${area.code_area || '-'}</td></tr>
        <tr><td class="text-muted"><strong>Alamat</strong></td><td>${area.address || '-'}</td></tr>
      </table>
      <div class="popup-actions">
        <button onclick="editAreaPolygon('${area.id}')" class="popup-btn popup-btn-edit"><i class="mdi mdi-pencil"></i> Edit</button>
        <button onclick="deleteArea('${area.id}','${(area.name||'').replace(/'/g,"\\'")}')" class="popup-btn popup-btn-delete"><i class="mdi mdi-delete"></i> Hapus</button>
      </div>
    </div>
  `, { maxWidth: 280 });

  polygon._areaData = area;
  layerGroups.area.addLayer(polygon);
  layerGroups.area.addLayer(label);
}

function startAreaDrawMode() {
  const drawHandler = new L.Draw.Polygon(map, {
    allowIntersection: false,
    showArea: true,
    shapeOptions: { color: '#9c27b0', weight: 2, fillOpacity: 0.2 }
  });
  drawHandler.enable();
  showToast('Klik pada peta untuk menggambar area. Klik titik pertama untuk menutup.', 'info');

  function onCreated(e) {
    map.off(L.Draw.Event.CREATED, onCreated);
    const coords = e.layer.getLatLngs()[0].map(ll => ({ lat: ll.lat, lng: ll.lng }));
    showAreaModal(null, coords);
  }
  map.on(L.Draw.Event.CREATED, onCreated);
}

function showAreaModal(editData, coordinates) {
  const isEdit = !!editData;

  // Ambil area tanpa koordinat untuk dropdown "pilih area existing"
  const allAreas = selectOptions.areas || [];
  // Area existing = yang ada di DB tapi belum punya polygon (id ada di selectOptions tapi tidak ada di layerGroups.area)
  const drawnIds = [];
  layerGroups.area.eachLayer(l => { if (l._areaData) drawnIds.push(l._areaData.id); });
  const undrawnAreas = allAreas.filter(a => !drawnIds.includes(a.id));
  const existingOpts = undrawnAreas.map(a => `<option value="${a.id}" data-name="${a.name}" data-code="${a.code_area || ''}">${a.name} (${a.code_area || '-'})</option>`).join('');
  const showExisting = !isEdit && undrawnAreas.length > 0;

  Swal.fire({
    title: `<i class="mdi mdi-vector-polygon me-2" style="color:#9c27b0"></i>${isEdit ? 'Edit' : 'Tambah'} Area`,
    html: `
      <div class="text-start">
        <div class="row g-3">
          ${showExisting ? `
          <div class="col-12">
            <label class="form-label">Pilih Area Existing <small class="text-muted">(opsional)</small></label>
            <select id="s-area-existing" class="form-select">
              <option value="">-- Buat Area Baru --</option>
              ${existingOpts}
            </select>
          </div>
          <div class="col-12"><hr class="my-0"></div>
          ` : ''}
          <div class="col-md-6"><label class="form-label">Nama Area <span class="text-danger">*</span></label><input type="text" id="s-area-name" class="form-control" value="${isEdit ? editData.name : ''}"></div>
          <div class="col-md-6"><label class="form-label">Kode Area <span class="text-danger">*</span></label><input type="text" id="s-area-code" class="form-control" value="${isEdit ? (editData.code_area||'') : ''}" placeholder="AR-01"></div>
          <div class="col-12"><label class="form-label">Alamat</label><input type="text" id="s-area-address" class="form-control" value="${isEdit ? (editData.address||'') : ''}"></div>
          <div class="col-md-6"><label class="form-label">Warna</label><input type="color" id="s-area-color" class="form-control form-control-color" value="${isEdit ? (editData.color||'#696cff') : '#696cff'}" style="height:38px;width:100%;"></div>
          <div class="col-md-6"><label class="form-label">Titik Polygon</label><div class="form-control-plaintext text-muted">${coordinates.length} titik</div></div>
        </div>
      </div>
    `,
    width: 500,
    showCancelButton: true,
    confirmButtonText: `<i class="mdi mdi-content-save me-1"></i>${isEdit ? 'Update' : 'Simpan'}`,
    cancelButtonText: '<i class="mdi mdi-close me-1"></i>Batal',
    customClass: { confirmButton: 'btn btn-primary me-2', cancelButton: 'btn btn-label-secondary' },
    buttonsStyling: false,
    didOpen: () => {
      const selExisting = document.getElementById('s-area-existing');
      if (selExisting) {
        selExisting.addEventListener('change', function() {
          const opt = this.selectedOptions[0];
          if (this.value) {
            document.getElementById('s-area-name').value = opt.dataset.name || '';
            document.getElementById('s-area-code').value = opt.dataset.code || '';
            document.getElementById('s-area-name').readOnly = true;
            document.getElementById('s-area-code').readOnly = true;
          } else {
            document.getElementById('s-area-name').value = '';
            document.getElementById('s-area-code').value = '';
            document.getElementById('s-area-name').readOnly = false;
            document.getElementById('s-area-code').readOnly = false;
          }
        });
      }
    },
    preConfirm: () => {
      const name = document.getElementById('s-area-name').value.trim();
      const code = document.getElementById('s-area-code').value.trim();
      if (!name || !code) { Swal.showValidationMessage('Nama dan Kode wajib diisi'); return false; }
      const selExisting = document.getElementById('s-area-existing');
      return {
        existingId: selExisting ? selExisting.value : null,
        name,
        code_area: code,
        address: document.getElementById('s-area-address').value.trim() || null,
        color: document.getElementById('s-area-color').value,
        coordinates: coordinates,
      };
    }
  }).then(result => {
    if (!result.isConfirmed) return;
    const data = result.value;
    const existingId = data.existingId;
    delete data.existingId;

    // Jika pilih area existing → PUT update, jika baru → POST create
    const useUpdate = isEdit || existingId;
    const areaId = isEdit ? editData.id : existingId;
    const url = useUpdate ? `${BASE}/area/${areaId}` : `${BASE}/area`;
    const method = useUpdate ? 'PUT' : 'POST';

    apiFetch(url, method, data)
      .then(() => { showToast(`Area berhasil ${useUpdate ? 'diupdate' : 'ditambahkan'}`, 'success'); loadMapData(); loadSelectOptions(); })
      .catch(e => showToast('Gagal: ' + e.message, 'error'));
  });
}

function editAreaPolygon(areaId) {
  map.closePopup();
  // Find area data from current layer
  let areaData = null;
  layerGroups.area.eachLayer(l => {
    if (l._areaData && l._areaData.id === areaId) areaData = l._areaData;
  });
  if (areaData) {
    showAreaModal(areaData, areaData.coordinates);
  }
}

function deleteArea(areaId, areaName) {
  map.closePopup();
  Swal.fire({
    title: 'Hapus Area?',
    text: `Area "${areaName}" akan dihapus permanen.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: '<i class="mdi mdi-delete me-1"></i>Hapus',
    cancelButtonText: '<i class="mdi mdi-close me-1"></i>Batal',
    customClass: { confirmButton: 'btn btn-danger me-2', cancelButton: 'btn btn-label-secondary' },
    buttonsStyling: false,
  }).then(result => {
    if (!result.isConfirmed) return;
    apiFetch(`${BASE}/area/${areaId}`, 'DELETE')
      .then(() => { showToast('Area berhasil dihapus', 'success'); loadMapData(); })
      .catch(e => showToast('Gagal: ' + e.message, 'error'));
  });
}

// =============== EDIT DEVICE ===============
function editDevice(type, id) {
  map.closePopup();
  apiFetch(`${BASE}/${type}/${id}`, 'GET')
    .then(res => {
      const d = res.data;
      if (type === 'olt') showOLTModal(d, null, null);
      else if (type === 'odc') showODCModal(d, null, null);
      else if (type === 'odp') showODPModal(d, null, null);
      else if (type === 'client') showClientModal(d, null, null);
    })
    .catch(e => showToast('Gagal memuat data: ' + e.message, 'error'));
}

// =============== DELETE DEVICE ===============
function deleteDevice(type, id) {
  map.closePopup();
  Swal.fire({
    title: `Hapus ${type.toUpperCase()}?`,
    text: 'Data yang dihapus tidak bisa dikembalikan!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: '<i class="mdi mdi-delete me-1"></i>Ya, Hapus!',
    cancelButtonText: '<i class="mdi mdi-close me-1"></i>Batal',
    customClass: { confirmButton: 'btn btn-danger me-2', cancelButton: 'btn btn-label-secondary' },
    buttonsStyling: false
  }).then(result => {
    if (!result.isConfirmed) return;
    apiFetch(`${BASE}/${type}/${id}`, 'DELETE')
      .then(() => { showToast(`${type.toUpperCase()} berhasil dihapus`, 'success'); loadMapData(); loadSelectOptions(); })
      .catch(e => showToast('Gagal hapus: ' + e.message, 'error'));
  });
}

// =============== IMPORT MODAL ===============
document.addEventListener('DOMContentLoaded', function() {
  document.getElementById('btnImport').addEventListener('click', showImportModal);
});

function showImportModal() {
  apiFetch(`${BASE}/unmapped-items`, 'GET').then(res => {
    const d = res.data;
    const oltList = (d.olts || []).map(o => `
      <div class="import-item">
        <div><div class="import-item-name">${o.nama}</div><div class="import-item-detail">Kode: ${o.kode} | IP: ${o.ip || '-'}</div></div>
        <button class="btn btn-sm btn-primary" onclick="startPlaceImport('olt',${o.id},'${o.nama}')"><i class="mdi mdi-map-marker-plus"></i> Place</button>
      </div>
    `).join('') || '<p class="text-muted text-center py-3">Semua OLT sudah dipetakan</p>';

    const odcList = (d.odcs || []).map(o => `
      <div class="import-item">
        <div><div class="import-item-name">${o.nama}</div><div class="import-item-detail">Kode: ${o.kode} | Port: ${o.port || 0}</div></div>
        <button class="btn btn-sm btn-primary" onclick="startPlaceImport('odc',${o.id},'${o.nama}')"><i class="mdi mdi-map-marker-plus"></i> Place</button>
      </div>
    `).join('') || '<p class="text-muted text-center py-3">Semua ODC sudah dipetakan</p>';

    const odpList = (d.odps || []).map(o => `
      <div class="import-item">
        <div><div class="import-item-name">${o.nama}</div><div class="import-item-detail">Kode: ${o.kode} | Port: ${o.port || 0} | Tipe: ${o.tipe || 'HTB'}</div></div>
        <button class="btn btn-sm btn-primary" onclick="startPlaceImport('odp',${o.id},'${o.nama}')"><i class="mdi mdi-map-marker-plus"></i> Place</button>
      </div>
    `).join('') || '<p class="text-muted text-center py-3">Semua ODP sudah dipetakan</p>';

    const clientList = (d.clients || []).map(o => `
      <div class="import-item">
        <div><div class="import-item-name">${o.nama}</div><div class="import-item-detail">${o.info || '-'}</div></div>
        <button class="btn btn-sm btn-primary" onclick="startPlaceImport('client',${o.id},'${o.nama}')"><i class="mdi mdi-map-marker-plus"></i> Place</button>
      </div>
    `).join('') || '<p class="text-muted text-center py-3">Semua Client sudah dipetakan</p>';

    Swal.fire({
      title: '<i class="mdi mdi-database-import me-2"></i>Import Device',
      html: `
        <div class="text-start">
          <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-olt">OLT <span class="badge bg-danger">${d.olts?.length || 0}</span></a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-odc">ODC <span class="badge bg-warning">${d.odcs?.length || 0}</span></a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-odp">ODP <span class="badge bg-success">${d.odps?.length || 0}</span></a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-client">Client <span class="badge bg-info">${d.clients?.length || 0}</span></a></li>
          </ul>
          <div class="tab-content pt-3">
            <div class="tab-pane fade show active" id="tab-olt"><div class="import-list">${oltList}</div></div>
            <div class="tab-pane fade" id="tab-odc"><div class="import-list">${odcList}</div></div>
            <div class="tab-pane fade" id="tab-odp"><div class="import-list">${odpList}</div></div>
            <div class="tab-pane fade" id="tab-client"><div class="import-list">${clientList}</div></div>
          </div>
        </div>
      `,
      width: 550,
      showConfirmButton: false,
      showCancelButton: true,
      cancelButtonText: '<i class="mdi mdi-close me-1"></i>Tutup',
      customClass: { cancelButton: 'btn btn-label-secondary' },
      buttonsStyling: false
    });
  }).catch(e => showToast('Gagal memuat data import: ' + e.message, 'error'));
}

function startPlaceImport(type, id, nama) {
  Swal.close();
  placingImport = { type, id };
  document.getElementById('map').classList.add('adding-marker');

  let ind = document.getElementById('addModeIndicator');
  if (!ind) {
    ind = document.createElement('div');
    ind.id = 'addModeIndicator';
    ind.className = 'add-mode-indicator';
    document.body.appendChild(ind);
  }
  ind.innerHTML = `<i class="mdi mdi-map-marker-plus me-2"></i>Klik peta untuk menempatkan <strong>${nama}</strong>`;
  ind.style.display = 'block';

  showToast(`Klik peta untuk menempatkan ${nama}`, 'info');
}

// =============== ESC KEY TO CANCEL ===============
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    if (addingMarkerMode || placingImport) cancelAddMode();
    if (editingRoute) disableRouteEdit(true);
  }
});

// =============== UTILITY FUNCTIONS ===============
function apiFetch(url, method, body) {
  const opts = {
    method: method || 'GET',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
  };
  if (body && method !== 'GET') opts.body = JSON.stringify(body);
  return fetch(url, opts).then(r => {
    if (!r.ok) return r.json().then(d => { throw new Error(d.message || `HTTP ${r.status}`); });
    return r.json();
  });
}

function showToast(msg, type) {
  let container = document.getElementById('toastContainer');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container';
    document.body.appendChild(container);
  }
  const icons = { success: 'check-circle', error: 'alert-circle', info: 'information', warning: 'alert' };
  const t = document.createElement('div');
  t.className = `custom-toast toast-${type || 'info'}`;
  t.innerHTML = `<i class="mdi mdi-${icons[type] || 'information'}"></i> ${msg}`;
  container.appendChild(t);
  setTimeout(() => { if (t.parentNode) t.parentNode.removeChild(t); }, 3100);
}

// =============== INIT ON LOAD ===============
document.addEventListener('DOMContentLoaded', initMap);
</script>
@endsection
