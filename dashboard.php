<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Dashboard - LoqQRSalud</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.4.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <style>
    :root {
      --primary-color: #4f46e5;
      --bg-light: #f9fafb;
      --text-color: #111827;
    }
    body { margin:0; padding:0; background:var(--bg-light); color:var(--text-color); font-family:'Segoe UI',sans-serif; }
    #content-container { padding:1rem; padding-bottom:4.5rem; }
    .nav-bottom { position:fixed; bottom:0; left:0; right:0; height:3.5rem; background:#fff;
                  display:flex; justify-content:space-around; align-items:center;
                  box-shadow:0 -2px 8px rgba(0,0,0,0.1); z-index:1000; }
    .nav-bottom a { flex:1; text-align:center; color:var(--text-color); font-size:0.875rem; text-decoration:none; }
    .nav-bottom a.active { color:var(--primary-color); }
    .nav-bottom i { display:block; font-size:1.25rem; margin-bottom:0.2rem; }
  </style>
</head>
<body>
  <div id="content-container"></div>

  <nav class="nav-bottom">
    <a href="#" id="nav-sugerencias" class="active"><i class="fas fa-lightbulb"></i><small>Sugerencias</small></a>
    <a href="index2.php" id="nav-generar"><i class="fas fa-qrcode"></i><small>Generar</small></a>
    <a href="qr_generados.php" id="nav-misqr"><i class="fas fa-file-alt"></i><small>Mis Qr</small></a>
    <a href="personalizar_qr.php" id="nav-personalizar"><i class="fas fa-cogs"></i><small>Personalizar</small></a>
  </nav>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    $(function() {
      const suggestionsHTML = `
        <div class="page-header" style="background: var(--primary-color); color:#fff; padding:1rem; text-align:center;">
          <h2>Dashboard</h2>
          <p class="mb-0">Este proyecto/aplicativo fue desarrollado con la finalidad de brindar una ayuda adicional frente a una situación de riesgo u accidente. Úselo con precaución y responsabilidad.</p>
        <p> Test deslogueo <a href="logout.php">Logout</a></p>
          </div>`;

      // Carga inicial
      $('#content-container').html(suggestionsHTML);

      // Función para cargar páginas
      function loadPage(page) {
        const uniquePage = page + (page.includes('?') ? '&' : '?') + '_=' + Date.now();
        
        $('#content-container').html(`
          <div class="text-center mt-5">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Cargando...</span>
            </div>
          </div>`);

        $.ajax({
          url: uniquePage,
          cache: false,
          success: function(response) {
            $('#content-container').html(response);
            // Inicializar módulos específicos si existen
            if (typeof initRegistroForm === 'function') initRegistroForm();
            if (typeof initPersonalizarQR === 'function') initPersonalizarQR();
          },
          error: function() {
            $('#content-container').html('<div class="alert alert-danger">Error al cargar la página</div>');
          }
        });
      }

      // Navegación
      $('.nav-bottom a').on('click', function(e) {
        e.preventDefault();
        const page = $(this).attr('href');
        $('.nav-bottom a').removeClass('active');
        $(this).addClass('active');
        page ? loadPage(page) : $('#content-container').html(suggestionsHTML);
      });
    });
  </script>
</body>
</html>