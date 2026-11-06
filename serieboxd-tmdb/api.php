<?php
// Proxy mínimo para a API do TMDB: a chave fica só no servidor, nunca no navegador.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: private, max-age=3600');

function fail($code, $msg) {
  header('Cache-Control: no-store');
  http_response_code($code);
  echo json_encode(['error' => $msg], JSON_UNESCAPED_UNICODE);
  exit;
}

$key = trim((string)getenv('TMDB_API_KEY'));
if ($key === '') {
  $cfgFile = __DIR__ . '/config.php';
  if (!is_file($cfgFile)) fail(500, 'Arquivo config.php não encontrado ao lado do api.php. Crie-o a partir do config.example.php (o nome precisa ser exatamente config.php).');
  $cfg = include $cfgFile;
  if (!is_array($cfg) || !isset($cfg['key'])) fail(500, 'O config.php precisa começar com <?php e conter: return ["key" => "SUA_CHAVE"];');
  $key = trim((string)$cfg['key']);
}
if ($key === '' || $key === 'COLE_SUA_CHAVE_AQUI') fail(500, 'A chave no config.php está vazia ou ainda é o texto de exemplo.');

$a = $_GET['a'] ?? '';
$p = ['language' => 'pt-BR'];

if ($a === 'search') {
  $q = trim($_GET['q'] ?? '');
  if ($q === '') fail(400, 'Busca vazia.');
  $path = '/search/tv';
  $p += ['query' => $q, 'include_adult' => 'false'];
} elseif ($a === 'tv') {
  $id = (int)($_GET['id'] ?? 0);
  if ($id < 1) fail(400, 'ID inválido.');
  $path = "/tv/$id";
} elseif ($a === 'season') {
  $id = (int)($_GET['id'] ?? 0);
  $n = (int)($_GET['n'] ?? -1);
  if ($id < 1 || $n < 0) fail(400, 'Parâmetros inválidos.');
  $path = "/tv/$id/season/$n";
} else {
  fail(400, 'Ação inválida.');
}

// Aceita tanto a chave v3 (api_key) quanto o "Token de Acesso de Leitura" v4 (começa com eyJ).
$headers = ['Accept: application/json'];
if (strpos($key, 'eyJ') === 0) $headers[] = 'Authorization: Bearer ' . $key;
else $p['api_key'] = $key;

$url = 'https://api.themoviedb.org/3' . $path . '?' . http_build_query($p);
$body = false;
$status = 0;
$err = '';

if (function_exists('curl_init')) {
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_TIMEOUT => 10,
  ]);
  $body = curl_exec($ch);
  $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $err = curl_error($ch);
  curl_close($ch);
} else {
  // Sem a extensão cURL: tenta o PHP puro (precisa de allow_url_fopen e da extensão openssl).
  $ctx = stream_context_create(['http' => [
    'method' => 'GET',
    'header' => implode("\r\n", $headers),
    'timeout' => 10,
    'ignore_errors' => true,
  ]]);
  $body = @file_get_contents($url, false, $ctx);
  if (isset($http_response_header[0]) && preg_match('#\s(\d{3})\s#', $http_response_header[0], $m)) $status = (int)$m[1];
}

if ($body === false) {
  fail(502, 'Não foi possível contatar o TMDB' . ($err ? ' (' . $err . ')' : '') . '. Ative as extensões curl e openssl no php.ini e reinicie o servidor.');
}
http_response_code($status ?: 502);
echo $body;
