<?php

if (!function_exists('statusUsiaHewan')) {
    /**
     * Menentukan status usia hewan.
     * Mengembalikan 'Dewasa' jika usia >= 3 tahun, dan 'Muda' jika di bawah 3 tahun.
     *
     * @param int|float|string $usia
     * @return string
     */
    function statusUsiaHewan($usia): string
    {
        return ((int) $usia >= 3) ? 'Dewasa' : 'Muda';
    }
}
