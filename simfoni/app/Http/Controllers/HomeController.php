<?php

namespace App\Http\Controllers;
use App\Models\Fenomena;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Arc;
use App\Models\User;
use Carbon\Carbon;

class HomeController extends Controller
{
        /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $resumes = DB::table('fenomena')->pluck('resume');

        // Gabungkan semua resume menjadi satu string besar
        $allText = $resumes->implode(' ');

        // Pecah teks menjadi array kata-kata
        $words = str_word_count($allText, 1);

        // Daftar kata-kata yang akan dihilangkan (stopwords)
        $stopWords = [
            'dan', 'yang', 'di', 'ke', 'dari', 'dengan', 'pada', 'untuk', 'adalah',
            'ini', 'itu', 'atau', 'sebagai', 'oleh', 'juga', 'karena', 'tersebut', 
            'dalam', 'maka', 'akan', 'dapat', 'telah', 'bahwa', 'dengan', 'hingga', 'seperti','sebesar','tahun'
        ];

        // Filter kata-kata dengan menghilangkan stopwords
        $filteredWords = array_filter($words, function($word) use ($stopWords) {
            return !in_array(strtolower($word), $stopWords);
        });

        // Hitung frekuensi setiap kata setelah filtering
        $wordFrequency = array_count_values($filteredWords);

        // Urutkan berdasarkan frekuensi, dan ambil 3 teratas
        arsort($wordFrequency);
        $topKeywords = array_slice($wordFrequency, 0, 3, true);
            
        $jumlahFenomenaProd = Fenomena::where('subjek', 'Statistik Produksi')->count();
        $jumlahFenomenaSos = Fenomena::where('subjek', 'Statistik Sosial')->count();
        $jumlahFenomenaDist = Fenomena::where('subjek', 'Statistik Distribusi')->count();
        $jumlahFenomenaPDRBLU = Fenomena::where('subjek', 'PDRB Menurut Lapangan Usaha')->count();
        $jumlahFenomenaPDRBP = Fenomena::where('subjek', 'PDRB Menurut Pengeluaran')->count();
        $jumlahFenomenaPDRB = $jumlahFenomenaPDRBLU + $jumlahFenomenaPDRBP;
        $jumlahFenomenaISL = Fenomena::where('subjek', 'Indikator Strategis Lainnya')->count();
        $jumlahFenomenaESU = Fenomena::where('subjek', 'Ekonomi Secara Umum')->count();

        $fenomenaPerBulan = DB::table('fenomena')
            ->select(DB::raw('COUNT(*) as jumlah'), DB::raw('MONTH(created_at) as bulan'))
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();
        $topKontributor = DB::table('fenomena')
            ->select('fenomena.niplama', DB::raw('COUNT(*) as jumlah'), 'users.firstname', 'users.lastname')
            ->join('users', 'users.niplama', '=', 'fenomena.niplama')
            ->groupBy('fenomena.niplama', 'users.firstname', 'users.lastname')
            ->orderBy('jumlah', 'desc')
            ->limit(3)
            ->get();

        $namaKontributor = [];
        foreach ($topKontributor as $kontributor) {
            $namaLengkap = $kontributor->firstname . ' ' . $kontributor->lastname;
            $namaKontributor[] = $namaLengkap;
        }

        // Mengonversi data untuk digunakan di chart
        $labels = $fenomenaPerBulan->pluck('bulan')->map(function($bulan) {
            return date('M', mktime(0, 0, 0, $bulan, 10)); // Mengubah angka bulan menjadi nama bulan
        });

        // === Tambahan: Data ARC ===
        $jadwalarc = Arc::select('id', 'nama_kegiatan as title', 'tanggal_kegiatan as start')->get();

        $arcs = Arc::all();
        $today = now()->toDateString();
        $events = $arcs->map(function ($arc) use ($today) {
        $eventDate = Carbon::parse($arc->tanggal_kegiatan)->toDateString();
            // tentukan status otomatis
            if ($eventDate == $today) {
                $status = 'Berjalan';
                $color = '#ffc400ff'; 
            } elseif ($eventDate < $today) {
                $status = 'Selesai';
                $color = '#28a745'; 
            } else {
                $status = 'Akan Datang';
                $color = '#007bff'; 
            }

            return [
                'title' => $arc->nama_kegiatan . " - " . $status, // hanya nama + status
                'start' => $eventDate,
                'color' => $color,
            ];
        });

        $data = $fenomenaPerBulan->pluck('jumlah');
            return view('pages.dashboard',  compact('jumlahFenomenaPDRB', 'jumlahFenomenaProd', 'jumlahFenomenaSos', 'jumlahFenomenaDist', 'jumlahFenomenaESU', 'jumlahFenomenaISL','labels', 'data','topKontributor', 'namaKontributor','topKeywords', 'jadwalarc', 'events'));
    }
}
