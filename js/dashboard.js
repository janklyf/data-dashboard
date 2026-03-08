// 数据看板 JavaScript
// 创建时间：2026-03-08

// 全局变量
let groups = [];
let blocks = [];
let blockModal;
let groupModal;

// 页面加载完成后初始化
document.addEventListener('DOMContentLoaded', function() {
    // 初始化模态框
    blockModal = new bootstrap.Modal(document.getElementById('blockModal'));
    groupModal = new bootstrap.Modal(document.getElementById('groupModal'));

    // 加载数据
    loadGroups();
    loadBlocks();
});

// 加载分组列表
async function loadGroups() {
    try {
        const response = await fetch('api.php/groups');
        const result = await response.json();

        if (result.success) {
            groups = result.data;
            updateGroupFilter();
            updateGroupSelect();
        } else {
            alert('加载分组失败: ' + result.error);
        }
    } catch (error) {
        console.error('加载分组失败:', error);
        alert('加载分组失败，请检查网络连接');
    }
}

// 加载数据块列表
async function loadBlocks() {
    try {
        const response = await fetch('api.php/blocks');
        const result = await response.json();

        if (result.success) {
            blocks = result.data;
            renderBlocks();
        } else {
            alert('加载数据块失败: ' + result.error);
        }
    } catch (error) {
        console.error('加载数据块失败:', error);
        alert('加载数据块失败，请检查网络连接');
    }
}

// 更新分组筛选下拉框
function updateGroupFilter() {
    const filterSelect = document.getElementById('groupFilter');
    filterSelect.innerHTML = '<option value="">全部分组</option>';

    groups.forEach(group => {
        const option = document.createElement('option');
        option.value = group.id;
        option.textContent = group.name;
        filterSelect.appendChild(option);
    });
}

// 更新分组选择下拉框
function updateGroupSelect() {
    const select = document.getElementById('blockGroup');
    select.innerHTML = '<option value="">请选择分组</option>';

    groups.forEach(group => {
        const option = document.createElement('option');
        option.value = group.id;
        option.textContent = group.name;
        select.appendChild(option);
    });
}

// 渲染数据块
function renderBlocks(filteredBlocks = null) {
    const container = document.getElementById('blocksContainer');
    const emptyState = document.getElementById('emptyState');
    const blocksToRender = filteredBlocks || blocks;

    if (blocksToRender.length === 0) {
        container.innerHTML = '';
        emptyState.classList.remove('d-none');
        return;
    }

    emptyState.classList.add('d-none');
    container.innerHTML = '';

    blocksToRender.forEach(block => {
        const col = document.createElement('div');
        col.className = `col-12 col-md-6 col-lg-4 col-xl-3`;

        const sizeClass = `block-size-${block.size}`;
        const colorClass = `block-color-${block.color}`;

        col.innerHTML = `
            <div class="block ${sizeClass} ${colorClass}">
                <div>
                    <h5 class="block-title">${escapeHtml(block.title)}</h5>
                    <p class="block-content">${escapeHtml(block.content)}</p>
                </div>
                <div class="block-meta">
                    尺寸: ${getSizeName(block.size)} | 颜色: ${getColorName(block.color)}
                </div>
                <div class="block-actions">
                    <button class="btn btn-sm btn-light block-btn block-btn-edit" onclick="editBlock(${block.id})">
                        编辑
                    </button>
                    <button class="btn btn-sm btn-light block-btn block-btn-delete" onclick="deleteBlock(${block.id})">
                        删除
                    </button>
                </div>
            </div>
        `;

        container.appendChild(col);
    });
}

// 筛选数据块
function filterBlocks() {
    const filterValue = document.getElementById('groupFilter').value;

    if (!filterValue) {
        renderBlocks();
        return;
    }

    const filteredBlocks = blocks.filter(block => block.group_id == filterValue);
    renderBlocks(filteredBlocks);
}

// 重置筛选
function resetFilter() {
    document.getElementById('groupFilter').value = '';
    renderBlocks();
}

// 打开数据块模态框
function openBlockModal() {
    document.getElementById('blockForm').reset();
    document.getElementById('blockId').value = '';
    document.getElementById('blockModalTitle').textContent = '添加数据块';
    updateGroupSelect();
    blockModal.show();
}

// 编辑数据块
async function editBlock(id) {
    try {
        const response = await fetch(`api.php/blocks/${id}`);
        const result = await response.json();

        if (result.success) {
            const block = result.data;
            document.getElementById('blockId').value = block.id;
            document.getElementById('blockTitle').value = block.title;
            document.getElementById('blockContent').value = block.content;
            document.getElementById('blockGroup').value = block.group_id;
            document.getElementById('blockSize').value = block.size;
            document.getElementById('blockColor').value = block.color;
            document.getElementById('blockModalTitle').textContent = '编辑数据块';
            blockModal.show();
        } else {
            alert('加载数据块失败: ' + result.error);
        }
    } catch (error) {
        console.error('加载数据块失败:', error);
        alert('加载数据块失败，请检查网络连接');
    }
}

// 保存数据块
async function saveBlock() {
    const id = document.getElementById('blockId').value;
    const title = document.getElementById('blockTitle').value.trim();
    const content = document.getElementById('blockContent').value.trim();
    const groupId = document.getElementById('blockGroup').value;
    const size = document.getElementById('blockSize').value;
    const color = document.getElementById('blockColor').value;

    if (!title || !content || !groupId) {
        alert('请填写所有必填字段');
        return;
    }

    const blockData = {
        title,
        content,
        group_id: parseInt(groupId),
        size,
        color
    };

    try {
        let url = 'api.php/blocks';
        let method = 'POST';

        if (id) {
            url = `api.php/blocks/${id}`;
            method = 'PUT';
            blockData.id = parseInt(id);
        }

        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(blockData)
        });

        const result = await response.json();

        if (result.success) {
            blockModal.hide();
            loadBlocks();
            loadGroups(); // 重新加载分组列表
        } else {
            alert('保存失败: ' + result.error);
        }
    } catch (error) {
        console.error('保存数据块失败:', error);
        alert('保存数据块失败，请检查网络连接');
    }
}

// 删除数据块
async function deleteBlock(id) {
    if (!confirm('确定要删除这个数据块吗？')) {
        return;
    }

    try {
        const response = await fetch(`api.php/blocks/${id}`, {
            method: 'DELETE'
        });

        const result = await response.json();

        if (result.success) {
            loadBlocks();
        } else {
            alert('删除失败: ' + result.error);
        }
    } catch (error) {
        console.error('删除数据块失败:', error);
        alert('删除数据块失败，请检查网络连接');
    }
}

// 打开分组模态框
function openGroupModal() {
    document.getElementById('groupForm').reset();
    document.getElementById('groupId').value = '';
    document.getElementById('groupModalTitle').textContent = '添加分组';
    groupModal.show();
}

// 保存分组
async function saveGroup() {
    const id = document.getElementById('groupId').value;
    const name = document.getElementById('groupName').value.trim();

    if (!name) {
        alert('请输入分组名称');
        return;
    }

    const groupData = { name };

    try {
        let url = 'api.php/groups';
        let method = 'POST';

        if (id) {
            url = `api.php/groups/${id}`;
            method = 'DELETE'; // 注意：这里使用 DELETE 方法，需要修改 API
        }

        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(groupData)
        });

        const result = await response.json();

        if (result.success) {
            groupModal.hide();
            loadGroups();
        } else {
            alert('保存失败: ' + result.error);
        }
    } catch (error) {
        console.error('保存分组失败:', error);
        alert('保存分组失败，请检查网络连接');
    }
}

// 删除分组
async function deleteGroup(id) {
    if (!confirm('确定要删除这个分组吗？分组下的数据块将保留但不再关联。')) {
        return;
    }

    try {
        const response = await fetch(`api.php/groups/${id}`, {
            method: 'DELETE'
        });

        const result = await response.json();

        if (result.success) {
            loadGroups();
            loadBlocks();
        } else {
            alert('删除失败: ' + result.error);
        }
    } catch (error) {
        console.error('删除分组失败:', error);
        alert('删除分组失败，请检查网络连接');
    }
}

// 获取尺寸名称
function getSizeName(size) {
    const names = {
        'small': '小',
        'medium': '中',
        'large': '大'
    };
    return names[size] || size;
}

// 获取颜色名称
function getColorName(color) {
    const names = {
        'gray': '灰色',
        'blue': '蓝色',
        'green': '绿色',
        'yellow': '黄色',
        'red': '红色'
    };
    return names[color] || color;
}

// HTML 转义，防止 XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
