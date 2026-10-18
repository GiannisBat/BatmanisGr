<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// ===== ΡΥΘΜΙΣΕΙΣ =====
define('CACHE_DIR', __DIR__ . '/rss_cache/');   // Φάκελος cache (δημιουργείται αυτόματα)
define('CACHE_TTL', 5 * 60);                    // Cache: 5 λεπτά (σε δευτερόλεπτα)
define('FETCH_TIMEOUT', 6);                     // Timeout για λήψη RSS (δευτερόλεπτα)
define('MAX_ITEMS', 10);                         // Μέγιστος αριθμός άρθρων ανά feed

// ===== ΒΟΗΘΗΤΙΚΕΣ ΣΥΝΑΡΤΗΣΕΙΣ =====

function send($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function error($msg) {
    send(['status' => 'error', 'message' => $msg]);
}

function getCacheFile($url) {
    return CACHE_DIR . md5($url) . '.json';
}

function getCached($url) {
    $file = getCacheFile($url);
    if (!file_exists($file)) return null;
    if (time() - filemtime($file) > CACHE_TTL) return null;
    $raw = file_get_contents($file);
    return $raw ? json_decode($raw, true) : null;
}

function setCache($url, $data) {
    if (!is_dir(CACHE_DIR)) {
        mkdir(CACHE_DIR, 0755, true);
    }
    file_put_contents(getCacheFile($url), json_encode($data, JSON_UNESCAPED_UNICODE));
}

function fetchUrl($url) {
    $ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

    // Μέθοδος 1: cURL (Ταχύτερη & πιο αξιόπιστη)
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_TIMEOUT        => FETCH_TIMEOUT,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT      => $ua,
            CURLOPT_ENCODING       => 'gzip, deflate',
            CURLOPT_HTTPHEADER     => [
                'Accept: application/rss+xml, application/xml, text/xml, application/atom+xml, */*',
                'Accept-Language: el,en;q=0.9',
            ]
        ]);
        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($data && $httpCode >= 200 && $httpCode < 400) return $data;
    }

    // Μέθοδος 2: file_get_contents fallback
    $context = stream_context_create([
        'http' => [
            'timeout'       => FETCH_TIMEOUT,
            'user_agent'    => $ua,
            'header'        => "Accept: application/rss+xml, application/xml, text/xml, */*\r\nAccept-Language: el,en;q=0.9\r\n",
            'follow_location' => true,
            'max_redirects' => 5,
        ],
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
        ]
    ]);
    $data = @file_get_contents($url, false, $context);
    if ($data) return $data;

    return false;
}

function extractThumbnail($item) {
    // 1. enclosure
    if (isset($item->enclosure)) {
        $type = (string)($item->enclosure['type'] ?? '');
        if (str_starts_with($type, 'image/')) {
            return (string)$item->enclosure['url'];
        }
    }

    // 2. media:thumbnail ή media:content
    $media = $item->children('media', true);
    if ($media) {
        if (isset($media->thumbnail)) return (string)$media->thumbnail['url'];
        if (isset($media->content))   return (string)$media->content['url'];
    }

    // 3. Πρώτη εικόνα μέσα στο description/content
    $desc = (string)($item->description ?? '') . (string)($item->children('content', true)->encoded ?? '');
    if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/', $desc, $m)) {
        return $m[1];
    }

    return '';
}

function parseRSS($xml) {
    libxml_use_internal_errors(true);

    // Καθαρισμός namespace προβλημάτων
    $xml = preg_replace('/(<\/?)(\w+):([^>\/\s]+)/', '$1$2_$3', $xml);
    $xml = preg_replace('/\sxmlns[^=]*="[^"]*"/', '', $xml);

    $feed = @simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOERROR);

    if (!$feed) {
        // Δεύτερη προσπάθεια με Atom
        $xml_orig = $xml;
        $feed = @simplexml_load_string($xml_orig);
        if (!$feed) return null;
    }

    return $feed;
}

function buildItems($feed) {
    $items = [];

    // RSS 2.0
    if (isset($feed->channel->item)) {
        $channel = $feed->channel;
        $feedTitle = (string)$channel->title;
        $feedLink  = (string)$channel->link;

        foreach ($channel->item as $item) {
            if (count($items) >= MAX_ITEMS) break;
            $items[] = [
                'title'       => html_entity_decode(strip_tags((string)$item->title), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'link'        => (string)$item->link ?: (string)$item->guid,
                'pubDate'     => (string)$item->pubDate,
                'description' => (string)$item->description,
                'thumbnail'   => extractThumbnail($item),
            ];
        }
        return ['title' => $feedTitle, 'link' => $feedLink, 'items' => $items];
    }

    // Atom
    if (isset($feed->entry)) {
        $feedTitle = (string)$feed->title;
        $feedLink  = '';
        foreach ($feed->link as $l) {
            if ((string)$l['rel'] === 'alternate' || empty($feedLink)) {
                $feedLink = (string)$l['href'];
            }
        }
        foreach ($feed->entry as $entry) {
            if (count($items) >= MAX_ITEMS) break;
            $link = '';
            foreach ($entry->link as $l) {
                if ((string)$l['rel'] === 'alternate' || empty($link)) {
                    $link = (string)$l['href'];
                }
            }
            $items[] = [
                'title'       => html_entity_decode(strip_tags((string)$entry->title), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'link'        => $link,
                'pubDate'     => (string)($entry->published ?? $entry->updated ?? ''),
                'description' => strip_tags((string)($entry->summary ?? $entry->content ?? '')),
                'thumbnail'   => '',
            ];
        }
        return ['title' => $feedTitle, 'link' => $feedLink, 'items' => $items];
    }

    return null;
}

// ===== ΚΑΘΑΡΙΣΜΟΣ ΠΑΛΙΟΥ CACHE (1% πιθανότητα ανά request) =====
if (rand(1, 100) === 1 && is_dir(CACHE_DIR)) {
    foreach (glob(CACHE_DIR . '*.json') as $f) {
        if (time() - filemtime($f) > CACHE_TTL * 10) {
            @unlink($f);
        }
    }
}

// ===== ΚΥΡΙΩΣ ΛΟΓΙΚΗ =====
$url = trim($_GET['url'] ?? '');

if (!$url) error('Δεν δόθηκε URL');
if (!filter_var($url, FILTER_VALIDATE_URL)) error('Μη έγκυρο URL');

// Επιτρεπόμενα πρωτόκολλα
$scheme = parse_url($url, PHP_URL_SCHEME);
if (!in_array($scheme, ['http', 'https'])) error('Μη επιτρεπτό πρωτόκολλο');

// Έλεγχος cache
$cached = getCached($url);
if ($cached) {
    $cached['cached'] = true;
    send($cached);
}

// Λήψη RSS
$xml = fetchUrl($url);
if (!$xml) error('Αποτυχία λήψης feed: ' . $url);

// Parsing
$feed = parseRSS($xml);
if (!$feed) error('Αποτυχία ανάλυσης XML');

// Δόμηση αποτελέσματος
$result = buildItems($feed);
if (!$result) error('Δεν βρέθηκαν άρθρα στο feed');

$response = [
    'status' => 'ok',
    'cached' => false,
    'feed'   => [
        'title' => $result['title'],
        'link'  => $result['link'],
        'url'   => $url,
    ],
    'items' => $result['items'],
];

// Αποθήκευση cache
setCache($url, $response);

send($response);