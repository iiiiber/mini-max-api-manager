<?php
/**
 * 核心函数库
 */

// 获取配置
function get_config() {
    static $config = null;
    if ($config === null) {
        $config = require __DIR__ . '/../config/config.php';
    }
    return $config;
}

// 数据库连接
function get_db() {
    static $pdo = null;
    if ($pdo === null) {
        $config = get_config();
        $db = $config['db'];
        $dsn = "mysql:host={$db['host']};dbname={$db['name']};charset={$db['charset']}";
        $pdo = new PDO($dsn, $db['user'], $db['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }
    return $pdo;
}

// JSON响应
function json_response($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// 成功响应
function success($data = null) {
    json_response(['success' => true, 'data' => $data]);
}

// 错误响应
function error($message, $code = 400) {
    json_response(['success' => false, 'error' => $message], $code);
}

// 检查是否已登录
function is_logged_in() {
    $config = get_config();
    session_name($config['session']['name']);
    session_start();
    return isset($_SESSION['admin']) && $_SESSION['admin'] === true;
}

// 要求登录
function require_auth() {
    if (!is_logged_in()) {
        error('请先登录', 401);
    }
}

// 生成随机API Key
function generate_api_key() {
    return 'mmx-' . bin2hex(random_bytes(16));
}

// 获取请求JSON数据
function get_json_input() {
    $input = file_get_contents('php://input');
    return json_decode($input, true) ?? [];
}

// 记录使用量
function log_usage($api_key, $tokens = 0, $cost = 0, $endpoint = '') {
    $db = get_db();
    
    // 更新 Key 使用统计
    $stmt = $db->prepare("UPDATE api_keys SET 
        used_today = used_today + 1,
        used_monthly = used_monthly + 1,
        total_used = total_used + 1,
        last_used_at = NOW()
        WHERE api_key = ?");
    $stmt->execute([$api_key]);
    
    // 记录日志
    $stmt = $db->prepare("INSERT INTO usage_logs (api_key, tokens_used, cost, endpoint) VALUES (?, ?, ?, ?)");
    $stmt->execute([$api_key, $tokens, $cost, $endpoint]);
}

// 检查限流
function check_rate_limit($api_key) {
    $db = get_db();
    
    // 获取 Key 配置
    $stmt = $db->prepare("SELECT daily_limit, monthly_limit, used_today, used_monthly FROM api_keys WHERE api_key = ? AND is_active = 1");
    $stmt->execute([$api_key]);
    $key = $stmt->fetch();
    
    if (!$key) {
        return ['allowed' => false, 'reason' => 'API Key 不存在或已禁用'];
    }
    
    if ($key['used_today'] >= $key['daily_limit']) {
        return ['allowed' => false, 'reason' => '已达每日限额'];
    }
    
    if ($key['used_monthly'] >= $key['monthly_limit']) {
        return ['allowed' => false, 'reason' => '已达每月限额'];
    }
    
    return ['allowed' => true];
}

// 重置每日用量（定时任务调用）
function reset_daily_usage() {
    $db = get_db();
    
    // 检查是否需要重置
    $stmt = $db->query("SELECT DATE(last_reset_at) as last_reset FROM api_keys LIMIT 1");
    $row = $stmt->fetch();
    
    if ($row && $row['last_reset'] !== date('Y-m-d')) {
        $db->exec("UPDATE api_keys SET used_today = 0, last_reset_at = NOW()");
    }
}

// 重置每月用量
function reset_monthly_usage() {
    $db = get_db();
    
    $stmt = $db->query("SELECT DATE(last_reset_at) as last_reset FROM api_keys LIMIT 1");
    $row = $stmt->fetch();
    
    if ($row && substr($row['last_reset'], 0, 7) !== date('Y-m')) {
        $db->exec("UPDATE api_keys SET used_monthly = 0, used_today = 0, last_reset_at = NOW()");
    }
}
