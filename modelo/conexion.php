<?php

class Conexion{

  static public function conectar(){

    /* =============================
         PARA TRABAJAR DE MANERA LOCAL 
         =================================*/

    $host="localhost";
    $db="merca_control";
    $userDB="root";
    $passDB="";

    $link=new PDO("mysql:host=".$host.";"."dbname=".$db, $userDB, $passDB);

    /* =============================
      PARA TRABAJAR CON EL SERVIDOR REMOTO
      =================================*/

/*
    $host = "localhost";
    $db = "u144048036_la_merca";
    $userDB = "u144048036_root";
    $passDB = "Lamerca123!";
    $link = new PDO("mysql:host=" . $host . ";" . "dbname=" . $db, $userDB, $passDB);
*/

    $link->exec("set names utf8");
    return $link;
  }
}