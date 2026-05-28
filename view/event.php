<?php
session_start();
if (isset($_SESSION["user_image"])) {
    $user_image = $_SESSION["user_image"];
}

$conexion = new mysqli("localhost", "root", "", "mp0487_firalia");
$conexion->set_charset("utf8");

// Obtener todos los eventos
$eventos_query = $conexion->query("SELECT * FROM EVENTOS");
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>


    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=arrow_forward" />

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/event.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>

<body style="background-color: #F2F0EF !important;">

    <nav class="main-nav">
        <!-- ------------------------------------------------------------------ SIDE BAR --------------------------------------------------------------------------------->
        <ul class="sidebar">
            <li onclick="hideSidebar()"><a href="#"><svg xmlns="http://www.w3.org/2000/svg" height="24px"
                        viewBox="0 -960 960 960" width="24px" fill="#">
                        <path
                            d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z" />
                    </svg></a></li>
            <li><a href="aboutus.php">NOSOTROS</a></li>
            <li><a href="event.php">Eventos</a></li>
            <li><a href="support.php">Soporte</a></li>
            <?php if (isset($_SESSION["user_id"])): ?>
                <li><a href="login.php">Perfil</a></li>
            <?php else: ?>
                <li><a href="login.php">Iniciar Sesión</a></li>
                <li><a href="register.php">Registrarse</a></li>
            <?php endif; ?>
        </ul>
        <!-- ------------------------------------------------------------------ MAIN MENU --------------------------------------------------------------------------------->
        <ul class="main-menu">
            <li>
                <a href="index.php">
                    <img class="logo-nav" src="images/logo2-modified.png" alt="logo" id="logo-nav">
                </a>
            </li>
            <li class="hideOnMobile link"><a href="aboutus.php">NOSOTROS</a></li>
            <li class="hideOnMobile link"><a href="event.php">EVENTOS</a></li>
            <li class="hideOnMobile link"><a href="support.php">SOPORTE</a></li>

            <li>
                <form class="nav-form">
                    <input type="text" class="search-bx" placeholder="">
                    <input type="image" class="search-icon" src="images/search.svg" alt="Submit">
                </form>
            </li>
            <?php if (isset($_SESSION["user_id"])): ?>
                <li>
                    <?php if ($_SESSION["rol"] == 1): ?>
                        <a href="profileadmin.php">
                            <img src="../controller/<?= $user_image ?>" alt="Pfp" class="pfpNav">
                        <?php else: ?>
                            <a href="profile.php">
                                <img src="images/icons/estandarPfp.jpg" alt="Pfp" class="pfpNav">
                            <?php endif; ?>
                            </a>
                </li>
            <?php else: ?>
                <li class="hideOnMobile"><button id="open-popup">LOG IN</button></li>
            <?php endif; ?>

            <li class="menu-button" onclick="showSidebar()"><a href="#"><svg xmlns="http://www.w3.org/2000/svg"
                        height="24px" viewBox="0 -960 960 960" width="26px" fill="#e8eaed">
                        <path d="M120-240v-80h720v80H120Zm0-200v-80h720v80H120Zm0-200v-80h720v80H120Z" />
                    </svg></a></li>
        </ul>
    </nav>
    <!-- -------------------------------------------------------------------------- LOG IN  --------------------------------------------------------------------------------->
    <div class="popup" id="popup">
        <div class="overlay"></div>
        <div class="popup-content">
            <h2>Login</h2>
            <form method="POST" action="../controller/UserController.php">
                <div class="login-box">
                    <?php
                    if (isset($_SESSION["error_message"])) {
                        echo "<p class='error-message'>" . $_SESSION["error_message"] . "</p>";
                        unset($_SESSION["error_message"]);
                    }
                    ?>
                    <input type="text" name="user" id="user" placeholder="Username" required>
                    <input type="hidden" name="login" value="login">
                    <input type="password" name="password" id="password" placeholder="Password" required>
                    <input type="hidden" name="redirect" value="<?php echo basename($_SERVER['PHP_SELF']); ?>">
                    <a href="register.php" class="atext">Problemas con la contraseña?</a>
                    <a href="register.php" class="atext">No tienes cuenta? Registrate!</a>
                </div>
                <div class="controls">
                    <button class="close-btn">Cancelar</button>
                    <button class="submit-btn" type="submit">Iniciar Sesión</button>
                </div>
            </form>
        </div>
    </div>




    <!-- PAGE NAV -->
    <nav class="page-nav">
        <ul>
            <li><a href="#events">EVENTS</a></li>
        </ul>
    </nav>

    <!-- BODY -->
    <section class="main-body">
        <div class="sections-container">
            <!-- EVENTS -->
            <div class="events" id="events">
                <h1>ALL EVENTS</h1>
                <div class="events-list">
                    <?php while ($evento = $eventos_query->fetch_assoc()):
                        $id_evento = $evento['ID'];
                        // Obtener fechas del evento
                        $fechas_query = $conexion->query("SELECT * FROM FECHAS_EVENTOS WHERE ID_EVENTO = $id_evento");
                        // Obtener galería del evento
                        $galeria_query = $conexion->query("SELECT * FROM GALERIA_EVENTOS WHERE ID_EVENTO = $id_evento");
                    ?>
                        <div class="event-card">
                            <div class="event-header">
                                <img src="<?= $evento['MAIN_IMAGE_PATH'] ?>" alt="<?= htmlspecialchars($evento['NOMBRE']) ?>" class="event-image" style="width: 300px; height: 200px; object-fit: cover;">
                                <div class="event-info">
                                    <h2><?= htmlspecialchars($evento['NOMBRE']) ?></h2>
                                    <?php if (isset($user['ROL'])): ?>
                                        <a href="eventManager.php?id=<?= $evento['ID'] ?>" class="btn btn-primary btn-sm">Manage</a>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- DATES -->
                            <div class="event-dates">
                                <h3>UPCOMING DATES</h3>
                                <ul style="list-style-type: none; padding: 0;">
                                    <?php
                                    if ($fechas_query->num_rows > 0):
                                        while ($fecha = $fechas_query->fetch_assoc()):
                                    ?>
                                            <li>
                                                <div class="entry">
                                                    <div class="date">
                                                        <p><?= $fecha['NUM_DIA'] ?></p>
                                                        <p><?= $fecha['MES'] ?></p>
                                                    </div>
                                                    <div class="description">
                                                        <p class="p4"><?= $fecha['NOMBRE_DIA'] ?> ·
                                                            <?= sprintf('%02d:%02d', $fecha['HORA'], $fecha['MINUTO']) ?></p>
                                                        <p class="p5"><?= $fecha['CIUDAD'] ?> · <?= $fecha['LOCALIZACION'] ?></p>
                                                        <p class="p4"><?= htmlspecialchars($evento['NOMBRE']) ?></p>
                                                    </div>
                                                    <div class="ticket-btn-container">
                                                        <?php if (isset($_SESSION["user_id"])): ?>
                                                            <button class="ticket-btn">COMPRAR TICKETS</button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </li>
                                        <?php
                                        endwhile;
                                    else:
                                        ?>
                                        <li>
                                            <p>No hay fechas disponibles</p>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>

                            <!-- ABOUT -->
                            <div class="event-about">
                                <h3>ABOUT</h3>
                                <p>
                                    <?= substr(html_entity_decode($evento['TEXT1']), 0, 200) ?>...
                                </p>
                                <a href="eventDetail.php?id=<?= $evento['ID'] ?>" class="view-more">Ver más</a>
                            </div>

                            <hr class="event-divider">
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </section>
    <!-- ------------------------------------------------------------------ FOOTER  --------------------------------------------------------------------------------->



    <footer class="footer">
        <div class="footer-container">
            <div class="footer-container-1-1">
                <p>FIRALIA</p>
            </div>
            <div class="footer-container-1-2">
                <p>Connecta con nosotros!</p>
                <nav>
                    <ul class="ul-apps">
                        <li><a href="#"><img src="images/icons/facebook.png" alt="Facebook"></a></li>
                        <li><a href="#"><img src="images/icons/instagram.png" alt="Instagram"></a></li>
                        <li><a href="#"><img src="images/icons/twitter.png" alt="X"></a></li>
                        <li><a href="#"><img src="images/icons/youtube.png" alt="YouTube"></a></li>
                        <li><a href="#"><img src="images/icons/tiktok.png" alt="TikTok"></a></li>
                    </ul>
                </nav>
            </div>
            <div class="footer-container-1-3">
                <p>Descarga Nuestra App</p>
                <nav>
                    <ul class="ul-download">
                        <li><a href="#"><img src="images/icons/appstore.png" alt="Apple Store"></a></li>
                        <li><a href="#"><img src="images/icons/googleplay.webp" alt="Google Play"></a></li>
                    </ul>
                </nav>
            </div>

            <div class="footer-container-2">
                <ul>
                    <li><a href="#">Política de Privacidad</a></li>
                    <li><a href="#">Política de Compra</a></li>
                    <li><a href="#">Términos de Uso</a></li>
                    <li><a href="#">Política de Cookies</a></li>
                    <li><a href="#">Control de Cookies</a></li>
                    <li>
                        <p>© 2024-2025 FIRALIA. Todos los derechos reservados.</p>
                    </li>
                </ul>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            function showSidebar() {
                const sidebar = document.querySelector('.sidebar');
                sidebar.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }

            function hideSidebar() {
                const sidebar = document.querySelector('.sidebar');
                sidebar.style.display = 'none';
                document.body.style.overflow = '';
            }


            function createPopup(id) {
                let popupNode = document.querySelector(id);
                let overlay = popupNode.querySelector(".overlay");
                let closeBtn = popupNode.querySelector(".close-btn");

                function openPopup() {
                    popupNode.classList.add("active");
                    document.body.style.overflow = 'hidden';
                }

                function closePopup() {
                    popupNode.classList.remove("active");
                    document.body.style.overflow = '';
                }

                if (document.querySelector(".error-message")) {
                    openPopup();
                }

                overlay.addEventListener("click", closePopup);
                closeBtn.addEventListener("click", closePopup);

                return openPopup;
            }


            const img = document.getElementById('logo-nav');
            if (img) {
                img.addEventListener('mouseenter', () => {
                    img.src = 'images/logo2.png';
                });

                img.addEventListener('mouseleave', () => {
                    img.src = 'images/logo2-modified.png';
                });
            }


            document.querySelector(".menu-button")?.addEventListener("click", showSidebar);
            document.querySelector(".sidebar li:first-child")?.addEventListener("click", hideSidebar);

            const openPopupBtn = document.querySelector("#open-popup");
            if (openPopupBtn) {
                let popup = createPopup("#popup");
                openPopupBtn.addEventListener("click", popup);
            }

            if (typeof Swiper !== 'undefined') {
                new Swiper('.swiper', {
                    slidesPerView: 'auto',
                    spaceBetween: 20,
                    freeMode: true,
                });
            }
        });
    </script>

</body>

</html>