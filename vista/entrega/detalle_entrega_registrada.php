<?php
$h = static fn($valor) => htmlspecialchars((string) ($valor ?? ""), ENT_QUOTES, "UTF-8");
if (!empty($errorVista)):
?>
  <div class="contenedor">
    <div class="card detalle-entrega-registrada">
      <p class="detalle-entrega-alerta"><?= $h($errorVista) ?></p>
      <a class="boton-volver-entregas" href="<?= $h($base_url . "entrega/entregadas") ?>">Volver a entregas</a>
    </div>
  </div>
  <?php return; ?>
<?php
endif;
$foto = !empty($entrega["foto"])
  ? $base_url . "assets/img/paquetes/" . rawurlencode(basename($entrega["foto"]))
  : "";
?>
<div class="contenedor">
  <section class="card detalle-entrega-registrada">
    <header class="detalle-entrega-encabezado">
      <div>
        <h1>Detalle de encomienda entregada</h1>
        <p><?= $h($entrega["codigo"]) ?></p>
      </div>
      <a class="boton-volver-entregas" href="<?= $h($base_url . "entrega/entregadas") ?>">Volver a entregas</a>
    </header>
    <div class="row">

      <div class="col-6">
        <div class="detalle-entrega-secciones">
          <section>
            <h2>Datos de la encomienda</h2>
            <dl>
              <div>
                <dt>Código</dt>
                <dd><?= $h($entrega["codigo"]) ?></dd>
              </div>
              <div>
                <dt>Destinatario</dt>
                <dd><?= $h($entrega["destinatario"]) ?></dd>
              </div>
              <div>
                <dt>Contacto</dt>
                <dd><?= $h($entrega["contacto"] ?: "Sin registrar") ?></dd>
              </div>
              <div>
                <dt>Descripción / detalle</dt>
                <dd><?= nl2br($h($entrega["descripcion"] ?: "Sin descripción")) ?></dd>
              </div>
              <div>
                <dt>Clasificación</dt>
                <dd><?= $h($entrega["clasificacion"] ?: "Sin clasificación") ?></dd>
              </div>
              <div>
                <dt>Quién paga</dt>
                <dd><?= $h($entrega["quien_paga"] ?: "No registrado") ?></dd>
              </div>
              <div>
                <dt>Fecha de registro</dt>
                <dd><?= $h(date("d/m/Y H:i", strtotime($entrega["fecha_encomienda"]))) ?></dd>
              </div>
            </dl>

          </section>
        </div>
      </div>

      <div class="col-6">
        <div class="text-center">
          <?php if ($foto !== ""): ?>
            <img width="35%" class="rounded" src="<?= $h($foto) ?>" alt="Foto de la encomienda <?= $h($entrega["codigo"]) ?>">
          <?php else: ?>
            <span>📷 Sin foto registrada</span>
          <?php endif; ?>
        </div>
      </div>

    </div>

    <div class="row" style="margin-top: 20px;">
      <div class="col-6">
        <div class="detalle-entrega-secciones">
          <section>
            <h2>Entrega</h2>
            <dl>
              <div>
                <dt>Fecha de entrega</dt>
                <dd><?= $h(date("d/m/Y H:i", strtotime($entrega["fecha_entrega"]))) ?></dd>
              </div>
              <div>
                <dt>Estado</dt>
                <dd><?= $h($entrega["estado_entrega"]) ?></dd>
              </div>
              <div>
                <dt>Precio registrado</dt>
                <dd>Bs <?= number_format((float) $entrega["precio"], 2) ?></dd>
              </div>
              <div>
                <dt>Recargo</dt>
                <dd>Bs <?= number_format((float) $entrega["recargo"], 2) ?></dd>
              </div>
              <div>
                <dt>Descuento</dt>
                <dd>Bs <?= number_format((float) $entrega["descuento"], 2) ?></dd>
              </div>
              <div class="detalle-entrega-total">
                <dt>Total cobrado</dt>
                <dd>Bs <?= number_format((float) $entrega["total_cobrado"], 2) ?></dd>
              </div>
              <div>
                <dt>Método de cobro</dt>
                <dd><?= $h($entrega["metodo_cobro"] ?: "No registrado") ?></dd>
              </div>
              <div>
                <dt>Entregado por</dt>
                <dd><?= $h($entrega["usuario_entrega"] ?: "No registrado") ?></dd>
              </div>
              <div>
                <dt>Almacén de entrega</dt>
                <dd><?= $h($entrega["almacen_entrega"] ?: "No registrado") ?></dd>
              </div>
              <?php if (!empty($entrega["observaciones_entrega"])): ?>
                <div>
                  <dt>Observaciones de entrega</dt>
                  <dd><?= nl2br($h($entrega["observaciones_entrega"])) ?></dd>
                </div>
              <?php endif; ?>
            </dl>
          </section>
        </div>

      </div>
      <div class="col-6">
        <div class="detalle-entrega-secciones">
          <section>
            <h2>Recepción / remitente</h2>
            <dl>
              <div>
                <dt>Remitente</dt>
                <dd><?= $h($entrega["cliente"] ?: "Sin registrar") ?></dd>
              </div>
              <div>
                <dt>Celular</dt>
                <dd><?= $h($entrega["celular_cliente"] ?: "Sin registrar") ?></dd>
              </div>
              <div>
                <dt>Empresa</dt>
                <dd><?= $h($entrega["empresa"] ?: "Sin registrar") ?></dd>
              </div>
              <div>
                <dt>Código de recepción</dt>
                <dd><?= $h($entrega["codigo_recepcion"] ?: "Sin código") ?></dd>
              </div>
              <div>
                <dt>Tipo de recepción</dt>
                <dd><?= $h($entrega["tipo_recepcion"] ?: "Sin registrar") ?></dd>
              </div>
              <div>
                <dt>Fecha de recepción</dt>
                <dd><?= $h(date("d/m/Y H:i", strtotime($entrega["fecha_recepcion"]))) ?></dd>
              </div>
              <?php if (!empty($entrega["observaciones_recepcion"])): ?>
                <div>
                  <dt>Observaciones de recepción</dt>
                  <dd><?= nl2br($h($entrega["observaciones_recepcion"])) ?></dd>
                </div>
              <?php endif; ?>
            </dl>
          </section>
        </div>

      </div>

    </div>


  </section>
</div>