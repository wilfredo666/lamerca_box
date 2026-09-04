<?php

require_once "modelo/encomiendasModelo.php";
require_once "controlador/encomiendasControlador.php";

$cobradoHoy = ControladorEncomiendas::ctrTotalCobradoHoy();
$encomiendasRegistradasHoy = ControladorEncomiendas::ctrCantidadEncomiendasHoy();
$encomiendasPendientes = ControladorEncomiendas::ctrCantidadPendientes();
$encomiendasEntregadasHoy = ControladorEncomiendas::ctrCantidadEntregadasHoy();

$totalCobradoHoy = $cobradoHoy["total"];
$encomiendasHoy = $encomiendasRegistradasHoy["total"];
$pendientes = $encomiendasPendientes["total"];
$entregadasHoy = $encomiendasEntregadasHoy["total"];
?>



<div class="contenedor">

    <div class="tarjetas">

        <div class="tarjeta">

            Encomiendas de Hoy

            <div class="numero">

                <?= $encomiendasHoy ?>

            </div>

        </div>

        <div class="tarjeta">

            Entregadas Hoy

            <div class="numero">

                <?= $entregadasHoy ?>

            </div>

        </div>

        <div class="tarjeta">

            Cobrado Hoy

            <div class="numero">

                Bs <?= number_format($totalCobradoHoy, 2) ?>

            </div>

        </div>

        <div class="tarjeta">

            Pendientes

            <div class="numero">

                <?= $pendientes ?>

            </div>

        </div>

    </div>

</div>

</div>

<div class="botones">

    <a href="<?= $base_url ?? '' ?>recepcion/general" class="boton">

        📦 Nueva Recepción

    </a>

    <a href="<?= $base_url ?? '' ?>entrega/entregadas" class="boton">

        ✅ Listado de Entregas

</a>

    <a href="<?= $base_url ?? '' ?>encomiendas/buscar" class="boton">
        🔍 Buscar Encomienda
    </a>

    <a href="<?= $base_url ?? '' ?>recepcion/cajas-buscar" class="boton">
        📦 Buscar Cajas
    </a>
</div>

</div>