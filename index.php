<?php
include "config.php";

$maxAttempts = 5;
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
}

$message = "";
$gameEnded = false;

if (isset($_POST['guess'])) {
    $guess = strtoupper(trim($_POST['guess']));

    if (strlen($guess) == $wordLength && count($_SESSION['attempts']) < $maxAttempts && $message == "") {
        $_SESSION['attempts'][] = $guess;

        if ($guess == $_SESSION['answer']) {
            $message = "You Win!";
        } elseif (count($_SESSION['attempts']) >= $maxAttempts) {
            $message = "Game Over! Word was " . $_SESSION['answer'];
        }
    } elseif (strlen($guess) == $wordLength && count($_SESSION['attempts']) >= $maxAttempts) {
        $message = "Game Over! Word was " . $_SESSION['answer'];
    }
}

$gameEnded = ($message != "" || count($_SESSION['attempts']) >= $maxAttempts);

function colorGuess($guess, $answer) {
    $result = [];
    $guessLength = strlen($guess);
    $answerLength = strlen($answer);

    for ($i = 0; $i < 10; $i++) {
        $guessChar = ($i < $guessLength) ? $guess[$i] : "";
        $answerChar = ($i < $answerLength) ? $answer[$i] : "";

        if ($guessChar == $answerChar) {
            $result[] = "green";
        } elseif (strpos($answer, $guessChar) !== false) {
            $result[] = "yellow";
        } else {
            $result[] = "gray";
        }
    }

    return $result;
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Fake Wordle</h1>

    <form method="POST" id="guessForm">
        <input type="hidden" id="guessInput" name="guess" value="">

        <div class="guess-display">
            <span>Current guess:</span>
            <strong id="currentGuess">-</strong>
        </div>

        <div class="keyboard">
            <div class="key-row">
                <button type="button" class="key key-letter" data-letter="Q" <?php echo $gameEnded ? 'disabled' : ''; ?>>Q</button>
                <button type="button" class="key key-letter" data-letter="W" <?php echo $gameEnded ? 'disabled' : ''; ?>>W</button>
                <button type="button" class="key key-letter" data-letter="E" <?php echo $gameEnded ? 'disabled' : ''; ?>>E</button>
                <button type="button" class="key key-letter" data-letter="R" <?php echo $gameEnded ? 'disabled' : ''; ?>>R</button>
                <button type="button" class="key key-letter" data-letter="T" <?php echo $gameEnded ? 'disabled' : ''; ?>>T</button>
                <button type="button" class="key key-letter" data-letter="Y" <?php echo $gameEnded ? 'disabled' : ''; ?>>Y</button>
                <button type="button" class="key key-letter" data-letter="U" <?php echo $gameEnded ? 'disabled' : ''; ?>>U</button>
                <button type="button" class="key key-letter" data-letter="I" <?php echo $gameEnded ? 'disabled' : ''; ?>>I</button>
                <button type="button" class="key key-letter" data-letter="O" <?php echo $gameEnded ? 'disabled' : ''; ?>>O</button>
                <button type="button" class="key key-letter" data-letter="P" <?php echo $gameEnded ? 'disabled' : ''; ?>>P</button>
            </div>
            <div class="key-row">
                <button type="button" class="key key-letter" data-letter="A" <?php echo $gameEnded ? 'disabled' : ''; ?>>A</button>
                <button type="button" class="key key-letter" data-letter="S" <?php echo $gameEnded ? 'disabled' : ''; ?>>S</button>
                <button type="button" class="key key-letter" data-letter="D" <?php echo $gameEnded ? 'disabled' : ''; ?>>D</button>
                <button type="button" class="key key-letter" data-letter="F" <?php echo $gameEnded ? 'disabled' : ''; ?>>F</button>
                <button type="button" class="key key-letter" data-letter="G" <?php echo $gameEnded ? 'disabled' : ''; ?>>G</button>
                <button type="button" class="key key-letter" data-letter="H" <?php echo $gameEnded ? 'disabled' : ''; ?>>H</button>
                <button type="button" class="key key-letter" data-letter="J" <?php echo $gameEnded ? 'disabled' : ''; ?>>J</button>
                <button type="button" class="key key-letter" data-letter="K" <?php echo $gameEnded ? 'disabled' : ''; ?>>K</button>
                <button type="button" class="key key-letter" data-letter="L" <?php echo $gameEnded ? 'disabled' : ''; ?>>L</button>
            </div>
            <div class="key-row">
                <button type="button" class="key wide" id="backspaceBtn" <?php echo $gameEnded ? 'disabled' : ''; ?>>⌫</button>
                <button type="button" class="key key-letter" data-letter="Z" <?php echo $gameEnded ? 'disabled' : ''; ?>>Z</button>
                <button type="button" class="key key-letter" data-letter="X" <?php echo $gameEnded ? 'disabled' : ''; ?>>X</button>
                <button type="button" class="key key-letter" data-letter="C" <?php echo $gameEnded ? 'disabled' : ''; ?>>C</button>
                <button type="button" class="key key-letter" data-letter="V" <?php echo $gameEnded ? 'disabled' : ''; ?>>V</button>
                <button type="button" class="key key-letter" data-letter="B" <?php echo $gameEnded ? 'disabled' : ''; ?>>B</button>
                <button type="button" class="key key-letter" data-letter="N" <?php echo $gameEnded ? 'disabled' : ''; ?>>N</button>
                <button type="button" class="key key-letter" data-letter="M" <?php echo $gameEnded ? 'disabled' : ''; ?>>M</button>
                <button type="button" class="key wide" id="submitBtn" <?php echo $gameEnded ? 'disabled' : ''; ?>>Enter</button>
            </div>
        </div>
    </form>

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
            }
        });
    }
    </script>

    <?php
    foreach ($_SESSION['attempts'] as $attempt) {
        $colors = colorGuess($attempt, $_SESSION['answer']);
        $attemptLength = strlen($attempt);

        echo "<div class='row'>";

        for ($i = 0; $i < 10; $i++) {
            $letter = ($i < $attemptLength) ? $attempt[$i] : "";
            $colorClass = isset($colors[$i]) ? $colors[$i] : "gray";
            echo "<div class='tile " . $colorClass . "'>" . $letter . "</div>";
        }

        echo "</div>";
    }
    ?>

    <h2><?php echo $message; ?></h2>

    <?php if ($message != "") : ?>
        <form action="reset.php">
            <button>Play Again</button>
        </form>
    <?php endif; ?>
</body>
</html>