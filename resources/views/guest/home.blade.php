@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
<div class="min-h-screen bg-primary-white2 px-4 py-10 space-y-12">

<!-- SECTION: HOME -->
<section id="home" class="bg-gradient-to-br from-green-50 to-white rounded-xl shadow-md p-10">
    <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-10 items-center">
        
        <!-- Konten Teks -->
        <div class="space-y-6">
            <h1 class="text-4xl md:text-5xl font-bold text-gray-800 leading-tight animate-fade-in">
                Selamat Datang di <br><span class="text-green-600">Personal Finance</span>!
            </h1>
            <p class="text-gray-600 text-lg md:text-xl leading-relaxed">
                🌟 Kelola keuanganmu dengan lebih <strong>mudah</strong>, <strong>aman</strong>, dan <strong>efisien</strong> hanya dalam genggamanmu.
            </p>
            <a href="#about" class="inline-flex items-center gap-2 px-6 py-3 bg-btn-color text-white text-lg font-semibold rounded-lg shadow-md hover:bg-green-700 transition duration-300">
                <i class="fas fa-arrow-down"></i> Jelajahi Fitur
            </a>
            <div class="flex items-center space-x-4 text-sm text-gray-500 pt-4">
                <div class="flex items-center gap-1">
                    <i class="fas fa-lock text-green-600"></i> Aman
                </div>
                <div class="flex items-center gap-1">
                    <i class="fas fa-bolt text-yellow-500"></i> Cepat
                </div>
                <div class="flex items-center gap-1">
                    <i class="fas fa-mobile-alt text-blue-600"></i> Mudah Digunakan
                </div>
            </div>
        </div>

        <!-- Gambar -->
        <div class="flex justify-center animate-slide-in">
            <img src="{{ asset('images/ic_home.png') }}" class="w-[300px] md:w-[350px] rounded-2xl shadow-xl" alt="Mockup Aplikasi">
        </div>
    </div>
</section>


<!-- SECTION: ABOUT -->
<section id="about" class="bg-white rounded-xl shadow-lg p-10">
    <div class="max-w-6xl mx-auto grid md:grid-cols-2 gap-10 items-center">
        
        <!-- Gambar -->
        <div class="flex justify-center">
            <img src="{{ asset('images/ic_home.png') }}" alt="Tentang Aplikasi" class="w-full md:w-3/4 rounded-xl shadow-md">
        </div>

        <!-- Konten -->
        <div>
            <h2 class="text-4xl font-bold text-gray-800 mb-4">Tentang Aplikasi</h2>
            <p class="text-gray-700 text-lg leading-relaxed mb-4">
                <strong class="text-green-700">Personal Finance</strong> adalah aplikasi pengelolaan keuangan pribadi yang dirancang untuk
                membantu kamu mencatat pemasukan, pengeluaran, serta merencanakan target finansial secara mudah.
            </p>
            <ul class="list-disc list-inside text-gray-700 space-y-2 text-md">
                <li><i class="fas fa-check-circle text-green-600 mr-2"></i>Pencatatan transaksi harian</li>
                <li><i class="fas fa-check-circle text-green-600 mr-2"></i>Perencanaan keuangan jangka pendek & panjang</li>
                <li><i class="fas fa-check-circle text-green-600 mr-2"></i>Grafik dan visualisasi data keuangan</li>
                <li><i class="fas fa-check-circle text-green-600 mr-2"></i>User-friendly & ringan digunakan</li>
            </ul>
        </div>
    </div>
</section>


<!-- SECTION: CONTACT -->
<section id="contact" class="bg-primary-white2 rounded-xl shadow-lg p-10 relative overflow-hidden">

    <!-- Konten Utama -->
    <div class="max-w-5xl mx-auto text-center relative z-10">
        <h2 class="text-4xl font-bold text-gray-800 mb-4">Kontak Kami</h2>
        <p class="text-gray-600 text-lg mb-8">Hubungi kami melalui salah satu kontak berikut:</p>

        <!-- Email -->
        <div class="flex justify-center items-center gap-4 mb-4">
            <i class="fas fa-envelope text-green-600 text-2xl"></i>
            <p class="text-lg text-gray-700">Email: <strong class="text-green-700">fransiuselyandy@gmail.com</strong></p>
        </div>

        <!-- WhatsApp -->
        <div class="flex justify-center items-center gap-4 mb-4">
            <i class="fab fa-whatsapp text-green-600 text-2xl"></i>
            <p class="text-lg text-gray-700">WhatsApp: <strong class="text-green-700">+62 823-8431-4526</strong></p>
        </div>

        <!-- Instagram -->
        <div class="flex justify-center items-center gap-4 mb-4">
            <i class="fab fa-instagram text-pink-500 text-2xl"></i>
            <p href="https://www.instagram.com/elyandi_fs/" class="text-lg text-gray-700">Instagram: <strong class="text-green-700">elyandi_fs</strong></p>
        </div>

        <!-- Tombol Aksi -->
        <div class="flex justify-center gap-4 mt-8">
            <a href="mailto:fransiuselyandy@gmail.com" class="bg-btn-color hover:bg-gray-700 text-white px-6 py-3 rounded-lg transition">
                <i class="fas fa-paper-plane mr-2"></i>Kirim Email
            </a>
            <a href="https://wa.me/6282384314526" class="bg-green-100 hover:bg-green-200 text-green-800 px-6 py-3 rounded-lg transition">
                <i class="fab fa-whatsapp mr-2"></i>Chat WhatsApp
            </a>
        </div>  
    </div>
</section>

    <!-- SECTION: DOWNLOAD -->
    <section id="download" class="bg-white rounded-xl shadow-lg p-6 sm:p-8 md:p-10">
    <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
        
        <!-- Gambar / Logo -->
        <div class="flex justify-center">
        <img src="{{ asset('images/ic_download.png') }}" alt="Download Aplikasi" class="w-2/3 sm:w-1/2 md:w-1/3 lg:w-1/4 rounded-xl shadow-md">
        </div>

        <!-- Konten -->
        <div class="text-center md:text-left px-2">
        <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-gray-700 mb-4">
            Unduh Aplikasi
        </h2>
        <p class="text-gray-700 text-base sm:text-lg leading-relaxed mb-6">
            Aplikasi <strong class="text-green-700">Personal Finance</strong> tersedia untuk perangkat Android. 
            Unduh sekarang dan mulai kelola keuanganmu dengan mudah!
        </p>

        <!-- Tombol Download -->
        <a href="#"
            class="inline-block bg-btn-color hover:bg-green-700 text-white text-base sm:text-lg font-semibold py-2 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-300">
            <i class="fas fa-download mr-2"></i> Download Sekarang
        </a>

        <!-- Sosial Media -->
        <div class="mt-6 flex justify-center md:justify-start space-x-4">
            <a href="https://www.instagram.com/elyandi_fs/" class="text-green-600 hover:text-green-800 text-xl sm:text-2xl"><i class="fab fa-facebook"></i></a>
            <a href="https://www.instagram.com/elyandi_fs/" class="text-green-600 hover:text-green-800 text-xl sm:text-2xl"><i class="fab fa-instagram"></i></a>
        </div>
        </div>
    </div>
    </section>





</div>
@endsection
