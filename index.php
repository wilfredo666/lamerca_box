<?php
/* controladores 

require_once "controlador/categoriaControlador.php";
require_once "controlador/productoControlador.php";
require_once "controlador/salidaControlador.php";
require_once "controlador/ingresoControlador.php";
require_once "controlador/cajaControlador.php"; */
require_once "controlador/plantillaControlador.php";
require_once "controlador/usuarioControlador.php";
require_once "controlador/almacenControlador.php";
require_once "controlador/recepcionControlador.php";
require_once "controlador/cajasTikTokControlador.php";
require_once "controlador/entregaControlador.php";
require_once "controlador/clienteControlador.php";
require_once "controlador/encomiendasControlador.php";

/* modelos 

require_once "modelo/categoriaModelo.php";
require_once "modelo/salidaModelo.php";
require_once "modelo/ingresoModelo.php";
require_once "modelo/cajaModelo.php";
*/
require_once "modelo/usuarioModelo.php";
require_once "modelo/almacenModelo.php";
require_once "modelo/recepcionModelo.php";
require_once "modelo/cajasTikTokModelo.php";
require_once "modelo/entregaModelo.php";
require_once "modelo/clienteModelo.php";
require_once "modelo/encomiendasModelo.php";


$plantilla=new ControladorPlantilla();
$plantilla->ctrPlantilla();