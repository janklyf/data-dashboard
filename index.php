<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>数据看板</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- 顶部导航栏 -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="3" y1="9" x2="21" y2="9"></line>
                    <line x1="9" y1="21" x2="9" y2="9"></line>
                </svg>
                数据看板
            </a>
            <div class="d-flex">
                <button class="btn btn-light me-2" onclick="openGroupModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    添加分组
                </button>
                <button class="btn btn-success" onclick="openBlockModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    添加数据块
                </button>
            </div>
        </div>
    </nav>

    <!-- 主内容区 -->
    <div class="container-fluid">
        <!-- 筛选栏 -->
        <div class="card mb-4">
            <div class="card-body">
                <form id="filterForm" class="row g-3">
                    <div class="col-md-4">
                        <label for="groupFilter" class="form-label">选择分组</label>
                        <select id="groupFilter" class="form-select" onchange="filterBlocks()">
                            <option value="">全部分组</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <button type="button" class="btn btn-outline-secondary" onclick="resetFilter()">重置筛选</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 数据块网格 -->
        <div id="blocksContainer" class="row g-3">
            <!-- 数据块将通过 JavaScript 动态加载 -->
        </div>

        <!-- 空状态提示 -->
        <div id="emptyState" class="text-center py-5 d-none">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#adb5bd" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="mb-3">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="3" y1="9" x2="21" y2="9"></line>
                <line x1="9" y1="21" x2="9" y2="9"></line>
            </svg>
            <h5 class="text-muted">暂无数据块</h5>
            <p class="text-muted">点击"添加数据块"按钮开始创建</p>
        </div>
    </div>

    <!-- 添加/编辑数据块模态框 -->
    <div class="modal fade" id="blockModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="blockModalTitle">添加数据块</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="blockForm">
                        <input type="hidden" id="blockId">
                        <div class="mb-3">
                            <label for="blockTitle" class="form-label">标题 *</label>
                            <input type="text" class="form-control" id="blockTitle" required>
                        </div>
                        <div class="mb-3">
                            <label for="blockContent" class="form-label">内容 *</label>
                            <textarea class="form-control" id="blockContent" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="blockGroup" class="form-label">分组 *</label>
                            <select class="form-select" id="blockGroup" required>
                                <option value="">请选择分组</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="blockSize" class="form-label">尺寸</label>
                            <select class="form-select" id="blockSize">
                                <option value="small">小</option>
                                <option value="medium">中</option>
                                <option value="large">大</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="blockColor" class="form-label">颜色</label>
                            <select class="form-select" id="blockColor">
                                <option value="gray">灰色</option>
                                <option value="blue">蓝色</option>
                                <option value="green">绿色</option>
                                <option value="yellow">黄色</option>
                                <option value="red">红色</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary" onclick="saveBlock()">保存</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 添加/编辑分组模态框 -->
    <div class="modal fade" id="groupModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="groupModalTitle">添加分组</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="groupForm">
                        <input type="hidden" id="groupId">
                        <div class="mb-3">
                            <label for="groupName" class="form-label">分组名称 *</label>
                            <input type="text" class="form-control" id="groupName" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary" onclick="saveGroup()">保存</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="js/dashboard.js"></script>
</body>
</html>
