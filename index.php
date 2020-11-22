<!DOCTYPE html>

<html lang="de">

<head>
	<title>Webconia Technology Conference 2021</title>
	<link rel="stylesheet" type="text/css" href="css/style.css" />
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
</head>

<body>
	

	<div class="container">
		
		<h1>Bitte eintragen:</h1>
		
		<form action="index.php" method="post">

			<input type="text" id="fname" name="fname" placeholder="Ihr Vorname">

			<input type="text" id="lname" name="lname" placeholder="Ihr Nachname">

			<input type="email" id="email" name="email" placeholder="Ihre E-Mail Adresse">

			<input type="text" id="company" name="company" placeholder="Ihre Firma">

			<input type="submit" name="submit" value="Anmelden">

		</form>
		
	</div>
	

</body>

<?php

//-----------------Funktionen--------------------

/*
 * Funktion ermittelt ob einer der übergeben Werte leer ist
 * In dieser Aufgabe extra auf das Formular zugeschnitten
 * 
 * $fname:string => Vorname
 * $lname:string => Nachname
 * $email:string => email
 * $comp:string => Firma
 * 
 * @return boolean
 * */
function inputEmpty($fname,$lname,$email,$comp){
	if(empty($fname) || empty($lname) || empty($email) || empty($comp)){
		return true;
	}
	return false;
}

/*
 * Funktion prüft anhand der email Adresse auf schon vorhandene
 * Datenbank Einträge um zu entscheiden ob der im formular betroffene
 * Kunde sich schon angemeldet hat
 * 
 * $conn: Verbindung zur Datenbank
 * $email:string => email adresse
 * 
 * @return boolean
 */
function hasDuplicate($conn,$email){
	$sql = "SELECT * FROM Eintraege WHERE email LIKE '". $email ."'" ;
	$result = mysqli_query($conn, $sql);
	//Es gibt duplikate wenn einträge bei diesem Funktionsaufruf auftreten  
	if(mysqli_num_rows($result) != 0){
		return true;
	} 	
	return false;
}
/*
 * Fügt Einträge in die Datenbank ein wenn kein vorhandener Eintrag existiert 
 * 
 * $conn: Verbindung zur Datenbank
 * $fname:string => Vorname
 * $lname:string => Nachname
 * $email:string => email
 * $comp:string => Firma
 * 
 * @return boolean
 */
function addToDB($conn,$fname,$lname,$email,$comp){
	if(!hasDuplicate($conn,$email)){
		$sql = 	"INSERT INTO Eintraege(fname, lname, email, company)".
				"VALUES('$fname','$lname','$email','$comp')";
		if(mysqli_query($conn, $sql)){
			return true;
		} 
	} 
	return false;
}



//--------------------Hauptprogramm-----------------------


//Datenbank ist in meinem fall lokal, bitte servername, username und password entsprechend setzen.
$servername = "localhost";
$username = "root";
$password = "";

// Herstellen der Verbindung zur Datenbank
$conn = mysqli_connect($servername, $username, $password);
// Abfangen eines Verbindungsfehlers
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error()."\n");
}

//Datenbank nur anlegen wenn sie nicht bereits existiert
$sql = "CREATE DATABASE IF NOT EXISTS Kunden";

//Sollte der query nicht ausgeführt werden => fehlermeldung
if (!mysqli_query($conn, $sql)) {
	echo "Error creating database: " . mysqli_error($conn)."\n" ;
}

//Datenbank Kunden auswählen
if (!mysqli_select_db($conn,'Kunden')){
	echo "Error selecting database: " . mysqli_error($conn) ."\n";
}

//Tabelle eintraege erstellen wenn noch nicht erstellt
$sql = "CREATE TABLE IF NOT EXISTS Eintraege( ".
            "id INT NOT NULL AUTO_INCREMENT, ".
            "fname VARCHAR(255) NOT NULL, ".
            "lname VARCHAR(255) NOT NULL, ".
            "email VARCHAR(255) NOT NULL, ".
            "company VARCHAR(255) NOT NULL, ".
            "PRIMARY KEY (id)); ";

if (!mysqli_query($conn, $sql)) {
	echo "Error creating Table: " . mysqli_error($conn)."\n" ;
}
//Deklarieren von error flags
$errInput = $errDB = ""; //für fehlerausgabe wenn ein fehler gefunden wird

$inputValid = false; //zum prüfen einer erfolgreichen anmeldung.

//Es soll nur etwas ausgeführt werden wenn es einen Post request gab
//Wenn alle inputs im request gesetzt sind, dann zuweisen
if(isset($_POST['submit'])){	
	//Leerer string ist auch set, deswegen nochmal auf leeren string prüfen
	if(isset($_POST['fname'],$_POST['lname'],$_POST['email'],$_POST['company'])) {
		if(!inputEmpty($_POST['fname'],$_POST['lname'],$_POST['email'],$_POST['company'])){
			//trim um mögliche leerzeichen abzufangen
			//trim bei email nicht nötig da input type email den Fall schon abfängt
			$fname = trim($_POST['fname']);
			$lname = trim($_POST['lname']);
			$email = $_POST['email'];
			$comp = trim($_POST['company']);
			//wenn die email nicht schon in der DB vorhanden ist einfügen
				if(addToDB($conn,$fname,$lname,$email,$comp)){
					$inputValid = true;
				} else {
					//Wenn input vollständig ist, addToDB aber false liefert muss ein duplikat in der DB sein
					//MySql Fehler werden extra ausgegeben
					$errDB = "FEHLER: Sie Sind bereits für die Veranstaltung angemeldet";
				}	
		} else {
			$errInput = "FEHLER: Einer oder mehrere Werte sind Leer";
		}
	} else {
		$errInput = "FEHLER: Einer oder mehrere Werte sind Leer";
	}
}
//Wenn error gesetzt, dann error ausgeben
if(!empty($errInput) || !empty($errDB)){
	echo "$errInput\n $errDB\n";
}
//Bei erflogreicher anmeldung
if($inputValid){
	echo "Ihre Anmeldung war erfolgreich\n";
}
//Verbindung zur Datenbank schließen
mysqli_close($conn);
?>
</html>
