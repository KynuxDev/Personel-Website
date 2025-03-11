<?php
// Sadece basit bir JSON çıktısı
header('Content-Type: application/json');

$data = [
    'test' => 'value',
    'number' => 42,
    'array' => [1, 2, 3]
];

echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
