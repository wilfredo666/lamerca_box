<?php $h = fn($valor) => htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8"); ?>
<div class="entrega-encabezado">📦 Entrega de Paquetes</div>
<div class="contenedor">
  <?php if (!empty($_GET["mensaje"])) { ?><p class="alerta"><?= $h($_GET["mensaje"]) ?></p><?php } ?>
  <div id="layoutPrincipal">
    <div class="card">
      <input type="text" id="buscar" placeholder="Buscar por nombre o celular...">
      <div id="resultados" style="margin-top:25px;">
        <?php foreach ($paquetes as $paquete):
          $precioBase = (float) ($paquete["precio"] ?? 2);
          $precioBase = $precioBase > 0 ? $precioBase : 2;
          $dias = max(0, (new DateTime($paquete["fecha_registro"]))->diff(new DateTime())->days);
          $recargo = $dias > 7 ? 1 : 0;
          $total = $precioBase + $recargo;
          $tipo = trim((string) $paquete["tipo"]);
          $etiqueta = strtolower($tipo) === "alfabeto" ? mb_strtoupper(mb_substr(trim($paquete["cliente"]), 0, 1, "UTF-8"), "UTF-8") : (strtolower($tipo) === "tiktok" ? ($paquete["codigo"] ?? "") : mb_strtoupper($tipo, "UTF-8"));
          $foto = !empty($paquete["foto"]) ? $base_url . "assets/img/paquetes/" . rawurlencode(basename($paquete["foto"])) : "";
        ?>
        <div class="paquete" data-id="<?= (int) $paquete["id"] ?>" data-precio="<?= $precioBase ?>" data-recargo="<?= $recargo ?>" data-total="<?= $total ?>">
          <div class="filaSuperior">
            <div style="display:flex;align-items:center;gap:15px;">
              <input type="checkbox" class="selectorPaquete" value="<?= (int) $paquete["id"] ?>" data-precio="<?= $precioBase ?>" data-recargo="<?= $recargo ?>" data-total="<?= $total ?>" <?= in_array((int) $paquete["id"], $seleccionados ?? [], true) ? "checked" : "" ?> style="width:22px;height:22px;cursor:pointer;">
              <div><div class="nombreCliente">👤 <?= $h($paquete["cliente"]) ?></div><div class="detalle">📦 <?= $h($paquete["observaciones"]) ?></div></div>
            </div>
            <div class="codigoCaja"><?= $h($etiqueta) ?></div>
          </div>
          <div class="contenidoPaquete">
            <div class="datosExtra">
              🏷️ <b>Tipo:</b> <?= $h($tipo) ?>
              <?php if (strtolower($tipo) === "tiktok"): ?><br>🏢 <b>Empresa:</b> <?= $h($paquete["empresa"]) ?><?php if (!empty($paquete["nota_caja"])): ?><br>📝 <b>Nota:</b> <?= $h($paquete["nota_caja"]) ?><?php endif; ?><?php endif; ?>
              <br>📱 <b>Celular:</b> <?= $h($paquete["celular"] ?: "Sin registrar") ?><br>📅 <b>Recepción:</b> <?= date("d/m/Y H:i", strtotime($paquete["fecha_registro"])) ?><br>🟡 <b>Estado:</b> Pendiente
            </div>
            <?php if (date("Y-m-d", strtotime($paquete["fecha_registro"])) === date("Y-m-d")): ?><div class="etiquetaHoy">HOY</div><?php endif; ?>
            <div class="fotoPaquete"><?php if ($foto): ?><img src="<?= $h($foto) ?>" alt="Foto del paquete" style="width:100%;height:100%;object-fit:cover;border-radius:10px;"><?php else: ?>📷 Foto pendiente<?php endif; ?></div>
          </div>
          <div style="margin-top:20px;"><a href="<?= $base_url ?>entrega/detalle?id=<?= (int) $paquete["id"] ?>"><button type="button" class="btnEntregar">✅ ENTREGAR</button></a></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div id="panelSeleccion">
      <h3>📦 Seleccionados: <span id="cantidadSeleccionados">0</span></h3><div id="listaSeleccionados"></div><hr>
      <div style="display:flex;justify-content:space-between;margin-bottom:10px;"><span>Subtotal:</span><b><span id="subtotalCobro">0</span> Bs</b></div>
      <label style="display:block;font-weight:bold;margin-bottom:6px;">💸 Descuento general:</label><input type="number" id="descuentoGeneral" value="0" min="0" step="0.50" style="width:100%;box-sizing:border-box;padding:10px;font-size:16px;border:1px solid #ccc;border-radius:8px;">
      <hr><div style="display:flex;justify-content:space-between;font-size:20px;margin-top:12px;"><span>💰 Total a cobrar:</span><b style="color:#16a34a;"><span id="totalCobro">0</span> Bs</b></div>
      <div style="margin-top:15px;"><button id="btnEfectivoMultiple" type="button" class="btn-multiple efectivo">💵 COBRAR Y ENTREGAR<br><span>EFECTIVO — <span id="montoEfectivoMultiple">0.00</span> Bs</span></button><button id="btnQRMultiple" type="button" class="btn-multiple qr">📱 COBRAR Y ENTREGAR<br><span>QR — <span id="montoQRMultiple">0.00</span> Bs</span></button></div>
    </div>
  </div>
</div>
<div id="modalQRMultiple" class="modal-entrega"><div class="modal-entrega-contenido"><h2>📱 Verificar cobro QR</h2><p>Confirma que verificaste correctamente el ingreso de:</p><div id="montoModalQRMultiple" class="monto-modal">0.00 Bs</div><p class="advertenciaQR">⚠️ No confirmes si el pago todavía no aparece en la cuenta.</p><div class="botonesModalQR"><button type="button" onclick="cerrarModalQRMultiple()">Cancelar</button><button type="button" id="btnConfirmarQRMultiple">✅ COBRO VERIFICADO</button></div></div></div>
<script>window.entregaMultipleUrl = <?= json_encode($base_url . "entrega/multiple") ?>; window.entregaCsrfToken = <?= json_encode($csrfToken) ?>;</script>
<script src="<?= $base_url ?>assets/js/modules/entrega/entregar.js"></script>
