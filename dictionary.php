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
    <div class="dictionary-container">
        
        <h1>Casino Wordle Dictionary</h1>
        <h2>Search by First Letter</h2>
        
        <!-- Alphabet Search Navigation -->
        <div class="alphabet-search">
            <?php
            // Generate A-Z buttons for the search feature
            foreach (range('A', 'Z') as $char) {
                // Output a button for each letter that triggers the JS function
                echo "<button type='button' class='letter-search-btn' onclick=\"showLetter('$char')\">$char</button>";
            }
            ?>
        </div>

        <div class="dictionary-results">
            <?php
            // Loop through each letter group and display them
            if (!empty($categorized_words)) {
                foreach ($categorized_words as $letter => $words) {
                    // Give each section a unique ID and hide it by default with display: none
                    echo "<div class='letter-section' id='letter-section-" . htmlspecialchars($letter) . "' style='display: none;'>";
                    
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
    </div>

    <!-- Script to handle popping out the words when a letter is clicked -->
    <script>
    function showLetter(letter) {
        // 1. Hide all letter sections first
        const sections = document.querySelectorAll('.letter-section');
        sections.forEach(section => {
            section.style.display = 'none';
        });

        // 2. Find the specific section for the clicked letter and show it
        const targetSection = document.getElementById('letter-section-' + letter);
        if (targetSection) {
            targetSection.style.display = 'block';
        }
    }
    </script>

    <!-- Styles for the new Alphabet Search Feature -->
    <style>
    .alphabet-search {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 6px;
        margin-bottom: 25px;
    }
    .letter-search-btn {
        background-color: #222;
        color: white;
        border: 2px solid #555;
        padding: 8px 14px;
        font-size: 16px;
        cursor: pointer;
        font-weight: bold;
        border-radius: 4px;
        transition: 0.2s;
    }
    .letter-search-btn:hover {
        background-color: #c9b458;
        border-color: #c9b458;
        color: black;
    }
    </style>