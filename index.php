<?php
require("conf.php");
require("functions.php");
require("login.php");
session_start();
if (!isset($_SESSION["error"])){
    $_SESSION["error"] = "";
}
if (!isset($_SESSION["admin"])){
    $_SESSION["admin"] = false;
}


if (isset($_REQUEST['knimi']) && isset($_REQUEST['psw'])){
    login($_POST['knimi'], $_POST['psw']);
}
$sort = "eesnimi";
$search_term = "";
if(isset($_REQUEST["sort"])) {
    $sort = $_REQUEST["sort"];
}
if(isset($_REQUEST["search_term"])) {
    $search_term = $_REQUEST["search_term"];
}
if(isset($_REQUEST["maakonna_lisamine"]) && isset($_SESSION["unimi"])) {
    global $connection;
    $maakonna_nimi=$_REQUEST["maakonna_nimi"];
    $query=mysqli_query($connection, "SELECT * FROM maakond WHERE maakonna_nimi='$maakonna_nimi'");
    $error = "Maakonna nimi või maakonna keskuse kast oli tühi";

    if (!empty(trim($_REQUEST["maakonna_nimi"])) &&
        !empty(trim($_REQUEST["maakonna_keskus"])) &&
        mysqli_num_rows($query)==0)
    {
        addCountry($_REQUEST["maakonna_nimi"], $_REQUEST["maakonna_keskus"]);
        header("Location: index.php");
        exit();
    } else {
        $error ="Maakonna nimi on juba olemas!";
    }
}
if(isset($_REQUEST["inimese_lisamine"]) && isset($_SESSION["unimi"])) {
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
    <link rel="stylesheet" href="modal.css">
    <title>Inimesed ja maakonnad</title>
</head>
<body>
<header class="header">
    <div class="container">
        <h1>Tabelid | Inimesed ja maakond</h1>
    </div>
    <div id="menuArea" style="position:absolute; top:0px ;left:25px;">
        <?php
        if (isset($_SESSION["unimi"])){
            ?>
            <h2> <?="$_SESSION[unimi]"?> on sisse logitud</h2>
            <a href="logout2.php">Logi välja</a>
            <?php
        }
        ?>
    </div>
    <?php
    if (!isset($_SESSION["unimi"])){
        ?>
        <button onclick="document.getElementById('id01').style.display='block'" style="width:auto;position:absolute; top:25px ;left:25px;">Logi Sisse</button>
        <?php
    }
    ?>
<!--    Sisse logimine-->
    <div id="id01" class="modal">

        <form class="modal-content animate" action="" method="post">
            <div class="imgcontainer">
                <span onclick="document.getElementById('id01').style.display='none'" class="close" title="Close Login">&times;</span>
                <img src="avatar.png" alt="Avatar" class="avatar">
            </div>

            <div class="container">
                <label for="knimi"><b>Kasutajanimi</b></label>
                <input type="text" placeholder="Kasutajanimi" name="knimi" id="knimi" required>
                <label for="psw"><b>Parool</b></label>
                <input type="password" placeholder="Parool" name="psw" id="psw" required>
                <input class="modal-submit" type="submit" value="Logi Sisse"><button type="button" class="modal-submit" onclick="document.getElementById('id01').style.display='none'">Tühista</button>
            </div>
            <div class="container" style="background-color:#f1f1f1">

            </div>
        </form>
    </div>
</header>
<main class="main">
    <div class="container">
        <form action="index.php">
            <input type="text" name="search_term" placeholder="Otsi...">
        </form>
    </div>
    <?php if(isset($_REQUEST["edit"]) && isAdmin()): ?>
        <?php foreach($people as $person): ?>
            <?php if($person->id == intval($_REQUEST["edit"])): ?>
                <div class="container">
                    <form action="index.php">
                        <input type="hidden" name="changed_id" value="<?=$person->id ?>" />
                        <input type="text" name="eesnimi" value="<?=$person->eesnimi?>" pattern="[A-Za-z]{3}">
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
<?php if(isAdmin()){ ?>
                    <td>
                        <a title="Kustuta inimene" class="deleteBtn" href="index.php?delete=<?=$person->id?>"
                           onclick="return confirm('Oled kindel, et soovid kustutada?');">X</a>
                        <a title="Muuda inimest" class="editBtn" href="index.php?edit=<?=$person->id?>">&#9998;</a>
                    </td>
<?php }?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        if (isset($_SESSION["unimi"])){

            ?>
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
                <dd><input type="text" name="eesnimi" placeholder="Sisesta eesnimi..." pattern="[A-Za-z]{5}"></dd>
                <dt>Perekonnanimi:</dt>
                <dd><input type="text" name="perekonnanimi" placeholder="Sisesta perekonna nimi..."></dd>
                <dt>Maakond</dt>
                <dd><?php
                    echo createSelect("SELECT id, maakonna_nimi FROM maakond", "maakonna_id");
                    ?></dd>
                <input type="submit" name="inimese_lisamine" value="Lisa inimene">
            </dl>
        </form>
        <?php echo ($error??"")."<br>"; }?>
    </div>
</main>
</body>
</html>