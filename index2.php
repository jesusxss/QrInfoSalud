<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Registro de Persona</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <style>
    :root {
      --primary-gradient: linear-gradient(135deg,#667eea,#764ba2);
      --bg-light: #f9fafb;
      --text-color: #1f2937;
    }
    .container-mobile {
      box-sizing: border-box;
      width: 100%;
      max-width: 400px;
      margin: 0 auto;
      padding: 1rem;
      background: var(--bg-light);
    }
    .card-custom {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      overflow: hidden;
      margin-bottom: 1rem;
    }
    .card-header-custom {
      background: var(--primary-gradient);
      text-align: center;
      padding: 1rem;
    }
    .card-header-custom i {
      color: #fff;
      font-size: 2rem;
    }
    .card-header-custom h4 {
      color: #fff;
      margin: 0.5rem 0 0;
      font-size: 1.25rem;
    }
    .card-body-custom {
      padding: 1rem;
    }
    .form-label {
      font-weight: 500;
      margin-bottom: 0.25rem;
      display: block;
    }
    .form-control, .form-select {
      width: 100%;
      box-sizing: border-box;
      border-radius: 8px;
      height: 2.75rem;
      font-size: 1rem;
      margin-bottom: 0.75rem;
      border: 1px solid #ccc;
      padding: 0 0.5rem;
    }
    .preview-img {
      display: block;
      margin: 0.5rem auto;
      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .btn-action {
      display: block;
      width: 100%;
      background: var(--primary-gradient);
      color: #fff;
      border: none;
      border-radius: 8px;
      height: 3rem;
      font-size: 1rem;
      font-weight: 500;
      margin-top: 0.5rem;
      transition: opacity 0.3s;
    }
    .btn-action:hover {
      opacity: 0.9;
    }
    
    /* Estilos para el modal */
    .modal-custom {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: rgba(0,0,0,0.5);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 1000;
      opacity: 0;
      transition: opacity 0.3s ease;
    }
    
    .modal-content-custom {
      background-color: #fff;
      border-radius: 12px;
      width: 90%;
      max-width: 400px;
      overflow: hidden;
      transform: translateY(-20px);
      transition: transform 0.3s ease;
      box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }
    
    .modal-show {
      opacity: 1;
      display: flex;
    }
    
    .modal-show .modal-content-custom {
      transform: translateY(0);
    }
    
    .modal-header-custom {
      padding: 1rem;
      background: linear-gradient(135deg, #667eea, #764ba2);
      color: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .modal-header-custom h5 {
      margin: 0;
      font-size: 1.25rem;
    }
    
    .modal-close-btn {
      background: none;
      border: none;
      color: white;
      font-size: 1.5rem;
      cursor: pointer;
      padding: 0 0.5rem;
    }
    
    .modal-body-custom {
      padding: 1.5rem;
      text-align: center;
    }
    
    .success-icon {
      font-size: 3rem;
      color: #4CAF50;
      margin-bottom: 1rem;
    }
    
    .error-icon {
      font-size: 3rem;
      color: #F44336;
      margin-bottom: 1rem;
    }
    
    .modal-footer-custom {
      padding: 1rem;
      display: flex;
      justify-content: center;
      border-top: 1px solid #eee;
    }
    
    .modal-action-btn {
      background: linear-gradient(135deg, #667eea, #764ba2);
      color: white;
      border: none;
      border-radius: 8px;
      padding: 0.75rem 1.5rem;
      font-size: 1rem;
      cursor: pointer;
      transition: all 0.3s;
      min-width: 120px;
    }
    
    .modal-action-btn:hover {
      opacity: 0.9;
      transform: translateY(-2px);
    }
    
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    
    @keyframes slideIn {
      from { transform: translateY(-20px); }
      to { transform: translateY(0); }
    }
    
    .modal-show {
      animation: fadeIn 0.3s ease forwards;
    }
    
    .modal-show .modal-content-custom {
      animation: slideIn 0.3s ease forwards;
    }
    
    @media (max-width: 400px) {
      .container-mobile,
      .card-header-custom,
      .card-body-custom {
        padding: 0.75rem;
      }
      .form-control, .form-select {
        height: 2.5rem;
        font-size: 0.9rem;
      }
      .btn-action {
        height: 2.5rem;
        font-size: 0.9rem;
      }
      .preview-img {
        width: 100px;
        height: 100px;
      }
    }
  </style>
</head>
<body>

<?php session_start(); ?>

<div class="container-mobile">
  <div class="card-custom">
    <div class="card-header-custom">
      <i class="fas fa-user-edit"></i>
      <h4>Registro de Persona</h4>
    </div>
  </div>

  <div class="card-body-custom">
    <form id="registroForm" action="save.php" method="POST" enctype="multipart/form-data" novalidate>
      <label for="dni" class="form-label">DNI</label>
      <input type="text" id="dni" name="dni" class="form-control" maxlength="8" placeholder="12345678" required>

      <label for="nombre_completo" class="form-label">Nombre Completo</label>
      <input type="text" id="nombre_completo" name="nombre_completo" class="form-control" placeholder="Nuñez Labio Jesús Luis" required>

      <label class="form-label">Foto</label>
      <img id="previewFoto" src="https://via.placeholder.com/120" alt="Preview" class="preview-img">
      <input type="file" id="foto" name="foto" class="form-control" accept="image/*" required>

      <label for="enfermedad_actual" class="form-label">Enfermedad Actual <small>(Opcional)</small></label>
      <input type="text" id="enfermedad_actual" name="enfermedad_actual" class="form-control" placeholder="Diabetes">

      <label for="alergias" class="form-label">Alergias Conocidas <small>(Opcional)</small></label>
      <input type="text" id="alergias" name="alergias" class="form-control" placeholder="Penicilina">

      <label for="seguro_medico" class="form-label">Seguro Médico</label>
      <select id="seguro_medico" name="seguro_medico" class="form-select" required>
        <option value="Esalud">Esalud</option>
        <option value="Sis">Sis</option>
        <option value="Otros">Otros</option>
      </select>
      <div id="otro_seguro_div" style="display:none;">
        <input type="text" id="otro_seguro" name="otro_seguro" class="form-control" placeholder="Nombre del seguro">
      </div>

      <label for="tipo_sangre" class="form-label">Tipo de Sangre</label>
      <select id="tipo_sangre" name="tipo_sangre" class="form-select" required>
        <option value="A+">A+</option><option value="A-">A-</option>
        <option value="B+">B+</option><option value="B-">B-</option>
        <option value="O+">O+</option><option value="O-">O-</option>
        <option value="AB+">AB+</option><option value="AB-">AB-</option>
      </select>

      <label class="form-label">Donador de Órganos</label>
      <div style="margin-bottom:0.75rem;">
        <label><input type="radio" name="donador_org" value="si" required> Sí</label>
        <label style="margin-left:1rem;"><input type="radio" name="donador_org" value="no"> No</label>
      </div>

      <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
      <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="form-control" required>

      <label for="contacto_emergencia" class="form-label">Contacto de Emergencia</label>
      <input type="tel" id="contacto_emergencia" name="contacto_emergencia" class="form-control" placeholder="987654321" maxlength="9" required>

      <label for="parentesco_emergencia" class="form-label">Parentesco / 2do Número</label>
      <input type="text" id="parentesco_emergencia" name="parentesco_emergencia" class="form-control" placeholder="Hermano/a" maxlength="9" required>

      <label for="intervenciones_quirurgicas" class="form-label">Intervenciones Quirúrgicas Anteriores</label>
      <input type="text" id="intervenciones_quirurgicas" name="intervenciones_quirurgicas" class="form-control" placeholder="Ej. Apendicitis, Cirugía de Rodilla">

      <label for="direccion_actual" class="form-label">Dirección Actual</label>
      <input type="text" id="direccion_actual" name="direccion_actual" class="form-control" placeholder="Av. Pardo 123, Lima, Perú">

      <button type="submit" id="btnRegistrar" class="btn-action" disabled>
        <span id="btnText">Registrar</span>
        <span id="btnSpinner" style="display:none;">
          <i class="fas fa-spinner fa-spin"></i> Procesando...
        </span>
      </button>
    </form>
  </div>
</div>

<!-- Modal para mensajes -->
<div id="registroModal" class="modal-custom" role="dialog" aria-modal="true" aria-labelledby="modalTitle" aria-describedby="modalMessage">
  <div class="modal-content-custom">
    <div class="modal-header-custom">
      <h5 id="modalTitle">¡Registro Exitoso!</h5>
      <button type="button" class="modal-close-btn" aria-label="Cerrar">&times;</button>
    </div>
    <div class="modal-body-custom">
      <i id="modalIcon" class="fas fa-check-circle success-icon"></i>
      <p id="modalMessage">El registro se ha completado correctamente.</p>
    </div>
    <div class="modal-footer-custom">
      <button type="button" class="modal-action-btn">Aceptar</button>
    </div>
  </div>
</div>

<script>
(function() {
  // Variables globales del módulo
  const form = document.getElementById('registroForm');
  let redirectAfterSuccess = '';
  
  // Funciones auxiliares
  function toggleSeguroInput() {
    const seguroSelect = document.getElementById('seguro_medico');
    const otroSeguroDiv = document.getElementById('otro_seguro_div');
    otroSeguroDiv.style.display = seguroSelect.value === 'Otros' ? 'block' : 'none';
    updateButtonState();
  }
  
  function previewImage(input, previewId) {
    const file = input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => document.getElementById(previewId).src = e.target.result;
    reader.readAsDataURL(file);
    updateButtonState();
  }
  
  function showLoading(show) {
    const btnText = document.getElementById('btnText');
    const btnSpinner = document.getElementById('btnSpinner');
    const btn = document.getElementById('btnRegistrar');
    
    if (show) {
      btnText.style.display = 'none';
      btnSpinner.style.display = 'inline';
      btn.disabled = true;
    } else {
      btnText.style.display = 'inline';
      btnSpinner.style.display = 'none';
      updateButtonState();
    }
  }
  
  function showModal(title, message, isSuccess) {
    const modal = document.getElementById('registroModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');
    const modalIcon = document.getElementById('modalIcon');
    
    // Configurar contenido del modal
    modalTitle.textContent = title;
    modalMessage.textContent = message;
    
    // Cambiar icono y colores según el estado
    modalIcon.className = isSuccess ? 'fas fa-check-circle success-icon' : 'fas fa-times-circle error-icon';
    modalIcon.style.color = isSuccess ? '#4CAF50' : '#F44336';
    
    // Mostrar modal con animación
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    setTimeout(() => modal.classList.add('modal-show'), 10);
    
    // Configurar eventos de cierre
    const closeModal = () => {
      modal.classList.remove('modal-show');
      setTimeout(() => {
        modal.style.display = 'none';
        document.body.style.overflow = '';
        
		//visualización previa de fotografia
        if (isSuccess) {
          form.reset();
          document.getElementById('previewFoto').src = 'https://via.placeholder.com/120';
          updateButtonState();
          
          if (redirectAfterSuccess) {
            if (window.AndroidInterface) {
              window.AndroidInterface.navigateTo(redirectAfterSuccess);
            } else {
              window.location.href = redirectAfterSuccess;
            }
          }
        }
      }, 300);
    };
    
    document.querySelector('.modal-close-btn').onclick = closeModal;
    document.querySelector('.modal-action-btn').onclick = closeModal;
    modal.onclick = e => e.target === modal && closeModal();
  }
  
  function updateButtonState() {
    document.getElementById('btnRegistrar').disabled = !form.checkValidity();
  }
  
  // Inicialización
  function init() {
    // Configurar eventos del formulario
    document.getElementById('seguro_medico').addEventListener('change', toggleSeguroInput);
    document.getElementById('foto').addEventListener('change', function() {
      previewImage(this, 'previewFoto');
    });
    
    //Autocompletar nombre al ingresar DNI
    document.getElementById('dni').addEventListener('input', function() {
      const dni = this.value.trim();
      if (dni.length === 8) {
        fetch('api-proxy.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({ dni })
        })
        .then(res => res.ok ? res.json() : Promise.reject(res.statusText))
        .then(data => {
          if (data.prenombres) {
            document.getElementById('nombre_completo').value = data.prenombres;
            updateButtonState();
          }
        })
        .catch(err => console.error('Error al autocompletar nombre:', err));
      }
    }); 
    
    // Validación en tiempo real
    const campos = form.querySelectorAll('[required]');
    campos.forEach(c => {
      c.addEventListener('input', updateButtonState);
      c.addEventListener('change', updateButtonState);
    });
    
    // Manejar envío del formulario
    form.addEventListener('submit', async function(e) {
      e.preventDefault();
      showLoading(true);
      
      try {
        const formData = new FormData(form);
        const response = await fetch('save.php', { method: 'POST', body: formData });
        const data = await response.json();
        
        if (data.success) {
          if (data.redirect) redirectAfterSuccess = data.redirect;
          showModal('¡Registro Exitoso!', data.message || 'Registro completado correctamente', true);
        } else {
          throw new Error(data.message || 'Error desconocido');
        }
      } catch (error) {
        showModal('Error en el Registro', error.message, false);
      } finally {
        showLoading(false);
      }
    });
    
    // Estado inicial
    toggleSeguroInput();
    updateButtonState();
  }
  
  // Iniciar cuando el DOM esté listo
  if (document.readyState === 'complete') {
    init();
  } else {
    document.addEventListener('DOMContentLoaded', init);
  }
})();
</script>

</body>
</html>