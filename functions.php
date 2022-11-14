<?php
require ('conf.php');

// tagastab isAdmin session
function isAdmin(){
    return $_SESSION['onAdmin'] ==1;
}

//sorteerimine
function countryData($sort_by = "eesnimi", $search_term = "") {
    global $connection;
    $sort_list = array("eesnimi", "perekonnanimi", "maakonna_nimi");
    if(!in_array($sort_by, $sort_list)) {
        return "Seda tulpa ei saa sorteerida";
    }
    $request = $connection->prepare("SELECT inimene.id, eesnimi, perekonnanimi, maakond.maakonna_nimi
    FROM inimene, maakond 
    WHERE inimene.maakonna_id = maakond.id 
    AND (eesnimi LIKE '%$search_term%' OR perekonnanimi LIKE '%$search_term%' OR maakonna_nimi LIKE '%$search_term%')
    ORDER BY $sort_by");
    $request->bind_result($id, $eesnimi, $perekonnanimi, $maakonna_nimi);
    $request->execute();
    $data = array();
    while($request->fetch()) {
        $person = new stdClass();
        $person->id = $id;
        $person->eesnimi = htmlspecialchars($eesnimi);
        $person->perekonnanimi = htmlspecialchars($perekonnanimi);
        $person->maakonna_nimi = $maakonna_nimi;
        array_push($data, $person);
    }
    return $data;
}
// valitud rea näitamine
function createSelect($query, $name) {
    global $connection;
    $query = $connection->prepare($query);
    $query->bind_result($id, $data);
    $query->execute();
    $result = "<select name='$name'>";
    while($query->fetch()) {
        $result .= "<option value='$id'>$data</option>";
    }
    $result .= "</select>";
    return $result;
}

//maakonna andmete lisamine tabelisse
function addCountry($country_name, $country_centre) {
    global $connection;
    $query = $connection->prepare("INSERT INTO maakond (maakonna_nimi, maakonna_keskus)
    VALUES (?, ?)");
    $query->bind_param("si", $country_name, $country_centre);
    $query->execute();
}
// Inimese andmete lisamine andmetabelisse
function addPerson($first_name, $last_name, $country_id) {
    global $connection;
    $query = $connection->prepare("INSERT INTO inimene (eesnimi, perekonnanimi, maakonna_id)
    VALUES (?, ?, ?)");
    $query->bind_param("ssd", $first_name, $last_name, $country_id);
    $query->execute();
}
//Inimese andmete kustutamine
function deletePerson($person_id) {
    global $connection;
    $query = $connection->prepare("DELETE FROM inimene WHERE id=?");
    $query->bind_param("i", $person_id);
    $query->execute();
}
//Inimese andmete muutmine
function savePerson($person_id, $first_name, $last_name, $country_id) {
    global $connection;
    $query = $connection->prepare("UPDATE inimene
    SET eesnimi=?, perekonnanimi=?, maakonna_id=?
    WHERE inimene.id=?");
    $query->bind_param("ssii", $first_name, $last_name, $country_id, $person_id);
    $query->execute();
}

?>