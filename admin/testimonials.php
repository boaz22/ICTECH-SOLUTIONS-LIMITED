<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

Auth::requireAdmin();
$db = Database::getInstance();
$errors = [];

$editId = getParam('edit', null, FILTER_VALIDATE_INT);
$testimonial = $editId ? $db->getRow('SELECT * FROM testimonials WHERE id = ?', [$editId]) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCSRFToken(postParam('csrf_token'))) {
        $errors[] = 'Security validation failed.';
    }

    $action = postParam('action');
    $testimonialId = postParam('testimonial_id', null, FILTER_VALIDATE_INT);
    $name = trim(postParam('name'));
    $role = trim(postParam('role'));
    $message = trim(postParam('message'));
    $type = postParam('type', 'student');
    $isFeatured = postParam('is_featured') ? 1 : 0;

    if (!$errors && $action === 'delete' && $testimonialId) {
        $db->delete('testimonials', 'id = ?', [$testimonialId]);
        header('Location: testimonials.php');
        exit;
    }

    if ($name === '' || $message === '') {
        $errors[] = 'Name and message are required.';
    }

    if (!$errors) {
        $photo = $testimonial['photo'] ?? '';
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload = uploadFile($_FILES['photo'], __DIR__ . '/../assets/images/testimonials/');
            if ($upload['success']) {
                $photo = $upload['filename'];
            } else {
                $errors[] = implode(', ', $upload['errors']);
            }
        }
    }

    if (!$errors) {
        $data = [
            'name' => $name,
            'role' => $role,
            'message' => $message,
            'type' => in_array($type, ['student', 'trainer'], true) ? $type : 'student',
            'is_featured' => $isFeatured,
            'photo' => $photo,
        ];

        if ($testimonialId) {
            $db->update('testimonials', $data, 'id = ?', [$testimonialId]);
        } else {
            $db->insert('testimonials', $data);
        }

        header('Location: testimonials.php?success=1');
        exit;
    }
}

$testimonials = $db->getAll('SELECT * FROM testimonials ORDER BY created_at DESC');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="../assets/images/favicon-32.png">
    <link rel="apple-touch-icon" href="../assets/images/apple-touch-icon.png">
    <title>Testimonial Management | ICTECH</title>
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
                <a href="certificates.php" class="<?php echo $page === 'certificates.php' ? 'active' : ''; ?>"><i class="fas fa-certificate"></i> Certificates</a>
                <a href="payments.php" class="<?php echo $page === 'payments.php' ? 'active' : ''; ?>"><i class="fas fa-credit-card"></i> Payments</a>
                <a href="reports.php" class="<?php echo $page === 'reports.php' ? 'active' : ''; ?>"><i class="fas fa-chart-bar"></i> Reports</a>
                <a href="testimonials.php" class="<?php echo $page === 'testimonials.php' ? 'active' : ''; ?>"><i class="fas fa-comments"></i> Testimonials</a>
                <a href="partners.php" class="<?php echo $page === 'partners.php' ? 'active' : ''; ?>"><i class="fas fa-handshake"></i> Partners</a>
                <a href="contact-messages.php" class="<?php echo $page === 'contact-messages.php' ? 'active' : ''; ?>"><i class="fas fa-envelope"></i> Messages</a>
                <a href="settings.php" class="<?php echo $page === 'settings.php' ? 'active' : ''; ?>"><i class="fas fa-sliders-h"></i> Settings</a>
            </nav>
            <div class="sidebar-footer"><form method="post" action="../logout.php"><input type="hidden" name="csrf_token" value="<?php echo h(Auth::generateCSRFToken()); ?>"><button type="submit" class="sidebar-logout"><i class="fas fa-sign-out-alt"></i> Logout</button></form></div>
        </div>
    </aside>
    <div class="admin-content">
        <header class="admin-topbar"><div class="admin-topbar-inner"><div class="brand-mark"><i class="fas fa-shield-alt"></i> Admin Console</div><div class="d-flex align-items-center gap-2"><div class="admin-user-chip"><i class="fas fa-user-circle"></i> <?php echo h(Auth::getCurrentUser()['name'] ?? 'Admin'); ?></div><form method="post" action="../logout.php" class="d-inline"><input type="hidden" name="csrf_token" value="<?php echo h(Auth::generateCSRFToken()); ?>"><button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-sign-out-alt"></i> Logout</button></form></div></div></header>
        <div class="admin-content-body">
            <main class="container-fluid px-0">
    <div class="admin-page-header d-flex justify-content-between align-items-center gap-3 flex-wrap">
        <div>
            <p class="eyebrow mb-2">ADMIN CONSOLE</p>
            <h1>Testimonial Management</h1>
        </div>
        <a href="index.php" class="btn btn-outline-primary">Back to dashboard</a>
    </div>

    <?php if (getParam('success')): ?>
        <div class="alert alert-success">Testimonial saved successfully.</div>
    <?php endif; ?>

    <?php foreach ($errors as $error): ?>
        <div class="alert alert-danger"><?php echo h($error); ?></div>
    <?php endforeach; ?>

    <section class="card mb-5">
        <div class="card-body">
            <h2 class="h4 mb-3"><?php echo $testimonial ? 'Edit testimonial' : 'Add testimonial'; ?></h2>
            <form method="post" enctype="multipart/form-data" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?php echo h(Auth::generateCSRFToken()); ?>">
                <input type="hidden" name="testimonial_id" value="<?php echo (int) ($testimonial['id'] ?? 0); ?>">
                <div class="col-md-4">
                    <label class="form-label">Name</label>
                    <input type="text" class="form-control" name="name" value="<?php echo h($testimonial['name'] ?? ''); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Role</label>
                    <input type="text" class="form-control" name="role" value="<?php echo h($testimonial['role'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Type</label>
                    <select class="form-select" name="type">
                        <option value="student" <?php echo ($testimonial['type'] ?? 'student') === 'student' ? 'selected' : ''; ?>>Student</option>
                        <option value="trainer" <?php echo ($testimonial['type'] ?? '') === 'trainer' ? 'selected' : ''; ?>>Trainer</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Message</label>
                    <textarea class="form-control" name="message" rows="4" required><?php echo h($testimonial['message'] ?? ''); ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Photo</label>
                    <input type="file" class="form-control" name="photo" accept="image/*">
                </div>
                <div class="col-md-6 d-flex align-items-center">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="is_featured" value="1" <?php echo !empty($testimonial['is_featured']) ? 'checked' : ''; ?>>
                        <label class="form-check-label">Feature this testimonial</label>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Save testimonial</button>
                    <?php if ($testimonial): ?>
                        <a href="testimonials.php" class="btn btn-link">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </section>

    <section class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Type</th>
                        <th>Featured</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($testimonials as $row): ?>
                        <tr>
                            <td><?php echo h($row['name']); ?></td>
                            <td><?php echo h($row['role'] ?: '—'); ?></td>
                            <td><?php echo h($row['type']); ?></td>
                            <td><?php echo $row['is_featured'] ? 'Yes' : 'No'; ?></td>
                            <td class="text-nowrap">
                                <a href="testimonials.php?edit=<?php echo (int) $row['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="post" class="d-inline-block">
                                    <input type="hidden" name="csrf_token" value="<?php echo h(Auth::generateCSRFToken()); ?>">
                                    <input type="hidden" name="testimonial_id" value="<?php echo (int) $row['id']; ?>">
                                    <button type="submit" name="action" value="delete" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this testimonial?')">Delete</button>
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
