<?php $h = fn($valor) => htmlspecialchars((string) ($valor ?? ""), ENT_QUOTES, "UTF-8"); ?>
<div class="contenedor-traspasos">
  <div class="card">
    <h2 class="titulo-traspasos">↔ Historial de traspasos</h2>
    <div class="tabla-responsive">
      <table id="tablaTraspasos" class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>Código de traspaso</th>
            <th>Encomienda</th>
            <th>Destinatario</th>
            <th>Almacén origen</th>
            <th>Almacén destino</th>
            <th>Observaciones</th>
            <th>Usuario</th>
            <th>Fecha</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($traspasos as $traspaso): ?>
            <tr>
              <td><?= $h($traspaso["codigo"]) ?></td>
              <td><?= $h($traspaso["codigo_encomienda"] ?: "Sin código") ?></td>
              <td><?= $h($traspaso["destinatario"] ?: "Sin destinatario") ?></td>
              <td><?= $h($traspaso["almacen_origen"]) ?></td>
              <td><?= $h($traspaso["almacen_destino"]) ?></td>
              <td><?= $h($traspaso["concepto"] ?: "Sin observaciones") ?></td>
              <td><?= $h($traspaso["usuario"]) ?></td>
              <td data-order="<?= $h($traspaso["fecha_traspaso"]) ?>">
                <?= $h(date("d/m/Y H:i", strtotime($traspaso["fecha_traspaso"]))) ?>
              </td>
              <td><span class="estado-traspaso"><?= $h($traspaso["estado"]) ?></span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
