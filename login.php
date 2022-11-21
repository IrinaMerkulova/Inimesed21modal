<?php
function login($login, $pass)
{
    require('conf.php');
    global $connection;
    if (isset($_POST['knimi']) && isset($_POST['psw'])) {
        $login = htmlspecialchars($_POST['knimi']);
        $pass = htmlspecialchars($_POST['psw']);
        $sool = 'taiestisuvalinetekst';;
        $krypt = crypt($pass, $sool);
//check the database for the user
        $kask = $connection->prepare("SELECT id,nimi,parool,onAdmin FROM kasutajad where nimi=?");
        $kask->bind_param("s", $login);
        $kask->bind_result($id, $kasutaja, $parool, $onAdmin);
        $kask->execute();

        if ($kask->fetch() && $krypt == $parool) {
            $_SESSION['unimi'] = $login;
            if ($onAdmin == 1) {
                $_SESSION['onAdmin'] = true;
            } else {
                $_SESSION['onAdmin'] = false;
            }
            $_SESSION['error'] = "";
            header("Location: index.php");
            $connection->close();
            exit();
        }
        $_SESSION['error'] = "Kasutaja $login või parool $pass on vale";
        header("Location: index.php");
        $connection->close();
    }
}

?>