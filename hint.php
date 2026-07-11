<?php   

$deck_of_cards = ["A", "2", "3", "4", "5", "6", "7", "8", "9", "10", "J", "Q", "K"];
$player_hand = ["2", "3", "4", "5", "6", "7", "8", "9", "10", "J", "Q"];

function getCardValue($card) {
    $values = [
        "A" => 1, "2" => 2, "3" => 3, "4" => 4, "5" => 5, "6" => 6, "7" => 7, 
        "8" => 8, "9" => 9, "10" => 10, "J" => 11, "Q" => 12, "K" => 13
    ];
    return $values[$card];  
}

if (isset($_POST['play_again']) || !isset($_SESSION['player_card'])) {
    $_SESSION['player_card'] = $player_hand[array_rand($player_hand)];
    $_SESSION['game_over'] = false;
}

$hint_message = "";
$dealer_card = "";

if (isset($_POST['choice']) && !$_SESSION['game_over']) {
    $choice = $_POST['choice']; // 'higher' or 'lower'
    
    $available_dealer_cards = array_diff($deck_of_cards, [$_SESSION['player_card']]);
    
    $dealer_card = $available_dealer_cards[array_rand($available_dealer_cards)];
    
    $playerValue = getCardValue($_SESSION['player_card']);
    $dealerValue = getCardValue($dealer_card);
    
    if ($choice === 'higher' && $dealerValue > $playerValue) {
        $hint_message = "You Win! The dealer drew a higher card.";
    } elseif ($choice === 'lower' && $dealerValue < $playerValue) {
        $hint_message = "You Win! The dealer drew a lower card.";
    } else {
        $hint_message = "You Lose! The dealer drew a " . ($dealerValue > $playerValue ? "higher" : "lower") . " card.";
    }
    
    $_SESSION['game_over'] = true;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Higher or Lower</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin-top: 50px; }
        .card { 
            display: inline-block; padding: 20px 30px; font-size: 2em; 
            border: 2px solid #333; border-radius: 8px; margin: 10px; 
            background-color: white; color: black; font-weight: bold;
        }
        .dealer-card { background-color: #f8f9fa; }
        button { padding: 10px 20px; font-size: 1.2em; margin: 5px; cursor: pointer; }
        
        /* Styles for the closable hint box */
        .hint-box {
            background-color: #f0f8ff;
            border: 2px solid #007bff;
            border-radius: 8px;
            padding: 10px 30px 10px 10px; /* Extra padding on right for the X */
            margin: 20px auto;
            width: 80%;
            max-width: 400px;
            position: relative;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .close-btn {
            position: absolute;
            top: 5px;
            right: 15px;
            font-size: 1.5em;
            font-weight: bold;
            cursor: pointer;
            color: #555;
        }
        .close-btn:hover { color: #000; }
        .hint-box h2 { margin: 10px 0; font-size: 1.5em; }
    </style>
</head>
<body>

    <h1>Higher or Lower</h1>
    
    <div>
        <h3>Your Card:</h3>
        <div class="card"><?php echo $_SESSION['player_card']; ?></div>
    </div>

    <?php if (!$_SESSION['game_over']): ?>
        <p>Will the dealer's card be higher or lower?</p>
        <form method="POST">
            <!-- ADD THIS HIDDEN FIELD -->
            <input type="hidden" name="hint_submitted" value="1">
            <button type="submit" name="choice" value="higher">Higher ⬆️</button>
            <button type="submit" name="choice" value="lower">Lower ⬇️</button>
        </form>
    <?php else: ?>
        <div>
            <h3>Dealer's Card:</h3>
            <div class="card dealer-card"><?php echo $dealer_card; ?></div>
        </div>
        
        <!-- The Closable Hint Message Box -->
        <?php if (!empty($hint_message)): ?>
            <div class="hint-box" id="hintBox">
                <span class="close-btn" onclick="document.getElementById('hintBox').style.display='none'">&times;</span>
                <h2><?php echo $hint_message; ?></h2>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <button type="submit" name="play_again" value="1">Play Again</button>
        </form>
    <?php endif; ?>

</body>
</html>