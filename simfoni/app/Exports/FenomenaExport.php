<?php

namespace App\Exports;

use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FenomenaExport implements FromCollection, WithHeadings
{
    protected $fenomena;

    public function __construct($fenomena)
    {
        $this->fenomena = $fenomena;
    }

    public function collection()
    {
        return $this->fenomena->map(function ($item) {
            return [
                'tanggal_awal'    => $item->tanggal_awal,
                'tanggal_akhir'   => $item->tanggal_akhir,
                'sumber'          => $item->sumber,
                'sumber_portal'   => $item->sumber_portal,
                'cakupan'         => $item->cakupan,
                'cakupankabkot'   => $item->cakupankabkot,
                'subjek'          => $item->subjek,
                'tema'            => $item->tema,
                'perbandingan'    => $item->perbandingan,
                'resume'          => $item->resume,
                'arah'            => $item->arah,
                'link_bukti'      => $item->linkbukti,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Tanggal Awal',
            'Tanggal Akhir',
            'Sumber',
            'Sumber Portal',
            'Cakupan',
            'Cakupan Kab/Kota',
            'Subject',
            'Tema',
            'Perbandingan',
            'Resume',
            'Arah',
            'Link Bukti',
        ];
    }
}
