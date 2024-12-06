<?php
session_start();
require_once 'db.php';

$zalogowany = isset($_SESSION['user_id']);
$czyAdmin = false;
$errorMessage = '';
$successMessage = '';
  
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
    header("Location: index.php");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['numer_zamowienia'], $_POST['produkt'], $_POST['opis_reklamacji'])) {
        
        $numer_zamowienia = $_POST['numer_zamowienia'];
        $produkt = $_POST['produkt'];
        $opis_reklamacji = $_POST['opis_reklamacji'];
        
        $zdjecia = [];
        if (isset($_FILES['zdjecia'])) {
            foreach ($_FILES['zdjecia']['tmp_name'] as $key => $tmp_name) {
                $file_name = $_FILES['zdjecia']['name'][$key];
                $file_tmp = $_FILES['zdjecia']['tmp_name'][$key];
                $file_type = $_FILES['zdjecia']['type'][$key];
                
                $upload_dir = 'uploads/';
                $upload_file = $upload_dir . basename($file_name);
                
                if (move_uploaded_file($file_tmp, $upload_file)) {
                    $zdjecia[] = $upload_file;
                }
            }
        }
        
        $message = "Numer zamówienia: $numer_zamowienia\n";
        $message .= "Nazwa produktu: $produkt\n";
        $message .= "Opis reklamacji:\n$opis_reklamacji\n";
        
        if (!empty($zdjecia)) {
            $message .= "\nZałączone zdjęcia:\n";
            foreach ($zdjecia as $zdjecie) {
                $message .= $zdjecie . "\n";
            }
        }
        
        $from = 'dejmix@dejmix.ct8.pl';
        $to = 'djshopdb@dejmix.ct8.pl';
        
        $subject = "Reklamacja produktu: $produkt";
        
        $headers = "From: $from\r\n";
        $headers .= "Reply-To: $from\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        if (mail($to, $subject, $message, $headers)) {
            $successMessage = "Reklamacja została wysłana pomyślnie.";
        } else {
             $errorMessage = "Wystąpił błąd podczas wysyłania reklamacji.";
        }
    } else {
       $errorMessage = "Proszę wypełnić wszystkie wymagane pola.";
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formularz reklamacji</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
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
  
    <main class="py-5">
        <div class="container">
            <h1 class="text-center mb-4">Formularz reklamacji</h1>
            <p class="lead text-center">Proszę uzupełnić poniższy formularz, aby zgłosić reklamację produktu.</p>

             <?php if ($errorMessage): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $errorMessage; ?>
                </div>
            <?php endif; ?>

            <?php if ($successMessage): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $successMessage; ?>
                </div>
            <?php endif; ?>
          
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="numer_zamowienia" class="form-label">Numer zamówienia</label>
                    <input type="text" class="form-control" id="numer_zamowienia" name="numer_zamowienia" required>
                </div>
                <div class="mb-3">
                    <label for="produkt" class="form-label">Nazwa produktu</label>
                    <input type="text" class="form-control" id="produkt" name="produkt" required>
                </div>
                <div class="mb-3">
                    <label for="opis_reklamacji" class="form-label">Opis reklamacji</label>
                    <textarea class="form-control" id="opis_reklamacji" name="opis_reklamacji" rows="5" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="zdjecia" class="form-label">Załącz zdjęcia (opcjonalnie)</label>
                    <input type="file" class="form-control" id="zdjecia" name="zdjecia[]" multiple>
                    <small class="form-text text-muted">Możesz załączyć zdjęcia uszkodzonego produktu.</small>
                </div>
                <button type="submit" class="btn btn-primary">Wyślij reklamację</button>
            </form>
        </div>
    </main>

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

















