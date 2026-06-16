<?php

namespace App\Helpers;

use Illuminate\Validation\Rule;

class SasaranInputRules
{
    public static function nikRule(bool $required = true): string
    {
        return ($required ? 'required|' : 'nullable|') . 'digits:16';
    }

    public static function noKkRule(bool $required = true): string
    {
        return ($required ? 'required|' : 'nullable|') . 'digits:16';
    }

    public static function pekerjaanRule(bool $required = true): array
    {
        return array_filter([
            $required ? 'required' : 'nullable',
            Rule::in(EnumConstants::pekerjaan()),
        ]);
    }

    public static function pendidikanRule(bool $required = false): array
    {
        return array_filter([
            $required ? 'required' : 'nullable',
            Rule::in(EnumConstants::pendidikan()),
        ]);
    }

    public static function nikMessages(string $field, string $label = 'NIK'): array
    {
        return [
            "{$field}.required" => "{$label} wajib diisi.",
            "{$field}.digits" => "{$label} harus 16 digit angka.",
            "{$field}.numeric" => "{$label} harus berupa angka.",
        ];
    }

    public static function noKkMessages(string $field, string $label = 'No KK'): array
    {
        return [
            "{$field}.required" => "{$label} wajib diisi.",
            "{$field}.digits" => "{$label} harus 16 digit angka.",
            "{$field}.numeric" => "{$label} harus berupa angka.",
        ];
    }

    public static function pekerjaanMessages(string $field, string $label = 'Pekerjaan'): array
    {
        return [
            "{$field}.required" => "{$label} wajib dipilih.",
            "{$field}.in" => "{$label} yang dipilih tidak valid.",
        ];
    }

    public static function pendidikanMessages(string $field, string $label = 'Pendidikan'): array
    {
        return [
            "{$field}.required" => "{$label} wajib dipilih.",
            "{$field}.in" => "{$label} yang dipilih tidak valid.",
        ];
    }
}
