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

if (isset($_GET['kategoria']) && is_numeric($_GET['kategoria'])) {
    $kategoria_id = $_GET['kategoria'];
} else {
    $kategoria_id = 1;
}

$stmt = $pdo->prepare("SELECT nazwa FROM Kategorie WHERE id_kategorii = :kategoria_id");
$stmt->bindValue(':kategoria_id', $kategoria_id, PDO::PARAM_INT);
$stmt->execute();
$kategoria = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$kategoria) {
    header("Location: kategoria_wybor.php");
    exit;
}

$query = "SELECT p.id, p.nazwa, p.cena, p.zdjecie FROM produkty p JOIN kategorie_k kk ON p.id = kk.id_produktu WHERE kk.id_kategorii = :kategoria_id";
  
if (isset($_GET['maxCena']) && is_numeric($_GET['maxCena'])) {
    $max_cena = $_GET['maxCena'];
    $query .= " AND p.cena <= :max_cena";
}

if (isset($_GET['sortBy'])) {
    if ($_GET['sortBy'] == 'priceAsc') {
        $query .= " ORDER BY p.cena ASC";
    } elseif ($_GET['sortBy'] == 'priceDesc') {
        $query .= " ORDER BY p.cena DESC";
    }
}

$stmt = $pdo->prepare($query);

$stmt->bindValue(':kategoria_id', $kategoria_id, PDO::PARAM_INT);

if (isset($max_cena)) {
    $stmt->bindValue(':max_cena', $max_cena, PDO::PARAM_INT);
}

$stmt->execute();
$produkty = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
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
    <title>Kategoria: <?= htmlspecialchars($kategoria['nazwa']) ?></title>
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
    <section class="py-5 bg-light">
        <div class="container text-center">
            <h1 class="fw-bold">Kategoria: <?= htmlspecialchars($kategoria['nazwa']) ?></h1>
            <p class="text-muted">Znajdź najlepsze produkty w tej kategorii.</p>
        </div>
    </section>

    <section class="py-3">
        <div class="container">
            <form class="row g-3" method="GET" action="">
                <input type="hidden" name="kategoria" value="<?= $kategoria_id ?>">

                <div class="col-md-4">
                    <label for="sortBy" class="form-label">Sortuj według:</label>
                    <select id="sortBy" name="sortBy" class="form-select">
                        <option value="priceAsc" <?= isset($_GET['sortBy']) && $_GET['sortBy'] == 'priceAsc' ? 'selected' : '' ?>>Cena: od najniższej</option>
                        <option value="priceDesc" <?= isset($_GET['sortBy']) && $_GET['sortBy'] == 'priceDesc' ? 'selected' : '' ?>>Cena: od najwyższej</option>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label for="priceRange" class="form-label">Zakres cen:</label>
                    <input type="range" id="priceRange" name="maxCena" class="form-range" min="0" max="10000" step="100" value="<?= isset($_GET['maxCena']) ? $_GET['maxCena'] : 10000 ?>">
                    <p class="small text-muted">Maksymalna cena: <span id="priceValue"><?= isset($_GET['maxCena']) ? $_GET['maxCena'] : 10000 ?> zł</span></p>
                </div>
                
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filtruj</button>
                </div>
            </form>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <?php foreach ($produkty as $produkt): ?>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <img src="<?= htmlspecialchars($produkt['zdjecie']) ?>" class="card-img-top" alt="<?= htmlspecialchars($produkt['nazwa']) ?>">
                            <div class="card-body d-flex flex-column text-center">
                                <h5 class="card-title"><?= htmlspecialchars($produkt['nazwa']) ?></h5>
                                <p class="card-text">Cena: <?= number_format($produkt['cena'], 2, ',', ' ') ?> zł</p>
                                <a href="produkt_strona.php?id=<?= $produkt['id'] ?>" class="btn btn-primary">Zobacz szczegóły</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
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
    <script>
        document.getElementById('priceRange').addEventListener('input', function() {
            document.getElementById('priceValue').innerText = this.value + ' zł';
        });
    </script>
</body>
</html>






























