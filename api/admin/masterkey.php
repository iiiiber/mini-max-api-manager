<?php
/**
 * 主Key配置
 */
require_once __DIR__ . '/../../core/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // 获取主Key
    require_auth();
    $db = get_db();
    $stmt = $db->query("SELECT api_key, base_url, is_active FROM master_keys WHERE id = 1");
    $master = $stmt->fetch();
    success([
        'configured' => !empty($master['api_key']),
        'api_key' => $master['api_key'] ?? '',
        'base_url' => $master['base_url'] ?? 'https://api.minimaxi.com/anthropic'
    ]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 设置主Key
    require_auth();
    $data = get_json_input();
    $api_key = trim($data['api_key'] ?? '');
    
    $db = get_db();
    $stmt = $db->prepare("UPDATE master_keys SET api_key = ? WHERE id = 1");
    $stmt->execute([$api_key]);
    
    success();
} else {
    error('请求方法不支持');
}
