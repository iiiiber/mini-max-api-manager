<?php
/**
 * 登出接口
 */
require_once __DIR__ . '/../core/functions.php';

$config = get_config();
session_name($config['session']['name']);
session_start();

session_destroy();

success();
