<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

Auth::requireAdmin();
$db = Database::getInstance();
$errors = [];
$message = '';

$editId = getParam('edit', null, FILTER_VALIDATE_INT);
$category = $editId ? $db->getRow('SELECT * FROM categories WHERE id = ?', [$editId]) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCSRFToken(postParam('csrf_token'))) {
        $errors[] = 'Security validation failed.';
    }

    $action = postParam('action');
    $categoryId = postParam('category_id', null, FILTER_VALIDATE_INT);
    $name = trim(postParam('name'));
    $slug = trim(postParam('slug'));

    if (!$errors && $action === 'delete' && $categoryId) {
        $db->delete('categories', 'id = ?', [$categoryId]);
        header('Location: categories.php');
        exit;
    }

    if ($name === '') {
        $errors[] = 'Category name is required.';
    }

    $slug = $slug !== '' ? $slug : generateSlug($name);
    if ($slug === '') {
        $errors[] = 'Category slug could not be generated.';
    }

    if (!$errors) {
        $duplicate = $db->getRow('SELECT id FROM categories WHERE slug = ? AND id <> ?', [$slug, $categoryId ?: 0]);
        if ($duplicate) {
            $errors[] = 'A category with that slug already exists.';
        }
    }

    if (!$errors) {
        $data = ['name' => $name, 'slug' => $slug];
        if ($categoryId) {
            $db->update('categories', $data, 'id = ?', [$categoryId]);
            $message = 'Category updated successfully.';
        } else {
            $db->insert('categories', $data);
            $message = 'Category added successfully.';
        }
        header('Location: categories.php?success=1');
        exit;
    }
}

$categories = $db->getAll(
    'SELECT c.*, COUNT(cr.id) AS course_count FROM categories c LEFT JOIN courses cr ON cr.category_id = c.id GROUP BY c.id ORDER BY c.name ASC'
);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Category Management | ICTECH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=20260919">
</head>
<body class="admin-shell">
<div class="admin-app">
    <aside class="admin-sidebar">
        <div class="admin-sidebar-inner">
            <div class="sidebar-brand"><img src="../assets/images/ictech-logo-transparent.png" alt="ICTECH Solutions"></div>
            <div class="admin-user-box"><p class="name"><?php echo h(Auth::getCurrentUser()['name'] ?? 'Administrator'); ?></p><p class="email"><?php echo h(Auth::getCurrentUser()['email'] ?? ''); ?></p></div>
            <nav>
                <?php $page = basename($_SERVER['PHP_SELF']); ?>
                <a href="index.php" class="<?php echo $page === 'index.php' ? 'active' : ''; ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="users.php" class="<?php echo $page === 'users.php' ? 'active' : ''; ?>"><i class="fas fa-users"></i> Users</a>
                <a href="enrollments.php" class="<?php echo $page === 'enrollments.php' ? 'active' : ''; ?>"><i class="fas fa-clipboard-list"></i> Enrollments</a>
                <a href="courses.php" class="<?php echo $page === 'courses.php' ? 'active' : ''; ?>"><i class="fas fa-book-open"></i> Courses</a>
                <a href="categories.php" class="<?php echo $page === 'categories.php' ? 'active' : ''; ?>"><i class="fas fa-tags"></i> Categories</a>
                <a href="payments.php" class="<?php echo $page === 'payments.php' ? 'active' : ''; ?>"><i class="fas fa-credit-card"></i> Payments</a>
                <a href="reports.php" class="<?php echo $page === 'reports.php' ? 'active' : ''; ?>"><i class="fas fa-chart-bar"></i> Reports</a>
                <a href="testimonials.php" class="<?php echo $page === 'testimonials.php' ? 'active' : ''; ?>"><i class="fas fa-comments"></i> Testimonials</a>
                <a href="partners.php" class="<?php echo $page === 'partners.php' ? 'active' : ''; ?>"><i class="fas fa-handshake"></i> Partners</a>
                <a href="contact-messages.php" class="<?php echo $page === 'contact-messages.php' ? 'active' : ''; ?>"><i class="fas fa-envelope"></i> Messages</a>
                <a href="settings.php" class="<?php echo $page === 'settings.php' ? 'active' : ''; ?>"><i class="fas fa-sliders-h"></i> Settings</a>
            </nav>
            <div class="sidebar-footer"><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
        </div>
    </aside>
    <div class="admin-content">
        <header class="admin-topbar"><div class="admin-topbar-inner"><div class="brand-mark"><i class="fas fa-shield-alt"></i> Admin Console</div><div class="admin-user-chip"><i class="fas fa-user-circle"></i> <?php echo h(Auth::getCurrentUser()['name'] ?? 'Admin'); ?></div></div></header>
        <div class="admin-content-body">
            <main class="container-fluid px-0">
                <div class="admin-page-header d-flex justify-content-between align-items-center gap-3 flex-wrap">
                    <div>
                        <p class="eyebrow mb-2">ADMIN CONSOLE</p>
                        <h1>Category Management</h1>
                    </div>
                    <a href="index.php" class="btn btn-outline-primary">Back to dashboard</a>
                </div>

    <?php if (getParam('success')): ?>
        <div class="alert alert-success">Category saved successfully.</div>
    <?php endif; ?>

    <?php foreach ($errors as $error): ?>
        <div class="alert alert-danger"><?php echo h($error); ?></div>
    <?php endforeach; ?>

    <section class="card mb-5">
        <div class="card-body">
            <h2 class="h4 mb-3"><?php echo $category ? 'Edit category' : 'Add category'; ?></h2>
            <form method="post" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?php echo h(Auth::generateCSRFToken()); ?>">
                <input type="hidden" name="category_id" value="<?php echo (int) ($category['id'] ?? 0); ?>">
                <div class="col-md-8">
                    <label class="form-label">Category name</label>
                    <input type="text" class="form-control" name="name" value="<?php echo h($category['name'] ?? ''); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Slug</label>
                    <input type="text" class="form-control" name="slug" value="<?php echo h($category['slug'] ?? ''); ?>">
                </div>
                <div class="col-12">
                    <button type="submit" name="action" value="save" class="btn btn-primary">Save category</button>
                    <?php if ($category): ?>
                        <a href="categories.php" class="btn btn-link">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </section>

    <section class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h4 mb-0">Existing categories</h2>
                <span class="text-muted"><?php echo count($categories); ?> categories</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Courses</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($categories as $row): ?>
                        <tr>
                            <td><?php echo h($row['name']); ?></td>
                            <td><code><?php echo h($row['slug']); ?></code></td>
                            <td><?php echo (int) $row['course_count']; ?></td>
                            <td class="text-nowrap">
                                <a href="categories.php?edit=<?php echo (int) $row['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="post" class="d-inline-block">
                                    <input type="hidden" name="csrf_token" value="<?php echo h(Auth::generateCSRFToken()); ?>">
                                    <input type="hidden" name="category_id" value="<?php echo (int) $row['id']; ?>">
                                    <button type="submit" name="action" value="delete" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this category?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
            </main>
        </div>
    </div>
</div>
</body>
</html>
