

<?php
session_start();

include('../php/conexion.php');


// Variables
$id = trim($_POST['id_producto'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$precio = trim($_POST['precio'] ?? '');
$stock = trim($_POST['stock'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$accion = $_POST['accion'] ?? '';
$mensaje = "";

// Funciones
function camposCompletos(...$campos) {
  foreach ($campos as $campo) {
    if ($campo === '') return false;
  }
  return true;
}
function limpiar($conexion, $valor) {
  return mysqli_real_escape_string($conexion, trim($valor));
}

// Limpiar entradas
$id = limpiar($conexion, $id);
$nombre = limpiar($conexion, $nombre);
$precio = limpiar($conexion, $precio);
$stock = limpiar($conexion, $stock);
$descripcion = limpiar($conexion, $descripcion);

// INSERTAR
if ($accion == "INSERTAR") {
  if (camposCompletos($id, $nombre, $precio, $stock, $descripcion)) {
    $check = $conexion->query("SELECT * FROM productos WHERE id_producto = '$id'");
    if ($check->num_rows > 0) {
      $mensaje = "El producto con ese ID ya existe.";
    } else {
      $sql = "INSERT INTO productos (id_producto, nombre, precio, stock, descripcion) 
              VALUES ('$id', '$nombre', '$precio', '$stock', '$descripcion')";
      if ($conexion->query($sql)) {
        $mensaje = "✅ Producto insertado correctamente.";
        $id = $nombre = $precio = $stock = $descripcion = '';
        unset($_SESSION['puede_editar_producto']);
      } else {
        $mensaje = "Error al insertar: " . $conexion->error;
      }
    }
  } else {
    $mensaje = "❗ Por favor llena todos los campos antes de insertar.";
  }
}

// EDITAR
if ($accion == "EDITAR") {
  if (!isset($_SESSION['puede_editar_producto']) || $_SESSION['puede_editar_producto'] !== true) {
    $mensaje = "Primero debes buscar el producto antes de editar.";
  } elseif (camposCompletos($id, $nombre, $precio, $stock, $descripcion)) {
    $check = $conexion->query("SELECT * FROM productos WHERE id_producto = '$id'");
    if ($check->num_rows > 0) {
      $sql = "UPDATE productos 
              SET nombre='$nombre', precio='$precio', stock='$stock', descripcion='$descripcion' 
              WHERE id_producto='$id'";
      if ($conexion->query($sql)) {
        $mensaje = "✅ Producto actualizado.";
        $id = $nombre = $precio = $stock = $descripcion = '';
        unset($_SESSION['puede_editar_producto']);
      } else {
        $mensaje = "Error al actualizar: " . $conexion->error;
      }
    } else {
      $mensaje = "Producto con ese ID no existe.";
    }
  } else {
    $mensaje = "❗ Por favor llena todos los campos antes de editar.";
  }
}

// ELIMINAR
if ($accion == "ELIMINAR") {
  if (!empty($id)) {
    $check = $conexion->query("SELECT * FROM productos WHERE id_producto = '$id'");
    if ($check->num_rows > 0) {
      $confirmacion = $_POST['confirmacion'] ?? '';

      if ($confirmacion !== 'SI') {
        echo '
        <div style="
          background-color:rgb(34, 39, 55); 
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
            ¿Estás seguro que deseas eliminar el producto con ID <strong>' . htmlspecialchars($id) . '</strong>?
          </p>
          <form method="POST" style="display: inline-block;">
            <input type="hidden" name="id_producto" value="' . htmlspecialchars($id) . '">
            <input type="hidden" name="accion" value="ELIMINAR">
            <input type="hidden" name="confirmacion" value="SI">
            <button type="submit" style="
              background-color: #FFDD00; 
              border: none; 
              color:rgb(38, 46, 65); 
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
          <button onclick="window.location.href=\'productos.php\'" style="
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

      // Confirmación recibida, eliminar producto
      $conexion->query("DELETE FROM ventas WHERE id_producto='$id'");
      if ($conexion->query("DELETE FROM productos WHERE id_producto='$id'")) {
        $mensaje = "✅ Producto eliminado correctamente .";
        $id = $nombre = $precio = $stock = $descripcion = '';
        unset($_SESSION['puede_editar_producto']);
      } else {
        $mensaje = "Error al eliminar: " . $conexion->error;
      }
    } else {
      $mensaje = "Producto con ese ID no existe.";
    }
  } else {
    $mensaje = "❗ Ingresa el ID del producto para eliminar.";
  }
}

// BUSCAR
if ($accion == "BUSCAR") {
  if (!empty($id)) {
    $sql = "SELECT * FROM productos WHERE id_producto='$id'";
    $resultado = $conexion->query($sql);
    if ($fila = $resultado->fetch_assoc()) {
      $nombre = $fila['nombre'];
      $precio = $fila['precio'];
      $stock = $fila['stock'];
      $descripcion = $fila['descripcion'];
      $_SESSION['puede_editar_producto'] = true;
      $mensaje = "🔍 Producto encontrado. Ya puedes editar.";
    } else {
      $mensaje = "Producto no encontrado.";
      unset($_SESSION['puede_editar_producto']);
    }
  } else {
    $mensaje = "❗ Ingresa el ID del producto para buscar.";
  }
}

// Obtener productos para listado
$productos_result = $conexion->query("SELECT * FROM productos ORDER BY id_producto");
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Productos</title>
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
      max-width: 1400px;
      margin: 0 auto;
      width: 100%;
    }
    .sidebar {
      position: fixed;
      left: 0;
      top: 4rem;
      bottom: 3rem;
      width: 250px;
      background-color: transparent;
      display: flex;
      justify-content: center;
      align-items: flex-start;
      padding: 2rem 1rem;
      box-shadow: 4px 0 8px rgba(0,0,0,0.5);
      flex-shrink: 0;
      z-index: 100;
      overflow-y: auto;
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
      color: #000000;
      transform: scale(1.05);
      outline-color: #000000;
      box-shadow: 4px 5px 17px -4px #000000;
    }
    .card .list .element:hover svg {
      stroke: #000000;
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
      padding: 80px 60px 40px 280px;
      gap: 80px;
      flex: 1;
    }
    .form-panel {
      display: flex;
      flex-direction: column;
      max-width: 500px;
      width: 100%;
      margin-top: 40px;
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
    input, textarea {
      width: 100%;
      padding: 1em;
      margin-top: .3rem;
      background-color: #ccc;
      border: none;
      border-radius: 15px;
      box-shadow: inset 2px 5px 10px rgba(0,0,0,0.3);
      transition: 300ms ease-in-out;
      color: #000;
      resize: vertical;
    }
    input:focus, textarea:focus {
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
      margin-top: 40px;
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
</head>
<body>
  <header>
    SOL Y RUTA
  </header>

  <aside class="sidebar">
    <div class="card">
      <div class="logo-container">
        <img src="../imagenes/IME.jpeg" alt="Logo" class="logo" />
        <div class="title">SOL Y RUTA</div>
      </div>
      <ul class="list">
        <li class="element" onclick="location.href='../html/menu.php'">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#ffeba7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"></path>
            <path d="m15 5 4 20"></path>
          </svg>
          <p class="label">Menú</p>
        </li>
        <li class="element" onclick="location.href='productos.php'">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#ffeba7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <line x1="20" y1="6" x2="9" y2="17"></line>
            <line x1="9" y1="6" x2="20" y2="17"></line>
            <circle cx="4" cy="4" r="2"></circle>
          </svg>
          <p class="label">Productos</p>
        </li>
        <li class="element" onclick="location.href='ventas.php'">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#ffeba7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <path d="M3 12h18"></path>
            <path d="M12 3v18"></path>
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
      <h2>Registro de Productos</h2>

      <form method="POST" action="productos.php" autocomplete="off">
        <label for="id_producto">ID Producto</label>
        <input id="id_producto" name="id_producto" type="text" value="<?= htmlspecialchars($id) ?>" />

        <label for="nombre">Nombre</label>
        <input id="nombre" name="nombre" type="text" value="<?= htmlspecialchars($nombre) ?>" />

        <label for="precio">Precio</label>
        <input id="precio" name="precio" type="number" step="0.01" value="<?= htmlspecialchars($precio) ?>" />

        <label for="stock">Stock</label>
        <input id="stock" name="stock" type="number" value="<?= htmlspecialchars($stock) ?>" />

        <label for="descripcion">Descripción</label>
        <textarea id="descripcion" name="descripcion" rows="3"><?= htmlspecialchars($descripcion) ?></textarea>

        <div class="buttons">
          <button type="submit" name="accion" value="INSERTAR">Insertar</button>
          <button type="submit" name="accion" value="EDITAR">Editar</button>
          <button type="submit" name="accion" value="BUSCAR">Buscar</button>
          <button type="submit" name="accion" value="ELIMINAR">Eliminar</button>
        </div>
      </form>

      <?php if ($mensaje): ?>
        <div class="mensaje"><?= htmlspecialchars($mensaje) ?></div>
      <?php endif; ?>
    </section>

    <section class="table-panel">
      <h2>Lista de Productos</h2>

      <div class="table-scroll">
        <table>
          <thead>
            <tr>
              <th>ID Producto</th>
              <th>Nombre</th>
              <th>Precio</th>
              <th>Stock</th>
              <th>Descripción</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($productos_result && $productos_result->num_rows > 0): ?>
              <?php while ($fila = $productos_result->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($fila['id_producto']) ?></td>
                  <td><?= htmlspecialchars($fila['nombre']) ?></td>
                  <td>$<?= number_format($fila['precio'], 2) ?></td>
                  <td><?= htmlspecialchars($fila['stock']) ?></td>
                  <td><?= htmlspecialchars($fila['descripcion']) ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="5">No hay productos registrados.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

  </main>

  <footer>
    SOL Y RUTA - 2025
  </footer>
</body>
</html>
