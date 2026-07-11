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

function winConditionEffect() {
     if (!empty($_SESSION['answer'])) {
        // Break the answer word down into individual unique letters
        $possibleLetters = array_unique(str_split($_SESSION['answer']));
        
        // Filter out letters that have ALREADY been hinted before
        if (isset($_SESSION['hinted_letters'])) {
            $possibleLetters = array_diff($possibleLetters, $_SESSION['hinted_letters']);
        }
        
        // If there are still letters left to hint, pick a random one
        if (!empty($possibleLetters)) {
            $randomHintLetter = $possibleLetters[array_rand($possibleLetters)];
            
            // Save it to the session tracker!
            $_SESSION['hinted_letters'][] = $randomHintLetter;
        }
    }
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
        winConditionEffect();
    
   
    } elseif ($choice === 'lower' && $dealerValue < $playerValue) {
        $hint_message = "You Win! The dealer drew a lower card.";
        winConditionEffect();
        } else {
        $hint_message = "You Lose! The dealer drew a " . ($dealerValue > $playerValue ? "higher" : "lower") . " card.";
        
        // Safety check: Only decrease if they have more than 1 attempt left
        if ($_SESSION['maxAttempts'] > 1) {
            $_SESSION['maxAttempts']--;
        } else {
            $hint_message = " You are on your last attempt!";
        }
    }
    
    $_SESSION['game_over'] = true;
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>Higher or Lower</title>
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
        
        <!-- Hint Message Box -->
        <?php if (!empty($hint_message)): ?>
            <div class="hint-box" id="hintBox">
                <h2><?php echo $hint_message; ?></h2>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <button type="submit" name="play_again" value="1">Play Again</button>
        </form>
    <?php endif; ?>

</body>
</html>