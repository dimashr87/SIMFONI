<?php

namespace App\Http\Controllers;

use App\Models\WebScrapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WebScrappingController extends Controller
{
    public function index()
    {
        $files = WebScrapping::latest()->get();
        return view('webscrapping.index', compact('files'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:10240',
        ]);

        $file = $request->file('file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('webscrapping', $filename, 'public');

        WebScrapping::create([
            'nama_file' => $filename,
            'path_file' => $path,
            'status' => 0,
        ]);

        return redirect()->route('webscrapping.index')->with('success', 'File berhasil diupload');
    }

    public function download($id)
    {
        $file = WebScrapping::findOrFail($id);

        // Update status menjadi sudah didownload
        $file->update(['status' => 1]);

        return Storage::disk('public')->download($file->path_file, $file->nama_file);
    }

    public function destroy($id)
    {
        $file = WebScrapping::findOrFail($id);

        // Hapus file dari storage
        if (Storage::disk('public')->exists($file->path_file)) {
            Storage::disk('public')->delete($file->path_file);
        }

        $file->delete();

        return redirect()->route('webscrapping.index')->with('success', 'File berhasil dihapus');
    }
}
