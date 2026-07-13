@extends('layouts.app')

@section('title', 'Panduan Penggunaan')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h3 page-title mb-1">Panduan Penggunaan</h1>
        <div class="text-secondary">Absensi Apel Pagi BPAD Provinsi NTT</div>
    </div>
    <div class="d-flex flex-wrap gap-2 no-print">
        @auth
            <a class="btn btn-primary" href="{{ route('dashboard') }}">Kembali ke Dashboard</a>
        @else
            <a class="btn btn-primary" href="{{ route('login') }}">Ke Halaman Login</a>
        @endauth
        <button class="btn btn-outline-primary" type="button" onclick="window.print()">Print Panduan</button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-4">
        <div class="stat-card p-3 h-100">
            <div class="stat-label">Target Waktu</div>
            <div class="stat-value">5 Menit</div>
            <div class="text-secondary mt-2">Gunakan pencarian nama dan tombol status langsung agar input cepat selesai.</div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="stat-card p-3 h-100">
            <div class="stat-label">Default Status</div>
            <div class="stat-value">Hadir</div>
            <div class="text-secondary mt-2">Pegawai yang belum diubah otomatis dianggap Hadir pada tampilan input. Petugas cukup mengubah yang tidak hadir.</div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="stat-card p-3 h-100">
            <div class="stat-label">Selesai Jika</div>
            <div class="stat-value">Submit</div>
            <div class="text-secondary mt-2">Setiap bidang harus menekan Submit Bidang setelah status pegawai diperiksa.</div>
        </div>
    </div>
</div>

<div class="stat-card p-4 mb-4">
    <h2 class="h5 mb-3">Cara Pakai Pencarian Nama Cepat</h2>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="border rounded p-3 h-100 bg-light">
                <div class="fw-semibold mb-1">1. Pegawai Datang Lebih Awal</div>
                <div class="text-secondary">Petugas bisa langsung mencari nama pegawai tanpa menunggu panggilan manual.</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="border rounded p-3 h-100 bg-light">
                <div class="fw-semibold mb-1">2. Petugas Ketik Nama</div>
                <div class="text-secondary">Ketik sebagian nama pada kolom pencarian. Daftar pegawai tersaring otomatis.</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="border rounded p-3 h-100 bg-light">
                <div class="fw-semibold mb-1">3. Klik Status</div>
                <div class="text-secondary">Klik Hadir atau status lain. Rekap live langsung berubah mengikuti pilihan petugas.</div>
            </div>
        </div>
    </div>
</div>

<div class="stat-card p-4 mb-4">
    <h2 class="h5 mb-3">Langkah Cepat dari Login sampai Selesai</h2>
    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 72px;">No</th>
                    <th>Yang Dilakukan</th>
                    <th>Patokan Aman</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="fw-bold text-center">1</td>
                    <td>Buka aplikasi, lalu login memakai akun BPAD atau akun bidang masing-masing.</td>
                    <td>Jika berhasil, halaman Dashboard akan terbuka.</td>
                </tr>
                <tr>
                    <td class="fw-bold text-center">2</td>
                    <td>Periksa tanggal aktif di bagian atas Dashboard.</td>
                    <td>Jika tanggal salah, pilih tanggal yang benar. Perubahan tanggal otomatis diterapkan.</td>
                </tr>
                <tr>
                    <td class="fw-bold text-center">3</td>
                    <td>Untuk pegawai yang datang lebih awal, gunakan kolom pencarian nama di Dashboard.</td>
                    <td>Ketik sebagian nama pegawai. Daftar akan tersaring otomatis tanpa tombol terapkan.</td>
                </tr>
                <tr>
                    <td class="fw-bold text-center">4</td>
                    <td>Klik status pegawai sesuai kondisi saat apel.</td>
                    <td>Di bawah nama harus muncul tulisan Status: Hadir, Izin, Sakit, Tugas, Tubel, Cuti, atau Terlambat.</td>
                </tr>
                <tr>
                    <td class="fw-bold text-center">5</td>
                    <td>Hapus teks pencarian untuk kembali melihat seluruh daftar pegawai.</td>
                    <td>Semua bidang yang relevan akan muncul lagi sesuai hak akses akun.</td>
                </tr>
                <tr>
                    <td class="fw-bold text-center">6</td>
                    <td>Untuk pegawai yang tidak Hadir, klik tombol status yang sesuai.</td>
                    <td>Di bawah nama harus muncul tulisan Status: Izin, Sakit, Tugas, Tubel, Cuti, atau Terlambat.</td>
                </tr>
                <tr>
                    <td class="fw-bold text-center">7</td>
                    <td>Perhatikan Rekap Harian Live di bagian atas Dashboard.</td>
                    <td>Angka Hadir, Kurang, dan Keterangan berubah otomatis setiap status diklik.</td>
                </tr>
                <tr>
                    <td class="fw-bold text-center">8</td>
                    <td>Akun bidang menekan Submit Bidang. Admin BPAD menekan satu tombol Submit Semua Bidang setelah seluruh daftar selesai.</td>
                    <td>Periksa ringkasan pada modal konfirmasi, lalu lanjutkan submit.</td>
                </tr>
                <tr>
                    <td class="fw-bold text-center">9</td>
                    <td>Buka Status Submit untuk melihat bidang mana yang sudah atau belum submit.</td>
                    <td>Semua bidang harus berstatus Sudah Submit sebelum rekap akhir dicetak.</td>
                </tr>
                <tr>
                    <td class="fw-bold text-center">10</td>
                    <td>Admin BPAD membuka Rekap Harian untuk melihat format kertas dan mencetak rekap.</td>
                    <td>Gunakan tombol Simpan PDF / Cetak pada halaman Rekap Harian atau Rekap Bulanan.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="stat-card p-4 h-100">
            <h2 class="h5 mb-3">Untuk Akun Bidang</h2>
            <ol class="mb-0 ps-3">
                <li>Login dengan akun bidang.</li>
                <li>Bidang hanya melihat pegawai bidangnya sendiri.</li>
                <li>Gunakan pencarian nama cepat untuk pegawai yang datang lebih awal.</li>
                <li>Ubah status pegawai yang tidak Hadir.</li>
                <li>Tekan Submit Bidang.</li>
                <li>Setelah submit, koreksi hanya dapat dilakukan admin BPAD.</li>
            </ol>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="stat-card p-4 h-100">
            <h2 class="h5 mb-3">Untuk Admin BPAD</h2>
            <ol class="mb-0 ps-3">
                <li>Dapat melihat semua bidang dalam satu Dashboard.</li>
                <li>Dapat mengisi, mengubah, atau koreksi absensi semua bidang.</li>
                <li>Dapat memakai pencarian nama untuk langsung menemukan pegawai.</li>
                <li>Tekan Submit Semua Bidang satu kali setelah seluruh absensi selesai.</li>
                <li>Memantau Status Submit 5 Bidang.</li>
                <li>Mencetak Rekap Harian setelah semua bidang submit.</li>
                <li>Mengelola data pegawai aktif, nonaktif, dan PPPK.</li>
            </ol>
        </div>
    </div>
</div>
@endsection
