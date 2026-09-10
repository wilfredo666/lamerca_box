<?php

require_once "modelo/encomiendasModelo.php";
require_once "controlador/encomiendasControlador.php";

$cobradoHoy = ControladorEncomiendas::ctrTotalCobradoHoy();
$encomiendasRegistradasHoy = ControladorEncomiendas::ctrCantidadEncomiendasHoy();
$encomiendasPendientes = ControladorEncomiendas::ctrCantidadPendientes();
$encomiendasEntregadasHoy = ControladorEncomiendas::ctrCantidadEntregadasHoy();
$resumenCaja = ControladorCaja::ctrVistaCaja()["resumen"];

$totalCobradoHoy = $cobradoHoy["total"];
$encomiendasHoy = $encomiendasRegistradasHoy["total"];
$pendientes = $encomiendasPendientes["total"];
$entregadasHoy = $encomiendasEntregadasHoy["total"];
$totalEnCaja = (float) $resumenCaja["saldo_actual"];
$totalAmbos = $totalCobradoHoy + $totalEnCaja;
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

        <div class="tarjeta tarjeta-resumen">

            <table class="tabla-resumen">
                <tr>
                    <td>Cobrado Hoy</td>
                    <td>Bs <?= number_format($totalCobradoHoy, 2) ?></td>
                </tr>
                <tr>
                    <td>Total en Caja</td>
                    <td>Bs <?= number_format($totalEnCaja, 2) ?></td>
                </tr>
                <tr class="fila-total">
                    <td>Total</td>
                    <td>Bs <?= number_format($totalAmbos, 2) ?></td>
                </tr>
            </table>
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
    <a href="<?= $base_url ?? '' ?>traspasos" class="boton">
        ↔ Listado de Traspasos
    </a>

    <a href="<?= $base_url ?? '' ?>encomiendas/buscar" class="boton">
        🔍 Buscar Encomienda
    </a>

    <a href="<?= $base_url ?? '' ?>entrega/entregadas" class="boton">

        ✅ Listado de Entregas

    </a>

    <a href="<?= $base_url ?? '' ?>recepcion/cajas-buscar" class="boton">
        📦 Buscar Cajas
    </a>


</div>

</div>