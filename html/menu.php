
<?php
session_start();

include('../php/conexion.php');
  ?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Menú Principal</title>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(135deg, #2a2b38, #2a2b38);
      padding-top: 60px;
      padding-bottom: 40px;
    }

    header, footer {
      position: fixed;
      left: 0;
      right: 0;
      background-color: #1e1f2c;
      color: #ffeba7;
      text-align: center;
      padding: 1rem;
      z-index: 1000;
    }

    header {
      top: 0;
    }

    footer {
      bottom: 0;
    }

    .main-content {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: calc(100vh - 100px);
      padding: 1rem;
    }

    .image-side {
      max-width: 400px;
      margin-right: 2rem;
    }

    .image-side img {
      width: 100%;
      border-radius: 1000px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
    }

    .menu-container {
      background: rgba(42, 43, 56, 0.6);
      border-radius: 20px;
      backdrop-filter: blur(12px);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
      padding: 2.5rem;
      width: 90%;
      max-width: 340px;
      text-align: center;
      color: #f5f5f5;
      animation: fadeIn 1s ease-in-out;
    }

    .menu-container h2 {
      font-size: 1.8rem;
      margin-bottom: 1.5rem;
      color: #ffeba7;
    }

    .menu-btn {
      display: inline-block;
      width: 100%;
      margin: 0.7rem 0;
      padding: 0.75rem 1rem;
      background-color: #ffeba7;
      color: #2a2b38;
      font-weight: bold;
      font-size: 1rem;
      text-transform: uppercase;
      text-decoration: none;
      border: none;
      border-radius: 8px;
      transition: all 0.3s ease;
      box-shadow: 0 4px 14px rgba(0, 0, 0, 0.2);
    }

    .menu-btn:hover {
      background-color: #5e6681;
      color: #ffeba7;
      box-shadow: 0 6px 20px rgba(16, 39, 112, 0.3);
      transform: scale(1.03);
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 768px) {
      .main-content {
        flex-direction: column;
      }

      .image-side {
        margin: 0 0 1.5rem 0;
        max-width: 90%;
      }
    }
  </style>
</head>
<body>
  <header>
    <h1>BIENVENIDO 
      </h1>
  </header>

  <div class="main-content">
    <div class="image-side">
      <img src="../imagenes/IME.jpeg" alt="Imagen Principal">
    </div>

    <div class="menu-container">
      <h2>Menú Principal</h2>
      <a class="menu-btn" href="../php/productos.php">Productos</a>
      <a class="menu-btn" href="../php/ventas.php">Ventas</a>
      <a class="menu-btn" href="../index.html">cerrar sesion</a>
    </div>
  </div>

  <footer>
    <p>&copy; 2025 Tu Empresa. Todos los derechos reservados.</p>
  </footer>
</body>
</html>
