<?php
  session_start();
  require_once 'db.php';

  if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
  }
  
  $id_user = $_SESSION['user_id'];
  $message = "";

  $stmt = $pdo->prepare("SELECT id_klienta FROM konta WHERE id = ?");
  $stmt->execute([$id_user]);
  $id_klienta = $stmt->fetchColumn();
  
  if (!$id_klienta) {
    die("Nie znaleziono powiązanego klienta dla tego użytkownika.");
  }

  $stmt = $pdo->prepare("SELECT ulica, numer_domu, miasto, kod_pocztowy, kraj FROM klienci WHERE id_klienta = ?");
  $stmt->execute([$id_klienta]);
  $adres_glowny = $stmt->fetch(PDO::FETCH_ASSOC);
  
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['dodaj_adres'])) {
      $ulica = $_POST['ulica'];
      $numer_domu = $_POST['numer_domu'];
      $miasto = $_POST['miasto'];
      $kod_pocztowy = $_POST['kod_pocztowy'];
      $kraj = $_POST['kraj'];
      $typ_adresu = $_POST['typ_adresu'];

      if (!preg_match('/^\d{2}-\d{3}$/', $kod_pocztowy)) {
        $message = "Kod pocztowy musi być w formacie XX-XXX (np. 01-234).";
      } else {
        if ($typ_adresu === 'dostawy') {
          $stmt = $pdo->prepare("INSERT INTO adres_dostawy (ulica, numer_domu, miasto, kod_pocztowy, kraj) VALUES (?, ?, ?, ?, ?)");
        } else {
          $stmt = $pdo->prepare("INSERT INTO adres_rozliczeniowy (ulica, numer_domu, miasto, kod_pocztowy, kraj) VALUES (?, ?, ?, ?, ?)");
        }
        
        try {
          $pdo->beginTransaction();
          $stmt->execute([$ulica, $numer_domu, $miasto, $kod_pocztowy, $kraj]);
          $adres_id = $pdo->lastInsertId();

          if ($typ_adresu === 'dostawy') {
            $stmt = $pdo->prepare("INSERT INTO adres_user (id_klienta, id_adres_d) VALUES (?, ?)");
          } else {
            $stmt = $pdo->prepare("INSERT INTO adres_user (id_klienta, id_adres_r) VALUES (?, ?)");
          }
          $stmt->execute([$id_klienta, $adres_id]);
          $pdo->commit();
          
          $message = "Adres został dodany pomyślnie.";
        } catch (Exception $e) {
          $pdo->rollBack();
          $message = "Wystąpił błąd: " . $e->getMessage();
        }
      }
    }

    if (isset($_POST['edytuj_adres'])) {
      $adres_id = $_POST['adres_id'];
      $ulica = $_POST['ulica'];
      $numer_domu = $_POST['numer_domu'];
      $miasto = $_POST['miasto'];
      $kod_pocztowy = $_POST['kod_pocztowy'];
      $kraj = $_POST['kraj'];
      $typ_adresu = $_POST['typ_adresu'];

      if (!preg_match('/^\d{2}-\d{3}$/', $kod_pocztowy)) {
        $message = "Kod pocztowy musi być w formacie XX-XXX (np. 01-234).";
      } else {
        if ($typ_adresu === 'dostawy') {
          $stmt = $pdo->prepare("UPDATE adres_dostawy SET ulica = ?, numer_domu = ?, miasto = ?, kod_pocztowy = ?, kraj = ? WHERE id_adresu_d = ?");
        } else {
          $stmt = $pdo->prepare("UPDATE adres_rozliczeniowy SET ulica = ?, numer_domu = ?, miasto = ?, kod_pocztowy = ?, kraj = ? WHERE id_adresu_r = ?");
        }
        
        try {
          $stmt->execute([$ulica, $numer_domu, $miasto, $kod_pocztowy, $kraj, $adres_id]);
          $message = "Adres został zaktualizowany.";
        } catch (Exception $e) {
          $message = "Błąd aktualizacji: " . $e->getMessage();
        }
      }
    }

    if (isset($_POST['usun_adres'])) {
      $adres_id = $_POST['adres_id'];
      $typ_adresu = $_POST['typ_adresu'];
      
      try {
        if ($typ_adresu === 'dostawy') {
          $stmt = $pdo->prepare("DELETE FROM adres_dostawy WHERE id_adresu_d = ?");
          $stmt->execute([$adres_id]);
        } else {
          $stmt = $pdo->prepare("DELETE FROM adres_rozliczeniowy WHERE id_adresu_r = ?");
          $stmt->execute([$adres_id]);
        }

        if ($typ_adresu === 'dostawy') {
          $stmt = $pdo->prepare("DELETE FROM adres_user WHERE id_adres_d = ?");
        } else {
          $stmt = $pdo->prepare("DELETE FROM adres_user WHERE id_adres_r = ?");
        }
        $stmt->execute([$adres_id]);
        
        $message = "Adres został usunięty.";
      } catch (Exception $e) {
        $message = "Błąd usuwania: " . $e->getMessage();
      }
    }
  }
  
  $stmt = $pdo->prepare("SELECT ad.id_adresu_d AS id_adresu_dostawy, ad.ulica AS ulica_dostawy, ad.numer_domu AS numer_dostawy, ad.miasto AS miasto_dostawy, ad.kod_pocztowy AS kod_dostawy, ad.kraj AS kraj_dostawy,
    ar.id_adresu_r AS id_adresu_rozliczeniowy, ar.ulica AS ulica_rozliczeniowa, ar.numer_domu AS numer_rozliczeniowy, ar.miasto AS miasto_rozliczeniowe, ar.kod_pocztowy AS kod_rozliczeniowy, ar.kraj AS kraj_rozliczeniowy
    FROM adres_user au LEFT JOIN adres_dostawy ad ON au.id_adres_d = ad.id_adresu_d LEFT JOIN adres_rozliczeniowy ar ON au.id_adres_r = ar.id_adresu_r WHERE au.id_klienta = ?");
  $stmt->execute([$id_klienta]);
  $adresy = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
  ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<div class="container mt-4">
  <h2>Moje Adresy</h2>
  <?php if ($message): ?>
  <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>
  
  <h4>Adres główny:</h4>
  <?php if ($adres_glowny): ?>
  <div class="card mb-4">
    <div class="card-body">
      <h5 class="card-title">Adres główny</h5>
      <p class="card-text">
        <?= htmlspecialchars($adres_glowny['ulica']) ?> <?= htmlspecialchars($adres_glowny['numer_domu']) ?><br>
        <?= htmlspecialchars($adres_glowny['miasto']) ?>, <?= htmlspecialchars($adres_glowny['kod_pocztowy']) ?><br>
        <?= htmlspecialchars($adres_glowny['kraj']) ?>
      </p>
    </div>
  </div>
  <?php else: ?>
  <p>Nie masz ustawionego adresu głównego.</p>
  <?php endif; ?>
  
  <h4>Dodaj nowy adres:</h4>
  <form method="POST">
    <div class="mb-3">
      <label for="ulica" class="form-label">Ulica:</label>
      <input type="text" name="ulica" id="ulica" class="form-control" required>
    </div>
    <div class="mb-3">
      <label for="numer_domu" class="form-label">Numer domu:</label>
      <input type="text" name="numer_domu" id="numer_domu" class="form-control" required>
    </div>
    <div class="mb-3">
      <label for="miasto" class="form-label">Miasto:</label>
      <input type="text" name="miasto" id="miasto" class="form-control" required>
    </div>
    <div class="mb-3">
      <label for="kod_pocztowy" class="form-label">Kod pocztowy:</label>
      <input type="text" name="kod_pocztowy" id="kod_pocztowy" class="form-control" required>
    </div>
    <div class="mb-3">
      <label for="kraj" class="form-label">Kraj:</label>
      <input type="text" name="kraj" id="kraj" class="form-control" required>
    </div>
    <div class="mb-3">
      <label for="typ_adresu" class="form-label">Typ adresu:</label>
      <select name="typ_adresu" id="typ_adresu" class="form-select">
        <option value="dostawy">Adres dostawy</option>
        <option value="rozliczeniowy">Adres rozliczeniowy</option>
      </select>
    </div>
    <button type="submit" name="dodaj_adres" class="btn btn-primary">Dodaj adres</button>
  </form>
  
  <h4>Twoje dodatkowe adresy:</h4>
  <div class="row">
    <?php foreach ($adresy as $adres): ?>
    <div class="col-md-4">
      <div class="card mb-3">
        <div class="card-body">
          <h5 class="card-title"><?= $adres['ulica_dostawy'] ? 'Adres dostawy' : 'Adres rozliczeniowy' ?></h5>
          <p class="card-text">
            <?= htmlspecialchars($adres['ulica_dostawy'] ?? $adres['ulica_rozliczeniowa']) ?> <?= htmlspecialchars($adres['numer_dostawy'] ?? $adres['numer_rozliczeniowy']) ?><br>
            <?= htmlspecialchars($adres['miasto_dostawy'] ?? $adres['miasto_rozliczeniowe']) ?>, <?= htmlspecialchars($adres['kod_dostawy'] ?? $adres['kod_rozliczeniowy']) ?><br>
            <?= htmlspecialchars($adres['kraj_dostawy'] ?? $adres['kraj_rozliczeniowy']) ?>
          </p>
          <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $adres['id_adresu_dostawy'] ?? $adres['id_adresu_rozliczeniowy'] ?>">
            Edytuj
          </button>
          <form method="POST" style="display:inline;">
            <input type="hidden" name="adres_id" value="<?= $adres['id_adresu_dostawy'] ?? $adres['id_adresu_rozliczeniowy'] ?>">
            <input type="hidden" name="typ_adresu" value="<?= $adres['id_adresu_dostawy'] ? 'dostawy' : 'rozliczeniowy' ?>">
            <button type="submit" name="usun_adres" class="btn btn-danger btn-sm" onclick="return confirmDelete()">Usuń</button>
          </form>

          <div class="modal fade" id="editModal<?= $adres['id_adresu_dostawy'] ?? $adres['id_adresu_rozliczeniowy'] ?>" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="editModalLabel">Edytuj Adres</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <form method="POST">
                    <input type="hidden" name="adres_id" value="<?= $adres['id_adresu_dostawy'] ?? $adres['id_adresu_rozliczeniowy'] ?>">
                    <div class="mb-3">
                      <label for="ulica" class="form-label">Ulica</label>
                      <input type="text" name="ulica" class="form-control" value="<?= htmlspecialchars($adres['ulica_dostawy'] ?? $adres['ulica_rozliczeniowa']) ?>" required>
                    </div>
                    <div class="mb-3">
                      <label for="numer_domu" class="form-label">Numer domu</label>
                      <input type="text" name="numer_domu" class="form-control" value="<?= htmlspecialchars($adres['numer_dostawy'] ?? $adres['numer_rozliczeniowy']) ?>" required>
                    </div>
                    <div class="mb-3">
                      <label for="miasto" class="form-label">Miasto</label>
                      <input type="text" name="miasto" class="form-control" value="<?= htmlspecialchars($adres['miasto_dostawy'] ?? $adres['miasto_rozliczeniowe']) ?>" required>
                    </div>
                    <div class="mb-3">
                      <label for="kod_pocztowy" class="form-label">Kod pocztowy</label>
                      <input type="text" name="kod_pocztowy" class="form-control" value="<?= htmlspecialchars($adres['kod_dostawy'] ?? $adres['kod_rozliczeniowy']) ?>" required>
                    </div>
                    <div class="mb-3">
                      <label for="kraj" class="form-label">Kraj</label>
                      <input type="text" name="kraj" class="form-control" value="<?= htmlspecialchars($adres['kraj_dostawy'] ?? $adres['kraj_rozliczeniowy']) ?>" required>
                    </div>
                    <div class="mb-3" style="display: none;">
                      <label for="typ_adresu" class="form-label">Typ adresu:</label>
                      <select name="typ_adresu" class="form-select">
                        <option value="dostawy" <?= $adres['id_adresu_dostawy'] ? 'selected' : ''?>>>Adres dostawy</option>
                        <option value="rozliczeniowy" <?= $adres['id_adresu_rozliczeniowy'] ? 'selected' : '' ?>>Adres rozliczeniowy</option>
                      </select>                                            
                    </div>
                      <button type="submit" name="edytuj_adres" class="btn btn-primary" onclick="return confirmEdit()">Zapisz zmiany</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>                     
<script>
  function confirmDelete() {
    return confirm("Czy na pewno chcesz usunąć adres?");
  }
  function confirmEdit() {
    return confirm("Czy na pewno chcesz zedytować adres?");
  }
</script>

                            
                            
                            
                            
                            












