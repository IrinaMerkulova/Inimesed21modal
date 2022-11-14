<?php
require("conf.php");

session_start();
if(!isset($_SESSION['tuvastamine'])){
    header('Location: ab_login.php');
    exit();
}

require("functions.php");
$sort = "eesnimi";
$search_term = "";
if(isset($_REQUEST["sort"])) {
    $sort = $_REQUEST["sort"];
}
if(isset($_REQUEST["search_term"])) {
    $search_term = $_REQUEST["search_term"];
}
if(isset($_REQUEST["maakonna_lisamine"])) {
    global $connection;
    $maakonna_nimi=$_REQUEST["maakonna_nimi"];
    $query=mysqli_query($connection, "SELECT * FROM maakond WHERE maakonna_nimi='$maakonna_nimi'");

    if (!empty(trim($_REQUEST["maakonna_nimi"])) &&
        !empty(trim($_REQUEST["maakonna_keskus"])) &&
        mysqli_num_rows($query)==0)
    {
        addCountry($_REQUEST["maakonna_nimi"], $_REQUEST["maakonna_keskus"]);
        header("Location: index.php");
        exit();
    }
}
if(isset($_REQUEST["inimese_lisamine"])) {
    // ei saa lisada tühja või tühikuga eesnimi ja perenimi
    if(!empty(trim($_REQUEST["eesnimi"])) && !empty(trim($_REQUEST["perekonnanimi"]))){
        addPerson($_REQUEST["eesnimi"], $_REQUEST["perekonnanimi"], $_REQUEST["maakonna_id"]);
        header("Location: index.php");
        exit();
    }
}
if(isset($_REQUEST["delete"]) && isAdmin()) {
    deletePerson($_REQUEST["delete"]);
}
if(isset($_REQUEST["save"])) {
    savePerson($_REQUEST["changed_id"], $_REQUEST["eesnimi"], $_REQUEST["perekonnanimi"], $_REQUEST["maakonna_id"]);
}
$people = countryData($sort, $search_term);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Inimesed ja maakonnad</title>
</head>
<body>
<header class="header">
    <?=$_SESSION['kasutaja']?> on sisse logitud
    <form action="logout.php" method="post">
        <input type="submit" value="Logi välja" name="logout">
    </form>
    <div class="container">
        <h1>Tabelid | Inimesed ja maakond</h1>
    </div>
</header>
<main class="main">
    <div class="container">
        <form action="index.php">
            <input type="text" name="search_term" placeholder="Otsi...">
        </form>
    </div>
    <?php if(isset($_REQUEST["edit"]) && $_SESSION['onAdmin']==1): ?>
        <?php foreach($people as $person): ?>
            <?php if($person->id == intval($_REQUEST["edit"])): ?>
                <div class="container">
                    <form action="index.php">
                        <input type="hidden" name="changed_id" value="<?=$person->id ?>"/>
                        <input type="text" name="eesnimi" value="<?=$person->eesnimi?>">
                        <input type="text" name="perekonnanimi" value="<?=$person->perekonnanimi?>">
                        <?php echo createSelect("SELECT id, maakonna_nimi FROM maakond", "maakonna_id"); ?>
                        <a title="Katkesta muutmine" class="cancelBtn" href="index.php" name="cancel">X</a>
                        <input type="submit" name="save" value="&#10004;">
                    </form>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
    <div class="container">
        <table>
            <thead>
            <tr>
                <th>Id</th>
                <th><a href="index.php?sort=eesnimi">Eesnimi</a></th>
                <th><a href="index.php?sort=perekonnanimi">Perekonnanimi</a></th>
                <th><a href="index.php?sort=maakonna_nimi">Maakond</a></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach($people as $person): ?>
                <tr>
                    <td><strong><?=$person->id ?></strong></td>
                    <td><?=$person->eesnimi ?></td>
                    <td><?=$person->perekonnanimi ?></td>
                    <td><?=$person->maakonna_nimi ?></td>
                    <?php  if($_SESSION["onAdmin"]==1) { ?>
                    <td>
                        <a title="Kustuta inimene" class="deleteBtn" href="index.php?delete=<?=$person->id?>"
                           onclick="return confirm('Oled kindel, et soovid kustutada?');">X</a>
                        <a title="Muuda inimest" class="editBtn" href="index.php?edit=<?=$person->id?>">&#9998;</a>
                    </td>
                    <?php  } ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <form action="index.php">
            <h2>Maakonna lisamine:</h2>
            <dl>
                <dt>Maakonna nimi:</dt>
                <dd><input type="text" name="maakonna_nimi" placeholder="Sisesta nimi..."></dd>
                <dt>Maakonna keskus:</dt>
                <dl><input type="text" name="maakonna_keskus" placeholder="Sisesta keskus..."></dl>
                <input type="submit" name="maakonna_lisamine" value="Lisa maakond">
            </dl>
        </form>
        <form action="index.php">
            <h2>Inimese lisamine:</h2>
            <dl>
                <dt>Eesnimi:</dt>
                <dd><input type="text" name="eesnimi" placeholder="Sisesta eesnimi..."></dd>
                <dt>Perekonnanimi:</dt>
                <dd><input type="text" name="perekonnanimi" placeholder="Sisesta perekonna nimi..."></dd>
                <dt>Maakond</dt>
                <dd><?php
                    echo createSelect("SELECT id, maakonna_nimi FROM maakond", "maakonna_id");
                    ?></dd>
                <input type="submit" name="inimese_lisamine" value="Lisa inimene">
            </dl>
        </form>
    </div>
</main>
</body>
</html>