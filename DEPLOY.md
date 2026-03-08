# 数据看板部署文档

**项目名称**: 数据看板
**技术栈**: PHP + Bootstrap 5 + SQLite3
**创建时间**: 2026-03-08

---

## 📋 环境要求

### 最低配置
- **PHP**: 7.4+
- **Web 服务器**: Apache / Nginx / PHP 内置服务器
- **数据库**: SQLite3（PHP 内置支持）
- **浏览器**: Chrome / Firefox / Safari / Edge（最新版本）

### 推荐配置
- **PHP**: 8.0+
- **Web 服务器**: Nginx 1.18+ / Apache 2.4+
- **PHP 扩展**: PDO, PDO_SQLITE
- **浏览器**: Chrome 90+

---

## 🚀 快速部署

### 方法 1: PHP 内置服务器（开发环境）

```bash
# 进入项目目录
cd /home/xkxx/dashboard-php

# 启动 PHP 内置服务器
php -S localhost:8000
```

访问: http://localhost:8000

### 方法 2: Apache 配置

```bash
# 1. 复制项目到网站目录
sudo cp -r /home/xkxx/dashboard-php /var/www/html/dashboard

# 2. 设置权限
sudo chown -R www-data:www-data /var/www/html/dashboard
sudo chmod -R 755 /var/www/html/dashboard

# 3. 配置虚拟主机（可选）
sudo nano /etc/apache2/sites-available/dashboard.conf
```

创建 `/etc/apache2/sites-available/dashboard.conf`:

```apache
<VirtualHost *:80>
    ServerName dashboard.local
    DocumentRoot /var/www/html/dashboard

    <Directory /var/www/html/dashboard>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/dashboard-error.log
    CustomLog ${APACHE_LOG_DIR}/dashboard-access.log combined
</VirtualHost>
```

启用站点:

```bash
sudo a2ensite dashboard.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### 方法 3: Nginx 配置

```bash
# 1. 复制项目到网站目录
sudo cp -r /home/xkxx/dashboard-php /var/www/html/dashboard

# 2. 设置权限
sudo chown -R www-data:www-data /var/www/html/dashboard
sudo chmod -R 755 /var/www/html/dashboard

# 3. 配置虚拟主机
sudo nano /etc/nginx/sites-available/dashboard
```

创建 `/etc/nginx/sites-available/dashboard`:

```nginx
server {
    listen 80;
    server_name dashboard.local;

    root /var/www/html/dashboard;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

启用站点:

```bash
sudo ln -s /etc/nginx/sites-available/dashboard /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

---

## 🔒 安全配置

### 1. 目录权限

```bash
# 设置正确的文件权限
sudo chown -R www-data:www-data /var/www/html/dashboard
sudo chmod -R 755 /var/www/html/dashboard
sudo chmod 644 /var/www/html/dashboard/*.php
sudo chmod 644 /var/www/html/dashboard/*.md
```

### 2. 数据库文件权限

```bash
# 确保数据库文件可写
sudo chmod 666 /var/www/html/dashboard/dashboard.db
```

### 3. 隐藏敏感文件

```bash
# 创建 .htaccess 隐藏文件
cd /var/www/html/dashboard
echo "Deny from all" > .htaccess
```

---

## 📊 性能优化

### 1. 启用 OPcache（PHP）

在 `php.ini` 中:

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
```

### 2. 数据库连接池

在 `api.php` 中已经使用 PDO 连接池，无需额外配置。

### 3. 启用 Gzip 压缩

在 Web 服务器配置中启用 Gzip:

**Apache**:
```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript
</IfModule>
```

**Nginx**:
```nginx
gzip on;
gzip_types text/plain text/css application/json application/javascript;
```

---

## 🔧 故障排查

### 问题 1: 数据库连接失败

**错误信息**: `数据库连接失败`

**解决方案**:
1. 检查 `dashboard.db` 文件是否存在
2. 检查文件权限：`sudo chmod 666 dashboard.db`
3. 检查 PHP PDO 扩展是否启用

### 问题 2: API 请求 404

**错误信息**: `HTTP/1.1 404 Not Found`

**解决方案**:
1. 检查 URL 是否正确：`http://localhost/dashboard-php/api.php/groups`
2. 检查 Web 服务器配置
3. 检查 PHP 内置服务器是否正常运行

### 问题 3: 跨域错误

**错误信息**: `CORS policy`

**解决方案**:
已在 `api.php` 中添加跨域支持，无需额外配置。

### 问题 4: 数据未保存

**解决方案**:
1. 检查浏览器控制台错误
2. 检查 API 返回的错误信息
3. 检查数据库文件权限

---

## 📝 维护指南

### 数据库备份

```bash
# 备份数据库
cp dashboard.db dashboard.db.backup

# 恢复数据库
cp dashboard.db.backup dashboard.db
```

### 清理日志

```bash
# 清理 Apache 日志
sudo truncate -s 0 /var/log/apache2/dashboard-error.log

# 清理 Nginx 日志
sudo truncate -s 0 /var/log/nginx/dashboard-access.log
```

### 检查 PHP 版本

```bash
php -v
```

### 检查 PHP 扩展

```bash
php -m | grep pdo
php -m | grep sqlite
```

---

## 📚 相关文档

- [README.md](README.md) - 使用说明
- [API_TEST_REPORT.md](API_TEST_REPORT.md) - API 测试报告
- [PROJECT_STRUCTURE.md](PROJECT_STRUCTURE.md) - 项目结构
- [schema.sql](schema.sql) - 数据库结构

---

**最后更新**: 2026-03-08
**维护者**: AI 一人公司系统
