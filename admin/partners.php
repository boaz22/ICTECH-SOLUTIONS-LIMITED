<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

Auth::requireAdmin();
$db = Database::getInstance();
$errors = [];

$editId = getParam('edit', null, FILTER_VALIDATE_INT);
$partner = $editId ? $db->getRow('SELECT * FROM partners WHERE id = ?', [$editId]) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCSRFToken(postParam('csrf_token'))) {
        $errors[] = 'Security validation failed.';
    }

    $action = postParam('action');
    $partnerId = postParam('partner_id', null, FILTER_VALIDATE_INT);
    $name = trim(postParam('name'));
    $website = trim(postParam('website'));
    $status = postParam('status', 'active');

    if ($action === 'delete' && $partnerId) {
        $db->delete('partners', 'id = ?', [$partnerId]);
        header('Location: partners.php');
        exit;
    }

    if ($name === '') {
        $errors[] = 'Partner name is required.';
    }

    if (!$errors) {
        $logo = $partner['logo'] ?? '';
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload = uploadFile($_FILES['logo'], __DIR__ . '/../assets/images/partners/');
            if ($upload['success']) {
                $logo = $upload['filename'];
            } else {
                $errors[] = implode(', ', $upload['errors']);
            }
        }
    }

    if (!$errors) {
        $data = [
            'name' => $name,
            'website' => $website,
            'status' => in_array($status, ['active', 'inactive'], true) ? $status : 'active',
            'logo' => $logo,
        ];

        if ($partnerId) {
            $db->update('partners', $data, 'id = ?', [$partnerId]);
        } else {
            $db->insert('partners', $data);
        }

        header('Location: partners.php?success=1');
        exit;
    }
}

$partners = $db->getAll('SELECT * FROM partners ORDER BY name ASC');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Partner Management | ICTECH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=20260902">
</head>
<body class="admin-shell">
<main class="container py-5">
    <div class="admin-page-header d-flex justify-content-between align-items-center gap-3 flex-wrap">
        <div>
            <p class="eyebrow mb-2">ADMIN CONSOLE</p>
            <h1>Partner Management</h1>
        </div>
        <a href="index.php" class="btn btn-outline-primary">Back to dashboard</a>
    </div>

    <?php if (getParam('success')): ?>
        <div class="alert alert-success">Partner saved successfully.</div>
    <?php endif; ?>

    <?php foreach ($errors as $error): ?>
        <div class="alert alert-danger"><?php echo h($error); ?></div>
    <?php endforeach; ?>

    <section class="card mb-5">
        <div class="card-body">
            <h2 class="h4 mb-3"><?php echo $partner ? 'Edit partner' : 'Add partner'; ?></h2>
            <form method="post" enctype="multipart/form-data" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?php echo h(Auth::generateCSRFToken()); ?>">
                <input type="hidden" name="partner_id" value="<?php echo (int) ($partner['id'] ?? 0); ?>">
                <div class="col-md-5">
                    <label class="form-label">Partner name</label>
                    <input type="text" class="form-control" name="name" value="<?php echo h($partner['name'] ?? ''); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Website</label>
                    <input type="url" class="form-control" name="website" value="<?php echo h($partner['website'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?php echo ($partner['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($partner['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Logo</label>
                    <input type="file" class="form-control" name="logo" accept="image/*">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Save partner</button>
                    <?php if ($partner): ?>
                        <a href="partners.php" class="btn btn-link">Cancel</a>
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
                        <th>Website</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($partners as $row): ?>
                        <tr>
                            <td><?php echo h($row['name']); ?></td>
                            <td><?php echo $row['website'] ? '<a href="' . h($row['website']) . '" target="_blank">' . h($row['website']) . '</a>' : '—'; ?></td>
                            <td><span class="badge text-bg-<?php echo $row['status'] === 'active' ? 'success' : 'secondary'; ?>"><?php echo h($row['status']); ?></span></td>
                            <td class="text-nowrap">
                                <a href="partners.php?edit=<?php echo (int) $row['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="post" class="d-inline-block">
                                    <input type="hidden" name="csrf_token" value="<?php echo h(Auth::generateCSRFToken()); ?>">
                                    <input type="hidden" name="partner_id" value="<?php echo (int) $row['id']; ?>">
                                    <button type="submit" name="action" value="delete" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this partner?')">Delete</button>
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
</body>
</html>
