<?php if(isset($errorVista)): ?>
<p><?= htmlspecialchars($errorVista, ENT_QUOTES, "UTF-8") ?></p>
<?php else: ?>

<div class="contenedor">

<div class="card">

<h1>

📦 TU MERCA ENCOMIENDAS

</h1>

<div class="info">

<div>
    <b>📦 Código:</b>
    <?= htmlspecialchars($caja["codigo"]) ?>
</div>

<div>
    <b>📦 Tipo:</b>
    <?= htmlspecialchars($caja["tipo"]) ?>
</div>

<?php if(!empty($caja["empresa"])): ?>

<div>
    <b>🏪 Empresa:</b>
    <?= htmlspecialchars($caja["empresa"]) ?>
</div>

<?php endif; ?>

<div>
    <b>📅 Fecha y hora de recepción:</b>
    <?= date("d/m/Y H:i",strtotime($caja["fecha"])) ?>
</div>

<div class="separador-info"></div>

<div>
    <b>📦 Total registrados:</b>
    <?= $resumen["total"] ?>
</div>

<div>
    🟡 Pendientes:
    <b><?= $resumen["pendientes"] ?></b>
</div>

<div>
    ✅ Entregados:
    <b><?= $resumen["entregados"] ?></b>
</div>

<div class="estado-final">
    <b>Estado:</b>
    <?= htmlspecialchars($caja["estado"]) ?>
</div>

</div>

<hr style="margin-top:25px;margin-bottom:25px;">

<?php

?>

<div class="paquetesGrid">

<?php

$numero = 1;

$loteActual = null;

foreach($paquetes as $paquete){

    /*
    Detectar cambio de recepción
    */

    if($paquete["lote_recepcion"] != $loteActual){

        $loteActual = $paquete["lote_recepcion"];

        $datosLote = $lotes[$loteActual];

        $horaLote = date(
            "H:i",
            strtotime($datosLote["hora_inicio"])
        );

        $cantidadLote = $datosLote["cantidad"];

        if($loteActual == $ultimoLote){

            echo '

            <div class="separadorLote">

                🕐 <strong>RECEPCIÓN ACTUAL</strong>

                <span>
                    · '.$horaLote.'
                    · '.$cantidadLote.'
                    paquete'.($cantidadLote != 1 ? 's' : '').' agregado'.($cantidadLote != 1 ? 's' : '').'
                </span>

            </div>

            ';

        }else{

            echo '

            <div class="separadorLote">

                🕐 <strong>RECEPCIÓN '.$loteActual.'</strong>

                <span>
                    · '.$horaLote.'
                    · '.$cantidadLote.'
                    paquete'.($cantidadLote != 1 ? 's' : '').'
                </span>

            </div>

            ';

        }

    }

?>

<div class="paquete <?= 
    $paquete["estado"]=="Pendiente"
    ? "paquetePendiente"
    : "paqueteEntregado"
?>">

<h3>

<?= 
    $paquete["estado"]=="Pendiente"
    ? "🟡"
    : "✅"
?>

Paquete <?= $numero ?>

</h3>

<br><br>

<b>👤 Cliente:</b>

<?= htmlspecialchars(
    $paquete["cliente"] ?? "",
    ENT_QUOTES,
    "UTF-8"
) ?>

<br><br>

<b>📱 Celular:</b>

<?= $paquete["celular"]=="" 
    ? "Sin registrar" 
    : htmlspecialchars(
        $paquete["celular"],
        ENT_QUOTES,
        "UTF-8"
    )
?>

<br><br>

<b>📝 Detalle:</b>

<?= htmlspecialchars(
    $paquete["observaciones"] ?? "",
    ENT_QUOTES,
    "UTF-8"
) ?>

<br><br>

<b>Estado:</b>

<span class="<?= 
    $paquete["estado"]=="Pendiente"
    ? "estadoPendiente"
    : "estadoEntregado"
?>">

<?= 
    $paquete["estado"]=="Pendiente"
    ? "🟡 Pendiente"
    : "✅ Entregado"
?>

</span>

</div>

<?php

$numero++;

}

?>

</div>

<hr style="margin-top:30px;">

<div class="acciones">

    <div class="tituloAcciones">
        ➕ ¿Qué recepción quieres registrar ahora?
    </div>

    <div class="botonesRecepcion">

        <a href="<?= $base_url ?>recepcion/general" class="btnRecepcion btnGeneral">
            📦 Recepción General
        </a>

        <a href="<?= $base_url ?>recepcion/tiktok" class="btnRecepcion btnTikTok">
            🎵 Recepción TikTok
        </a>

    </div>

    <button
        class="btnWhatsapp"
        data-whatsapp="<?= htmlspecialchars($numeroWhatsapp, ENT_QUOTES, "UTF-8") ?>"
        data-mensaje="<?= htmlspecialchars($mensajeWhatsapp, ENT_QUOTES, "UTF-8") ?>">

        📲 Compartir por WhatsApp

    </button>

</div>

</div>

</div>

<script src="<?= $base_url ?>assets/js/modules/recepcion/comprobante_caja.js"></script>
<?php endif; ?>