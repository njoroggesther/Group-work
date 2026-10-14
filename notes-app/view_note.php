<?php
require_once 'db.php';
session_start();

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    $_SESSION['flash'] = ['type' => 'warning', 'message' => 'No note was selected.'];
    header('Location: index.php');
    exit;
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare('SELECT id, title, content, created_at, updated_at FROM notes WHERE id = ?');
$stmt->execute([$id]);
$note = $stmt->fetch();

if (!$note) {
    http_response_code(404);
    $pageTitle = 'Note not found';
    $extraCss = 'css/view.css';
    require 'header.php';
    echo '<div class="missing-note"><h1>Note not found</h1><p>This note does not exist or was deleted.</p><a href="index.php" class="btn btn-ink">Back to dashboard</a></div>';
    require 'footer.php';
    exit;
}

$pageTitle = $note['title'];
$extraCss = 'view.css';
require 'header.php';
?>

<article class="note-view">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
        <div>
            <h1 class="mb-2"><?php echo htmlspecialchars($note['title']); ?></h1>
            <p class="note-meta mb-0">
                Created <?php echo date('M j, Y g:i A', strtotime($note['created_at'])); ?>
                · Updated <?php echo date('M j, Y g:i A', strtotime($note['updated_at'])); ?>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="create_note.php?id=<?php echo (int)$note['id']; ?>" class="btn btn-ink">Edit</a>
            <form action="delete_note.php" method="post" onsubmit="return confirm('Delete this note? This cannot be undone.');">
                <input type="hidden" name="id" value="<?php echo (int)$note['id']; ?>">
                <button type="submit" class="btn btn-outline-danger">Delete</button>
            </form>
        </div>
    </div>
    <div class="note-body"><?php echo nl2br(htmlspecialchars($note['content'])); ?></div>
</article>

<?php require 'footer.php'; ?>