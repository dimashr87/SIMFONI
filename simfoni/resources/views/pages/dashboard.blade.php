@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
@include('layouts.navbars.auth.topnav', ['title' => 'Dashboard'])

<style>
.carousel-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: #ffffffff;
    color: #19171cff;
    border: none;
    padding: 12px 16px;
    border-radius: 50%;
    font-size: 18px;
    z-index: 5;
}
.carousel-btn-prev {
    left: -30px; /* geser di luar card */
}
.carousel-btn-next {
    right: -30px;
}
.carousel-btn:focus {
    outline: none;
    box-shadow: none;
}
.carousel-btn:hover {
    background: #ffffffff; /* tidak berubah saat hover */
}

/* Custom dot indicators */
.carousel-indicators.custom-indicators {
    bottom: -35px; /* posisikan di bawah card */
}
.carousel-indicators.custom-indicators [data-bs-target] {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background-color: #6c757d;
    opacity: 0.6;
}
.carousel-indicators.custom-indicators .active {
    background-color: #343a40;
    opacity: 1;
}

.custom-indicators {
    position: relative;
    margin-top: 10px; /* kasih jarak ke atas */
    justify-content: center;
}

.custom-indicators button {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background-color: #ccc;
    border: none;
    margin: 0 5px;
    transition: background-color 0.3s ease;
}

.custom-indicators .active {
    background-color: #333; /* warna aktif lebih gelap */
}
</style>

<div class="container-fluid py-4">
      <!-- Selamat datang dan nama pengguna -->
    <div class="alert">
        <h5 class="text-white">
            Selamat datang, {{ Auth::user()->firstname }} {{ Auth::user()->lastname }}!
        </h5>
    </div>

    
<div class="row">
    <div class="col-12 mb-4 position-relative">
        <div class="card">
            <div class="card-header pb-0">
                <h5 class="mb-0 font-weight-bold">Total Fenomena Terkumpul</h5>
            </div>
            <div class="card-body p-4">
                
                <!-- Carousel -->
                <div id="fenomenaCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">

                    <div class="carousel-inner">

                        <!-- Slide 1 -->
                        <div class="carousel-item active">
                            <div class="row text-center g-4">
                                <div class="col-md-4">
                                    <div class="d-flex flex-column align-items-center p-3 shadow-sm rounded-3 h-100">
                                        <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle mb-3">
                                            <i class="ni ni-money-coins text-lg opacity-10"></i>
                                        </div>
                                        <h6 class="font-weight-bold mb-2">Produk Domestik Regional Bruto</h6>
                                        <h2 class="font-weight-bolder text-dark mb-0">{{ $jumlahFenomenaPDRB }}</h2>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex flex-column align-items-center p-3 shadow-sm rounded-3 h-100">
                                        <div class="icon icon-shape bg-gradient-info shadow-info text-center rounded-circle mb-3">
                                            <i class="ni ni-single-02 text-lg opacity-10"></i>
                                        </div>
                                        <h6 class="font-weight-bold mb-2">Statistik Sosial</h6>
                                        <h2 class="font-weight-bolder text-dark mb-0">{{ $jumlahFenomenaSos }}</h2>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex flex-column align-items-center p-3 shadow-sm rounded-3 h-100">
                                        <div class="icon icon-shape bg-gradient-danger shadow-danger text-center rounded-circle mb-3">
                                            <i class="ni ni-world text-lg opacity-10"></i>
                                        </div>
                                        <h6 class="font-weight-bold mb-2">Statistik Produksi</h6>
                                        <h2 class="font-weight-bolder text-dark mb-0">{{ $jumlahFenomenaProd }}</h2>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Slide 2 -->
                        <div class="carousel-item">
                            <div class="row text-center g-4">
                                <div class="col-md-4">
                                    <div class="d-flex flex-column align-items-center p-3 shadow-sm rounded-3 h-100">
                                        <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle mb-3">
                                            <i class="ni ni-delivery-fast text-lg opacity-10"></i>
                                        </div>
                                        <h6 class="font-weight-bold mb-2">Statistik Distribusi</h6>
                                        <h2 class="font-weight-bolder text-dark mb-0">{{ $jumlahFenomenaDist }}</h2>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex flex-column align-items-center p-3 shadow-sm rounded-3 h-100">
                                        <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle mb-3">
                                            <i class="ni ni-chart-bar-32 text-lg opacity-10"></i>
                                        </div>
                                        <h6 class="font-weight-bold mb-2">Ekonomi Secara Umum</h6>
                                        <h2 class="font-weight-bolder text-dark mb-0">{{ $jumlahFenomenaESU }}</h2>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex flex-column align-items-center p-3 shadow-sm rounded-3 h-100">
                                        <div class="icon icon-shape bg-gradient-secondary shadow-secondary text-center rounded-circle mb-3">
                                            <i class="ni ni-bulb-61 text-lg opacity-10"></i>
                                        </div>
                                        <h6 class="font-weight-bold mb-2">Indikator Strategis Lainnya</h6>
                                        <h2 class="font-weight-bolder text-dark mb-0">{{ $jumlahFenomenaISL }}</h2>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Dot Indicators -->
                    <div class="carousel-indicators custom-indicators">
                        <button type="button" data-bs-target="#fenomenaCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                        <button type="button" data-bs-target="#fenomenaCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                    </div>
                </div>

                <!-- Custom Buttons (Outside Carousel) -->
                <button class="carousel-btn carousel-btn-prev" type="button" data-bs-target="#fenomenaCarousel" data-bs-slide="prev">
                    <i class="ni ni-bold-left"></i>
                </button>
                <button class="carousel-btn carousel-btn-next" type="button" data-bs-target="#fenomenaCarousel" data-bs-slide="next">
                    <i class="ni ni-bold-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>
    <div class="row mt-4">
        <div class="col-lg-7 mb-lg-0 mb-4">
            <div class="card z-index-2 h-100">
                <div class="card-header pb-0 pt-3 bg-transparent">
                    <h5 class="text-capitalize">Grafik Perkembangan Jumlah Fenomena</h6>
                    <p class="text-sm mb-0">
                        <i class="fa fa-arrow-up text-success"></i>
                      
                    </p>
                </div>
                <div class="card-body p-3">
                    <div class="chart">
                        <canvas id="chart-line" class="chart-canvas" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card card-carousel overflow-hidden h-100 p-0">
                <div id="carouselExampleCaptions" class="carousel slide h-100" data-bs-ride="carousel">
                    <div class="carousel-inner border-radius-lg h-100">
                        <div class="carousel-item h-100 active" style="background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%); background-size: cover;">
                            <div class="carousel-caption d-none d-md-block bottom-0 text-start start-0 ms-5" style="top: 20%;">
                                <div class="icon icon-shape icon-sm bg-white text-center border-radius-md mb-3">
                                    <i class="ni ni-camera-compact text-dark opacity-10"></i>
                                </div>
                                <h3 class="text-white mb-1">Top 3 Keyword</h3>
                                @foreach($topKeywords as $keyword => $count)
                                    <h5 class="text-white mb-1">{{ $keyword }} - {{ $count }} kali muncul</h5>
                                @endforeach
                            </div>
                        </div>
                        <div class="carousel-item h-100" style="background: linear-gradient(135deg, #b04600ff 0%, #ffa053ff 100%); background-size: cover;">
                            <div class="carousel-caption d-none d-md-block bottom-0 text-start start-0 ms-5" style="top: 20%;">
                                <div class="icon icon-shape icon-sm bg-white text-center border-radius-md mb-3">
                                    <i class="ni ni-bulb-61 text-dark opacity-10"></i>
                                </div>
                                <h3 class="text-white mb-1">Top 3 Kontributor</h3>
                                @foreach($topKontributor as $kontributor)
                                    <h5 class="text-white mb-1">{{ $kontributor->firstname }} {{ $kontributor->lastname }} - {{ $kontributor->jumlah }} Fenomena</h5>
                                @endforeach
                            </div>
                        </div>
                        <div class="carousel-item h-100" style="background: linear-gradient(135deg, #0b0822ff 0%, #3a366cff 100%); background-size: cover;">
                            <div class="carousel-caption d-none d-md-block bottom-0 text-start start-0 ms-5" style="top: 20%;">
                                <div class="icon icon-shape icon-sm bg-white text-center border-radius-md mb-3">
                                    <i class="ni ni-trophy text-dark opacity-10"></i>
                                </div>
                                <h3 class="text-white mb-1">Simfoni</h3>
                                <h5 class="text-white mb-1">Sistem Pengumpulan Fenomena Terintegrasi</h5>
                            </div>
                        </div>
                    </div>
                    <button class="carousel-control-prev w-5 me-3" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next w-5 me-3" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-lg-12 mb-lg-0 mb-4">
            <div class="card">
                <div class="card-header pb-0 p-3">
                    <h5 class="mb-2">Kalender Rencana Terbit</h5>
                </div>
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="eventModalLabel">Detail Kegiatan</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <p><strong>Nama Kegiatan:</strong> <span id="eventTitle"></span></p>
            <p><strong>Tanggal:</strong> <span id="eventDate"></span></p>
        </div>
        </div>
    </div>
    </div>

</div>

    @include('layouts.footers.auth.footer')
</div>
@endsection

@push('js')

<!-- ChartJS JS -->
<script src="./assets/js/plugins/chartjs.min.js"></script>
<script>
    var ctx1 = document.getElementById("chart-line").getContext("2d");

new Chart(ctx1, {
    type: "bar", // GANTI dari "line" ke "bar"
    data: {
        labels: {!! json_encode($labels) !!}, // data label dari controller
        datasets: [{
            label: "Jumlah Fenomena",
            backgroundColor: "#fb6340", // warna batang
            borderRadius: 6, // sudut batang melengkung
            data: {!! json_encode($data) !!}, // data jumlah dari controller
            maxBarThickness: 40 // tebal maksimal batang
        }],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false,
            }
        },
        interaction: {
            intersect: false,
            mode: 'index',
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    drawBorder: false,
                    display: true,
                    drawOnChartArea: true,
                    drawTicks: false,
                    borderDash: [5, 5]
                },
                ticks: {
                    display: true,
                    padding: 10,
                    color: '#333',
                    font: {
                        size: 12,
                        family: "Open Sans",
                        style: 'normal',
                        lineHeight: 2
                    },
                }
            },
            x: {
                grid: {
                    drawBorder: false,
                    display: false,
                    drawOnChartArea: false,
                    drawTicks: false,
                },
                ticks: {
                    display: true,
                    color: '#333',
                    padding: 10,
                    font: {
                        size: 12,
                        family: "Open Sans",
                        style: 'normal',
                        lineHeight: 2
                    },
                }
            },
        },
    },
});
</script>

<!-- FullCalendar -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'id',
        height: "auto", // otomatis menyesuaikan tinggi
        headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: ''
    },
    events: @json($events),
    eventClick: function(info) {
        // ambil data dari event
        var title = info.event.title;
        var date = info.event.start.toLocaleDateString('id-ID');

        // masukkan ke modal
        document.getElementById('eventTitle').textContent = title;
        document.getElementById('eventDate').textContent = date;

        // tampilkan modal bootstrap
        var myModal = new bootstrap.Modal(document.getElementById('eventModal'));
        myModal.show();
    },
    eventDidMount: function(info) {
    if (info.view.type === 'listMonth') {
        var dotEl = info.el.querySelector('.fc-list-event-dot');
        if (dotEl) {
        dotEl.style.backgroundColor = info.event.backgroundColor;
        }
    }
    },
    eventMouseEnter: function(info) {
        info.el.style.cursor = 'pointer';
        info.el.style.transform = 'scale(1.05)';
        info.el.style.transition = '0.2s ease';
        info.el.style.boxShadow = '0 4px 8px rgba(0,0,0,0.2)';
    },
    eventMouseLeave: function(info) {
        info.el.style.transform = 'scale(1)';
        info.el.style.boxShadow = 'none';
    },
    });

    calendar.render();
  });
</script>

<style>
  /* kecilkan tinggi tiap cell kalender */
  .fc-daygrid-day-frame {
      min-height: 60px !important; /* default biasanya 120px */
  }

  /* kecilkan font judul event */
  .fc-event-title {
      font-size: 0.8rem !important;
  }

  /* kecilkan judul bulan */
  .fc-toolbar-title {
      font-size: 1.1rem !important;
  }
</style>
@endpush