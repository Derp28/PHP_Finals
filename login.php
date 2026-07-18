<?php
include "config.php";

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $stmt = mysqli_prepare($conn, "SELECT id, password, is_admin FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $row['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int) $row['id'];
                $_SESSION['username'] = $username;
                $_SESSION['is_admin'] = (int) $row['is_admin'];
                header("Location: index.php");
                exit();
            } else {
                $message = "Incorrect password.";
            }
        } else {
            $message = "User does not exist.";
        }
    }
}
?>


<head>
    <link rel="stylesheet" href="style.css">
    <title>Login - Casino Wordle</title>
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

        .auth-error {
            color: #ff4d4d;
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
        <h1>Casino Wordle</h1>
        <div class="auth-subtitle">THE HOUSE ALWAYS WINS</div>
        
        <?php if(!empty($message)) echo "<div class='auth-error'>$message</div>"; ?>
        
        <form method="POST">
            <input type="text" name="username" class="auth-input" placeholder="Username" required><br>
            <input type="password" name="password" class="auth-input" placeholder="Password" required><br>
            <button type="submit">LOGIN</button>
        </form>
        
        <p style="margin-top: 20px; font-size: 0.95rem;">
            Don't have an account? <a href="register.php" class="auth-link">Register here</a>
        </p>
    </div>

</body>
