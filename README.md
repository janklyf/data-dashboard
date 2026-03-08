# 数据看板

一个简单易用的数据看板系统，支持数据块管理、分组管理、尺寸和颜色自定义。

**技术栈**: PHP + Bootstrap 5 + SQLite3
**版本**: 1.0.0
**创建时间**: 2026-03-08

---

## ✨ 功能特性

### ✅ 已实现功能

1. **数据块管理**
   - ✅ 添加数据块
   - ✅ 编辑数据块
   - ✅ 删除数据块
   - ✅ 数据块展示（网格布局）

2. **分组管理**
   - ✅ 添加分组
   - ✅ 删除分组
   - ✅ 按分组筛选数据块

3. **自定义样式**
   - ✅ 三种尺寸（小、中、大）
   - ✅ 五种颜色（灰、蓝、绿、黄、红）
   - ✅ 响应式布局

4. **用户体验**
   - ✅ Bootstrap 5 界面
   - ✅ AJAX 异步操作
   - ✅ 模态框表单
   - ✅ 实时更新

---

## 🚀 快速开始

### 环境要求

- PHP 7.4+
- Web 服务器（Apache/Nginx/PHP 内置服务器）
- 浏览器（Chrome/Firefox/Safari/Edge）

### 安装步骤

1. **克隆项目**

```bash
git clone <repository-url> dashboard-php
cd dashboard-php
```

2. **启动服务器**

```bash
# 使用 PHP 内置服务器
php -S localhost:8000

# 或使用 Apache/Nginx
```

3. **访问应用**

打开浏览器访问: http://localhost:8000

---

## 📖 使用说明

### 添加数据块

1. 点击右上角"添加数据块"按钮
2. 填写标题、内容、分组
3. 选择尺寸和颜色
4. 点击"保存"

### 编辑数据块

1. 点击数据块上的"编辑"按钮
2. 修改信息
3. 点击"保存"

### 删除数据块

1. 点击数据块上的"删除"按钮
2. 确认删除操作

### 管理分组

1. 点击"添加分组"按钮
2. 输入分组名称
3. 点击"保存"

### 筛选数据块

1. 在"选择分组"下拉框中选择分组
2. 数据块列表自动更新

---

## 📂 项目结构

```
dashboard-php/
├── assets/           # 静态资源
├── css/              # 样式文件
│   └── style.css     # 自定义样式
├── js/               # JavaScript 文件
│   └── dashboard.js  # 核心逻辑
├── uploads/          # 上传文件（预留）
├── dashboard.db      # SQLite3 数据库
├── api.php           # 后端 API
├── index.php         # 主页面
├── schema.sql        # 数据库结构
├── DEPLOY.md         # 部署文档
└── README.md         # 本文件
```

---

## 🎨 样式说明

### 尺寸

- **小**: 120px 高度，适合简洁信息
- **中**: 200px 高度，适合中等内容
- **大**: 300px 高度，适合详细内容

### 颜色

- **灰色**: 专业、中性
- **蓝色**: 科技、商务
- **绿色**: 成功、积极
- **黄色**: 警告、注意
- **红色**: 重要、紧急

---

## 🔧 技术细节

### 后端 API

所有 API 接口位于 `api.php`:

| 接口 | 方法 | 说明 |
|------|------|------|
| `/api/groups` | GET | 获取所有分组 |
| `/api/groups` | POST | 添加分组 |
| `/api/groups/{id}` | DELETE | 删除分组 |
| `/api/blocks` | GET | 获取所有数据块 |
| `/api/blocks` | POST | 添加数据块 |
| `/api/blocks/{id}` | PUT | 修改数据块 |
| `/api/blocks/{id}` | DELETE | 删除数据块 |

### 数据库结构

**groups 表**:
- `id`: 分组 ID
- `name`: 分组名称
- `created_at`: 创建时间

**blocks 表**:
- `id`: 数据块 ID
- `group_id`: 所属分组
- `title`: 标题
- `content`: 内容
- `size`: 尺寸（small/medium/large）
- `color`: 颜色（gray/blue/green/yellow/red）
- `created_at`: 创建时间
- `updated_at`: 更新时间

---

## 🐛 常见问题

### Q1: 数据库文件无法写入？

**A**: 检查文件权限，确保 `dashboard.db` 可写：

```bash
chmod 666 dashboard.db
```

### Q2: 数据不显示？

**A**: 检查浏览器控制台错误信息，确保 API 请求成功。

### Q3: 如何备份数据？

**A**: 直接复制 `dashboard.db` 文件：

```bash
cp dashboard.db dashboard.db.backup
```

---

## 📝 更新日志

### v1.0.0 (2026-03-08)

- ✅ 初始版本发布
- ✅ 数据块 CRUD 功能
- ✅ 分组管理功能
- ✅ 尺寸和颜色自定义
- ✅ 响应式布局

---

## 🤝 贡献指南

欢迎提交 Issue 和 Pull Request！

---

## 📄 许可证

MIT License

---

## 📞 联系方式

如有问题，请通过以下方式联系：

- GitHub Issues
- Email: support@example.com

---

**最后更新**: 2026-03-08
**版本**: 1.0.0
