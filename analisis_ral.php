<?php
// analisis_ral.php
require_once __DIR__ . '/vendor/autoload.php';

use MathPHP\Probability\Distribution\Continuous\F;
use MathPHP\Probability\Distribution\Continuous\StudentT;
use MathPHP\Probability\Distribution\Continuous\Normal;
use MathPHP\Statistics\ANOVA;

session_start();

// Set headers untuk JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Fungsi untuk mendapatkan nilai F-tabel menggunakan distribusi F
function getFTabel($alpha, $df1, $df2)
{
    try {
        $fDistribution = new F($df1, $df2);
        return $fDistribution->inverse(1 - $alpha);
    } catch (Exception $e) {
        // Fallback jika error
        return 3.84; // Nilai default untuk df1=1, df2=∞, alpha=0.05
    }
}

// Fungsi untuk mendapatkan nilai t-tabel menggunakan distribusi t
function getTTabel($alpha, $df)
{
    try {
        $tDistribution = new StudentT($df);
        return $tDistribution->inverse(1 - $alpha / 2); // Two-tailed
    } catch (Exception $e) {
        // Fallback jika error
        return 2.0;
    }
}

// Fungsi uji Kolmogorov-Smirnov
function kolmogorovSmirnovTest($data, $alpha)
{
    if (count($data) < 5) {
        return null;
    }

    $n = count($data);
    $mean = array_sum($data) / $n;
    $sd = sqrt(array_sum(array_map(function ($x) use ($mean) {
        return pow($x - $mean, 2);
    }, $data)) / ($n - 1));

    // Sort data
    sort($data);

    // Hitung CDF empiris
    $d_plus = [];
    $d_minus = [];

    for ($i = 0; $i < $n; $i++) {
        $x = $data[$i];
        // CDF normal menggunakan distribusi Normal
        try {
            $normal = new Normal($mean, $sd);
            $cdf_normal = $normal->cdf($x);
        } catch (Exception $e) {
            // Fallback menggunakan fungsi erf
            $cdf_normal = 0.5 * (1 + erf(($x - $mean) / ($sd * sqrt(2))));
        }

        $cdf_empirical = ($i + 1) / $n;

        $d_plus[] = $cdf_empirical - $cdf_normal;
        $d_minus[] = $cdf_normal - ($i / $n);
    }

    $d_statistic = max(max($d_plus), max($d_minus));

    // Hitung p-value aproksimasi (formula KS untuk n besar)
    $p_value = exp(-2 * $n * pow($d_statistic, 2));

    return [
        'stat' => $d_statistic,
        'p_value' => $p_value,
        'normal' => $p_value > $alpha
    ];
}

// Fungsi error function untuk distribusi normal
function erf($x)
{
    $t = 1 / (1 + 0.5 * abs($x));
    $tau = $t * exp(-$x * $x - 1.26551223 +
        $t * (1.00002368 +
            $t * (0.37409196 +
                $t * (0.09678418 +
                    $t * (-0.18628806 +
                        $t * (0.27886807 +
                            $t * (-1.13520398 +
                                $t * (1.48851587 +
                                    $t * (-0.82215223 +
                                        $t * 0.17087277)))))))));
    return $x >= 0 ? 1 - $tau : $tau - 1;
}

// Fungsi uji Lilliefors
function lillieforsTest($data, $alpha)
{
    if (count($data) < 5) {
        return null;
    }

    $n = count($data);
    $mean = array_sum($data) / $n;
    $sd = sqrt(array_sum(array_map(function ($x) use ($mean) {
        return pow($x - $mean, 2);
    }, $data)) / ($n - 1));

    // Standardize data
    $standardized = array_map(function ($x) use ($mean, $sd) {
        return ($x - $mean) / $sd;
    }, $data);

    sort($standardized);

    $d_plus = [];
    $d_minus = [];

    for ($i = 0; $i < $n; $i++) {
        $x = $standardized[$i];
        // CDF normal standar
        try {
            $normal = new Normal(0, 1);
            $cdf_normal = $normal->cdf($x);
        } catch (Exception $e) {
            $cdf_normal = 0.5 * (1 + erf($x / sqrt(2)));
        }

        $cdf_empirical = ($i + 1) / $n;

        $d_plus[] = $cdf_empirical - $cdf_normal;
        $d_minus[] = $cdf_normal - ($i / $n);
    }

    $d_statistic = max(max($d_plus), max($d_minus));

    // Tabel kritis Lilliefors (untuk alpha = 0.05)
    $critical_values = [
        5 => 0.315,
        10 => 0.258,
        15 => 0.231,
        20 => 0.210,
        25 => 0.195,
        30 => 0.184,
        40 => 0.165,
        50 => 0.148,
        100 => 0.107,
        200 => 0.077,
        500 => 0.049,
        1000 => 0.035
    ];

    // Interpolasi untuk n
    $critical_value = 0.0;
    $ns = array_keys($critical_values);

    if ($n <= $ns[0]) {
        $critical_value = $critical_values[$ns[0]];
    } elseif ($n >= end($ns)) {
        $critical_value = $critical_values[end($ns)];
    } else {
        // Interpolasi linier pada skala log
        for ($i = 0; $i < count($ns) - 1; $i++) {
            if ($n >= $ns[$i] && $n <= $ns[$i + 1]) {
                $x1 = $ns[$i];
                $x2 = $ns[$i + 1];
                $y1 = $critical_values[$x1];
                $y2 = $critical_values[$x2];
                $critical_value = $y1 + ($y2 - $y1) * (log($n) - log($x1)) / (log($x2) - log($x1));
                break;
            }
        }
    }

    // Adjust untuk alpha berbeda
    if ($alpha == 0.01) {
        $critical_value *= 1.031; // Adjust untuk alpha 0.01
    } elseif ($alpha == 0.10) {
        $critical_value *= 0.900; // Adjust untuk alpha 0.10
    }

    return [
        'stat' => $d_statistic,
        'critical_value' => $critical_value,
        'normal' => $d_statistic < $critical_value
    ];
}

// Tangani request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Baca data JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['data']) || !isset($input['alpha'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit;
}

// Ekstrak data
$data = $input['data'];
$alpha = floatval($input['alpha']);
$perlakuan = isset($input['perlakuan']) ? intval($input['perlakuan']) : count($data);
$ulangan = isset($input['ulangan']) ? intval($input['ulangan']) : (count($data) > 0 ? count($data[0]) : 0);

// Validasi data
if ($perlakuan < 1 || $ulangan < 1) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid number of treatments or replications']);
    exit;
}

try {
    // ==================== PERHITUNGAN ANOVA RAL ====================

    // 1. Hitung total dan rata-rata
    $grand_total = 0;
    $grand_total_squared = 0;
    $n_total = $perlakuan * $ulangan;

    $perlakuan_totals = [];
    $perlakuan_means = [];

    for ($i = 0; $i < $perlakuan; $i++) {
        $perlakuan_total = 0;
        for ($j = 0; $j < $ulangan; $j++) {
            $value = floatval($data[$i][$j] ?? 0);
            $perlakuan_total += $value;
            $grand_total += $value;
            $grand_total_squared += $value * $value;
        }
        $perlakuan_totals[$i] = $perlakuan_total;
        $perlakuan_means[$i] = $perlakuan_total / $ulangan;
    }

    $grand_mean = $grand_total / $n_total;

    // 2. Hitung Jumlah Kuadrat (JK)
    // JK Total
    $jk_total = $grand_total_squared - pow($grand_total, 2) / $n_total;

    // JK Perlakuan
    $jk_perlakuan = 0;
    foreach ($perlakuan_totals as $total) {
        $jk_perlakuan += pow($total, 2) / $ulangan;
    }
    $jk_perlakuan -= pow($grand_total, 2) / $n_total;

    // JK Galat
    $jk_galat = $jk_total - $jk_perlakuan;

    // 3. Hitung Derajat Bebas (db)
    $db_perlakuan = $perlakuan - 1;
    $db_galat = $n_total - $perlakuan;
    $db_total = $n_total - 1;

    // 4. Hitung Kuadrat Tengah (KT)
    $kt_perlakuan = $jk_perlakuan / $db_perlakuan;
    $kt_galat = $jk_galat / $db_galat;

    // 5. Hitung F-hit
    $f_hit = ($kt_galat > 0) ? $kt_perlakuan / $kt_galat : 0;

    // 6. Hitung F-tabel menggunakan distribusi F
    $f_tabel = getFTabel($alpha, $db_perlakuan, $db_galat);

    // 7. Hitung p-value menggunakan distribusi F
    try {
        $fDistribution = new F($db_perlakuan, $db_galat);
        $p_value = 1 - $fDistribution->cdf($f_hit);
    } catch (Exception $e) {
        // Aproksimasi p-value jika error
        $p_value = exp(-$f_hit / 2);
    }

    // 8. Tentukan signifikansi
    $significant = $f_hit > $f_tabel;

    // ==================== PERHITUNGAN KOEFISIEN KERAGAMAN (KK) ====================
    $kk = ($kt_galat > 0 && $grand_mean != 0) ? (sqrt($kt_galat) / $grand_mean) * 100 : 0;

    // ==================== HITUNG RESIDUAL UNTUK UJI NORMALITAS ====================
    $residuals = [];
    $fitted_values = [];

    for ($i = 0; $i < $perlakuan; $i++) {
        for ($j = 0; $j < $ulangan; $j++) {
            $observed = floatval($data[$i][$j] ?? 0);
            $fitted = $perlakuan_means[$i];
            $residual = $observed - $fitted;

            $residuals[] = $residual;
            $fitted_values[] = $fitted;
        }
    }

    // ==================== UJI NORMALITAS ====================
    $normality_results = [
        'ks' => null,
        'lilliefors' => null
    ];

    $normality_requested = $input['normality'] ?? ['ks' => true, 'lilliefors' => true];

    if (($normality_requested['ks'] ?? false) && count($residuals) >= 5) {
        $normality_results['ks'] = kolmogorovSmirnovTest($residuals, $alpha);
    }

    if (($normality_requested['lilliefors'] ?? false) && count($residuals) >= 5) {
        $normality_results['lilliefors'] = lillieforsTest($residuals, $alpha);
    }

    // ==================== HITUNG KEBUTUHAN LAIN ====================

    // Rata-rata umum
    $rata_rata_umum = $grand_mean;

    // Hitung standard error
    $standard_error = sqrt($kt_galat / $ulangan);

    // Hitung confidence interval (95%)
    $t_value = getTTabel($alpha, $db_galat);
    $ci_lower = $grand_mean - $t_value * sqrt($kt_galat / $n_total);
    $ci_upper = $grand_mean + $t_value * sqrt($kt_galat / $n_total);

    // ==================== PREPARE RESPONSE ====================
    $response = [
        'success' => true,
        'data' => [
            'design' => 'RAL',
            'perlakuan' => $perlakuan,
            'ulangan' => $ulangan,
            'n_total' => $n_total,
            'alpha' => $alpha,

            // Hasil ANOVA
            'jk_total' => $jk_total,
            'jk_perlakuan' => $jk_perlakuan,
            'jk_galat' => $jk_galat,

            'db_total' => $db_total,
            'db_perlakuan' => $db_perlakuan,
            'db_galat' => $db_galat,

            'kt_perlakuan' => $kt_perlakuan,
            'kt_galat' => $kt_galat,

            'f_hit' => $f_hit,
            'f_tabel' => $f_tabel,
            'p_value' => $p_value,
            'significant' => $significant,

            // Statistik deskriptif
            'rata_rata_umum' => $rata_rata_umum,
            'rata_rata_perlakuan' => $perlakuan_means,
            'total_perlakuan' => $perlakuan_totals,
            'grand_total' => $grand_total,

            // Koefisien keragaman
            'kk' => $kk,
            'standard_error' => $standard_error,

            // Confidence interval
            'confidence_interval' => [
                'lower' => $ci_lower,
                'upper' => $ci_upper,
                'level' => (1 - $alpha) * 100
            ],

            // Data untuk plotting
            'residuals' => $residuals,
            'fitted' => $fitted_values,

            // Uji normalitas
            'normality' => $normality_results,

            // Raw data (untuk reference)
            'raw_data' => $data,

            // Metadata
            'calculated_at' => date('Y-m-d H:i:s'),
            'version' => 'STATDEN v3.0'
        ],
        'message' => 'Analisis RAL berhasil dihitung'
    ];

    // Simpan ke session jika diperlukan
    $_SESSION['anova_results'] = $response['data'];

    echo json_encode($response, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error dalam perhitungan: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
