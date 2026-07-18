<?php   
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
// All calculations occur at the top of index.php.
?>

<div>
    <h3>Your Card:</h3>
    <div class="card"><?php echo htmlspecialchars($_SESSION['player_card']); ?></div>
</div>

<?php if (!$_SESSION['game_over']): ?>
    <p>Will the dealer's card be higher or lower?</p>
    <form method="POST">
        <input type="hidden" name="hint_submitted" value="1">
        <button type="submit" name="choice" value="higher">Higher ⬆️</button>
        <button type="submit" name="choice" value="lower">Lower ⬇️</button>
    </form>
<?php else: ?>
    <div>
        <h3>Dealer's Card:</h3>
        <div class="card dealer-card"><?php echo htmlspecialchars($_SESSION['dealer_card']); ?></div>
    </div>
    
    <?php if (!empty($_SESSION['hint_message'])): ?>
        <div class="hint-box" id="hintBox">
            <h2><?php echo htmlspecialchars($_SESSION['hint_message']); ?></h2>
        </div>
    <?php endif; ?>
    
    <form method="POST">
        <!-- Hidden field keeps the modal open when clicking Play Again -->
        <input type="hidden" name="hint_submitted" value="1"> 
        <button type="submit" name="play_again" value="1">Play Again</button>
    </form>
<?php endif; ?>