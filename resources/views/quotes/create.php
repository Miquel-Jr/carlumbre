<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Nuevo Presupuesto | Carlumbre</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php view('partials/menu'); ?>

    <div class="container mt-5">

        <h2 class="mb-4">Crear Presupuesto</h2>

        <form action="/quotes/store" method="POST">

            <!-- Cliente -->
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    Información General
                </div>
                <div class="card-body">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Cliente *</label>
                            <select name="client_id" id="clientSelect" class="form-select" required>
                                <option value="">Seleccionar cliente</option>
                                <?php foreach ($clients as $client): ?>
                                <option value="<?= $client['id'] ?>">
                                    <?= htmlspecialchars($client['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Auto *</label>
                            <select name="car_id" id="carSelect" class="form-select" required>
                                <option value="">Seleccionar auto</option>
                                <?php foreach ($cars as $car): ?>
                                <option value="<?= $car['id'] ?>" data-client="<?= $car['client_id'] ?>">
                                    <?= $car['brand'] ?> <?= $car['model'] ?> - <?= $car['plate'] ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div id="carGalleryContainer">
                                    <div class="alert alert-secondary">
                                        Selecciona un auto para ver su galería.
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notas</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>

                </div>
            </div>

            <!-- Servicios -->
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    Servicios
                </div>
                <div class="card-body">

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <select id="serviceSelect" class="form-select">
                                <option value="">Seleccionar servicio</option>
                                <?php foreach ($services as $service): ?>
                                <option value="<?= $service['id'] ?>"
                                    data-name="<?= htmlspecialchars($service['name']) ?>"
                                    data-price="<?= $service['price'] ?>">
                                    <?= $service['name'] ?> - S/ <?= number_format($service['price'], 2) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <input type="number" id="quantity" class="form-control" value="1" min="1">
                        </div>

                        <div class="col-md-2">
                            <input type="number" id="priceInput" class="form-control" placeholder="Precio" step="0.01"
                                min="0">
                        </div>

                        <div class="col-md-2">
                            <button type="button" class="btn btn-success w-100" onclick="addService()">
                                Agregar
                            </button>
                        </div>
                    </div>


                    <table class="table table-bordered" id="servicesTable">
                        <thead class="table-light">
                            <tr>
                                <th>Servicio</th>
                                <th>Cantidad</th>
                                <th>Precio</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                    <div class="text-end">
                        <h4>Total: S/ <span id="totalAmount">0.00</span></h4>
                    </div>

                    <input type="hidden" name="total" id="totalInput">

                </div>
            </div>


            <div class="text-end">
                <a href="/quotes" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-warning">Guardar Presupuesto</button>
            </div>

        </form>

    </div>


</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php view('partials/sweetalert'); ?>
<script>
const carPhotos = <?= json_encode($photos) ?>;
let total = 0;

function addService() {

    const select = document.getElementById("serviceSelect");
    const quantity = parseInt(document.getElementById("quantity").value);
    const price = parseFloat(document.getElementById("priceInput").value);

    if (!select.value || !quantity || !price) {
        alert("Completa servicio, cantidad y precio.");
        return;
    }

    const name = select.options[select.selectedIndex].dataset.name;
    const subtotal = quantity * price;

    total += subtotal;
    updateTotal();

    const row = `
        <tr>
            <td>
                ${name}
                <input type="hidden" name="services[]" value="${select.value}">
                <input type="hidden" name="descriptions[]" value="${name}">
            </td>
            <td>
                ${quantity}
                <input type="hidden" name="quantities[]" value="${quantity}">
            </td>
            <td>
                S/ ${price.toFixed(2)}
                <input type="hidden" name="prices[]" value="${price}">
            </td>
            <td>S/ ${subtotal.toFixed(2)}</td>
            <td>
                <button type="button" 
                        class="btn btn-sm btn-danger"
                        onclick="removeRow(this, ${subtotal})">
                        X
                </button>
            </td>
        </tr>`;

    document.querySelector("#servicesTable tbody")
        .insertAdjacentHTML("beforeend", row);

    // Reset campos
    select.value = "";
    document.getElementById("quantity").value = 1;
    document.getElementById("priceInput").value = "";
}


function removeRow(button, subtotal) {
    button.closest("tr").remove();
    total -= subtotal;
    updateTotal();
}

function updateTotal() {
    document.getElementById("totalAmount").innerText = total.toFixed(2);
    document.getElementById("totalInput").value = total.toFixed(2);
}


// Filtrar autos por cliente
document.getElementById("clientSelect").addEventListener("change", function() {

    const clientId = this.value;
    const carSelect = document.getElementById("carSelect");

    for (let option of carSelect.options) {
        if (!option.dataset.client) continue;

        option.style.display = option.dataset.client === clientId ? "block" : "none";
    }

    carSelect.value = "";
});

document.getElementById("carSelect").addEventListener("change", function() {

    const carId = this.value;
    const container = document.getElementById("carGalleryContainer");

    container.innerHTML = "";

    const photos = carPhotos.filter(photo => photo.car_id == carId);

    if (photos.length === 0) {
        container.innerHTML = `
            <div class="alert alert-warning">
                Este auto no tiene fotos registradas.
            </div>
        `;
        return;
    }

    let indicators = "";
    let items = "";

    photos.forEach((photo, index) => {

        indicators += `
            <button type="button" 
                data-bs-target="#carCarousel" 
                data-bs-slide-to="${index}" 
                ${index === 0 ? 'class="active"' : ''}>
            </button>
        `;

        items += `
            <div class="carousel-item ${index === 0 ? 'active' : ''}">
                <img src="${photo.photo_path}" 
                     class="d-block w-100 rounded shadow"
                     style="height:400px; object-fit:cover;">
            </div>
        `;
    });

    container.innerHTML = `
        <div id="carCarousel" class="carousel slide" data-bs-ride="carousel">

            <div class="carousel-indicators">
                ${indicators}
            </div>

            <div class="carousel-inner">
                ${items}
            </div>

            <button class="carousel-control-prev" 
                    type="button" 
                    data-bs-target="#carCarousel" 
                    data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>

            <button class="carousel-control-next" 
                    type="button" 
                    data-bs-target="#carCarousel" 
                    data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>

        </div>
    `;
});

document.getElementById("serviceSelect").addEventListener("change", function() {

    const selectedOption = this.options[this.selectedIndex];

    if (!this.value) {
        document.getElementById("priceInput").value = "";
        return;
    }

    const basePrice = selectedOption.dataset.price;
    document.getElementById("priceInput").value = parseFloat(basePrice).toFixed(2);
});
</script>

</html>