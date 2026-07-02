<?php

include('../config/auth.php');
include('../config/db.php');
require_once('../config/notifications.php');

$page_title = 'Create Notification';
$success = '';
$error = '';

$user_id = (int) ($_SESSION['user']['id'] ?? 0);

if (!canCreate('notifications')) {
    permission_deny_and_exit();
}

$type_config = notification_type_config();
$quick_templates = NOTIFICATION_QUICK_TEMPLATES;

$classes_query = mysqli_query(
    $conn,
    "SELECT class_id, class_name, class_code
     FROM classes
     WHERE status = 'active'
     ORDER BY class_name ASC"
);

$students_query = mysqli_query(
    $conn,
    "SELECT s.student_id, u.full_name, c.class_code, sec.section_code
     FROM students s
     JOIN users u ON s.user_id = u.id
     LEFT JOIN classes c ON s.class_id = c.class_id
     LEFT JOIN sections sec ON s.section_id = sec.section_id
     ORDER BY u.full_name ASC"
);

$teachers_query = mysqli_query(
    $conn,
    "SELECT t.teacher_id, u.full_name
     FROM teachers t
     JOIN users u ON t.user_id = u.id
     ORDER BY u.full_name ASC"
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $type = trim($_POST['type'] ?? 'general');
    $targets = $_POST['targets'] ?? [];

    if ($title === '' || $message === '') {
        $error = 'Please fill in the title and message.';
    } elseif (!notification_is_valid_type($type)) {
        $error = 'Invalid notification type selected.';
    } elseif (empty(notification_parse_targets($targets))) {
        $error = 'Please select at least one recipient.';
    } else {
        $notification_id = notification_create($conn, [
            'title'      => $title,
            'message'    => $message,
            'type'       => $type,
            'created_by' => $user_id,
            'targets'    => $targets,
        ]);

        if ($notification_id) {
            header('Location: view.php?id=' . $notification_id . '&success=created');
            exit();
        }

        $error = 'Failed to create notification. Please try again.';
    }
}

$classes_count = mysqli_num_rows($classes_query);
$students_count = mysqli_num_rows($students_query);
$teachers_count = mysqli_num_rows($teachers_query);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Notification | MindMerge SmartCampus</title>
<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/layout.css">
<link rel="stylesheet" href="../assets/css/components.css">
<link rel="stylesheet" href="../assets/css/notifications.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<link rel="stylesheet" href="../../assets/css/material-theme.css">
</head>
<body>

<div class="app-layout">

<?php include('../partials/sidebar.php'); ?>

<div class="main-content">

<?php include('../partials/topbar.php'); ?>

<div class="page-content">

<div class="page-header">

<div>
<h1>Create Notification</h1>
<p>Compose and broadcast announcements to students, teachers, and parents.</p>
</div>

<a href="index.php" class="btn">
<i class="fa-solid fa-arrow-left"></i>
Back to Notifications
</a>

</div>

<?php if ($error !== '') { ?>
<div class="alert-banner error">
<i class="fa-solid fa-circle-exclamation"></i>
<?php echo htmlspecialchars($error); ?>
</div>
<?php } ?>

<div class="dashboard-grid">

<div class="dashboard-card stat-card">
<div class="stat-top">
<div class="card-icon"><i class="fa-solid fa-layer-group"></i></div>
<h3><?php echo (int) $classes_count; ?></h3>
</div>
<p>Active Classes</p>
</div>

<div class="dashboard-card stat-card">
<div class="stat-top">
<div class="card-icon"><i class="fa-solid fa-user-graduate"></i></div>
<h3><?php echo (int) $students_count; ?></h3>
</div>
<p>Students</p>
</div>

<div class="dashboard-card stat-card">
<div class="stat-top">
<div class="card-icon"><i class="fa-solid fa-chalkboard-user"></i></div>
<h3><?php echo (int) $teachers_count; ?></h3>
</div>
<p>Teachers</p>
</div>

</div>

<form method="POST" action="" id="createNotificationForm">

<div class="dashboard-section">

<div class="section-header">
<h2>Quick Templates</h2>
</div>

<p style="margin-bottom:16px;">Click a template to pre-fill title, message, and type.</p>

<div class="notif-template-grid" id="templateGrid">

<?php foreach ($quick_templates as $key => $template) {
    $tc = notification_type_config($template['type']);
?>
<button
type="button"
class="notif-template-card"
data-template="<?php echo htmlspecialchars($key); ?>"
data-title="<?php echo htmlspecialchars($template['title']); ?>"
data-message="<?php echo htmlspecialchars($template['message']); ?>"
data-type="<?php echo htmlspecialchars($template['type']); ?>"
>
<i class="fa-solid <?php echo htmlspecialchars($template['icon']); ?>" style="color:<?php echo $tc['color']; ?>"></i>
<h4><?php echo htmlspecialchars($template['name']); ?></h4>
</button>
<?php } ?>

</div>

</div>

<div class="notif-compose-grid">

<div>

<div class="dashboard-section">

<div class="section-header">
<h2>Notification Details</h2>
</div>

<div class="form-grid">

<div class="form-group">
<label class="form-label">Notification Type</label>
<select name="type" id="notificationType" class="form-select" required>
<?php foreach ($type_config as $key => $type) { ?>
<option value="<?php echo $key; ?>"><?php echo htmlspecialchars($type['label']); ?></option>
<?php } ?>
</select>
</div>

<div class="form-group">
<label class="form-label">Title</label>
<input type="text" name="title" id="notificationTitle" class="form-input" maxlength="200" placeholder="Enter notification title" required value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
</div>

</div>

<div class="form-group">
<label class="form-label">Message</label>
<textarea name="message" id="notificationMessage" class="form-textarea" rows="6" placeholder="Write your message here..." required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
</div>

</div>

<div class="dashboard-section">

<div class="section-header">
<h2>Recipients</h2>
</div>

<p style="margin-bottom:18px;">Select one or more audience groups. You can combine role, class, section, and individual targets.</p>

<div class="recipient-group">
<div class="recipient-group-title">Broadcast Groups</div>
<div class="recipient-chips">
<label class="recipient-chip">
<input type="checkbox" name="targets[]" value="role:all">
<i class="fa-solid fa-globe"></i> All Users
</label>
<label class="recipient-chip">
<input type="checkbox" name="targets[]" value="role:student">
<i class="fa-solid fa-user-graduate"></i> All Students
</label>
<label class="recipient-chip">
<input type="checkbox" name="targets[]" value="role:teacher">
<i class="fa-solid fa-chalkboard-user"></i> All Teachers
</label>
<label class="recipient-chip">
<input type="checkbox" name="targets[]" value="role:parent">
<i class="fa-solid fa-people-roof"></i> All Parents
</label>
<label class="recipient-chip">
<input type="checkbox" name="targets[]" value="role:admin">
<i class="fa-solid fa-user-shield"></i> All Admins
</label>
<label class="recipient-chip">
<input type="checkbox" name="targets[]" value="role:driver">
<i class="fa-solid fa-bus"></i>
All Drivers
</label>
</div>
</div>

<div class="recipient-group">
<div class="recipient-group-title">By Class</div>
<div class="recipient-chips">
<?php
mysqli_data_seek($classes_query, 0);
while ($class = mysqli_fetch_assoc($classes_query)) {
    $label = ($class['class_code'] ? $class['class_code'] . ' — ' : '') . $class['class_name'];
?>
<label class="recipient-chip">
<input type="checkbox" name="targets[]" value="class:<?php echo (int) $class['class_id']; ?>">
<i class="fa-solid fa-school"></i> <?php echo htmlspecialchars($label); ?>
</label>
<?php } ?>
</div>
</div>

<div class="recipient-group">
<div class="recipient-group-title">By Section</div>
<div class="form-group" style="margin-bottom:12px;">
<label class="form-label">Load sections for class</label>
<select id="classSelector" class="form-select">
<option value="">Select a class to load sections</option>
<?php
mysqli_data_seek($classes_query, 0);
while ($class = mysqli_fetch_assoc($classes_query)) {
    $label = ($class['class_code'] ? $class['class_code'] . ' — ' : '') . $class['class_name'];
?>
<option value="<?php echo (int) $class['class_id']; ?>"><?php echo htmlspecialchars($label); ?></option>
<?php } ?>
</select>
</div>
<div id="sectionsList" class="section-picker">
<p class="section-picker-empty">Select a class above to choose sections.</p>
</div>
</div>

<div class="recipient-group">
<div class="recipient-group-title">Specific People</div>
<div class="form-grid">
<div class="form-group">
<label class="form-label">Students</label>
<select name="targets[]" class="form-select" multiple size="5">
<?php
mysqli_data_seek($students_query, 0);
while ($student = mysqli_fetch_assoc($students_query)) {
    $meta = trim(($student['class_code'] ?? '') . ' ' . ($student['section_code'] ?? ''));
?>
<option value="student:<?php echo htmlspecialchars($student['student_id']); ?>">
<?php echo htmlspecialchars($student['full_name']); ?>
<?php echo $meta ? ' (' . htmlspecialchars($meta) . ')' : ''; ?>
</option>
<?php } ?>
</select>
<small style="color:var(--muted);font-size:12px;">Hold Ctrl (Windows) or Cmd (Mac) to select multiple.</small>
</div>
<div class="form-group">
<label class="form-label">Teachers</label>
<select name="targets[]" class="form-select" multiple size="5">
<?php
mysqli_data_seek($teachers_query, 0);
while ($teacher = mysqli_fetch_assoc($teachers_query)) {
?>
<option value="teacher:<?php echo htmlspecialchars($teacher['teacher_id']); ?>">
<?php echo htmlspecialchars($teacher['full_name']); ?> (<?php echo htmlspecialchars($teacher['teacher_id']); ?>)
</option>
<?php } ?>
</select>
</div>
</div>
</div>

</div>

</div>

<div class="dashboard-section" style="position:sticky;top:100px;">

<div class="section-header">
<h2>Live Preview</h2>
</div>

<div class="notif-preview" id="previewBox">

<div class="notif-preview-header">
<span class="notif-preview-badge" id="previewBadge">
<i class="fa-solid fa-bullhorn"></i>
<span id="previewBadgeText">General</span>
</span>
<span style="font-size:12px;color:var(--muted);">Preview</span>
</div>

<div class="notif-preview-title" id="previewTitle">Notification Title</div>
<div class="notif-preview-message" id="previewMessage">Your message will appear here as you type.</div>

<div class="notif-preview-meta">
<i class="fa-regular fa-clock"></i>
<span>Just now</span>
</div>

</div>

<div style="margin-top:20px;">
<button type="submit" class="btn btn-primary" style="width:100%;">
<i class="fa-solid fa-paper-plane"></i>
Send Notification
</button>
</div>

</div>

</div>

</form>

</div>

</div>

</div>

<script>
const TYPE_CONFIG = <?php echo json_encode($type_config, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

const titleInput = document.getElementById('notificationTitle');
const messageInput = document.getElementById('notificationMessage');
const typeSelect = document.getElementById('notificationType');
const previewBox = document.getElementById('previewBox');
const previewBadge = document.getElementById('previewBadge');
const previewBadgeText = document.getElementById('previewBadgeText');
const previewTitle = document.getElementById('previewTitle');
const previewMessage = document.getElementById('previewMessage');

function updatePreview() {
    const type = typeSelect.value;
    const config = TYPE_CONFIG[type] || TYPE_CONFIG.general;
    const title = titleInput.value.trim();
    const message = messageInput.value.trim();

    previewTitle.textContent = title || 'Notification Title';
    previewMessage.textContent = message || 'Your message will appear here as you type.';

    previewBadge.style.background = config.bg;
    previewBadge.style.color = config.color;
    previewBadge.querySelector('i').className = 'fa-solid ' + config.icon;
    previewBadgeText.textContent = config.label;

    previewBox.style.borderLeft = '5px solid ' + config.color;
    previewBox.style.background = config.bg;

    if (type === 'emergency') {
        previewBox.style.boxShadow = '0 0 0 1px rgba(220,38,38,.2)';
    } else {
        previewBox.style.boxShadow = 'none';
    }
}

document.querySelectorAll('.notif-template-card').forEach(card => {
    card.addEventListener('click', () => {
        document.querySelectorAll('.notif-template-card').forEach(c => c.classList.remove('active'));
        card.classList.add('active');
        titleInput.value = card.dataset.title || '';
        messageInput.value = card.dataset.message || '';
        typeSelect.value = card.dataset.type || 'general';
        updatePreview();
    });
});

titleInput.addEventListener('input', updatePreview);
messageInput.addEventListener('input', updatePreview);
typeSelect.addEventListener('change', updatePreview);

document.getElementById('classSelector').addEventListener('change', function () {
    const classId = this.value;
    const sectionBox = document.getElementById('sectionsList');

    if (!classId) {
        sectionBox.innerHTML = '<p class="section-picker-empty">Select a class above to choose sections.</p>';
        return;
    }

    sectionBox.innerHTML = '<p class="section-picker-empty"><i class="fa-solid fa-spinner fa-spin"></i> Loading sections...</p>';

    fetch('get_sections.php?class_id=' + encodeURIComponent(classId), {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(sections => {
        if (!sections.length) {
            sectionBox.innerHTML = '<p class="section-picker-empty">No active sections found for this class.</p>';
            return;
        }

        sectionBox.innerHTML = sections.map(section => `
            <label class="recipient-chip">
                <input type="checkbox" name="targets[]" value="section:${section.section_id}">
                <i class="fa-solid fa-layer-group"></i>
                ${section.section_code ? section.section_code + ' — ' : ''}${section.section_name}
            </label>
        `).join('');
    })
    .catch(() => {
        sectionBox.innerHTML = '<p class="section-picker-empty">Unable to load sections.</p>';
    });
});

updatePreview();
</script>

<script src="../assets/js/common.js"></script>

</body>
</html>
