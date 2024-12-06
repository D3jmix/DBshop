<?php
session_start();
require_once 'db.php';

$zalogowany = isset($_SESSION['user_id']);
$isAdmin = false;
  
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


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['wyloguj'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['name'], $_POST['email'], $_POST['message'])) {
        $name = htmlspecialchars($_POST['name']);
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        $message = htmlspecialchars($_POST['message']);

        if (!$email) {
            header('Location: kontakt.php?status=invalid_email');
            exit;
        }
      
        $to = 'djshopdb@dejmix.ct8.pl';
        $subject = 'Nowa wiadomość kontaktowa z formularza';

        $emailMessage = "Imię i nazwisko: $name\n";
        $emailMessage .= "Email: $email\n\n";
        $emailMessage .= "Wiadomość:\n$message";

        $headers = "From: dejmix@dejmix.ct8.pl\r\n";
        $headers .= "Reply-To: $email\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
      
        if (mail($to, $subject, $emailMessage, $headers)) {
            header('Location: kontakt.php?status=ok');
            exit;
        } else {
            header('Location: kontakt.php?status=error');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontakt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        iframe {
            border: 0;
            width: 100%;
            height: 400px;
        }
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

  <section class="py-5">
    <div class="container">
      <h1 class="fw-bold text-center mb-4">Kontakt z nami</h1>
      
      <?php if (isset($_GET['status'])): ?>
      <?php if ($_GET['status'] == 'ok'): ?>
      <div class="alert alert-success" role="alert">
        Wiadomość została pomyślnie wysłana! Dziękujemy za kontakt.
      </div>
      <?php elseif ($_GET['status'] == 'error'): ?>
      <div class="alert alert-danger" role="alert">
        Wystąpił błąd podczas wysyłania wiadomości. Spróbuj ponownie.
      </div>
      <?php endif; ?>
      <?php endif; ?>
      
      <div class="row">
        <div class="col-md-6">
          <h3>Formularz kontaktowy</h3>
          <form method="POST">
            <div class="mb-3">
              <label for="name" class="form-label">Imię i nazwisko</label>
              <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
              <label for="message" class="form-label">Wiadomość</label>
              <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Wyślij wiadomość</button>
          </form>
        </div>
                <div class="col-md-6">
                    <h3>Nasze dane kontaktowe</h3>
                    <p><strong>Adres:</strong> ul. Prosta 123, 00-001 Warszawa, Polska</p>
                    <p><strong>Email:</strong> djshopdb@dejmix.ct8.pl </p>
                    <p><strong>Telefon:</strong> +48 123 456 789</p>
                </div>
            </div>
        </div>
    </section>
        
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>














