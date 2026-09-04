<?php if (isset($errorVista)): ?>
  <div class="alerta-error" role="alert"><?= htmlspecialchars($errorVista, ENT_QUOTES, "UTF-8") ?></div>
<?php else: ?>
  <?php
  $escapar = static fn($valor) => htmlspecialchars((string) ($valor ?? ""), ENT_QUOTES, "UTF-8");
  $totalPaquetes = count($paquetes);
  ?>
  <div class="contenedor">
    <div class="card">
      <div class="cabecera-comprobante">
        <div>
          <h1>📦 TU MERCA ENCOMIENDAS</h1>
          <p>Comprobante de recepción</p>
        </div>
        <strong><?= $escapar($recepcion["codigo"]) ?></strong>
      </div>

      <div class="info">
        <div><b>Cliente:</b> <?= $escapar($recepcion["nombre_cliente"]) ?></div>
        <div><b>Tipo de recepción:</b> <?= $escapar($recepcion["tipo_recepcion"]) ?></div>
        <?php if (!empty($recepcion["empresa"])): ?>
          <div><b>Empresa o tienda:</b> <?= $escapar($recepcion["empresa"]) ?></div>
        <?php endif; ?>
        <div><b>Almacén:</b> <?= $escapar($recepcion["nombre_almacen"]) ?></div>
        <div><b>Fecha:</b> <?= $escapar(date("d/m/Y H:i", strtotime($recepcion["fecha_registro"]))) ?></div>
        <div><b>Total de encomiendas:</b> <?= $totalPaquetes ?></div>
        <div><b>Estado:</b> <?= $escapar($recepcion["estado"]) ?></div>
        <?php if (!empty($recepcion["observaciones"])): ?>
          <div><b>Observaciones:</b> <?= $escapar($recepcion["observaciones"]) ?></div>
        <?php endif; ?>
      </div>

      <hr>

      <?php foreach ($paquetes as $indice => $paquete): ?>
        <?php $pendiente = $paquete["estado"] === "Pendiente"; ?>
        <article class="paquete-comprobante <?= $pendiente ? "pendiente" : "entregado" ?>">
          <h2><?= $pendiente ? "🟡" : "✅" ?> Encomienda <?= $indice + 1 ?></h2>
          <div class="datos-paquete">
            <div><b>Código:</b> <?= $escapar($paquete["codigo"]) ?></div>
            <div><b>Destinatario:</b> <?= $escapar($paquete["destinatario"]) ?></div>
            <div><b>Contacto:</b> <?= $escapar($paquete["contacto"]) ?></div>
            <div><b>Clasificación:</b> <?= $escapar($paquete["clasificacion"]) ?></div>
            <div><b>Descripción:</b> <?= $escapar($paquete["descripcion"]) ?></div>
            <div><b>Precio:</b> Bs <?= number_format((float) $paquete["precio"], 2) ?></div>
            <div><b>Paga:</b> <?= $escapar($paquete["quien_paga"]) ?></div>
            <div><b>Estado:</b> <?= $escapar($paquete["estado"]) ?></div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>
