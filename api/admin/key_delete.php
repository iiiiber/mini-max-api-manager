<?php
/**
 * 删除API Key
 */
require_once __DIR__ . '/../../core/functions.php';

require_auth();

$api_key = $_GET['api_key'] ?? '';
if (empty($api_key)) {
    error('API Key不能为空');
}

$db = get_db();
$stmt = $db->prepare("DELETE FROM api_keys WHERE api_key = ?");
$stmt->execute([$api_key]);

success();
