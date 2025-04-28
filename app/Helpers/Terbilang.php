<?php

namespace App\Helpers;

class Terbilang
{
    public static function terbilang($angka)
    {
        $angka = (int) $angka; // Pastikan angka adalah tipe integer
        $huruf = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan"];
        $unit = ["", "ribu", "juta", "miliar", "triliun"];
        $temp = "";

        // Jika angka 0
        if ($angka == 0) {
            return strtolower("nol rupiah");
        }

        // Menghitung posisi unit (ribu, juta, miliar, dll)
        $unitIndex = 0;
        while ($angka > 0) {
            $part = $angka % 1000; // Ambil bagian ribuan, jutaan, dll
            if ($part > 0) {
                $temp = self::convertToWords($part, $huruf) . ($unitIndex > 0 ? " " . $unit[$unitIndex] : "") . " " . $temp;
            }
            $angka = floor($angka / 1000); // Hilangkan bagian terakhir (ribu, juta, dll)
            $unitIndex++;
        }
        return strtolower(trim($temp)) . " rupiah";
    }

    private static function convertToWords($angka, $huruf)
    {
        $temp = "";

        // Untuk menangani ratusan
        if ($angka >= 100) {
            $temp .= $huruf[(int)($angka / 100)] . " ratus ";
            $angka %= 100;
        }

        // Untuk puluhan
        if ($angka >= 20) {
            $temp .= $huruf[(int)($angka / 10)] . " puluh ";
            $angka %= 10;
        }

        // Untuk angka satuan
        if ($angka > 0) {
            // Cek apakah indeks ada dalam array $huruf
            if (isset($huruf[$angka])) {
                $temp .= $huruf[$angka];
            } else {
                // Jika tidak ada, lakukan sesuatu (misalnya, mengabaikan atau memberikan nilai default)
                $temp .= '';
            }
        }

        return trim($temp);
    }
}
