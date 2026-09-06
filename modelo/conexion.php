<?php

class Conexion{

  static public function conectar(){

    /* =============================
         PARA TRABAJAR DE MANERA LOCAL 
         =================================*/
/*
    $host="localhost";
    $db="merca_control";
    $userDB="root";
    $passDB="";
*/
    /* =============================
      PARA TRABAJAR CON EL SERVIDOR REMOTO
      =================================*/

    $host = "localhost";
    $db = "u144048036_merca_control";
    $userDB = "u144048036_admin";
    $passDB = "Adminisitrador123!";

    $link = new PDO("mysql:host=" . $host . ";" . "dbname=" . $db, $userDB, $passDB);


    $link->exec("set names utf8");
    return $link;
  }
}