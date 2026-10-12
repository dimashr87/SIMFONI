<?php

namespace App\Http\Controllers;

use App\Models\Fenomena;
use App\Notifications\FenomenaNotification;
use Illuminate\Support\Facades\Notification;
use App\Models\SumberFenomena;
use App\Models\Cakupan;
use App\Models\Kabkot;
use App\Models\PortalBerita;
use App\Models\Subjek;
use App\Models\Tema;
use App\Models\User;
use App\Providers\SummarizationService;
use Illuminate\Http\Request;
use App\Imports\FenomenaImport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Exports\FenomenaExport;

class FenomenaController extends Controller
{

    
    public function index(Request $request)
    {
        // Ambil niplamasupervisor dari session atau dari user yang sedang login
        $niplama = $request->session()->get('niplama');

        // Dapatkan fenomena yang sesuai dengan niplamasupervisor
        $fenomena = Fenomena::where('niplama', $niplama)->get();
        return view('fenomena.index', compact('fenomena'));
    }
    
    public function approval(Request $request)
    {
        // Ambil niplamasupervisor dari session atau dari user yang sedang login
        $niplama = $request->session()->get('niplama');
        $subjekIds = Subjek::where('supervisor', $niplama)->pluck('subjek');
        $fenomena = Fenomena::whereIn('subjek', $subjekIds)->get();

        return view('fenomena.approval', compact('fenomena'));
    }

        public function pencarian()
    {
        $fenomena = Fenomena::all();
        return view('fenomena.pencarian', compact('fenomena'));
    }
    
    public function downloadTemplate()
    {
        $filePath = public_path('templateimport.xlsx');

        if (file_exists($filePath)) {
            return response()->download($filePath, 'template_fenomena.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        return redirect()->route('fenomena.index')->with('error', 'File template tidak ditemukan.');
    }

    public function create()
    {
        // Ambil data dari tabel-tabel terkait
        $sumberFenomena = SumberFenomena::all();
        $cakupan = Cakupan::all();
        $subjects = Subjek::select('subjek')->distinct()->get(); // Ambil data subjek
        $portalBeritas = PortalBerita::all();
        $kabkots = Kabkot::all();

        // Kirim data ke view
        return view('fenomena.create', compact('sumberFenomena', 'cakupan', 'subjects', 'portalBeritas', 'kabkots'));
    }

    public function store(Request $request)
    {
        // Validasi input, tanpa 'tanggalcreated'
        $request->validate([
            'tanggal_awal' => 'required|max:10',
            'tanggal_akhir' => 'max:10',
            'sumber' => 'required|max:100',
            'sumberportal' => 'max:100',
            'cakupan' => 'required|max:100',
            'cakupankabkot' => 'max:100',
            'subjek' => 'required|max:100',
            'tema' => 'required|max:200',
            'resume' => ['required', function ($attribute, $value, $fail) {
                if (str_word_count($value) > 150) {
                    $fail('Kolom ' . $attribute . ' tidak boleh lebih dari 150 kata.');
                }
            }],
            'linkbukti' => 'required|max:200',
        ]);

        // Ambil niplama dari session
        $niplama = $request->session()->get('niplama');

        // Ambil niplamasupervisor dari tabel tema berdasarkan tema yang dipilih
        $tema = Tema::where('tema', $request->input('tema'))->first();
        $niplamasupervisor = $tema ? $tema->supervisor : null;
        // Gabungkan niplama dan tanggalcreated dengan data request lainnya
        $data = $request->all();
        $data['niplama'] = $niplama;
        $data['supervisor'] = $niplamasupervisor;
        $data['tanggalcreated'] = now();

        // ubah periodicity array → string "qtq,mtm,yoy"
        if ($request->has('periodicity')) {
            $data['periodicity'] = implode(',', $request->input('periodicity'));
        }

        Fenomena::create($data);

        return redirect()->route('fenomena.index')
            ->with('success', 'Fenomena created successfully.');
    }

    public function show(Fenomena $fenomena)
    {
        return view('fenomena.show', compact('fenomena'));
    }
    
    public function user()
    {
        $users = User::all();
        return view('pages.user-management', compact('users'));
    }
    
    public function approve($id, $action, Request $request)
    {
        $fenomena = Fenomena::find($id);

        if ($fenomena) {
            if ($action === 'approve') {
                // Set approval to 1 (approved)
                $fenomena->approval = 1;
            } else if ($action === 'reject') {
                // Set approval to 0 (rejected) and save the reason
                $fenomena->approval = 0;
                $fenomena->rejection_reason = $request->reason;
                if ($request->reason == 'Lainnya') {
                    $fenomena->rejection_reason = $request->other_reason;
                }
            }

            // Save the changes
            $fenomena->save();

            // Return success message
            $message = ($action === 'approve') ? 'Fenomena berhasil disetujui.' : 'Fenomena berhasil ditolak dengan alasan: ' . $fenomena->rejection_reason;
            return redirect()->route('fenomena.approval')->with('success', $message);
        } else {
            return redirect()->route('fenomena.approval')->with('error', 'Data tidak ditemukan.');
        }
    }


    public function edit($id)
    {
        $fenomena = Fenomena::findOrFail($id);
        // Ambil data dari tabel-tabel terkait
        $sumberFenomena = SumberFenomena::all();
        $cakupan = Cakupan::all();
        $subjects = Subjek::select('subjek')->distinct()->get();; // Ambil data subjek
        $portalBeritas = PortalBerita::all();
        $kabkots = Kabkot::all();
        
        // Kirim data ke view
        return view('fenomena.edit', compact('fenomena', 'sumberFenomena', 'cakupan','subjects', 'portalBeritas', 'kabkots'));
    }

   public function update(Request $request, $id)
    {
        // Validasi input, sesuai dengan yang ada di store
        $request->validate([
            'tanggal_awal' => 'required|max:10',
            'tanggal_akhir' => 'max:10',
            'sumber' => 'required|max:100',
            'sumberportal' => 'max:100',
            'cakupan' => 'required|max:100',
            'cakupankabkot' => 'max:100',
            'subjek' => 'required|max:100',
            'resume' => ['required', function ($attribute, $value, $fail) {
                if (str_word_count($value) > 150) {
                    $fail('Kolom ' . $attribute . ' tidak boleh lebih dari 150 kata.');
                }
            }],
            'linkbukti' => 'required|max:200',
        ]);

        // Temukan fenomena yang akan diupdate
        $fenomena = Fenomena::findOrFail($id);

        // Gabungkan data yang ada di request dengan data yang sudah ada di model
        $data = $request->all();
        $data['tanggalcreated'] = $fenomena->tanggalcreated; // Pertahankan tanggalcreated yang ada

        // ubah periodicity array → string "qtq,mtm,yoy"
        if ($request->has('periodicity')) {
            $data['periodicity'] = implode(',', $request->input('periodicity'));
        }

        // Update data ke database
        $fenomena->update($data);

        return redirect()->route('fenomena.index')
            ->with('success', 'Fenomena updated successfully.');
    }


    public function destroy($id)
    {
        $fenomenum = Fenomena::find($id);

        if (!$fenomenum) {
            return redirect()->route('fenomena.index')->with('error', 'Fenomena tidak ditemukan.');
        }

        if ($fenomenum->delete()) {
            return redirect()->route('fenomena.index')->with('success', 'Fenomena berhasil dihapus');
        } else {
            return redirect()->route('fenomena.index')->with('error', 'Fenomena gagal dihapus');
        }
    }

    public function getThemes($subject)
    {
        // Ambil data tema berdasarkan subject ID dengan join ke tabel subjek
        $themes = Tema::join('subjek', 'tema.idsubjek', '=', 'subjek.id')
                    ->where('subjek.subjek', $subject)
                    ->select('tema.*', 'subjek.subjek as subjek_nama')
                    ->get();

        return response()->json($themes);
    }


    public function approveEvent($id)
    {
        $event = Fenomena::find($id);
        $event->approval = '1';
        $event->save();

        foreach ($event->users as $user) {
            $user->notify(new \App\Notifications\EventNotification($event, 'approved'));
        }

        return redirect()->back()->with('success', 'Fenomena approved successfully!');
    }

    public function rejectEvent($id)
    {
    $event = Fenomena::find($id);
        $event->approval = '0';
        $event->save();

        foreach ($event->users as $user) {
            $user->notify(new \App\Notifications\EventNotification($event, 'rejected'));
        }

        return redirect()->back()->with('success', 'Fenomena rejected successfully!');
    }

    public function getNotifications()
    {
        $notifications = auth()->user()->unreadNotifications;
        return view('layouts.navbar.auth.topnav', compact('notifications'));
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx'
        ]);
          $niplama = session('niplama');
        //     Excel::import(new FenomenaImport, $request->file('file'));
        Excel::import(new FenomenaImport($niplama), $request->file('file'));
        return redirect()->back()->with('success', 'Data fenomena berhasil diimport!');
    }
    
    public function checkNotifications()
    {
        // Mengambil fenomena yang mendekati H-1
        $fenomenas = Fenomena::where('approval', null)
            ->whereDate('created_at', '=', Carbon::now()->addDay()->format('Y-m-d'))
            ->get();

        foreach ($fenomenas as $fenomena) {
            // Kirim notifikasi H-1
            $message = "Fenomena {$fenomena->id} mendekati H-1.";
            Notification::send($fenomena->niplama, new FenomenaNotification($fenomena, $message));
        }

        // Mengambil fenomena yang baru saja di-approve atau di-reject
        $approvedOrRejected = Fenomena::whereNotNull('approval')
            ->whereDate('updated_at', '=', Carbon::today()->format('Y-m-d'))
            ->get();

        foreach ($approvedOrRejected as $fenomena) {
            $message = $fenomena->approval == 1
                ? "Fenomena {$fenomena->id} telah di-approve."
                : "Fenomena {$fenomena->id} telah di-reject.";

            Notification::send($fenomena->niplama, new FenomenaNotification($fenomena, $message));
        }
    }

    public function exportView()
    {
        $sumberFenomena = SumberFenomena::all();
        $cakupan = Cakupan::all();
        $subjects = Subjek::select('subjek')->distinct()->get();
        $kabkots = Kabkot::all();

        return view('fenomena.export', compact('sumberFenomena', 'cakupan', 'subjects', 'kabkots'));
    }

    public function exportFenomena(Request $request)
    {
        // Ambil input filter
        $tanggalAwal    = $request->input('tanggal_awal');
        $tanggalAkhir   = $request->input('tanggal_akhir');
        $cakupan        = $request->input('cakupan');
        $cakupanKabkot  = $request->input('cakupankabkot');
        $subjek         = $request->input('subjek');

        // Query dasar
        $query = Fenomena::query();

        if ($tanggalAwal && $tanggalAkhir) {
            $query->whereBetween('tanggal_awal', [$tanggalAwal, $tanggalAkhir]);
        }

        // Filter cakupan kalau dipilih
        if ($cakupan) {
            $query->where('cakupan', $cakupan);
        }

        // Filter kab/kota hanya jika user pilih
        if ($cakupanKabkot) {
            $query->where('cakupankabkot', $cakupanKabkot);
        }

        if ($subjek) {
            $query->where('subjek', $subjek);
        }

        $fenomena = $query->get();

        // Export ke Excel
        $fileName = 'fenomena_export_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new FenomenaExport($fenomena), $fileName);
    }
}