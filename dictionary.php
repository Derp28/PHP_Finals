<?php
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
    <link rel="stylesheet" href="style.css">
    <div class="dictionary-container">
        
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

