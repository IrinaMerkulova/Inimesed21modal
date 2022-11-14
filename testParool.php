<?php
$pass="admin";
$sool = 'taiestisuvalinetekst';
$kryp = crypt($pass, $sool);
echo $kryp;
