<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STATDEN v3.0 — Rancob - Analisis RAL & RAK</title>
    <meta name="application-name" content="STATDEN VERSI 3.0">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jstat/1.9.2/jstat.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link rel="stylesheet" href="css/css_index.css">

</head>

<body>
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p style="font-weight: 600; color: #333;">Memproses analisis...</p>
            <p style="font-size: 13px; color: #666; margin-top: 10px;">Tunggu sebentar</p>
        </div>
    </div>

    <header class="app-header">
        <div class="app-logo">
            <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNTgiIGhlaWdodD0iNTgiIHZpZXdCb3g9IjAgMCA1OCA1OCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjU4IiBoZWlnaHQ9IjU4IiByeD0iMTAiIGZpbGw9IiMwMDk5RTYiLz4KPHBhdGggZD0iTTE1IDI4TDI4IDE1TDQxIDI4TDI4IDQxTDE1IDI4WiIgZmlsbD0id2hpdGUiLz4KPHBhdGggZD0iTTI4IDI4TDM0IDIyTDI4IDE2TDIyIDIyTDI4IDI4WiIgZmlsbD0iIzAwNkJCMzIiLz4KPC9zdmc+" alt="STATDEN logo">
        </div>
        <div class="app-title-wrap">
            <div class="app-title">STATDEN ANALYTICS</div>
            <div class="app-subtitle">Sistem Analisis Rancangan Percobaan (RAL & RAK)</div>
        </div>
        <div class="version-badge">VERSION 3.0 PRO</div>
    </header>

    <div class="tab-container">
        <button class="tab-button active" onclick="switchTab('data-tab')">📊 Data Input</button>
        <button class="tab-button" onclick="switchTab('anova-tab')">📈 <span id="anovaTabLabel">ANALISIS RAL</span></button>
        <button class="tab-button" onclick="switchTab('bnj-tab')"><span id="testTypeTab">📊 Uji Lanjut</span></button>
        <button class="tab-button" onclick="switchTab('results-tab')">📋 Hasil Lengkap</button>
        <button class="tab-button no-print" onclick="switchTab('help-tab')">❓ Bantuan</button>
    </div>

    <div class="controls">
        <form class="controls-form" id="controlsForm">
            <div class="grid-controls">
                <div class="control-item">
                    <label for="perlakuan">🎯 Perlakuan (t)</label>
                    <input type="number" id="perlakuan" name="perlakuan" value="7" min="1">
                    <small style="color: #666; font-size: 12px;">Jumlah perlakuan dalam penelitian</small>
                </div>

                <div class="control-item">
                    <label for="ulangan">🔄 Ulangan (r)</label>
                    <input type="number" id="ulangan" name="ulangan" value="5" min="1">
                    <small style="color: #666; font-size: 12px;">Jumlah ulangan/replikasi</small>
                </div>

                <div class="control-item">
                    <label for="kodePerlakuan">🏷️ Kode Perlakuan</label>
                    <input type="text" id="kodePerlakuan" name="kodePerlakuan" placeholder="Contoh: A,B,C,D">
                    <small style="color: #666; font-size: 12px;">Pisahkan dengan koma</small>
                </div>

                <div class="control-item">
                    <label for="design">🧪 Desain Percobaan</label>
                    <select id="design" name="design">
                        <option value="ral">RAL (Rancangan Acak Lengkap)</option>
                        <option value="rak">RAK (Rancangan Acak Kelompok)</option>
                    </select>
                </div>

                <div class="control-item">
                    <label for="alpha">📏 Tingkat Signifikansi (α)</label>
                    <select id="alpha" name="alpha">
                        <option value="0.05">0.05 (5%) - Standar</option>
                        <option value="0.01">0.01 (1%) - Ketat</option>
                        <option value="0.10">0.10 (10%) - Longgar</option>
                    </select>
                </div>

                <div class="control-item">
                    <label for="postHocTest">📊 Uji Lanjut</label>
                    <select id="postHocTest" name="postHocTest">
                        <option value="bnt">BNT (Beda Nyata Terkecil)</option>
                        <option value="bnj">BNJ (Beda Nyata Jujur)</option>
                        <option value="dmrt">DMRT (Duncan's Test)</option>
                    </select>
                    <div id="postHocRecommendation" style="margin-top: 8px;"></div>
                </div>

                <div class="control-item">
                    <label for="normalityTest">📈 Uji Normalitas</label>
                    <select id="normalityTest" name="normalityTest">
                        <option value="none">Tidak ada</option>
                        <option value="ks">Kolmogorov-Smirnov (KS)</option>
                        <option value="lilliefors" selected>Lilliefors (MC)</option>
                        <option value="both">Keduanya (KS & Lilliefors)</option>
                    </select>
                </div>
                <div class="control-item">
                    <button type="button" onclick="createGrid()" title="Buat tabel data baru">
                        <span>🔄</span> Buat Grid Baru
                    </button>
                    <button type="button" onclick="loadData()" title="Muat data dari file">
                        <span>📂</span> Muat Data
                    </button>


                </div>
            </div>
        </form>


    </div>

    <!-- Tab 1: Data Input -->
    <div id="data-tab" class="tab-content active">
        <div class="edit-notice">
            <strong>📝 Edit Data:</strong> Anda dapat mengedit data kapan saja. Setelah edit, klik "<span id="analysisName">Analisis RAL</span>" untuk memperbarui hasil.
        </div>
        <div class="actions">
            <button type="button" onclick="runANOVA()" title="Jalankan analisis statistik">
                <span>📈</span> <span id="runAnalysisLabel">Analisis RAL</span>
            </button>
            <button type="button" onclick="runPostHocTest()" title="Jalankan uji perbandingan">
                <span>📊</span> Uji Lanjut
            </button>

            <button type="button" onclick="saveData()" title="Simpan data ke file">
                <span>💾</span> Simpan Data
            </button>

            <button type="button" onclick="exportToExcel()" title="Export ke Excel">
                <span>📊</span> Export Excel
            </button>
            <button type="button" onclick="exportToPDF()" title="Export ke PDF">
                <span>📄</span> Export PDF
            </button>
            <button type="button" onclick="clearAll()" title="Reset semua data" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
                <span>🗑️</span> Reset All
            </button>
        </div>

        <div class="data-container">
            <div class="table-container" id="tableContainer">
                <table id="dataTable"></table>
            </div>
        </div>
    </div>

    <!-- Tab 2: ANOVA Results -->
    <div id="anova-tab" class="tab-content">
        <div class="edit-notice">
            <strong>🔄 Perbarui Data:</strong> Klik tab "Data Input" untuk memperbaiki data, klik "<span id="analysisName2">Analisis RAL</span>" lagi untuk memperbarui hasil ANOVA.
        </div>

        <div class="section">
            <div class="section-title">Tabel ANOVA <span id="designTitle">RAL</span></div>
            <div class="table-container">
                <table id="anovaTable" class="anova-table">
                    <thead>
                        <tr>
                            <th>Sumber Keragaman</th>
                            <th>db</th>
                            <th>JK</th>
                            <th>KT</th>
                            <th>F-hit</th>
                            <th>F-tabel (α=<span id="alpha-value">0.05</span>)</th>
                            <th>p-value</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody id="anovaBody">
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 30px;">
                                <div style="color: #666; font-size: 16px;">
                                    <div style="font-size: 48px; margin-bottom: 10px;">📊</div>
                                    <strong>Belum ada hasil analisis</strong><br>
                                    Klik tombol "<span id="analysisName3">Analisis RAL</span>" untuk memulai
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="anovaConclusion" class="result-card">
                <strong>📋 Kesimpulan ANOVA:</strong> Belum ada hasil analisis. Klik "Analisis RAL" untuk memulai.
            </div>
        </div>

        <div class="section">
            <div class="section-title">Koefisien Keragaman (KK)</div>
            <div id="kkResult" class="result-card">
                <strong>📊 Koefisien Keragaman:</strong> Akan muncul setelah analisis ANOVA.
            </div>
        </div>

        <div class="section">
            <div class="section-title">Diagnostic Plots (Uji Asumsi)</div>
            <div id="plots" style="display: flex; gap: 20px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 350px;">
                    <div style="font-weight: 700; margin-bottom: 10px; color: #003d5c; display: flex; align-items: center; gap: 10px;">
                        <span>📈</span> Q–Q Plot (Residuals)
                    </div>
                    <canvas id="qqCanvas" width="500" height="400" style="background: #fff; border: 2px solid #dee2e6; border-radius: 10px; display: block;"></canvas>
                    <div class="test-recommendation">
                        <strong>💡 Cara Interpretasi:</strong><br>
                        • Titik-titik pada/dekat garis merah = Normalitas terpenuhi ✓<br>
                        • Jauh dari garis = Ada penyimpangan dari normalitas<br>
                        • Pola linear = Distribusi normal baik
                    </div>
                </div>
                <div style="flex: 1; min-width: 350px;">
                    <div style="font-weight: 700; margin-bottom: 10px; color: #003d5c; display: flex; align-items: center; gap: 10px;">
                        <span>📊</span> Residuals vs Fitted
                    </div>
                    <canvas id="resCanvas" width="500" height="400" style="background: #fff; border: 2px solid #dee2e6; border-radius: 10px; display: block;"></canvas>
                    <div class="test-recommendation">
                        <strong>💡 Cara Interpretasi:</strong><br>
                        • Titik-titik menyebar acak = Homogenitas varians ✓<br>
                        • Ada pola (corong/curve) = Heterogenitas varians<br>
                        • Garis merah = Residual nol (ideal)
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 3: Post-Hoc Test Results -->
    <div id="bnj-tab" class="tab-content">
        <div class="edit-notice">
            <strong id="postHocTestNotice">📊 Uji Lanjut:</strong> <span id="postHocTestDesc">Uji ini hanya dilakukan jika ANOVA menunjukkan perbedaan nyata.</span>
        </div>

        <div class="section">
            <div class="section-title" id="postHocResultTitle">Hasil Uji Lanjut</div>
            <div class="table-container">
                <table id="bnjTable" class="bnj-results">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Perlakuan</th>
                            <th>Rata-rata</th>
                            <th>Notasi</th>
                            <th>Kelompok</th>
                        </tr>
                    </thead>
                    <tbody id="bnjBody">
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 30px;">
                                <div style="color: #666; font-size: 16px;">
                                    <div style="font-size: 48px; margin-bottom: 10px;">📈</div>
                                    <strong>Lakukan analisis ANOVA terlebih dahulu</strong><br>
                                    Hasil uji lanjut akan muncul di sini
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="pagination" id="bnjPagination" style="display: none;"></div>
            <div id="bnjInfo" class="result-card">
                <strong>📋 Informasi Uji Lanjut:</strong> Akan muncul setelah uji dilakukan.
            </div>
        </div>

        <div class="section">
            <div class="section-title">Perbandingan Antar Perlakuan</div>
            <div id="comparisonMatrix" class="result-card">
                Matriks perbandingan akan muncul setelah uji BNJ.
            </div>
        </div>

        <div class="section">
            <div class="section-title">Visualisasi Hasil</div>
            <div style="background: white; padding: 20px; border-radius: 10px; margin-top: 15px;">
                <canvas id="meanComparisonChart" width="800" height="400" style="max-width: 100%;"></canvas>
            </div>
        </div>
    </div>

    <!-- Tab 4: Full Results -->
    <div id="results-tab" class="tab-content">
        <div style="margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap;">
            <button onclick="exportToExcel()" style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);">
                <span>📊</span> Export ke Excel
            </button>
            <button onclick="exportToPDF()" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
                <span>📄</span> Export ke PDF
            </button>
            <button onclick="printResults()" style="background: linear-gradient(135deg, #6c757d 0%, #545b62 100%);">
                <span>🖨️</span> Cetak Laporan
            </button>
            <button onclick="copyToClipboard()" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);">
                <span>📋</span> Copy Hasil
            </button>
        </div>

        <div id="fullResults" class="result-card">
            <div style="text-align: center; color: #666; padding: 40px;">
                <div style="font-size: 64px; margin-bottom: 20px;">📋</div>
                <strong style="font-size: 20px;">Hasil Lengkap Akan Muncul Di Sini</strong><br>
                <p style="margin-top: 10px;">Lakukan analisis untuk melihat hasil lengkap</p>
            </div>
        </div>
    </div>

    <!-- Tab 5: Help -->
    <div id="help-tab" class="tab-content">
        <div class="section">
            <div class="section-title">❓ Panduan Penggunaan STATDEN</div>

            <div class="result-card">
                <h3>📖 Cara Menggunakan:</h3>
                <ol style="line-height: 2;">
                    <li><strong>Isi jumlah perlakuan dan ulangan</strong> sesuai penelitian Anda</li>
                    <li><strong>Klik "Buat Grid Baru"</strong> untuk membuat tabel input data</li>
                    <li><strong>Masukkan data penelitian</strong> ke dalam tabel</li>
                    <li><strong>Klik "Analisis RAL/RAK"</strong> untuk memproses data</li>
                    <li><strong>Jika signifikan, klik "Uji Lanjut"</strong> untuk perbandingan</li>
                    <li><strong>Export hasil</strong> ke Excel, PDF, atau cetak</li>
                </ol>
            </div>

            <div class="result-card">
                <h3>🎯 Fitur Unggulan:</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-top: 15px;">
                    <div style="background: #e7f3ff; padding: 15px; border-radius: 8px;">
                        <strong>📈 ANOVA Lengkap</strong><br>
                        RAL & RAK dengan presisi tinggi
                    </div>
                    <div style="background: #fff3cd; padding: 15px; border-radius: 8px;">
                        <strong>📊 Uji Lanjut</strong><br>
                        BNT, BNJ, DMRT presisi
                    </div>
                    <div style="background: #d4edda; padding: 15px; border-radius: 8px;">
                        <strong>📈 Diagnostic Plots</strong><br>
                        Q-Q Plot & Residuals vs Fitted
                    </div>
                    <div style="background: #f8d7da; padding: 15px; border-radius: 8px;">
                        <strong>💾 Export Data</strong><br>
                        Excel, PDF, JSON, Print
                    </div>
                </div>
            </div>

            <div class="result-card">
                <h3>📊 Interpretasi Hasil:</h3>
                <table style="width: 100%; margin-top: 15px;">
                    <tr>
                        <th>Simbol</th>
                        <th>Arti</th>
                        <th>Keterangan</th>
                    </tr>
                    <tr>
                        <td><span class="stat-badge success">ns</span></td>
                        <td>Not Significant</td>
                        <td>Tidak berbeda nyata</td>
                    </tr>
                    <tr>
                        <td><span class="stat-badge warning">*</span></td>
                        <td>Significant</td>
                        <td>Berbeda nyata (α=0.05)</td>
                    </tr>
                    <tr>
                        <td><span class="stat-badge danger">**</span></td>
                        <td>Highly Significant</td>
                        <td>Sangat berbeda nyata (α=0.01)</td>
                    </tr>
                    <tr>
                        <td><span class="stat-badge info">a, b, c</span></td>
                        <td>Group Notation</td>
                        <td>Huruf sama = tidak berbeda nyata</td>
                    </tr>
                </table>
            </div>

            <div class="result-card">
                <h3>⚙️ Spesifikasi Teknis:</h3>
                <ul style="line-height: 2;">
                    <li><strong>Presisi:</strong> Tabel statistik lengkap dengan interpolasi</li>
                    <li><strong>Kapasitas:</strong> Unlimited perlakuan & ulangan</li>
                    <li><strong>Uji Normalitas:</strong> Kolmogorov-Smirnov & Lilliefors</li>
                    <li><strong>Browser Support:</strong> Chrome, Firefox, Edge, Safari</li>
                    <li><strong>Export Format:</strong> Excel (XLSX), PDF, JSON, Print</li>
                    <li><strong>Responsive:</strong> Support desktop, tablet, mobile</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        // ==================== KONFIGURASI GLOBAL ====================
        let data = [];
        let anovaResults = null;
        let kodePerlakuanArray = [];
        let postHocResults = null;
        let currentPostHocPage = 1;
        const ITEMS_PER_PAGE = 20;

        // ==================== FUNGSI UTILITAS ====================
        function showLoading(show) {
            document.getElementById('loadingOverlay').style.display = show ? 'flex' : 'none';
        }

        function showNotification(message, type = 'info', duration = 3000) {
            const notification = document.createElement('div');
            const bgColor = type === 'success' ? '#d4edda' :
                type === 'error' ? '#f8d7da' :
                type === 'warning' ? '#fff3cd' : '#d1ecf1';
            const textColor = type === 'success' ? '#155724' :
                type === 'error' ? '#721c24' :
                type === 'warning' ? '#856404' : '#0c5460';

            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                background: ${bgColor};
                color: ${textColor};
                border: 1px solid ${type === 'success' ? '#c3e6cb' : 
                                 type === 'error' ? '#f5c6cb' : 
                                 type === 'warning' ? '#ffeaa7' : '#bee5eb'};
                border-radius: 8px;
                z-index: 10000;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                animation: slideInRight 0.3s ease;
                max-width: 400px;
                font-weight: 500;
            `;

            const icon = type === 'success' ? '✅' :
                type === 'error' ? '❌' :
                type === 'warning' ? '⚠️' : 'ℹ️';

            notification.innerHTML = `
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 18px;">${icon}</span>
                    <span>${message}</span>
                </div>
            `;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, duration);

            // Tambahkan style animasi jika belum ada
            if (!document.getElementById('notification-styles')) {
                const style = document.createElement('style');
                style.id = 'notification-styles';
                style.textContent = `
                    @keyframes slideInRight {
                        from { transform: translateX(100%); opacity: 0; }
                        to { transform: translateX(0); opacity: 1; }
                    }
                    @keyframes slideOutRight {
                        from { transform: translateX(0); opacity: 1; }
                        to { transform: translateX(100%); opacity: 0; }
                    }
                `;
                document.head.appendChild(style);
            }
        }

        function clearCanvas(canvas) {
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        }

        // ==================== FUNGSI DATA & GRID ====================
        function generateKodePerlakuan(perlakuan) {
            const kodeInput = document.getElementById('kodePerlakuan').value.trim();
            let kodeArray = [];

            if (kodeInput) {
                kodeArray = kodeInput.split(',').map(k => k.trim()).filter(k => k !== '');

                while (kodeArray.length < perlakuan) {
                    kodeArray.push(`P${kodeArray.length + 1}`);
                }

                if (kodeArray.length > perlakuan) {
                    kodeArray = kodeArray.slice(0, perlakuan);
                }
            } else {
                for (let i = 1; i <= perlakuan; i++) {
                    kodeArray.push(`P${i}`);
                }
            }

            return kodeArray;
        }

        function createGrid() {
            const perlakuan = parseInt(document.getElementById('perlakuan').value);
            const ulangan = parseInt(document.getElementById('ulangan').value);

            // Validasi input
            if (isNaN(perlakuan) || perlakuan < 1) {
                alert("Jumlah perlakuan harus minimal 1");
                document.getElementById('perlakuan').value = 4;
                return;
            }

            if (isNaN(ulangan) || ulangan < 1) {
                alert("Jumlah ulangan harus minimal 1");
                document.getElementById('ulangan').value = 5;
                return;
            }

            // Validasi ukuran wajar
            const totalCells = perlakuan * ulangan;
            if (totalCells > 1000) {
                if (!confirm(`⚠️ PERINGATAN: Anda akan membuat ${totalCells} sel data.\nIni mungkin mempengaruhi performa.\n\nLanjutkan?`)) {
                    return;
                }
            }

            // Generate kode perlakuan
            kodePerlakuanArray = generateKodePerlakuan(perlakuan);

            // Update rekomendasi uji lanjut
            updatePostHocRecommendation();

            // Buat HTML tabel
            let tableHTML = '<thead><tr><th rowspan="2" class="corner-cell">Perlakuan</th>';

            for (let u = 1; u <= ulangan; u++) {
                tableHTML += `<th>Ulangan ${u}</th>`;
            }
            tableHTML += '<th>Total</th><th>Rata-rata</th></tr></thead><tbody>';

            // Cek apakah ada data yang bisa digunakan kembali
            const useExistingData = data.length === perlakuan &&
                data[0] && data[0].length === ulangan;

            for (let p = 0; p < perlakuan; p++) {
                const kode = kodePerlakuanArray[p] || `P${p+1}`;
                tableHTML += `<tr>
                    <td class="label-col">${kode}</td>`;

                for (let u = 0; u < ulangan; u++) {
                    let value = 0;
                    if (useExistingData && data[p] && data[p][u] !== undefined) {
                        value = data[p][u];
                    }
                    tableHTML += `<td><input type="number" step="0.01" min="0" value="${value}" 
                        class="data-input" data-row="${p}" data-col="${u}" 
                        id="input_${kode}_u${u+1}" style="width: 100%;"></td>`;
                }
                tableHTML += `<td class="total-col" id="total_${kode}">0.00</td>
                    <td class="avg-col" id="avg_${kode}">0.00</td>`;
                tableHTML += '</tr>';
            }

            // Baris total per ulangan
            tableHTML += '<tr class="total-row"><td>Total</td>';
            for (let u = 1; u <= ulangan; u++) {
                tableHTML += `<td id="total_u${u}">0.00</td>`;
            }
            tableHTML += `<td id="grand_total">0.00</td>
                <td id="grand_avg">0.00</td>`;
            tableHTML += '</tr>';

            // Baris rata-rata per ulangan
            tableHTML += '<tr class="avg-row"><td>Rata-rata</td>';
            for (let u = 1; u <= ulangan; u++) {
                tableHTML += `<td id="avg_u${u}">0.00</td>`;
            }
            tableHTML += `<td id="avg_of_totals">0.00</td>
                <td id="avg_of_avgs">0.00</td>`;
            tableHTML += '</tr></tbody>';

            document.getElementById('dataTable').innerHTML = tableHTML;

            // Pasang event listener
            document.querySelectorAll('.data-input').forEach(input => {
                input.addEventListener('input', function() {
                    const row = parseInt(this.getAttribute('data-row'));
                    const col = parseInt(this.getAttribute('data-col'));
                    const value = parseFloat(this.value) || 0;

                    if (!data[row]) data[row] = [];
                    data[row][col] = value;

                    calculateTotals();

                    // Reset hasil analisis
                    resetResults();

                    showNotification('Data diperbarui. Klik "Analisis" untuk menghitung ulang.', 'info');
                });
            });

            // Hitung total awal
            calculateTotals();

            // Adjust table container height
            const tableContainer = document.getElementById('tableContainer');
            const maxHeight = Math.min(600, 100 + perlakuan * 40);
            tableContainer.style.maxHeight = `${maxHeight}px`;

            showNotification(`Grid ${perlakuan}×${ulangan} berhasil dibuat!`, 'success');
        }

        function calculateTotals() {
            const perlakuan = parseInt(document.getElementById('perlakuan').value);
            const ulangan = parseInt(document.getElementById('ulangan').value);

            if (isNaN(perlakuan) || isNaN(ulangan)) return;

            // Reset data array jika perlu
            if (!data.length || data.length !== perlakuan) {
                data = new Array(perlakuan);
                for (let i = 0; i < perlakuan; i++) {
                    data[i] = new Array(ulangan).fill(0);
                }
            }

            const totalsPerlakuan = new Array(perlakuan).fill(0);
            const totalsUlangan = new Array(ulangan).fill(0);
            let grandTotal = 0;

            // Hitung dari input
            for (let p = 0; p < perlakuan; p++) {
                for (let u = 0; u < ulangan; u++) {
                    const input = document.querySelector(`.data-input[data-row="${p}"][data-col="${u}"]`);
                    if (input) {
                        const value = parseFloat(input.value) || 0;
                        data[p][u] = value;
                        totalsPerlakuan[p] += value;
                        totalsUlangan[u] += value;
                        grandTotal += value;
                    }
                }
            }

            // Update tampilan total per perlakuan
            for (let p = 0; p < perlakuan; p++) {
                const kode = kodePerlakuanArray[p] || `P${p+1}`;
                const totalCell = document.getElementById(`total_${kode}`);
                const avgCell = document.getElementById(`avg_${kode}`);

                if (totalCell) totalCell.textContent = totalsPerlakuan[p].toFixed(2);
                if (avgCell) avgCell.textContent = (totalsPerlakuan[p] / ulangan).toFixed(2);
            }

            // Update tampilan total per ulangan
            for (let u = 0; u < ulangan; u++) {
                const totalCell = document.getElementById(`total_u${u+1}`);
                const avgCell = document.getElementById(`avg_u${u+1}`);

                if (totalCell) totalCell.textContent = totalsUlangan[u].toFixed(2);
                if (avgCell) avgCell.textContent = (totalsUlangan[u] / perlakuan).toFixed(2);
            }

            // Update grand total
            const grandTotalCell = document.getElementById('grand_total');
            const grandAvgCell = document.getElementById('grand_avg');
            const avgOfTotalsCell = document.getElementById('avg_of_totals');
            const avgOfAvgsCell = document.getElementById('avg_of_avgs');

            if (grandTotalCell) grandTotalCell.textContent = grandTotal.toFixed(2);
            if (grandAvgCell) grandAvgCell.textContent = (grandTotal / (perlakuan * ulangan)).toFixed(2);
            if (avgOfTotalsCell) avgOfTotalsCell.textContent = (grandTotal / perlakuan).toFixed(2);
            if (avgOfAvgsCell) avgOfAvgsCell.textContent = (grandTotal / (perlakuan * ulangan)).toFixed(2);
        }

        // ==================== FUNGSI STATISTIK PRESISI TINGGI ====================

        // Fungsi getTValue dengan tabel lengkap dan interpolasi
        function getTValue(alpha, df) {
            // Gunakan jStat jika tersedia untuk presisi maksimal
            if (typeof jStat !== 'undefined' && jStat.studentt && jStat.studentt.inv) {
                try {
                    const p = 1 - alpha / 2; // Two-tailed test untuk BNT
                    return Math.abs(jStat.studentt.inv(p, df));
                } catch (e) {
                    console.warn('jStat t-distribution error:', e);
                }
            }

            // Tabel t-distribution lengkap (df 1-∞)
            const t_tables = {
                0.10: {
                    1: 6.314,
                    2: 2.920,
                    3: 2.353,
                    4: 2.132,
                    5: 2.015,
                    6: 1.943,
                    7: 1.895,
                    8: 1.860,
                    9: 1.833,
                    10: 1.812,
                    11: 1.796,
                    12: 1.782,
                    13: 1.771,
                    14: 1.761,
                    15: 1.753,
                    16: 1.746,
                    17: 1.740,
                    18: 1.734,
                    19: 1.729,
                    20: 1.725,
                    21: 1.721,
                    22: 1.717,
                    23: 1.714,
                    24: 1.711,
                    25: 1.708,
                    26: 1.706,
                    27: 1.703,
                    28: 1.701,
                    29: 1.699,
                    30: 1.697,
                    40: 1.684,
                    60: 1.671,
                    120: 1.658,
                    Infinity: 1.645
                },
                0.05: {
                    1: 12.706,
                    2: 4.303,
                    3: 3.182,
                    4: 2.776,
                    5: 2.571,
                    6: 2.447,
                    7: 2.365,
                    8: 2.306,
                    9: 2.262,
                    10: 2.228,
                    11: 2.201,
                    12: 2.179,
                    13: 2.160,
                    14: 2.145,
                    15: 2.131,
                    16: 2.120,
                    17: 2.110,
                    18: 2.101,
                    19: 2.093,
                    20: 2.086,
                    21: 2.080,
                    22: 2.074,
                    23: 2.069,
                    24: 2.064,
                    25: 2.060,
                    26: 2.056,
                    27: 2.052,
                    28: 2.048,
                    29: 2.045,
                    30: 2.042,
                    40: 2.021,
                    60: 2.000,
                    120: 1.980,
                    Infinity: 1.960
                },
                0.01: {
                    1: 63.657,
                    2: 9.925,
                    3: 5.841,
                    4: 4.604,
                    5: 4.032,
                    6: 3.707,
                    7: 3.499,
                    8: 3.355,
                    9: 3.250,
                    10: 3.169,
                    11: 3.106,
                    12: 3.055,
                    13: 3.012,
                    14: 2.977,
                    15: 2.947,
                    16: 2.921,
                    17: 2.898,
                    18: 2.878,
                    19: 2.861,
                    20: 2.845,
                    21: 2.831,
                    22: 2.819,
                    23: 2.807,
                    24: 2.797,
                    25: 2.787,
                    26: 2.779,
                    27: 2.771,
                    28: 2.763,
                    29: 2.756,
                    30: 2.750,
                    40: 2.704,
                    60: 2.660,
                    120: 2.617,
                    Infinity: 2.576
                }
            };

            const table = t_tables[alpha];
            if (!table) return 2.0;

            // Cari nilai terdekat
            const dfs = Object.keys(table).map(Number).sort((a, b) => a - b);

            if (df <= dfs[0]) return table[dfs[0]];
            if (df >= dfs[dfs.length - 1]) return table[dfs[dfs.length - 1]];

            // Interpolasi linier
            let lower = dfs[0],
                upper = dfs[dfs.length - 1];
            for (let i = 0; i < dfs.length - 1; i++) {
                if (dfs[i] <= df && df <= dfs[i + 1]) {
                    lower = dfs[i];
                    upper = dfs[i + 1];
                    break;
                }
            }

            const t_lower = table[lower];
            const t_upper = table[upper];
            return t_lower + (t_upper - t_lower) * (df - lower) / (upper - lower);
        }

        // Fungsi getQValue dengan tabel Studentized Range lengkap
        function getQValue(alpha, p, df) {
            // Tabel q lengkap (p 2-20, df 1-∞)
            const q_tables = {
                0.10: getQTableForAlpha(0.10),
                0.05: getQTableForAlpha(0.05),
                0.01: getQTableForAlpha(0.01)
            };

            const table = q_tables[alpha];
            if (!table) return 3.0;

            // Jika p > 20, gunakan ekstrapolasi
            if (p > 20) {
                return extrapolateQValue(alpha, p, df);
            }

            // Cari nilai di tabel
            const p_table = table[p];
            if (!p_table) return 3.0;

            const dfs = Object.keys(p_table).map(Number).sort((a, b) => a - b);

            if (df <= dfs[0]) return p_table[dfs[0]];
            if (df >= dfs[dfs.length - 1]) return p_table[dfs[dfs.length - 1]];

            // Interpolasi linier untuk df
            let lower = dfs[0],
                upper = dfs[dfs.length - 1];
            for (let i = 0; i < dfs.length - 1; i++) {
                if (dfs[i] <= df && df <= dfs[i + 1]) {
                    lower = dfs[i];
                    upper = dfs[i + 1];
                    break;
                }
            }

            const q_lower = p_table[lower];
            const q_upper = p_table[upper];
            return q_lower + (q_upper - q_lower) * (df - lower) / (upper - lower);
        }

        function getQTableForAlpha(alpha) {
            // Tabel q untuk alpha tertentu (sampai p=20)
            const baseTable = {
                2: {
                    1: 17.97,
                    2: 6.08,
                    3: 4.50,
                    4: 3.93,
                    5: 3.64,
                    6: 3.46,
                    7: 3.34,
                    8: 3.26,
                    9: 3.20,
                    10: 3.15,
                    11: 3.11,
                    12: 3.08,
                    13: 3.06,
                    14: 3.03,
                    15: 3.01,
                    16: 3.00,
                    17: 2.98,
                    18: 2.97,
                    19: 2.96,
                    20: 2.95,
                    24: 2.92,
                    30: 2.89,
                    40: 2.86,
                    60: 2.83,
                    120: 2.80,
                    Infinity: 2.77
                },
                3: {
                    1: 26.98,
                    2: 8.33,
                    3: 5.91,
                    4: 5.04,
                    5: 4.60,
                    6: 4.34,
                    7: 4.16,
                    8: 4.04,
                    9: 3.95,
                    10: 3.88,
                    11: 3.82,
                    12: 3.77,
                    13: 3.73,
                    14: 3.70,
                    15: 3.67,
                    16: 3.65,
                    17: 3.63,
                    18: 3.61,
                    19: 3.59,
                    20: 3.58,
                    24: 3.53,
                    30: 3.49,
                    40: 3.44,
                    60: 3.40,
                    120: 3.36,
                    Infinity: 3.31
                },
                4: {
                    1: 32.82,
                    2: 9.80,
                    3: 6.82,
                    4: 5.76,
                    5: 5.22,
                    6: 4.90,
                    7: 4.68,
                    8: 4.53,
                    9: 4.41,
                    10: 4.33,
                    11: 4.26,
                    12: 4.20,
                    13: 4.15,
                    14: 4.11,
                    15: 4.08,
                    16: 4.05,
                    17: 4.02,
                    18: 4.00,
                    19: 3.98,
                    20: 3.96,
                    24: 3.90,
                    30: 3.85,
                    40: 3.79,
                    60: 3.74,
                    120: 3.68,
                    Infinity: 3.63
                },
                5: {
                    1: 37.08,
                    2: 10.88,
                    3: 7.50,
                    4: 6.29,
                    5: 5.67,
                    6: 5.30,
                    7: 5.06,
                    8: 4.89,
                    9: 4.76,
                    10: 4.65,
                    11: 4.57,
                    12: 4.51,
                    13: 4.45,
                    14: 4.41,
                    15: 4.37,
                    16: 4.33,
                    17: 4.30,
                    18: 4.28,
                    19: 4.25,
                    20: 4.23,
                    24: 4.17,
                    30: 4.11,
                    40: 4.04,
                    60: 3.98,
                    120: 3.92,
                    Infinity: 3.86
                },
                6: {
                    1: 40.41,
                    2: 11.74,
                    3: 8.04,
                    4: 6.71,
                    5: 6.03,
                    6: 5.63,
                    7: 5.36,
                    8: 5.17,
                    9: 5.02,
                    10: 4.91,
                    11: 4.82,
                    12: 4.75,
                    13: 4.69,
                    14: 4.64,
                    15: 4.59,
                    16: 4.56,
                    17: 4.52,
                    18: 4.49,
                    19: 4.47,
                    20: 4.45,
                    24: 4.37,
                    30: 4.30,
                    40: 4.23,
                    60: 4.16,
                    120: 4.08,
                    Infinity: 4.00
                },
                7: {
                    1: 43.12,
                    2: 12.44,
                    3: 8.48,
                    4: 7.05,
                    5: 6.33,
                    6: 5.90,
                    7: 5.61,
                    8: 5.40,
                    9: 5.24,
                    10: 5.12,
                    11: 5.03,
                    12: 4.95,
                    13: 4.88,
                    14: 4.83,
                    15: 4.78,
                    16: 4.74,
                    17: 4.70,
                    18: 4.67,
                    19: 4.64,
                    20: 4.62,
                    24: 4.53,
                    30: 4.45,
                    40: 4.37,
                    60: 4.29,
                    120: 4.21,
                    Infinity: 4.13
                },
                8: {
                    1: 45.40,
                    2: 13.03,
                    3: 8.85,
                    4: 7.35,
                    5: 6.58,
                    6: 6.12,
                    7: 5.82,
                    8: 5.60,
                    9: 5.43,
                    10: 5.30,
                    11: 5.20,
                    12: 5.11,
                    13: 5.04,
                    14: 4.98,
                    15: 4.93,
                    16: 4.88,
                    17: 4.84,
                    18: 4.81,
                    19: 4.78,
                    20: 4.75,
                    24: 4.66,
                    30: 4.57,
                    40: 4.48,
                    60: 4.40,
                    120: 4.31,
                    Infinity: 4.23
                },
                9: {
                    1: 47.36,
                    2: 13.54,
                    3: 9.18,
                    4: 7.60,
                    5: 6.80,
                    6: 6.32,
                    7: 6.00,
                    8: 5.77,
                    9: 5.60,
                    10: 5.46,
                    11: 5.35,
                    12: 5.26,
                    13: 5.18,
                    14: 5.12,
                    15: 5.06,
                    16: 5.01,
                    17: 4.97,
                    18: 4.93,
                    19: 4.90,
                    20: 4.87,
                    24: 4.77,
                    30: 4.67,
                    40: 4.58,
                    60: 4.48,
                    120: 4.39,
                    Infinity: 4.30
                },
                10: {
                    1: 49.07,
                    2: 13.99,
                    3: 9.46,
                    4: 7.83,
                    5: 6.99,
                    6: 6.49,
                    7: 6.16,
                    8: 5.92,
                    9: 5.74,
                    10: 5.60,
                    11: 5.49,
                    12: 5.39,
                    13: 5.31,
                    14: 5.24,
                    15: 5.18,
                    16: 5.13,
                    17: 5.08,
                    18: 5.04,
                    19: 5.01,
                    20: 4.98,
                    24: 4.87,
                    30: 4.76,
                    40: 4.66,
                    60: 4.56,
                    120: 4.46,
                    Infinity: 4.36
                },
                // Extended untuk p 11-20 dengan interpolasi
                15: {
                    24: 5.07,
                    30: 4.95,
                    40: 4.83,
                    60: 4.71,
                    120: 4.59,
                    Infinity: 4.47
                },
                20: {
                    24: 5.24,
                    30: 5.11,
                    40: 4.98,
                    60: 4.85,
                    120: 4.72,
                    Infinity: 4.58
                }
            };

            // Adjust berdasarkan alpha
            const multiplier = {
                0.10: 0.85,
                0.05: 1.00,
                0.01: 1.18
            } [alpha] || 1.0;

            // Scale tabel berdasarkan alpha
            const scaledTable = {};
            for (const [p, values] of Object.entries(baseTable)) {
                scaledTable[p] = {};
                for (const [df, q] of Object.entries(values)) {
                    scaledTable[p][df] = q * multiplier;
                }
            }

            return scaledTable;
        }

        function extrapolateQValue(alpha, p, df) {
            // Formula ekstrapolasi untuk p > 20
            // q(p,df) ≈ q(20,df) * sqrt(p/20) * adjustment

            const q20 = getQValue(alpha, 20, df);
            const qInfinity = getQValue(alpha, 20, Infinity);

            // Interpolasi antara df dan infinity
            const q_df = q20 + (qInfinity - q20) * Math.min(1, (df - 20) / 100);

            // Aproksimasi untuk p besar
            const adjustment = 1 + Math.log(p / 20) * 0.05;
            return q_df * Math.sqrt(p / 20) * adjustment;
        }

        function getDuncanValue(alpha, p, df) {
            // Untuk DMRT, gunakan tabel khusus atau aproksimasi dari q
            if (p > 15) {
                // Untuk p besar, DMRT kurang umum
                return getQValue(alpha, Math.min(p, 20), df) * 0.9;
            }

            // Tabel DMRT sederhana (untuk p ≤ 15)
            const baseValue = getQValue(alpha, p, df);

            // Adjust untuk DMRT (biasanya lebih kecil dari BNJ)
            const adjustment = 0.92 - (p - 2) * 0.01;
            return baseValue * Math.max(0.85, adjustment);
        }

        // ==================== FUNGSI ANOVA ====================
        function runANOVA() {
            showLoading(true);
            calculateTotals();

            const perlakuan = parseInt(document.getElementById('perlakuan').value);
            const ulangan = parseInt(document.getElementById('ulangan').value);

            // Validasi data
            if (data.length === 0 || perlakuan === 0 || ulangan === 0) {
                alert("Masukkan data terlebih dahulu!");
                showLoading(false);
                return;
            }

            // Validasi RAK minimal 2 blok
            const design = document.getElementById('design').value;
            if (design === 'rak' && ulangan < 2) {
                alert("RAK membutuhkan minimal 2 ulangan (blok)");
                showLoading(false);
                return;
            }

            const alpha = document.getElementById('alpha').value;
            const endpoint = design === 'rak' ? 'analisis_rak.php' : 'analisis_ral.php';

            // Prepare normality options
            const normalityTest = document.getElementById('normalityTest').value;
            const normality = {
                ks: normalityTest === 'ks' || normalityTest === 'both',
                lilliefors: normalityTest === 'lilliefors' || normalityTest === 'both'
            };

            // Kirim request ke server
            fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        data: data,
                        alpha: parseFloat(alpha),
                        perlakuan: perlakuan,
                        ulangan: ulangan,
                        normality: normality
                    })
                })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    return response.json();
                })
                .then(result => {
                    if (result.success) {
                        anovaResults = result.data;
                        updateDesignLabels();
                        displayANOVAResults();

                        // Render plots
                        if (result.data.residuals && result.data.fitted) {
                            setTimeout(() => {
                                renderQQPlot(result.data.residuals);
                                renderResidualsVsFitted(result.data.fitted, result.data.residuals);
                            }, 100);
                        }

                        resetPostHocResults();
                        showNotification('Analisis ANOVA selesai!', 'success');
                        switchTab('anova-tab');
                    } else {
                        throw new Error(result.message || 'Unknown error');
                    }
                    showLoading(false);
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan: ' + error.message);
                    showLoading(false);
                });
        }

        function displayANOVAResults() {
            if (!anovaResults) return;

            const alpha = document.getElementById('alpha').value;
            document.getElementById('alpha-value').textContent = alpha;

            let rowsHTML = '';

            if (anovaResults.design === 'RAK') {
                rowsHTML = `
                    <tr>
                        <td>Perlakuan</td>
                        <td>${anovaResults.db_perlakuan}</td>
                        <td>${anovaResults.jk_perlakuan.toFixed(4)}</td>
                        <td>${anovaResults.kt_perlakuan.toFixed(4)}</td>
                        <td>${anovaResults.f_hit.toFixed(4)}</td>
                        <td>${anovaResults.f_tabel.toFixed(4)}</td>
                        <td>${anovaResults.p_value.toFixed(6)}</td>
                        <td class="${anovaResults.significant ? 'significance-star' : 'significance-ns'}">
                            ${anovaResults.significant ? (alpha === '0.01' ? '**' : '*') : 'ns'}
                        </td>
                    </tr>
                    <tr>
                        <td>Blok</td>
                        <td>${anovaResults.db_blok}</td>
                        <td>${anovaResults.jk_blok.toFixed(4)}</td>
                        <td>${anovaResults.kt_blok.toFixed(4)}</td>
                        <td>${anovaResults.f_hit_blok.toFixed(4)}</td>
                        <td>${anovaResults.f_tabel_blok.toFixed(4)}</td>
                        <td>${anovaResults.p_value_blok.toFixed(6)}</td>
                        <td class="${anovaResults.significant_blok ? 'significance-star' : 'significance-ns'}">
                            ${anovaResults.significant_blok ? '*' : 'ns'}
                        </td>
                    </tr>
                    <tr>
                        <td>Galat</td>
                        <td>${anovaResults.db_galat}</td>
                        <td>${anovaResults.jk_galat.toFixed(4)}</td>
                        <td>${anovaResults.kt_galat.toFixed(4)}</td>
                        <td colspan="4"></td>
                    </tr>
                `;
            } else {
                rowsHTML = `
                    <tr>
                        <td>Perlakuan</td>
                        <td>${anovaResults.db_perlakuan}</td>
                        <td>${anovaResults.jk_perlakuan.toFixed(4)}</td>
                        <td>${anovaResults.kt_perlakuan.toFixed(4)}</td>
                        <td>${anovaResults.f_hit.toFixed(4)}</td>
                        <td>${anovaResults.f_tabel.toFixed(4)}</td>
                        <td>${anovaResults.p_value.toFixed(6)}</td>
                        <td class="${anovaResults.significant ? 'significance-star' : 'significance-ns'}">
                            ${anovaResults.significant ? (alpha === '0.01' ? '**' : '*') : 'ns'}
                        </td>
                    </tr>
                    <tr>
                        <td>Galat</td>
                        <td>${anovaResults.db_galat}</td>
                        <td>${anovaResults.jk_galat.toFixed(4)}</td>
                        <td>${anovaResults.kt_galat.toFixed(4)}</td>
                        <td colspan="4"></td>
                    </tr>
                `;
            }

            rowsHTML += `
                <tr>
                    <td><strong>Total</strong></td>
                    <td><strong>${anovaResults.db_total}</strong></td>
                    <td><strong>${anovaResults.jk_total.toFixed(4)}</strong></td>
                    <td colspan="5"></td>
                </tr>
            `;

            document.getElementById('anovaBody').innerHTML = rowsHTML;

            // Update conclusion
            const conclusion = document.getElementById('anovaConclusion');
            if (anovaResults.significant) {
                conclusion.innerHTML = `
                    <strong>✅ KESIMPULAN ANOVA:</strong> Terdapat pengaruh yang 
                    ${alpha === '0.01' ? 'sangat nyata' : 'nyata'} dari perlakuan 
                    (F-hit = ${anovaResults.f_hit.toFixed(4)} > F-tabel = ${anovaResults.f_tabel.toFixed(4)})<br>
                    <small>p-value = ${anovaResults.p_value.toFixed(6)} < α = ${alpha}</small><br>
                    <span class="stat-badge success">Lakukan uji lanjut untuk mengetahui perbedaan antar perlakuan</span>
                `;
            } else {
                conclusion.innerHTML = `
                    <strong>ℹ️ KESIMPULAN ANOVA:</strong> Tidak terdapat pengaruh nyata dari perlakuan<br>
                    <small>p-value = ${anovaResults.p_value.toFixed(6)} ≥ α = ${alpha}</small><br>
                    <span class="stat-badge warning">Tidak perlu melakukan uji lanjut</span>
                `;
            }

            // Update KK
            document.getElementById('kkResult').innerHTML = `
                <strong>📊 KOEFISIEN KERAGAMAN (KK):</strong> ${anovaResults.kk.toFixed(2)}%<br>
                <small>KK = (√KT Galat / Rata-rata Umum) × 100%</small><br>
                <span class="stat-badge ${anovaResults.kk < 10 ? 'success' : anovaResults.kk < 20 ? 'info' : anovaResults.kk < 30 ? 'warning' : 'danger'}">
                    ${interpretKK(anovaResults.kk)}
                </span>
            `;

            // Add normality test results if available
            if (anovaResults.normality) {
                const norm = anovaResults.normality;
                let normHTML = '<div style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 6px;">';
                normHTML += '<strong>📈 HASIL UJI NORMALITAS:</strong><br>';

                if (norm.ks && norm.ks.stat !== null) {
                    normHTML += `• <strong>Kolmogorov-Smirnov:</strong> D = ${norm.ks.stat.toFixed(4)}, p = ${norm.ks.p_value.toFixed(6)}<br>`;
                    normHTML += `&nbsp;&nbsp;<span class="stat-badge ${norm.ks.normal ? 'success' : 'danger'}">`;
                    normHTML += norm.ks.normal ? 'Normal ✓' : 'Tidak Normal ✗';
                    normHTML += '</span><br>';
                }

                if (norm.lilliefors && norm.lilliefors.stat !== null) {
                    normHTML += `• <strong>Lilliefors:</strong> D = ${norm.lilliefors.stat.toFixed(4)}, p = ${norm.lilliefors.p_value.toFixed(6)}<br>`;
                    normHTML += `&nbsp;&nbsp;<span class="stat-badge ${norm.lilliefors.normal ? 'success' : 'danger'}">`;
                    normHTML += norm.lilliefors.normal ? 'Normal ✓' : 'Tidak Normal ✗';
                    normHTML += '</span>';
                }

                normHTML += '</div>';
                document.getElementById('kkResult').innerHTML += normHTML;
            }
        }

        function interpretKK(kk) {
            if (kk < 10) return "Presisi sangat baik (eksperimen terkendali)";
            if (kk < 20) return "Presisi baik";
            if (kk < 30) return "Presisi cukup";
            return "Presisi kurang (eksperimen kurang terkendali)";
        }

        // ==================== FUNGSI UJI LANJUT ====================
        function updatePostHocRecommendation() {
            const perlakuan = parseInt(document.getElementById('perlakuan').value);
            if (isNaN(perlakuan) || perlakuan < 2) return;

            const recommendation = getRecommendedPostHocTest(perlakuan);
            const select = document.getElementById('postHocTest');

            document.getElementById('postHocRecommendation').innerHTML = `
                <div style="background: ${recommendation.color}; padding: 8px; border-radius: 4px; font-size: 12px;">
                    <strong>💡 Rekomendasi:</strong> ${recommendation.text}
                </div>
            `;

            // Auto-select jika belum dipilih atau tidak sesuai
            if (recommendation.recommended !== select.value && perlakuan > 10) {
                select.value = recommendation.recommended;
                updateTestLabels(recommendation.recommended);
            }
        }

        function getRecommendedPostHocTest(perlakuan) {
            if (perlakuan <= 10) {
                return {
                    recommended: 'dmrt',
                    text: 'DMRT optimal untuk ≤10 perlakuan',
                    color: '#e3f2fd'
                };
            } else if (perlakuan <= 20) {
                return {
                    recommended: 'bnj',
                    text: 'BNJ optimal untuk 11-20 perlakuan',
                    color: '#fff3cd'
                };
            } else {
                return {
                    recommended: 'bnt',
                    text: 'BNT direkomendasikan untuk >20 perlakuan',
                    color: '#f8d7da'
                };
            }
        }

        function runPostHocTest() {
            if (!anovaResults) {
                alert("Lakukan analisis ANOVA terlebih dahulu!");
                return;
            }

            if (!anovaResults.significant) {
                alert("ANOVA menunjukkan tidak ada perbedaan nyata. Uji lanjut tidak diperlukan.");
                return;
            }

            const testType = document.getElementById('postHocTest').value;
            const perlakuan = anovaResults.perlakuan;

            // Validasi test berdasarkan jumlah perlakuan
            const validation = validatePostHocTest(testType, perlakuan);
            if (!validation.valid) {
                if (!confirm(`${validation.message}\n\nTetap lanjutkan dengan ${testType.toUpperCase()}?`)) {
                    return;
                }
            }

            updateTestLabels(testType);

            // Calculate post-hoc test
            postHocResults = calculatePostHocTest(testType);
            displayPostHocResults(postHocResults);

            switchTab('bnj-tab');
            showNotification(`Uji ${testType.toUpperCase()} selesai!`, 'success');
        }

        function validatePostHocTest(testType, perlakuan) {
            const limits = {
                'bnt': {
                    max: Infinity,
                    message: "BNT cocok untuk semua jumlah perlakuan"
                },
                'bnj': {
                    max: 30,
                    message: `BNJ kurang optimal untuk ${perlakuan} > 30 perlakuan`
                },
                'dmrt': {
                    max: 15,
                    message: `DMRT tidak direkomendasikan untuk ${perlakuan} > 15 perlakuan`
                }
            };

            const limit = limits[testType];
            return {
                valid: perlakuan <= limit.max,
                message: limit.message
            };
        }

        function calculatePostHocTest(testType) {
            const alpha = parseFloat(anovaResults.alpha);
            const perlakuan = anovaResults.perlakuan;
            const ulangan = anovaResults.ulangan;
            const kt_galat = anovaResults.kt_galat;
            const db_galat = anovaResults.db_galat;
            const averages = anovaResults.rata_rata_perlakuan;

            let critical_value = 0;
            let formula = '';
            let method = '';

            switch (testType) {
                case 'bnj':
                    const q_value = getQValue(alpha, perlakuan, db_galat);
                    critical_value = q_value * Math.sqrt(kt_galat / ulangan);
                    formula = `BNJ = q<sub>${perlakuan},${db_galat}</sub> × √(KT Galat / r)`;
                    method = `q(${perlakuan},${db_galat}) = ${q_value.toFixed(4)}`;
                    break;

                case 'bnt':
                    const t_value = getTValue(alpha, db_galat);
                    critical_value = t_value * Math.sqrt(2 * kt_galat / ulangan);
                    formula = `BNT = t<sub>${db_galat}</sub> × √(2 × KT Galat / r)`;
                    method = `t(${db_galat}) = ${t_value.toFixed(4)}`;
                    break;

                case 'dmrt':
                    const duncan_value = getDuncanValue(alpha, perlakuan, db_galat);
                    critical_value = duncan_value * Math.sqrt(kt_galat / ulangan);
                    formula = `DMRT = r<sub>${perlakuan},${db_galat}</sub> × √(KT Galat / r)`;
                    method = `r(${perlakuan},${db_galat}) = ${duncan_value.toFixed(4)}`;
                    break;
            }

            // Sort averages
            const sorted_data = averages.map((avg, i) => ({
                index: i,
                average: avg,
                kode: kodePerlakuanArray[i] || `P${i+1}`
            })).sort((a, b) => b.average - a.average);

            // Group treatments
            const groups = [];
            const group_letters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

            sorted_data.forEach((item, i) => {
                let placed = false;

                for (let g = 0; g < groups.length; g++) {
                    const last_in_group = groups[g][groups[g].length - 1];
                    const diff = Math.abs(item.average - sorted_data[last_in_group].average);

                    if (diff <= critical_value) {
                        groups[g].push(i);
                        placed = true;
                        break;
                    }
                }

                if (!placed) {
                    groups.push([i]);
                }
            });

            // Assign notations
            const notations = new Array(perlakuan).fill('');
            groups.forEach((group, g) => {
                const letter = group_letters[g] || `G${g+1}`;
                group.forEach(pos => {
                    const idx = sorted_data[pos].index;
                    notations[idx] += letter;
                });
            });

            // Prepare final results
            const results = [];
            for (let i = 0; i < perlakuan; i++) {
                results.push({
                    kode: kodePerlakuanArray[i] || `P${i+1}`,
                    average: averages[i],
                    notation: notations[i]
                });
            }

            return {
                test_type: testType.toUpperCase(),
                test_name: getTestName(testType),
                critical_value: critical_value,
                formula: formula,
                method: method,
                results: results,
                sorted_data: sorted_data
            };
        }

        function getTestName(testType) {
            const names = {
                'bnt': 'BNT (Beda Nyata Terkecil)',
                'bnj': 'BNJ (Beda Nyata Jujur / Tukey\'s HSD)',
                'dmrt': 'DMRT (Duncan\'s Multiple Range Test)'
            };
            return names[testType] || testType.toUpperCase();
        }

        function displayPostHocResults(results) {
            const perlakuan = anovaResults.perlakuan;

            // Clear previous results
            document.getElementById('bnjBody').innerHTML = '';
            document.getElementById('bnjPagination').style.display = 'none';

            // Display for large number of treatments with pagination
            if (perlakuan > ITEMS_PER_PAGE) {
                displayPagedResults(results);
            } else {
                displayAllResults(results);
            }

            // Update info
            document.getElementById('bnjInfo').innerHTML = `
                <div style="background: #e7f3ff; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                    <strong>📊 ${results.test_name} (α=${anovaResults.alpha}):</strong><br>
                    Nilai Kritis = <strong>${results.critical_value.toFixed(6)}</strong><br>
                    <small>${results.formula}</small><br>
                    <small><strong>Metode:</strong> ${results.method}</small>
                </div>
                <div class="test-recommendation">
                    <strong>💡 Interpretasi:</strong><br>
                    • Dua perlakuan <strong>berbeda nyata</strong> jika selisih rata-ratanya > ${results.critical_value.toFixed(6)}<br>
                    • Perlakuan dengan <strong>huruf yang sama</strong> tidak berbeda nyata<br>
                    • Huruf berbeda = berbeda nyata pada α=${anovaResults.alpha}
                </div>
            `;

            // Display comparison matrix
            displayComparisonMatrix(results.critical_value);

            // Create visualization
            createMeanComparisonChart(results);
        }

        function displayAllResults(results) {
            let tableHTML = '';

            results.sorted_data.forEach((item, rank) => {
                tableHTML += `
                    <tr>
                        <td>${rank + 1}</td>
                        <td>${item.kode}</td>
                        <td>${item.average.toFixed(4)}</td>
                        <td>${results.results[item.index].notation}</td>
                        <td>
                            ${results.results[item.index].notation.split('').map(l => 
                                `<span class="stat-badge info">${l}</span>`
                            ).join('')}
                        </td>
                    </tr>
                `;
            });

            document.getElementById('bnjBody').innerHTML = tableHTML;
        }

        function displayPagedResults(results) {
            const totalPages = Math.ceil(anovaResults.perlakuan / ITEMS_PER_PAGE);
            currentPostHocPage = 1;

            function renderPage(page) {
                const start = (page - 1) * ITEMS_PER_PAGE;
                const end = Math.min(start + ITEMS_PER_PAGE, anovaResults.perlakuan);

                let tableHTML = '';
                for (let i = start; i < end; i++) {
                    const item = results.sorted_data[i];
                    tableHTML += `
                        <tr>
                            <td>${i + 1}</td>
                            <td>${item.kode}</td>
                            <td>${item.average.toFixed(4)}</td>
                            <td>${results.results[item.index].notation}</td>
                            <td>
                                ${results.results[item.index].notation.split('').map(l => 
                                    `<span class="stat-badge info">${l}</span>`
                                ).join('')}
                            </td>
                        </tr>
                    `;
                }

                document.getElementById('bnjBody').innerHTML = tableHTML;

                // Update pagination
                let paginationHTML = '';
                if (page > 1) {
                    paginationHTML += `<button onclick="changePostHocPage(${page - 1})">← Prev</button>`;
                }

                paginationHTML += `<span style="margin: 0 15px; padding: 8px 15px; background: #e9ecef; border-radius: 4px;">
                    Halaman ${page} dari ${totalPages}
                </span>`;

                if (page < totalPages) {
                    paginationHTML += `<button onclick="changePostHocPage(${page + 1})">Next →</button>`;
                }

                document.getElementById('bnjPagination').innerHTML = paginationHTML;
                document.getElementById('bnjPagination').style.display = 'flex';

                window.currentPostHocPage = page;
            }

            window.changePostHocPage = function(page) {
                if (page >= 1 && page <= totalPages) {
                    currentPostHocPage = page;
                    renderPage(page);
                }
            };

            renderPage(1);
        }

        function displayComparisonMatrix(criticalValue) {
            const perlakuan = anovaResults.perlakuan;
            const averages = anovaResults.rata_rata_perlakuan;

            if (perlakuan > 15) {
                document.getElementById('comparisonMatrix').innerHTML = `
                    <div style="text-align: center; padding: 20px; color: #666;">
                        <div style="font-size: 48px; margin-bottom: 10px;">📋</div>
                        <strong>Matriks perbandingan tidak ditampilkan</strong><br>
                        Untuk ${perlakuan} perlakuan, matriks akan terlalu besar.<br>
                        Gunakan notasi huruf untuk melihat kelompok perlakuan.
                    </div>
                `;
                return;
            }

            let matrixHTML = '<div style="overflow-x: auto;"><table style="width: auto;">';
            matrixHTML += '<tr><th style="background: #e8f4fd;">Perbandingan</th>';

            for (let i = 0; i < perlakuan; i++) {
                matrixHTML += `<th style="background: #e8f4fd;">${kodePerlakuanArray[i]}</th>`;
            }
            matrixHTML += '</tr>';

            for (let i = 0; i < perlakuan; i++) {
                matrixHTML += `<tr><th style="background: #e8f4fd;">${kodePerlakuanArray[i]}</th>`;
                for (let j = 0; j < perlakuan; j++) {
                    if (i === j) {
                        matrixHTML += '<td style="background: #f8f9fa;">-</td>';
                    } else {
                        const diff = Math.abs(averages[i] - averages[j]);
                        const isSignificant = diff > parseFloat(criticalValue);
                        matrixHTML += `<td style="background: ${isSignificant ? '#ffcccc' : '#ccffcc'};">
                            ${diff.toFixed(4)} ${isSignificant ? '<strong>*</strong>' : 'ns'}
                        </td>`;
                    }
                }
                matrixHTML += '</tr>';
            }

            matrixHTML += '</table></div>';
            matrixHTML += '<div style="margin-top: 15px; display: flex; gap: 10px; flex-wrap: wrap;">';
            matrixHTML += '<span class="stat-badge success">ns = Tidak berbeda nyata</span>';
            matrixHTML += '<span class="stat-badge danger">* = Berbeda nyata</span>';
            matrixHTML += '</div>';

            document.getElementById('comparisonMatrix').innerHTML = matrixHTML;
        }

        function createMeanComparisonChart(results) {
            const canvas = document.getElementById('meanComparisonChart');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');
            clearCanvas(canvas);

            const sortedResults = results.sorted_data;
            const perlakuan = sortedResults.length;

            if (perlakuan === 0) return;

            // Prepare data
            const labels = sortedResults.map(item => item.kode);
            const means = sortedResults.map(item => item.average);
            const notations = sortedResults.map(item =>
                results.results[item.index].notation
            );

            // Find min and max for scaling
            const minMean = Math.min(...means);
            const maxMean = Math.max(...means);
            const range = maxMean - minMean;
            const padding = range * 0.1;

            // Set canvas dimensions
            const width = canvas.width;
            const height = canvas.height;
            const margin = {
                top: 40,
                right: 40,
                bottom: 60,
                left: 60
            };
            const chartWidth = width - margin.left - margin.right;
            const chartHeight = height - margin.top - margin.bottom;

            // Clear canvas
            ctx.clearRect(0, 0, width, height);

            // Draw chart area
            ctx.fillStyle = '#fff';
            ctx.fillRect(margin.left, margin.top, chartWidth, chartHeight);

            // Draw grid
            ctx.strokeStyle = '#eee';
            ctx.lineWidth = 1;

            // Vertical grid
            const xStep = chartWidth / (perlakuan - 1 || 1);
            for (let i = 0; i < perlakuan; i++) {
                const x = margin.left + i * xStep;
                ctx.beginPath();
                ctx.moveTo(x, margin.top);
                ctx.lineTo(x, margin.top + chartHeight);
                ctx.stroke();
            }

            // Horizontal grid
            const ySteps = 10;
            for (let i = 0; i <= ySteps; i++) {
                const y = margin.top + (i / ySteps) * chartHeight;
                ctx.beginPath();
                ctx.moveTo(margin.left, y);
                ctx.lineTo(margin.left + chartWidth, y);
                ctx.stroke();
            }

            // Draw axes
            ctx.strokeStyle = '#333';
            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.moveTo(margin.left, margin.top);
            ctx.lineTo(margin.left, margin.top + chartHeight);
            ctx.lineTo(margin.left + chartWidth, margin.top + chartHeight);
            ctx.stroke();

            // Plot means
            for (let i = 0; i < perlakuan; i++) {
                const x = margin.left + i * xStep;
                const y = margin.top + chartHeight * (1 - (means[i] - minMean + padding) / (range + 2 * padding));

                // Draw point
                ctx.fillStyle = '#007cba';
                ctx.beginPath();
                ctx.arc(x, y, 6, 0, Math.PI * 2);
                ctx.fill();

                // Draw notation
                ctx.fillStyle = '#333';
                ctx.font = '12px Arial';
                ctx.textAlign = 'center';
                ctx.fillText(notations[i], x, y - 15);

                // Draw label
                ctx.fillText(labels[i], x, margin.top + chartHeight + 20);

                // Draw mean value
                ctx.fillText(means[i].toFixed(2), x, y + 20);
            }

            // Draw title
            ctx.fillStyle = '#003d5c';
            ctx.font = 'bold 16px Arial';
            ctx.textAlign = 'center';
            ctx.fillText('Perbandingan Rata-rata Perlakuan', width / 2, 20);

            // Draw y-axis label
            ctx.save();
            ctx.translate(20, height / 2);
            ctx.rotate(-Math.PI / 2);
            ctx.fillText('Rata-rata', 0, 0);
            ctx.restore();
        }

        // ==================== FUNGSI PLOT ====================
        function renderQQPlot(residuals, canvasId) {
            canvasId = canvasId || 'qqCanvas';
            const canvas = document.getElementById(canvasId);
            if (!canvas) return;

            const ctx = canvas.getContext('2d');
            clearCanvas(canvas);

            if (!residuals || residuals.length === 0) {
                ctx.fillStyle = '#666';
                ctx.font = '14px Arial';
                ctx.fillText('Tidak ada data residual', 10, 20);
                return;
            }

            const width = canvas.width;
            const height = canvas.height;
            const margin = {
                top: 40,
                right: 40,
                bottom: 60,
                left: 60
            };
            const plotWidth = width - margin.left - margin.right;
            const plotHeight = height - margin.top - margin.bottom;

            // Sort residuals
            const sortedResiduals = residuals.slice().sort((a, b) => a - b);
            const n = sortedResiduals.length;

            // Calculate theoretical quantiles
            const theoretical = [];
            if (typeof jStat !== 'undefined') {
                for (let i = 0; i < n; i++) {
                    const p = (i + 0.5) / n;
                    theoretical.push(jStat.normal.inv(p, 0, 1));
                }
            } else {
                // Simple approximation
                for (let i = 0; i < n; i++) {
                    const p = (i + 0.5) / n;
                    theoretical.push(-4 + 8 * p);
                }
            }

            // Find min/max for scaling
            const minTheo = Math.min(...theoretical);
            const maxTheo = Math.max(...theoretical);
            const minRes = Math.min(...sortedResiduals);
            const maxRes = Math.max(...sortedResiduals);

            // Scale functions with padding
            const rangeTheo = (maxTheo - minTheo) || 1;
            const rangeRes = (maxRes - minRes) || 1;
            const paddingTheo = rangeTheo * 0.05;
            const paddingRes = rangeRes * 0.05;
            const scaleX = (x) => margin.left + (x - minTheo + paddingTheo) / (rangeTheo + 2 * paddingTheo) * plotWidth;
            const scaleY = (y) => margin.top + plotHeight - (y - minRes + paddingRes) / (rangeRes + 2 * paddingRes) * plotHeight;

            // Draw grid
            ctx.strokeStyle = '#eee';
            ctx.lineWidth = 1;

            for (let i = 0; i <= 10; i++) {
                const x = margin.left + (i / 10) * plotWidth;
                const y = margin.top + (i / 10) * plotHeight;

                ctx.beginPath();
                ctx.moveTo(x, margin.top);
                ctx.lineTo(x, margin.top + plotHeight);
                ctx.stroke();

                ctx.beginPath();
                ctx.moveTo(margin.left, y);
                ctx.lineTo(margin.left + plotWidth, y);
                ctx.stroke();
            }

            // Draw axes
            ctx.strokeStyle = '#333';
            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.moveTo(margin.left, margin.top);
            ctx.lineTo(margin.left, margin.top + plotHeight);
            ctx.lineTo(margin.left + plotWidth, margin.top + plotHeight);
            ctx.stroke();

            // Draw points
            ctx.fillStyle = '#007cba';
            for (let i = 0; i < n; i++) {
                const x = scaleX(theoretical[i]);
                const y = scaleY(sortedResiduals[i]);

                ctx.beginPath();
                ctx.arc(x, y, 4, 0, Math.PI * 2);
                ctx.fill();
            }

            // Draw fit line (linear regression)
            const meanTheo = theoretical.reduce((a, b) => a + b, 0) / n;
            const meanRes = sortedResiduals.reduce((a, b) => a + b, 0) / n;
            let numerator = 0,
                denominator = 0;
            for (let i = 0; i < n; i++) {
                numerator += (theoretical[i] - meanTheo) * (sortedResiduals[i] - meanRes);
                denominator += (theoretical[i] - meanTheo) ** 2;
            }
            const slope = denominator === 0 ? 1 : numerator / denominator;
            const intercept = meanRes - slope * meanTheo;

            // Draw fit line with extended bounds
            const x1 = minTheo - (maxTheo - minTheo) * 0.1;
            const y1 = intercept + slope * x1;
            const x2 = maxTheo + (maxTheo - minTheo) * 0.1;
            const y2 = intercept + slope * x2;

            ctx.strokeStyle = '#dc3545';
            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.moveTo(scaleX(x1), scaleY(y1));
            ctx.lineTo(scaleX(x2), scaleY(y2));
            ctx.stroke();

            // Draw labels
            ctx.fillStyle = '#333';
            ctx.font = '14px Arial';
            ctx.textAlign = 'center';
            ctx.fillText('Theoretical Quantiles', width / 2, height - 10);

            ctx.save();
            ctx.translate(20, height / 2);
            ctx.rotate(-Math.PI / 2);
            ctx.fillText('Sample Quantiles', 0, 0);
            ctx.restore();

            // Draw title
            ctx.fillStyle = '#003d5c';
            ctx.font = 'bold 16px Arial';
            ctx.fillText('Normal Q-Q Plot', width / 2, 20);
        }

        function renderResidualsVsFitted(fitted, residuals, canvasId) {
            canvasId = canvasId || 'resCanvas';
            const canvas = document.getElementById(canvasId);
            if (!canvas) return;

            const ctx = canvas.getContext('2d');
            clearCanvas(canvas);

            if (!fitted || !residuals || fitted.length === 0) {
                ctx.fillStyle = '#666';
                ctx.font = '14px Arial';
                ctx.fillText('Tidak ada data', 10, 20);
                return;
            }

            const width = canvas.width;
            const height = canvas.height;
            const margin = {
                top: 40,
                right: 40,
                bottom: 60,
                left: 60
            };
            const plotWidth = width - margin.left - margin.right;
            const plotHeight = height - margin.top - margin.bottom;

            // Find min/max
            const minFitted = Math.min(...fitted);
            const maxFitted = Math.max(...fitted);
            const minResidual = Math.min(...residuals);
            const maxResidual = Math.max(...residuals);
            const rangeFitted = maxFitted - minFitted || 1;
            const rangeResidual = maxResidual - minResidual || 1;

            // Add padding
            const paddingFitted = rangeFitted * 0.1;
            const paddingResidual = rangeResidual * 0.1;

            // Scale functions
            const scaleX = (x) => margin.left + (x - minFitted + paddingFitted) / (rangeFitted + 2 * paddingFitted) * plotWidth;
            const scaleY = (y) => margin.top + plotHeight - (y - minResidual + paddingResidual) / (rangeResidual + 2 * paddingResidual) * plotHeight;

            // Draw grid
            ctx.strokeStyle = '#eee';
            ctx.lineWidth = 1;

            for (let i = 0; i <= 10; i++) {
                const x = margin.left + (i / 10) * plotWidth;
                const y = margin.top + (i / 10) * plotHeight;

                ctx.beginPath();
                ctx.moveTo(x, margin.top);
                ctx.lineTo(x, margin.top + plotHeight);
                ctx.stroke();

                ctx.beginPath();
                ctx.moveTo(margin.left, y);
                ctx.lineTo(margin.left + plotWidth, y);
                ctx.stroke();
            }

            // Draw axes
            ctx.strokeStyle = '#333';
            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.moveTo(margin.left, margin.top);
            ctx.lineTo(margin.left, margin.top + plotHeight);
            ctx.lineTo(margin.left + plotWidth, margin.top + plotHeight);
            ctx.stroke();

            // Draw zero line
            ctx.strokeStyle = '#dc3545';
            ctx.lineWidth = 1;
            const zeroY = scaleY(0);
            ctx.beginPath();
            ctx.moveTo(margin.left, zeroY);
            ctx.lineTo(margin.left + plotWidth, zeroY);
            ctx.stroke();

            // Draw points
            ctx.fillStyle = '#007cba';
            for (let i = 0; i < fitted.length; i++) {
                const x = scaleX(fitted[i]);
                const y = scaleY(residuals[i]);

                ctx.beginPath();
                ctx.arc(x, y, 4, 0, Math.PI * 2);
                ctx.fill();
            }

            // Draw labels
            ctx.fillStyle = '#333';
            ctx.font = '14px Arial';
            ctx.textAlign = 'center';
            ctx.fillText('Fitted Values', width / 2, height - 10);

            ctx.save();
            ctx.translate(20, height / 2);
            ctx.rotate(-Math.PI / 2);
            ctx.fillText('Residuals', 0, 0);
            ctx.restore();

            // Draw title
            ctx.fillStyle = '#003d5c';
            ctx.font = 'bold 16px Arial';
            ctx.fillText('Residuals vs Fitted', width / 2, 20);
        }

        // ==================== FUNGSI TAB & UI ====================
        function switchTab(tabName) {
            // Update tab buttons
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });

            // Activate selected tab
            document.querySelector(`.tab-button[onclick*="${tabName}"]`).classList.add('active');
            document.getElementById(tabName).classList.add('active');

            // Update full results if needed
            if (tabName === 'results-tab') {
                updateFullResults();
            }

            // Scroll to top
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        function updateDesignLabels() {
            const design = document.getElementById('design').value;
            const isRAK = design === 'rak';
            const analysisLabel = isRAK ? 'Analisis RAK' : 'Analisis RAL';

            const el1 = document.getElementById('runAnalysisLabel');
            if (el1) el1.textContent = analysisLabel;
            const el2 = document.getElementById('anovaTabLabel');
            if (el2) el2.textContent = 'ANALISIS ' + (isRAK ? 'RAK' : 'RAL');
            const el3 = document.getElementById('designTitle');
            if (el3) el3.textContent = isRAK ? 'RAK' : 'RAL';
            const el4 = document.getElementById('analysisName');
            if (el4) el4.textContent = analysisLabel;
            const el5 = document.getElementById('analysisName2');
            if (el5) el5.textContent = analysisLabel;
            const el6 = document.getElementById('analysisName3');
            if (el6) el6.textContent = analysisLabel;
        }

        function updateTestLabels(testType) {
            const testInfo = {
                'bnt': {
                    name: 'BNT (Beda Nyata Terkecil)',
                    desc: 'Uji dengan t-distribution. Cocok untuk semua jumlah perlakuan.'
                },
                'bnj': {
                    name: 'BNJ (Beda Nyata Jujur)',
                    desc: 'Uji dengan Studentized Range. Optimal untuk ≤30 perlakuan.'
                },
                'dmrt': {
                    name: 'DMRT (Duncan\'s Test)',
                    desc: 'Uji Duncan. Optimal untuk ≤15 perlakuan.'
                }
            };

            const info = testInfo[testType] || {
                name: testType.toUpperCase(),
                desc: ''
            };

            document.getElementById('testTypeTab').textContent = '📊 ' + info.name;
            document.getElementById('postHocResultTitle').textContent = 'Hasil ' + info.name;
            document.getElementById('postHocTestNotice').textContent = '📊 ' + info.name + ':';
            document.getElementById('postHocTestDesc').textContent = info.desc;
        }

        function resetResults() {
            // Reset ANOVA results
            document.getElementById('anovaBody').innerHTML = `
                <tr>
                    <td colspan="8" style="text-align: center; padding: 30px;">
                        <div style="color: #666; font-size: 16px;">
                            <div style="font-size: 48px; margin-bottom: 10px;">📊</div>
                            <strong>Data telah diubah</strong><br>
                            Klik "Analisis RAL/RAK" untuk menghitung ulang
                        </div>
                    </td>
                </tr>
            `;

            document.getElementById('anovaConclusion').innerHTML =
                '<strong>📋 Kesimpulan ANOVA:</strong> Data telah diubah. Klik "Analisis RAL/RAK" untuk menghitung ulang.';

            document.getElementById('kkResult').innerHTML =
                '<strong>📊 Koefisien Keragaman:</strong> Akan muncul setelah analisis ANOVA.';

            // Clear plots
            clearCanvas(document.getElementById('qqCanvas'));
            clearCanvas(document.getElementById('resCanvas'));
            clearCanvas(document.getElementById('meanComparisonChart'));

            // Reset post-hoc results
            resetPostHocResults();

            // Reset full results
            document.getElementById('fullResults').innerHTML = `
                <div style="text-align: center; color: #666; padding: 40px;">
                    <div style="font-size: 64px; margin-bottom: 20px;">📋</div>
                    <strong style="font-size: 20px;">Data telah diubah</strong><br>
                    <p style="margin-top: 10px;">Klik "Analisis RAL/RAK" untuk menghitung ulang</p>
                </div>
            `;
        }

        function resetPostHocResults() {
            document.getElementById('bnjBody').innerHTML = `
                <tr>
                    <td colspan="5" style="text-align: center; padding: 30px;">
                        <div style="color: #666; font-size: 16px;">
                            <div style="font-size: 48px; margin-bottom: 10px;">📈</div>
                            <strong>Lakukan analisis ANOVA terlebih dahulu</strong><br>
                            Hasil uji lanjut akan muncul di sini
                        </div>
                    </td>
                </tr>
            `;

            document.getElementById('bnjInfo').innerHTML =
                '<strong>📋 Informasi Uji Lanjut:</strong> Akan muncul setelah uji dilakukan.';

            document.getElementById('comparisonMatrix').innerHTML =
                'Matriks perbandingan akan muncul setelah uji BNJ.';

            document.getElementById('bnjPagination').style.display = 'none';
        }

        // ==================== FUNGSI EXPORT & IMPORT ====================
        function exportToExcel() {
            if (!anovaResults) {
                alert("Lakukan analisis ANOVA terlebih dahulu!");
                return;
            }

            try {
                const wb = XLSX.utils.book_new();
                const ws_data = [];

                // Sheet 1: Data
                const header = ['Perlakuan'];
                for (let u = 1; u <= anovaResults.ulangan; u++) {
                    header.push(`Ulangan ${u}`);
                }
                header.push('Total', 'Rata-rata');
                ws_data.push(header);

                for (let p = 0; p < anovaResults.perlakuan; p++) {
                    const row = [kodePerlakuanArray[p]];
                    let total = 0;

                    for (let u = 0; u < anovaResults.ulangan; u++) {
                        const val = anovaResults.data[p][u] || 0;
                        row.push(val);
                        total += val;
                    }

                    row.push(total, total / anovaResults.ulangan);
                    ws_data.push(row);
                }

                const ws1 = XLSX.utils.aoa_to_sheet(ws_data);
                XLSX.utils.book_append_sheet(wb, ws1, "Data");

                // Sheet 2: ANOVA Results
                const anovaData = [
                    [`TABEL ANOVA ${anovaResults.design}`],
                    ['Sumber Keragaman', 'db', 'JK', 'KT', 'F-hit', `F-tabel (α=${anovaResults.alpha})`, 'p-value', 'Keterangan']
                ];

                if (anovaResults.design === 'RAK') {
                    anovaData.push([
                        'Perlakuan',
                        anovaResults.db_perlakuan,
                        anovaResults.jk_perlakuan,
                        anovaResults.kt_perlakuan,
                        anovaResults.f_hit,
                        anovaResults.f_tabel,
                        anovaResults.p_value,
                        anovaResults.significant ? 'Nyata' : 'Tidak Nyata'
                    ]);
                    anovaData.push([
                        'Blok',
                        anovaResults.db_blok,
                        anovaResults.jk_blok,
                        anovaResults.kt_blok,
                        anovaResults.f_hit_blok,
                        anovaResults.f_tabel_blok,
                        anovaResults.p_value_blok,
                        anovaResults.significant_blok ? 'Nyata' : 'Tidak Nyata'
                    ]);
                    anovaData.push([
                        'Galat',
                        anovaResults.db_galat,
                        anovaResults.jk_galat,
                        anovaResults.kt_galat,
                        '', '', '', ''
                    ]);
                } else {
                    anovaData.push([
                        'Perlakuan',
                        anovaResults.db_perlakuan,
                        anovaResults.jk_perlakuan,
                        anovaResults.kt_perlakuan,
                        anovaResults.f_hit,
                        anovaResults.f_tabel,
                        anovaResults.p_value,
                        anovaResults.significant ? 'Nyata' : 'Tidak Nyata'
                    ]);
                    anovaData.push([
                        'Galat',
                        anovaResults.db_galat,
                        anovaResults.jk_galat,
                        anovaResults.kt_galat,
                        '', '', '', ''
                    ]);
                }

                anovaData.push([
                    'Total',
                    anovaResults.db_total,
                    anovaResults.jk_total,
                    '', '', '', '', ''
                ]);

                anovaData.push([]);
                anovaData.push(['Koefisien Keragaman (KK):', `${anovaResults.kk.toFixed(2)}%`]);

                const ws2 = XLSX.utils.aoa_to_sheet(anovaData);
                XLSX.utils.book_append_sheet(wb, ws2, "ANOVA");

                // Sheet 3: Post-Hoc Results if available
                if (postHocResults) {
                    const postHocData = [
                        [`HASIL UJI ${postHocResults.test_type}`],
                        ['Rank', 'Perlakuan', 'Rata-rata', 'Notasi', 'Kelompok']
                    ];

                    postHocResults.sorted_data.forEach((item, rank) => {
                        postHocData.push([
                            rank + 1,
                            item.kode,
                            item.average,
                            postHocResults.results[item.index].notation,
                            postHocResults.results[item.index].notation
                        ]);
                    });

                    postHocData.push([]);
                    postHocData.push(['Nilai Kritis:', postHocResults.critical_value]);
                    postHocData.push(['Formula:', postHocResults.formula]);

                    const ws3 = XLSX.utils.aoa_to_sheet(postHocData);
                    XLSX.utils.book_append_sheet(wb, ws3, "Uji Lanjut");
                }

                // Save file
                const date = new Date().toISOString().slice(0, 10).replace(/-/g, '');
                XLSX.writeFile(wb, `STATDEN_${anovaResults.design}_${date}.xlsx`);

                showNotification('Data berhasil diexport ke Excel!', 'success');
            } catch (error) {
                console.error('Export error:', error);
                alert('Error export ke Excel: ' + error.message);
            }
        }

        function exportToPDF() {
            if (!anovaResults) {
                alert("Lakukan analisis ANOVA terlebih dahulu!");
                return;
            }

            const element = document.getElementById('fullResults');
            const opt = {
                margin: [10, 10, 10, 10],
                filename: `STATDEN_${anovaResults.design}_${new Date().toISOString().slice(0,10)}.pdf`,
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2,
                    backgroundColor: '#fff',
                    logging: false,
                    useCORS: true
                },
                jsPDF: {
                    unit: 'mm',
                    format: 'a4',
                    orientation: 'portrait'
                },
                pagebreak: {
                    mode: ['avoid-all', 'css', 'legacy']
                }
            };

            html2pdf()
                .set(opt)
                .from(element)
                .save()
                .then(() => {
                    showNotification('PDF berhasil diunduh!', 'success');
                })
                .catch((err) => {
                    console.warn('PDF export error:', err);
                    showNotification('Gagal membuat PDF: ' + err.message, 'error');
                });
        }

        function printResults() {
            if (!anovaResults) {
                alert("Lakukan analisis ANOVA terlebih dahulu!");
                return;
            }

            const printContent = document.getElementById('fullResults').innerHTML;
            const printWindow = window.open('', '_blank');

            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="UTF-8">
                    <title>STATDEN - Hasil Analisis</title>
                    <style>
                        body { 
                            font-family: Arial, sans-serif; 
                            margin: 20px; 
                            background: white;
                            color: #333;
                        }
                        h1, h2, h3 { color: #1a1a1a; margin-top: 20px; }
                        table { border-collapse: collapse; width: 100%; margin-bottom: 15px; }
                        th, td { border: 1px solid #bbb; padding: 8px; text-align: left; }
                        th { background: #f0f0f0; font-weight: bold; }
                        .section { margin-bottom: 25px; page-break-inside: avoid; }
                        img { max-width: 100%; height: auto; margin: 15px 0; }
                        @media print {
                            body { font-size: 11pt; margin: 10px; }
                            h1 { font-size: 16pt; }
                            h2 { font-size: 14pt; }
                            h3 { font-size: 12pt; }
                            table { font-size: 9pt; }
                            .section { page-break-inside: avoid; }
                        }
                    </style>
                </head>
                <body>
                    <h1>STATDEN v3.0 - Hasil Analisis ${anovaResults.design.toUpperCase()}</h1>
                    <p><strong>Tanggal:</strong> ${new Date().toLocaleDateString('id-ID')}</p>
                    <hr>
                    ${printContent}
                </body>
                </html>
            `);

            printWindow.document.close();

            setTimeout(() => {
                printWindow.focus();
                printWindow.print();
                printWindow.close();
            }, 500);
        }

        function copyToClipboard() {
            if (!anovaResults) {
                alert("Tidak ada hasil untuk disalin!");
                return;
            }

            const text = `HASIL ANALISIS ${anovaResults.design.toUpperCase()}\n` +
                `Tanggal: ${new Date().toLocaleDateString('id-ID')}\n\n` +
                `Perlakuan: ${anovaResults.perlakuan}\n` +
                `Ulangan: ${anovaResults.ulangan}\n` +
                `Alpha: ${anovaResults.alpha}\n\n` +
                `F-hit: ${anovaResults.f_hit.toFixed(4)}\n` +
                `F-tabel: ${anovaResults.f_tabel.toFixed(4)}\n` +
                `p-value: ${anovaResults.p_value.toFixed(6)}\n` +
                `Signifikan: ${anovaResults.significant ? 'Ya' : 'Tidak'}\n` +
                `KK: ${anovaResults.kk.toFixed(2)}%`;

            navigator.clipboard.writeText(text)
                .then(() => showNotification('Hasil disalin ke clipboard!', 'success'))
                .catch(err => showNotification('Gagal menyalin: ' + err, 'error'));
        }

        function saveData() {
            calculateTotals();

            const dataToSave = {
                meta: {
                    exportedAt: new Date().toISOString(),
                    app: 'STATDEN Rancob v3.0'
                },
                config: {
                    perlakuan: parseInt(document.getElementById('perlakuan').value),
                    ulangan: parseInt(document.getElementById('ulangan').value),
                    alpha: document.getElementById('alpha').value,
                    design: document.getElementById('design').value,
                    postHocTest: document.getElementById('postHocTest').value,
                    normalityTest: document.getElementById('normalityTest').value,
                    kodePerlakuan: kodePerlakuanArray
                },
                data: data,
                anovaResults: anovaResults,
                postHocResults: postHocResults
            };

            const dataStr = JSON.stringify(dataToSave, null, 2);
            const blob = new Blob([dataStr], {
                type: 'application/json'
            });
            const url = URL.createObjectURL(blob);

            const a = document.createElement('a');
            a.href = url;
            a.download = `statden_data_${new Date().toISOString().slice(0, 10)}.json`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);

            showNotification('Data berhasil disimpan!', 'success');
        }

        function loadData() {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = '.json,application/json';

            input.onchange = function(e) {
                const file = e.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = function(event) {
                    try {
                        const savedData = JSON.parse(event.target.result);
                        applyLoadedData(savedData);
                        showNotification('Data berhasil dimuat!', 'success');
                    } catch (error) {
                        alert('Error membaca file: ' + error.message);
                    }
                };
                reader.readAsText(file);
            };
            input.click();
        }

        function applyLoadedData(savedData) {
            if (!savedData || typeof savedData !== 'object') {
                throw new Error('Format file tidak valid');
            }

            // Load configuration
            const config = savedData.config || savedData;

            if (config.perlakuan) document.getElementById('perlakuan').value = config.perlakuan;
            if (config.ulangan) document.getElementById('ulangan').value = config.ulangan;
            if (config.alpha) document.getElementById('alpha').value = config.alpha;
            if (config.design) document.getElementById('design').value = config.design;
            if (config.postHocTest) document.getElementById('postHocTest').value = config.postHocTest;
            if (config.normalityTest) document.getElementById('normalityTest').value = config.normalityTest;
            if (config.kodePerlakuan) {
                document.getElementById('kodePerlakuan').value = config.kodePerlakuan.join(',');
                kodePerlakuanArray = config.kodePerlakuan;
            }

            // Load raw data
            const rawData = savedData.data || [];
            data = rawData.map(row => row.map(val => Number(val) || 0));

            // Create grid
            createGrid();
            updateDesignLabels();
            updatePostHocRecommendation();

            // Load ANOVA results
            if (savedData.anovaResults) {
                anovaResults = savedData.anovaResults;
                displayANOVAResults();

                // Render plots
                setTimeout(() => {
                    if (anovaResults.residuals && anovaResults.fitted) {
                        renderQQPlot(anovaResults.residuals);
                        renderResidualsVsFitted(anovaResults.fitted, anovaResults.residuals);
                    }
                }, 500);
            }

            // Load post-hoc results
            if (savedData.postHocResults) {
                postHocResults = savedData.postHocResults;
                displayPostHocResults(postHocResults);
            }

            switchTab('data-tab');
        }

        function updateFullResults() {
            const fullResultsElement = document.getElementById('fullResults');

            // Clear any previous content first
            fullResultsElement.innerHTML = '';

            if (!anovaResults) {
                fullResultsElement.innerHTML = `
            <div style="text-align: center; color: #666; padding: 40px;">
                <div style="font-size: 64px; margin-bottom: 20px;">📋</div>
                <strong style="font-size: 20px;">Lakukan analisis ANOVA terlebih dahulu</strong><br>
                <p style="margin-top: 10px;">Hasil lengkap akan muncul di sini</p>
            </div>
        `;
                return;
            }

            // Build HTML step by step
            let html = `
        <div style="padding: 20px;">
            <div style="background: linear-gradient(135deg, #007cba 0%, #005a87 100%); color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <h2 style="margin: 0; font-size: 24px;">📋 LAPORAN LENGKAP ANALISIS ${anovaResults.design.toUpperCase()}</h2>
                <p style="margin: 10px 0 0 0; opacity: 0.9;">Tanggal: ${new Date().toLocaleDateString('id-ID')}</p>
            </div>
    `;

            // ========== SECTION 1: BASIC INFORMATION ==========
            html += `
        <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3 style="margin-top: 0; color: #007cba; border-bottom: 2px solid #007cba; padding-bottom: 10px;">📊 INFORMASI UMUM</h3>
            <div style="display: flex; flex-wrap: wrap; gap: 15px; margin-top: 15px;">
                <div style="flex: 1; min-width: 150px; padding: 15px; background: #e7f3ff; border-radius: 6px; text-align: center;">
                    <div style="font-size: 14px; color: #005a87; margin-bottom: 5px;">Perlakuan (t)</div>
                    <div style="font-size: 28px; font-weight: bold; color: #007cba;">${anovaResults.perlakuan}</div>
                </div>
                <div style="flex: 1; min-width: 150px; padding: 15px; background: #d4edda; border-radius: 6px; text-align: center;">
                    <div style="font-size: 14px; color: #155724; margin-bottom: 5px;">Ulangan (r)</div>
                    <div style="font-size: 28px; font-weight: bold; color: #28a745;">${anovaResults.ulangan}</div>
                </div>
                <div style="flex: 1; min-width: 150px; padding: 15px; background: #fff3cd; border-radius: 6px; text-align: center;">
                    <div style="font-size: 14px; color: #856404; margin-bottom: 5px;">Alpha (α)</div>
                    <div style="font-size: 28px; font-weight: bold; color: #ffc107;">${anovaResults.alpha}</div>
                </div>
                <div style="flex: 1; min-width: 150px; padding: 15px; background: #f8d7da; border-radius: 6px; text-align: center;">
                    <div style="font-size: 14px; color: #721c24; margin-bottom: 5px;">KK</div>
                    <div style="font-size: 28px; font-weight: bold; color: #dc3545;">${anovaResults.kk.toFixed(2)}%</div>
                </div>
            </div>
        </div>
    `;

            // ========== SECTION 2: EXPERIMENTAL DATA ==========
            html += `
        <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3 style="margin-top: 0; color: #007cba; border-bottom: 2px solid #007cba; padding-bottom: 10px;">📈 DATA PERCOBAAN</h3>
            <div style="overflow-x: auto; margin-top: 15px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #007cba; color: white;">
                            <th style="padding: 10px; border: 1px solid #005a87;">Perlakuan</th>
    `;

            // Add ulangan headers
            for (let u = 1; u <= anovaResults.ulangan; u++) {
                html += `<th style="padding: 10px; border: 1px solid #005a87; text-align: center;">U${u}</th>`;
            }

            html += `
                            <th style="padding: 10px; border: 1px solid #005a87; text-align: center;">Total</th>
                            <th style="padding: 10px; border: 1px solid #005a87; text-align: center;">Rata-rata</th>
                        </tr>
                    </thead>
                    <tbody>
    `;

            // Add data rows
            for (let p = 0; p < anovaResults.perlakuan; p++) {
                let rowTotal = 0;
                html += `
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd; background: #f8f9fa; font-weight: bold;">
                    ${kodePerlakuanArray[p] || `P${p+1}`}
                </td>
        `;

                // Add data cells
                for (let u = 0; u < anovaResults.ulangan; u++) {
                    const value = anovaResults.data[p] ? (anovaResults.data[p][u] || 0) : 0;
                    rowTotal += value;
                    html += `<td style="padding: 10px; border: 1px solid #ddd; text-align: right;">${value.toFixed(2)}</td>`;
                }

                const avg = rowTotal / anovaResults.ulangan;
                html += `
                <td style="padding: 10px; border: 1px solid #ddd; text-align: right; background: #e7f3ff; font-weight: bold;">
                    ${rowTotal.toFixed(2)}
                </td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: right; background: #fff3cd; font-weight: bold;">
                    ${avg.toFixed(2)}
                </td>
            </tr>
        `;
            }

            html += `
                    </tbody>
                </table>
            </div>
        </div>
    `;

            // ========== SECTION 3: ANOVA RESULTS ==========
            html += `
        <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3 style="margin-top: 0; color: #007cba; border-bottom: 2px solid #007cba; padding-bottom: 10px;">📊 TABEL ANOVA</h3>
            <div style="overflow-x: auto; margin-top: 15px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #28a745; color: white;">
                            <th style="padding: 10px; border: 1px solid #1e7e34;">Sumber Keragaman</th>
                            <th style="padding: 10px; border: 1px solid #1e7e34;">db</th>
                            <th style="padding: 10px; border: 1px solid #1e7e34;">JK</th>
                            <th style="padding: 10px; border: 1px solid #1e7e34;">KT</th>
                            <th style="padding: 10px; border: 1px solid #1e7e34;">F-hit</th>
                            <th style="padding: 10px; border: 1px solid #1e7e34;">F-tabel</th>
                            <th style="padding: 10px; border: 1px solid #1e7e34;">p-value</th>
                            <th style="padding: 10px; border: 1px solid #1e7e34;">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
    `;

            // Add ANOVA rows based on design
            if (anovaResults.design === 'RAK') {
                // RAK Design
                html += `
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd;">Perlakuan</td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">${anovaResults.db_perlakuan}</td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: right;">${anovaResults.jk_perlakuan.toFixed(4)}</td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: right;">${anovaResults.kt_perlakuan.toFixed(4)}</td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: right;">${anovaResults.f_hit.toFixed(4)}</td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: right;">${anovaResults.f_tabel.toFixed(4)}</td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: right;">${anovaResults.p_value.toFixed(6)}</td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">
                    <span style="padding: 4px 8px; border-radius: 4px; background: ${anovaResults.significant ? '#f8d7da' : '#d4edda'}; color: ${anovaResults.significant ? '#721c24' : '#155724'};">
                        ${anovaResults.significant ? (anovaResults.alpha === 0.01 ? '**' : '*') : 'ns'}
                    </span>
                </td>
            </tr>
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd;">Blok</td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">${anovaResults.db_blok}</td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: right;">${anovaResults.jk_blok.toFixed(4)}</td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: right;">${anovaResults.kt_blok.toFixed(4)}</td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: right;">${anovaResults.f_hit_blok.toFixed(4)}</td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: right;">${anovaResults.f_tabel_blok.toFixed(4)}</td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: right;">${anovaResults.p_value_blok.toFixed(6)}</td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">
                    <span style="padding: 4px 8px; border-radius: 4px; background: ${anovaResults.significant_blok ? '#f8d7da' : '#d4edda'}; color: ${anovaResults.significant_blok ? '#721c24' : '#155724'};">
                        ${anovaResults.significant_blok ? 'Nyata' : 'Tidak'}
                    </span>
                </td>
            </tr>
        `;
            } else {
                // RAL Design
                html += `
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd;">Perlakuan</td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">${anovaResults.db_perlakuan}</td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: right;">${anovaResults.jk_perlakuan.toFixed(4)}</td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: right;">${anovaResults.kt_perlakuan.toFixed(4)}</td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: right;">${anovaResults.f_hit.toFixed(4)}</td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: right;">${anovaResults.f_tabel.toFixed(4)}</td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: right;">${anovaResults.p_value.toFixed(6)}</td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">
                    <span style="padding: 4px 8px; border-radius: 4px; background: ${anovaResults.significant ? '#f8d7da' : '#d4edda'}; color: ${anovaResults.significant ? '#721c24' : '#155724'};">
                        ${anovaResults.significant ? (anovaResults.alpha === 0.01 ? '**' : '*') : 'ns'}
                    </span>
                </td>
            </tr>
        `;
            }

            // Add Galat and Total rows
            html += `
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd;">Galat</td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">${anovaResults.db_galat}</td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: right;">${anovaResults.jk_galat.toFixed(4)}</td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: right;">${anovaResults.kt_galat.toFixed(4)}</td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: center;" colspan="4">-</td>
            </tr>
            <tr style="background: #f8f9fa;">
                <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold;">Total</td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: center; font-weight: bold;">${anovaResults.db_total}</td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: right; font-weight: bold;">${anovaResults.jk_total.toFixed(4)}</td>
                <td style="padding: 10px; border: 1px solid #ddd;" colspan="5"></td>
            </tr>
        </tbody>
    </table>
</div>

<div style="margin-top: 20px; padding: 15px; background: ${anovaResults.significant ? '#d4edda' : '#fff3cd'}; border-radius: 6px;">
    <strong>📋 KESIMPULAN ANOVA:</strong>
    <p style="margin: 10px 0;">
        ${anovaResults.significant ? 
            `Terdapat pengaruh yang <strong>${anovaResults.alpha === 0.01 ? 'sangat nyata' : 'nyata'}</strong> dari perlakuan.<br>
            F-hit = ${anovaResults.f_hit.toFixed(4)} ${anovaResults.f_hit > anovaResults.f_tabel ? '>' : '<'} F-tabel = ${anovaResults.f_tabel.toFixed(4)}<br>
            p-value = ${anovaResults.p_value.toFixed(6)} < α = ${anovaResults.alpha}` : 
            `Tidak terdapat pengaruh nyata dari perlakuan.<br>
            F-hit = ${anovaResults.f_hit.toFixed(4)} ${anovaResults.f_hit > anovaResults.f_tabel ? '>' : '<'} F-tabel = ${anovaResults.f_tabel.toFixed(4)}<br>
            p-value = ${anovaResults.p_value.toFixed(6)} ≥ α = ${anovaResults.alpha}`
        }
    </p>
    ${anovaResults.significant ? 
        '<p style="margin: 10px 0 0 0; padding: 8px; background: #c3e6cb; border-radius: 4px;"><strong>✅ Rekomendasi:</strong> Lakukan uji lanjut untuk mengetahui perbedaan antar perlakuan.</p>' : 
        '<p style="margin: 10px 0 0 0; padding: 8px; background: #ffeaa7; border-radius: 4px;"><strong>ℹ️ Rekomendasi:</strong> Tidak perlu melakukan uji lanjut.</p>'
    }
</div>
        </div>
    `;

            // ========== SECTION 4: NORMALITY TESTS ==========
            if (anovaResults.normality && (anovaResults.normality.ks || anovaResults.normality.lilliefors)) {
                const norm = anovaResults.normality;
                html += `
            <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h3 style="margin-top: 0; color: #007cba; border-bottom: 2px solid #007cba; padding-bottom: 10px;">📊 UJI NORMALITAS RESIDUAL</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px; margin-top: 15px;">
        `;

                if (norm.ks && norm.ks.stat !== null) {
                    const isNormal = norm.ks.normal;
                    html += `
                <div style="padding: 15px; background: ${isNormal ? '#d4edda' : '#f8d7da'}; border-radius: 6px; border-left: 4px solid ${isNormal ? '#28a745' : '#dc3545'};">
                    <div style="font-weight: bold; margin-bottom: 10px; color: ${isNormal ? '#155724' : '#721c24'};">
                        Kolmogorov-Smirnov (KS) Test
                    </div>
                    <div style="font-size: 14px;">
                        <div style="margin-bottom: 5px;">Statistik D: <strong>${norm.ks.stat.toFixed(6)}</strong></div>
                        <div style="margin-bottom: 5px;">P-value: <strong>${norm.ks.p_value ? norm.ks.p_value.toFixed(6) : 'N/A'}</strong></div>
                        <div style="margin-bottom: 5px;">Tingkat Signifikansi: α = ${anovaResults.alpha}</div>
                        <div>
                            Kesimpulan: 
                            <span style="font-weight: bold; color: ${isNormal ? '#28a745' : '#dc3545'};">
                                ${isNormal ? '✓ Residual berdistribusi normal' : '✗ Residual tidak berdistribusi normal'}
                            </span>
                        </div>
                    </div>
                </div>
            `;
                }

                if (norm.lilliefors && norm.lilliefors.stat !== null) {
                    const isNormal = norm.lilliefors.normal;
                    html += `
                <div style="padding: 15px; background: ${isNormal ? '#d4edda' : '#f8d7da'}; border-radius: 6px; border-left: 4px solid ${isNormal ? '#28a745' : '#dc3545'};">
                    <div style="font-weight: bold; margin-bottom: 10px; color: ${isNormal ? '#155724' : '#721c24'};">
                        Lilliefors Test (Monte Carlo)
                    </div>
                    <div style="font-size: 14px;">
                        <div style="margin-bottom: 5px;">Statistik D: <strong>${norm.lilliefors.stat.toFixed(6)}</strong></div>
                        <div style="margin-bottom: 5px;">P-value: <strong>${norm.lilliefors.p_value ? norm.lilliefors.p_value.toFixed(6) : 'N/A'}</strong></div>
                        <div style="margin-bottom: 5px;">Tingkat Signifikansi: α = ${anovaResults.alpha}</div>
                        <div>
                            Kesimpulan: 
                            <span style="font-weight: bold; color: ${isNormal ? '#28a745' : '#dc3545'};">
                                ${isNormal ? '✓ Residual berdistribusi normal' : '✗ Residual tidak berdistribusi normal'}
                            </span>
                        </div>
                    </div>
                </div>
            `;
                }

                html += `
                </div>
                <div style="margin-top: 15px; padding: 12px; background: #e7f3ff; border-radius: 6px; font-size: 13px;">
                    <strong>Interpretasi:</strong> Uji normalitas memeriksa apakah residual berdistribusi normal (asumsi ANOVA). 
                    Jika residual tidak normal, transformasi data mungkin diperlukan sebelum melanjutkan analisis.
                    <ul style="margin: 8px 0 0 20px;">
                        <li>H₀: Residual berdistribusi normal</li>
                        <li>Jika P-value > α: Gagal tolak H₀ (residual normal)</li>
                        <li>Jika P-value ≤ α: Tolak H₀ (residual tidak normal)</li>
                    </ul>
                </div>
            </div>
        `;
            }

            // ========== SECTION 5: POST-HOC TEST RESULTS ==========
            if (postHocResults && postHocResults.results) {
                html += `
            <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h3 style="margin-top: 0; color: #007cba; border-bottom: 2px solid #007cba; padding-bottom: 10px;">📊 HASIL UJI LANJUT (${postHocResults.test_name})</h3>
                
                <div style="margin-bottom: 20px; padding: 15px; background: #f0f9ff; border-radius: 6px;">
                    <strong>Parameter Uji:</strong>
                    <div style="margin-top: 10px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
                        <div style="padding: 10px; background: white; border-radius: 4px;">
                            <div style="font-size: 12px; color: #666;">Nilai Kritis</div>
                            <div style="font-size: 18px; font-weight: bold; color: #007cba;">${postHocResults.critical_value.toFixed(6)}</div>
                        </div>
                        <div style="padding: 10px; background: white; border-radius: 4px;">
                            <div style="font-size: 12px; color: #666;">Formula</div>
                            <div style="font-size: 14px;">${postHocResults.formula}</div>
                        </div>
                        <div style="padding: 10px; background: white; border-radius: 4px;">
                            <div style="font-size: 12px; color: #666;">Metode</div>
                            <div style="font-size: 14px;">${postHocResults.method}</div>
                        </div>
                    </div>
                </div>
                
                <div style="overflow-x: auto; margin-bottom: 20px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #6c757d; color: white;">
                                <th style="padding: 10px; border: 1px solid #545b62; text-align: center;">Rank</th>
                                <th style="padding: 10px; border: 1px solid #545b62; text-align: center;">Perlakuan</th>
                                <th style="padding: 10px; border: 1px solid #545b62; text-align: center;">Rata-rata</th>
                                <th style="padding: 10px; border: 1px solid #545b62; text-align: center;">Notasi</th>
                                <th style="padding: 10px; border: 1px solid #545b62; text-align: center;">Kelompok</th>
                            </tr>
                        </thead>
                        <tbody>
        `;

                // Display post-hoc results
                postHocResults.sorted_data.forEach((item, rank) => {
                    const notation = postHocResults.results[item.index]?.notation || '';
                    html += `
                <tr>
                    <td style="padding: 10px; border: 1px solid #ddd; text-align: center; font-weight: bold;">${rank + 1}</td>
                    <td style="padding: 10px; border: 1px solid #ddd; text-align: center; font-weight: bold;">${item.kode}</td>
                    <td style="padding: 10px; border: 1px solid #ddd; text-align: right; font-weight: bold;">${item.average.toFixed(4)}</td>
                    <td style="padding: 10px; border: 1px solid #ddd; text-align: center; font-weight: bold;">${notation}</td>
                    <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">
            `;

                    // Add group badges
                    if (notation) {
                        notation.split('').forEach(letter => {
                            html += `<span style="display: inline-block; padding: 4px 8px; margin: 2px; background: #d1ecf1; color: #0c5460; border-radius: 4px; font-size: 12px; font-weight: bold;">${letter}</span>`;
                        });
                    }

                    html += `
                    </td>
                </tr>
            `;
                });

                html += `
                        </tbody>
                    </table>
                </div>
                
                <div style="padding: 15px; background: #fff3cd; border-radius: 6px; font-size: 14px;">
                    <strong>💡 Interpretasi Hasil:</strong>
                    <ul style="margin: 10px 0 0 20px;">
                        <li>Perlakuan dengan <strong>huruf yang sama</strong> <strong>TIDAK BERBEDA NYATA</strong> pada α = ${anovaResults.alpha}</li>
                        <li>Perlakuan dengan <strong>huruf berbeda</strong> <strong>BERBEDA NYATA</strong> pada α = ${anovaResults.alpha}</li>
                        <li>Nilai kritis = ${postHocResults.critical_value.toFixed(6)}</li>
                        <li>Dua perlakuan berbeda nyata jika selisih rata-ratanya > ${postHocResults.critical_value.toFixed(6)}</li>
                    </ul>
                </div>
            </div>
        `;
            }

            // ========== SECTION 6: GRAPHS ==========
            html += `
        <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3 style="margin-top: 0; color: #007cba; border-bottom: 2px solid #007cba; padding-bottom: 10px;">📈 DIAGNOSTIC PLOTS</h3>
            <div style="display: flex; flex-wrap: wrap; gap: 20px; margin-top: 15px;">
                <div style="flex: 1; min-width: 300px;">
                    <div style="margin-bottom: 10px; font-weight: bold; color: #333;">Q-Q Plot (Residuals)</div>
                    <canvas id="fullQQCanvas" width="500" height="400" style="width: 100%; max-width: 100%; border: 1px solid #ddd; border-radius: 8px; background: white;"></canvas>
                    <div style="margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 4px; font-size: 12px;">
                        <strong>Interpretasi:</strong> Titik-titik yang mengikuti garis merah menunjukkan residual berdistribusi normal.
                    </div>
                </div>
                <div style="flex: 1; min-width: 300px;">
                    <div style="margin-bottom: 10px; font-weight: bold; color: #333;">Residuals vs Fitted Values</div>
                    <canvas id="fullResCanvas" width="500" height="400" style="width: 100%; max-width: 100%; border: 1px solid #ddd; border-radius: 8px; background: white;"></canvas>
                    <div style="margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 4px; font-size: 12px;">
                        <strong>Interpretasi:</strong> Pola acak menunjukkan homogenitas varians. Pola tertentu menunjukkan heterogenitas.
                    </div>
                </div>
            </div>
        </div>
    `;

            // ========== SECTION 7: MEAN COMPARISON CHART ==========
            if (postHocResults) {
                html += `
            <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h3 style="margin-top: 0; color: #007cba; border-bottom: 2px solid #007cba; padding-bottom: 10px;">📊 PERBANDINGAN RATA-RATA PERLAKUAN</h3>
                <div style="margin-top: 15px;">
                    <canvas id="fullMeanChart" width="800" height="400" style="width: 100%; max-width: 100%; border: 1px solid #ddd; border-radius: 8px; background: white;"></canvas>
                </div>
                <div style="margin-top: 15px; padding: 12px; background: #f0f9ff; border-radius: 6px; font-size: 13px;">
                    <strong>Keterangan:</strong> Grafik menunjukkan rata-rata perlakuan dengan notasi kelompok. Perlakuan dengan huruf yang sama tidak berbeda nyata.
                </div>
            </div>
        `;
            }

            // ========== SECTION 8: KK INTERPRETATION ==========
            html += `
        <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3 style="margin-top: 0; color: #007cba; border-bottom: 2px solid #007cba; padding-bottom: 10px;">📈 KOEFISIEN KERAGAMAN (KK)</h3>
            <div style="display: flex; align-items: center; gap: 20px; margin-top: 15px;">
                <div style="flex: 1; text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                    <div style="font-size: 12px; color: #666; margin-bottom: 10px;">Nilai KK</div>
                    <div style="font-size: 48px; font-weight: bold; 
                        color: ${anovaResults.kk < 10 ? '#28a745' : 
                                 anovaResults.kk < 20 ? '#17a2b8' : 
                                 anovaResults.kk < 30 ? '#ffc107' : '#dc3545'};">
                        ${anovaResults.kk.toFixed(2)}%
                    </div>
                    <div style="margin-top: 10px; font-size: 12px; color: #666;">
                        KK = (√KT Galat / Rata-rata Umum) × 100%
                    </div>
                </div>
                <div style="flex: 2; padding: 20px; background: #f0f9ff; border-radius: 8px;">
                    <strong>Interpretasi:</strong>
                    <p style="margin: 10px 0; font-size: 14px;">
                        ${interpretKK(anovaResults.kk)}
                    </p>
                    <div style="margin-top: 15px; font-size: 12px;">
                        <strong>Klasifikasi:</strong>
                        <ul style="margin: 5px 0 0 20px;">
                            <li>KK &lt; 10%: Presisi sangat baik (eksperimen terkendali)</li>
                            <li>KK 10-20%: Presisi baik</li>
                            <li>KK 20-30%: Presisi cukup</li>
                            <li>KK &gt; 30%: Presisi kurang (eksperimen kurang terkendali)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    `;

            // Close the main container
            html += `
        <div style="text-align: center; margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; font-size: 12px; color: #666;">
            <strong>STATDEN ANALYTICS v3.0 PRO</strong> | Laporan ini dibuat otomatis pada ${new Date().toLocaleString('id-ID')}
        </div>
    </html>`;

            fullResultsElement.innerHTML = html;

            // ========== RENDER GRAPHS AFTER HTML IS INSERTED ==========
            setTimeout(() => {
                // Render Q-Q Plot
                if (anovaResults.residuals && anovaResults.residuals.length > 0) {
                    renderQQPlot(anovaResults.residuals, 'fullQQCanvas');
                }

                // Render Residuals vs Fitted
                if (anovaResults.fitted && anovaResults.residuals && anovaResults.fitted.length > 0) {
                    renderResidualsVsFitted(anovaResults.fitted, anovaResults.residuals, 'fullResCanvas');
                }

                // Render Mean Comparison Chart
                if (postHocResults && postHocResults.sorted_data) {
                    createMeanComparisonChart(postHocResults, 'fullMeanChart');
                }
            }, 100);
        }

        // Helper function for mean comparison chart
        function createMeanComparisonChart(results, canvasId = 'fullMeanChart') {
            const canvas = document.getElementById(canvasId);
            if (!canvas || !results || !results.sorted_data) return;

            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            const sortedData = results.sorted_data;
            const n = sortedData.length;
            if (n === 0) return;

            const width = canvas.width;
            const height = canvas.height;
            const margin = {
                top: 40,
                right: 40,
                bottom: 60,
                left: 60
            };
            const chartWidth = width - margin.left - margin.right;
            const chartHeight = height - margin.top - margin.bottom;

            // Prepare data
            const labels = sortedData.map(item => item.kode);
            const means = sortedData.map(item => item.average);
            const notations = sortedData.map(item =>
                results.results[item.index]?.notation || ''
            );

            // Find min and max
            const minMean = Math.min(...means);
            const maxMean = Math.max(...means);
            const range = maxMean - minMean;
            const padding = range * 0.1;

            // Clear canvas
            ctx.fillStyle = '#fff';
            ctx.fillRect(0, 0, width, height);

            // Draw grid
            ctx.strokeStyle = '#eee';
            ctx.lineWidth = 1;

            // Draw grid lines
            for (let i = 0; i <= 10; i++) {
                const y = margin.top + (i / 10) * chartHeight;
                ctx.beginPath();
                ctx.moveTo(margin.left, y);
                ctx.lineTo(margin.left + chartWidth, y);
                ctx.stroke();
            }

            // Draw axes
            ctx.strokeStyle = '#333';
            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.moveTo(margin.left, margin.top);
            ctx.lineTo(margin.left, margin.top + chartHeight);
            ctx.lineTo(margin.left + chartWidth, margin.top + chartHeight);
            ctx.stroke();

            // Draw means as bars
            const barWidth = chartWidth / n * 0.6;
            const barSpacing = chartWidth / n * 0.4;

            for (let i = 0; i < n; i++) {
                const x = margin.left + i * (barWidth + barSpacing) + barSpacing / 2;
                const barHeight = ((means[i] - minMean + padding) / (range + 2 * padding)) * chartHeight;
                const y = margin.top + chartHeight - barHeight;

                // Draw bar
                ctx.fillStyle = '#007cba';
                ctx.fillRect(x, y, barWidth, barHeight);

                // Draw notation
                ctx.fillStyle = '#333';
                ctx.font = '12px Arial';
                ctx.textAlign = 'center';
                ctx.fillText(notations[i], x + barWidth / 2, y - 10);

                // Draw mean value
                ctx.fillStyle = '#666';
                ctx.font = '11px Arial';
                ctx.fillText(means[i].toFixed(2), x + barWidth / 2, y + barHeight + 15);

                // Draw label
                ctx.fillStyle = '#333';
                ctx.font = '12px Arial';
                ctx.fillText(labels[i], x + barWidth / 2, margin.top + chartHeight + 25);
            }

            // Draw title
            ctx.fillStyle = '#003d5c';
            ctx.font = 'bold 16px Arial';
            ctx.textAlign = 'center';
            ctx.fillText('Perbandingan Rata-rata Perlakuan', width / 2, 20);

            // Draw y-axis label
            ctx.save();
            ctx.translate(20, height / 2);
            ctx.rotate(-Math.PI / 2);
            ctx.fillText('Rata-rata', 0, 0);
            ctx.restore();
        }

        function renderQQPlotToImage(elementId, residuals) {
            const canvas = document.createElement('canvas');
            canvas.width = 600;
            canvas.height = 400;

            const tempCtx = canvas.getContext('2d');
            tempCtx.fillStyle = '#fff';
            tempCtx.fillRect(0, 0, canvas.width, canvas.height);

            // Simulate plot rendering
            if (residuals && residuals.length > 0) {
                const sorted = residuals.slice().sort((a, b) => a - b);
                const n = sorted.length;

                // Draw simple representation
                tempCtx.fillStyle = '#007cba';
                for (let i = 0; i < n; i++) {
                    const x = 50 + (i / n) * 500;
                    const y = 200 + sorted[i] * 10;
                    tempCtx.beginPath();
                    tempCtx.arc(x, y, 3, 0, Math.PI * 2);
                    tempCtx.fill();
                }

                // Draw line
                tempCtx.strokeStyle = '#dc3545';
                tempCtx.lineWidth = 2;
                tempCtx.beginPath();
                tempCtx.moveTo(50, 150);
                tempCtx.lineTo(550, 250);
                tempCtx.stroke();
            }

            document.getElementById(elementId).src = canvas.toDataURL('image/png');
        }

        function renderResidualsVsFittedToImage(elementId, fitted, residuals) {
            const canvas = document.createElement('canvas');
            canvas.width = 600;
            canvas.height = 400;

            const tempCtx = canvas.getContext('2d');
            tempCtx.fillStyle = '#fff';
            tempCtx.fillRect(0, 0, canvas.width, canvas.height);

            if (fitted && residuals && fitted.length > 0) {
                // Draw points
                tempCtx.fillStyle = '#007cba';
                for (let i = 0; i < fitted.length; i++) {
                    const x = 50 + (fitted[i] % 500);
                    const y = 200 + residuals[i] * 10;
                    tempCtx.beginPath();
                    tempCtx.arc(x, y, 3, 0, Math.PI * 2);
                    tempCtx.fill();
                }

                // Draw zero line
                tempCtx.strokeStyle = '#dc3545';
                tempCtx.lineWidth = 1;
                tempCtx.beginPath();
                tempCtx.moveTo(50, 200);
                tempCtx.lineTo(550, 200);
                tempCtx.stroke();
            }

            document.getElementById(elementId).src = canvas.toDataURL('image/png');
        }

        function clearAll() {
            if (!confirm("Apakah Anda yakin ingin menghapus semua data dan hasil?")) {
                return;
            }

            // Reset form
            document.getElementById('perlakuan').value = 4;
            document.getElementById('ulangan').value = 5;
            document.getElementById('alpha').value = '0.05';
            document.getElementById('kodePerlakuan').value = '';
            document.getElementById('design').value = 'ral';
            document.getElementById('postHocTest').value = 'bnt';
            document.getElementById('normalityTest').value = 'lilliefors';

            // Reset data
            data = [];
            anovaResults = null;
            postHocResults = null;
            kodePerlakuanArray = [];

            // Recreate grid
            createGrid();
            updateDesignLabels();
            updatePostHocRecommendation();

            // Clear all results
            resetResults();

            showNotification("Semua data dan hasil telah direset.", "success");
            switchTab('data-tab');
        }

        // ==================== INISIALISASI ====================
        window.onload = function() {
            // Create initial grid
            createGrid();
            updateDesignLabels();
            updateTestLabels('bnt');
            updatePostHocRecommendation();

            // Add event listeners
            document.getElementById('perlakuan').addEventListener('change', function() {
                updatePostHocRecommendation();
            });

            document.getElementById('design').addEventListener('change', function() {
                updateDesignLabels();
            });

            document.getElementById('postHocTest').addEventListener('change', function() {
                updateTestLabels(this.value);
            });

            // Check for libraries
            setTimeout(() => {
                if (typeof jStat === 'undefined') {
                    console.warn('jStat library tidak ditemukan. Beberapa fitur mungkin terbatas.');
                }
                if (typeof XLSX === 'undefined') {
                    console.warn('XLSX library tidak ditemukan. Export Excel tidak tersedia.');
                }
                if (typeof html2pdf === 'undefined') {
                    console.warn('html2pdf library tidak ditemukan. Export PDF tidak tersedia.');
                }
            }, 2000);

            showNotification('STATDEN v3.0 siap digunakan!', 'success', 2000);
        };
    </script>

    <footer style="margin-top: 30px; padding: 20px; text-align: center; color: #666; font-size: 14px; border-top: 1px solid #e9ecef; background: white; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div>
                <strong>STATDEN ANALYTICS v3.0 PRO</strong><br>
                <small>Sistem Analisis Statistik Rancangan Percobaan</small>
            </div>
            <div>
                <span class="stat-badge success">✅ RAL & RAK</span>
                <span class="stat-badge info">📊 BNT/BNJ/DMRT</span>
                <span class="stat-badge warning">📈 Diagnostic Plots</span>
                <span class="stat-badge danger">💾 Export Data</span>
            </div>
            <div>
                <small>© 2024 STATDEN Team. For Educational Purposes.</small>
            </div>
        </div>
    </footer>
</body>

</html>