<?php

namespace App\Services;

use App\Models\Wilayah;
use App\Models\SettingPlafonPerjalananDinas;
use Carbon\Carbon;

class PlafonService
{
    /**
     * Resolve wilayah ke level Provinsi.
     */
    public function resolveProvinsiId($wilayahId)
    {
        if (!$wilayahId) return null;

        $w = Wilayah::find($wilayahId);
        if (!$w) return null;

        return $w->parent_id ?? $w->id;
    }

    /**
     * Sesuai ENUM database: Reguler / Sabtu / Libur
     */
    public function mapHariEvent($tanggal, $isLibur)
    {
        if ($isLibur) return 'Libur';
        return Carbon::parse($tanggal)->isSaturday() ? 'Sabtu' : 'Reguler';
    }

    /**
     * PLAFON PERJALANAN DINAS (Level + Prov Asal + Prov Tujuan + Effective Date)
     */
    public function getPlafonPerjalananDinas($user, $tujuanWilayahId, $tanggal)
    {
        if (!$user->level_id) return null;

        $provTujuan = $this->resolveProvinsiId($tujuanWilayahId);
        if (!$provTujuan) return null;

        $provAsal = $this->resolveProvinsiId($user->homebase_id);
        if (!$provAsal) return null;

        // Level 1: Exact match
        $plafon = SettingPlafonPerjalananDinas::where('level_id', $user->level_id)
            ->where('provinsi_awal_id', $provAsal) // sesuaikan nama kolom asli DB Anda
            ->where('provinsi_tujuan_id', $provTujuan)
            ->whereDate('tanggal_berlaku', '<=', $tanggal)
            ->orderBy('tanggal_berlaku', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        // Level 2: Fallback - semua tujuan dari provinsi asal
        if (!$plafon) {
            $plafon = SettingPlafonPerjalananDinas::where('level_id', $user->level_id)
                ->where('provinsi_awal_id', $provAsal)
                ->whereNull('provinsi_tujuan_id')
                ->whereDate('tanggal_berlaku', '<=', $tanggal)
                ->orderBy('tanggal_berlaku', 'desc')
                ->orderBy('id', 'desc')
                ->first();
        }

        // Level 3: Fallback - default level (semua rute)
        if (!$plafon) {
            $plafon = SettingPlafonPerjalananDinas::where('level_id', $user->level_id)
                ->whereNull('provinsi_awal_id')
                ->whereNull('provinsi_tujuan_id')
                ->whereDate('tanggal_berlaku', '<=', $tanggal)
                ->orderBy('tanggal_berlaku', 'desc')
                ->orderBy('id', 'desc')
                ->first();
        }

        return $plafon;
    }


    public function resolveKelompokHariKerjaId($user)
{
    if (!$user->hari_kerja_user) return null;

    // Jika sudah berupa angka (ID), pakai langsung
    if (is_numeric($user->hari_kerja_user)) {
        return (int) $user->hari_kerja_user;
    }

    // Jika berupa code seperti '5 HK', cari ID-nya di tabel weekday_group
    $wg = \App\Models\WeekdayGroup::where('code', $user->hari_kerja_user)->first();
    return $wg ? $wg->id : null;
}
    /**
     * 🎯 PERBAIKAN UTAMA: PLAFON EVENT MARKETING
     * Sekarang memfilter berdasarkan Kelompok Hari Kerja user (5 HK / 6 HK)
     */
    public function getPlafonEvent($modelClass, $user, $tanggal, $isLibur, $jamKerjaEventId)
{
    if (!$user->level_id) return null;

    $kelompokId = $this->resolveKelompokHariKerjaId($user);
    if (!$kelompokId) return null; // graceful: profil hari kerja kosong / tidak dikenali

    return $modelClass::where('level_id', $user->level_id)
        ->where('kelompok_hari_kerja_id', $kelompokId) // ✅ PAKAI HASIL RESOLVER (ID ketemu ID)
        ->where('hari_event', $this->mapHariEvent($tanggal, $isLibur))
        ->where('jam_kerja_event_id', $jamKerjaEventId)
        ->whereDate('tanggal_berlaku', '<=', $tanggal)
        ->orderBy('tanggal_berlaku', 'desc')
        ->orderBy('id', 'desc')
        ->first();
}

    /**
     * SOFT CONTROL: bandingkan nominal dengan plafon
     */
    public function check($plafon, $nominal): array
    {
        if (!$plafon) {
            return [
                'is_over'      => false,
                'plafon_value' => null,
                'selisih'      => 0,
                'sisa'         => 0,
                'message'      => '⚠️ Plafon untuk kombinasi ini belum disetting.',
            ];
        }

        $plafonValue = (float) $plafon->default_value;
        $over = $nominal > $plafonValue;

        return [
            'is_over'      => $over,
            'plafon_value' => $plafonValue,
            'selisih'      => $over ? ($nominal - $plafonValue) : 0,
            'sisa'         => $over ? 0 : ($plafonValue - $nominal),
            'message'      => $over ? '⚠️ Melebihi plafon!' : '✅ Masih dalam plafon.',
        ];
    }

    public function checkPerjalananDinas($user, $tujuanWilayahId, $tanggal, $nominal): array
    {
        return $this->check(
            $this->getPlafonPerjalananDinas($user, $tujuanWilayahId, $tanggal),
            $nominal
        );
    }

    public function checkEvent($modelClass, $user, $tanggal, $isLibur, $jamKerjaEventId, $nominal): array
    {
        return $this->check(
            $this->getPlafonEvent($modelClass, $user, $tanggal, $isLibur, $jamKerjaEventId),
            $nominal
        );
    }
}