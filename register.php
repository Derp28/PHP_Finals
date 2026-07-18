<?php
include "config.php";
$message = "";
$is_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $message = "Username is already taken!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_stmt = mysqli_prepare($conn, "INSERT INTO users (username, password) VALUES (?, ?)");
            mysqli_stmt_bind_param($insert_stmt, "ss", $username, $hashed_password);
            
            if (mysqli_stmt_execute($insert_stmt)) {
                $message = "Registration successful! <a href='login.php' style='color:#6aaa64; font-weight:bold; text-decoration:none;'>Login here</a>";
                $is_success = true;
            } else {
                $message = "Something went wrong. Try again.";
            }
        }
    } else {
        $message = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>Register - Casino Wordle</title>
    <style>
        /* Container box utilizing your existing project color scheme */
        .auth-card {
            background-color: #222;
            border: 2px solid #c9b458; /* Casino Gold */
            max-width: 400px;
            margin: 80px auto;
            padding: 35px 25px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.6);
        }

        .auth-card h1 {
            margin-top: 0;
            margin-bottom: 5px;
        }

        .auth-subtitle {
            color: #c9b458;
            font-size: 1.1rem;
            margin-bottom: 25px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .auth-input {
            padding: 12px;
            width: 85%;
            font-size: 16px;
            margin-bottom: 15px;
            border-radius: 6px;
            border: 2px solid #787c7e; /* Gray */
            background: #111;
            color: white;
            text-align: center;
        }

        .auth-input:focus {
            border-color: #c9b458;
            outline: none;
        }

        .auth-card button {
            width: 92%;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            margin-top: 10px;
        }

        .auth-status {
            margin-bottom: 15px;
            font-weight: bold;
        }

        .auth-link {
            color: #c9b458;
            text-decoration: none;
        }

        .auth-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <!-- Container Div Wrapper -->
    <div class="auth-card">
        <h1>Create Account</h1>
        <div class="auth-subtitle">JOIN THE CASINO</div>
        
        <?php 
        if(!empty($message)) {
            $color = $is_success ? '#6aaa64' : '#ff4d4d';
            echo "<div class='auth-status' style='color: $color;'>$message</div>"; 
        } 
        ?>
        
        <form method="POST">
            <input type="text" name="username" class="auth-input" placeholder="Username" required><br>
            <input type="password" name="password" class="auth-input" placeholder="Password" required><br>
            <button type="submit">REGISTER</button>
        </form>
        
        <p style="margin-top: 20px; font-size: 0.95rem;">
            Already have an account? <a href="login.php" class="auth-link">Login here</a>
        </p>
    </div>

</body>
</html>