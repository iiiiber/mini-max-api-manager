<?php
/**
 * 登录接口
 */
require_once __DIR__ . '/../core/functions.php';

$config = get_config();
session_name($config['session']['name']);
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error('请求方法不支持');
}

$data = get_json_input();
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

if (empty($username) || empty($password)) {
    error('用户名和密码不能为空');
}

// 验证账号
if ($username !== $config['admin']['username'] || $password !== $config['admin']['password']) {
    error('账号或密码错误');
}

// 登录成功
$_SESSION['admin'] = true;
$_SESSION['login_time'] = time();

success();
