<?php
require ('conf.php');
global $connection;
session_start();
if (isset($_SESSION['tuvastamine'])) {
    header('Location: index.php');
    exit();
}
//kontrollime kas väljad on täidetud
if (!empty($_POST['login']) && !empty($_POST['pass'])) {
    //eemaldame kasutaja sisestusest kahtlase pahna
    $login = htmlspecialchars(trim($_POST['login']));
    $pass = htmlspecialchars(trim($_POST['pass']));
    //SIIA UUS KONTROLL
    $sool = 'taiestisuvalinetekst';
    $kryp = crypt($pass, $sool);
    //kontrollime kas andmebaasis on selline kasutaja ja parool
    $paring = "SELECT nimi, onAdmin, koduleht FROM kasutajad WHERE nimi=? AND parool=?";
    $kask=$connection->prepare($paring);
    $kask->bind_param("ss", $login, $kryp);
    $kask->bind_result($kasutaja, $onAdmin, $koduleht);
    $kask->execute();
    //$valjund = mysqli_query($connection, $paring);
    //kui on, siis loome sessiooni ja suuname
    //if (mysqli_num_rows($valjund)==1) {
    if($kask->fetch()){
        $_SESSION['tuvastamine'] = 'misiganes';
        $_SESSION['kasutaja'] = $kasutaja;
        $_SESSION['onAdmin'] =  $onAdmin;
        if(isset($koduleht)){
            header("Location: $koduleht");
        } else {
            header('Location: index.php');
            exit();
        }
    } else {
        echo "kasutaja $login või parool $kryp on vale";
    }
}
/*
 * kasutaja: opilane, parool -admin, õigused - admin
 * kasutaja: laps, parool -admin, õigused ei ole admin
 * */
?>
<h1>Login</h1>
<form action="" method="post">
    Login: <input type="text" name="login"><br>
    Password: <input type="password" name="pass"><br>
    <input type="submit" value="Logi sisse">
</form>

