<?php
require_once 'db.php';
session_start();

$error_mes = "";
$success_mes = "";
$email = "";
$token_error = false;
$token = isset($_GET['token']) ? $_GET['token'] : "";
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
  

if ($token) {
    $stmt = $pdo->prepare("SELECT email, reset_token_expires FROM konta WHERE reset_token = :token");
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
        
        $stmt = $pdo->prepare("UPDATE konta SET password = :password, reset_token = NULL, reset_token_expires = NULL WHERE email = :email");
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/reset_passw.css">
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






