<?php
/**
 * 重置API Key用量
 */
require_once __DIR__ . '/../../core/functions.php';

require_auth();

$api_key = $_GET['api_key'] ?? '';
if (empty($api_key)) {
    error('API Key不能为空');
}

$db = get_db();
$stmt = $db->prepare("UPDATE api_keys SET used_today = 0, used_monthly = 0, last_reset_at = NOW() WHERE api_key = ?");
$stmt->execute([$api_key]);

success();
