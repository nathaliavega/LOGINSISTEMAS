<?php 
session_start();
require_once '../inc/conexion.php';
require_once '../inc/funciones.php';

$errores = [
    'nombre' => '',
    'email' => '',
    'password' => '',
    'imagen' => '',
    'exito' => ''
];

$nombre = '';
$email = '';
$password = '';
$rol = 'viewer'; 
$imagen = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = limpiar_dato($_POST['nombre']);
    $email = limpiar_dato($_POST['email']);
    $password = $_POST['password'];
    $rol = limpiar_dato($_POST['rol']); 
    $imagen = $_FILES['imagen'];

    // Validaciones de campos
    if (empty($nombre)) {
        $errores['nombre'] = 'El nombre es obligatorio.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores['email'] = 'El email no es válido.';
    }
    if (strlen($password) < 6) {
        $errores['password'] = 'La contraseña debe tener al menos 6 caracteres.';
    }
    // Verificar si el email ya existe en la base de datos
    $sqlVerificacion = "SELECT COUNT(*) FROM usuarios WHERE email = :email";
    $stmtVerificacion = $conexion->prepare($sqlVerificacion);
    $stmtVerificacion->bindParam(':email', $email);
    $stmtVerificacion->execute();
    $emailExiste = $stmtVerificacion->fetchColumn();

    if ($emailExiste) {
        $errores['email'] = 'El correo electrónico ya está registrado.';
    }
    if ($imagen['error'] === UPLOAD_ERR_OK) {
        $rutaDestino = '../views/uploads' . basename($imagen['name']);
        $extension = strtolower(pathinfo($rutaDestino, PATHINFO_EXTENSION));
        $tamanoMaximo = 5 * 1024 * 1024; // Tamaño máximo de 2 MB
        $tipoMime = mime_content_type($imagen['tmp_name']);
if (!in_array($tipoMime, ['image/jpeg', 'image/png'])) {
    $errores['imagen'] = 'Solo se permiten imágenes JPG, JPEG o PNG.';
}
        // Validar tipo de archivo
       
        // Validar tamaño del archivo
        elseif ($imagen['size'] > $tamanoMaximo) {
            $errores['imagen'] = 'El archivo es demasiado grande. Máximo 2MB.';
        }
        else {
            // Mover el archivo subido al destino final
            if (!move_uploaded_file($imagen['tmp_name'], $rutaDestino)) {
                $errores['imagen'] = 'Error al subir la imagen.';
            }
        }
    } elseif ($imagen['error'] !== UPLOAD_ERR_NO_FILE) {
        $errores['imagen'] = 'Error al cargar la imagen.';
    } else {
        $rutaDestino = ''; // No se subió ninguna imagen
    }



      

    // Si no hay errores, proceder con el registro
    if (empty(array_filter($errores))) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios (nombre, email, password, rol, imagen) 
                VALUES (:nombre, :email, :password, :rol, :imagen)";
        $stmt = $conexion->prepare($sql);

        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $passwordHash);
        $stmt->bindParam(':rol', $rol);
        $stmt->bindParam(':imagen', $rutaDestino);

        if ($stmt->execute()) {
            $errores['exito'] = 'Usuario registrado exitosamente.';
        } else {
            echo "Error al registrar el usuario.";
        }
    }   
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        body{
            margin: 0; /* Elimina márgenes por defecto */
        }
        .caja{
            display: grid; /* Activa el modo de grid */
            place-items: center; /* Centra el contenido horizontal y verticalmente */
            min-height: 100vh; /* Asegura que el body tenga al menos la altura completa de la pantalla */
            background-color: #f0f0f0; /* Color de fondo opcional */
            font-weight: bold;
        }
        .hl{
            border: 1px solid #ccc; /* Borde gris */
            border-radius: 4px;
            width: 100%;
            padding: 8px;
        }
        header{
            display: flex;
            justify-content: flex-end;
            align-items: center;
            height: 50px;
        }
        a{
            padding-right: 20px;
            text-decoration: none;
            color: black;
            font-size: 27px;
        }
        form{
            width: 100%;
        }
        h2{
            text-align: center;
        }
        .exito{
            text-align: center;
            color: green;
            font-weight: bold;
        }
        input{
            width: -webkit-fill-available;
        }
        .error {
            color: red;
            font-size: 0.9em;
        }
       .registro {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .roles {
            margin-bottom: 5px;
            display: flex;
            align-items: flex-start; /* Centra verticalmente */
        }

       .rol {
            padding: 12px;
            border: 1px solid #ccc; /* Borde gris */
            border-radius: 4px;
            margin-bottom: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 10px;
            font-weight: bold;
        }

       .hl {
            border: 1px solid #ccc; /* Borde gris */
            border-radius: 4px;
            width: 100%;
            padding: 11px;
            font-weight: bold;
        }

        .select-hola {
            display: flex;
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc; /* Borde gris */
            border-radius: 4px;
            box-sizing: border-box;
            padding-right: 30px;
            color: black;
            font-weight: bold;

            
        }
        .imagenes {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        .imagen {
            padding: 4px;
            border: 1px solid #ccc; /* Borde gris */
            border-radius: 4px;
            margin-right: 10px;
            display: flex;
            padding-right: 10px;
            white-space: nowrap;
            text-overflow: ellipsis;
            padding-top: 12px;
            padding-bottom: 12px;
            align-items: center;
            justify-content: center; /* Centra el contenido horizontalmente */
            align-items: center; /* Centra el contenido verticalmente */
        }
        .select-container {
            height: 23px;
            width: 50%;
            border: 1px solid #ccc; /* Borde para el select */
            border-radius: 4px;
            padding: 10px;
            text-align: center; /* Centra el contenido dentro del contenedor */
           
        }

       /* Estilo para inputs, selects y botones */
        
        .btnarchivos {
            border: 1px solid black;
            background-color: transparent; 
            color: black;
            cursor: pointer; /* Cambia el cursor en forma de mano cuando se pasa por encima*/ 
            padding: 0; /* Añade espacio interno al botón */
            width: 100%; 
            height: 20px;
            font-size: 14px; 
            font-weight: bold; /* Texto en negrita para todos */
            text-align: center; /* Centra el texto dentro del botón */
            border-radius: 3px;
            }
            .btnarchivos:hover {
                background-color:#ccc ;
            }
            .file-input {
                display: none; /* Oculta completamente el input de archivo */
            }
         

      
</style>
</head>
<body>
    <header>
        <a href="../index.php">Index</a>
        <a href="login.php">Login</a>
    </header>

    <div class="caja">
        <form method="post" enctype="multipart/form-data">
            <h2>Registro de Usuario</h2>
            
            <?php if (!empty($errores['exito'])): ?>
                <p class="exito"><?php echo $errores['exito']; ?></p>
            <?php endif; ?>
    
            <label for="nombre">Nombre:</label>
            <input type="text" name="nombre" id="nombre" value="<?php echo htmlspecialchars($nombre); ?>" >
            <?php if (!empty($errores['nombre'])): ?>
                <p class="error"><?php echo $errores['nombre']; ?></p>
            <?php endif; ?>
        
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>" >
            <?php if (!empty($errores['email'])): ?>
                <p class="error"><?php echo $errores['email']; ?></p>
            <?php endif; ?>
        
            <label for="password">Contraseña:</label>
            <input type="password" name="password" id="password" >
            <?php if (!empty($errores['password'])): ?>
                <p class="error"><?php echo $errores['password']; ?></p>
            <?php endif; ?>

            <?php if (!empty($errores['imagen'])): ?>
                <p class="error"><?php echo $errores['imagen']; ?></p>
            <?php endif; ?>
        
            <li class="roles">
                <label class="rol" for="rol">Rol:</label>
                <div class="hl">
                    <select class="hola" name="rol" id="rol">
                        <option value="viewer">Invitado</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
            </li>
    
            <div class="roles">
                <div class="imagen">
                    Imagen de perfil:
                </div>
                <div class="select-container">
                    <input type="file" id="imagen" name="imagen" class="file-input" accept="image/*">
                    <button type="button" class="btnarchivos" onclick="uploadFile()">Elegir archivo</button>
                </div>
            </div>

            <button type="submit">Registrar</button>
        </form>
    </div>
    <script>
        document.querySelector('.btnarchivos').addEventListener('click', function() {
            document.querySelector('#imagen').click(); // Simula un clic en el input de archivo oculto
            fileName = this.files[0]?.name || ''; 
        });
           // Cambia el título del botón al seleccionar un archivo
    document.querySelector('#imagen').addEventListener('change', function() {
        const fileName = this.files[0]?.name || ''; // Obtiene el nombre del archivo seleccionado
        const button = document.querySelector('.btnarchivos');
        if (fileName) {
            button.title = fileName; // Establece el nombre del archivo como tooltip
        } else {
            button.title = 'Elegir archivo'; // Mensaje por defecto
        }
    });
        
    </script>
    
    </div>
</body>
</html>