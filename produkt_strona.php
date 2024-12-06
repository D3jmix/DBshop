<?php
session_start();
require_once 'db.php';

$zalogowany = isset($_SESSION['user_id']);
$czyAdmin = false;
$product_id = $_GET['id'] ?? ($_POST['id'] ?? null);
  
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
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($product_id <= 0) {
    exit;
}

$produkt = null;
if ($product_id) {
    $stmt = $pdo->prepare("SELECT * FROM produkty WHERE id = :id");
    $stmt->bindValue(':id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $produkt = $stmt->fetch(PDO::FETCH_ASSOC);
}

$powiazane_produkty = array();
if ($product_id) {
    $stmt = $pdo->prepare("SELECT DISTINCT p.* FROM produkty p JOIN kategorie_k kk ON p.id = kk.id_produktu WHERE kk.id_kategorii IN (SELECT id_kategorii FROM kategorie_k WHERE id_produktu = :id) AND p.id != :id");
    $stmt->bindValue(':id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $powiazane_produkty = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['dodaj_koszyk'])) {
    $user_id = $_SESSION['user_id'] ?? null;
    $product_id = $_POST['id'] ?? null;
    $ilosc = $_POST['ilosc'] ?? null;

    if ($user_id && $product_id && $ilosc) {
      
        if ($ilosc <= 0) {
            $alert_message = 'Ilość produktu musi być większa niż 0.';
            $alert_type = 'alert-danger';
        } else {
             $stmt = $pdo->prepare("SELECT stan FROM produkty WHERE id = :id");
            $stmt->bindValue(':id', $product_id, PDO::PARAM_INT);
            $stmt->execute();
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($product) {
                $stan_magazynowy = $product['stan'];

                if ($ilosc > $stan_magazynowy) {
                    $alert_message = "Nie możesz dodać do koszyka więcej niż $stan_magazynowy sztuk tego produktu.";
                    $alert_type = 'alert-danger';
                } else {
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM koszyk WHERE id_user = :id_user AND id_produkt = :id_produkt");
                    $stmt->bindValue(':id_user', $user_id, PDO::PARAM_INT);
                    $stmt->bindValue(':id_produkt', $product_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $existingProduct = $stmt->fetchColumn();

                    if ($existingProduct > 0) {
                        $alert_message = 'Ten produkt jest już w Twoim koszyku!';
                        $alert_type = 'alert-warning';
                    } else {
                        try {
                            $stmt = $pdo->prepare("INSERT INTO koszyk (id_user, id_produkt, ilosc) VALUES (:id_user, :id_produkt, :ilosc)");
                            $stmt->bindValue(':id_user', $user_id, PDO::PARAM_INT);
                            $stmt->bindValue(':id_produkt', $product_id, PDO::PARAM_INT);
                            $stmt->bindValue(':ilosc', $ilosc, PDO::PARAM_INT);
                            $stmt->execute();
                            $alert_message = 'Produkt został dodany do koszyka!';
                            $alert_type = 'alert-success';
                        } catch (PDOException $e) {
                            $alert_message = 'Błąd: ' . $e->getMessage();
                            $alert_type = 'alert-danger';
                        }
                    }
                }
            } else {
                $alert_message = 'Produkt nie istnieje.';
                $alert_type = 'alert-danger';
            }
        }
    } else {
        $alert_message = 'Brakuje danych do dodania do koszyka.';
        $alert_type = 'alert-danger';
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['dodaj_ulubione'])) {
    $user_id = $_SESSION['user_id'] ?? null;
    $product_id = $_POST['id'] ?? null;

    if ($user_id && $product_id) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM ulubione WHERE id_user = :id_user AND id_produkt = :id_produkt");
        $stmt->bindValue(':id_user', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':id_produkt', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        $czyIstnieje = $stmt->fetchColumn();

        if ($czyIstnieje > 0) {
            $alert_message = 'Ten produkt jest już w Twoich ulubionych!';
            $alert_type = 'alert-warning';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO ulubione (id_user, id_produkt) VALUES (:id_user, :id_produkt)");
                $stmt->bindValue(':id_user', $_SESSION['user_id'], PDO::PARAM_INT);
                $stmt->bindValue(':id_produkt', $product_id, PDO::PARAM_INT);
                $stmt->execute();
                $alert_message = 'Produkt został dodany do ulubionych.';
                $alert_type = 'alert-success';
            } catch (PDOException $e) {
                $alert_message = 'Błąd: ' . $e->getMessage();
                $alert_type = 'alert-danger';
            }
        }
    } else {
        $alert_message = 'Musisz być zalogowany, aby dodać produkt do ulubionych.';
        $alert_type = 'alert-danger';
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($produkt['nazwa']); ?> - DBshop</title>
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
            <div class="row">
                <div class="col-md-6">
                    <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img src="<?php echo htmlspecialchars($produkt['zdjecie']); ?>" class="d-block w-100" alt="Zdjęcie produktu">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <h1 class="fw-bold"><?php echo htmlspecialchars($produkt['nazwa']); ?></h1>
                    <p class="mt-3">Stan produktu w magazynie: <?php echo htmlspecialchars($produkt['stan']); ?></p>
                    <h2 class="text-success"><?php echo number_format($produkt['cena'], 2, ',', ' '); ?> zł</h2>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="ilosc" class="form-label">Ilość:</label>
                            <input type="number" id="ilosc" name="ilosc" class="form-control w-25" value="1" min="1" required>
                        </div>
                        <input type="hidden" name="id" value="<?= htmlspecialchars($product_id) ?>">

                        <button type="submit" name="dodaj_koszyk" class="btn btn-primary btn-lg">Dodaj do koszyka</button>
                        <button type="submit" name="dodaj_ulubione" class="btn btn-outline-secondary btn-lg">Dodaj do ulubionych</button>
                    </form>
                </div>
            </div>
          <div class="container mt-3">
            <?php if (isset($alert_message)): ?>
            <div class="alert <?php echo $alert_type; ?> alert-dismissible fade show" role="alert">
              <?php echo htmlspecialchars($alert_message); ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
          </div>
        </div>
    </section>

    <section class="py-5 bg-light">
        <div class="container">
            <ul class="nav nav-tabs" id="productTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab">Opis</button>
                </li>
            </ul>
            <div class="tab-content mt-4" id="productTabsContent">
                <div class="tab-pane fade show active" id="description" role="tabpanel">
                    <p><?php echo nl2br(htmlspecialchars($produkt['opis'])); ?></p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-4">Produkty powiązane</h2>
            <div class="row">
                <?php foreach ($powiazane_produkty as $powiazane): ?>
                <div class="col-md-3">
                    <div class="card">
                        <img src="<?php echo htmlspecialchars($powiazane['zdjecie']); ?>" class="card-img-top" alt="Produkt">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?php echo htmlspecialchars($powiazane['nazwa']); ?></h5>
                            <p class="card-text">Cena: <?php echo number_format($powiazane['cena'], 2, ',', ' '); ?> zł</p>
                            <a href="produkt_strona.php?id=<?php echo $powiazane['id']; ?>" class="btn btn-primary">Zobacz</a>
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
</body>
</html>

























































