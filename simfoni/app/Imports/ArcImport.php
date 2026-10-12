<?php

namespace App\Imports;

use App\Models\Arc;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;

class ArcImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        if (empty($row['nama_kegiatan']) || empty($row['tanggal_kegiatan'])) {
            return null; // skip kalau ada row kosong
        }

        $tanggal = $row['tanggal_kegiatan'];

        // Kalau nilai tanggal dari Excel berupa angka serial
        if (is_numeric($tanggal)) {
            $tanggal = Carbon::instance(Date::excelToDateTimeObject($tanggal))->toDateString(); 
            // hasil "YYYY-MM-DD"
        } else {
            // Kalau sudah string (misalnya "2025-09-01")
            $tanggal = Carbon::parse($tanggal)->toDateString();
        }

        return new Arc([
            'nama_kegiatan'   => $row['nama_kegiatan'],
            'tanggal_kegiatan'=> $tanggal
        ]);
    }

}
