<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Presupuesto #<?= str_pad($quote['id'], 8, '0', STR_PAD_LEFT) ?> - Carlumbre</title>
  <style>
  @page {
    margin: 8mm;
  }

  body {
    font-family: Helvetica, DejaVu Sans, Arial, sans-serif;
    font-size: 11px;
    color: #25303b;
    line-height: 1.45;
    margin: 0px;
    background: #eef2f5;
  }

  .page-content {
    margin: 6px 8px;
    padding: 12px 14px 10px 14px;
    background: rgba(255, 255, 255, 0.82);
    border: 1px solid #d7dde3;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
  }

  .bg-shape {
    position: fixed;
    width: 0;
    height: 0;
    z-index: -2;
  }

  .bg-top-right-black {
    top: 0;
    right: 0;
    border-top: 84px solid #1c2732;
    border-left: 130px solid transparent;
  }

  .bg-top-right-red-main {
    top: 0;
    right: 0;
    border-top: 110px solid #da4b49;
    border-left: 180px solid transparent;
  }

  .bg-top-right-red-strong {
    top: 0;
    right: 0;
    border-top: 46px solid #bf1e2d;
    border-left: 70px solid transparent;
  }

  .bg-top-right-dark-overlay {
    top: 68px;
    right: 0;
    border-top: 72px solid rgba(28, 39, 50, 0.55);
    border-left: 74px solid transparent;
  }

  .bg-bottom-left-black {
    bottom: 0;
    left: 0;
    border-bottom: 94px solid #1c2732;
    border-right: 138px solid transparent;
  }

  .bg-bottom-left-red-main {
    bottom: 0;
    left: 0;
    border-bottom: 124px solid #da4b49;
    border-right: 176px solid transparent;
  }

  .bg-bottom-left-red-strong {
    bottom: 0;
    left: 0;
    border-bottom: 50px solid #bf1e2d;
    border-right: 78px solid transparent;
  }

  .bg-bottom-left-dark-overlay {
    bottom: 36px;
    left: 0;
    border-bottom: 64px solid rgba(28, 39, 50, 0.52);
    border-right: 64px solid transparent;
  }

  .header {
    width: 100%;
    margin-bottom: 8px;
  }

  .header-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 0;
  }

  .header-table td {
    vertical-align: middle;
    border: none;
    padding: 0;
  }

  .header-logo {
    width: 140px;
  }

  .logo {
    width: 184px;
  }

  .brand-lockup {
    width: 100%;
  }

  .brand-table {
    width: auto;
    border-collapse: collapse;
    margin: 0;
  }

  .brand-table td {
    border: none;
    padding: 0;
    vertical-align: middle;
  }

  .brand-logo-cell {
    width: 184px;
    padding-right: 8px;
  }

  .brand-text-cell {
    text-align: left;
    color: #2f3944;
  }

  .brand-line-1 {
    font-size: 31px;
    font-weight: 800;
    line-height: 1;
    letter-spacing: 0.45px;
  }

  .brand-line-2 {
    font-size: 13px;
    font-weight: 800;
    margin-top: 2px;
    letter-spacing: 0.35px;
  }

  .brand-line-3 {
    font-size: 10px;
    font-weight: 500;
    margin-top: 3px;
    letter-spacing: 0.2px;
    color: #5c6670;
  }

  .brand-slogan {
    margin-top: 6px;
    font-size: 10px;
    font-weight: 700;
    color: #4d5863;
    letter-spacing: 0.38px;
    text-transform: uppercase;
  }

  .company-info {
    text-align: right;
    font-size: 12px;
  }

  .separator {
    border-bottom: 1px solid #2f3944;
    margin-top: 7px;
    margin-bottom: 13px;
  }

  .section-title {
    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #202a33;
    margin-bottom: 7px;
  }

  .info-box {
    margin-bottom: 16px;
    font-size: 11px;
  }

  .doc-number {
    font-size: 14px;
    font-weight: 800;
    text-transform: none;
    letter-spacing: 0.2px;
  }

  .meta-row {
    margin-top: 4px;
    color: #3a3f45;
  }

  .meta-label {
    font-weight: 700;
    margin-right: 4px;
  }

  .client-reference {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 14px;
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid #d4dbe2;
  }

  .client-reference td {
    vertical-align: top;
    border: none;
    padding: 10px;
  }

  .client-col {
    width: 70%;
    padding-right: 16px;
  }

  .reference-col {
    width: 30%;
    text-align: right;
  }

  .client-line {
    margin-bottom: 4px;
  }

  .field-label {
    font-weight: 700;
    color: #1f2429;
  }

  .field-value {
    color: #33383e;
  }

  .reference-image {
    width: 150px;
    height: 105px;
    object-fit: cover;
    opacity: 0.84;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 7px;
  }

  th {
    background: #27323d;
    color: #fff;
    padding: 7px 9px;
    font-size: 11px;
    text-transform: uppercase;
  }

  tbody tr:nth-child(even) {
    background: #f5f7fa;
  }

  td {
    border-bottom: 1px solid #dce2e8;
    padding: 7px 8px;
    font-size: 11px;
  }

  .total-box {
    margin-top: 14px;
    text-align: right;
    font-size: 15px;
    font-weight: 900;
    color: #000;
    letter-spacing: 0.3px;
  }

  .footer {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    font-size: 9px;
    text-align: center;
    color: #000;
    border-top: 1px solid #d2d9e0;
    padding-top: 5px;
  }

  .footer a {
    color: #000;
    text-decoration: none;
    font-weight: bold;
  }

  .footer a:hover {
    text-decoration: underline;
  }

  .watermark-frame {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    overflow: hidden;
    z-index: -1;
  }

  .watermark {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 780px;
    mix-blend-mode: screen;
    opacity: 0.35;
    margin-top: -250px;
    margin-left: -390px;
  }
  </style>
</head>

<body>

  <!-- Fondo decorativo -->
  <div class="bg-shape bg-top-right-red-main"></div>
  <div class="bg-shape bg-top-right-red-strong"></div>
  <div class="bg-shape bg-top-right-black"></div>
  <div class="bg-shape bg-top-right-dark-overlay"></div>

  <div class="bg-shape bg-bottom-left-red-main"></div>
  <div class="bg-shape bg-bottom-left-red-strong"></div>
  <div class="bg-shape bg-bottom-left-black"></div>
  <div class="bg-shape bg-bottom-left-dark-overlay"></div>

  <!-- Marca de agua -->
  <div class="watermark-frame">
    <img src="<?= $logoBase64 ?>" alt="Carlumbre Watermark" class="watermark">
  </div>

  <div class="page-content">

    <br>
    <br>
    <br>
    <!-- HEADER -->
    <div class="header">
      <table class="header-table">
        <tr>
          <td class="header-logo">
            <div class="brand-lockup">
              <table class="brand-table">
                <tr>
                  <td class="brand-logo-cell">
                    <img src="<?= $carPng ?>" alt="Carlumbre Logo" class="logo">
                  </td>
                  <td class="brand-text-cell">
                    <div class="brand-line-1">LUMBRE</div>
                    <div class="brand-line-2">MECANICA AUTOMOTRIZ</div>
                    <div class="brand-line-3">Av. Lorenzo de Encalada 384, Rimac - Lima</div>
                  </td>
                </tr>
              </table>
              <div class="brand-slogan">Impulsando tu confianza en cada reparacion</div>
            </div>
          </td>
        </tr>
      </table>
    </div>

    <div class="separator"></div>

    <br>

    <!-- DATOS DEL DOCUMENTO -->
    <div class="info-box">
      <div class="section-title doc-number">Presupuesto #<?= str_pad($quote['id'], 8, '0', STR_PAD_LEFT) ?></div>
      <div class="meta-row">
        <span class="meta-label">Fecha:</span>
        <span><?= date('d/m/Y', strtotime($quote['created_at'])) ?></span>
      </div>
    </div>

    <?php
        $firstReferenceImage = null;
        foreach ($items as $item) {
            if (!empty($item['reference_image_url'])) {
                $firstReferenceImage = $item['reference_image_url'];
                break;
            }
        }
        
        if ($quote['document_type'] === '1') {
            $documentTypeLabel = 'DNI';
        } elseif ($quote['document_type'] === '2') {
            $documentTypeLabel = 'RUC';
        } else {
            $documentTypeLabel = 'PASAPORTE';
        }
    ?>

    <table class="client-reference">
      <tbody>
        <tr>
          <td class="client-col">
            <div class="section-title">Datos del Cliente</div>
            <div class="client-line"><span class="field-label">Nombre:</span>
              <span class="field-value"><?= $quote['client_name'] ?></span>
            </div>
            <div class="client-line"><span class="field-label"><?= $documentTypeLabel ?>:</span>
              <span class="field-value"><?= $quote['document_number'] ?></span>
            </div>
            <div class="client-line"><span class="field-label">Email:</span>
              <span class="field-value"><?= $quote['email'] ?></span>
            </div>
            <div class="client-line"><span class="field-label">Teléfono:</span>
              <span class="field-value"><?= $quote['phone'] ?></span>
            </div>
            <div class="client-line"><span class="field-label">Dirección:</span>
              <span class="field-value"><?= $quote['address'] ?></span>
            </div>
            <div class="client-line"><span class="field-label">Vehículo:</span>
              <span class="field-value"><?= $quote['brand'] ?> <?= $quote['model'] ?> - <?= $quote['plate'] ?></span>
            </div>
          </td>
          <td class="reference-col">
            <?php if (!empty($firstReferenceImage)): ?>
            <img src="<?= htmlspecialchars($firstReferenceImage) ?>" alt="Referencia" class="reference-image">
            <?php endif; ?>
          </td>
        </tr>
      </tbody>
    </table>

    <table>
      <thead>
        <tr>
          <th>Descripción</th>
          <th>Cantidad</th>
          <th>Precio</th>
          <th>Subtotal</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $item): ?>
        <tr>
          <td><?= $item['description'] ?></td>
          <td><?= $item['quantity'] ?></td>
          <td>S/ <?= number_format($item['price'], 2) ?></td>
          <td>S/ <?= number_format($item['subtotal'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="total-box">
      TOTAL: S/ <?= number_format($quote['total'], 2) ?>
    </div>

    <?php
    $warrantyItems = array_values(array_filter($items, static function ($item) {
        return (int) ($item['has_warranty'] ?? 0) === 1;
    }));
    ?>

    <div style="margin-top: 26px;">
      <div class="section-title">Garantías aplicables</div>

      <?php if (!empty($warrantyItems)): ?>
      <div
        style="border: 1px solid #d2d6db; border-radius: 4px; padding: 10px; background: rgba(255, 255, 255, 0.58); font-size: 11px;">
        <?php foreach ($warrantyItems as $warrantyItem): ?>
        <div style="margin-bottom: 7px;">
          <strong><?= htmlspecialchars($warrantyItem['description']) ?></strong><br>
          Tiempo de garantía: <?= (int) max(1, (int) ($warrantyItem['warranty_time_base'] ?? 1)) ?> meses.
        </div>
        <?php endforeach; ?>
        <div style="margin-top: 7px; font-size: 10px; color: #383d43;">
          <strong>Condición de vigencia:</strong> La garantía inicia desde la entrega del vehículo al cliente.
        </div>
      </div>
      <?php else: ?>
      <div style="font-size: 10px; color: #41464d;">Este presupuesto no incluye servicios con garantía.</div>
      <?php endif; ?>
    </div>

    <div style="margin-top: 28px; font-size: 11px;">
      <strong style="font-weight: 800;">Notas:</strong><br>
      <?= $quote['notes'] ?? 'Sin observaciones.' ?>
    </div>

    <div style="margin-top: 28px; font-size: 10px; color: #474c52;">
      <em>Este presupuesto tiene una validez de 7 días.</em>
    </div>
  </div>

  <div class="footer">

    <div style="margin-bottom:6px;">
      <strong>Síguenos:</strong>
      <a href="https://www.facebook.com/CARLUMBRE" target="_blank">
        <img src="<?= $facebookIcon ?>" alt="Facebook" width="16" style="vertical-align: middle;">
      </a> ·

      <a href="https://www.instagram.com/carlumbreautomotriz" target="_blank">
        <img src="<?= $instagramIcon ?>" alt="Instagram" width="16" style="vertical-align: middle;">
      </a> ·

      <a href="https://wa.me/+51979701851" target="_blank">
        <img src="<?= $whatsappIcon ?>" alt="WhatsApp" width="16" style="vertical-align: middle;">
      </a>
    </div>

    <div>
      Mecanica Automotriz Carlumbre · Todos los derechos reservados · <?= date('Y') ?>
    </div>

  </div>

</body>

</html>
