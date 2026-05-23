<?php
/**
 * 配置文件
 */

return [
    // 数据库配置
    'db' => [
        'host' => 'localhost',
        'user' => 'tools_imoons_cn',
        'pass' => 'FPMmHMPFFyWajMzs',
        'name' => 'tools_imoons_cn',
        'charset' => 'utf8mb4'
    ],
    
    // Session配置
    'session' => [
        'name' => 'MINI_SESSION',
        'lifetime' => 86400 * 7 // 7天
    ],
    
    // MiniMax API配置
    'minimax' => [
        'base_url' => 'https://api.minimaxi.com/anthropic'
    ],
    
    // 管理员账号密码 (bcrypt hash)
    'admin' => [
        'username' => 'admin',
        'password' => 'Pf555b0cee!2026' // 验证时明文比较
    ]
];
