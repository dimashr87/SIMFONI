<?php

namespace App\Http\Controllers;

use App\Models\Arc;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ArcImport;
use Carbon\Carbon;

class ArcController extends Controller
{
    // Tampilkan halaman input ARC
    public function index()
    {
        $arcs = Arc::all()->map(function ($arc) {
            $eventDate = Carbon::parse($arc->tanggal_kegiatan)->toDateString();
            $today = now()->toDateString();

            if ($eventDate == $today) {
                $arc->status = 'processing'; // hari ini
            } elseif ($eventDate < $today) {
                $arc->status = 'done'; // sudah lewat
            } else {
                $arc->status = 'pending'; // akan datang
            }

            // untuk tampilan tanggal tanpa jam
            $arc->tanggal_format = Carbon::parse($arc->tanggal_kegiatan)->format('Y-m-d');

            return $arc;
        });
        return view('arc.index', compact('arcs'));
    }


    // Simpan data ARC manual
    public function store(Request $request)
    {
        $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'tanggal_kegiatan' => 'required|date',
        ]);

        Arc::create($request->all());

        return redirect()->route('arc.index')->with('success', 'Kegiatan berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $arc = Arc::findOrFail($id);
        return view('arc.edit', compact('arc'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'tanggal_kegiatan' => 'required|date',
        ]);

        $arc = Arc::findOrFail($id);
        $arc->update($request->all());

        return redirect()->route('arc.index')->with('success', 'Data ARC berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $arc = Arc::findOrFail($id);
        $arc->delete();

        return redirect()->route('arc.index')->with('success', 'Data ARC berhasil dihapus!');
    }

    // Import dari Excel
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        Excel::import(new ArcImport, $request->file('file'));

        return redirect()->route('arc.index')->with('success', 'Data berhasil diimport.');
    }

    public function downloadTemplate()
    {
        $filePath = public_path('template_arc.xlsx');

        if (file_exists($filePath)) {
            return response()->download($filePath, 'template_arc.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        return redirect()->route('arc.index')->with('error', 'File template tidak ditemukan.');
    }

    public function show($id)
    {
        $arc = Arc::findOrFail($id);
        return view('arc.show', compact('arc'));
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;

        if ($ids && count($ids) > 0) {
            Arc::whereIn('id', $ids)->delete();
            return redirect()->route('arc.index')->with('success', 'ARC Terpilih berhasil dihapus.');
        }

        return redirect()->route('arc.index')->with('error', 'Tidak ada data yang dipilih.');
    }
}