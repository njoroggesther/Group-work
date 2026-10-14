<?php
require_once 'db.php';
session_start();

$note = ['id' => null, 'title' => '', 'content' => ''];
$isEdit = false;
$errors = [];

if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT id, title, content FROM notes WHERE id = ?');
    $stmt->execute([(int)$_GET['id']]);
    $found = $stmt->fetch();
    if (!$found) {
        $_SESSION['flash'] = ['type' => 'warning', 'message' => 'That note could not be found.'];
        header('Location: index.php');
        exit;
    }
    $note = $found;
    $isEdit = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) && ctype_digit($_POST['id']) ? (int)$_POST['id'] : null;
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === '') {
        $errors[] = 'Please add a title.';
    }
    if ($content === '') {
        $errors[] = 'Please write some note content.';
    }

    if (!$errors) {
        if ($id) {
            $stmt = $pdo->prepare('UPDATE notes SET title = ?, content = ? WHERE id = ?');
            $stmt->execute([$title, $content, $id]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Note updated.'];
            header('Location: view_note.php?id=' . $id);
            exit;
        }

        $stmt = $pdo->prepare('INSERT INTO notes (title, content) VALUES (?, ?)');
        $stmt->execute([$title, $content]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Note saved.'];
        header('Location: index.php');
        exit;
    }

    $note = ['id' => $id, 'title' => $title, 'content' => $content];
    $isEdit = (bool)$id;
}

$pageTitle = $isEdit ? 'Edit note' : 'New note';
$extraCss = 'css/editor.css';
require 'header.php';
?>

<h1 class="mb-3"><?php echo $isEdit ? 'Edit note' : 'Write a note'; ?></h1>

<?php foreach ($errors as $error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endforeach; ?>

<form method="post" class="editor-card">
    <?php if ($note['id']): ?>
        <input type="hidden" name="id" value="<?php echo (int)$note['id']; ?>">
    <?php endif; ?>

    <div class="mb-3">
        <label for="title" class="form-label">Title</label>
        <input type="text" class="form-control" id="title" name="title" maxlength="255"
               value="<?php echo htmlspecialchars($note['title']); ?>" required>
    </div>

    <div class="mb-3">
        <label for="content" class="form-label">Note</label>
        <textarea class="form-control" id="content" name="content" rows="12" required><?php echo htmlspecialchars($note['content']); ?></textarea>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-accent"><?php echo $isEdit ? 'Save changes' : 'Save note'; ?></button>
        <a href="<?php echo $isEdit ? 'view_note.php?id=' . (int)$note['id'] : 'index.php'; ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>

<?php require 'footer.php'; ?>

