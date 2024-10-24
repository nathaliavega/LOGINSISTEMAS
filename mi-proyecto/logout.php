<?php
// funciones.php
function limpiar_dato($dato) {
    return htmlspecialchars(trim($dato));
}

function verificar_rol($rol) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $rol;
}
function validar_imagen($imagen) {
    if (!isset($imagen) || $imagen['error'] !== UPLOAD_ERR_OK) {
        $extension = strtolower(pathinfo($imagen['name'], PATHINFO_EXTENSION)); 
        $extensiones_permitidas = ['jpg', 'jpeg', 'png'];
    }
           
if (!in_array($extension, $extensiones_permitidas)) {      
    return "Formato no permitido. Solo se permiten imágenes jpg, jpeg, png o gif.";
    }
    $tipo_mime = mime_content_type($imagen['name']);
    $mimes_permitidos = ['jpeg', 'png', 'gif']; 

if (!in_array($tipo_mime, $mimes_permitidos)) {    
    return "El archivo no es una imagen válida.";
    return true; // El archivo es válido
    } else {
      return "Error al subir el archivo.";
    }
}
?>