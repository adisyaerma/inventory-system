<?php

namespace App\Imports\Concerns;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;

/**
 * Shared, defensive Excel date parsing — used by every Import class so
 * behaviour stays identical everywhere. Never throws: an unparseable date
 * always comes back as null instead of blocking the row from being saved.
 */
trait ParsesExcelDates
{
    private function parseDate($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Real Excel date cells often come back as a native DateTime/Carbon
        // object (not a string or serial number) — must be checked first.
        // Passing an object into trim()/is_numeric() below would either
        // throw a TypeError or silently misbehave.
        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->startOfDay();
        }

        // Excel serial number (raw, unformatted date cell).
        if (is_numeric($value)) {
            try {
                return Carbon::instance(
                    Date::excelToDateTimeObject($value)
                )->startOfDay();
            } catch (\Throwable $e) {
                return null;
            }
        }

        $value = trim((string) $value);

        if ($value === '' || $value === '-') {
            return null;
        }

        // Strip a trailing time part if present, e.g. "3/6/2026 00:00:00"
        $value = preg_replace('/\s+\d{1,2}:\d{2}(:\d{2})?$/', '', $value);

        // dd/mm/yyyy, dd-mm-yyyy, or dd.mm.yyyy — zero-padded or not, with
        // either a 4-digit or 2-digit year, e.g. 05/03/2025, 5-3-2025,
        // 24/1/2026, 13/06/26
        if (preg_match('#^(\d{1,2})[/\-.](\d{1,2})[/\-.](\d{2}|\d{4})$#', $value, $m)) {
            $day = (int) $m[1];
            $month = (int) $m[2];
            $year = (int) $m[3];

            // 2-digit year, e.g. "26" -> 2026.
            if ($year < 100) {
                $year += ($year <= 68) ? 2000 : 1900;
            }

            // Safeguard: if the "month" slot is out of range (13-31) but
            // the "day" slot would be a valid month, that value was
            // actually written month-first — swap instead of dropping it.
            if ($month > 12 && $day <= 12) {
                [$day, $month] = [$month, $day];
            }

            try {
                return Carbon::createFromFormat('d/m/Y', sprintf('%02d/%02d/%04d', $day, $month, $year))->startOfDay();
            } catch (\Throwable $e) {
                return null;
            }
        }

        // yyyy-mm-dd (ISO), e.g. 2025-03-05
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            try {
                return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
            } catch (\Throwable $e) {
                return null;
            }
        }

        // Coba beberapa format yang umum dipakai
        $formats = [
            'd/m/Y',
            'd-m-Y',
            'd.m.Y',
            'd/m/y',
            'd-m-y',
            'd.m.y',
            'Y-m-d',
        ];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->startOfDay();
            } catch (\Throwable $e) {
                // coba format berikutnya
            }
        }

        // Terakhir, hanya gunakan Carbon::parse jika string mengandung nama bulan,
        // misalnya "5 Maret 2026" atau "March 5, 2026".
        if (preg_match('/[a-zA-Z]/', $value)) {
            try {
                return Carbon::parse($value)->startOfDay();
            } catch (\Throwable $e) {
                //
            }
        }

        return null;
    }
}
