

<?php
session_start();

include('../php/conexion.php');

// Obtener productos para el <select>
$productos_result = $conexion->query("SELECT id_producto, nombre, precio, stock FROM productos");
$productos = [];
while ($row = $productos_result->fetch_assoc()) {
  $productos[] = $row;
}

// Variables
$nombre   = trim($_POST['nombre']   ?? '');
$id_venta   = trim($_POST['id_venta']   ?? '');
$id_producto = trim($_POST['id_producto'] ?? '');
$cantidad   = trim($_POST['cantidad']   ?? '');
$subtotal   = trim($_POST['subtotal']   ?? '');
$iva        = trim($_POST['IVA']        ?? '');
$total      = trim($_POST['total']      ?? '');
$accion     = $_POST['accion']         ?? '';
$mensaje    = "";

// Funciones para validar campos
function camposCompletos(...$campos) {
  foreach ($campos as $campo) {
    if ($campo === '') return false;
  }
  return true;
}
function camposNumericosValidos(...$campos) {
  foreach ($campos as $campo) {
    if (!is_numeric($campo)) return false;
  }
  return true;
}

// INSERTAR
if ($accion == "INSERTAR") {
  if (camposCompletos($id_venta, $id_producto, $cantidad, $subtotal, $iva, $total)) {
    if (!camposNumericosValidos($id_venta, $id_producto, $cantidad, $subtotal, $iva, $total) || $cantidad <= 0) {
      $mensaje = "Todos los campos deben ser números válidos. La cantidad debe ser mayor que cero.";
    } else {
      $check = $conexion->query("SELECT stock FROM productos WHERE id_producto='$id_producto'");
      if ($check && $fila = $check->fetch_assoc()) {
        if ($fila['stock'] >= $cantidad) {
          $sql = "INSERT INTO ventas (id_venta, id_producto, cantidad, subtotal, IVA, total) 
                  VALUES ('$id_venta', '$id_producto', '$cantidad', '$subtotal', '$iva', '$total')";
          $conexion->query($sql);
          $nuevoStock = $fila['stock'] - $cantidad;
          $conexion->query("UPDATE productos SET stock='$nuevoStock' WHERE id_producto='$id_producto'");
          $mensaje = "✅ Venta registrada y stock actualizado.";
          $id_venta = $id_producto = $cantidad = $subtotal = $iva = $total = '';
        } else {
          $mensaje = "No hay suficiente stock disponible. Stock actual: " . $fila['stock'];
        }
      } else {
        $mensaje = "Producto no encontrado para validar stock.";
      }
    }
  } else {
    $mensaje = "❗ Por favor llena todos los campos antes de insertar.";
  }
}

// EDITAR
if ($accion == "EDITAR") {
  if (!isset($_SESSION['puede_editar']) || $_SESSION['puede_editar'] !== true) {
    $mensaje = "Primero debes buscar la venta antes de editar.";
  } elseif (camposCompletos($id_venta, $id_producto, $cantidad, $subtotal, $iva, $total)) {
    if (!camposNumericosValidos($id_venta, $id_producto, $cantidad, $subtotal, $iva, $total) || $cantidad <= 0) {
      $mensaje = "Todos los campos deben ser números válidos. La cantidad debe ser mayor que cero.";
    } else {
      $prev = $conexion->query("SELECT cantidad FROM ventas WHERE id_venta='$id_venta'");
      $prev_cantidad = ($prev && $r = $prev->fetch_assoc()) ? $r['cantidad'] : 0;
      $stock_res = $conexion->query("SELECT stock FROM productos WHERE id_producto='$id_producto'");
      if ($stock_res && $sr = $stock_res->fetch_assoc()) {
        $stock_actual = $sr['stock'];
        $diferencia = $cantidad - $prev_cantidad;
        if ($stock_actual >= $diferencia) {
          $sql = "UPDATE ventas 
                  SET id_producto='$id_producto', cantidad='$cantidad', subtotal='$subtotal', IVA='$iva', total='$total' 
                  WHERE id_venta='$id_venta'";
          $conexion->query($sql);
          $nuevoStock = $stock_actual - $diferencia;
          $conexion->query("UPDATE productos SET stock='$nuevoStock' WHERE id_producto='$id_producto'");
          $mensaje = "✅ Venta actualizada .";
          $id_venta = $id_producto = $cantidad = $subtotal = $iva = $total = '';
          unset($_SESSION['puede_editar']);
        } else {
          $mensaje = "No hay suficiente stock para actualizar la venta. Stock actual: $stock_actual";
        }
      } else {
        $mensaje = "No se pudo verificar el stock actual.";
      }
    }
  } else {
    $mensaje = "❗ Por favor llena todos los campos antes de editar.";
  }
}

// ELIMINAR
if ($accion == "ELIMINAR") {
  if ($id_venta !== '') {
    $res = $conexion->query("SELECT id_producto, cantidad FROM ventas WHERE id_venta='$id_venta'");
    if ($res && $r = $res->fetch_assoc()) {
      $id_p = $r['id_producto'];
      $cant = $r['cantidad'];

      // Verificamos confirmación
      $confirmacion = $_POST['confirmacion'] ?? '';

      if ($confirmacion !== 'SI') {
        echo '
        <div style="
          background-color: #121212; 
          color: #FFDD00; 
          font-family: Arial, sans-serif; 
          padding: 30px; 
          max-width: 400px; 
          margin: 100px auto; 
          border-radius: 10px; 
          box-shadow: 0 0 15px #FFDD00;
          text-align: center;
        ">
          <h2 style="margin-bottom: 20px;">⚠ Confirmar eliminación</h2>
          <p style="font-size: 18px; margin-bottom: 30px;">
            ¿Estás seguro que deseas eliminar la venta con ID <strong>' . htmlspecialchars($id_venta) . '</strong>?
          </p>
          <form method="POST" style="display: inline-block;">
            <input type="hidden" name="id_venta" value="' . htmlspecialchars($id_venta) . '">
            <input type="hidden" name="accion" value="ELIMINAR">
            <input type="hidden" name="confirmacion" value="SI">
            <button type="submit" style="
              background-color: #FFDD00; 
              border: none; 
              color: #121212; 
              font-weight: bold; 
              padding: 10px 25px; 
              margin-right: 15px; 
              border-radius: 5px; 
              cursor: pointer;
              font-size: 16px;
              transition: background-color 0.3s;
            " onmouseover="this.style.backgroundColor=\'#e6c900\'" onmouseout="this.style.backgroundColor=\'#FFDD00\'">
              Confirmar
            </button>
          </form>
          <button onclick="window.location.href=\'../php/ventas.php\'" style="
            background-color: transparent; 
            border: 2px solid #FFDD00; 
            color: #FFDD00; 
            font-weight: bold; 
            padding: 10px 25px; 
            border-radius: 5px; 
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s, color 0.3s;
          " onmouseover="this.style.backgroundColor=\'#FFDD00\'; this.style.color=\'#121212\'" onmouseout="this.style.backgroundColor=\'transparent\'; this.style.color=\'#FFDD00\'">
            Cancelar
          </button>
        </div>';
        exit;
      }

      // Confirmado, eliminar venta y actualizar stock
      $conexion->query("DELETE FROM ventas WHERE id_venta='$id_venta'");
      $conexion->query("UPDATE productos SET stock = stock + $cant WHERE id_producto='$id_p'");
      $mensaje = "✅ Venta eliminada.";
      $id_venta = $id_producto = $cantidad = $subtotal = $iva = $total = '';
    } else {
      $mensaje = "Venta no encontrada para eliminar.";
    }
  } else {
    $mensaje = "❗ Ingresa el ID de venta para eliminar.";
  }
}

// BUSCAR
if ($accion == "BUSCAR") {
  $res = $conexion->query("SELECT * FROM ventas WHERE id_venta='$id_venta'");
  if ($f = $res->fetch_assoc()) {
    $id_producto = $f['id_producto'];
    $cantidad    = $f['cantidad'];
    $subtotal    = $f['subtotal'];
    $iva         = $f['IVA'];
    $total       = $f['total'];
    $_SESSION['puede_editar'] = true;
    $mensaje = "🔍 Venta encontrada. Ya puedes editar.";
  } else {
    $mensaje = "Venta no encontrada.";
    unset($_SESSION['puede_editar']);
  }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Ventas</title>
  <style>
    * {
      box-sizing: border-box; 
      margin: 0; padding: 0; 
      font-family: Arial, sans-serif;
    }
    body {
      background: #2a2b38;
      color: #f5f5f5;
      display: flex;
      min-height: 100vh;
      flex-direction: column;
    }
    header, footer {
      background-color: #171824;
      color: #ffeba7;
      text-align: center;
      padding: 1rem 0;
      font-weight: bold;
      font-family: 'Arial Black', Arial, sans-serif;
      user-select: none;
      flex-shrink: 0;
    }
.content {
  display: flex;
  gap: 2%;
  padding: 1rem 2rem;
  max-width: 1400px; /* más ancho para dar espacio */
  margin: 0 auto;
  width: 100%;
}

.sidebar {
  position: fixed;
  left: 0;
  top: 4rem; /* altura del header, ajusta si cambia */
  bottom: 3rem; /* altura del footer, ajusta si cambia */
  width: 250px;
  background-color: transparent;
  display: flex;
  justify-content: center;
  align-items: flex-start;
  padding: 2rem 1rem;
  box-shadow: 4px 0 8px rgba(0,0,0,0.5);
  flex-shrink: 0;
  z-index: 100;
  overflow-y: auto; /* si el menú es largo */
}

    .card {
      width: 100%;
      background: linear-gradient(139deg, rgba(36, 40, 50, 1) 0%, rgba(37, 28, 40, 1) 100%);
      border-radius: 10px;
      padding: 15px 0;
      display: flex;
      flex-direction: column;
      gap: 20px;
      height: 100%;
    }
    .card .title {
      font-weight: 700;
      font-size: 20px;
      color: #ffeba7;
      text-align: center;
      margin-bottom: 15px;
      user-select: none;
      font-family: 'Arial Black', Arial, sans-serif;
    }
    .card .list {
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: 50px;
      padding: 0 10px;
      margin: 0;
    }
    .card .list .element {
      position: relative;
      display: flex;
      align-items: center;
      color: #7e8590;
      gap: 10px;
      cursor: pointer;
      user-select: none;
      padding: 8px 12px;
      border-radius: 6px;
      overflow: hidden;
      font-weight: 6000;
      font-size: 14px;
      outline: 2px solid #2c9caf;
      transition: color 300ms, transform 300ms, outline-color 300ms, box-shadow 300ms;
    }
    .card .list .element svg {
      width: 20px;
      height: 20px;
      stroke: #ffeba7;
      transition: stroke 300ms;
      z-index: 2;
    }
    .card .list .element::before {
      content: "";
      position: absolute;
      left: -50px;
      top: 0;
      width: 0;
      height: 100%;
      background-color: #ffeba7;
      transform: skewX(45deg);
      z-index: 1;
      transition: width 300ms;
      border-radius: 6px;
    }
.card .list .element:hover {
  color: #000000; /* Texto negro */
  transform: scale(1.05);
  outline-color: #000000;
  box-shadow: 4px 5px 17px -4px #000000;
}
 
.card .list .element:hover svg {
  stroke: #000000; /* Icono negro */
}
    .card .list .element:hover::before {
      width: 200%;
    }
    .card .list:last-child .element {
      outline-color: #ffeba7;
      color: #ffeba7;
    }
    .card .list:last-child .element::before {
      background-color: #ffeba7;
    }
.main-panel {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  padding: 80px 60px 40px 280px; /* mueve hacia abajo y respeta el sidebar */
  gap: 80px;
  flex: 1;
}


.form-panel {
  display: flex;
  flex-direction: column;
  max-width: 500px;
  width: 100%;
  margin-top: 40px; /* Alinea con la tabla */
}

    .form-panel h2 {
      color: #ffeba7;
      margin-bottom: 1rem;
      text-align: center;
    }
    label {
      display: block;
      margin-top: 1rem;
      font-size: .95rem;
    }
    input, select {
      width: 100%;
      padding: 1em;
      margin-top: .3rem;
      background-color: #ccc;
      border: none;
      border-radius: 15px;
      box-shadow: inset 2px 5px 10px rgba(0,0,0,0.3);
      transition: 300ms ease-in-out;
      color: #000;
    }
    input:focus, select:focus {
      background-color: white;
      transform: scale(1.05);
      box-shadow: 13px 13px 100px #969696, -13px -13px 100px #ffffff;
      outline: none;
    }
    .buttons {
      margin-top: 1.5rem;
      display: flex;
      gap: .5rem;
      flex-wrap: wrap;
      justify-content: center;
    }
    button {
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 1em;
      background-color: rgba(100,77,237,0.08);
      border: none;
      border-radius: 1.25em;
      transition: all 0.2s linear;
      cursor: pointer;
      color: #ffeba7;
      font-weight: bold;
      font-size: 13px;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      gap: .25rem;
      min-width: 80px;
    }
    button:hover {
      box-shadow: 4px 4px 15px rgba(0,0,0,0.3);
    }
    .mensaje {
      margin-top: 1rem;
      font-weight: bold;
      color: #ffeba7;
      text-align: center;
    }
  
.table-panel {
  display: flex;
  flex-direction: column;
  max-width: 750px;
  width: 100%;
  margin-top: 40px; /* misma altura que el form */
}



.form-panel h2,
.table-panel h2 {
  text-align: center;
  margin-bottom: 1rem;
  color: #ffeba7;
  font-size: 22px;
}

    /* Scroll para tabla */
    .table-scroll {
      overflow-y: auto;
      max-height: 600px;
      border-radius: 20px;
      box-shadow: inset 0 0 5px rgba(255,235,167,0.2);
      border: 1px solid #444;
    }
    table {
      
      width: 100%;
      border-collapse: collapse;
      background: #2a2b38;
      table-layout: fixed;
    }
    thead th {
      position: sticky;
      top: 0;
      background: #171824;
      color: #ffeba7;
      font-weight: bold;
      z-index: 10;
      border-bottom: 2px solid #ffeba7;
      padding: .8rem;
      text-align: center;
      word-wrap: break-word;
    }
    tbody td {
      padding: .8rem;
      text-align: center;
      border-bottom: 1px solid #444;
      color: #f5f5f5;
      word-wrap: break-word;
    }
    tbody tr:hover td {
      background: rgba(255,235,167,0.1);
    }
    @media(max-width: 900px){
      .content {
        flex-direction: column;
        padding: 1rem;
      }
      .sidebar {
        width: 100%;
        padding: 1rem 0.5rem;
        box-shadow: none;
        align-items: center;
      }
      .form-panel, .table-panel {
        max-width: 100%;
      }
    }
    .logo-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
}

.logo {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #ffeba7;
  box-shadow: 0 0 10px rgba(255, 235, 167, 0.5);
}

  </style>

  <script>
    let precios = {};
    <?php foreach ($productos as $p): ?>
      precios["<?= $p['id_producto'] ?>"] = <?= $p['precio'] ?>;
    <?php endforeach; ?>

    function calcularValores() {
      const idp = document.getElementById("id_producto").value;
      const cant = parseFloat(document.getElementById("cantidad").value) || 0;
      const prec = precios[idp] || 0;
      const sub = cant * prec;
      const iv  = sub * 0.16;
      const tot = sub + iv;
      document.getElementById("subtotal").value = sub.toFixed(2);
      document.getElementById("IVA").value      = iv.toFixed(2);
      document.getElementById("total").value    = tot.toFixed(2);
    }
  </script>
</head>
<body>
  <header>
    SOL Y RUTA
  </header>

<aside class="sidebar">
  <div class="card">
    <div class="logo-container">
  <img src="../imagenes/IME.jpeg" alt="Logo" class="logo">
  <div class="title">SOL Y RUTA</div>
</div>

    <ul class="list">
      <li class="element" onclick="location.href='../html/menu.php'">
        <!-- Icono Menú (igual que el tuyo) -->
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#ffeba7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"></path>
          <path d="m15 5 4 20"></path>
        </svg>
        <p class="label">Menú</p>
      </li>
      <li class="element" onclick="location.href='productos.php'">
        <!-- Icono Lista para Productos -->
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#ffeba7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
          <line x1="8" y1="6" x2="21" y2="6"></line>
          <line x1="8" y1="12" x2="21" y2="12"></line>
          <line x1="8" y1="18" x2="21" y2="18"></line>
          <circle cx="3" cy="6" r="1"></circle>
          <circle cx="3" cy="12" r="1"></circle>
          <circle cx="3" cy="18" r="1"></circle>
        </svg>
        <p class="label">Productos</p>
      </li>
      <li class="element" onclick="location.href='ventas.php'">
        <!-- Icono Canasta para Ventas -->
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#ffeba7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
          <circle cx="9" cy="21" r="1"></circle>
          <circle cx="20" cy="21" r="1"></circle>
          <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
        </svg>
        <p class="label">Ventas</p>
      </li>
      <li class="element" onclick="location.href='../index.html'">
        <!-- Icono Flecha Salida para Salir -->
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#ffeba7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
          <polyline points="16 17 21 12 16 7"></polyline>
          <line x1="21" y1="12" x2="9" y2="12"></line>
        </svg>
        <p class="label">cerrar sesion</p>
      </li>
    </ul>
  </div>
</aside>


    <main class="main-panel">
      <section class="form-panel">
        <h2>VENTAS</h2>
        <p>
        <form method="POST" autocomplete="off">
          <label for="id_venta">ID Venta</label>
          <input id="id_venta"     type="text" name="id_venta"   value="<?= htmlspecialchars($id_venta) ?>">
          <label for="id_producto">Producto</label>
          <select id="id_producto" name="id_producto" onchange="calcularValores()">
            <option value="">-- Selecciona un producto --</option>
            <?php foreach ($productos as $p): ?>
              <option value="<?= $p['id_producto'] ?>" <?= $id_producto == $p['id_producto'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($p['nombre']) ?> 
              </option>
            <?php endforeach; ?>
          </select>
          <label for="cantidad">Cantidad</label>
          <input id="cantidad" type="number" name="cantidad" value="<?= htmlspecialchars($cantidad) ?>" oninput="calcularValores()">
          <label for="subtotal">Subtotal</label>
          <input id="subtotal" name="subtotal" type="text" value="<?= htmlspecialchars($subtotal) ?>" readonly>
          <label for="IVA">IVA</label>
          <input id="IVA" name="IVA" type="text" value="<?= htmlspecialchars($iva) ?>" readonly>
          <label for="total">Total</label>
          <input id="total" name="total" type="text" value="<?= htmlspecialchars($total) ?>" readonly>

          <div class="buttons">
            <button type="submit" name="accion" value="INSERTAR">Insertar</button>
            <button type="submit" name="accion" value="EDITAR">Editar</button>
            <button type="submit" name="accion" value="ELIMINAR">Eliminar</button>
            <button type="submit" name="accion" value="BUSCAR">Buscar</button>
          </div>
          <div class="mensaje"><?= htmlspecialchars($mensaje) ?></div>
        </form>
      </section>

      <section class="table-panel">
        <h2>Listado de Ventas</h2>
        <div class="table-scroll">
          <table>
            <thead>
              <tr>
                <th>ID Venta</th>
                <th>Nombre de Producto</th>
                <th>Cantidad</th>
                <th>Subtotal</th>
                <th>IVA</th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody>
            <?php
            $consulta = "SELECT id_venta, nombre, cantidad, subtotal, IVA, total FROM ventas
            INNER JOIN productos ON ventas.id_producto=productos.id_producto";
            $resultado = $conexion->query($consulta);
            while ($fila = $resultado->fetch_assoc()) {
              echo "<tr>
                      <td>" . htmlspecialchars($fila['id_venta']) . "</td>
                      <td>" . htmlspecialchars($fila['nombre']) . "</td>
                      <td>" . htmlspecialchars($fila['cantidad']) . "</td>
                      <td>" . htmlspecialchars(number_format($fila['subtotal'],2)) . "</td>
                      <td>" . htmlspecialchars(number_format($fila['IVA'],2)) . "</td>
                      <td>" . htmlspecialchars(number_format($fila['total'],2)) . "</td>
                    </tr>";
            }
            ?>
            </tbody>
          </table>
        </div>
      </section>
    </main>
  </div>

  <footer>
    Sol y Ruta &copy; 2025 | Todos los derechos reservados
  </footer>
</body>
</html>
