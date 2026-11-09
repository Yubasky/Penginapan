<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ApiKeyModel;
use CodeIgniter\HTTP\ResponseInterface;

abstract class ApiBase extends BaseController
{
    protected function json(array $payload, int $status = 200): ResponseInterface
    {
        return $this->response
            ->setStatusCode($status)
            ->setHeader('Access-Control-Allow-Origin', '*')
            ->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
            ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Accept, X-API-KEY, Authorization')
            ->setJSON($payload);
    }

    /** Mengembalikan response 401 bila kunci API salah/tidak ada; null bila boleh lanjut. */
    protected function butuhKey(): ?ResponseInterface
    {
        $key = trim((string) $this->request->getHeaderLine('X-API-KEY'));
        if ($key === '') {
            $auth = (string) $this->request->getHeaderLine('Authorization');
            if (stripos($auth, 'Bearer ') === 0) {
                $key = trim(substr($auth, 7));
            }
        }
        if (! (new ApiKeyModel())->cek($key)) {
            return $this->json(['status' => 'error', 'message' => 'Kunci API tidak valid. Kirim header X-API-KEY.'], 401);
        }

        return null;
    }

    public function options(): ResponseInterface
    {
        return $this->json([], 204);
    }

    protected function filterDari(): array
    {
        return [
            'q'          => (string) $this->request->getGet('q'),
            'kecamatan'  => (string) $this->request->getGet('kecamatan'),
            'sarapan'    => (string) $this->request->getGet('sarapan'),
            'rating_min' => (string) $this->request->getGet('rating_min'),
            'sort'       => (string) $this->request->getGet('sort'),
        ];
    }

    protected function paging(): array
    {
        $limit = min(max((int) ($this->request->getGet('limit') ?: 20), 1), 100);
        $page  = max((int) $this->request->getGet('page'), 1);

        return [$page, $limit];
    }
}
