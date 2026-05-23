<?php
/**
 * 获取API Key列表
 */
require_once __DIR__ . '/../../core/functions.php';

require_auth();

$db = get_db();
$stmt = $db->query("SELECT * FROM api_keys ORDER BY created_at DESC");
$keys = $stmt->fetchAll();

success($keys);
