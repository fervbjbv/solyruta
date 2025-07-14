<?php
// archivo: cargar_con_sesion.php

// Iniciar sesión solo si aún no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si no hay sesión activa, redirigir al login
if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php"); // Ajusta si tu login está en otra ruta
    exit();
}

// Obtener la vista deseada desde la URL: ?vista=productos.php
$archivo = $_GET['vista'] ?? '';

// Lista blanca de archivos permitidos para incluir (ruta relativa desde este archivo)
$permitidos = [
    'productos.php' => '../php/productos.php',
    'ventas.php'    => '../php/ventas.php',
    'menu.php'      => '../php/menu.php'
];

// Si el archivo está en la lista blanca, incluirlo
if (array_key_exists($archivo, $permitidos)) {
    include $permitidos[$archivo];
} else {
    // Si no está permitido o no se especificó
    echo "<h2 style='color: red; text-align: center; margin-top: 50px;'>⚠️ Archivo no permitido o no especificado.</h2>";
}
?>
