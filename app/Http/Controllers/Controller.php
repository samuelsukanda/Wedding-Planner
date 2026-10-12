<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Batas atas kolom uang. Semua kolom decimal(15,2) di MySQL punya presisi 15
     * dengan skala 2, jadi hanya 13 digit bulat yang muat; angka yang lebih
     * panjang lolos validasi numeric lalu meledak jadi QueryException
     * SQLSTATE[22003] saat disimpan. Batas ini sama dengan yang dipotong
     * helper wpDigits di sisi browser, jadi input dan validasi tidak berbeda.
     */
    protected const MONEY_MAX = 9999999999999;

    /**
     * Aturan validasi untuk input uang: wajib, angka, tidak negatif, dan
     * tidak melebihi kapasitas kolom database.
     */
    protected function moneyRule(bool $required = true): string
    {
        return ($required ? 'required' : 'nullable')
            . '|numeric|min:0|max:' . static::MONEY_MAX;
    }

    /**
     * Input uang & telepon dikirim terformat ("Rp 1.000.000", "0812-3456").
     * Simpan hanya angkanya supaya validasi numeric dan link wa.me tetap aman.
     */
    protected function normalizeDigitInputs(\Illuminate\Http\Request $request, array $fields): void
    {
        foreach ($fields as $field) {
            if ($request->filled($field)) {
                $request->merge([$field => preg_replace('/\D/', '', (string) $request->input($field))]);
            }
        }
    }
}
