<?php
require_once 'db.php';
session_start();

$error_mes = "";
$success_mes = "";
$email = "";
$token_error = false;
$token = isset($_GET['token']) ? $_GET['token'] : "";

if ($token) {
    $stmt = $pdo->prepare("SELECT email, reset_token_expires FROM users WHERE reset_token = :token");
    $stmt->bindParam(":token", $token);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $email = $user['email'];
        $reset_token_expires = $user['reset_token_expires'];
      
        $now = new DateTime();
        if (new DateTime($reset_token_expires) < $now) {
            $error_mes = "Link resetujący hasło jest nieprawidłowy lub wygasł.";
            $token_error = true;
        }
    } else {
        $error_mes = "Link resetujący hasło jest nieprawidłowy lub wygasł.";
    }
} else {
    $error_mes = "Link wygasł lub token jest nie prawidłowy";
    $token_error = true;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && !$token_error) {
    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];

    if (empty($new_password) || empty($confirm_password)) {
        $error_mes = "Oba pola hasła są wymagane.";
    } elseif ($new_password !== $confirm_password) {
        $error_mes = "Hasła muszą być identyczne.";
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $new_password)) {
        $error_mes = "Hasło musi mieć co najmniej 8 znaków, zawierać małą i dużą literę, cyfrę oraz znak specjalny.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("UPDATE users SET password = :password, reset_token = NULL, reset_token_expires = NULL WHERE email = :email");
        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":email", $email);
        
        if ($stmt->execute()) {
            $success_mes = "Hasło zostało pomyślnie zresetowane. Możesz się teraz <a href='https://dejmix.ct8.pl/login.php'>zalogować</a>.";
        } else {
            $error_mes = "Wystąpił problem podczas aktualizacji hasła. Spróbuj ponownie później.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resetowanie hasła</title>
    <link rel="stylesheet" href="styles/reset_passw.css">
    <script src="https://kit.fontawesome.com/0811bb0147.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="background">
        <div class="form-box">
            <h1>Resetowanie hasła</h1>
            
            <?php if ($error_mes): ?>
                <div class="error-box"><p><?php echo htmlspecialchars($error_mes); ?></p></div>
            <?php elseif ($success_mes): ?>
                <div class="success-box"><p><?php echo $success_mes; ?></p></div>
            <?php endif; ?>

            <?php if (!$token_error && empty($success_mes)): ?>
            <form action="reset_passw.php?token=<?php echo htmlspecialchars($token); ?>" method="POST">
                <div class="input-field">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" readonly>
                </div>
                
                <div class="input-field">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="new_password" placeholder="Wpisz nowe hasło" required>
                </div>
                
                <div class="input-field">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="confirm_password" placeholder="Potwierdź hasło" required>
                </div>
                
                <button type="submit" class="submit-button">Zresetuj hasło</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>




