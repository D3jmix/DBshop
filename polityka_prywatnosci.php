<?php
session_start();
require_once 'db.php';

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
  
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['wyloguj'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Polityka Prywatności</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .content {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        h1, h2, h3 {
            color: #343a40;
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

    <main class="py-5">
        <div class="container">
            <div class="content">
                <h1>Polityka Prywatności</h1>
                <p>
                    Witaj na stronie DBShop! Dbamy o Twoją prywatność i bezpieczeństwo danych osobowych.
                    Niniejszy dokument określa, jakie dane zbieramy, w jaki sposób je wykorzystujemy oraz jakie masz prawa w związku z ich przetwarzaniem.
                </p>

                <h2>1. Administrator danych</h2>
                <p>
                    Administratorem Twoich danych osobowych jest firma DBShop z siedzibą gdzieś na pewno, ul. Przykładowa 1, 07-429 Warszawa.
                    Możesz skontaktować się z nami poprzez formularz na stronie <a href="kontakt.php">kontakt</a>.
                </p>

                <h2>2. Zakres zbieranych danych</h2>
                <p>
                    Zbieramy dane osobowe w celu realizacji zamówień, obsługi konta użytkownika, marketingu oraz wsparcia klienta. 
                    Dane mogą obejmować:
                </p>
                <ul>
                    <li>Imię i nazwisko</li>
                    <li>Adres e-mail</li>
                    <li>Numer telefonu</li>
                    <li>Adres do wysyłki i rozliczeń</li>
                    <li>Dane dotyczące zamówień (np. historia zakupów)</li>
                </ul>

                <h2>3. Cel przetwarzania danych</h2>
                <p>Dane osobowe przetwarzamy w celu:</p>
                <ul>
                    <li>Realizacji zamówień i usług.</li>
                    <li>Obsługi konta użytkownika.</li>
                    <li>Marketingu i personalizacji ofert.</li>
                    <li>Zapewnienia bezpieczeństwa i przeciwdziałania oszustwom.</li>
                    <li>Zgodności z przepisami prawa.</li>
                </ul>

                <h2>4. Udostępnianie danych</h2>
                <p>
                    Twoje dane mogą być udostępniane partnerom, takim jak firmy kurierskie, operatorzy płatności lub dostawcy usług IT,
                    wyłącznie w celu realizacji zamówień lub zgodnie z wymogami prawnymi.
                </p>

                <h2>5. Twoje prawa</h2>
                <p>Masz prawo do:</p>
                <ul>
                    <li>Dostępu do swoich danych osobowych.</li>
                    <li>Sprostowania swoich danych.</li>
                    <li>Usunięcia danych („prawo do bycia zapomnianym”).</li>
                    <li>Ograniczenia przetwarzania danych.</li>
                    <li>Sprzeciwu wobec przetwarzania danych w celach marketingowych.</li>
                    <li>Przenoszenia danych.</li>
                    <li>Złożenia skargi do organu nadzorczego.</li>
                </ul>

                <h2>6. Zmiany w polityce prywatności</h2>
                <p>
                    Zastrzegamy sobie prawo do wprowadzania zmian w niniejszej polityce prywatności. 
                    Aktualne wersje dokumentu będą publikowane na tej stronie.
                </p>

                <h2>7. Kontakt</h2>
                <p>
                    W razie pytań dotyczących Twojej prywatności, skontaktuj się z nami poprzez stronę 
                    <a href="kontakt.php">kontakt</a> lub na adres e-mail: djshopdb@dejmix.ct8.pl.
                </p>
            </div>
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

