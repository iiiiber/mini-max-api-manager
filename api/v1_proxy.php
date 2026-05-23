<?php
/**
 * API代理 - 代理到MiniMax API
 */
require_once __DIR__ . '/../core/functions.php';

// 获取请求的API Key
$api_key = $_SERVER['HTTP_X_API_KEY'] ?? '';
if (empty($api_key)) {
    // 从URL参数获取
    $api_key = $_GET['api_key'] ?? '';
}

if (empty($api_key)) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => ['type' => 'invalid_request', 'message' => 'API key is required']]);
    exit;
}

// 验证API Key
$db = get_db();
$stmt = $db->prepare("SELECT * FROM api_keys WHERE api_key = ? AND is_active = 1");
$stmt->execute([$api_key]);
$key_record = $stmt->fetch();

if (!$key_record) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => ['type' => 'authentication_error', 'message' => 'Invalid API key']]);
    exit;
}

// 检查限流
$limit_check = check_rate_limit($api_key);
if (!$limit_check['allowed']) {
    http_response_code(429);
    header('Content-Type: application/json');
    echo json_encode(['error' => ['type' => 'rate_limit_error', 'message' => $limit_check['reason']]]);
    exit;
}

// 获取主Key
$stmt = $db->query("SELECT api_key FROM master_keys WHERE id = 1 AND is_active = 1");
$master = $stmt->fetch();

if (empty($master['api_key'])) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => ['type' => 'server_error', 'message' => 'Master API key not configured']]);
    exit;
}

// 获取请求路径和内容
$path = $_SERVER['REQUEST_URI'];
// 去掉 /mini 前缀（如果有）
$path = preg_replace('#^/mini(/v1)?#', '', $path);
$path = preg_replace('#^/v1/#', '', $path);
$method = $_SERVER['REQUEST_METHOD'];
$body = file_get_contents('php://input');

// 构建MiniMax API URL
$base_url = 'https://api.minimax.chat';
$url = $base_url . '/v1/' . ltrim($path, '/');

// 转发请求到MiniMax API
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 120,
    CURLOPT_CUSTOMREQUEST => $method,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $master['api_key'],
        'Content-Type: application/json',
        'HTTP-X-Api-Key: ' . $api_key
    ]
]);

if (!empty($body) && in_array($method, ['POST', 'PUT', 'PATCH'])) {
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
}

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    http_response_code(502);
    header('Content-Type: application/json');
    echo json_encode(['error' => ['type' => 'proxy_error', 'message' => 'Failed to connect to upstream API']]);
    exit;
}

// 记录使用量
log_usage($api_key, 0, 0, $path);

// 返回响应
http_response_code($http_code);
echo $response;
