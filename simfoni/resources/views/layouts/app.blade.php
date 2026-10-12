<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('img/1.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('img/1.png') }}">
    <title>Simfoni</title>
    <!-- Fonts and icons -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <!-- Nucleo Icons -->
    <link href="{{ asset('assets/css/nucleo-icons.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/nucleo-svg.css') }}" rel="stylesheet" />
    <!-- Font Awesome Icons -->
    <script src="https://kit.fontawesome.com/75b957f7e0.js" crossorigin="anonymous"></script>
    <!-- CSS Files -->
    <link id="pagestyle" href="{{ asset('assets/css/argon-dashboard.css') }}" rel="stylesheet" />
    <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
    <script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css" />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar/index.global.min.js'></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- Modal CSS -->
    <style>
        /* Modal styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed;
            z-index: 1050; /* Ensures modal is on top */
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5); /* Black with opacity */
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px; /* Adjusted max-width for better fit */
            z-index: 1060; /* Higher than modal backdrop */
            position: relative;
        }

        .close-button {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close-button:hover,
        .close-button:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        .btn-icon {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%; /* bulat */
            font-size: 16px;
            padding: 0;
        }

        .table td {
            vertical-align: middle;
        }
        .deskripsi-col {
            max-width: 200px;        /* atur lebar kolom sesuai kebutuhan */
            white-space: nowrap;     
            overflow: hidden;        
            text-overflow: ellipsis; /* kasih "..." */
        }
    </style>
</head>

<body class="{{ $class ?? '' }}">
    @guest
        @yield('content')
    @endguest

    @auth
        @if (in_array(request()->route()->getName(), ['sign-in-static', 'sign-up-static', 'login', 'register', 'recover-password', 'rtl', 'virtual-reality']))
            @yield('content')
        @else
            @if (!in_array(request()->route()->getName(), ['profile', 'profile-static']))
                <div class="min-height-300 bg-primary position-absolute w-100"></div>
            @elseif (in_array(request()->route()->getName(), ['profile-static', 'profile']))
                <div class="position-absolute w-100 min-height-300 top-0"
                    style="background-image: url('{{ asset('img/profile-layout-header.jpg') }}'); background-position-y: 50%;">
                    <span class="mask bg-primary opacity-6"></span>
                </div>
            @endif
            @include('layouts.navbars.auth.sidenav')
            <main class="main-content border-radius-lg" style="padding: 20px;">
                @yield('content')
            </main>
            @include('components.fixed-plugin')
        @endif
    @endauth

<!-- Feedback Modal -->
<div id="feedbackModal" class="modal">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <h2 style="text-align: center; margin-bottom: 20px;">Beri Feedback</h2>
        <form action="{{ route('feedback.store') }}" method="POST">
            @csrf
            <div style="margin-bottom: 15px;">
                <label for="emoji" style="font-weight: bold;">Tingkat Kepuasan:</label>
                <select id="emoji" name="emoji" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ddd;">
                    <option value="Sangat Puas">😊 - Sangat Puas</option>
                    <option value="Puas">🙂 - Puas</option>
                    <option value="Biasa Saja">😐 - Biasa Saja</option>
                    <option value="Tidak Puas">🙁 - Tidak Puas</option>
                    <option value="Sangat Tidak Puas">😠 - Sangat Tidak Puas</option>
                </select>
            </div>

            <div style="margin-bottom: 15px;">
                <label for="suggestion" style="font-weight: bold;">Saran:</label>
                <textarea id="suggestion" name="suggestion" rows="4" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ddd;" placeholder="Tulis saran Anda..."></textarea>
            </div>

            <button type="submit" style="width: 100%; background-color: #28a745; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer;">
                Kirim Feedback
            </button>
        </form>
    </div>
</div>

<!-- Modal CSS -->
<style>
    /* Modal background */
    .modal {
        display: none;
        position: fixed;
        z-index: 1050;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        transition: opacity 0.3s ease;
    }

    /* Modal content */
    .modal-content {
        background-color: white;
        margin: 10% auto;
        padding: 20px;
        border-radius: 8px;
        width: 90%;
        max-width: 500px;
        animation: fadeIn 0.3s;
    }

    /* Close button */
    .close-button {
        float: right;
        font-size: 24px;
        font-weight: bold;
        color: #888;
        cursor: pointer;
    }

    .close-button:hover {
        color: black;
    }

    /* Button styles */
    button:hover {
        background-color: #218838;
    }

    /* Animations */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<!-- Button for opening modal with star icon -->
<button onclick="openFeedbackModal()" style="position: fixed; width: 4em; height: 4em; bottom: 2em; left: 3em; padding: 15px; background-color: #007bff; color: white; border: none; border-radius: 50%; font-size: 12px; cursor: pointer; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); z-index: 9999;">
    <i class="fas fa-star"></i>
</button>

    <!-- Core JS Files -->
    <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js') }}"></script>

    <script>
        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            var options = {
                damping: '0.5'
            }
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }

        // Modal JavaScript
        document.addEventListener('DOMContentLoaded', function () {
            var modal = document.getElementById('feedbackModal');
            var closeButton = document.querySelector('.close-button');

            // Open the modal
            window.openFeedbackModal = function () {
                modal.style.display = 'block';
            };

            // Close the modal
            closeButton.onclick = function () {
                modal.style.display = 'none';
            };

            // Close the modal if the user clicks outside of the modal
            window.onclick = function (event) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            };
        });
    </script>
    <!-- Github buttons -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
    <script src="{{ asset('assets/js/argon-dashboard.js') }}"></script>
    @stack('js')
</body>

</html>
