<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SummarizationService;
use App\Models\Fenomena;


class SummarizationController extends Controller
{
    protected $summarizationService;

    public function __construct(SummarizationService $summarizationService)
    {
        $this->summarizationService = $summarizationService;
    }

    // Display the summarization form page
    public function index()
    {
        return view('summarize.index');
    }

    // Handle the text input and return the summary
    public function summarizeTema(Request $request, $id)
    {
        // Ambil query params
        $tanggalAwal = $request->query('tanggal_awal');
        $tanggalAkhir = $request->query('tanggal_akhir');

        // Query dasar
        $query = Fenomena::where('tema', $id)
            ->where('approval', 1);

        // Filter berdasarkan tanggal
        if ($tanggalAwal && $tanggalAkhir) {
            $query->whereBetween('tanggal_awal', [$tanggalAwal, $tanggalAkhir]);
        } elseif ($tanggalAwal) {
            $query->whereDate('tanggal_awal', $tanggalAwal);
        }

        $fenomena = $query->get();

        if ($fenomena->isNotEmpty()) {
            // Gabungkan semua resume
            $textToSummarize = $fenomena->pluck('resume')->implode(' ');

            // Tambahkan tema di awal
            $textToSummarize = $id . ' ' . $textToSummarize;

            // Panggil layanan summarization
            $summary = $this->summarizationService->summarize($textToSummarize);

            return response()->json([
                'summary'   => $summary,
                'request'   => [
                    'tema'          => $id,
                    'tanggal_awal'  => $tanggalAwal,
                    'tanggal_akhir' => $tanggalAkhir,
                ],
                'fenomena'  => $fenomena,
            ]);
        }

        return response()->json([
            'summary' => 'Tema tidak ditemukan atau tidak ada resume terkait.',
            'request' => [
                'tema'          => $id,
                'tanggal_awal'  => $tanggalAwal,
                'tanggal_akhir' => $tanggalAkhir,
            ]
        ], 404);
    }

    public function showSummarizationForm()
    {
        // Ambil semua tema dari database
        $temas = Fenomena::distinct()->pluck('tema');

        // Kirimkan tema ke view
        return view('summarize.index', compact('temas'));
    }
}
