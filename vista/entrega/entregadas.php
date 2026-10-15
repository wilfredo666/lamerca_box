<?php $h = fn($valor) => htmlspecialchars((string) ($valor ?? ""), ENT_QUOTES, "UTF-8"); ?>
<div class="contenedor">
  <div class="card">
    <h2 class="tituloHistorial">✅ Encomiendas entregadas</h2>
    <form method="GET" action="<?= $h($base_url) ?>" class="formulario-busqueda-entregas">
      <input type="hidden" name="ruta" value="entrega/entregadas">
      <div class="input-group grupo-busqueda-responsive">
        <input type="text" name="buscar" value="<?= $h($buscar) ?>" maxlength="100" placeholder="🔎 Buscar por código, destinatario, descripción o remitente..." class="form-control">
        <span class="input-group-append">
          <button type="submit" class="btn btn-info btn-flat"><i class="fas fa-search" aria-hidden="true"></i> Buscar</button>
          <a href="<?= $h($base_url . "entrega/entregadas") ?>" class="btn btn-danger btn-flat"><i class="fas fa-broom" aria-hidden="true"></i> Limpiar</a>
        </span>
      </div>
    </form>
    <div class="tabla-responsive">
      <table id="tablaEntregas" class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>Código</th>
            <th>Destinatario</th>
            <th>Descripción</th>
            <th>Remitente</th>
            <th>Total cobrado</th>
            <th>Método</th>
            <th>Fecha de entrega</th>
            <th>Estado</th>
            <th>Detalle</th>
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
              <td>
                <?= $h($paquete["metodo_cobro"] ?: "No registrado") ?>
                <?php if (!empty($paquete["observaciones"])): ?><br><small><?= $h($paquete["observaciones"]) ?></small><?php endif; ?>
              </td>
              <td data-order="<?= $h($paquete["fecha_entrega"]) ?>">
                <?= $h(date("d/m/Y H:i", strtotime($paquete["fecha_entrega"]))) ?>
              </td>
              <td><span class="estadoEntregado"><?= $h($paquete["estado"]) ?></span></td>
              <td>
                <a class="boton-ver-detalle-entrega" href="<?= $h($base_url . "entrega/ver-entregada?id=" . (int) $paquete["id"]) ?>">
                  <i class="fas fa-eye" aria-hidden="true"></i> Detalle
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($paquetes)): ?>
            <tr><td colspan="9" class="text-center text-muted">No se encontraron entregas.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <?php if ($totalEntregas > 0): ?>
      <?php
      $urlPagina = static fn($pagina) => $base_url . "entrega/entregadas?" . http_build_query([
        "ruta" => "entrega/entregadas",
        "buscar" => $buscar,
        "pagina" => $pagina
      ]);
      $paginasVisibles = array_unique(array_merge(
        [1, $totalPaginas],
        range(max(1, $paginaActual - 2), min($totalPaginas, $paginaActual + 2))
      ));
      sort($paginasVisibles);
      ?>
      <nav class="paginacion-entregas" aria-label="Paginación de entregas">
        <span>Mostrando <?= (int) (($paginaActual - 1) * $porPagina + 1) ?> a <?= (int) min($paginaActual * $porPagina, $totalEntregas) ?> de <?= (int) $totalEntregas ?></span>
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item <?= $paginaActual === 1 ? "disabled" : "" ?>">
            <a class="page-link" href="<?= $h($urlPagina(max(1, $paginaActual - 1))) ?>" aria-label="Página anterior">&laquo;</a>
          </li>
          <?php $paginaPrevia = 0; ?>
          <?php foreach ($paginasVisibles as $pagina): ?>
            <?php if ($paginaPrevia > 0 && $pagina > $paginaPrevia + 1): ?>
              <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
            <?php endif; ?>
            <li class="page-item <?= $pagina === $paginaActual ? "active" : "" ?>">
              <a class="page-link" href="<?= $h($urlPagina($pagina)) ?>"><?= (int) $pagina ?></a>
            </li>
            <?php $paginaPrevia = $pagina; ?>
          <?php endforeach; ?>
          <li class="page-item <?= $paginaActual === $totalPaginas ? "disabled" : "" ?>">
            <a class="page-link" href="<?= $h($urlPagina(min($totalPaginas, $paginaActual + 1))) ?>" aria-label="Página siguiente">&raquo;</a>
          </li>
        </ul>
      </nav>
    <?php endif; ?>
  </div>
</div>
