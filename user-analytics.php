<?php
// Set content type and allow CORS (optional, adjust as needed)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Adjust in production
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, X-CSRF-TOKEN');

// Only allow POST and GET (for fallback)
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Handle POST request (primary method)
if ($method === 'POST') {
    // Read raw JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON']);
        exit();
    }

    // ✅ Process and store data (examples below)
    logAnalyticsData($data);

    // Respond with success
    http_response_code(200);
    echo json_encode(['status' => 'success']);
    exit();
}

// Handle GET fallback (query string: ?data=...)
if ($method === 'GET' && isset($_GET['data'])) {
    $encodedData = $_GET['data'];
    $data = json_decode(htmlspecialchars_decode(urldecode($encodedData)), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid data in query']);
        exit();
    }

    logAnalyticsData($data);

    http_response_code(200);
    // Return empty response or a 1x1 transparent GIF if used for tracking
    header('Content-Type: image/gif');
    echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'); // 1x1 transparent GIF
    exit();
}

// Method not allowed
http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
exit();

// ———————————————————————————————————————————————
// 🔽 Helper Function: Save data to file, DB, etc.
// ———————————————————————————————————————————————
function logAnalyticsData($data) {
    // Example 1: Log to file (simple)
    $logFile = __DIR__ . '/analytics.log';
    $logEntry = date('c') . ' - ' . json_encode($data) . PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

    // Example 2: Save to database (PDO example)
    // saveToDatabase($data);

    // Example 3: Send to external service
    // sendToAnalyticsService($data);
}

// Optional: Save to MySQL/MariaDB
function saveToDatabase($data) {
    $host = 'localhost';
    $dbname = 'your_db';
    $username = 'db_user';
    $password = 'db_pass';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("
            INSERT INTO user_analytics (
                os_name, browser_name, screen_width, screen_height,
                touchable, cpu_cores, memory, timezone, language,
                page_url, page_title, user_agent, collected_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['os']['name'] ?? null,
            $data['browser']['name'] ?? null,
            $data['screen']['width'] ?? null,
            $data['screen']['height'] ?? null,
            $data['touchable'] ? 1 : 0,
            $data['hardware']['cpuCores'] ?? null,
            $data['hardware']['memory'] ?? null,
            $data['timezone'] ?? null,
            $data['language'] ?? null,
            $data['url'] ?? null,
            $data['page'] ?? null,
            $data['userAgent'] ?? null,
            $data['timestamp'] ?? date('c')
        ]);
    } catch (PDOException $e) {
        error_log('DB Error: ' . $e->getMessage());
    }
}
