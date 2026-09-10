<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
Auth::requireAdmin();
$db = Database::getInstance();
$errors = [];
$message = '';
$editId = getParam('edit', null, FILTER_VALIDATE_INT);
$course = $editId ? $db->getRow('SELECT * FROM courses WHERE id = ?', [$editId]) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCSRFToken(postParam('csrf_token'))) $errors[] = 'Security validation failed.';
    $action = postParam('action');
    $courseId = postParam('course_id', null, FILTER_VALIDATE_INT);
    $title = trim(postParam('title'));
    $slug = trim(postParam('slug'));
    $description = trim(postParam('description'));
    $objectives = trim(postParam('objectives'));
    $requirements = trim(postParam('requirements'));
    $categoryId = postParam('category_id', null, FILTER_VALIDATE_INT);
    $price = (float) postParam('price', 0);
    $duration = trim(postParam('duration'));
    $status = postParam('status', 'draft');
    $isFeatured = postParam('is_featured') ? 1 : 0;

    if (!$errors && $action === 'archive' && $courseId) {
        $db->update('courses', ['status' => 'archived'], 'id = ?', [$courseId]);
        header('Location: courses.php?saved=1');
        exit;
    }

    if (!$title || !$slug || !$categoryId) $errors[] = 'Title, slug, and category are required.';
    if (!in_array($status, ['published', 'draft', 'archived'], true)) $errors[] = 'Invalid course status.';
    if ($price < 0) $errors[] = 'Price cannot be negative.';
    if (!$db->getRow('SELECT id FROM categories WHERE id = ?', [$categoryId])) $errors[] = 'Select a valid category.';
    $duplicate = $db->getRow('SELECT id FROM courses WHERE slug = ? AND id <> ?', [$slug, $courseId ?: 0]);
    if ($duplicate) $errors[] = 'That slug is already in use.';

    $image = $course['image'] ?? null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK || $_FILES['image']['size'] > 5242880) $errors[] = 'Image must be smaller than 5MB.';
        $mime = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($mime, $_FILES['image']['tmp_name']);
        finfo_close($mime);
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($extensions[$mimeType])) $errors[] = 'Use a JPG, PNG, or WebP image.';
        if (!$errors) {
            $image = 'course-' . bin2hex(random_bytes(8)) . '.' . $extensions[$mimeType];
            if (!move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../assets/images/' . $image)) $errors[] = 'The image could not be saved.';
        }
    }

    if (!$errors) {
        $data = ['title'=>$title, 'slug'=>$slug, 'description'=>$description, 'objectives'=>$objectives, 'requirements'=>$requirements, 'category_id'=>$categoryId, 'price'=>$price, 'duration'=>$duration, 'status'=>$status, 'is_featured'=>$isFeatured, 'image'=>$image];
        if ($courseId) $db->update('courses', $data, 'id = ?', [$courseId]); else $db->insert('courses', $data);
        header('Location: courses.php?saved=1');
        exit;
    }
}

$categories = getCategories();
$courses = $db->getAll('SELECT c.*, cat.name AS category_name FROM courses c JOIN categories cat ON cat.id = c.category_id ORDER BY c.created_at DESC');
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Course Management | ICTECH</title><link rel="stylesheet" href="../assets/css/style.css?v=20260919"><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></head><body class="admin-shell"><div class="admin-app"><aside class="admin-sidebar"><div class="admin-sidebar-inner"><div class="sidebar-brand"><img src="../assets/images/ictech-logo-transparent.png" alt="ICTECH Solutions"></div><div class="admin-user-box"><p class="name"><?php echo h(Auth::getCurrentUser()['name'] ?? 'Administrator'); ?></p><p class="email"><?php echo h(Auth::getCurrentUser()['email'] ?? ''); ?></p></div><nav><?php $page = basename($_SERVER['PHP_SELF']); ?><a href="index.php" class="<?php echo $page === 'index.php' ? 'active' : ''; ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a><a href="users.php" class="<?php echo $page === 'users.php' ? 'active' : ''; ?>"><i class="fas fa-users"></i> Users</a><a href="enrollments.php" class="<?php echo $page === 'enrollments.php' ? 'active' : ''; ?>"><i class="fas fa-clipboard-list"></i> Enrollments</a><a href="courses.php" class="<?php echo $page === 'courses.php' ? 'active' : ''; ?>"><i class="fas fa-book-open"></i> Courses</a><a href="categories.php" class="<?php echo $page === 'categories.php' ? 'active' : ''; ?>"><i class="fas fa-tags"></i> Categories</a><a href="payments.php" class="<?php echo $page === 'payments.php' ? 'active' : ''; ?>"><i class="fas fa-credit-card"></i> Payments</a><a href="reports.php" class="<?php echo $page === 'reports.php' ? 'active' : ''; ?>"><i class="fas fa-chart-bar"></i> Reports</a><a href="testimonials.php" class="<?php echo $page === 'testimonials.php' ? 'active' : ''; ?>"><i class="fas fa-comments"></i> Testimonials</a><a href="partners.php" class="<?php echo $page === 'partners.php' ? 'active' : ''; ?>"><i class="fas fa-handshake"></i> Partners</a><a href="contact-messages.php" class="<?php echo $page === 'contact-messages.php' ? 'active' : ''; ?>"><i class="fas fa-envelope"></i> Messages</a><a href="settings.php" class="<?php echo $page === 'settings.php' ? 'active' : ''; ?>"><i class="fas fa-sliders-h"></i> Settings</a></nav><div class="sidebar-footer"><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></div></div></aside><div class="admin-content"><header class="admin-topbar"><div class="admin-topbar-inner"><div class="brand-mark"><i class="fas fa-shield-alt"></i> Admin Console</div><div class="d-flex align-items-center gap-2"><div class="admin-user-chip"><i class="fas fa-user-circle"></i> <?php echo h(Auth::getCurrentUser()['name'] ?? 'Admin'); ?></div><a href="../logout.php" class="btn btn-sm btn-outline-primary"><i class="fas fa-sign-out-alt"></i> Logout</a></div></div></header><div class="admin-content-body"><main class="container-fluid px-0"><div class="d-flex justify-content-between align-items-center mb-4"><div><p class="text-secondary mb-1">ADMIN CONSOLE</p><h1>Course Management</h1></div><a href="index.php" class="btn btn-outline-primary">Back to Dashboard</a></div><?php if (getParam('saved')): ?><div class="alert alert-success">Course changes saved.</div><?php endif; ?><?php foreach ($errors as $error): ?><div class="alert alert-danger"><?php echo h($error); ?></div><?php endforeach; ?><section class="card mb-5"><div class="card-body"><h2 class="h4"><?php echo $course ? 'Edit Course' : 'Add Course'; ?></h2><form method="post" enctype="multipart/form-data" class="row g-3"><input type="hidden" name="csrf_token" value="<?php echo h(Auth::generateCSRFToken()); ?>"><input type="hidden" name="course_id" value="<?php echo (int) ($course['id'] ?? 0); ?>"><div class="col-md-8"><label class="form-label">Course title</label><input class="form-control" name="title" value="<?php echo h($course['title'] ?? ''); ?>" required></div><div class="col-md-4"><label class="form-label">URL slug</label><input class="form-control" name="slug" value="<?php echo h($course['slug'] ?? ''); ?>" required></div><div class="col-md-6"><label class="form-label">Category</label><select class="form-select" name="category_id" required><option value="">Select category</option><?php foreach ($categories as $category): ?><option value="<?php echo (int) $category['id']; ?>" <?php echo (($course['category_id'] ?? '') == $category['id']) ? 'selected' : ''; ?>><?php echo h($category['name']); ?></option><?php endforeach; ?></select></div><div class="col-md-3"><label class="form-label">Price (KES)</label><input class="form-control" type="number" min="0" step="0.01" name="price" value="<?php echo h($course['price'] ?? '0'); ?>"></div><div class="col-md-3"><label class="form-label">Duration</label><input class="form-control" name="duration" value="<?php echo h($course['duration'] ?? ''); ?>" placeholder="8 weeks"></div><div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3"><?php echo h($course['description'] ?? ''); ?></textarea></div><div class="col-md-6"><label class="form-label">Learning objectives</label><textarea class="form-control" name="objectives" rows="3"><?php echo h($course['objectives'] ?? ''); ?></textarea></div><div class="col-md-6"><label class="form-label">Requirements</label><textarea class="form-control" name="requirements" rows="3"><?php echo h($course['requirements'] ?? ''); ?></textarea></div><div class="col-md-6"><label class="form-label">Course image</label><input class="form-control" type="file" name="image" accept="image/jpeg,image/png,image/webp"></div><div class="col-md-3"><label class="form-label">Visibility</label><select class="form-select" name="status"><option value="draft" <?php echo (($course['status'] ?? '') === 'draft') ? 'selected' : ''; ?>>Draft</option><option value="published" <?php echo (($course['status'] ?? '') === 'published') ? 'selected' : ''; ?>>Published</option><option value="archived" <?php echo (($course['status'] ?? '') === 'archived') ? 'selected' : ''; ?>>Archived</option></select></div><div class="col-md-3 d-flex align-items-end"><label class="form-check"><input class="form-check-input" type="checkbox" name="is_featured" value="1" <?php echo !empty($course['is_featured']) ? 'checked' : ''; ?>> Featured course</label></div><div class="col-12"><button class="btn btn-primary" name="action" value="save">Save Course</button><?php if ($course): ?><a href="courses.php" class="btn btn-link">Cancel edit</a><?php endif; ?></div></form></div></section><div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h4 mb-0">All Courses</h2><span class="text-muted"><?php echo count($courses); ?> records</span></div><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Course</th><th>Category</th><th>Price</th><th>Status</th><th>Featured</th><th></th></tr></thead><tbody><?php foreach ($courses as $item): ?><tr><td><?php echo h($item['title']); ?></td><td><?php echo h($item['category_name']); ?></td><td>KES <?php echo number_format((float) $item['price'], 2); ?></td><td><span class="badge text-bg-<?php echo $item['status'] === 'published' ? 'success' : ($item['status'] === 'draft' ? 'warning' : 'secondary'); ?>"><?php echo h($item['status']); ?></span></td><td><?php echo $item['is_featured'] ? 'Yes' : 'No'; ?></td><td class="text-nowrap"><a href="courses.php?edit=<?php echo (int) $item['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a><form method="post" class="d-inline"><input type="hidden" name="csrf_token" value="<?php echo h(Auth::generateCSRFToken()); ?>"><input type="hidden" name="course_id" value="<?php echo (int) $item['id']; ?>"><button name="action" value="archive" class="btn btn-sm btn-outline-secondary">Archive</button></form></td></tr><?php endforeach; ?></tbody></table></div></main></div></div></div></body></html>
