<?php
$h = static fn($valor) => htmlspecialchars((string) ($valor ?? ""), ENT_QUOTES, "UTF-8");
$fecha = static fn($valor) => !empty($valor) ? date("d/m/Y H:i", strtotime($valor)) : "No registrada";
$dato = static fn($valor, $vacio = "Sin registrar") => $valor !== null && $valor !== "" ? $valor : $vacio;
$clasesEstado = [
  "Pendiente" => "estado-pendiente",
  "Entregado" => "estado-entregado",
  "Eliminado" => "estado-eliminado"
];
?>
<br>
  <div class="card consulta-encomienda">

    <div class="card-header">
      <h1>Consultar encomienda</h1>
      <p class="consulta-ayuda">Busca por código, destinatario, remitente o celular en todos los almacenes.</p>
      <form method="GET" action="<?= $h($base_url) ?>" class="consulta-formulario">
        <input type="hidden" name="ruta" value="encomiendas/consultar">
        <div class="input-group">
          <input type="text" name="buscar" class="form-control" value="<?= $h($buscar) ?>"
            placeholder="Código, destinatario, remitente o celular" autocomplete="off" autofocus>
          <div class="input-group-append">
            <button type="submit" class="btn btn-info btn-flat"><i class="fas fa-search"></i> Buscar</button>
            <a href="<?= $h($base_url) ?>encomiendas/consultar" class="btn btn-danger btn-flat"><i class="fas fa-broom"></i> Limpiar</a>
          </div>
        </div>
      </form>
    </div>

    <?php if ($busquedaRealizada && empty($resultados)): ?>
      <p class="consulta-vacio">No se encontró ninguna encomienda para «<?= $h($buscar) ?>».</p>
    <?php elseif ($busquedaRealizada): ?>
      <p class="consulta-ayuda"><?= count($resultados) ?> resultado(s)<?= count($resultados) >= 20 ? " (se muestran los 20 más recientes; afina tu búsqueda)" : "" ?></p>
    <?php endif; ?>

    <?php foreach ($resultados as $e): ?>
      <?php
      $foto = !empty($e["foto"]) ? $base_url . "assets/img/paquetes/" . rawurlencode(basename($e["foto"])) : "";
      $claseEstado = $clasesEstado[$e["estado"]] ?? "estado-pendiente";
      ?>
      <article class="consulta-resultado">
        <header class="consulta-cabecera">
          <div>
            <strong class="consulta-codigo"><?= $h($e["codigo"]) ?></strong>
            <span class="consulta-estado <?= $claseEstado ?>"><?= $h($e["estado"]) ?></span>
          </div>
          <?php if ($e["estado"] !== "Eliminado"): ?>
            <a class="btn btn-sm btn-secondary" href="<?= $h($base_url) ?>encomiendas/ver?id=<?= (int) $e["id"] ?>">Ver detalle</a>
          <?php endif; ?>
        </header>

        <div class="consulta-ubicacion">
          <?php if ($e["estado"] === "Entregado"): ?>
            ✅ Entregada el <?= $h($fecha($e["fecha_entrega"])) ?><?= !empty($e["almacen_entrega"]) ? " en " . $h($e["almacen_entrega"]) : "" ?>
          <?php elseif ($e["estado"] === "Eliminado"): ?>
            🗑 Eliminada · última ubicación: <?= $h($dato($e["almacen_actual"])) ?>
          <?php else: ?>
            📍 Ubicada en <strong><?= $h($dato($e["almacen_actual"])) ?></strong><?= !empty($e["ciudad_almacen"]) ? " (" . $h($e["ciudad_almacen"]) . ")" : "" ?>
          <?php endif; ?>
        </div>

        <div class="consulta-secciones">
          <section>
            <h2>Encomienda</h2>
            <dl>
              <div>
                <dt>Destinatario</dt>
                <dd><?= $h($dato($e["destinatario"])) ?></dd>
              </div>
              <div>
                <dt>Contacto</dt>
                <dd><?= $h($dato($e["contacto"])) ?></dd>
              </div>
              <div>
                <dt>Descripción</dt>
                <dd><?= nl2br($h($dato($e["descripcion"], "Sin descripción"))) ?></dd>
              </div>
              <div>
                <dt>Clasificación</dt>
                <dd><?= $h($dato($e["clasificacion"])) ?></dd>
              </div>
              <div>
                <dt>Precio</dt>
                <dd>Bs <?= number_format((float) $e["precio"], 2) ?></dd>
              </div>
              <div>
                <dt>Quién paga</dt>
                <dd><?= $h($dato($e["quien_paga"])) ?></dd>
              </div>
              <div>
                <dt>Cobrado</dt>
                <dd><?= !empty($e["cobrado"]) ? "Sí" : "No" ?></dd>
              </div>
              <div>
                <dt>Registrada</dt>
                <dd><?= $h($fecha($e["fecha_registro"])) ?></dd>
              </div>
            </dl>
          </section>

          <section>
            <h2>Remitente y recepción</h2>
            <dl>
              <div>
                <dt>Remitente</dt>
                <dd><?= $h($dato($e["remitente"])) ?></dd>
              </div>
              <div>
                <dt>Celular</dt>
                <dd><?= $h($dato($e["celular_remitente"])) ?></dd>
              </div>
              <div>
                <dt>Empresa</dt>
                <dd><?= $h($dato($e["empresa"])) ?></dd>
              </div>
              <div>
                <dt>Caja / recepción</dt>
                <dd><?= $h($dato($e["codigo_recepcion"])) ?> (<?= $h($dato($e["estado_recepcion"], "-")) ?>)</dd>
              </div>
              <div>
                <dt>Tipo</dt>
                <dd><?= $h($dato($e["tipo_recepcion"])) ?></dd>
              </div>
              <div>
                <dt>Fecha recepción</dt>
                <dd><?= $h($fecha($e["fecha_recepcion"])) ?></dd>
              </div>
            </dl>
          </section>

          <?php if ($e["estado"] === "Entregado" || !empty($e["fecha_entrega"])): ?>
            <section>
              <h2>Entrega</h2>
              <dl>
                <div>
                  <dt>Fecha</dt>
                  <dd><?= $h($fecha($e["fecha_entrega"])) ?></dd>
                </div>
                <div>
                  <dt>Entregado por</dt>
                  <dd><?= $h($dato($e["usuario_entrega"])) ?></dd>
                </div>
                <div>
                  <dt>Almacén</dt>
                  <dd><?= $h($dato($e["almacen_entrega"])) ?></dd>
                </div>
                <div>
                  <dt>Método de cobro</dt>
                  <dd><?= $h($dato($e["metodo_cobro"])) ?></dd>
                </div>
                <div>
                  <dt>Recargo</dt>
                  <dd>Bs <?= number_format((float) $e["recargo"], 2) ?></dd>
                </div>
                <div>
                  <dt>Descuento</dt>
                  <dd>Bs <?= number_format((float) $e["descuento"], 2) ?></dd>
                </div>
                <div>
                  <dt>Total cobrado</dt>
                  <dd><strong>Bs <?= number_format((float) $e["total_cobrado"], 2) ?></strong></dd>
                </div>
                <?php if (!empty($e["observaciones_entrega"])): ?>
                  <div>
                    <dt>Observaciones</dt>
                    <dd><?= nl2br($h($e["observaciones_entrega"])) ?></dd>
                  </div>
                <?php endif; ?>
              </dl>
            </section>
          <?php endif; ?>

          <?php if ($e["estado"] === "Eliminado"): ?>
            <section>
              <h2>Eliminación</h2>
              <dl>
                <div>
                  <dt>Fecha</dt>
                  <dd><?= $h($fecha($e["fecha_actualizacion"])) ?></dd>
                </div>
                <div>
                  <dt>Motivo</dt>
                  <dd><?= nl2br($h($dato($e["observacion"], "Sin motivo registrado"))) ?></dd>
                </div>
              </dl>
            </section>
          <?php endif; ?>

          <section>
            <h2>Traspasos</h2>
            <?php if (empty($e["traspasos"])): ?>
              <p class="consulta-ayuda">Sin traspasos registrados.</p>
            <?php else: ?>
              <ul class="consulta-traspasos">
                <?php foreach ($e["traspasos"] as $t): ?>
                  <li>
                    <strong><?= $h($dato($t["origen"], "?")) ?> → <?= $h($dato($t["destino"], "?")) ?></strong>
                    <span><?= $h($fecha($t["fecha_traspaso"])) ?> · <?= $h($t["codigo"]) ?> · <?= $h($t["estado"]) ?><?= !empty($t["usuario"]) ? " · " . $h($t["usuario"]) : "" ?></span>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </section>

          <section class="consulta-foto">
            <?php if ($foto !== ""): ?>
              <a href="<?= $h($foto) ?>" target="_blank" rel="noopener"><img src="<?= $h($foto) ?>" alt="Foto de la encomienda"></a>
            <?php else: ?>
              <span>📷 Sin imagen registrada</span>
            <?php endif; ?>
          </section>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
