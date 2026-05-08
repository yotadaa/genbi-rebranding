<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        $db->exec("CREATE TABLE IF NOT EXISTS tbl_contact_setting (
            id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
            place_name VARCHAR(120) NOT NULL,
            address VARCHAR(255) NOT NULL,
            email VARCHAR(120) NOT NULL,
            phone VARCHAR(80) NOT NULL,
            coordinates_label VARCHAR(160) NOT NULL,
            maps_url TEXT NOT NULL,
            latitude DECIMAL(10, 6) NULL,
            longitude DECIMAL(10, 6) NULL,
            meta_title VARCHAR(255) NULL,
            meta_keyword VARCHAR(255) NULL,
            meta_description TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $seedSql = "INSERT INTO tbl_contact_setting
            (id, place_name, address, email, phone, coordinates_label, maps_url, latitude, longitude, meta_title, meta_keyword, meta_description, created_at, updated_at)
            VALUES
            (1, :place_name, :address, :email, :phone, :coordinates_label, :maps_url, :latitude, :longitude, :meta_title, :meta_keyword, :meta_description, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
            place_name = VALUES(place_name),
            address = VALUES(address),
            email = VALUES(email),
            phone = VALUES(phone),
            coordinates_label = VALUES(coordinates_label),
            maps_url = VALUES(maps_url),
            latitude = VALUES(latitude),
            longitude = VALUES(longitude),
            meta_title = VALUES(meta_title),
            meta_keyword = VALUES(meta_keyword),
            meta_description = VALUES(meta_description),
            updated_at = NOW()";

        $stmt = $db->prepare($seedSql);
        $stmt->execute([
            ':place_name' => 'Bank Indonesia Jambi',
            ':address' => 'Jl. A Yani No.14, Telanaipura, Kec. Telanaipura, Kota Jambi, Jambi 36361',
            ':email' => 'genbijambibi@gmail.com',
            ':phone' => '089627896750',
            ':coordinates_label' => '9HRM+74 Telanaipura, Kota Jambi, Jambi',
            ':maps_url' => 'https://www.google.com/maps/place/Bank+Indonesia+Jambi/@-1.6092871,103.5827899,17z/data=!3m1!4b1!4m6!3m5!1s0x2e25885c04515687:0xe424228e0264e09a!8m2!3d-1.6092871!4d103.5827899!16s%2Fg%2F1pzr95__x?hl=id&entry=ttu',
            ':latitude' => '-1.609287',
            ':longitude' => '103.582790',
            ':meta_title' => 'Contact | GenBI Provinsi Jambi',
            ':meta_keyword' => 'GenBI Jambi, Contact',
            ':meta_description' => 'Hubungi GenBI Provinsi Jambi untuk kolaborasi, informasi kegiatan, dan kebutuhan komunikasi resmi.',
        ]);
    },
];

