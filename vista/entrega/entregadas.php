<?php $h = fn($valor) => htmlspecialchars((string) ($valor ?? ""), ENT_QUOTES, "UTF-8"); ?>
<div class="contenedor">
  <div class="card">
    <h2 class="tituloHistorial">✅ Encomiendas entregadas</h2>
    <div class="tabla-responsive">
      <table id="tablaEntregas" class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>Código</th>
            <th>Destinatario</th>
            <th>Descripción</th>
            <th>Cliente</th>
            <th>Total cobrado</th>
            <th>Método</th>
            <th>Fecha de entrega</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($paquetes as $paquete): ?>
            <tr>
              <td><?= $h($paquete["codigo"]) ?></td>
              <td><?= $h($paquete["destinatario"]) ?></td>
              <td><?= $h($paquete["descripcion"] ?: "Sin descripción") ?></td>
              <td><?= $h($paquete["cliente"] ?: "Sin cliente") ?></td>
              <td data-order="<?= number_format((float) $paquete["total_cobrado"], 2, ".", "") ?>">
                Bs <?= number_format((float) $paquete["total_cobrado"], 2) ?>
              </td>
              <td><?= $h($paquete["metodo_cobro"] ?: "No registrado") ?></td>
              <td data-order="<?= $h($paquete["fecha_entrega"]) ?>">
                <?= $h(date("d/m/Y H:i", strtotime($paquete["fecha_entrega"]))) ?>
              </td>
              <td><span class="estadoEntregado"><?= $h($paquete["estado"]) ?></span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
