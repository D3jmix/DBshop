<?php
session_start();
require_once 'db.php';

$zalogowany = false;
$username = '';
$ulubioneProdukty = array();
$listyUlubionych = array();
$wszystkieUlubioneProdukty = array();
$wybranaLista = null;
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
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT username FROM konta WHERE id=:id");
    $stmt->bindValue(':id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $zalogowany = true;
        $username = $user['username'];

        $stmt = $pdo->prepare("SELECT id, nazwa FROM listy_ulubionych WHERE id_user = :user_id");
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        $listyUlubionych = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $query = "SELECT p.id, p.nazwa, p.cena, p.zdjecie FROM produkty p JOIN ulubione u ON p.id = u.id_produkt WHERE u.id_user = :user_id";
        
        if (isset($_GET['lista_id'])) {
            $wybranaLista = (int)$_GET['lista_id'];
            $query .= " AND p.id IN (SELECT id_produkt FROM ulubione_list_dod WHERE id_lista = :lista_id)";
        }

        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        if ($wybranaLista !== null) {
            $stmt->bindValue(':lista_id', $wybranaLista, PDO::PARAM_INT);
        }
        $stmt->execute();
        $ulubioneProdukty = $stmt->fetchAll(PDO::FETCH_ASSOC);
      
        $stmt = $pdo->prepare("SELECT p.id, p.nazwa, p.cena FROM produkty p JOIN ulubione u ON p.id = u.id_produkt WHERE u.id_user = :user_id");
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        $wszystkieUlubioneProdukty = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['wyloguj'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}
  
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['dodaj_lista'])) {
    $nazwaListy = trim($_POST['nazwa_listy']);
    if (!empty($nazwaListy)) {
        $stmt = $pdo->prepare("INSERT INTO listy_ulubionych (id_user, nazwa) VALUES (:id_user, :nazwa)");
        $stmt->bindValue(':id_user', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':nazwa', $nazwaListy, PDO::PARAM_STR);
        $stmt->execute();
        header("Location: ulubione.php");
        exit;
    }
}
  
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['dodaj_do_listy'])) {
    $produkty = $_POST['produkty'];
    $listaId = $_POST['lista_id'];

    foreach ($produkty as $produktId) {
        $stmt = $pdo->prepare("SELECT 1 FROM ulubione_list_dod WHERE id_produkt = :id_produkt AND id_lista = :id_lista");
        $stmt->bindValue(':id_produkt', $produktId, PDO::PARAM_INT);
        $stmt->bindValue(':id_lista', $listaId, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            $stmt = $pdo->prepare("INSERT INTO ulubione_list_dod (id_produkt, id_lista, id_user) VALUES (:id_produkt, :id_lista, :id_user)");
            $stmt->bindValue(':id_produkt', $produktId, PDO::PARAM_INT);
            $stmt->bindValue(':id_lista', $listaId, PDO::PARAM_INT);
            $stmt->bindValue(':id_user', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->execute();
        }
    }

    header("Location: ulubione.php?lista_id=" . $listaId);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['usun_ulubione'])) {
    $produkt_id = $_POST['produkt_id'];

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM ulubione_list_dod WHERE id_user = :user_id AND id_produkt = :produkt_id");
    $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->bindValue(':produkt_id', $produkt_id, PDO::PARAM_INT);
    $stmt->execute();
    $liczbaList = $stmt->fetchColumn();

    if ($liczbaList > 0) {
        echo "<script>
            if (!confirm('Ten produkt jest przypisany do jednej lub więcej list ulubionych. Czy na pewno chcesz go usunąć z ulubionych? (Zostanie także usunięty z tych list.)')) {
                window.location.href = 'ulubione.php';
                return;
            }
        </script>";
    }

    $stmt = $pdo->prepare("DELETE FROM ulubione_list_dod WHERE id_user = :user_id AND id_produkt = :produkt_id");
    $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->bindValue(':produkt_id', $produkt_id, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = $pdo->prepare("DELETE FROM ulubione WHERE id_user = :user_id AND id_produkt = :produkt_id");
    $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->bindValue(':produkt_id', $produkt_id, PDO::PARAM_INT);
    $stmt->execute();

    header("Location: ulubione.php");
    exit;
}

if (isset($_POST['usun_liste'])) {
    $lista_id = $_POST['lista_id'];
    $user_id = $_SESSION['user_id'];

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("DELETE FROM ulubione_list_dod WHERE id_lista = :lista_id AND id_user = :user_id");
        $stmt->bindValue(':lista_id', $lista_id, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $stmt = $pdo->prepare("DELETE FROM listy_ulubionych WHERE id = :lista_id AND id_user = :user_id");
        $stmt->bindValue(':lista_id', $lista_id, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
      
        $pdo->commit();

        header("Location: ulubione.php");
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "Błąd: " . $e->getMessage();
    }
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['usun_z_listy'])) {
    $produkt_id = $_POST['produkt_id'];
    $lista_id = $_POST['lista_id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM ulubione_list_dod WHERE id_user = :user_id AND id_produkt = :produkt_id AND id_lista = :lista_id");
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':produkt_id', $produkt_id, PDO::PARAM_INT);
        $stmt->bindValue(':lista_id', $lista_id, PDO::PARAM_INT);
        $stmt->execute();

        header("Location: ulubione.php?lista_id=" . $lista_id);
        exit;
    } catch (PDOException $e) {
        echo "Błąd: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ulubione Produkty</title>
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
    <div class="container-fluid">
        <div class="row">
            <aside class="col-md-3 bg-light py-4">
                <h4 class="mb-4">Twoje listy</h4>
                <ul class="list-group">
                    <li class="list-group-item">
                        <a href="ulubione.php" class="text-decoration-none">Wszystkie produkty</a>
                    </li>
                    <?php foreach ($listyUlubionych as $lista): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="?lista_id=<?php echo $lista['id']; ?>" class="text-decoration-none">
                                <?php echo htmlspecialchars($lista['nazwa']); ?>
                            </a>
                            <div>
                                <button class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#dodajProdukty<?php echo $lista['id']; ?>">Dodaj produkty</button>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="lista_id" value="<?php echo $lista['id']; ?>">
                                    <button type="submit" name="usun_liste" class="btn btn-sm btn-danger" onclick="return confirm('Czy na pewno chcesz usunąć tę listę?')">Usuń</button>
                                </form>
                            </div>
                        </li>
                        <div id="dodajProdukty<?php echo $lista['id']; ?>" class="collapse mt-2">
                          <form method="POST" class="p-3 border">
                            <input type="hidden" name="lista_id" value="<?php echo $lista['id']; ?>">
                            <div class="form-group">
                              <label>Wybierz produkty do dodania:</label>
                              <ul class="list-group">
                                <?php foreach ($wszystkieUlubioneProdukty as $produkt): ?>
                                <?php
                                  $stmt = $pdo->prepare("SELECT 1 FROM ulubione_list_dod WHERE id_produkt = :produkt_id AND id_lista = :lista_id AND id_user = :user_id");
                                  $stmt->bindValue(':produkt_id', $produkt['id'], PDO::PARAM_INT);
                                  $stmt->bindValue(':lista_id', $lista['id'], PDO::PARAM_INT);
                                  $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                                  $stmt->execute();
                                  $jestNaLiscie = $stmt->rowCount() > 0;
                                ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                  <span><?php echo htmlspecialchars($produkt['nazwa']) . " - " . number_format($produkt['cena'], 2, ',', ' ') . " zł"; ?></span>
                                  <input type="checkbox" name="produkty[]" value="<?php echo $produkt['id']; ?>" <?php echo $jestNaLiscie ? 'disabled' : ''; ?>>
                                </li>
                                <?php endforeach; ?>
                              </ul>
                            </div>
                            <button type="submit" name="dodaj_do_listy" class="btn btn-success mt-2">Dodaj do listy</button>
                          </form>
                  </div>
                    <?php endforeach; ?>
                </ul>
            </aside>

          <main class="col-md-9 py-4">
            <section class="py-5">
              <div class="container">
                <h2 class="text-center mb-4">Stwórz nową listę ulubionych produktów</h2>
                <form method="POST">
                  <div class="form-group">
                    <label for="nazwa_listy">Nazwa listy:</label>
                    <input type="text" class="form-control" id="nazwa_listy" name="nazwa_listy" required>
                  </div>
                  <button type="submit" name="dodaj_lista" class="btn btn-primary mt-3">Stwórz listę</button>
                </form>
              </div>
            </section>
          </main>
          <section class="py-5">
            <div class="container">
              <h2 class="text-center mb-4">Twoje ulubione produkty</h2>
              <div class="row">
            <?php if (!empty($ulubioneProdukty)): ?>
                <?php foreach ($ulubioneProdukty as $produkt): ?>
                <div class="col-sm-6 col-md-4 col-lg-3 mb-4">
                  <div class="card h-100">
                    <img src="<?= htmlspecialchars($produkt['zdjecie']) ?>" class="card-img-top" alt="<?= htmlspecialchars($produkt['nazwa']) ?>">
                    <div class="card-body d-flex flex-column text-center">
                      <h5 class="card-title flex-grow-1"><?php echo htmlspecialchars($produkt['nazwa']); ?></h5>
                      <p class="card-text">Cena: <?php echo number_format($produkt['cena'], 2, ',', ' '); ?> zł</p>
                      <a href="produkt_strona.php?id=<?= $produkt['id'] ?>" class="btn btn-primary">Zobacz szczegóły</a>
                      <form method="POST" class="mt-2" onsubmit="return confirmDelete1();">
                        <input type="hidden" name="produkt_id" value="<?php echo $produkt['id']; ?>">
                        <button type="submit" name="usun_ulubione" class="btn btn-danger">Usuń z ulubionych</button>
                      </form>
                      <?php if ($wybranaLista !== null): ?>
                      <form method="POST" class="mt-2">
                        <input type="hidden" name="produkt_id" value="<?php echo $produkt['id']; ?>">
                        <input type="hidden" name="lista_id" value="<?php echo $wybranaLista; ?>">
                        <button type="submit" name="usun_z_listy" class="btn btn-danger" onclick="return confirm('Czy na pewno chcesz usunąć ten produkt z tej listy?')">Usuń z listy</button>
                      </form>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <p class="text-center">Nie masz jeszcze żadnych ulubionych produktów.</p>
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
    <script>
    function confirmDelete1() {
    return confirm("Czy na pewno chcesz usunąć ten produkt z ulubionych?");
    }
    function confirmDelete2() {
    return confirm("Czy na pewno chcesz usunąć tę listę ulubionych?");
    }
    </script>
</body>
</html>



























