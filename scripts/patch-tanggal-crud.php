<?php

/**
 * Transform CRUD traits from hari/bulan/tahun to TanggalInput d/m/Y.
 */

$root = dirname(__DIR__);

function ensureImport(string $content): string
{
    if (str_contains($content, 'use App\\Helpers\\TanggalInput;')) {
        return $content;
    }

    if (str_contains($content, 'use App\\Helpers\\SasaranInputRules;')) {
        return str_replace(
            'use App\\Helpers\\SasaranInputRules;',
            "use App\\Helpers\\SasaranInputRules;\nuse App\\Helpers\\TanggalInput;",
            $content
        );
    }

    // After namespace use block — insert after first use statement
    return preg_replace(
        '/^(namespace [^;]+;\s+)/m',
        "$1\nuse App\\Helpers\\TanggalInput;\n",
        $content,
        1
    );
}

/**
 * @param  array{suffix: string, tanggalProp: string, hari: string, bulan: string, tahun: string, label: string, required?: bool, hasUmur?: bool, umurProp?: string, combineMethod?: string, updatedMethods?: list<string>, calculateMethod?: ?string}  $cfg
 */
function transformSimpleTrait(string $path, array $cfg): void
{
    $c = file_get_contents($path);
    $c = ensureImport($c);

    $hari = $cfg['hari'];
    $bulan = $cfg['bulan'];
    $tahun = $cfg['tahun'];
    $tanggal = $cfg['tanggalProp'];
    $label = $cfg['label'];
    $required = $cfg['required'] ?? true;

    // Remove property declarations
    $c = preg_replace('/\s*public \$' . preg_quote($hari, '/') . ';\s*/', "\n", $c);
    $c = preg_replace('/\s*public \$' . preg_quote($bulan, '/') . ';\s*/', "\n", $c);
    $c = preg_replace('/\s*public \$' . preg_quote($tahun, '/') . ';\s*/', "\n", $c);

    // Remove reset lines
    $c = preg_replace('/\s*\$this->' . preg_quote($hari, '/') . ' = \'\';\s*/', "\n", $c);
    $c = preg_replace('/\s*\$this->' . preg_quote($bulan, '/') . ' = \'\';\s*/', "\n", $c);
    $c = preg_replace('/\s*\$this->' . preg_quote($tahun, '/') . ' = \'\';\s*/', "\n", $c);

    // Remove combine call
    if (! empty($cfg['combineMethod'])) {
        $c = preg_replace(
            '/\s*\/\/ Combine hari, bulan, tahun menjadi tanggal lahir\s*\$this->' . preg_quote($cfg['combineMethod'], '/') . '\(\);\s*/',
            "\n",
            $c
        );
        $c = preg_replace(
            '/\s*\$this->' . preg_quote($cfg['combineMethod'], '/') . '\(\);\s*/',
            "\n",
            $c
        );
    }

    // Replace validation rules block for hari/bulan/tahun/tanggal
    $rulesReplacement = "'" . $tanggal . "' => TanggalInput::rules(" . ($required ? 'true' : 'false') . "),";
    $c = preg_replace(
        "/'" . preg_quote($hari, '/') . "' => 'required\\|numeric\\|min:1\\|max:31',\\s*"
        . "'" . preg_quote($bulan, '/') . "' => 'required\\|numeric\\|min:1\\|max:12',\\s*"
        . "'" . preg_quote($tahun, '/') . "' => 'required\\|numeric\\|min:1900\\|max:' \\. date\\('Y'\\),\\s*"
        . "'" . preg_quote($tanggal, '/') . "' => 'required\\|date',/",
        $rulesReplacement,
        $c
    );

    // Optional nullable variants for suami
    $c = preg_replace(
        "/'" . preg_quote($hari, '/') . "' => 'nullable\\|numeric\\|min:1\\|max:31',\\s*"
        . "'" . preg_quote($bulan, '/') . "' => 'nullable\\|numeric\\|min:1\\|max:12',\\s*"
        . "'" . preg_quote($tahun, '/') . "' => 'nullable\\|numeric\\|min:1900\\|max:' \\. date\\('Y'\\),\\s*"
        . "'" . preg_quote($tanggal, '/') . "' => 'nullable\\|date',/",
        "'" . $tanggal . "' => TanggalInput::rules(false),",
        $c
    );

    // Remove old message keys for hari/bulan/tahun and tanggal.date
    $c = preg_replace(
        "/\\s*'" . preg_quote($hari, '/') . "\\.[^']+' => '[^']*',\\s*/",
        "\n",
        $c
    );
    $c = preg_replace(
        "/\\s*'" . preg_quote($bulan, '/') . "\\.[^']+' => '[^']*',\\s*/",
        "\n",
        $c
    );
    $c = preg_replace(
        "/\\s*'" . preg_quote($tahun, '/') . "\\.[^']+' => '[^']*',\\s*/",
        "\n",
        $c
    );
    $c = preg_replace(
        "/\\s*'" . preg_quote($tanggal, '/') . "\\.required' => '[^']*',\\s*/",
        "\n",
        $c
    );
    $c = preg_replace(
        "/\\s*'" . preg_quote($tanggal, '/') . "\\.date' => '[^']*',\\s*/",
        "\n",
        $c
    );

    // Add TanggalInput messages into validate second arg if not present
    if (! str_contains($c, "TanggalInput::messages('{$tanggal}'")) {
        // Try: ], [  after validate rules closing
        $c = preg_replace(
            "/(\\$this->validate\\(\\[[\\s\\S]*?\\],)\\s*(\\[[\\s\\S]*?)(\\n\\s*\\]\\);)/",
            "$1 array_merge($2, TanggalInput::messages('{$tanggal}', '{$label}'))$3",
            $c,
            1
        );
    }

    echo "Transformed base: $path\n";
    file_put_contents($path, $c);
}

// For reliability, do targeted file-by-file rewrites for the simpler traits using full rewrite of critical sections via Task...
// Instead: manually patch Orangtua and Dewasa completely with known good content patterns via search-replace in this script.

function patchFile(string $path, array $replacements): void
{
    $c = file_get_contents($path);
    $c = ensureImport($c);
    foreach ($replacements as [$from, $to]) {
        if (! str_contains($c, $from)) {
            echo "WARN missing needle in $path: " . substr($from, 0, 80) . "...\n";
            continue;
        }
        $c = str_replace($from, $to, $c);
    }
    file_put_contents($path, $c);
    echo "OK $path\n";
}

// ---- OrangtuaCrud ----
patchFile($root . '/app/Livewire/SuperAdmin/Traits/OrangtuaCrud.php', [
    [
        "    public \$tanggal_lahir_orangtua;\n    public \$hari_lahir_orangtua;\n    public \$bulan_lahir_orangtua;\n    public \$tahun_lahir_orangtua;\n",
        "    public \$tanggal_lahir_orangtua;\n",
    ],
    [
        "        \$this->tanggal_lahir_orangtua = '';\n        \$this->hari_lahir_orangtua = '';\n        \$this->bulan_lahir_orangtua = '';\n        \$this->tahun_lahir_orangtua = '';\n",
        "        \$this->tanggal_lahir_orangtua = '';\n",
    ],
    [
        "        // Combine hari, bulan, tahun menjadi tanggal lahir\n        \$this->combineTanggalLahirOrangtua();\n\n        \$this->validate([",
        "        \$this->validate([",
    ],
    [
        "            'hari_lahir_orangtua' => 'required|numeric|min:1|max:31',\n            'bulan_lahir_orangtua' => 'required|numeric|min:1|max:12',\n            'tahun_lahir_orangtua' => 'required|numeric|min:1900|max:' . date('Y'),\n            'tanggal_lahir_orangtua' => 'required|date',\n",
        "            'tanggal_lahir_orangtua' => TanggalInput::rules(true),\n",
    ],
    [
        "            'hari_lahir_orangtua.required' => 'Hari lahir wajib diisi.',\n            'hari_lahir_orangtua.numeric' => 'Hari harus berupa angka.',\n            'hari_lahir_orangtua.min' => 'Hari minimal 1.',\n            'hari_lahir_orangtua.max' => 'Hari maksimal 31.',\n            'bulan_lahir_orangtua.required' => 'Bulan lahir wajib diisi.',\n            'bulan_lahir_orangtua.numeric' => 'Bulan harus berupa angka.',\n            'bulan_lahir_orangtua.min' => 'Bulan minimal 1.',\n            'bulan_lahir_orangtua.max' => 'Bulan maksimal 12.',\n            'tahun_lahir_orangtua.required' => 'Tahun lahir wajib diisi.',\n            'tahun_lahir_orangtua.numeric' => 'Tahun harus berupa angka.',\n            'tahun_lahir_orangtua.min' => 'Tahun minimal 1900.',\n            'tahun_lahir_orangtua.max' => 'Tahun maksimal ' . date('Y') . '.',\n            'tanggal_lahir_orangtua.required' => 'Tanggal lahir wajib diisi.',\n            'tanggal_lahir_orangtua.date' => 'Tanggal lahir harus berupa tanggal yang valid.',\n",
        "",
    ],
    [
        "        ], [\n            ...SasaranInputRules::nikMessages('nik_orangtua'),",
        "        ], array_merge([\n            ...SasaranInputRules::nikMessages('nik_orangtua'),",
    ],
    [
        "            'nomor_telepon_orangtua.max' => 'Nomor telepon maksimal 20 karakter.',\n        ]);\n\n        \$data = [\n            'nik' => \$this->nik_orangtua,\n            'nama' => \$this->nama_orangtua,\n            'no_kk' => \$this->no_kk_orangtua ?: null,\n            'tempat_lahir' => \$this->tempat_lahir_orangtua,\n            'tanggal_lahir' => \$this->tanggal_lahir_orangtua,\n",
        "            'nomor_telepon_orangtua.max' => 'Nomor telepon maksimal 20 karakter.',\n        ], TanggalInput::messages('tanggal_lahir_orangtua', 'Tanggal lahir')));\n\n        \$data = [\n            'nik' => \$this->nik_orangtua,\n            'nama' => \$this->nama_orangtua,\n            'no_kk' => \$this->no_kk_orangtua ?: null,\n            'tempat_lahir' => \$this->tempat_lahir_orangtua,\n            'tanggal_lahir' => TanggalInput::toYmd(\$this->tanggal_lahir_orangtua),\n",
    ],
    [
        "        \$this->tanggal_lahir_orangtua = \$orangtua->tanggal_lahir ? \$orangtua->tanggal_lahir->format('Y-m-d') : '';\n        // Split tanggal lahir menjadi hari, bulan, tahun\n        if (\$orangtua->tanggal_lahir) {\n            \$date = Carbon::parse(\$orangtua->tanggal_lahir);\n            \$this->hari_lahir_orangtua = \$date->day;\n            \$this->bulan_lahir_orangtua = \$date->month;\n            \$this->tahun_lahir_orangtua = \$date->year;\n        } else {\n            \$this->hari_lahir_orangtua = '';\n            \$this->bulan_lahir_orangtua = '';\n            \$this->tahun_lahir_orangtua = '';\n        }\n",
        "        \$this->tanggal_lahir_orangtua = TanggalInput::toDisplay(\$orangtua->tanggal_lahir);\n",
    ],
    [
        "    /**\n     * Combine hari, bulan, tahun menjadi tanggal lahir\n     */\n    private function combineTanggalLahirOrangtua()\n    {\n        if (\$this->hari_lahir_orangtua && \$this->bulan_lahir_orangtua && \$this->tahun_lahir_orangtua) {\n            try {\n                \$this->tanggal_lahir_orangtua = Carbon::create(\n                    \$this->tahun_lahir_orangtua,\n                    \$this->bulan_lahir_orangtua,\n                    \$this->hari_lahir_orangtua\n                )->format('Y-m-d');\n            } catch (\\Exception \$e) {\n                \$this->tanggal_lahir_orangtua = null;\n            }\n        } else {\n            \$this->tanggal_lahir_orangtua = null;\n        }\n    }\n",
        "",
    ],
]);

echo "Orangtua done\n";
