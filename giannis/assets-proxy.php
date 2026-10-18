<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

define('CACHE_DIR', __DIR__ . '/assets_cache/');
define('CACHE_TTL', 5 * 60);
define('FETCH_TIMEOUT', 6);

function send($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function getCached() {
    $file = CACHE_DIR . 'assets.json';
    if (!file_exists($file)) return null;
    if (time() - filemtime($file) > CACHE_TTL) return null;
    $raw = file_get_contents($file);
    return $raw ? json_decode($raw, true) : null;
}

function setCache($data) {
    if (!is_dir(CACHE_DIR)) mkdir(CACHE_DIR, 0755, true);
    file_put_contents(CACHE_DIR . 'assets.json', json_encode($data, JSON_UNESCAPED_UNICODE));
}

function fetchYahooAsset($yahooSymbol) {
    $url = "https://query1.finance.yahoo.com/v8/finance/chart/" . urlencode($yahooSymbol) . "?interval=1d&range=1d";
    $ua  = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

    $res = null;
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => FETCH_TIMEOUT,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT      => $ua,
        ]);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode !== 200) $res = null;
    }

    if (!$res) {
        $ctx = stream_context_create([
            'http' => [
                'timeout'    => FETCH_TIMEOUT,
                'user_agent' => $ua,
            ],
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false,
            ]
        ]);
        $res = @file_get_contents($url, false, $ctx);
    }

    if (!$res) return null;

    $json = json_decode($res, true);
    if (!isset($json['chart']['result'][0]['meta'])) return null;

    $meta      = $json['chart']['result'][0]['meta'];
    $price     = $meta['regularMarketPrice'] ?? null;
    $prevClose = $meta['chartPreviousClose'] ?? $meta['previousClose'] ?? null;
    $currency  = $meta['currency'] ?? '';

    if ($price === null) return null;

    $change    = ($prevClose !== null) ? round($price - $prevClose, 2) : null;
    $changePct = ($prevClose !== null && $prevClose != 0) ? round(($price - $prevClose) / $prevClose * 100, 2) : null;

    return [
        'price'     => (float)$price,
        'change'    => $change,
        'changePct' => $changePct,
        'currency'  => $currency,
        'date'      => date('Y-m-d'),
        'time'      => date('H:i:s'),
    ];
}

$ASSETS = [
    'VUAA'     => ['symbol' => 'VUAA.DE',  'name' => 'Vanguard S&P 500 ETF',      'flag' => '🇪🇺', 'currencySign' => '€'],
    'O.US'     => ['symbol' => 'O',        'name' => 'Realty Income Corp',        'flag' => '🇺🇸', 'currencySign' => '$'],
    'DGRW.EU'  => ['symbol' => 'DGRW.L',   'name' => 'WisdomTree US Quality Div', 'flag' => '🇪🇺', 'currencySign' => '$'],
    'PPC.GR'   => ['symbol' => 'PPC.AT',   'name' => 'ΔΕΗ S.A.',                 'flag' => '🇬🇷', 'currencySign' => '€'],
    'MYTIL.GR' => ['symbol' => 'MTLN.AT',  'name' => 'Metlen (Μυτιληναίος)',      'flag' => '🇬🇷', 'currencySign' => '€'],
    'BTC'      => ['symbol' => 'BTC-USD',  'name' => 'Bitcoin',                  'flag' => '₿',  'currencySign' => '$'],
    'ETH'      => ['symbol' => 'ETH-USD',  'name' => 'Ethereum',                 'flag' => 'Ξ',  'currencySign' => '$'],
];

if (isset($_GET['refresh'])) {
    @unlink(CACHE_DIR . 'assets.json');
}

$cached = getCached();
if ($cached) send($cached);

$results = [];
foreach ($ASSETS as $key => $info) {
    $data = fetchYahooAsset($info['symbol']);
    if (!$data) continue;
    $results[$key] = array_merge($data, [
        'name'         => $info['name'],
        'flag'         => $info['flag'],
        'currencySign' => $info['currencySign']
    ]);
}

if (empty($results)) {
    send(['error' => 'Δεν βρέθηκαν δεδομένα']);
}

setCache($results);
send($results);
