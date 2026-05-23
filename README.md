# MiniMax API 管理平台

基于 PHP + MySQL 的 MiniMax API 代理管理平台，支持多 Key 生成、限流、代理转发。

## 项目结构

```
mini/
├── index.html          # 前端主页面（登录+Key管理界面）
├── query.html          # API查询页面
├── docs.html           # 使用文档
├── index.php           # 入口文件（重定向到index.html）
├── api/
│   ├── login.php       # 登录接口
│   ├── logout.php      # 登出接口
│   ├── check.php       # 登录状态检查
│   ├── v1_proxy.php    # MiniMax API 代理核心
│   └── v1_proxy.php.bak
├── config/
│   └── config.php      # 配置文件（数据库/API/管理员账号）
├── core/
│   └── functions.php   # 核心函数库
├── sql/
│   └── schema.sql      # 数据库表结构
└── mini.imoons.cn_4jSNh.tar.gz  # 源码备份
```

## 数据库表结构

### api_keys — 用户API Key表
| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT AUTO_INCREMENT | 主键 |
| api_key | VARCHAR(100) | Key值（唯一） |
| user_name | VARCHAR(100) | 用户名 |
| daily_limit | INT | 每日限额默认1000 |
| monthly_limit | INT | 每月限额默认30000 |
| used_today | INT | 今日已用 |
| used_monthly | INT | 本月已用 |
| total_used | INT | 累计使用 |
| is_active | TINYINT | 是否启用 |
| created_at | DATETIME | 创建时间 |
| last_used_at | DATETIME | 最后使用时间 |
| last_reset_at | DATETIME | 最后重置时间 |

### master_keys — 主Key表
| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT DEFAULT 1 | 主键 |
| api_key | VARCHAR(200) | MiniMax主API Key |
| is_active | TINYINT | 是否启用 |

### usage_logs — 使用日志表
| 字段 | 类型 | 说明 |
|------|------|------|
| id | BIGINT AUTO_INCREMENT | 主键 |
| api_key | VARCHAR(100) | 关联的API Key |
| tokens_used | INT | Token用量 |
| cost | DECIMAL(10,6) | 费用 |
| endpoint | VARCHAR(200) | 调用的API端点 |
| created_at | DATETIME | 调用时间 |

## 部署指南

### 环境要求
- PHP 7.4+ (PDO/MySQLi)
- MySQL 5.7+ / MariaDB 10.3+
- Nginx 或 Apache

### 步骤 1：上传代码
```bash
# 上传整个 mini 目录到服务器
scp -r ./mini root@your-server:/www/wwwroot/tools.imoons.cn/
```

### 步骤 2：创建数据库
```bash
mysql -u root -p'YOUR_DB_PASSWORD'
```
```sql
CREATE DATABASE tools_imoons_cn DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'tools_imoons_cn'@'localhost' IDENTIFIED BY 'YOUR_DB_PASSWORD';
GRANT ALL PRIVILEGES ON tools_imoons_cn.* TO 'tools_imoons_cn'@'localhost';
FLUSH PRIVILEGES;
USE tools_imoons_cn;
SOURCE sql/schema.sql;
```

### 步骤 3：修改配置
编辑 `config/config.php`：
```php
'db' => [
    'host' => 'localhost',
    'user' => 'tools_imoons_cn',
    'pass' => 'YOUR_DB_PASSWORD',      // 修改为实际密码
    'name' => 'tools_imoons_cn',
    'charset' => 'utf8mb4'
],
'admin' => [
    'username' => 'admin',
    'password' => 'YOUR_ADMIN_PASSWORD' // 修改为实际密码
],
```

### 步骤 4：配置Nginx
```nginx
server {
    listen 80;
    server_name tools.imoons.cn;
    root /www/wwwroot/tools.imoons.cn/mini;
    index index.html index.php;

    location / {
        try_files $uri $uri/ =404;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\. {
        deny all;
    }
}
```

### 步骤 5：导入数据库
```bash
mysql -u tools_imoons_cn -p'YOUR_DB_PASSWORD' tools_imoons_cn < sql/schema.sql
```

### 步骤 6：验证
访问 `http://tools.imoons.cn/mini/` 使用管理员账号登录。

## API 文档

### 认证
- 管理员登录：`POST /api/login` → `{"username":"admin","password":"..."}`
- 状态检查：`GET /api/check.php`

### Key管理
- 生成Key：`POST /api/v1_proxy.php` (header: `X-Api-Key: {master_key}`)

### 代理转发
- 格式：`GET/POST /mini/v1/{endpoint}?api_key={user_key}`
- 认证：Header `Authorization: Bearer {master_key}`

## 部署服务器信息

- **服务器IP**: 42.192.112.148
- **网站目录**: `/www/wwwroot/tools.imoons.cn/mini`
- **数据库**: `tools_imoons_cn` (MySQL)
- **管理员**: admin / Pf555b0cee!2026

## 开发说明

### 本地开发
```bash
# 启动本地PHP服务器
php -S localhost:8080
```

### 数据库连接
生产环境使用 localhost，配置在 `config/config.php`。

### 关键函数（core/functions.php）
- `get_db()` — 获取PDO数据库连接
- `generate_api_key()` — 生成格式为 `mmx-{16字节hex}` 的API Key
- `check_rate_limit()` — 检查日/月限额
- `log_usage()` — 记录API调用日志
- `is_logged_in()` / `require_auth()` — Session认证