<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransaksiOperasionalHeader;
use App\Models\TransaksiOperasionalDetail;
use Illuminate\Support\Facades\DB; // WAJIB DIPANGGIL UNTUK TRANSAKSI

class TransaksiController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi Dasar
        $request->validate([
            'tanggal' => 'required|date',
            'user_id' => 'required|integer',
            'description' => 'required|string',
            'details' => 'required|array', // Pastikan details berupa array
        ]);

        // 2. Mulai Mode Transaksi Database (All or Nothing)
        DB::beginTransaction();

        try {
            // 3. Buat Transaksi ID unik (Karena Anda pakai varchar, bukan Auto Increment)
            // Contoh format: OP-20260622-123456
            $transaksiId = 'OP-' . \App\Support\AppTime::now()->format('Ymd') . '-' . rand(1000, 9999);

            // 4. Simpan Data ke Tabel Header
            $header = TransaksiOperasionalHeader::create([
                'transaksi_id' => $transaksiId,
                'tanggal'      => $request->tanggal,
                'user_id'      => $request->user_id,
                'description'  => $request->description,
            ]);

            // 5. Simpan Data ke Tabel Detail secara Looping (Berulang)
            // Kita pecah array 'details' yang dikirim dari form tadi
            foreach ($request->details as $itemDetail) {
                
                // Pastikan nominal diisi agar baris kosong tidak ikut tersimpan
                if (!empty($itemDetail['nominal'])) {
                    TransaksiOperasionalDetail::create([
                        'transaksi_id'     => $transaksiId, // Ambil dari ID Header di atas
                        'cost_category_id' => $itemDetail['cost_category_id'],
                        'remark'           => $itemDetail['remark'],
                        'nominal'          => $itemDetail['nominal'],
                    ]);
                }
            }

            // 6. Jika semua proses di atas sukses tanpa error, KUNCI data ke database!
            DB::commit();

            return redirect()->back()->with('success', 'Transaksi berhasil disimpan!');

        } catch (\Exception $e) {
            // 7. Jika ada ERROR di tengah jalan (misal: internet putus, kolom database tidak cocok)
            // BATALKAN semua yang sudah terlanjur disimpan agar database tidak kotor
            DB::rollBack();
            
            // Tampilkan pesan errornya agar kita tahu letak salahnya
            return redirect()->back()->withErrors('Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}