<?php
session_start();


$user = new UserController();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["login"])) {
        $user->login();
    } elseif (isset($_POST["register"])) {
        $user->register();
    } elseif (isset($_POST["logout"])) {
        $user->logout();
    } elseif (isset($_POST["deleteUser"])) {
        $user->deleteUser();
    } elseif (isset($_POST["updateUser"])) {
        $user->updateUser();
    } elseif (isset($_POST["changePassword"])) {
        $user->changePassword();
    }
}



class UserController
{


    function login()
    {
        session_start();
        //Guardo el PDO q vamos a usar y lo creamos con conn
        $pdo = $this->conn();
        //Redireccion basica
        $redirect = "../view/index.php";
        if (isset($_POST["redirect"]) && preg_match("/^[a-zA-Z0-9\/\-_]+\.php$/", $_POST["redirect"])) {
            $redirect = "../view/" . $_POST["redirect"];
        }

        if (empty($_POST["user"]) || empty($_POST["password"])) {
            $_SESSION["error_message"] = "Usuario o contraseña vacíos.";
            header("Location: $redirect");
            exit();
        }
        //Query, el statement q usamos para enviarlo a la bbdd con pdo.
        $sql = "SELECT * FROM users WHERE user = ?";
        $stmt = $pdo->prepare($sql);
        //Ejecutamos stmt i con fetch cojemos los resultados y lo guardamos en variable array usuario
        $stmt->execute([$_POST["user"]]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        //Importante: verificamos con password verify, esto solo es possible si hemos encriptado la password. Ponemos los datos en 
        //el array de sesion asi lo podemos usar en todas las paginas
        if ($user && password_verify($_POST["password"], $user["PASSWORD"])) {
            $_SESSION["user_id"] = $user["ID"];
            $_SESSION["rol"] = $user["ROL"];
            $_SESSION["name"] = $user["NAME"];
            $_SESSION["lastname"] = $user["LASTNAME"];
            $_SESSION["email"] = $user["EMAIL"];
            $_SESSION["username"] = $user["USER"];
            $_SESSION["user_image"] = $user["USER_IMAGE"];

            //Identificamos q tipo de usuario es, dependiendo de cual sea lo enviamos a un profile u otro.
            if ($_SESSION['ROL'] == 1) {
                header("Location: ../view/profileadmin.php");
            } else {
                header("Location: ../view/profile.php");
            }

            exit();
        }
        //Establecemos mensaje de error en caso que no se haya podido logear
        $_SESSION["error_message"] = "Usuario o contraseña incorrectos.";
        header("Location: $redirect");
        exit();
    }

    //El login con pdo sigue igual
    function logout()
    {
        session_start();
        session_unset();
        session_destroy();

        if (!headers_sent()) {
            header("Location: ../view/index.php");
            exit();
        } else {
            echo "Error: Las cabeceras ya han sido enviadas. No se puede redirigir.";
        }
    }

    function register()
    {
        //Iniciamos sesion i creamos objeto conn y lo guardamos en pdo
        session_start();
        $pdo = $this->conn();

        // VALIDACIONES (Ya hechas por Mario anteriormente)
        if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
            $_SESSION["register_error_message_email"] = "Email no válido.";
            header("Location: ../view/register.php");
            exit();
        }

        if (!preg_match("/^[1-3]$/", $_POST["rol"])) {
            $_SESSION["register_error_message_rol"] = "El rol debe ser 1, 2 o 3.";
            header("Location: ../view/register.php");
            exit();
        }

        $password = $_POST["password"];
        if (strlen($password) < 8 || !preg_match("/[a-z]/i", $password) || !preg_match("/[0-9]/", $password)) {
            $_SESSION["register_error_message_password"] = "La contraseña debe tener al menos 8 caracteres, una letra y un número.";
            header("Location: ../view/register.php");
            exit();
        }

        if ($password !== $_POST["password_confirmation"]) {
            $_SESSION["register_error_message_confirmation"] = "Las contraseñas no coinciden.";
            header("Location: ../view/register.php");
            exit();
        }
        //Una vez se ha validado todo , encriptamos la password con hash 
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $name = trim(htmlspecialchars($_POST["name"]));
        $lastname = trim(htmlspecialchars($_POST["lastname"]));
        $username = trim(htmlspecialchars($_POST["user"]));
        $email = trim(htmlspecialchars($_POST["email"]));
        $rol = intval($_POST["rol"]);

        $user_image = null;
        if (!empty($_FILES['imagen']['name'])) {
            $img_name = $_FILES['imagen']['name'];
            $type = $_FILES['imagen']['type'];
            $size = $_FILES['imagen']['size'];

            if ($size > 2000000) {
                $_SESSION["register_error_message_image_size"] = "La imagen es demasiado grande (máx 2MB).";
                header("Location: ../view/register.php");
                exit();
            }

            if ($type == "image/jpeg" || $type == "image/jpg" || $type == "image/png") {
                $directory = __DIR__ . "/images/";
                if (!is_dir($directory)) {
                    mkdir($directory, 0777, true);
                }

                $file_name = time() . "_" . basename($img_name);
                $user_image = "images/" . $file_name;
                move_uploaded_file($_FILES['imagen']['tmp_name'], $directory . $file_name);
            } else {
                $_SESSION["register_error_message_image_format"] = "Formato de imagen no válido (solo JPG, JPEG o PNG).";
                header("Location: ../view/register.php");
                exit();
            }
        }

        $sql = "INSERT INTO users (USER, NAME, LASTNAME, EMAIL, PASSWORD, ROL, USER_IMAGE) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username, $name, $lastname, $email, $password_hash, $rol, $user_image]);

            $_SESSION["success_message"] = "Registro exitoso. Ahora puedes iniciar sesión.";
            header("Location: ../view/login.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION["error_message"] = "Error en el registro: " . $e->getMessage();
            header("Location: ../view/register.php");
            exit();
        }
    }

    function deleteUser()
    {
        $pdo = $this->conn();

        if (isset($_SESSION["user_id"])) {

            try {
                $sql = "DELETE FROM users WHERE ID = :id";
                $stmt = $pdo->prepare($sql);

                if (!$stmt) {
                    die("Error en la preparación de la consulta.");
                }
                $id = $_SESSION["user_id"];
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();

                session_destroy();

                if (!headers_sent()) {
                    header("Location: ../view/index.php");
                    exit;
                } else {
                    echo "Error: Las cabeceras ya han sido enviadas. No se puede redirigir.";
                }
            } catch (PDOException $e) {
                die("Error en la consulta: " . $e->getMessage());
            }
        }
    }

    function updateUser()
    {
        session_start(); // Asegúrate de que la sesión esté iniciada

        // Validar email
        if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
            die("Email no válido");
        }


        // Conectar con PDO usando el método conn()
        $pdo = $this->conn();

        // Sanitizar entradas
        $_SESSION["name"] = trim(htmlspecialchars($_POST["name"]));
        $_SESSION["lastname"] = trim(htmlspecialchars($_POST["lastname"]));
        $_SESSION["username"] = trim(htmlspecialchars($_POST["user"]));
        $_SESSION["email"] = trim(htmlspecialchars($_POST["email"]));

        // Obtener ID del usuario desde la sesión
        $userId = $_SESSION["user_id"]; // ⚠️ Asegúrate de que esté definida la sesión y este valor

        // Preparar consulta
        $sql = "UPDATE users SET USER = :username, `NAME` = :name, LASTNAME = :lastname, EMAIL = :email WHERE ID = :id";


        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':username' => $_SESSION["username"],
                ':name' => $_SESSION["name"],
                ':lastname' =>  $_SESSION["lastname"],
                ':email' =>  $_SESSION["email"],
                ':id' => $userId
            ]);



            if ($_SESSION["rol"] == 1) {
                header("Location: ../view/profileAdmin.php");
                exit;
            } else {
                header("Location: ../view/profile.php");
                exit;
            }
        } catch (PDOException $e) {
            echo "Error en el update: " . $e->getMessage();
        }
    }





    function changePassword()
    {
        session_start();
        $pdo = $this->conn();

        $userId = $_SESSION["user_id"];
        $currentPassword = $_POST["currentPassword"];
        $newPassword = $_POST["newPassword"];
        $confirmNewPassword = $_POST["confirmNewPassword"];

        // Validaciones básicas

        if ($newPassword !== $confirmNewPassword) {
            $_SESSION["error_message_newpassword"] = "Las contraseñas nuevas no coinciden.";
            header("Location: ../view/updatePassword.php");
            exit();
        }

        if (strlen($newPassword) < 8 || !preg_match("/[a-z]/i", $newPassword) || !preg_match("/[0-9]/", $newPassword)) {
            $_SESSION["error_message_password"] = "La nueva contraseña debe tener al menos 8 caracteres y una letra.";
            header("Location: ../view/updatePassword.php");
            exit();
        }

        // Obtener la contraseña actual del usuario
        $sql = "SELECT PASSWORD FROM users WHERE ID = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($currentPassword, $user["PASSWORD"])) {
            $_SESSION["error_message_current_password"] = "La contraseña actual es incorrecta.";
            header("Location: ../view/updatePassword.php");
            exit();
        }

        // Hashear la nueva contraseña
        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

        // Actualizar la contraseña en la base de datos
        $sql = "UPDATE users SET PASSWORD = ? WHERE ID = ?";
        $stmt = $pdo->prepare($sql);

        try {
            $stmt->execute([$newPasswordHash, $userId]);
            $_SESSION["success_message"] = "Contraseña actualizada correctamente.";
            $_SESSION["password"] = $newPasswordHash; // Actualizar en sesión

            // Redirigir según el rol
            if ($_SESSION["rol"] == 1) {
                header("Location: ../view/profileAdmin.php");
            } else {
                header("Location: ../view/profile.php");
            }
            exit();
        } catch (PDOException $e) {
            $_SESSION["error_message"] = "Error al actualizar la contraseña: " . $e->getMessage();
            header("Location: ../view/updatePassword.php");
            exit();
        }
    }

    function conn()
    {
        $host = "localhost";
        $dbname = "mp0487_firalia";
        $username = "root";
        $password = "";

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
}
