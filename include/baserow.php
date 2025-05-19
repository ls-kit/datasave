<?php
if (!defined('BASEROW_TOKEN')) {
  require_once 'env.php'; // fallback if needed
}



function baserow_post($table_key, $data) {
  global $baserow_tables;
  $table_id = $baserow_tables[$table_key] ?? null;
  if (!$table_id) return ['error' => 'Missing table ID'];

  // ✅ FIXED URL
  $url = BASEROW_BASE_URL . "database/rows/table/{$table_id}/";

  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Token " . BASEROW_TOKEN
  ]);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

  $response = curl_exec($ch);
  $error = curl_error($ch);
  curl_close($ch);

  if ($response === false) {
    return ['error' => 'Curl failed: ' . $error];
  }

  return json_decode($response, true);
}






function baserow_get_all($table_key) {
  global $baserow_tables;
  $table_id = $baserow_tables[$table_key] ?? null;
  if (!$table_id) return [];

  $url = BASEROW_BASE_URL . "database/rows/table/{$table_id}/?user_field_names=true"; // ✅ CORRECT

  $headers = [
    "Authorization: Token " . BASEROW_TOKEN
  ];

  $opts = [
    "http" => [
      "method" => "GET",
      "header" => implode("\r\n", $headers)
    ]
  ];

  $context = stream_context_create($opts);
  $response = file_get_contents($url, false, $context);
  return json_decode($response, true);
}


