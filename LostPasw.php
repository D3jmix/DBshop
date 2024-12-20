<?php
require_once 'db.php'; 
session_start();
$error_mes = "";
$success_mes = "";
$zalogowany = isset($_SESSION['user_id']);
$czyAdmin = false;
  
if ($zalogowany) {
    $userId = $_SESSION['user_id'];
    $czyAdmin = false;
     try {
        $query = $pdo->prepare("SELECT admin FROM konta WHERE id = :id");
        $query->bindParam(':id', $userId, PDO::PARAM_INT);
        $query->execute();
        $admin = $query->fetchColumn();

        $czyAdmin = ($admin == 1);
    } catch (PDOException $e) {
        die("Błąd zapytania do bazy danych: " . $e->getMessage());
    }
}
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    if (empty($email)) {
        $error_mes = "Pole email jest wymagane.";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_mes = "Wprowadź poprawny adres email.";
    } else {
        $stmt = $pdo->prepare("SELECT reset_token_expires, reset_token FROM konta WHERE email = :email");
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
            
            $stmt = $pdo->prepare("UPDATE konta SET reset_token = :token, reset_token_expires = :expires_at WHERE email = :email");
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/lostPasw.css"/>
    <script src="https://kit.fontawesome.com/0811bb0147.js" crossorigin="anonymous"></script>
    <style>
      .wyloguj-btn {
        background-color: orange;
        color: white;
        border: none;
      }

      .wyloguj-btn:hover {
        background-color: red;
        color: white;
      }

      .wyloguj-btn:focus, .wyloguj-btn:active {
        background-color: red;
        color: white;
      }
      </style>
</head>
<body>
  <header class="bg-dark text-white py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="index.php" class="text-white text-decoration-none fs-4"><img src="/photos/logo.png" alt="DB shop" width="70px" height="70px"></a>
            <nav class="mx-auto">
                <ul class="nav justify-content-center">
                    <li class="nav-item"><a href="kategoria_wybor.php" class="nav-link text-white">Kategorie</a></li>
                    <li class="nav-item"><a href="produkty_str.php" class="nav-link text-white">Produkty</a></li>
                    <li class="nav-item"><a href="kontakt.php" class="nav-link text-white">Kontakt</a></li>
                </ul>
            </nav>
            <div class="d-flex align-items-center">
              <a href="koszyk.php" class="text-decoration-none me-4">
                <i class="bi bi-cart fs-4"></i>
              </a>
              <a href="ulubione.php" class="text-decoration-none me-4">
                <i class="bi bi-bag-heart fs-4"></i>
              </a>
                <div class="dropdown">
                  <button class="btn btn-secondary dropdown-toggle" type="button" id="menuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    Menu
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="menuDropdown">
                    <?php if ($zalogowany): ?>
                    <li><a class="dropdown-item" href="panel_uzytkownika.php">Panel użytkownika</a></li>
                    <?php if ($czyAdmin): ?>
                    <li><a class="dropdown-item" href="panel_admina.php">Panel admina</a></li>
                    <?php endif; ?>
                    <li><a class="dropdown-item" href="twoje_zamowienia.php">Twoje zamówienia</a></li>
                    <?php else: ?>
                    <li><a class="dropdown-item" href="login.php">Zaloguj się</a></li>
                    <?php endif; ?>
                    <li><a class="dropdown-item" href="kategorie_strona.php">Kategorie</a></li>
                    <li><a class="dropdown-item" href="kontakt.php">Kontakt</a></li>
                    <li><a class="dropdown-item" href="polityka_prywatnosci.php">Polityka prywatności</a></li>
                    <li><a class="dropdown-item" href="regulamin.php">Regulamin</a></li>
                    <li><a class="dropdown-item" href="faq.php">FAQ</a></li>
                    <?php if ($zalogowany): ?>
                    <li>
                        <form method="POST" class="d-inline">
                            <button type="submit" name="wyloguj" class="dropdown-item wyloguj-btn">Wyloguj</button>
                        </form>
                    </li>
                    <?php endif; ?>
                  </ul>
                </div>
          </div>
      </div>
  </header>
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
  <footer class="bg-dark text-white py-4">
    <div class="container text-center">
        <p>&copy; 2024 DBShop. Wszystkie prawa zastrzeżone.</p>
        <div class="row">
            <div class="col-12">
                <a href="polityka_prywatnosci.php" class="text-white text-decoration-none me-3">Polityka prywatności</a>
                <a href="regulamin.php" class="text-white text-decoration-none">Regulamin</a>
            </div>
            <div class="col-12 mt-2">
                <a href="faq.php" class="text-white text-decoration-none">FAQ</a>
            </div>
        </div>
    </div>
</footer>
</body>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</html>















