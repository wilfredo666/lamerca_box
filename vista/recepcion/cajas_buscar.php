<?php
$h = static fn($valor) => htmlspecialchars((string) ($valor ?? ""), ENT_QUOTES, "UTF-8");
?>
<div class="busqueda-encomiendas">
  <div class="encabezado-busqueda">
    <h1>Cajas recibidas <span data-contador-resultados><?= (int) $totalCajas ?></span></h1>
    <form method="GET" action="<?= $base_url ?>recepcion/cajas-buscar" class="formulario-busqueda-manual">
      <input type="hidden" name="ruta" value="recepcion/cajas-buscar">
      <input type="search" name="buscar" value="<?= $h($buscar) ?>" placeholder="🔎 Buscar por cliente, empresa, código o tipo..." autofocus>
      <button type="submit" class="boton-buscar-manual"><i class="fas fa-search" aria-hidden="true"></i> Buscar</button>
      <a href="<?= $h($base_url . "recepcion/cajas-buscar?ruta=recepcion%2Fcajas-buscar") ?>" class="boton-limpiar-busqueda"><i class="fas fa-times" aria-hidden="true"></i> Limpiar</a>
    </form>
  </div>

  <?php if (!empty($_GET["mensaje"])): ?>
    <div class="alert alert-<?= ($_GET["tipo"] ?? "") === "error" ? "warning" : "success" ?>" role="alert">
      <?= $h($_GET["mensaje"]) ?>
    </div>
  <?php endif; ?>

  <div class="grid-encomiendas">
    <?php foreach ($cajas as $caja): ?>
      <article class="tarjeta-encomienda">
        <div class="encabezado-tarjeta">
          <div>
            <strong>📦 <?= $h($caja["nombre_cliente"]) ?></strong>
            <small><?= $h($caja["empresa"] ?: "Recepción general") ?></small>
          </div>
          <b><?= $h($caja["codigo"]) ?></b>
        </div>
        <div class="datos-tarjeta">
          <div>📦 <b>Tipo:</b> <?= $h($caja["tipo_recepcion"]) ?></div>
          <div>📱 <b>Celular:</b> <?= $h($caja["celular"] ?: "Sin registrar") ?></div>
          <div>📅 <b>Recepción:</b>
            <?php if (date("Y-m-d", strtotime($caja["fecha_registro"])) === date("Y-m-d")): ?>
              <span class="etiqueta-hoy">Hoy</span>
            <?php else: ?>
              <?= $h(date("d/m/Y H:i", strtotime($caja["fecha_registro"]))) ?>
            <?php endif; ?>
          </div>
          <div>📦 <b>Encomiendas:</b> <?= (int) $caja["total_encomiendas"] ?></div>
          <div>🟡 <b>Pendientes:</b> <?= (int) $caja["pendientes"] ?></div>
          <div>🔵 <b>Entregadas:</b> <?= (int) $caja["entregados"] ?></div>
          <div>🔵 <b>Estado:</b> <?= $h($caja["estado"]) ?></div>
        </div>
        <div class="foto-tarjeta">
          <?= !empty($caja["foto"]) ? '<img src="' . $h($base_url . "assets/img/recepciones/" . rawurlencode(basename($caja["foto"]))) . '" alt="Foto de la caja">' : "📷 Foto pendiente" ?>
        </div>
        <div class="acciones-tarjeta">
          <a class="accion ver" href="<?= $base_url ?>recepcion/caja-ver?id=<?= (int) $caja["id"] ?>">👁 Ver</a>
          <a class="accion editar" href="<?= $base_url ?>recepcion/caja-editar?id=<?= (int) $caja["id"] ?>">✏ Editar</a>
          <form method="POST" action="<?= $base_url ?>recepcion/caja-eliminar" onsubmit="return confirm('¿Eliminar esta caja y sus encomiendas?');">
            <input type="hidden" name="csrf_token" value="<?= $h($csrfToken) ?>">
            <input type="hidden" name="id" value="<?= (int) $caja["id"] ?>">
            <input type="hidden" name="buscar" value="<?= $h($buscar) ?>">
            <input type="hidden" name="pagina" value="<?= (int) $paginaActual ?>">
            <?php
            if (ControladorUsuario::ctrUsuarioPermiso($_SESSION["idUsuario"], 21)) {
            ?>
              <?php if ((int) $caja["entregados"] > 0): ?>
                <button
                  class="accion eliminar"
                  type="button"
                  title="No se puede eliminar una caja que contiene encomiendas entregadas."
                  aria-label="Eliminación bloqueada: la caja contiene encomiendas entregadas"
                  style="height: 47px; background-color: #ccc; cursor: not-allowed;"
                  disabled>
                  🗑 Eliminar
                </button>
              <?php else: ?>
                <button class="accion eliminar" type="submit">🗑 Eliminar</button>
              <?php endif; ?>
            <?php
            } else {
            ?>
              <!-- El usuario no tiene permiso para eliminar -->
              <button
                type="button"
                class="accion eliminar"
                style="height: 47px; background-color: #ccc; cursor: not-allowed;" disabled>
                🗑 Eliminar
              </button>
            <?php
            }
            ?>
          </form>
          <?php if ((int) $caja["entregados"] > 0): ?>
            <!--mensaje opcional si la caja tiene encomiendas entregadas.-->
          <?php endif; ?>
          <form method="POST" action="<?= $base_url ?>recepcion/caja-foto" enctype="multipart/form-data" class="formulario-foto">
            <input type="hidden" name="csrf_token" value="<?= $h($csrfToken) ?>">
            <input type="hidden" name="id" value="<?= (int) $caja["id"] ?>">
            <input type="hidden" name="buscar" value="<?= $h($buscar) ?>">
            <input type="hidden" name="pagina" value="<?= (int) $paginaActual ?>">
            <label class="accion foto">
              📷 Foto
              <input type="file" name="foto" accept="image/jpeg,image/png,image/gif,image/webp" capture="environment" required onchange="this.form.submit()">
            </label>
          </form>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
  <p class="sin-resultados" data-sin-resultados <?= empty($cajas) ? "" : "hidden" ?>>No se encontraron cajas TikTok o cajas generales.</p>
  <?php if ($totalCajas > 0): ?>
    <?php
    $urlPagina = static function ($pagina) use ($base_url, $buscar) {
      return $base_url . "recepcion/cajas-buscar?" . http_build_query([
        "ruta" => "recepcion/cajas-buscar",
        "buscar" => $buscar,
        "pagina" => $pagina
      ]);
    };
    $paginasVisibles = array_unique(array_merge(
      [1, $totalPaginas],
      range(max(1, $paginaActual - 2), min($totalPaginas, $paginaActual + 2))
    ));
    sort($paginasVisibles);
    ?>
    <nav class="paginacion-encomiendas" aria-label="Paginación de cajas">
      <span>Mostrando <?= (int) ((($paginaActual - 1) * 12) + 1) ?> a <?= (int) min($paginaActual * 12, $totalCajas) ?> de <?= (int) $totalCajas ?></span>
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
            <a class="page-link" href="<?= $h($urlPagina($pagina)) ?>" <?= $pagina === $paginaActual ? 'aria-current="page"' : "" ?>><?= (int) $pagina ?></a>
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