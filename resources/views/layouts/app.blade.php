<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'PersonalFinance')</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


    <style>
        .bg-primary-color {
            background-color: #465455;
        }
        .bg-btn-color {
            background-color: #263238;
        }
          .bg-primary-white {
            background-color: #BBC8B8;
        }
          .bg-primary-white2 {
            background-color: #F7FEF7;
        }
    </style>
</head>
<body class="bg-primary-white2 text-gray-900">

<!-- Gunakan kelas custom -->
<nav class="bg-primary-color text-[#465455] p-6 sticky top-0 z-50">
  <div class="container mx-auto flex justify-between items-center">
    
    <!-- Logo dan Judul -->
    <div class="flex items-center space-x-2">
      <img src="{{ asset('images/ic_logo.png') }}" alt="Logo" class="h-8 w-8">
      <span class="text-[#465455] italic text-lg font-bold">Personal Finance</span>
    </div>

    <!-- Tombol Menu Hamburger (hanya muncul di mobile) -->
    <div class="md:hidden">
      <button id="menu-toggle" class="text-white focus:outline-none">
        <svg class="h-6 w-6 fill-current" viewBox="0 0 24 24">
          <path d="M4 5h16M4 12h16M4 19h16"/>
        </svg>
      </button>
    </div>

    <!-- Link Navigasi -->
    <div id="menu" class="hidden md:flex space-x-4">
      <a href="#home" class="hover:underline text-[#F7FEF7] text-base font-medium">Home</a>
      <a href="#about" class="hover:underline text-[#F7FEF7] text-base font-medium">About</a>
      <a href="#contact" class="hover:underline text-[#F7FEF7] text-base font-medium">Contact</a>
      <a href="#download" class="hover:underline text-[#F7FEF7] text-base font-medium">Download</a>
    </div>
  </div>

  <!-- Menu Mobile (toggle) -->
  <div id="mobile-menu" class="md:hidden hidden mt-4 px-6 space-y-2">
    <a href="#home" class="block text-[#F7FEF7] hover:underline">Home</a>
    <a href="#about" class="block text-[#F7FEF7] hover:underline">About</a>
    <a href="#contact" class="block text-[#F7FEF7] hover:underline">Contact</a>
    <a href="#download" class="block text-[#F7FEF7] hover:underline">Download</a>
  </div>
</nav>


<main class="container mx-auto mt-2">
    @yield('content')
</main>
</body>
</html>
