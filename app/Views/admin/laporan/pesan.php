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
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-baru { background: #fee2e2; color: #991b1b; }
        .badge-diproses { background: #fef3c7; color: #92400e; }
        .badge-selesai { background: #dcfce7; color: #166534; }

        @media print {
            .no-print { display: none !important; }
            body { padding: 0; background: #fff; }
            th { background: #eee !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            @page {
                size: A4 landscape;
                margin: 1.2cm;
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
        <a class="btn-back" href="<?= site_url('admin/pesan') ?>" style="margin-left:15px">← Kembali</a>
    </div>
</div>

<div class="kop">
    <h1>SINGGAH MEDAN</h1>
    <p>Sistem Informasi Direktori &amp; Panduan Penginapan Kota Medan</p>
    <p>Alamat: Jl. Balai Kota No. 1, Medan, Sumatera Utara · Email: info@singgahmedan.com</p>
</div>

<div class="judul-laporan">
    <h2>Laporan Rekapitulasi Pesan &amp; Reservasi Tamu</h2>
    <div style="font-size:10pt;color:#666">Dicetak pada: <?= esc($tgl) ?></div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="val"><?= (int) $stats['total'] ?></div>
        <div class="lbl">Total Pesan / Tanya</div>
    </div>
    <div class="stat-card">
        <div class="val" style="color:#b91c1c"><?= (int) $stats['baru'] ?></div>
        <div class="lbl">Status Baru</div>
    </div>
    <div class="stat-card">
        <div class="val" style="color:#b45309"><?= (int) $stats['diproses'] ?></div>
        <div class="lbl">Sedang Diproses</div>
    </div>
    <div class="stat-card">
        <div class="val" style="color:#15803d"><?= (int) $stats['selesai'] ?></div>
        <div class="lbl">Selesai Ditangani</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th class="text-center" style="width:30px">No</th>
            <th style="width:75px">Tanggal</th>
            <th>Nama Tamu</th>
            <th>Kontak (Email / Telp)</th>
            <th>Hotel Tujuan</th>
            <th style="width:130px">Rencana Menginap</th>
            <th class="text-center" style="width:65px">Status</th>
            <th>Isi Pesan Tamu</th>
            <th>Balasan Admin</th>
        </tr>
    </thead>
    <tbody>
        <?php if (! $pesan): ?>
            <tr>
                <td colspan="9" class="text-center" style="padding:1.5rem;color:#777">Belum ada data pesan masuk.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($pesan as $i => $p): ?>
                <tr>
                    <td class="text-center"><?= $i + 1 ?></td>
                    <td style="font-size:8.5pt"><?= date('d/m/Y', strtotime($p['created_at'])) ?></td>
                    <td><strong><?= esc($p['nama']) ?></strong></td>
                    <td style="font-size:8.5pt">
                        <?= esc($p['email']) ?>
                        <?= $p['telepon'] ? '<br>' . esc($p['telepon']) : '' ?>
                    </td>
                    <td><strong><?= esc($p['hotel_nama'] ?? '-') ?></strong></td>
                    <td style="font-size:8.5pt">
                        <?= $p['tgl_checkin'] ? esc($p['tgl_checkin']) : '-' ?> s/d <?= $p['tgl_checkout'] ? esc($p['tgl_checkout']) : '-' ?>
                        <?= $p['jumlah_tamu'] ? '<br>(' . (int) $p['jumlah_tamu'] . ' tamu)' : '' ?>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-<?= esc($p['status']) ?>"><?= esc($p['status']) ?></span>
                    </td>
                    <td style="font-size:8.5pt"><?= nl2br(esc($p['pesan'] ?: '-')) ?></td>
                    <td style="font-size:8.5pt"><?= nl2br(esc($p['balasan_admin'] ?: '-')) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
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

