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
  
$produkty = array();
try {
    $stmt = $pdo->query("SELECT id, nazwa, opis, cena, stan, zdjecie FROM produkty ORDER BY data_dodania DESC LIMIT 4");
    $produkty = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Błąd bazy danych: " . $e->getMessage();
}
$kategorie = array();
try {
    $stmt = $pdo->query("SELECT id_kategorii, nazwa, opis FROM Kategorie");
    $kategorie = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Błąd bazy danych: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Główna Strona Sklepu</title>
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
    <section class="py-5">
    <div class="container">
        <h2 class="text-center mb-4">Kategorie produktów</h2>
        <div class="row">
            <?php if (!empty($kategorie)): ?>
                <?php foreach ($kategorie as $kategoria): ?>
                    <div class="col-md-4 py-2">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column text-center">
                                <h5 class="card-title flex-grow-1"><?= htmlspecialchars($kategoria['nazwa']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars($kategoria['opis']) ?></p>
                                <a href="kategorie_strona.php?kategoria=<?= $kategoria['id_kategorii'] ?>" class="btn btn-outline-primary">Zobacz więcej</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center">Brak dostępnych kategorii do wyświetlenia.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

    <section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-4">Polecane produkty</h2>
        <div class="row">
            <?php if (!empty($produkty)): ?>
                <?php foreach ($produkty as $produkt): ?>
                    <div class="col-md-3">
                        <div class="card h-100">
                            <img src="<?= htmlspecialchars($produkt['zdjecie']) ?>" class="card-img-top" alt="<?= htmlspecialchars($produkt['nazwa']) ?>">
                            <div class="card-body d-flex flex-column text-center">
                                <h5 class="card-title flex-grow-1"><?= htmlspecialchars($produkt['nazwa']) ?></h5>
                                <p class="card-text">Cena: <?= number_format($produkt['cena'], 2, ',', ' ') ?> zł</p>
                                <a href="produkt_strona.php?id=<?= $produkt['id'] ?>" class="btn btn-primary">Zobacz szczegóły</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center">Brak polecanych produktów do wyświetlenia.</p>
            <?php endif; ?>
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

































