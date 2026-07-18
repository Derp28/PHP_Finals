<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once "config.php";

if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    echo '<div class="admin-message error">You do not have permission to view this section.</div>';
    return;
}

$message = "";
$messageType = "success";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_user'])) {
        $userId = (int) $_POST['user_id'];
        if ($userId !== (int) $_SESSION['user_id']) {
            $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $userId);
            mysqli_stmt_execute($stmt);
            $message = "User deleted.";
        } else {
            $message = "You cannot delete your own admin account.";
            $messageType = "error";
        }
    } elseif (isset($_POST['create_word'])) {
        $word = trim($_POST['word']);
        if ($word !== '' && strlen($word) === 10) {
            $normalizedWord = strtolower($word);
            $stmt = mysqli_prepare($conn, "INSERT INTO words (word) VALUES (?)");
            mysqli_stmt_bind_param($stmt, 's', $normalizedWord);
            mysqli_stmt_execute($stmt);
            $message = "Word created.";
        } else {
            $message = "Please enter a 10-letter word.";
            $messageType = "error";
        }
    } elseif (isset($_POST['update_word'])) {
        $wordId = (int) $_POST['word_id'];
        $word = trim($_POST['word']);
        if ($word !== '' && strlen($word) === 10) {
            $normalizedWord = strtolower($word);
            $stmt = mysqli_prepare($conn, "UPDATE words SET word = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'si', $normalizedWord, $wordId);
            mysqli_stmt_execute($stmt);
            $message = "Word updated.";
        } else {
            $message = "Please enter a 10-letter word.";
            $messageType = "error";
        }
    } elseif (isset($_POST['delete_word'])) {
        $wordId = (int) $_POST['word_id'];
        $stmt = mysqli_prepare($conn, "DELETE FROM words WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $wordId);
        mysqli_stmt_execute($stmt);
        $message = "Word deleted.";
    }
}

// --- Pagination Logic for Words ---
$wordsPerPage = 50; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $wordsPerPage;

// Get total count to calculate total pages
$totalWordsQuery = mysqli_query($conn, "SELECT COUNT(*) as count FROM words");
$totalWordsRow = mysqli_fetch_assoc($totalWordsQuery);
$totalWords = $totalWordsRow['count'];
$totalPages = ceil($totalWords / $wordsPerPage);

// Fetch only the current page's words
$usersResult = mysqli_query($conn, "SELECT id, username, is_admin, created_at FROM users ORDER BY username ASC");
// --- Alphabetical Filter Logic for Words ---
$letters = range('A', 'Z');
// Check if a valid letter is in the URL, otherwise default to 'A'
$currentLetter = isset($_GET['letter']) && in_array(strtoupper($_GET['letter']), $letters) ? strtoupper($_GET['letter']) : 'A';

// Fetch users exactly as before
$usersResult = mysqli_query($conn, "SELECT id, username, is_admin, created_at FROM users ORDER BY username ASC");

// Fetch only words starting with the selected letter
$likePattern = strtolower($currentLetter) . '%';
$stmtWords = mysqli_prepare($conn, "SELECT id, word FROM words WHERE word LIKE ? ORDER BY word ASC");
mysqli_stmt_bind_param($stmtWords, 's', $likePattern);
mysqli_stmt_execute($stmtWords);
$wordsResult = mysqli_stmt_get_result($stmtWords);

?>

<div class="admin-page">
    <h1>Admin Panel</h1>
    <p class="auth-subtitle">MANAGE USERS AND WORDS</p>

    <?php if ($message !== ''): ?>
        <div class="admin-message <?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="admin-card">
        <h2>Users</h2>
        <table class="admin-list">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Joined</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = mysqli_fetch_assoc($usersResult)) : ?>
                    <tr>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= (int) $user['is_admin'] === 1 ? 'Admin' : 'Player' ?></td>
                        <td><?= htmlspecialchars($user['created_at']) ?></td>
                        <td>
                            <?php if ((int) $user['id'] !== (int) $_SESSION['user_id']): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?= (int) $user['id'] ?>">
                                    <button type="submit" name="delete_user" class="admin-inline-btn admin-danger" onclick="return confirm('Delete this user?')">Delete</button>
                                </form>
                            <?php else: ?>
                                <span>Current user</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        </table> <!-- This is your existing closing table tag -->

    <!-- Add this new pagination block -->
    <div class="admin-pagination" style="margin-top: 15px; text-align: center;">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>" class="admin-inline-btn">Previous</a>
        <?php endif; ?>
        
        <span style="margin: 0 15px;">Page <?= $page ?> of <?= $totalPages ?></span>
        
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>" class="admin-inline-btn">Next</a>
        <?php endif; ?>
    </div>
    </div>

    <div class="admin-card">
        <h2>Create Word</h2>
        <form method="POST" class="admin-form">
            <input type="text" name="word" placeholder="Enter 10-letter word" required maxlength="10">
            <button type="submit" name="create_word">Create Word</button>
        </form>
    </div>

<div class="admin-card">
        <h2>Manage Words</h2>
        
        <!-- Add this A-Z navigation bar -->
        <div class="admin-alphabet-filter" style="margin-bottom: 20px; text-align: center; display: flex; flex-wrap: wrap; justify-content: center; gap: 5px;">
            <?php foreach ($letters as $letter): ?>
                <a href="?letter=<?= $letter ?>" 
                   class="admin-inline-btn" 
                   style="padding: 5px 10px; text-decoration: none; <?= $letter === $currentLetter ? 'background-color: #333; color: white; font-weight: bold;' : '' ?>">
                    <?= $letter ?>
                </a>
            <?php endforeach; ?>
        </div>
        <!-- End of A-Z navigation -->

        <table class="admin-list">
            <thead>
                <tr>
                    <th>Word</th>
                    <th>Update</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($word = mysqli_fetch_assoc($wordsResult)) : ?>
                    <tr>
                        <td><?= htmlspecialchars(strtoupper($word['word'])) ?></td>
                        <td>
                            <form method="POST" class="admin-form">
                                <input type="hidden" name="word_id" value="<?= (int) $word['id'] ?>">
                                <input type="text" name="word" value="<?= htmlspecialchars($word['word']) ?>" maxlength="10" required>
                                <button type="submit" name="update_word">Update</button>
                            </form>
                        </td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="word_id" value="<?= (int) $word['id'] ?>">
                                <button type="submit" name="delete_word" class="admin-inline-btn admin-danger" onclick="return confirm('Delete this word?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <p><a href="index.php" class="nav-btn">Back to Game</a></p>
</div>
