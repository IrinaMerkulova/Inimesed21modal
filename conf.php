<?php
$connection = new mysqli('localhost', 'imerkulova21', '123456', 'imerkulova21');
$connection->set_charset('UTF8');
/*CREATE TABLE maakond(
    id int primary key AUTO_INCREMENT,
    maakonna_nimi varchar(50),
    maakonna_keskus varchar(30))
CREATE TABLE inimene (
    id int primary key AUTO_INCREMENT,
    eesnimi varchar(20),
    perekonnanimi varchar(25),
    maakonna_id int,
FOREIGN Key (maakonna_id) references maakond(id) )
*/