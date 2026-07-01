<?php

namespace App\Exports;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UserExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithChunkReading
{
  protected $areas;

  public function __construct()
  {
    $this->areas = DB::table('lamtim_areas')->pluck('name', 'id');
  }

  public function query()
  {
    return User::query()
      ->with([
        'user_detail',
        'user_mikrotik.mikrotik:id,nama',
        'user_mikrotik.kategori:id,nama',
        'user_mikrotik.paket:id,nama,price',
        'user_mikrotik.olt:id,nama',
        'user_mikrotik.odc:id,nama',
        'user_mikrotik.odp:id,nama',
      ])
      ->where('idRole', 5)
      ->orderBy('name');
  }

  public function chunkSize(): int
  {
    return 500;
  }

  public function headings(): array
  {
    return [
      'No',
      'Nama',
      'WA',
      'Jenis Kelamin',
      'Jenis Identitas',
      'No Identitas',
      'Alamat',
      'Area',
      'Tanggal Daftar',
      'Jatuh Tempo',
      'Status PPN',
      'Status Tagihan',
      'Jenis Bayar',
      'Google Map',
      'Keterangan Pelanggan',
      'Kategori',
      'Paket',
      'Harga Paket',
      'Mikrotik',
      'Username PPPoE',
      'Password PPPoE',
      'Service',
      'Profile',
      'Status Isolir',
      'IP Local',
      'IP Remote',
      'OLT',
      'ODC',
      'ODP',
      'Port ODP',
      'Mode IP',
      'Latitude',
      'Longitude',
      'Tanggal Diisolir',
      'Tanggal Dibuka',
      'Tanggal Off',
      'Diskon',
      'Upload Speed',
      'Download Speed',
      'Wifi Name',
      'Kabel',
      'Keterangan Mikrotik',
      'Status',
      'Status User',
      'Terdaftar Sistem',
    ];
  }

  public function map($row): array
  {
    static $no = 0;
    $no++;

    $detail = $row->user_detail;
    $mikrotik = $row->user_mikrotik;

    return [
      $no,
      $row->name,
      $row->wa,
      $this->genderLabel($detail->js ?? null),
      $detail->identitas ?? '-',
      $detail->noIdentitas ?? '-',
      $detail->alamat ?? '-',
      $detail ? ($this->areas[$detail->idArea] ?? '-') : '-',
      $detail && $detail->tglDafatar ? Carbon::parse($detail->tglDafatar)->format('d-m-Y') : '-',
      $detail && $detail->tglJatuhTempo ? Carbon::parse($detail->tglJatuhTempo)->format('d-m-Y') : '-',
      $this->yesNoLabel($detail->statusPpn ?? null),
      $this->yesNoLabel($detail->statusTagihan ?? null),
      $this->jenisBayarLabel($detail->jenisBayar ?? null),
      $detail->googleMap ?? '-',
      $detail->keterangan ?? '-',
      $mikrotik->kategori->nama ?? '-',
      $mikrotik->paket->nama ?? '-',
      $mikrotik && $mikrotik->paket ? $mikrotik->paket->price : '-',
      $mikrotik->mikrotik->nama ?? '-',
      $mikrotik->namaMikrotikUser ?? '-',
      $mikrotik->password ?? '-',
      $mikrotik->serviceMikrotikUser ?? '-',
      $mikrotik->profileMikrotikUser ?? '-',
      $this->isolirLabel($mikrotik->statusIsolir ?? null),
      $mikrotik->localAdress ?? '-',
      $mikrotik->remoteAdress ?? '-',
      $mikrotik->olt->nama ?? '-',
      $mikrotik->odc->nama ?? '-',
      $mikrotik->odp->nama ?? '-',
      $mikrotik->portOdp ?? '-',
      $mikrotik->modeIp ?? '-',
      $mikrotik->latitude ?? '-',
      $mikrotik->longitude ?? '-',
      $mikrotik && $mikrotik->tglDiIsolir ? Carbon::parse($mikrotik->tglDiIsolir)->format('d-m-Y') : '-',
      $mikrotik && $mikrotik->tglDiBuka ? Carbon::parse($mikrotik->tglDiBuka)->format('d-m-Y') : '-',
      $mikrotik && $mikrotik->tglDiOff ? Carbon::parse($mikrotik->tglDiOff)->format('d-m-Y') : '-',
      $mikrotik->diskon ?? '-',
      $mikrotik->upload_speed ?? '-',
      $mikrotik->download_speed ?? '-',
      $mikrotik->wifi_name ?? '-',
      $mikrotik->kabel ?? '-',
      $mikrotik->keterangan ?? '-',
      $mikrotik->status ?? '-',
      $row->isActive ? 'Aktif' : 'Nonaktif',
      Carbon::parse($row->created_at)->format('d-m-Y H:i:s'),
    ];
  }

  private function genderLabel($value)
  {
    return match ($value) {
      'L' => 'Laki-laki',
      'P' => 'Perempuan',
      default => '-',
    };
  }

  private function yesNoLabel($value)
  {
    if ($value === null) return '-';
    return $value ? 'Aktif' : 'Tidak';
  }

  private function jenisBayarLabel($value)
  {
    return match ((string) $value) {
      '1' => 'Pascabayar',
      '2' => 'Prabayar',
      default => '-',
    };
  }

  private function isolirLabel($value)
  {
    if ($value === null) return '-';
    return $value ? 'Isolir' : 'Normal';
  }

  public function styles(Worksheet $sheet)
  {
    return [
      1 => ['font' => ['bold' => true]],
    ];
  }
}
