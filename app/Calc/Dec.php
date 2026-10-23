<?php

namespace App\Calc;

/**
 * Десятичная арифметика на bcmath: смета округляет вверх (CEILING), и на float
 * 26269.4 × 20 легко превращается в 525388.0000001 → лишний мешок. Всё считаем строками.
 */
final class Dec
{
    public const SCALE = 6;

    public static function of(int|float|string|null $v): string
    {
        if ($v === null || $v === '') {
            return '0';
        }
        if (is_float($v)) {
            // 0.1 + 0.2 не должно протечь в смету: берём кратчайшее точное представление
            $v = rtrim(rtrim(sprintf('%.10F', $v), '0'), '.');
        }
        return bcadd((string) $v, '0', self::SCALE);
    }

    public static function add(string ...$xs): string
    {
        $s = '0';
        foreach ($xs as $x) {
            $s = bcadd($s, $x, self::SCALE);
        }
        return $s;
    }

    public static function sub(string $a, string $b): string
    {
        return bcsub($a, $b, self::SCALE);
    }

    public static function mul(string ...$xs): string
    {
        $p = '1';
        foreach ($xs as $x) {
            $p = bcmul($p, $x, self::SCALE);
        }
        return $p;
    }

    public static function div(string $a, string $b): string
    {
        return bccomp($b, '0', self::SCALE) === 0 ? '0' : bcdiv($a, $b, self::SCALE);
    }

    /** CEILING(x, step) как в Excel: вверх до кратного шагу. */
    public static function ceil(string $x, string $step): string
    {
        if (bccomp($step, '0', self::SCALE) <= 0) {
            return $x;
        }
        $q = bcdiv($x, $step, 0);
        if (bccomp(bcmul($q, $step, self::SCALE), $x, self::SCALE) < 0) {
            $q = bcadd($q, '1', 0);
        }
        return bcmul($q, $step, self::SCALE);
    }

    /** ROUND(x, 2) как в Excel: половина — от нуля. */
    public static function round2(string $x): string
    {
        $shift = bccomp($x, '0', self::SCALE) < 0 ? '-0.005' : '0.005';
        return bcadd(bcadd($x, $shift, self::SCALE), '0', 2);
    }

    public static function max0(string $x): string
    {
        return bccomp($x, '0', self::SCALE) < 0 ? '0' : $x;
    }

    public static function float(string $x): float
    {
        return (float) $x;
    }
}
