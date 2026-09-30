<?php
$h = static fn($valor) => htmlspecialchars((string) ($valor ?? ""), ENT_QUOTES, "UTF-8");
if (!empty($errorVista)):
?>
  <div class="contenedor">
    <div class="card">
      <p><?= $h($errorVista) ?></p>
      <a class="boton-volver-encomiendas" href="<?= $h($base_url . "encomiendas/eliminadas") ?>">Volver al historial</a>
    </div>
  </div>
  <?php return; ?>
<?php
endif;
$foto = !empty($encomiendaEliminada["foto"])
  ? $base_url . "assets/img/paquetes/" . rawurlencode(basename($encomiendaEliminada["foto"]))
  : "";
?>
<div class="contenedor">
  <div class="card detalle-encomienda-eliminada">
    <div class="detalle-eliminada-cabecera">
      <div>
        <h1>Detalle de encomienda eliminada</h1>
        <strong><?= $h($encomiendaEliminada["codigo"]) ?></strong>
               <span class="estado-encomienda-eliminada">Eliminado</span>
      </div>
      
       <a class="boton-volver-encomiendas" href="<?= $h($base_url . "encomiendas/eliminadas") ?>">← Volver al historial</a>
    </div>
    <div class="detalle-eliminada-contenido">

      <section>
        <h2>Datos de encomienda</h2>
        <dl>
          <div>
            <dt>Destinatario</dt>
            <dd><?= $h($encomiendaEliminada["destinatario"]) ?></dd>
          </div>
          <div>
            <dt>Contacto</dt>
            <dd><?= $h($encomiendaEliminada["contacto"] ?: "Sin registrar") ?></dd>
          </div>
          <div>
            <dt>Descripción</dt>
            <dd><?= nl2br($h($encomiendaEliminada["descripcion"] ?: "Sin descripción")) ?></dd>
          </div>
          <div>
            <dt>Clasificación</dt>
            <dd><?= $h($encomiendaEliminada["clasificacion"] ?: "Sin registrar") ?></dd>
          </div>
          <div>
            <dt>Precio</dt>
            <dd>Bs <?= number_format((float) $encomiendaEliminada["precio"], 2) ?></dd>
          </div>
          <div>
            <dt>Quién paga</dt>
            <dd><?= $h($encomiendaEliminada["quien_paga"] ?: "Sin registrar") ?></dd>
          </div>
          <div>
            <dt>Estado</dt>
            <dd><?= $h($encomiendaEliminada["estado"]) ?></dd>
            <dt>Motivo</dt>
            <dd><?= nl2br($h($encomiendaEliminada["motivo_eliminacion"] ?: "Sin motivo registrado")) ?></dd>
          </div>
        </dl>
      </section>
      <section>
        <div class="detalle-eliminada-foto">
          <?php if ($foto !== ""): ?>
            <a href="<?= $h($foto) ?>" target="_blank" rel="noopener"><img src="<?= $h($foto) ?>" alt="Foto de la encomienda eliminada"></a>
          <?php else: ?>
            <span>📷 Sin imagen registrada</span>
          <?php endif; ?>
        </div>
      </section>
      <section>
        <h2>Remitente y recepción</h2>
        <dl>
          <div>
            <dt>Remitente</dt>
            <dd><?= $h($encomiendaEliminada["cliente"] ?: "Sin registrar") ?></dd>
          </div>
          <div>
            <dt>Celular del remitente</dt>
            <dd><?= $h($encomiendaEliminada["celular_remitente"] ?: "Sin registrar") ?></dd>
          </div>
          <div>
            <dt>Empresa</dt>
            <dd><?= $h($encomiendaEliminada["empresa"] ?: "Sin registrar") ?></dd>
          </div>
          <div>
            <dt>Código de recepción</dt>
            <dd><?= $h($encomiendaEliminada["codigo_recepcion"] ?: "Sin registrar") ?></dd>
          </div>
          <div>
            <dt>Tipo de recepción</dt>
            <dd><?= $h($encomiendaEliminada["tipo_recepcion"] ?: "Sin registrar") ?></dd>
          </div>
          <div>
            <dt>Fecha de recepción</dt>
            <dd><?= $h(date("d/m/Y H:i", strtotime($encomiendaEliminada["fecha_recepcion"]))) ?></dd>
          </div>
          <?php if (!empty($encomiendaEliminada["observaciones_recepcion"])): ?>
            <div>
              <dt>Observaciones de recepción</dt>
              <dd><?= nl2br($h($encomiendaEliminada["observaciones_recepcion"])) ?></dd>
            </div>
          <?php endif; ?>
          <div>
            <dt>Fecha de eliminación</dt>
            <dd><?= !empty($encomiendaEliminada["fecha_eliminacion"]) ? $h(date("d/m/Y H:i", strtotime($encomiendaEliminada["fecha_eliminacion"]))) : "No registrada" ?></dd>
          </div>
          <div></div>
        </dl>
      </section>

    </div>

  </div>
</div>
</div>