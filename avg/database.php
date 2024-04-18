<?php
class database
{
  var $dbh;


  function connect()
  {
    $hostname = "******";
    $database = "******";
    $username = "******";
    $password = "******";

    try {
      $this->dbh = new PDO("mysql:host=" . $hostname . ";dbname=" . $database . ";", $username, $password);
    } catch (PDOException $e) {
      echo "Error: " . $e->getMessage();
    }
  }


  function getKlant($id)
  {
    $statement = $this->dbh->prepare("SELECT * FROM klanten WHERE id = :id");
    $result = $statement->execute(array(":id" => $id));
    $klant = $statement->fetch(PDO::FETCH_ASSOC);
    return $klant;
  }



  function clearTable()
  {
    try {
      $sql = $this->dbh->prepare("TRUNCATE TABLE klanten");
      if ($result = $sql->execute()) {
        return true;
      } else {
        return false;
      }
    } catch (PDOException $e) {
      echo "Error: " . $e->getMessage();
    }
  }



  function getKlanten()
  {
    $result = $this->dbh->query("SELECT id, voornaam, tussenvoegsel, achternaam FROM klanten ORDER BY achternaam");
    $result = $result->fetchAll(PDO::FETCH_ASSOC);
    return $result;
  }


  function insertKlant($voornaam, $tussenvoegsel, $achternaam, $geboortedatum)
  {
    try {
      $sql = $this->dbh->prepare("INSERT INTO klanten (`voornaam`, `tussenvoegsel`, `achternaam`, `geboortedatum`) VALUES (:voornaam, :tussenvoegsel, :achternaam, :geboortedatum)");
      $sql->bindParam(':voornaam', $voornaam, PDO::PARAM_STR);
      $sql->bindParam(':tussenvoegsel', $tussenvoegsel, PDO::PARAM_STR);
      $sql->bindParam(':achternaam', $achternaam, PDO::PARAM_STR);
      $sql->bindParam(':geboortedatum', $geboortedatum, PDO::PARAM_STR);
      if ($result = $sql->execute()) {
        return $this->dbh->lastInsertId();
      } else {
        return false;
      }
    } catch (PDOException $e) {
      echo "Error: " . $e->getMessage();
    }
  }
}



$db = new database();
$db->connect();
