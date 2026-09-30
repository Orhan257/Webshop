<?php
session_start();
require_once 'includes/config.php';

// -------------------- INput --------------------
function clean_input($str) {
    return htmlspecialchars(strip_tags(trim($str)), ENT_QUOTES, 'UTF-8');
}

// Blacklisted words
$offensiveWords = ["fuck","shit","bitch","admin","root","drop","delete","insert","script","update"];
$error = "";
$success = "";

// ============================================================
//                      LOGIN HANDLER
// ============================================================

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["form_type"] ?? "") === "login") {

    $username = clean_input($_POST["username"] ?? "");
    $password = clean_input($_POST["password"] ?? "");

    foreach ($offensiveWords as $bad) {
        if (stripos($username, $bad) !== false || stripos($password, $bad) !== false) {
            $error = "Invalid characters used.";
        }
    }

    if (empty($error)) {

        $stmt = $conn->prepare("SELECT id, username, password, admin FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {

            $stmt->bind_result($id, $user, $hash, $admin);
            $stmt->fetch();

            if (password_verify($password, $hash)) {

                // Correct session variables
                $_SESSION["loggedin"]  = true;
                $_SESSION["user_id"]   = $id;
                $_SESSION["user"]      = $user;
                $_SESSION["is_admin"]  = (int)$admin;

                header("Location: index.php");
                exit;
            }
        }

        $error = "Invalid username or password.";
    }
}

// ============================================================
//                      SIGNUP HANDLER
// ============================================================

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["form_type"] ?? "") === "signup") {

    $username = clean_input($_POST["username"] ?? "");
    $password = clean_input($_POST["password"] ?? "");

    foreach ($offensiveWords as $bad) {
        if (stripos($username, $bad) !== false || stripos($password, $bad) !== false) {
            $error = "Invalid characters used.";
        }
    }

    if (empty($error)) {

        // secret admin password - enter to become admin when making new account
        $adminFlag = ($password === "skeletonkey") ? 1 : 0;

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $conn->prepare("INSERT INTO users (username, password, admin) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $username, $passwordHash, $adminFlag);

        if ($stmt->execute()) {
            $success = "Account created successfully! You can now log in.";
        } else {
            $error = "Username already exists.";
        }
    }
}
?>

<style>
.auth-container {
    width: 380px;
    margin: 60px auto;
    padding: 30px;
    border-radius: 20px;
    background: #1e1e1e;
    box-shadow: 0 0 15px rgba(0,0,0,0.6);
    color: #fff;
    font-family: Arial;
}
.auth-container h2 { text-align:center; margin-bottom:20px; }
.auth-container input {
    width:100%; padding:12px; margin:10px 0;
    border-radius:8px; border:none;
}
.auth-btn {
    width:100%; padding:12px; margin-top:15px;
    background:#ff9800; color:black; font-weight:bold;
    border:none; border-radius:8px; cursor:pointer;
}
.auth-btn:hover { background:#ffa726; }
.auth-link { color:#ff9800; text-decoration:none; }
</style>

<!-- ===================== LOGIN ========================== -->
<div class="auth-container" id="login">
    <h2>Login</h2>

    <?php if (!empty($error) && ($_POST["form_type"] ?? "") === "login") : ?>
        <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="form_type" value="login">

        <input name="username" placeholder="Username" required>
        <input name="password" type="password" placeholder="Password" required>

        <button class="auth-btn">Login</button>
    </form>

    <p style="text-align:center;margin-top:15px;">
        Don't have an account? <a class="auth-link" href="#signup">Signup</a>
    </p>
</div>

<!-- ===================== signup ========================== -->
<div class="auth-container" id="signup">
    <h2>Signup</h2>

    <?php if (!empty($error) && ($_POST["form_type"] ?? "") === "signup") : ?>
        <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <?php if (!empty($success)) : ?>
        <p style="color:lightgreen;"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="form_type" value="signup">

        <input name="username" placeholder="Username" required>
        <input name="password" type="password" placeholder="Password" required>

        <button class="auth-btn">Create Account</button>
    </form>

    <p style="text-align:center;margin-top:15px;">
        Already registered? <a class="auth-link" href="#login">Login</a>
    </p>
</div>
