<?php

namespace App\Http\Controllers;

abstract class Controller
{
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
