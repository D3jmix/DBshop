<?php
require_once 'db.php'; 
session_start();
$error_mes = "";
$success_mes = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    if (empty($email)) {
        $error_mes = "Pole email jest wymagane.";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_mes = "Wprowadź poprawny adres email.";
    } else {
        $stmt = $pdo->prepare("SELECT reset_token_expires, reset_token FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            $error_mes = "Nie znaleziono użytkownika z podanym adresem e-mail.";
        }else{
            $reset_token_expires = $user['reset_token_expires'];
            $reset_token = $user['reset_token'];
          
            $now = new DateTime();
            if ($now < new DateTime($reset_token_expires) && $reset_token) {
                $error_mes = "Link resetujący hasło już został wysłany. Sprawdź swoją skrzynkę lub poczekaj chwilę przed ponownym wysłaniem.";
            }else {
            $token = bin2hex(random_bytes(32));
            $expires_at = date("Y-m-d H:i:s", strtotime('+1 hour'));
            
            $stmt = $pdo->prepare("UPDATE users SET reset_token = :token, reset_token_expires = :expires_at WHERE email = :email");
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':expires_at', $expires_at);
            $stmt->bindParam(':email', $email);
            
            if ($stmt->execute()) {
                if (sendPassReset($email, $token)) {
                    $success_mes = "Link resetujący hasło został wysłany na podany email.";
                } else {
                    $error_mes = "Wystąpił problem podczas wysyłania wiadomości. Spróbuj ponownie później.";
                }
            }else{
               $error_mes = "Nie udało się zaktualizować danych użytkownika. Spróbuj ponownie później.";
            }
        }
     }
   }
}

function sendPassReset($email, $token) {
    $subject = "Resetowanie hasła";
    $resetLink = "https://dejmix.ct8.pl/reset_passw.php?token=" . urlencode($token);
    $message = "Kliknij poniższy link, aby zresetować hasło (ważny przez 1 godzinę): \n\n" . $resetLink;
    $headers = "From: djshopdb@dejmix.ct8.pl\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    return mail($email, $subject, $message, $headers);
  }
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Przypomnij hasło</title>
    <link rel="stylesheet" href="styles/lostPasw.css"/>
    <script src="https://kit.fontawesome.com/0811bb0147.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="background">
        <div class="form-box">
            <h1>Przypomnij hasło</h1>
            <?php if ($error_mes): ?>
                <div class="error-box"><p><?php echo $error_mes; ?></p></div>
            <?php elseif ($success_mes): ?>
                <div class="success-box"><p><?php echo $success_mes; ?></p></div>
            <?php endif; ?>
            <form action="LostPasw.php" method="POST">
                <div class="input-field">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" name="email" placeholder="Podaj swój email" required>
                </div>
                <button type="submit" class="submit-button">Wyślij Email</button>
            </form>
        </div>
    </div>
</body>
</html>


<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Przypomnij hasło</title>
    <link rel="stylesheet" href="styles/lostPasw.css"/>
    <script src="https://kit.fontawesome.com/0811bb0147.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="background">
        <div class="form-box">
            <h1>Przypomnij hasło</h1>
            <?php if ($error_mes): ?>
                <div class="error-box"><p><?php echo $error_mes; ?></p></div>
            <?php elseif ($success_mes): ?>
                <div class="success-box"><p><?php echo $success_mes; ?></p></div>
            <?php endif; ?>
            <form action="LostPasw.php" method="POST">
                <div class="input-field">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" name="email" placeholder="Podaj swój email" required>
                </div>
                <button type="submit" class="submit-button">Wyślij Email</button>
            </form>
        </div>
    </div>
</body>
</html>














