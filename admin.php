<?php
/**
 * 数据看板后台管理页面
 * 创建时间：2026-03-10
 */

header('Content-Type: text/html; charset=utf-8');
session_start();

// 简单的登录验证
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$dbFile = __DIR__ . '/dashboard.db';
try {
    $db = new PDO("sqlite:" . $dbFile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('数据库连接失败: ' . $e->getMessage());
}

$page = $_GET['page'] ?? 'groups';
$group_id = $_GET['group_id'] ?? null;
$block_id = $_GET['block_id'] ?? null;

// 处理分组操作
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'add_group') {
        $name = trim($_POST['name'] ?? '');
        if ($name) {
            $stmt = $db->prepare("INSERT INTO groups (name) VALUES (:name)");
            $stmt->execute([':name' => $name]);
        }
    } elseif ($action === 'update_group') {
        $id = $_POST['id'] ?? null;
        $name = trim($_POST['name'] ?? '');
        $color = $_POST['color'] ?? 'gray';
        if ($id && $name) {
            $stmt = $db->prepare("UPDATE groups SET name = :name, color = :color WHERE id = :id");
            $stmt->execute([':name' => $name, ':color' => $color, ':id' => $id]);
        }
    } elseif ($action === 'delete_group') {
        $id = $_POST['id'] ?? null;
        if ($id) {
            $db->beginTransaction();
            try {
                $stmt = $db->prepare("DELETE FROM groups WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }
        }
    }
}

// 处理数据块操作
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'add_block') {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $group_id = $_POST['group_id'] ?? null;
        $size = $_POST['size'] ?? 'small';
        $color = $_POST['color'] ?? 'gray';
        if ($title && $content && $group_id) {
            $stmt = $db->prepare("INSERT INTO blocks (group_id, title, content, size, color) VALUES (:group_id, :title, :content, :size, :color)");
            $stmt->execute([':group_id' => $group_id, ':title' => $title, ':content' => $content, ':size' => $size, ':color' => $color]);
        }
    } elseif ($action === 'update_block') {
        $id = $_POST['id'] ?? null;
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $group_id = $_POST['group_id'] ?? null;
        $size = $_POST['size'] ?? 'small';
        $color = $_POST['color'] ?? 'gray';
        if ($id && $title && $content) {
            $stmt = $db->prepare("UPDATE blocks SET title = :title, content = :content, group_id = :group_id, size = :size, color = :color WHERE id = :id");
            $stmt->execute([':title' => $title, ':content' => $content, ':group_id' => $group_id, ':size' => $size, ':color' => $color, ':id' => $id]);
        }
    } elseif ($action === 'delete_block') {
        $id = $_POST['id'] ?? null;
        if ($id) {
            $stmt = $db->prepare("DELETE FROM blocks WHERE id = :id");
            $stmt->execute([':id' => $id]);
        }
    }
}

// 获取分组列表
$stmt = $db->query('SELECT * FROM groups ORDER BY id ASC');
$groups = $stmt->fetchAll();

// 获取数据块列表
$stmt = $db->query('SELECT b.*, g.name as group_name FROM blocks b LEFT JOIN groups g ON b.group_id = g.id ORDER BY b.id ASC');
$blocks = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台管理 - 数据看板</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #0d6efd 0%, #0a58ca 100%);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.85);
            padding: 12px 20px;
            margin: 4px 0;
            border-radius: 6px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover {
            color: white;
            background-color: rgba(255,255,255,0.1);
        }
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,0.2);
            font-weight: 600;
        }
        .main-content {
            padding: 30px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }
        .page-header {
            background: white;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            margin-bottom: 30px;
        }
        .page-header h1 {
            font-size: 1.75rem;
            font-weight: 600;
            color: #212529;
        }
        .btn-primary {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            border: none;
            padding: 10px 24px;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #0a58ca 0%, #0d6efd 100%);
        }
        .table thead {
            background-color: #f8f9fa;
        }
        .table th {
            font-weight: 600;
            color: #495057;
        }
        .action-btn {
            padding: 6px 12px;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-0">
                <div class="p-4">
                    <h4 class="text-white mb-4">
                        <i class="bi bi-speedometer2"></i> 数据看板
                    </h4>
                    <nav class="nav flex-column">
                        <a class="nav-link <?php echo $page === 'groups' ? 'active' : ''; ?>" href="admin.php?page=groups">
                            <i class="bi bi-collection me-2"></i>分组管理
                        </a>
                        <a class="nav-link <?php echo $page === 'blocks' ? 'active' : ''; ?>" href="admin.php?page=blocks">
                            <i class="bi bi-grid-3x3 me-2"></i>数据块管理
                        </a>
                        <a class="nav-link" href="index.php">
                            <i class="bi bi-box-arrow-left me-2"></i>返回前台
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <div class="page-header">
                    <h1>
                        <?php if ($page === 'groups'): ?>
                            <i class="bi bi-collection me-2"></i>分组管理
                        <?php else: ?>
                            <i class="bi bi-grid-3x3 me-2"></i>数据块管理
                        <?php endif; ?>
                    </h1>
                </div>

                <?php if ($page === 'groups'): ?>
                    <!-- 分组管理 -->
                    <div class="card mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">
                                <i class="bi bi-plus-circle me-2"></i>添加分组
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="add_group">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">分组名称 *</label>
                                        <input type="text" class="form-control" name="name" required placeholder="请输入分组名称">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">默认颜色</label>
                                        <select class="form-select" name="color">
                                            <option value="gray">灰色</option>
                                            <option value="blue">蓝色</option>
                                            <option value="green">绿色</option>
                                            <option value="yellow">黄色</option>
                                            <option value="red">红色</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-plus-lg me-2"></i>添加分组
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- 分组列表 -->
                    <div class="card">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">
                                <i class="bi bi-list me-2"></i>分组列表
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>名称</th>
                                            <th>颜色</th>
                                            <th>数据块数量</th>
                                            <th>创建时间</th>
                                            <th>操作</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($groups as $group): ?>
                                            <tr>
                                                <td><?php echo $group['id']; ?></td>
                                                <td><?php echo htmlspecialchars($group['name']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $group['color']; ?>">
                                                        <?php echo $group['color']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $stmt = $db->prepare("SELECT COUNT(*) as count FROM blocks WHERE group_id = :group_id");
                                                    $stmt->execute([':group_id' => $group['id']]);
                                                    echo $stmt->fetchColumn();
                                                    ?>
                                                </td>
                                                <td><?php echo $group['created_at']; ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-warning action-btn" onclick="editGroup(<?php echo $group['id']; ?>, '<?php echo htmlspecialchars($group['name']); ?>', '<?php echo $group['color']; ?>')">
                                                        <i class="bi bi-pencil"></i> 编辑
                                                    </button>
                                                    <button class="btn btn-sm btn-danger action-btn" onclick="deleteGroup(<?php echo $group['id']; ?>, '<?php echo htmlspecialchars($group['name']); ?>')">
                                                        <i class="bi bi-trash"></i> 删除
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- 数据块管理 -->
                    <div class="card mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">
                                <i class="bi bi-plus-circle me-2"></i>添加数据块
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="add_block">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">标题 *</label>
                                        <input type="text" class="form-control" name="title" required placeholder="请输入标题">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">内容 *</label>
                                        <textarea class="form-control" name="content" rows="2" required placeholder="请输入内容"></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">分组 *</label>
                                        <select class="form-select" name="group_id" required>
                                            <option value="">请选择分组</option>
                                            <?php foreach ($groups as $group): ?>
                                                <option value="<?php echo $group['id']; ?>">
                                                    <?php echo htmlspecialchars($group['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">尺寸</label>
                                        <select class="form-select" name="size">
                                            <option value="small">小</option>
                                            <option value="medium">中</option>
                                            <option value="large">大</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">颜色</label>
                                        <select class="form-select" name="color">
                                            <option value="gray">灰色</option>
                                            <option value="blue">蓝色</option>
                                            <option value="green">绿色</option>
                                            <option value="yellow">黄色</option>
                                            <option value="red">红色</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-plus-lg me-2"></i>添加数据块
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- 数据块列表 -->
                    <div class="card">
                        <div class="card-header bg-white py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="bi bi-list me-2"></i>数据块列表
                                </h5>
                                <form method="GET" class="d-flex gap-2">
                                    <input type="hidden" name="page" value="blocks">
                                    <select name="filter_group" class="form-select" onchange="this.form.submit()" style="width: 200px;">
                                        <option value="">全部分组</option>
                                        <?php foreach ($groups as $group): ?>
                                            <option value="<?php echo $group['id']; ?>" <?php echo (isset($_GET['filter_group']) && $_GET['filter_group'] == $group['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($group['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (isset($_GET['filter_group']) && $_GET['filter_group']): ?>
                                        <a href="admin.php?page=blocks" class="btn btn-sm btn-secondary">清除筛选</a>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>标题</th>
                                            <th>内容</th>
                                            <th>分组</th>
                                            <th>尺寸</th>
                                            <th>颜色</th>
                                            <th>创建时间</th>
                                            <th>操作</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // 按分组筛选数据块
                                        if (isset($_GET['filter_group']) && $_GET['filter_group']) {
                                            $filterGroup = $_GET['filter_group'];
                                            $stmt = $db->prepare("SELECT b.*, g.name as group_name FROM blocks b LEFT JOIN groups g ON b.group_id = g.id WHERE b.group_id = :group_id ORDER BY b.id ASC");
                                            $stmt->execute([':group_id' => $filterGroup]);
                                            $filteredBlocks = $stmt->fetchAll();
                                        } else {
                                            $filteredBlocks = $blocks;
                                        }
                                        ?>
                                        <?php foreach ($filteredBlocks as $block): ?>
                                            <tr>
                                                <td><?php echo $block['id']; ?></td>
                                                <td><?php echo htmlspecialchars($block['title']); ?></td>
                                                <td><?php echo htmlspecialchars($block['content']); ?></td>
                                                <td><?php echo htmlspecialchars($block['group_name']); ?></td>
                                                <td><?php echo $block['size']; ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $block['color']; ?>">
                                                        <?php echo $block['color']; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $block['created_at']; ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-warning action-btn" onclick="editBlock(<?php echo $block['id']; ?>, '<?php echo htmlspecialchars($block['title']); ?>', '<?php echo htmlspecialchars($block['content']); ?>', <?php echo $block['group_id']; ?>, '<?php echo $block['size']; ?>', '<?php echo $block['color']; ?>')">
                                                        <i class="bi bi-pencil"></i> 编辑
                                                    </button>
                                                    <button class="btn btn-sm btn-danger action-btn" onclick="deleteBlock(<?php echo $block['id']; ?>, '<?php echo htmlspecialchars($block['title']); ?>')">
                                                        <i class="bi bi-trash"></i> 删除
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($filteredBlocks)): ?>
                                            <tr>
                                                <td colspan="8" class="text-center py-5">
                                                    <div class="text-muted">
                                                        <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                                        暂无数据块
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 编辑分组模态框 -->
    <div class="modal fade" id="editGroupModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">编辑分组</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editGroupForm">
                        <input type="hidden" name="action" value="update_group">
                        <input type="hidden" id="editGroupId">
                        <div class="mb-3">
                            <label class="form-label">分组名称 *</label>
                            <input type="text" class="form-control" id="editGroupName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">颜色</label>
                            <select class="form-select" id="editGroupColor">
                                <option value="gray">灰色</option>
                                <option value="blue">蓝色</option>
                                <option value="green">绿色</option>
                                <option value="yellow">黄色</option>
                                <option value="red">红色</option>
                            </select>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                            <button type="submit" class="btn btn-primary">保存</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 编辑数据块模态框 -->
    <div class="modal fade" id="editBlockModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">编辑数据块</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editBlockForm">
                        <input type="hidden" name="action" value="update_block">
                        <input type="hidden" id="editBlockId">
                        <div class="mb-3">
                            <label class="form-label">标题 *</label>
                            <input type="text" class="form-control" id="editBlockTitle" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">内容 *</label>
                            <textarea class="form-control" id="editBlockContent" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">分组 *</label>
                            <select class="form-select" id="editBlockGroup" required>
                                <option value="">请选择分组</option>
                                <?php foreach ($groups as $group): ?>
                                    <option value="<?php echo $group['id']; ?>">
                                        <?php echo htmlspecialchars($group['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">尺寸</label>
                            <select class="form-select" id="editBlockSize">
                                <option value="small">小</option>
                                <option value="medium">中</option>
                                <option value="large">大</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">颜色</label>
                            <select class="form-select" id="editBlockColor">
                                <option value="gray">灰色</option>
                                <option value="blue">蓝色</option>
                                <option value="green">绿色</option>
                                <option value="yellow">黄色</option>
                                <option value="red">红色</option>
                            </select>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                            <button type="submit" class="btn btn-primary">保存</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 编辑分组
        function editGroup(id, name, color) {
            document.getElementById('editGroupId').value = id;
            document.getElementById('editGroupName').value = name;
            document.getElementById('editGroupColor').value = color;
            new bootstrap.Modal(document.getElementById('editGroupModal')).show();
        }

        // 删除分组
        function deleteGroup(id, name) {
            if (confirm('确定要删除分组 "' + name + " 吗？\n注意：此操作将同时删除该分组下的所有数据块。")) {
                if (confirm('再次确认：删除操作不可恢复！')) {
                    document.getElementById('editGroupId').value = id;
                    document.getElementById('editGroupName').value = name;
                    document.getElementById('editGroupColor').value = '';
                    document.getElementById('editGroupColor').disabled = true;
                    new bootstrap.Modal(document.getElementById('editGroupModal')).show();
                    
                    // 自动提交删除
                    setTimeout(() => {
                        document.getElementById('editGroupForm').submit();
                    }, 500);
                }
            }
        }

        // 编辑数据块
        function editBlock(id, title, content, groupId, size, color) {
            document.getElementById('editBlockId').value = id;
            document.getElementById('editBlockTitle').value = title;
            document.getElementById('editBlockContent').value = content;
            document.getElementById('editBlockGroup').value = groupId;
            document.getElementById('editBlockSize').value = size;
            document.getElementById('editBlockColor').value = color;
            new bootstrap.Modal(document.getElementById('editBlockModal')).show();
        }

        // 删除数据块
        function deleteBlock(id, title) {
            if (confirm('确定要删除数据块 "' + title + '" 吗？')) {
                if (confirm('再次确认：删除操作不可恢复！')) {
                    document.getElementById('editBlockId').value = id;
                    document.getElementById('editBlockTitle').value = title;
                    document.getElementById('editBlockContent').value = '';
                    document.getElementById('editBlockGroup').value = '';
                    document.getElementById('editBlockSize').value = 'small';
                    document.getElementById('editBlockColor').value = 'gray';
                    new bootstrap.Modal(document.getElementById('editBlockModal')).show();
                    
                    // 自动提交删除
                    setTimeout(() => {
                        document.getElementById('editBlockForm').submit();
                    }, 500);
                }
            }
        }
    </script>
</body>
</html>
