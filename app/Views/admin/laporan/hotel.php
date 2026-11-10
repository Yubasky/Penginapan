<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title><?= esc($title) ?></title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', Arial, sans-serif;
            color: #1a1a1a;
            margin: 0;
            padding: 2rem;
            background: #fdfdfd;
            font-size: 11pt;
        }
        .no-print {
            background: #0f766e;
            color: #fff;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-print {
            background: #f59e0b;
            color: #1a1a1a;
            border: 0;
            padding: .6rem 1.2rem;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-back {
            color: #fff;
            text-decoration: none;
            font-size: 14px;
        }
        .kop {
            text-align: center;
            border-bottom: 3px double #1a1a1a;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }
        .kop h1 {
            margin: 0;
            font-size: 20pt;
            letter-spacing: 1px;
            color: #0f766e;
        }
        .kop p {
            margin: .2rem 0;
            font-size: 10pt;
            color: #555;
        }
        .judul-laporan {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .judul-laporan h2 {
            margin: 0 0 .3rem;
            font-size: 14pt;
            text-transform: uppercase;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .stat-card {
            border: 1px solid #ddd;
            padding: .8rem;
            border-radius: 6px;
            text-align: center;
            background: #fafafa;
        }
        .stat-card .val {
            font-size: 16pt;
            font-weight: bold;
            color: #0f766e;
        }
        .stat-card .lbl {
            font-size: 9pt;
            color: #666;
            margin-top: .2rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background: #f0fdf4;
            font-weight: bold;
            color: #064e3b;
        }
        tr:nth-child(even) {
            background: #fafafa;
        }
        .text-center { text-align: center; }
        .ttd-box {
            margin-top: 3rem;
            display: flex;
            justify-content: flex-end;
            page-break-inside: avoid;
        }
        .ttd {
            text-align: center;
            width: 250px;
        }
        .ttd .space {
            height: 70px;
        }

        @media print {
            .no-print { display: none !important; }
            body { padding: 0; background: #fff; }
            th { background: #eee !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            @page {
                size: A4 portrait;
                margin: 1.5cm;
            }
        }
    </style>
</head>
<body>

<div class="no-print">
    <div>
        <strong>Dokumen Siap Cetak</strong>
        <span style="opacity:.8;font-size:12px;margin-left:10px">Gunakan opsi "Save as PDF" di menu cetak browser jika ingin menyimpan PDF.</span>
    </div>
    <div>
        <button class="btn-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
        <a class="btn-back" href="<?= site_url('admin/hotel') ?>" style="margin-left:15px">← Kembali</a>
    </div>
</div>

<div class="kop">
    <h1>SINGGAH MEDAN</h1>
    <p>Sistem Informasi Direktori &amp; Panduan Penginapan Kota Medan</p>
    <p>Alamat: Jl. Balai Kota No. 1, Medan, Sumatera Utara · Email: info@singgahmedan.com</p>
</div>

<div class="judul-laporan">
    <h2>Laporan Rekapitulasi Data Penginapan &amp; Hotel</h2>
    <div style="font-size:10pt;color:#666">Dicetak pada: <?= esc($tgl) ?></div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="val"><?= (int) $stats['total'] ?></div>
        <div class="lbl">Total Penginapan</div>
    </div>
    <div class="stat-card">
        <div class="val"><?= (int) $stats['sarapan'] ?></div>
        <div class="lbl">Dengan Sarapan</div>
    </div>
    <div class="stat-card">
        <div class="val"><?= (int) $stats['terkoordinat'] ?></div>
        <div class="lbl">Terpetakan (Koordinat)</div>
    </div>
    <div class="stat-card">
        <div class="val"><?= (int) $stats['berating'] ?></div>
        <div class="lbl">Memiliki Ulasan &amp; Rating</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th class="text-center" style="width:35px">No</th>
            <th>Nama Penginapan</th>
            <th>Kecamatan</th>
            <th class="text-center" style="width:55px">Rating</th>
            <th class="text-center" style="width:65px">Ulasan</th>
            <th class="text-center" style="width:75px">Sarapan</th>
            <th>Alamat</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($hotels as $i => $h): ?>
            <tr>
                <td class="text-center"><?= $i + 1 ?></td>
                <td><strong><?= esc($h['nama']) ?></strong></td>
                <td><?= esc($h['kecamatan'] ?: '-') ?></td>
                <td class="text-center"><?= $h['rating'] !== null ? '★ ' . number_format((float) $h['rating'], 1) : '-' ?></td>
                <td class="text-center"><?= $h['jumlah_ulasan'] !== null ? number_format((int) $h['jumlah_ulasan'], 0, ',', '.') : '-' ?></td>
                <td class="text-center"><?= esc($h['sarapan']) ?></td>
                <td style="font-size:8.5pt"><?= esc($h['alamat']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="ttd-box">
    <div class="ttd">
        <p>Medan, <?= date('d F Y') ?><br>Mengetahui,<br><strong>Pengelola Sistem Singgah Medan</strong></p>
        <div class="space"></div>
        <p><strong>( Administrator Singgah Medan )</strong></p>
    </div>
</div>

</body>
</html>

