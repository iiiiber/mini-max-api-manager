<?php
/**
 * 检查登录状态
 */
require_once __DIR__ . '/../core/functions.php';

if (is_logged_in()) {
    json_response(['authenticated' => true]);
} else {
    json_response(['authenticated' => false]);
}
