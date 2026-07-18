<?php
    include "config.php";
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    $wordLength = 10;

    if (!isset($_SESSION['answer'])) {
        $query = mysqli_query($conn, "SELECT word FROM words ORDER BY RAND() LIMIT 1");
        $row = mysqli_fetch_assoc($query);

        if ($row && isset($row['word'])) {
            $_SESSION['answer'] = strtoupper($row['word']);
        } else {
            $_SESSION['answer'] = "";
        }

        $_SESSION['attempts'] = [];
        $_SESSION['hinted_letters'] = []; 
        $_SESSION['maxAttempts'] = 5; 
        $_SESSION['wordle_tracked']= false;
    }

    // =====================================================================
    // HINT BACKEND LOGIC
    // =====================================================================
    $deck_of_cards = ["A", "2", "3", "4", "5", "6", "7", "8", "9", "10", "J", "Q", "K"];
    $player_hand = ["2", "3", "4", "5", "6", "7", "8", "9", "10", "J", "Q"];

    if (!function_exists('getCardValue')) {
        function getCardValue($card) {
            $values = [
                "A" => 1, "2" => 2, "3" => 3, "4" => 4, "5" => 5, "6" => 6, "7" => 7, 
                "8" => 8, "9" => 9, "10" => 10, "J" => 11, "Q" => 12, "K" => 13
            ];
            return $values[$card];  
        }
    }

    if (!function_exists('winConditionEffect')) {
        function winConditionEffect() {
            if (!empty($_SESSION['answer'])) {
                $possibleLetters = array_unique(str_split($_SESSION['answer']));
                if (isset($_SESSION['hinted_letters'])) {
                    $possibleLetters = array_diff($possibleLetters, $_SESSION['hinted_letters']);
                }
                if (!empty($possibleLetters)) {
                    $randomHintLetter = $possibleLetters[array_rand($possibleLetters)];
                    $_SESSION['hinted_letters'][] = $randomHintLetter;
                }
            }
        }
    }

    if (isset($_POST['play_again']) || !isset($_SESSION['player_card'])) {
        $_SESSION['player_card'] = $player_hand[array_rand($player_hand)];
        $_SESSION['dealer_card'] = "";
        $_SESSION['hint_message'] = "";
        $_SESSION['game_over'] = false;
    }

   if (isset($_POST['choice']) && !$_SESSION['game_over']) {
        $choice = $_POST['choice']; 
        $available_dealer_cards = array_diff($deck_of_cards, [$_SESSION['player_card']]);
        $_SESSION['dealer_card'] = $available_dealer_cards[array_rand($available_dealer_cards)];
        
        $playerValue = getCardValue($_SESSION['player_card']);
        $dealerValue = getCardValue($_SESSION['dealer_card']);
        
        // TRACK INDIVIDUAL GAMBLE ATTEMPTS
        $user_id = $_SESSION['user_id'];
        mysqli_query($conn, "UPDATE users SET gambles_made = gambles_made + 1 WHERE id = $user_id");
        
        if (($choice === 'higher' && $dealerValue > $playerValue) || ($choice === 'lower' && $dealerValue < $playerValue)) {
            $_SESSION['hint_message'] = "You Win! The dealer drew a " . ($choice === 'higher' ? "higher" : "lower") . " card.";
            winConditionEffect();
            $_SESSION['game_over'] = true; 

            // TRACK INDIVIDUAL GAMBLE WINS
            mysqli_query($conn, "UPDATE users SET gambles_won = gambles_won + 1 WHERE id = $user_id");
        } else {
            if ($_SESSION['maxAttempts'] > 1) {
                $_SESSION['maxAttempts']--;
            }
            $_SESSION['player_card'] = $player_hand[array_rand($player_hand)];
            $_SESSION['dealer_card'] = "";
            $_SESSION['hint_message'] = "";
            $_SESSION['game_over'] = false;
            header("Location: index.php");
            exit();
        }
    }
    
    // =====================================================================

    $maxAttempts = $_SESSION['maxAttempts']; 
    $gameEnded = false; 
    $message = "";

    if (isset($_POST['guess'])) {
        $guess = strtoupper(trim($_POST['guess']));
        if (strlen($guess) == $wordLength && count($_SESSION['attempts']) < $maxAttempts) {
            $checkQuery = mysqli_query($conn, "SELECT word FROM words WHERE word = '" . mysqli_real_escape_string($conn, strtolower($guess)) . "'");
            if (mysqli_num_rows($checkQuery) > 0) {
                $_SESSION['attempts'][] = $guess;
                if ($guess == $_SESSION['answer']) {
                    $message = "You Win!";
                    $gameEnded = true;
                } elseif (count($_SESSION['attempts']) >= $maxAttempts) {
                    $message = "Game Over! Word was " . $_SESSION['answer'];
                }
            } else {
                $message = "Invalid word! Try again.";
            }
        }
    }

    $gameEnded = (count($_SESSION['attempts']) >= $maxAttempts || in_array($_SESSION['answer'], $_SESSION['attempts']));

    if ($gameEnded && in_array($_SESSION['answer'], $_SESSION['attempts'])) {
        $message = "You Win!";
    } elseif ($gameEnded && empty($message)) {
        $message = "Game Over! Word was " . $_SESSION['answer'];
    }

    $gameEnded = (count($_SESSION['attempts']) >= $maxAttempts || in_array($_SESSION['answer'], $_SESSION['attempts']));

    if ($gameEnded && in_array($_SESSION['answer'], $_SESSION['attempts'])) {
        $message = "You Win!";
    } elseif ($gameEnded && empty($message)) {
        $message = "Game Over! Word was " . $_SESSION['answer'];
    }

    // REAL-TIME TRACKING FOR PROFILE WORDLE STATS
    if ($gameEnded && (!isset($_SESSION['wordle_tracked']) || $_SESSION['wordle_tracked'] === false)) {
        $user_id = $_SESSION['user_id'];
        
        // 1. Log overall game completion
        mysqli_query($conn, "UPDATE users SET games_played = games_played + 1 WHERE id = $user_id");
        
        // 2. Process personal achievements if they successfully solved the word
        if (in_array($_SESSION['answer'], $_SESSION['attempts'])) {
            $current_attempts = count($_SESSION['attempts']);
            $current_word = $_SESSION['answer'];
            
            // Query current high scores to evaluate if they set a new personal record
            $check_record_query = mysqli_query($conn, "SELECT least_attempts FROM users WHERE id = $user_id");
            $record_data = mysqli_fetch_assoc($check_record_query);
            $previous_least = (int)$record_data['least_attempts'];
            
            // If it's their first win (0) or faster than their previous record, write to DB
            if ($previous_least === 0 || $current_attempts < $previous_least) {
                $stmt_record = mysqli_prepare($conn, "UPDATE users SET best_word = ?, least_attempts = ? WHERE id = ?");
                mysqli_stmt_bind_param($stmt_record, "sii", $current_word, $current_attempts, $user_id);
                mysqli_stmt_execute($stmt_record);
                mysqli_stmt_close($stmt_record);
            }
        }
        
        // Seal state variable to stop duplicate tracking entries on refresh
        $_SESSION['wordle_tracked'] = true;
    }
    function colorGuess($guess, $answer) {
        $result = [];
        for ($i = 0; $i < 10; $i++) {
            if ($guess[$i] == $answer[$i]) $result[] = "green";
            elseif (strpos($answer, $guess[$i]) !== false) $result[] = "yellow";
            else $result[] = "gray";
        }
        return $result;
    }

    function getKeyStyle($letter) {
        if (isset($_SESSION['hinted_letters']) && in_array($letter, $_SESSION['hinted_letters'])) {
            return 'background-color: #c9b458 !important; color: white !important;';
        }
        return '';
    }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Casino Wordle</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- ================= NAVBAR ================= -->
    <nav class="navbar">
        <div class="nav-left">
            CASINO WORDLE
        </div>
        
        <div class="nav-center">
            Attempts Left: <?php echo $_SESSION['maxAttempts'] - count($_SESSION['attempts']); ?>
        </div>
        
        <div class="nav-right">
            <a onclick="document.getElementById('myModal2').style.display='flex'" class="nav-link" style="cursor: pointer;">DICTIONARY</a>
            <a onclick="document.getElementById('myModal3').style.display='flex'" class="nav-link" style="cursor: pointer;">PROFILE</a>
            <?php if (!empty($_SESSION['is_admin'])) { echo '<a onclick="document.getElementById(\'myModal4\').style.display=\'flex\'" class="nav-link" style="cursor: pointer;">ADMIN</a>'; } ?>
        </div>
    </nav>

    <h2 class="text-center">Guess a 10-letter word</h2>

    <!-- ================= HINT MODAL ================= -->
    <div class="hint text-center" style="margin-bottom: 20px;">
        <?php
        $modalTitle = "Gamble for a Hint!";
        $remainingAttempts = $maxAttempts - count($_SESSION['attempts']);
        $isHintDisabled = ($remainingAttempts <= 1 || $gameEnded);
        $modalDisplay = (isset($_POST['hint_submitted']) && !$isHintDisabled) ? 'flex' : 'none';
        ?>

        <div id="myModal" class="hint-modal" style="display: <?php echo $modalDisplay; ?>;">
            <div class="hint-modal-content">
                <button type="button" class="hint-modal-close" onclick="document.getElementById('myModal').style.display='none'">Close</button>
                <h3><?php echo $modalTitle; ?></h3>
                <?php include "hint.php"; ?>
            </div>
        </div>
        
        <button type="button" class="hint-open-btn" onclick="document.getElementById('myModal').style.display='flex'"
                <?php if ($isHintDisabled) echo 'disabled style="opacity: 0.5; cursor: not-allowed; filter: grayscale(100%);"'; ?>>
            💡
        </button>
    </div>

    <!-- ================= DICTIONARY MODAL ================= -->
    <div id="myModal2" class="dict-modal" style="display: none;">
        <div class="dict-modal-content">
            <button type="button" class="dict-modal-close" onclick="document.getElementById('myModal2').style.display='none'">Close</button>
            <h3>See the Dictionary!</h3>
            <?php include "dictionary.php"; ?>
        </div>
    </div>

    <!-- ================= PROFILE MODAL ================= -->
    <div id="myModal3" class="profile-modal" style="display: none;">
        <div class="profile-modal-content">
            <button type="button" class="profile-modal-close" onclick="document.getElementById('myModal3').style.display='none'">Close</button>
            <?php include "profile.php"; ?>
        </div>
    </div>

<!-- ================= ADMIN MODAL ================= -->
    <?php
    // Check if a pagination, letter filter, or an admin POST submission just occurred
    $showAdmin = (
        isset($_GET['page']) || 
        isset($_GET['letter']) || 
        isset($_POST['delete_user']) || 
        isset($_POST['create_word']) || 
        isset($_POST['update_word']) || 
        isset($_POST['delete_word'])
    );
    // Determine the display property based on the check above
    $adminDisplay = $showAdmin ? 'flex' : 'none';
    ?>
    <div id="myModal4" class="admin-modal" style="display: <?php echo $adminDisplay; ?>;">
        <div class="admin-modal-content">
            <!-- Note: if you want the URL to clean up when they close it, you could change this button to redirect to index.php instead of just hiding it -->
            <button type="button" class="admin-modal-close" onclick="window.location.href='index.php';">Close</button>
            <?php include "admin_content.php"; ?>
        </div>
    </div>
    <!-- ================= 1. GAME BOARD ================= -->
    <div class="board">
    <?php
    $attemptsCount = count($_SESSION['attempts']);
    
    // Loop through max attempts to render empty slots for unguessed words
    for ($rowIdx = 0; $rowIdx < $maxAttempts; $rowIdx++) {
        echo "<div class='row'>";
        
        if ($rowIdx < $attemptsCount) {
            // Render completed guess
            $attempt = $_SESSION['attempts'][$rowIdx];
            $colors = colorGuess($attempt, $_SESSION['answer']);
            for ($i = 0; $i < 10; $i++) {
                $letter = $attempt[$i];
                $colorClass = $colors[$i];
                echo "<div class='tile " . $colorClass . "'>" . htmlspecialchars($letter) . "</div>";
            }
        } elseif ($rowIdx == $attemptsCount && !$gameEnded) {
            // Render active row (where typed letters will go)
            for ($i = 0; $i < 10; $i++) {
                echo "<div class='tile empty active-tile' id='active-tile-$i'></div>";
            }
        } else {
            // Render future empty rows
            for ($i = 0; $i < 10; $i++) {
                echo "<div class='tile empty'></div>";
            }
        }
        echo "</div>";
    }
    ?>
    </div>

    <!-- ================= 2. GAME MESSAGES / PLAY AGAIN ================= -->
    <!-- This sits exactly between the board and the keyboard now -->
    <div class="game-messages text-center">
    <?php
    if (!empty($message)) {
        echo "<h2 style='margin: 15px 0;'>" . htmlspecialchars($message) . "</h2>";
    }

    if ($gameEnded == true)  {
        echo '<form action="reset.php" style="margin: 20px 0;"><button type="submit" class="play-again-btn">Play Again</button></form>';
    }
    ?>
    </div>

    <!-- ================= 3. KEYBOARD ================= -->
    <form method="POST" id="guessForm">
        <input type="hidden" id="guessInput" name="guess" value="">
        <div class="keyboard">
            <div class="key-row">
                <button type="button" class="key key-letter" data-letter="Q" style="<?php echo getKeyStyle('Q'); ?>" <?php echo $gameEnded ? 'disabled' : ''; ?>>Q</button>
                <button type="button" class="key key-letter" data-letter="W" style="<?php echo getKeyStyle('W'); ?>" <?php echo $gameEnded ? 'disabled' : ''; ?>>W</button>
                <button type="button" class="key key-letter" data-letter="E" style="<?php echo getKeyStyle('E'); ?>" <?php echo $gameEnded ? 'disabled' : ''; ?>>E</button>
                <button type="button" class="key key-letter" data-letter="R" style="<?php echo getKeyStyle('R'); ?>" <?php echo $gameEnded ? 'disabled' : ''; ?>>R</button>
                <button type="button" class="key key-letter" data-letter="T" style="<?php echo getKeyStyle('T'); ?>" <?php echo $gameEnded ? 'disabled' : ''; ?>>T</button>
                <button type="button" class="key key-letter" data-letter="Y" style="<?php echo getKeyStyle('Y'); ?>" <?php echo $gameEnded ? 'disabled' : ''; ?>>Y</button>
                <button type="button" class="key key-letter" data-letter="U" style="<?php echo getKeyStyle('U'); ?>" <?php echo $gameEnded ? 'disabled' : ''; ?>>U</button>
                <button type="button" class="key key-letter" data-letter="I" style="<?php echo getKeyStyle('I'); ?>" <?php echo $gameEnded ? 'disabled' : ''; ?>>I</button>
                <button type="button" class="key key-letter" data-letter="O" style="<?php echo getKeyStyle('O'); ?>" <?php echo $gameEnded ? 'disabled' : ''; ?>>O</button>
                <button type="button" class="key key-letter" data-letter="P" style="<?php echo getKeyStyle('P'); ?>" <?php echo $gameEnded ? 'disabled' : ''; ?>>P</button>
            </div>  
            <div class="key-row">
                <button type="button" class="key key-letter" data-letter="A" style="<?php echo getKeyStyle('A'); ?>" <?php echo $gameEnded ? 'disabled' : ''; ?>>A</button>
                <button type="button" class="key key-letter" data-letter="S" style="<?php echo getKeyStyle('S'); ?>" <?php echo $gameEnded ? 'disabled' : ''; ?>>S</button>
                <button type="button" class="key key-letter" data-letter="D" style="<?php echo getKeyStyle('D'); ?>" <?php echo $gameEnded ? 'disabled' : ''; ?>>D</button>
                <button type="button" class="key key-letter" data-letter="F" style="<?php echo getKeyStyle('F'); ?>" <?php echo $gameEnded ? 'disabled' : ''; ?>>F</button>
                <button type="button" class="key key-letter" data-letter="G" style="<?php echo getKeyStyle('G'); ?>" <?php echo $gameEnded ? 'disabled' : ''; ?>>G</button>
                <button type="button" class="key key-letter" data-letter="H" style="<?php echo getKeyStyle('H'); ?>" <?php echo $gameEnded ? 'disabled' : ''; ?>>H</button>
                <button type="button" class="key key-letter" data-letter="J" style="<?php echo getKeyStyle('J'); ?>" <?php echo $gameEnded ? 'disabled' : ''; ?>>J</button>
                <button type="button" class="key key-letter" data-letter="K" style="<?php echo getKeyStyle('K'); ?>" <?php echo $gameEnded ? 'disabled' : ''; ?>>K</button>
                <button type="button" class="key key-letter" data-letter="L" style="<?php echo getKeyStyle('L'); ?>" <?php echo $gameEnded ? 'disabled' : ''; ?>>L</button>
            </div>
            <div class="key-row">
                <button type="button" class="key wide" id="backspaceBtn" <?php echo $gameEnded ? 'disabled' : ''; ?>>⌫</button>
                <button type="button" class="key key-letter" data-letter="Z" style="<?php echo getKeyStyle('Z'); ?>" <?php echo $gameEnded ? 'disabled' : ''; ?>>Z</button>
                <button type="button" class="key key-letter" data-letter="X" style="<?php echo getKeyStyle('X'); ?>" <?php echo $gameEnded ? 'disabled' : ''; ?>>X</button>
                <button type="button" class="key key-letter" data-letter="C" style="<?php echo getKeyStyle('C'); ?>" <?php echo $gameEnded ? 'disabled' : ''; ?>>C</button>
                <button type="button" class="key key-letter" data-letter="V" style="<?php echo getKeyStyle('V'); ?>" <?php echo $gameEnded ? 'disabled' : ''; ?>>V</button>
                <button type="button" class="key key-letter" data-letter="B" style="<?php echo getKeyStyle('B'); ?>" <?php echo $gameEnded ? 'disabled' : ''; ?>>B</button>
                <button type="button" class="key key-letter" data-letter="N" style="<?php echo getKeyStyle('N'); ?>" <?php echo $gameEnded ? 'disabled' : ''; ?>>N</button>
                <button type="button" class="key key-letter" data-letter="M" style="<?php echo getKeyStyle('M'); ?>" <?php echo $gameEnded ? 'disabled' : ''; ?>>M</button>
                <button type="button" class="key wide" id="submitBtn" <?php echo $gameEnded ? 'disabled' : ''; ?>>Enter</button>
            </div>
        </div>
    </form>

    <script>
    const guessForm = document.getElementById('guessForm');
    const guessInput = document.getElementById('guessInput');

    // Automatically injects the typed letters into the empty slots
    function updateGuessDisplay() {
        const currentGuess = guessInput.value;
        for (let i = 0; i < 10; i++) {
            const tile = document.getElementById('active-tile-' + i);
            if (tile) {
                // If there's a letter at this index, show it. Otherwise, clear it.
                tile.textContent = currentGuess[i] ? currentGuess[i] : '';
                
                // Add a visual pop when a letter is typed
                if (currentGuess[i]) {
                    tile.style.borderColor = '#c9b458';
                } else {
                    tile.style.borderColor = 'white';
                }
            }
        }
    }

    function addLetter(letter) {
        if (guessInput.value.length < 10) {
            guessInput.value += letter.toUpperCase();
            updateGuessDisplay();
        }
    }

    function removeLetter() {
        guessInput.value = guessInput.value.slice(0, -1);
        updateGuessDisplay();
    }

    function submitGuess() {
        if (guessInput.value.length === 10) {
            guessForm.submit();
        }
    }

    if (guessForm) {
        document.querySelectorAll('.key-letter').forEach(function(button) {
            button.addEventListener('click', function() {
                addLetter(button.getAttribute('data-letter'));
            });
        });

        document.getElementById('backspaceBtn').addEventListener('click', removeLetter);
        document.getElementById('submitBtn').addEventListener('click', submitGuess);

        document.addEventListener('keydown', function(event) {
            if (event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA') {
                return;
            }
            const key = event.key;

            if (/^[a-zA-Z]$/.test(key)) {
                event.preventDefault();
                addLetter(key);
            } else if (key === 'Backspace') {
                event.preventDefault();
                removeLetter();
            } else if (key === 'Enter') {
                event.preventDefault();
                submitGuess();
                } else if (key === 'Escape') {
                const hintModal = document.getElementById('myModal');
                const dictModal = document.getElementById('myModal2');
                const profileModal = document.getElementById('myModal3');
                const adminModal = document.getElementById('myModal4');

                if (hintModal && hintModal.style.display === 'flex') {
                    hintModal.style.display = 'none';
                } else if (dictModal && dictModal.style.display === 'flex') {
                    dictModal.style.display = 'none';
                } else if (profileModal && profileModal.style.display === 'flex') {
                    profileModal.style.display = 'none';
                } else if (adminModal && adminModal.style.display === 'flex') {
                    adminModal.style.display = 'none';
                }
            }
        });
    }
    </script>
</body>
</html>