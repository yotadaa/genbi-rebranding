<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Models\ContactSetting;

final class ContactSettingController
{
    public function __construct(private ?ContactSetting $contactSetting = null)
    {
    }

    public function show(Request $request, Response $response): void
    {
        if (!$this->contactSetting) {
            $response->json(['error' => 'Contact settings tidak tersedia'], 500);
            return;
        }

        $response->json(['data' => $this->contactSetting->get()]);
    }

    public function update(Request $request, Response $response): void
    {
        if (!$this->contactSetting) {
            $response->json(['error' => 'Contact settings tidak tersedia'], 500);
            return;
        }

        $body = $request->json();
        $clean = $this->contactSetting->sanitize($body);
        $errors = $this->validate($clean);
        if ($errors !== []) {
            $response->json(['error' => 'Validasi gagal', 'details' => $errors], 422);
            return;
        }

        $ok = $this->contactSetting->save($clean);
        if (!$ok) {
            $response->json(['error' => 'Gagal menyimpan contact settings'], 500);
            return;
        }

        $response->json(['data' => $this->contactSetting->get()]);
    }

    /** @param array<string, string> $clean @return array<int, string> */
    private function validate(array $clean): array
    {
        $errors = [];
        if ($clean['place_name'] === '') {
            $errors[] = 'Place name wajib diisi.';
        }
        if ($clean['address'] === '') {
            $errors[] = 'Address wajib diisi.';
        }
        if ($clean['email'] === '' || filter_var($clean['email'], FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'Email tidak valid.';
        }
        if ($clean['phone'] === '') {
            $errors[] = 'Phone wajib diisi.';
        }
        if ($clean['maps_url'] === '') {
            $errors[] = 'Google Maps link harus valid dan memakai https pada domain Google Maps.';
        }
        if ($clean['latitude'] === '' || $clean['longitude'] === '') {
            $errors[] = 'Latitude dan longitude wajib valid.';
        }

        return $errors;
    }
}

