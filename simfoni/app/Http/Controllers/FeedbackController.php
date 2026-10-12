<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feedback; // Model untuk menyimpan feedback

class FeedbackController extends Controller
{
    public function store(Request $request)
    {
        // Validasi data
        $request->validate([
            'emoji' => 'required|string',
            'suggestion' => 'nullable|string',
        ]);

        // Ambil niplama dari session
        $niplama = session('niplama'); // Pastikan 'niplama' sudah disimpan di session sebelumnya

        // Simpan data ke dalam database
        Feedback::create([
            'emoji' => $request->emoji,
            'suggestion' => $request->suggestion,
            'niplama' => $niplama, // Simpan niplama ke database
        ]);

        // Redirect atau respon sukses
        return redirect()->back()->with('success', 'Feedback berhasil dikirim.');
    }
}
