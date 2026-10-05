<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WilayahSeeder extends Seeder
{
    public function run()
    {
        // 1. Ambil semua data dari tabel mentah Kepmendagri
        // Kita urutkan dari kodenya (asc) agar Provinsi masuk lebih dulu, 
        // lalu Kabupaten, Kecamatan, dst. Ini wajib agar parent_id selalu ketemu.
        $dataKepmendagri = DB::table('wilayah_kepmendagri')->orderBy('kode', 'asc')->get();

        foreach ($dataKepmendagri as $data) {
            $kode = $data->kode;
            $nama = $data->nama;
            $parentId = null; // Default kosong (untuk level Provinsi)

            // 2. LOGIKA PARENT-CHILD (Memecah Titik)
            // Cek apakah ada karakter titik ('.') di dalam kode wilayah
            if (strpos($kode, '.') !== false) {
                // Pecah kode berdasarkan titik (Contoh: '11.01.05' jadi array ['11', '01', '05'])
                $bagian = explode('.', $kode);
                
                // Buang bagian paling akhir untuk mencari kode Bapak/Parent-nya (Jadi ['11', '01'])
                array_pop($bagian);
                
                // Gabungkan lagi dengan titik (Jadi '11.01')
                $kodeParent = implode('.', $bagian);

                // Cari ID dari Bapak/Parent tersebut di tabel 'wilayah' yang baru
                $parent = DB::table('wilayah')->where('code', $kodeParent)->first();
                
                if ($parent) {
                    $parentId = $parent->id; // Masukkan ID si Bapak ke data anak
                }
            }

            // 3. LOGIKA TERITORI (Jawa / Luar Jawa)
            $teritory = 'Luar Jawa'; // Anggap semua luar jawa dulu
            
            // Ambil 2 angka pertama dari kode (Ini adalah kode Provinsi)
            $kodeProvinsi = substr($kode, 0, 2);
            
            // Daftar kode provinsi di pulau Jawa (Banten, DKI, Jabar, Jateng, DIY, Jatim)
            $provinsiJawa = ['31', '32', '33', '34', '35', '36'];
            
            if (in_array($kodeProvinsi, $provinsiJawa)) {
                $teritory = 'Jawa'; // Ubah jadi Jawa jika kodenya cocok
            }

            // 4. MASUKKAN DATA KE TABEL WILAYAH (EMMO)
            DB::table('wilayah')->insert([
                'code' => $kode,
                'nama' => $nama,
                'parent_id' => $parentId,
                'teritory' => $teritory,
                'is_active' => 1
            ]);
        }
        
        $this->command->info('Data wilayah berhasil diadaptasi ke sistem EMMO!');
    }
}