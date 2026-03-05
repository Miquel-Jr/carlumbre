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
  }

  .header {
    width: 100%;
    margin-bottom: 20px;
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
    width: 140px;
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
    top: 35%;
    left: 20%;
    width: 400px;
    opacity: 0.05;
    z-index: -1;
  }
  </style>
</head>

<body>

  <!-- Marca de agua -->
  <img src="<?= $logoBase64 ?>" alt="Carlumbre Watermark" class="watermark">

  <!-- HEADER -->
  <div class="header">
    <table class="header-table">
      <tr>
        <td class="header-logo">
          <img src="<?= $logoBase64 ?>" alt="Carlumbre Logo" class="logo">
        </td>
        <td class="company-info">
          <strong>MECANICA AUTOMOTRIZ CARLUMBRE</strong><br>
          Dirección: Av. lorenzo de encalada 384, Rimac - Lima<br>
          Tel: 979 701 851<br>
          Email: lumbrecar@gmail.com<br>
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
