<?php

namespace App\Http\Controllers;

use App\Models\Fenomena;
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

class UserController extends Controller
{
    
    public function index()
    {
        $users = User::all();
        return view('pages.user-management', compact('users'));
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);

        // Ambil semua subjek unik
        $subjeks = Subjek::select('subjek')->distinct()->get();

        // Ambil subjek yang disupervisi user ini
        $subjek_user = Subjek::where('supervisor', $user->niplama)
                            ->pluck('subjek')
                            ->toArray();

        return view('pages.edit', compact('user', 'subjeks', 'subjek_user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Update data user
        $user->update($request->only(['username', 'email', 'level']));

        // 1. Hapus semua subjek lama yang disupervisi user ini
        Subjek::where('supervisor', $user->niplama)->delete();

        // 2. Tambahkan subjek baru dari request
        if ($request->has('subjeks')) {
            foreach ($request->subjeks as $subjekId) {
                Subjek::create([
                    'subjek'        => $subjekId,
                    'supervisor'=> $user->niplama,
                    // kalau ada kolom lain wajib diisi, tambahkan di sini
                ]);
            }
        }

        return redirect()->route('user.index')->with('success', 'User updated successfully.');
    }



    public function destroy($id)
    {
        User::destroy($id);

        return redirect()->route('user.index')->with('success', 'User deleted successfully');
    }
}