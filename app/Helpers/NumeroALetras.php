<?php

namespace App\Helpers;

/**
 * Convierte montos a palabras en español para facturas.
 * Ej: 101.69 → "CIENTO UNO 69/100 DOLARES"
 */
class NumeroALetras
{
    private const UNIDADES = [
        '', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE',
        'DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISÉIS', 'DIECISIETE',
        'DIECIOCHO', 'DIECINUEVE', 'VEINTE',
    ];

    private const DECENAS = [
        '', '', 'VEINTI', 'TREINTA', 'CUARENTA', 'CINCUENTA',
        'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA',
    ];

    private const CENTENAS = [
        '', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS',
        'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS',
    ];

    public static function convertir(float $monto, string $moneda = 'DOLARES'): string
    {
        $entero = (int) floor($monto);
        $decimales = (int) round(($monto - $entero) * 100);

        if ($decimales === 100) {
            $entero++;
            $decimales = 0;
        }

        $palabras = $entero === 0 ? 'CERO' : self::grupo($entero);

        return $palabras . ' ' . str_pad((string) $decimales, 2, '0', STR_PAD_LEFT) . '/100 ' . $moneda;
    }

    private static function grupo(int $n): string
    {
        if ($n < 0) return 'MENOS ' . self::grupo(-$n);
        if ($n <= 20) return self::UNIDADES[$n];
        if ($n < 100) {
            $d = intdiv($n, 10);
            $r = $n % 10;
            if ($d === 2) return 'VEINTI' . ($r > 0 ? self::UNIDADES[$r] : '');
            return self::DECENAS[$d] . ($r > 0 ? ' Y ' . self::UNIDADES[$r] : '');
        }
        if ($n < 1000) {
            if ($n === 100) return 'CIEN';
            $c = intdiv($n, 100);
            $r = $n % 100;
            return self::CENTENAS[$c] . ($r > 0 ? ' ' . self::grupo($r) : '');
        }
        if ($n < 1000000) {
            $m = intdiv($n, 1000);
            $r = $n % 1000;
            $miles = $m === 1 ? 'MIL' : self::grupo($m) . ' MIL';
            return $miles . ($r > 0 ? ' ' . self::grupo($r) : '');
        }
        $mm = intdiv($n, 1000000);
        $r = $n % 1000000;
        $millones = $mm === 1 ? 'UN MILLÓN' : self::grupo($mm) . ' MILLONES';
        return $millones . ($r > 0 ? ' ' . self::grupo($r) : '');
    }
}
