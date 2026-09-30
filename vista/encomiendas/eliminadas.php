<?php $h = static fn($valor) => htmlspecialchars((string) ($valor ?? ""), ENT_QUOTES, "UTF-8"); ?>
<div class="contenedor">
  <div class="card">
    <h2 class="tituloHistorial">🗑️ Historial de encomiendas eliminadas</h2>
    <div class="tabla-responsive">
      <table id="tablaEncomiendasEliminadas" class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>Código encomienda</th>
            <th>Remitente</th>
            <th>Destinatario</th>
            <th>Fecha eliminación</th>
            <th>Motivo</th>
            <th>Detalle</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($encomiendasEliminadas as $encomienda): ?>
            <tr>
              <td><?= $h($encomienda["codigo"]) ?></td>
              <td><?= $h($encomienda["cliente"] ?: "Sin registrar") ?></td>
              <td><?= $h($encomienda["destinatario"]) ?></td>
              <td data-order="<?= $h($encomienda["fecha_eliminacion"] ?? "") ?>">
                <?= !empty($encomienda["fecha_eliminacion"]) ? $h(date("d/m/Y H:i", strtotime($encomienda["fecha_eliminacion"]))) : "No registrada" ?>
              </td>
              <td><?= $h($encomienda["motivo_eliminacion"] ?: "Sin motivo registrado") ?></td>
              <td>
                <a class="boton-ver-detalle-eliminada" href="<?= $h($base_url . "encomiendas/eliminadas/ver?id=" . (int) $encomienda["id"]) ?>">
                  <i class="fas fa-eye" aria-hidden="true"></i> Ver detalle
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <a class="boton-volver-encomiendas" href="<?= $h($base_url . "inicio") ?>">← Volver al inicio</a>
  </div>
</div>
