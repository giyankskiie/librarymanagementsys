

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    if ($admin && password_verify($password, $admin["password"])) {
        $_SESSION["admin"] = $admin["username"];
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "Invalid credentials";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login</title>
<style>
    /* Reset & Body */
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: 'Inter', sans-serif;
        background: linear-gradient(135deg, #14249c, #aba30e);
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: 20px;
    }

    /* Login Card */
    .login-card {
        background: #fff;
        border-radius: 20px;
        padding: 40px 30px;
        width: 100%;
        max-width: 400px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        text-align: center;
    }

    .login-card h2 {
        color: #14249c;
        margin-bottom: 25px;
        font-size: 28px;
        font-weight: 700;
    }

    .login-card input {
        width: 100%;
        padding: 15px;
        margin: 12px 0;
        border-radius: 12px;
        border: 1px solid #ccc;
        font-size: 16px;
    }

    .login-card input:focus {
        outline: none;
        border-color: #14249c;
        box-shadow: 0 4px 12px rgba(20,36,156,0.2);
    }

    .login-card button {
        width: 100%;
        padding: 15px;
        margin-top: 15px;
        border-radius: 12px;
        border: none;
        background: linear-gradient(135deg, #14249c, #aba30e);
        color: #fff;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .login-card button:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.25);
    }

    /* Error message */
    .error {
        color: #b91c1c;
        margin-bottom: 15px;
        font-weight: 600;
    }

    /* Responsive */
    @media(max-width: 500px){
        .login-card { padding: 30px 20px; }
        .login-card h2 { font-size: 24px; }
    }
</style>
</head>
<body>

<div class="login-card">
    <h2>Admin Login</h2>
    <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
</div>

</body>
</html>
