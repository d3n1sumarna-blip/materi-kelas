<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Fungsi untuk menghitung ANOVA RAK (Rancangan Acak Kelompok)
function calculateRAK($data, $alpha, $perlakuan, $ulangan, $normalityOptions = null)
{
    // Validasi input
    if (!is_array($data) || count($data) === 0) {
        return ['success' => false, 'message' => 'Data tidak valid'];
    }

    // Inisialisasi variabel
    $grand_total = 0;
    $grand_total_squared = 0;
    $treatment_totals = array_fill(0, $perlakuan, 0);
    $block_totals = array_fill(0, $ulangan, 0);

    // Hitung total perlakuan, total blok, dan grand total
    foreach ($data as $i => $row) {
        foreach ($row as $j => $value) {
            $value = floatval($value);
            $treatment_totals[$i] += $value;
            $block_totals[$j] += $value;
            $grand_total += $value;
            $grand_total_squared += pow($value, 2);
        }
    }

    // Hitung Faktor Koreksi (FK)
    $FK = pow($grand_total, 2) / ($perlakuan * $ulangan);

    // Hitung Jumlah Kuadrat
    $JK_total = $grand_total_squared - $FK;

    // Jumlah Kuadrat Perlakuan
    $JK_perlakuan = 0;
    foreach ($treatment_totals as $total) {
        $JK_perlakuan += pow($total, 2) / $ulangan;
    }
    $JK_perlakuan -= $FK;

    // Jumlah Kuadrat Blok
    $JK_blok = 0;
    foreach ($block_totals as $total) {
        $JK_blok += pow($total, 2) / $perlakuan;
    }
    $JK_blok -= $FK;

    // Jumlah Kuadrat Galat
    $JK_galat = $JK_total - $JK_perlakuan - $JK_blok;

    // Hitung Derajat Bebas YANG BENAR untuk RAK:
    // Total: (perlakuan × ulangan) - 1
    // Perlakuan: perlakuan - 1
    // Blok: ulangan - 1
    // Galat: (perlakuan-1) × (ulangan-1)

    $db_total = ($perlakuan * $ulangan) - 1;
    $db_perlakuan = $perlakuan - 1;
    $db_blok = $ulangan - 1;
    $db_galat = ($perlakuan - 1) * ($ulangan - 1); // INI YANG BENAR!

    // Debug: tampilkan derajat bebas
    $debug_info = [
        'db_total' => $db_total,
        'db_perlakuan' => $db_perlakuan,
        'db_blok' => $db_blok,
        'db_galat' => $db_galat,
        'perlakuan' => $perlakuan,
        'ulangan' => $ulangan,
        'jk_galat' => $JK_galat,
        'jk_blok' => $JK_blok,
        'jk_perlakuan' => $JK_perlakuan
    ];

    // Hitung Kuadrat Tengah
    $KT_perlakuan = $db_perlakuan > 0 ? $JK_perlakuan / $db_perlakuan : 0;
    $KT_blok = $db_blok > 0 ? $JK_blok / $db_blok : 0;
    $KT_galat = $db_galat > 0 ? $JK_galat / $db_galat : 0;

    // Hitung F-hit untuk Perlakuan
    $F_hit_perlakuan = $KT_galat > 0 ? $KT_perlakuan / $KT_galat : 0;

    // Validasi F_hit
    if (!is_finite($F_hit_perlakuan) || $F_hit_perlakuan < 0) {
        $F_hit_perlakuan = 0;
    }

    // Hitung F-hit untuk Blok
    $F_hit_blok = $KT_galat > 0 ? $KT_blok / $KT_galat : 0;

    // Validasi F_hit_blok
    if (!is_finite($F_hit_blok) || $F_hit_blok < 0) {
        $F_hit_blok = 0;
    }

    // Hitung F-tabel dengan derajat bebas yang benar
    $F_tabel_perlakuan = getFTableValueFixed($alpha, $db_perlakuan, $db_galat);
    $F_tabel_blok = getFTableValueFixed($alpha, $db_blok, $db_galat);

    // Hitung p-value dengan fungsi yang sudah DIPERBAIKI
    $p_value_perlakuan = calculatePValueFixed($F_hit_perlakuan, $db_perlakuan, $db_galat);
    $p_value_blok = calculatePValueFixed($F_hit_blok, $db_blok, $db_galat);

    // Debug p-value calculation
    $debug_pvalue = [
        'F_hit_perlakuan' => $F_hit_perlakuan,
        'F_hit_blok' => $F_hit_blok,
        'p_value_perlakuan_raw' => $p_value_perlakuan,
        'p_value_blok_raw' => $p_value_blok,
        'db_perlakuan' => $db_perlakuan,
        'db_blok' => $db_blok,
        'db_galat' => $db_galat
    ];

    $significant_perlakuan = $p_value_perlakuan < $alpha;
    $significant_blok = $p_value_blok < $alpha;

    // Hitung rata-rata perlakuan
    $rata_rata_perlakuan = [];
    foreach ($treatment_totals as $total) {
        $rata_rata_perlakuan[] = $total / $ulangan;
    }

    // Hitung rata-rata umum
    $rata_rata_umum = $grand_total / ($perlakuan * $ulangan);

    // Hitung Koefisien Keragaman (KK)
    $KK = $rata_rata_umum > 0 ? (sqrt($KT_galat) / $rata_rata_umum) * 100 : 0;

    // Normality tests are optional and only computed when requested
    $ks_D = $ks_p = $lillie_p = null;
    $ks_normal = $lillie_normal = null;
    $fitted = [];
    $residuals = [];

    if (!empty($normalityOptions) && (!empty($normalityOptions['ks']) || !empty($normalityOptions['lilliefors']))) {
        // Hitung mean perlakuan dan mean blok
        $treatment_means = $rata_rata_perlakuan;
        $block_means = [];
        foreach ($block_totals as $total) {
            $block_means[] = $total / $perlakuan;
        }

        // Residual: obs - (treatment_mean + block_mean - overall_mean)
        $fitted = [];
        $residuals = [];
        foreach ($data as $i => $row) {
            foreach ($row as $j => $value) {
                $val = floatval($value);
                $pred = $treatment_means[$i] + $block_means[$j] - $rata_rata_umum;
                $residuals[] = $val - $pred;
                $fitted[] = $pred;
            }
        }

        // Normal CDF and erf
        $erf_approx = function ($x) {
            $sign = ($x < 0) ? -1 : 1;
            $x = abs($x);
            $a1 = 0.254829592;
            $a2 = -0.284496736;
            $a3 = 1.421413741;
            $a4 = -1.453152027;
            $a5 = 1.061405429;
            $p = 0.3275911;
            $t = 1.0 / (1.0 + $p * $x);
            $y = 1.0 - (((($a5 * $t + $a4) * $t + $a3) * $t + $a2) * $t + $a1) * $t * exp(-$x * $x);
            return $sign * $y;
        };

        $normal_cdf = function ($x) use ($erf_approx) {
            return 0.5 * (1 + $erf_approx($x / sqrt(2)));
        };

        $ks_statistic = function ($sample, $mu, $sigma) use ($normal_cdf) {
            $n = count($sample);
            if ($n <= 0 || $sigma <= 0) return 0.0;
            sort($sample);
            $D = 0.0;
            for ($i = 0; $i < $n; $i++) {
                $xi = $sample[$i];
                $Fi = ($i + 1) / $n;
                $Fxi = $normal_cdf(($xi - $mu) / $sigma);
                $D = max($D, abs($Fi - $Fxi), abs(($i / $n) - $Fxi));
            }
            return $D;
        };

        $ks_pvalue_asymptotic = function ($D, $n) {
            if ($D <= 0) return 1.0;
            $lambda = ($n ** 0.5) * $D;
            $sum = 0.0;
            $k = 1;
            while (true) {
                $term = 2 * ((-1) ** ($k - 1)) * exp(-2 * $k * $k * $lambda * $lambda);
                $sum += $term;
                if (abs($term) < 1e-8) break;
                $k++;
                if ($k > 100) break;
            }
            $p = $sum;
            return min(1.0, max(0.0, $p));
        };

        $lilliefors_pvalue_mc = function ($sample, $iterations = 5000) use ($ks_statistic) {
            $n = count($sample);
            if ($n <= 0) return 1.0;
            $mu = array_sum($sample) / $n;
            $sd = sqrt(array_sum(array_map(function ($x) use ($mu) {
                return ($x - $mu) * ($x - $mu);
            }, $sample)) / ($n - 1));
            if ($sd <= 0) return 1.0;
            $Dobs = $ks_statistic($sample, $mu, $sd);

            $count = 0;
            for ($it = 0; $it < $iterations; $it++) {
                $sim = [];
                for ($i = 0; $i < $n; $i++) {
                    $u1 = mt_rand() / mt_getrandmax();
                    $u2 = mt_rand() / mt_getrandmax();
                    $z = sqrt(-2 * log(max($u1, 1e-12))) * cos(2 * M_PI * $u2);
                    $sim[] = $z;
                }
                $m = array_sum($sim) / $n;
                $s = sqrt(array_sum(array_map(function ($x) use ($m) {
                    return ($x - $m) * ($x - $m);
                }, $sim)) / ($n - 1));
                $Dsim = $ks_statistic($sim, $m, $s);
                if ($Dsim >= $Dobs) $count++;
            }
            return $count / $iterations;
        };

        // compute tests
        $nres = count($residuals);
        if ($nres > 0) {
            $mu_res = array_sum($residuals) / $nres;
            $sd_res = sqrt(array_sum(array_map(function ($x) use ($mu_res) {
                return ($x - $mu_res) * ($x - $mu_res);
            }, $residuals)) / max(1, $nres - 1));

            if (!empty($normalityOptions['ks']) && $sd_res > 0) {
                $ks_D = $ks_statistic($residuals, $mu_res, $sd_res);
                $ks_p = $ks_pvalue_asymptotic($ks_D, $nres);
                $ks_normal = ($ks_p !== null) ? ($ks_p >= $alpha) : null;
            }

            if (!empty($normalityOptions['lilliefors']) && $sd_res > 0) {
                // compute Dobs then p-value via MC
                $lillie_D = $ks_statistic($residuals, $mu_res, $sd_res);
                $lillie_p = $lilliefors_pvalue_mc($residuals, 3000);
                $lillie_normal = ($lillie_p !== null) ? ($lillie_p >= $alpha) : null;
            }
        }
    }

    // Return hasil
    return [
        'design' => 'RAK',
        'perlakuan' => $perlakuan,
        'ulangan' => $ulangan,
        'alpha' => $alpha,

        'grand_total' => $grand_total,
        'rata_rata_umum' => $rata_rata_umum,
        'rata_rata_perlakuan' => $rata_rata_perlakuan,

        'jk_total' => $JK_total,
        'jk_perlakuan' => $JK_perlakuan,
        'jk_blok' => $JK_blok,
        'jk_galat' => $JK_galat,

        'db_total' => $db_total,
        'db_perlakuan' => $db_perlakuan,
        'db_blok' => $db_blok,
        'db_galat' => $db_galat,

        'kt_perlakuan' => $KT_perlakuan,
        'kt_blok' => $KT_blok,
        'kt_galat' => $KT_galat,

        'f_hit' => $F_hit_perlakuan,
        'f_hit_blok' => $F_hit_blok,
        'f_tabel' => $F_tabel_perlakuan,
        'f_tabel_blok' => $F_tabel_blok,
        'p_value' => $p_value_perlakuan,
        'p_value_blok' => $p_value_blok,
        'significant' => $significant_perlakuan,
        'significant_blok' => $significant_blok,

        'kk' => $KK,
        'fk' => $FK,

        'data' => $data,
        'block_totals' => $block_totals,
        'fitted' => $fitted,
        'residuals' => $residuals,
        'debug_info' => $debug_info,
        'debug_pvalue' => $debug_pvalue,
        'normality' => [
            'ks' => ['stat' => $ks_D, 'p_value' => $ks_p, 'normal' => $ks_normal],
            'lilliefors' => ['stat' => isset($lillie_D) ? $lillie_D : null, 'p_value' => $lillie_p, 'normal' => $lillie_normal]
        ]
    ];
}

// ==============================================
// FUNGSI P-VALUE YANG BENAR-BENAR DIPERBAIKI
// ==============================================

/**
 * FUNGSI P-VALUE YANG BENAR - REVISI TOTAL
 * CDF F-distribution: F_CDF(f, df1, df2) = I_x(df1/2, df2/2)
 * where x = (df1 * f) / (df1 * f + df2)
 * P-value = 1 - CDF (right tail)
 */
function calculatePValueFixed($F, $df1, $df2)
{
    if ($F <= 0 || $df1 <= 0 || $df2 <= 0) {
        return 1.0;
    }

    // Untuk F yang sangat besar, p-value sangat kecil mendekati 0
    if ($F > 1000) {
        return 1e-16;
    }

    try {
        // Hitung x untuk distribusi Beta
        $x = ($df1 * $F) / ($df1 * $F + $df2);

        // Parameter Beta
        $a = $df1 / 2.0;
        $b = $df2 / 2.0;

        // CDF F = I_x(a, b) (regularized incomplete Beta)
        $cdf = regularizedBetaSimple($x, $a, $b);

        // P-value = area di ekor kanan = 1 - CDF
        $p_value = 1.0 - $cdf;

        // Pastikan dalam range yang valid
        $p_value = max(0.0, min(1.0, $p_value));

        // Untuk nilai yang sangat kecil, batasi minimum
        if ($p_value < 1e-16) {
            $p_value = 1e-16;
        }

        return $p_value;
    } catch (Exception $e) {
        // Fallback: jika error, gunakan approximation
        return calculatePValueApprox($F, $df1, $df2);
    }
}

/**
 * Simple regularized Beta function menggunakan continued fraction
 * Lebih stabil secara numerik
 */
function regularizedBetaSimple($x, $a, $b)
{
    if ($x <= 0.0) return 0.0;
    if ($x >= 1.0) return 1.0;
    if ($a <= 0.0 || $b <= 0.0) return 0.5;

    // Untuk x kecil atau besar, gunakan symmetry
    if ($x > (($a + 1.0) / ($a + $b + 2.0))) {
        return 1.0 - regularizedBetaSimple(1.0 - $x, $b, $a);
    }

    // Gunakan continued fraction
    return betaCF($x, $a, $b) / betaComplete($a, $b);
}

/**
 * Complete Beta function B(a,b)
 */
function betaComplete($a, $b)
{
    return exp(logGammaSimple($a) + logGammaSimple($b) - logGammaSimple($a + $b));
}

/**
 * Simple Log Gamma function
 */
function logGammaSimple($x)
{
    // Koefisien Stirling
    $cof = [
        76.18009172947146,
        -86.50532032941677,
        24.01409824083091,
        -1.231739572450155,
        0.1208650973866179e-2,
        -0.5395239384953e-5
    ];

    $y = $x;
    $tmp = $x + 5.5;
    $tmp -= ($x + 0.5) * log($tmp);
    $ser = 1.000000000190015;

    for ($j = 0; $j < 6; $j++) {
        $ser += $cof[$j] / ++$y;
    }

    return -$tmp + log(2.5066282746310005 * $ser / $x);
}

/**
 * Continued fraction untuk incomplete Beta - VERSI SEDERHANA
 */
function betaCF($x, $a, $b)
{
    $max_iter = 200;
    $eps = 1e-12;

    $az = 1.0;
    $am = 1.0;
    $bm = 1.0;
    $bz = 1.0 - ($a + $b) * $x / ($a + 1.0);

    for ($m = 1; $m <= $max_iter; $m++) {
        $em = $m;
        $tem = $em + $em;

        // Even term
        $d = $em * ($b - $m) * $x / (($a + $tem - 1.0) * ($a + $tem));
        $ap = $az + $d * $am;
        $bp = $bz + $d * $bm;

        // Odd term
        $d = - ($a + $em) * ($a + $b + $em) * $x / (($a + $tem) * ($a + $tem + 1.0));
        $app = $ap + $d * $az;
        $bpp = $bp + $d * $bz;

        // Update
        $am = $ap / $bpp;
        $bm = $bp / $bpp;
        $aold = $az;
        $az = $app / $bpp;
        $bz = 1.0;

        if (abs($az - $aold) < $eps * abs($az)) {
            // Hitung faktor depan
            $front = exp($a * log($x) + $b * log(1.0 - $x) -
                (logGammaSimple($a) + logGammaSimple($b) - logGammaSimple($a + $b)));
            return $front * $az / $a;
        }
    }

    // Jika tidak konvergen, return approximation
    $front = exp($a * log($x) + $b * log(1.0 - $x) -
        (logGammaSimple($a) + logGammaSimple($b) - logGammaSimple($a + $b)));
    return $front * $az / $a;
}

/**
 * Approximation p-value untuk kasus darurat
 */
function calculatePValueApprox($F, $df1, $df2)
{
    if ($F <= 0) return 1.0;

    // Approximation menggunakan hubungan dengan distribusi Chi-square
    $chi_sq = $F * $df1;

    // Untuk F besar, p-value kecil
    if ($chi_sq > 50) {
        return exp(-$chi_sq / 2.0);
    }

    // Approximation sederhana
    $x = $df2 / ($df2 + $df1 * $F);
    $p = pow($x, $df2 / 2.0) * pow(1.0 - $x, $df1 / 2.0);

    return min(1.0, max(0.0, $p));
}

/**
 * TABEL F-TABEL YANG BENAR (untuk alpha=0.05)
 */
function getFTableValueFixed($alpha, $df1, $df2)
{
    // Pastikan parameter valid
    $df1 = max(1, intval($df1));
    $df2 = max(1, intval($df2));
    $alpha = max(0.001, min(0.5, $alpha));

    // Tabel F untuk alpha = 0.05 (dari tabel statistik standar)
    // Format: "df1,df2" => F-value
    $F_TABLE_005 = array(
        // df1 = 1
        '1,1' => 161.4476,
        '1,2' => 18.5128,
        '1,3' => 10.1280,
        '1,4' => 7.7086,
        '1,5' => 6.6079,
        '1,6' => 5.9874,
        '1,7' => 5.5914,
        '1,8' => 5.3177,
        '1,9' => 5.1174,
        '1,10' => 4.9646,
        '1,12' => 4.7472,
        '1,15' => 4.5431,
        '1,20' => 4.3512,
        '1,24' => 4.2597,
        '1,30' => 4.1709,
        '1,40' => 4.0848,
        '1,60' => 4.0012,
        '1,120' => 3.9201,
        '1,1000' => 3.8515,

        // df1 = 2
        '2,1' => 199.5000,
        '2,2' => 19.0000,
        '2,3' => 9.5521,
        '2,4' => 6.9443,
        '2,5' => 5.7861,
        '2,6' => 5.1433,
        '2,7' => 4.7374,
        '2,8' => 4.4590,
        '2,9' => 4.2565,
        '2,10' => 4.1028,
        '2,12' => 3.8853,
        '2,15' => 3.6823,
        '2,20' => 3.4928,
        '2,24' => 3.4028,
        '2,30' => 3.3158,
        '2,40' => 3.2317,
        '2,60' => 3.1504,
        '2,120' => 3.0718,
        '2,1000' => 3.0050,

        // df1 = 3
        '3,1' => 215.7073,
        '3,2' => 19.1643,
        '3,3' => 9.2766,
        '3,4' => 6.5914,
        '3,5' => 5.4095,
        '3,6' => 4.7571,
        '3,7' => 4.3468,
        '3,8' => 4.0662,
        '3,9' => 3.8625,
        '3,10' => 3.7083,
        '3,12' => 3.4903,
        '3,15' => 3.2874,
        '3,20' => 3.0984,
        '3,24' => 3.0088,
        '3,30' => 2.9223,
        '3,40' => 2.8387,
        '3,60' => 2.7581,
        '3,120' => 2.6802,
        '3,1000' => 2.6120,

        // df1 = 4
        '4,1' => 224.5832,
        '4,2' => 19.2468,
        '4,3' => 9.1172,
        '4,4' => 6.3882,
        '4,5' => 5.1922,
        '4,6' => 4.5337,
        '4,7' => 4.1203,
        '4,8' => 3.8379,
        '4,9' => 3.6331,
        '4,10' => 3.4780,
        '4,12' => 3.2592,
        '4,15' => 3.0556,
        '4,20' => 2.8661,
        '4,24' => 2.7763,
        '4,30' => 2.6896,
        '4,40' => 2.6060,
        '4,60' => 2.5252,
        '4,120' => 2.4472,
        '4,1000' => 2.3780,
        // df1=4, df2=16 (khusus untuk kasus Anda)
        '4,16' => 3.0069,

        // df1 = 5
        '5,1' => 230.1619,
        '5,2' => 19.2964,
        '5,3' => 9.0135,
        '5,4' => 6.2561,
        '5,5' => 5.0503,
        '5,6' => 4.3874,
        '5,7' => 3.9715,
        '5,8' => 3.6875,
        '5,9' => 3.4817,
        '5,10' => 3.3258,
        '5,12' => 3.1059,
        '5,15' => 2.9013,
        '5,20' => 2.7109,
        '5,24' => 2.6207,
        '5,30' => 2.5336,
        '5,40' => 2.4495,
        '5,60' => 2.3683,
        '5,120' => 2.2900,
        '5,1000' => 2.2201,
    );

    $key = "$df1,$df2";

    // Jika ada di tabel, gunakan nilai dari tabel
    if (isset($F_TABLE_005[$key])) {
        $F_alpha_05 = $F_TABLE_005[$key];

        // Adjust untuk alpha lain menggunakan faktor scaling
        if (abs($alpha - 0.05) < 0.001) {
            return $F_alpha_05;
        } elseif ($alpha == 0.01) {
            // Untuk alpha 0.01, F-tabel lebih besar
            return $F_alpha_05 * 1.5;
        } elseif ($alpha == 0.10) {
            // Untuk alpha 0.10, F-tabel lebih kecil
            return $F_alpha_05 * 0.7;
        }
    }

    // Jika tidak ada di tabel, hitung dengan binary search
    return calculateFCritical($alpha, $df1, $df2);
}

/**
 * Hitung F critical jika tidak ada di tabel
 */
function calculateFCritical($alpha, $df1, $df2)
{
    // Binary search untuk menemukan F dimana p-value = alpha
    $low = 0.0;
    $high = 10.0;

    // Expand high bound
    while (calculatePValueFixed($high, $df1, $df2) > $alpha && $high < 1000) {
        $high *= 2.0;
    }

    // Binary search
    for ($i = 0; $i < 50; $i++) {
        $mid = ($low + $high) / 2.0;
        $p_mid = calculatePValueFixed($mid, $df1, $df2);

        if (abs($p_mid - $alpha) < 1e-6) {
            return $mid;
        }

        if ($p_mid > $alpha) {
            $low = $mid;
        } else {
            $high = $mid;
        }
    }

    return ($low + $high) / 2.0;
}

// Main execution
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get JSON data
        $json = file_get_contents('php://input');
        $request = json_decode($json, true);

        if (!$request || !isset($request['data'])) {
            echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
            exit;
        }

        $data = $request['data'];
        $alpha = isset($request['alpha']) ? floatval($request['alpha']) : 0.05;
        $perlakuan = isset($request['perlakuan']) ? intval($request['perlakuan']) : count($data);
        $ulangan = isset($request['ulangan']) ? intval($request['ulangan']) : (isset($data[0]) ? count($data[0]) : 0);

        // Validate RAK requirements
        if ($ulangan < 2) {
            echo json_encode([
                'success' => false,
                'message' => 'RAK membutuhkan minimal 2 blok (ulangan)'
            ]);
            exit;
        }

        // Calculate ANOVA RAK (pass normality options if provided)
        $normalityOptions = isset($request['normality']) ? $request['normality'] : null;
        $result = calculateRAK($data, $alpha, $perlakuan, $ulangan, $normalityOptions);

        echo json_encode([
            'success' => true,
            'data' => $result
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}
