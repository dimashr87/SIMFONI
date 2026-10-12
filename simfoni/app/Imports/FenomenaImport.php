<?php

namespace App\Imports;

use App\Models\Fenomena;
use App\Models\Tema; // Pastikan Anda mengimpor model Tema
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class FenomenaImport implements ToModel, WithStartRow
{
    protected $niplama;

    public function __construct($niplama)
    {
        $this->niplama = $niplama; // Simpan niplama dari session
    }

    public function model(array $row)
    {
        // Ambil supervisor berdasarkan tema
        $tema = $row[6]; // Pastikan index ini sesuai dengan posisi tema dalam array

        return new Fenomena([
            'tanggal_awal' => $row[0],
            'tanggal_akhir' => $row[1],
            'sumber' => $row[2],
            'sumberportal' => $row[3],
            'cakupan' => $row[4],
            'cakupankabkot' => $row[5],
            'subjek' => $row[6],
            'tema' => $tema,
            'periodicity' => $row[8],
            'resume' => $row[9],
            'arah' => $row[10],
            'linkbukti' => $row[11],
            'niplama' => $this->niplama, // Menggunakan niplama dari session
            'tanggalcreated' => now(),
        ]);
    }

    // Menetapkan baris awal pembacaan dari baris kedua
    public function startRow(): int
    {
        return 2;
    }
}
