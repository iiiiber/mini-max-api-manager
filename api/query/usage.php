<?php
/**
 * 用量查询 - 公开接口
 */
require_once __DIR__ . '/../../core/functions.php';

$api_key = $_GET['api_key'] ?? '';
if (empty($api_key)) {
    error('API Key不能为空');
}

$db = get_db();
$stmt = $db->prepare("SELECT api_key, user_name, daily_limit, monthly_limit, used_today, used_monthly, total_used, is_active, last_used_at FROM api_keys WHERE api_key = ?");
$stmt->execute([$api_key]);
$key = $stmt->fetch();

if (!$key) {
    error('API Key不存在');
}

success([
    'api_key' => $key['api_key'],
    'user_name' => $key['user_name'],
    'daily_limit' => $key['daily_limit'],
    'monthly_limit' => $key['monthly_limit'],
    'used_today' => $key['used_today'],
    'used_monthly' => $key['used_monthly'],
    'total_used' => $key['total_used'],
    'is_active' => (bool)$key['is_active'],
    'last_used_at' => $key['last_used_at']
]);
