<?php
session_start();
require_once 'db.php';

$zalogowany = isset($_SESSION['user_id']);
$czyAdmin = false;
$produkty = array();
$kategorie = array();
  
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_favorite'])) {
    $produktId = (int) $_POST['produkt_id'];
    $userId = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT * FROM ulubione WHERE id_user = :user_id AND id_produkt = :produkt_id");
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':produkt_id', $produktId, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $deleteStmt = $pdo->prepare("DELETE FROM ulubione WHERE id_user = :user_id AND id_produkt = :produkt_id");
        $deleteStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $deleteStmt->bindValue(':produkt_id', $produktId, PDO::PARAM_INT);
        $deleteStmt->execute();
    } else {
        $insertStmt = $pdo->prepare("INSERT INTO ulubione (id_user, id_produkt) VALUES (:user_id, :produkt_id)");
        $insertStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $insertStmt->bindValue(':produkt_id', $produktId, PDO::PARAM_INT);
        $insertStmt->execute();
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$cena_min = isset($_GET['cena_min']) ? $_GET['cena_min'] : 0;
$cena_max = isset($_GET['maxCena']) ? $_GET['maxCena'] : 15000;
$kategoria_id = isset($_GET['kategoria']) ? $_GET['kategoria'] : 0;
$sortBy = isset($_GET['sortBy']) ? $_GET['sortBy'] : '';

try {
    $query = "SELECT DISTINCT p.id, p.nazwa, p.opis, p.cena, p.stan, p.zdjecie FROM produkty p JOIN kategorie_k kk ON p.id = kk.id_produktu WHERE p.cena BETWEEN :cena_min AND :cena_max";

    if ($kategoria_id > 0) {
        $query .= " AND kk.id_kategorii = :id_kategorii";
    }

    if ($sortBy === 'priceAsc') {
        $query .= " ORDER BY p.cena ASC";
    } elseif ($sortBy === 'priceDesc') {
        $query .= " ORDER BY p.cena DESC";
    } else {
        $query .= " ORDER BY p.data_dodania DESC";
    }

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':cena_min', $cena_min, PDO::PARAM_INT);
    $stmt->bindValue(':cena_max', $cena_max, PDO::PARAM_INT);

    if ($kategoria_id > 0) {
        $stmt->bindValue(':id_kategorii', $kategoria_id, PDO::PARAM_INT);
    }

    $stmt->execute();
    $produkty = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Błąd bazy danych: " . $e->getMessage();
}

try {
    $stmt = $pdo->query("SELECT id_kategorii, nazwa FROM Kategorie");
    $kategorie = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Błąd bazy danych: " . $e->getMessage();
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
    <title>Produkty</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
     <style>
        .bi-heart, .bi-heart-fill {
            transition: transform 0.2s ease, color 0.2s ease;
        }
        .bi-heart:hover, .bi-heart-fill:hover {
            transform: scale(1.2);
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
                <input type="range" id="priceRange" name="maxCena" class="form-range" min="0" max="15000" step="100" value="<?= isset($_GET['maxCena']) ? $_GET['maxCena'] : 15000 ?>">
                <p class="small text-muted">Maksymalna cena: <span id="priceValue"><?= isset($_GET['maxCena']) ? $_GET['maxCena'] : 15000 ?> zł</span></p>
            </div>

            <div class="col-md-4">
                <label for="kategoria" class="form-label">Kategoria</label>
                <select name="kategoria" id="kategoria" class="form-select">
                    <option value="0">Wszystkie</option>
                    <?php foreach ($kategorie as $kategoria): ?>
                        <option value="<?= $kategoria['id_kategorii'] ?>" <?= $kategoria['id_kategorii'] == $kategoria_id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($kategoria['nazwa']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Zastosuj filtr</button>
            </div>
        </form>
    </div>
</section>
<section class="container mt-5">
    <h2>Produkty</h2>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        <?php foreach ($produkty as $produkt): ?>
        <div class="col">
            <div class="card h-100 d-flex flex-column">
                <img src="<?= htmlspecialchars($produkt['zdjecie']) ?>" class="card-img-top" alt="<?= htmlspecialchars($produkt['nazwa']) ?>">
                <div class="card-body text-center d-flex flex-column justify-content-between">
                    <h5 class="card-title"><?= htmlspecialchars($produkt['nazwa']) ?></h5>
                    <p class="card-text">Cena: <?= number_format($produkt['cena'], 2, ',', ' ') ?> zł</p>
                    <form method="post">
                        <a href="produkt_strona.php?id=<?= $produkt['id'] ?>" class="btn btn-primary mt-3">Zobacz szczegóły</a>
                        <input type="hidden" name="produkt_id" value="<?= $produkt['id'] ?>">
                        <?php if ($zalogowany): ?>
                        <button type="submit" name="toggle_favorite" class="btn btn-light mt-3">
                            <?php
                            $stmt = $pdo->prepare("SELECT * FROM ulubione WHERE id_user = :user_id AND id_produkt = :produkt_id");
                            $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                            $stmt->bindValue(':produkt_id', $produkt['id'], PDO::PARAM_INT);
                            $stmt->execute();
                            if ($stmt->rowCount() > 0):
                                ?>
                                <i class="bi bi-heart-fill text-danger"></i>
                            <?php else: ?>
                                <i class="bi bi-heart"></i>
                            <?php endif; ?>
                        </button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
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






































