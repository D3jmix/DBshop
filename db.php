<?php
  try {
    $pdo = new PDO('mysql:host=mysql.ct8.pl;dbname=m51188_sklep','m51188_admin','sklepDJDB5it');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  } catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
  }












