<?php
// 1. Ensure the session is started (if not already handled in index.php)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once "config.php";

// 2. Check if the user is actually logged in. If not, kick them back to login.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 3. Fetch all the user's information from the database
$stmt = mysqli_prepare($conn, "SELECT username, games_played, gambles_made, gambles_won, best_word, least_attempts, created_at FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
?>

<div class="profile-card">
    <h1><?php echo htmlspecialchars($user['username']); ?></h1>
    <div class="auth-subtitle">PLAYER DOSSIER</div>

    <div class="stat-grid">
        <!-- Best Game Stats -->
        <div class="stat-box highlight">
            <div class="stat-label">Best Word (Least Attempts)</div>
            <div class="stat-value">
                <?php echo htmlspecialchars($user['best_word'] ? $user['best_word'] : 'NONE'); ?> 
                <?php if ($user['least_attempts'] > 0) echo "(" . $user['least_attempts'] . " tries)"; ?>
            </div>
        </div>

        <!-- Total Games -->
        <div class="stat-box">
            <div class="stat-value"><?php echo $user['games_played']; ?></div>
            <div class="stat-label">Games Played</div>
        </div>

        <!-- Gambles -->
        <div class="stat-box">
            <div class="stat-value"><?php echo $user['gambles_made']; ?></div>
            <div class="stat-label">Gambles Made</div>
        </div>

        <!-- Gamble Wins -->
        <div class="stat-box">
            <div class="stat-value"><?php echo $user['gambles_won']; ?></div>
            <div class="stat-label">Gambles Won</div>
        </div>
    </div>

    <p style="color: #666; font-size: 0.8rem; margin-bottom: 20px;">
        Member since: <?php echo date("F j, Y", strtotime($user['created_at'])); ?>
    </p>

    <div>
        <!-- Close Modal / Return to Game -->
        <button type="button" class="nav-btn" onclick="document.getElementById('myModal3').style.display='none'">RETURN TO CASINO</button>
        
        <?php if (!empty($_SESSION['is_admin'])) { echo '<a href="admin.php" class="nav-btn">ADMIN PANEL</a>'; } ?>
        
        <!-- LOG OUT BUTTON -->
        <a href="logout.php" class="logout-btn">LOG OUT</a>
        
        <!-- DELETE ACCOUNT FORM -->
    </div>
</div>