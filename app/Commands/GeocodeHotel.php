<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/** Mencari koordinat hotel lewat OpenStreetMap Nominatim (1 permintaan/detik sesuai aturan layanan). */
class GeocodeHotel extends BaseCommand
{
    protected $group       = 'Singgah';
    protected $name        = 'hotel:geocode';
    protected $description = 'Isi latitude/longitude hotel untuk halaman peta. Opsi: --semua (ulangi yang sudah terisi).';

    public function run(array $params)
    {
        $db    = db_connect();
        $semua = CLI::getOption('semua') !== null;
        $b     = $db->table('hotels')->select('id, nama, alamat')->orderBy('peringkat');
        if (! $semua) {
            $b->where('latitude IS NULL', null, false);
        }
        $hotels = $b->get()->getResultArray();
        CLI::write(count($hotels) . ' hotel diproses (sekitar ' . ceil(count($hotels) * 1.5 / 60) . ' menit). Ctrl+C untuk berhenti; hasil tersimpan per hotel.');

        $client = \Config\Services::curlrequest(['timeout' => 15, 'http_errors' => false]);
        $ok     = 0;
        foreach ($hotels as $i => $h) {
            $alamat  = trim(preg_replace('/,?\s*Kec\..*$/i', '', (string) $h['alamat']));
            // nama jalan saja (tanpa nomor), mis. "Jl. Pemuda No. 3" -> "Jalan Pemuda"
            $jalan   = trim(preg_replace('/\s*(No\.?|Nomor)\s*[\w\-\/]+.*$/i', '', explode(',', $alamat)[0]));
            $jalan   = trim(preg_replace('/^Jl\.?\s*/i', 'Jalan ', $jalan));
            $kec     = '';
            if (preg_match('/Kec\.?\s*([A-Za-z ]+?)\s*(?:,|$)/i', (string) $h['alamat'], $m)) {
                $kec = trim($m[1]);
            }
            $kandidat = array_values(array_unique(array_filter([
                $h['nama'] . ', Medan',
                $alamat . ', Medan',
                $jalan !== '' && $kec !== '' ? "{$jalan}, {$kec}, Medan" : null,
                $jalan !== '' ? "{$jalan}, Medan" : null,
            ])));
            $hasil   = null;
            foreach ($kandidat as $q) {
                $hasil = $this->cari($client, $q);
                sleep(1);
                if ($hasil) {
                    break;
                }
            }
            if ($hasil) {
                $db->table('hotels')->where('id', $h['id'])->update(['latitude' => $hasil[0], 'longitude' => $hasil[1]]);
                $ok++;
            }
            CLI::write(sprintf('[%d/%d] %s — %s', $i + 1, count($hotels), $h['nama'], $hasil ? implode(', ', $hasil) : 'tidak ditemukan'), $hasil ? 'green' : 'yellow');
        }
        CLI::write("Selesai: {$ok} dari " . count($hotels) . ' hotel mendapat koordinat. Sisanya bisa diisi manual lewat admin.', 'green');
    }

    private function cari($client, string $q): ?array
    {
        try {
            $res = $client->get('https://nominatim.openstreetmap.org/search', [
                'query'   => ['format' => 'json', 'limit' => 1, 'countrycodes' => 'id', 'q' => $q],
                'headers' => ['User-Agent' => 'SinggahMedan/1.0 (proyek kuliah)', 'Accept-Language' => 'id'],
            ]);
            $j = json_decode((string) $res->getBody(), true);
            if (! is_array($j) || empty($j[0]['lat'])) {
                return null;
            }
            $lat = (float) $j[0]['lat'];
            $lon = (float) $j[0]['lon'];
            // hanya terima titik di sekitar Kota Medan
            if ($lat < 3.40 || $lat > 3.85 || $lon < 98.50 || $lon > 98.82) {
                return null;
            }

            return [round($lat, 6), round($lon, 6)];
        } catch (\Throwable $e) {
            return null;
        }
    }
}
