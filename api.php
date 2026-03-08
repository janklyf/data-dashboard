<?php
/**
 * 数据看板 API
 * 技术栈：PHP + SQLite3 + PDO
 * 创建时间：2026-03-08
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 处理 OPTIONS 请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 数据库连接
$dbFile = __DIR__ . '/dashboard.db';
try {
    $db = new PDO("sqlite:" . $dbFile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => '数据库连接失败: ' . $e->getMessage()]);
    exit;
}

// 获取请求方法
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/dashboard-php/api.php', '', $uri);
$path = trim($path, '/');

// 路由处理
if ($path === 'groups') {
    handleGroups($method, $db);
} elseif ($path === 'blocks') {
    handleBlocks($method, $db);
} else {
    http_response_code(404);
    echo json_encode(['error' => '接口不存在']);
}

/**
 * 处理分组接口
 */
function handleGroups($method, $db) {
    // 获取所有分组
    if ($method === 'GET') {
        $stmt = $db->query("SELECT * FROM groups ORDER BY id ASC");
        $groups = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $groups]);
    }
    // 添加分组
    elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $name = trim($data['name'] ?? '');

        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => '分组名称不能为空']);
            return;
        }

        try {
            $stmt = $db->prepare("INSERT INTO groups (name) VALUES (:name)");
            $stmt->execute([':name' => $name]);
            echo json_encode(['success' => true, 'data' => ['id' => $db->lastInsertId(), 'name' => $name]]);
        } catch (PDOException $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => '分组名称已存在']);
        }
    }
    // 删除分组
    elseif ($method === 'DELETE') {
        $id = $_GET['id'] ?? null;

        if (empty($id)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => '分组ID不能为空']);
            return;
        }

        try {
            $stmt = $db->prepare("DELETE FROM groups WHERE id = :id");
            $stmt->execute([':id' => $id]);
            echo json_encode(['success' => true, 'message' => '删除成功']);
        } catch (PDOException $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => '删除失败']);
        }
    } else {
        http_response_code(405);
        echo json_encode(['error' => '方法不允许']);
    }
}

/**
 * 处理数据块接口
 */
function handleBlocks($method, $db) {
    // 获取所有数据块
    if ($method === 'GET') {
        $groupId = $_GET['group_id'] ?? null;
        $sql = "SELECT b.*, g.name as group_name FROM blocks b LEFT JOIN groups g ON b.group_id = g.id";

        if ($groupId) {
            $sql .= " WHERE b.group_id = :group_id";
        }

        $sql .= " ORDER BY b.id ASC";

        if ($groupId) {
            $stmt = $db->prepare($sql);
            $stmt->execute([':group_id' => $groupId]);
        } else {
            $stmt = $db->query($sql);
        }

        $blocks = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $blocks]);
    }
    // 添加数据块
    elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $title = trim($data['title'] ?? '');
        $content = trim($data['content'] ?? '');
        $groupId = $data['group_id'] ?? null;
        $size = trim($data['size'] ?? 'small');
        $color = trim($data['color'] ?? 'gray');

        // 验证必填字段
        if (empty($title) || empty($content)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => '标题和内容不能为空']);
            return;
        }

        try {
            $stmt = $db->prepare("
                INSERT INTO blocks (group_id, title, content, size, color)
                VALUES (:group_id, :title, :content, :size, :color)
            ");
            $stmt->execute([
                ':group_id' => $groupId,
                ':title' => $title,
                ':content' => $content,
                ':size' => $size,
                ':color' => $color
            ]);
            echo json_encode(['success' => true, 'data' => [
                'id' => $db->lastInsertId(),
                'title' => $title,
                'content' => $content,
                'size' => $size,
                'color' => $color
            ]]);
        } catch (PDOException $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => '添加失败']);
        }
    }
    // 修改数据块
    elseif ($method === 'PUT') {
        $id = $_GET['id'] ?? null;

        if (empty($id)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => '数据块ID不能为空']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        $title = trim($data['title'] ?? '');
        $content = trim($data['content'] ?? '');
        $groupId = $data['group_id'] ?? null;
        $size = trim($data['size'] ?? 'small');
        $color = trim($data['color'] ?? 'gray');

        // 验证必填字段
        if (empty($title) || empty($content)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => '标题和内容不能为空']);
            return;
        }

        try {
            $sql = "UPDATE blocks SET title = :title, content = :content, group_id = :group_id, size = :size, color = :color, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':title' => $title,
                ':content' => $content,
                ':group_id' => $groupId,
                ':size' => $size,
                ':color' => $color,
                ':id' => $id
            ]);
            echo json_encode(['success' => true, 'message' => '更新成功']);
        } catch (PDOException $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => '更新失败']);
        }
    }
    // 删除数据块
    elseif ($method === 'DELETE') {
        $id = $_GET['id'] ?? null;

        if (empty($id)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => '数据块ID不能为空']);
            return;
        }

        try {
            $stmt = $db->prepare("DELETE FROM blocks WHERE id = :id");
            $stmt->execute([':id' => $id]);
            echo json_encode(['success' => true, 'message' => '删除成功']);
        } catch (PDOException $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => '删除失败']);
        }
    } else {
        http_response_code(405);
        echo json_encode(['error' => '方法不允许']);
    }
}
