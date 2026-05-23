<?php
/**
 * API路由 - 将请求路由到对应的PHP文件
 */
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 静态文件直接返回
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// API路由
if (strpos($uri, '/api/') === 0) {
    $apiFile = __DIR__ . $uri . '.php';
    if (file_exists($apiFile)) {
        require_once $apiFile;
        exit;
    }
}

// 默认返回index.html
require __DIR__ . '/index.html';
