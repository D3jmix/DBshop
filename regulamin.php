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
    <title>Regulamin</title>
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
            <h1 class="text-center mb-4">Regulamin Sklepu Online</h1>
            <div class="bg-light p-4 rounded">
                <h2>1. Postanowienia ogólne</h2>
                <p>
                    Niniejszy regulamin określa zasady korzystania z serwisu SklepOnline, składania zamówień, zawierania umów sprzedaży oraz zasady zwrotów i reklamacji.
                </p>

                <h2>2. Definicje</h2>
                <p>
                    <strong>Klient:</strong> osoba fizyczna, prawna lub jednostka organizacyjna, która dokonuje zakupów w sklepie.<br>
                    <strong>Sprzedawca:</strong> DBShopz z siedzibą w Warszawie, ul. Przykładowa 1, 00-001 Warszawa.<br>
                    <strong>Produkt:</strong> towary dostępne w sklepie internetowym.
                </p>

                <h2>3. Zasady korzystania z serwisu</h2>
                <ul>
                    <li>Klient zobowiązany jest do podania prawdziwych danych podczas rejestracji i składania zamówienia.</li>
                    <li>Zabronione jest wykorzystywanie serwisu w sposób niezgodny z prawem.</li>
                </ul>

                <h2>4. Składanie zamówień</h2>
                <p>
                    Zamówienia można składać za pośrednictwem strony internetowej 24/7.
                </p>

                <h2>5. Płatności</h2>
                <p>
                    Dostępne metody płatności obejmują przelew bankowy, płatności online oraz płatność za pobraniem.
                </p>

                <h2>6. Dostawa</h2>
                <p>
                    Produkty dostarczane są za pośrednictwem firm kurierskich na adres wskazany przez klienta. 
                    Termin dostawy wynosi od 2 do 7 dni roboczych.
                </p>

                <h2>7. Zwroty i reklamacje</h2>
                <p>
                    Klient ma prawo odstąpić od umowy w ciągu 14 dni od daty otrzymania produktu bez podania przyczyny. 
                    Reklamacje można zgłaszać poprzez formularz dostępny na stronie w panelu użytkownika.
                </p>

                <h2>8. Postanowienia końcowe</h2>
                <p>
                    Regulamin wchodzi w życie z dniem publikacji na stronie internetowej. 
                    Sprzedawca zastrzega sobie prawo do zmian w regulaminie.
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

