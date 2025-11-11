<?php
include '../../config/config.php';

if(session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is admin or owner
if(!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['admin', 'owner'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Handle form submissions
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['action'])) {
        $action = $_POST['action'];

        if($action == 'add_todo') {
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $priority = $_POST['priority'];
            $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;

            if(!empty($title)) {
                $stmt = $pdo->prepare("INSERT INTO todos (user_id, title, description, priority, due_date) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $title, $description, $priority, $due_date]);
                $_SESSION['success'] = "Todo added successfully!";
            }
        } elseif($action == 'update_status') {
            $todo_id = $_POST['todo_id'];
            $status = $_POST['status'];

            // Check if todo belongs to current user
            $check_stmt = $pdo->prepare("SELECT id FROM todos WHERE id = ? AND user_id = ?");
            $check_stmt->execute([$todo_id, $user_id]);

            if($check_stmt->fetch()) {
                $stmt = $pdo->prepare("UPDATE todos SET status = ? WHERE id = ?");
                $stmt->execute([$status, $todo_id]);
                $_SESSION['success'] = "Todo status updated!";
            }
        } elseif($action == 'delete_todo') {
            $todo_id = $_POST['todo_id'];

            // Check if todo belongs to current user
            $check_stmt = $pdo->prepare("SELECT id FROM todos WHERE id = ? AND user_id = ?");
            $check_stmt->execute([$todo_id, $user_id]);

            if($check_stmt->fetch()) {
                $stmt = $pdo->prepare("DELETE FROM todos WHERE id = ?");
                $stmt->execute([$todo_id]);
                $_SESSION['success'] = "Todo deleted successfully!";
            }
        }
    }

    header("Location: todos.php");
    exit();
}

// Get todos for current user
$stmt = $pdo->prepare("SELECT * FROM todos WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$todos = $stmt->fetchAll();

// Get todo statistics
$stats_stmt = $pdo->prepare("
    SELECT
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
    FROM todos WHERE user_id = ?
");
$stats_stmt->execute([$user_id]);
$stats = $stats_stmt->fetch();

$page_title = "Todo List";
include 'header.php';
?>

<style>
.todo-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.todo-card:hover {
    transform: translateY(-2px);
}

.priority-high {
    border-left: 4px solid #dc3545;
}

.priority-medium {
    border-left: 4px solid #ffc107;
}

.priority-low {
    border-left: 4px solid #28a745;
}

.status-badge {
    font-size: 0.8rem;
}

.todo-form {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    padding: 2rem;
    color: white;
}

.btn-add-todo {
    background: rgba(255,255,255,0.2);
    border: 2px solid white;
    color: white;
    transition: all 0.3s ease;
}

.btn-add-todo:hover {
    background: white;
    color: #667eea;
}

.stats-card {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    border-radius: 15px;
}

.todo-item {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.todo-actions {
    opacity: 0;
    transition: opacity 0.3s ease;
}

.todo-item:hover .todo-actions {
    opacity: 1;
}
</style>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-tasks me-2"></i>Todo List</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTodoModal">
                    <i class="fas fa-plus me-2"></i>Add Todo
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <h3><?php echo $stats['total']; ?></h3>
                    <p class="mb-0">Total Tasks</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <h3><?php echo $stats['pending']; ?></h3>
                    <p class="mb-0">Pending</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <h3><?php echo $stats['in_progress']; ?></h3>
                    <p class="mb-0">In Progress</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <h3><?php echo $stats['completed']; ?></h3>
                    <p class="mb-0">Completed</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <!-- Todo List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <?php if(empty($todos)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">No todos yet</h4>
                            <p class="text-muted">Create your first todo item to get started!</p>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach($todos as $todo): ?>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="todo-item priority-<?php echo $todo['priority']; ?>">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-0"><?php echo htmlspecialchars($todo['title']); ?></h6>
                                            <div class="todo-actions">
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="delete_todo">
                                                    <input type="hidden" name="todo_id" value="<?php echo $todo['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                            onclick="return confirm('Are you sure you want to delete this todo?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>

                                        <?php if(!empty($todo['description'])): ?>
                                            <p class="text-muted small mb-2"><?php echo htmlspecialchars($todo['description']); ?></p>
                                        <?php endif; ?>

                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="badge bg-<?php
                                                    echo $todo['status'] == 'completed' ? 'success' :
                                                         ($todo['status'] == 'in_progress' ? 'warning' : 'secondary');
                                                ?> status-badge">
                                                    <?php echo ucfirst(str_replace('_', ' ', $todo['status'])); ?>
                                                </span>
                                                <span class="badge bg-<?php
                                                    echo $todo['priority'] == 'high' ? 'danger' :
                                                         ($todo['priority'] == 'medium' ? 'warning' : 'success');
                                                ?> ms-1">
                                                    <?php echo ucfirst($todo['priority']); ?>
                                                </span>
                                            </div>

                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="todo_id" value="<?php echo $todo['id']; ?>">
                                                <select name="status" class="form-select form-select-sm d-inline-block w-auto"
                                                        onchange="this.form.submit()">
                                                    <option value="pending" <?php echo $todo['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="in_progress" <?php echo $todo['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                                    <option value="completed" <?php echo $todo['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                </select>
                                            </form>
                                        </div>

                                        <?php if(!empty($todo['due_date'])): ?>
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar me-1"></i>Due: <?php echo date('M d, Y', strtotime($todo['due_date'])); ?>
                                                </small>
                                            </div>
                                        <?php endif; ?>

                                        <div class="mt-2">
                                            <small class="text-muted">
                                                Created: <?php echo date('M d, Y', strtotime($todo['created_at'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Todo Modal -->
<div class="modal fade" id="addTodoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Add New Todo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_todo">

                    <div class="mb-3">
                        <label for="title" class="form-label">Title *</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="priority" class="form-label">Priority</label>
                                <select class="form-select" id="priority" name="priority">
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="due_date" class="form-label">Due Date</label>
                                <input type="date" class="form-control" id="due_date" name="due_date">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Todo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Set minimum date for due date to today
document.getElementById('due_date').min = new Date().toISOString().split('T')[0];
</script>

<?php include 'footer.php'; ?>
