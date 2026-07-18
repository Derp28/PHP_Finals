<?php
    include "config.php";

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
    }

    // =====================================================================
    // HINT BACKEND LOGIC (Processes BEFORE the Wordle board calculates)
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

    // Initialize or Reset card mini-game
    if (isset($_POST['play_again']) || !isset($_SESSION['player_card'])) {
        $_SESSION['player_card'] = $player_hand[array_rand($player_hand)];
        $_SESSION['dealer_card'] = "";
        $_SESSION['hint_message'] = "";
        $_SESSION['game_over'] = false;
    }

    // Process higher/lower choice instantly
    if (isset($_POST['choice']) && !$_SESSION['game_over']) {
        $choice = $_POST['choice']; 
        $available_dealer_cards = array_diff($deck_of_cards, [$_SESSION['player_card']]);
        $_SESSION['dealer_card'] = $available_dealer_cards[array_rand($available_dealer_cards)];
        
        $playerValue = getCardValue($_SESSION['player_card']);
        $dealerValue = getCardValue($_SESSION['dealer_card']);
        
        if (($choice === 'higher' && $dealerValue > $playerValue) || ($choice === 'lower' && $dealerValue < $playerValue)) {
            $_SESSION['hint_message'] = "You Win! The dealer drew a " . ($choice === 'higher' ? "higher" : "lower") . " card.";
            winConditionEffect();
            $_SESSION['game_over'] = true; // Keeps modal open only for winners to see their hint
        } else {
            // 1. Apply the penalty immediately
            if ($_SESSION['maxAttempts'] > 1) {
                $_SESSION['maxAttempts']--;
            }
            
            // 2. Reset the mini-game parameters silently so it is fresh for their next attempt
            $_SESSION['player_card'] = $player_hand[array_rand($player_hand)];
            $_SESSION['dealer_card'] = "";
            $_SESSION['hint_message'] = "";
            $_SESSION['game_over'] = false;
            
            // 3. Force the page to reload cleanly, closing the modal instantly
            header("Location: index.php");
            exit();
        }
    }
    
    // =====================================================================

    // Reads the newly updated max attempts value instantly on the same page load!
    $maxAttempts = $_SESSION['maxAttempts']; 
    $gameEnded = false; 

    // Handles the Wordle guess submission
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
            <!-- Dictionary Modal Trigger Link -->
            <a onclick="document.getElementById('myModal2').style.display='flex'" class="nav-link" style="cursor: pointer;">DICTIONARY</a>
            <a href="profile.php" class="nav-link">PROFILE</a>
        </div>
    </nav>

    <h2 class="text-center">Guess a 10-letter word</h2>

<!-- ================= 1. HINT MODAL ================= -->
    <div class="hint">
        <?php
        $modalTitle = "Gamble for a Hint!";
        // Calculate remaining available attempts
        $remainingAttempts = $maxAttempts - count($_SESSION['attempts']);
        // Disable button if they have 1 or fewer attempts left, or if the game is already over
        $isHintDisabled = ($remainingAttempts <= 1 || $gameEnded);
        // Only allow the modal to display 'flex' if the hint button isn't disabled
        $modalDisplay = (isset($_POST['hint_submitted']) && !$isHintDisabled) ? 'flex' : 'none';
        ?>
        <div id="myModal" class="hint-modal" style="display: <?php echo $modalDisplay; ?>;">
            <div class="hint-modal-content">
                <button type="button" class="hint-modal-close" onclick="document.getElementById('myModal').style.display='none'">Close</button>
                <h3><?php echo $modalTitle; ?></h3>
                <?php include "hint.php"; ?>
            </div>
        </div>
        <!-- Updated Hint Button with disabled conditions and basic inline styles for visual feedback -->
        <button type="button"
                class="hint-open-btn"
                onclick="document.getElementById('myModal').style.display='flex'"
        <?php if ($isHintDisabled) echo 'disabled style="opacity: 0.5; cursor: not-allowed; filter: grayscale(100%);"'; ?>>💡</button>
    </div>

    <!-- ================= 2. DICTIONARY MODAL (Hidden Container) ================= -->
    <?php $dictModalTitle = "See the Dictionary!"; ?>
    <!-- THIS ONE is strictly 'none' so form submissions never force it open -->
    <div id="myModal2" class="dict-modal" style="display: none;">
        <div class="dict-modal-content">
            <button type="button" class="dict-modal-close" onclick="document.getElementById('myModal2').style.display='none'">Close</button>
            <h3><?php echo $dictModalTitle; ?></h3>
            <?php include "dictionary.php"; ?>
        </div>
    </div>

    <!-- ================= GAME BOARD AND KEYBOARD ================= -->
    <form method="POST" id="guessForm">
        <input type="hidden" id="guessInput" name="guess" value="">

        <!-- Displays guess -->
        <div class="guess-display text-center"> 
            <span> </span>
            <strong id="currentGuess">-</strong>
        </div>

        <!-- Digital keyboard that disables when game ends -->
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

    <!-- Handles keyboard input and updates the guess display -->
    <script>
    const guessForm = document.getElementById('guessForm');
    const guessInput = document.getElementById('guessInput');
    const currentGuess = document.getElementById('currentGuess');

    function updateGuessDisplay() {
        currentGuess.textContent = guessInput.value || '-';
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

                if (hintModal && hintModal.style.display === 'flex') {
                    hintModal.style.display = 'none';
                } else if (dictModal && dictModal.style.display === 'flex') {
                    dictModal.style.display = 'none';
                }
            }
        });
    }
    </script>
    
    <!-- Displays the board where guesses can be seen -->
    <div class="board">
    <?php
    foreach ($_SESSION['attempts'] as $attempt) {
        $colors = colorGuess($attempt, $_SESSION['answer']);
        $attemptLength = strlen($attempt);

        echo "<div class='row' style='display:flex; justify-content: center; gap: 5px; margin-bottom: 5px;'>"; // Added justify-content center for the board rows

        // Saves the guess and colors the tiles based on the guess
        for ($i = 0; $i < 10; $i++) {
            $letter = ($i < $attemptLength) ? $attempt[$i] : "";
            $colorClass = isset($colors[$i]) ? $colors[$i] : "gray";
            echo "<div class='tile " . $colorClass . "'>" . htmlspecialchars($letter) . "</div>";
        }

        echo "</div>";
    }
    ?>
    </div>

    <!-- Displays message if word is invalid or if the game is over -->
    <div class="game-messages text-center">
    <?php
    if (!empty($message)) {
        echo "<h2>" . htmlspecialchars($message) . "</h2>";
    }

    if ($gameEnded == true)  {
        echo '<form action="reset.php"><button type="submit">Play Again</button></form>';
    }
    ?>
    </div>

</body>
</html>