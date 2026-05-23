<?php
/**
 * 生成新API Key
 */
require_once __DIR__ . '/../../core/functions.php';

require_auth();

$data = get_json_input();
$user_name = trim($data['user_name'] ?? '');
$daily_limit = intval($data['daily_limit'] ?? 1000);
$monthly_limit = intval($data['monthly_limit'] ?? 30000);

$api_key = generate_api_key();

$db = get_db();
$stmt = $db->prepare("INSERT INTO api_keys (api_key, user_name, daily_limit, monthly_limit) VALUES (?, ?, ?, ?)");
$stmt->execute([$api_key, $user_name, $daily_limit, $monthly_limit]);

success(['api_key' => $api_key]);
