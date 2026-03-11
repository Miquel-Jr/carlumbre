<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Presupuesto #<?= str_pad($quote['id'], 8, '0', STR_PAD_LEFT) ?> - Carlumbre</title>
  <style>
  body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 12px;
    color: #333;
    margin: 0px;
    background: #f7f7f7;
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
    border-top: 92px solid #121820;
    border-left: 130px solid transparent;
  }

  .bg-top-right-red-main {
    top: 0;
    right: 0;
    border-top: 128px solid #ef5a57;
    border-left: 180px solid transparent;
  }

  .bg-top-right-red-strong {
    top: 0;
    right: 0;
    border-top: 58px solid #cf0f22;
    border-left: 70px solid transparent;
  }

  .bg-top-right-dark-overlay {
    top: 78px;
    right: 0;
    border-top: 88px solid rgba(18, 24, 32, 0.7);
    border-left: 74px solid transparent;
  }

  .bg-bottom-left-black {
    bottom: 0;
    left: 0;
    border-bottom: 108px solid #121820;
    border-right: 138px solid transparent;
  }

  .bg-bottom-left-red-main {
    bottom: 0;
    left: 0;
    border-bottom: 146px solid #ef5a57;
    border-right: 176px solid transparent;
  }

  .bg-bottom-left-red-strong {
    bottom: 0;
    left: 0;
    border-bottom: 62px solid #cf0f22;
    border-right: 78px solid transparent;
  }

  .bg-bottom-left-dark-overlay {
    bottom: 44px;
    left: 0;
    border-bottom: 80px solid rgba(18, 24, 32, 0.72);
    border-right: 64px solid transparent;
  }

  .header {
    width: 100%;
    margin-bottom: 14px;
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
    width: 200px;
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
    width: 200px;
    padding-right: 10px;
  }

  .brand-text-cell {
    text-align: left;
    color: #44484d;
  }

  .brand-line-1 {
    font-size: 35px;
    font-weight: 900;
    line-height: 0.9;
    letter-spacing: 1px;
  }

  .brand-line-2 {
    font-size: 18px;
    font-weight: 800;
    margin-top: 4px;
    letter-spacing: 0.5px;
  }

  .brand-slogan {
    margin-top: 8px;
    font-size: 12px;
    font-weight: 700;
    color: #44484d;
    letter-spacing: 0.4px;
    text-transform: uppercase;
  }

  .company-info {
    text-align: right;
    font-size: 12px;
  }

  .separator {
    border-bottom: 2px solid #000;
    margin-top: 10px;
    margin-bottom: 20px;
  }

  .section-title {
    font-size: 14px;
    font-weight: bold;
    margin-bottom: 8px;
  }

  .info-box {
    margin-bottom: 20px;
  }

  .client-reference {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
  }

  .client-reference td {
    vertical-align: top;
    border: none;
    padding: 0;
  }

  .client-col {
    width: 70%;
    padding-right: 16px;
  }

  .reference-col {
    width: 30%;
    text-align: right;
  }

  .reference-image {
    width: 150px;
    height: 105px;
    object-fit: cover;
    opacity: 0.72;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
  }

  th {
    background-color: #111;
    color: #fff;
    padding: 8px;
    font-size: 12px;
  }

  td {
    border-bottom: 1px solid #ddd;
    padding: 8px;
  }

  .total-box {
    margin-top: 20px;
    text-align: right;
    font-size: 16px;
    font-weight: bold;
  }

  .footer {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    font-size: 10px;
    text-align: center;
    color: #666;
    border-top: 1px solid #ccc;
    padding-top: 6px;
  }

  .footer a {
    color: #000;
    text-decoration: none;
    font-weight: bold;
  }

  .footer a:hover {
    text-decoration: underline;
  }

  .watermark {
    position: fixed;
    top: 15%;
    left: 0%;
    width: 700px;
    opacity: 0.09;
    z-index: -1;
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
  <img src="<?= $logoBase64 ?>" alt="Carlumbre Watermark" class="watermark">

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
                  <div class="brand-line-2">Av. lorenzo de encalada 384, Rimac - Lima</div>
                </td>
              </tr>
            </table>
            <div class="brand-slogan">!IMPULSANDO TU CONFIANZA EN CADA REPARACION !</div>
          </div>
        </td>
      </tr>
    </table>
  </div>

  <div class="separator"></div>

  <!-- DATOS DEL DOCUMENTO -->
  <div class="info-box">
    <div class="section-title">Presupuesto #<?= str_pad($quote['id'], 8, '0', STR_PAD_LEFT) ?></div>
    Fecha: <?= date('d/m/Y', strtotime($quote['created_at'])) ?><br>
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
          Nombre: <?= $quote['client_name'] ?><br>
          <?= $documentTypeLabel ?>:
          <?= $quote['document_number'] ?><br>
          Email: <?= $quote['email'] ?><br>
          Teléfono: <?= $quote['phone'] ?><br>
          Dirección: <?= $quote['address'] ?><br>
          Vehículo: <?= $quote['brand'] ?> <?= $quote['model'] ?> - <?= $quote['plate'] ?>
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

  <div style="margin-top: 30px;">
    <div class="section-title">Garantías aplicables</div>

    <?php if (!empty($warrantyItems)): ?>
    <div style="border: none; border-radius: 6px; padding: 12px; background: rgba(255, 255, 255, 0.55);">
      <?php foreach ($warrantyItems as $warrantyItem): ?>
      <div style="margin-bottom: 8px;">
        <strong><?= htmlspecialchars($warrantyItem['description']) ?></strong><br>
        Tiempo de garantía: <?= (int) max(1, (int) ($warrantyItem['warranty_time_base'] ?? 1)) ?> meses.
      </div>
      <?php endforeach; ?>
      <div style="margin-top: 8px; font-size: 11px;">
        <strong>Condición de vigencia:</strong> La garantía inicia desde la entrega del vehículo al cliente.
      </div>
    </div>
    <?php else: ?>
    <div style="font-size: 11px;">Este presupuesto no incluye servicios con garantía.</div>
    <?php endif; ?>
  </div>

  <div style="margin-top:40px;">
    <strong>Notas:</strong><br>
    <?= $quote['notes'] ?? 'Sin observaciones.' ?>
  </div>

  <div style="margin-top:40px;">
    <em>Este presupuesto tiene una validez de 7 días.</em>
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
