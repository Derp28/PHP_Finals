<?php
    // Connect to the database
    include "config.php";

    // Fetch all words from the database and order them alphabetically (A-Z)
    $query = mysqli_query($conn, "SELECT word FROM words ORDER BY word ASC");
    
    // Create an associative array to group words by their starting letter
    $categorized_words = [];
    
    if ($query) {
        while ($row = mysqli_fetch_assoc($query)) {
            $word = strtoupper($row['word']);
            $first_letter = $word[0]; // Get the first letter of the word
            
            // Add the word to the array under its starting letter
            $categorized_words[$first_letter][] = $word;
        }
    }
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>Word Dictionary - Casino Wordle</title>
    <style>
        .dictionary-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            text-align: center;
        }

        .back-btn {
            background-color: #c9b458;
            color: black;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 20px;
            font-size: 16px;
        }

        .back-btn:hover {
            background-color: #a89545;
        }

        /* New styles for the letter categories */
        .letter-section {
            margin-top: 40px;
            text-align: left; /* Align headers to the left for better readability */
        }

        .letter-header {
            color: #c9b458;
            font-size: 2.5em;
            border-bottom: 2px solid #c9b458;
            padding-bottom: 5px;
            margin-bottom: 20px;
            font-family: sans-serif;
        }

        .word-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 15px;
        }

        .word-card {
            background-color: #222;
            color: white;
            padding: 12px;
            border-radius: 5px;
            border: 1px solid #c9b458;
            font-family: monospace;
            font-size: 1.2em;
            letter-spacing: 2px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
            text-align: center; /* Keep the words centered inside their cards */
        }
    </style>
</head>
<body>

    <div class="dictionary-container">
        <a href="index.php" class="back-btn">← Back to Game</a>
        
        <h1>Casino Wordle Dictionary</h1>
        <h2>All Valid 10-Letter Words</h2>
        
        <?php
        // Loop through each letter group and display them
        if (!empty($categorized_words)) {
            foreach ($categorized_words as $letter => $words) {
                echo "<div class='letter-section'>";
                
                // Display the large gold letter header
                echo "<h2 class='letter-header'>" . htmlspecialchars($letter) . "</h2>";
                
                // Start a new grid for the words under this letter
                echo "<div class='word-grid'>";
                foreach ($words as $word) {
                    echo "<div class='word-card'>" . htmlspecialchars($word) . "</div>";
                }
                echo "</div>"; // Close word-grid
                
                echo "</div>"; // Close letter-section
            }
        } else {
            echo "<h3>No words found in the database.</h3>";
        }
        ?>
    </div>

</body>
</html>