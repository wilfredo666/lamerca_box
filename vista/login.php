<body class="hold-transition login-page">
  <div id="back"></div>
  <div class="login-box">
    <div class="card">
      <div class="card-body login-card-body card-outline card-primary">
        <img src="assets/img/logo.jpg" alt="La Merca BOX" class="brand-image img-circle elevation-3" style="margin: 0 auto; display: block;" width="120">
        <h4 class="login-box-msg"> <b>La Merca</b> BOX</h4>
        <p class="login-box-msg" style="color: #dad7d7;">Recepcion y entrega de productos</p>

        <form action="#" method="post">
          <div class="input-group mb-3">
            <input type="text" class="form-control" placeholder="Login de usuario" name="usuario" id="usuario">
            <div class="input-group-append">
              <div class="input-group-text">
                <span class="fas fa-user"></span>
              </div>
            </div>
          </div>
          <div class="input-group mb-3">
            <input type="password" class="form-control" name="password" id="password" placeholder="Ingrese su contraseña">
            <div class="input-group-append">
              <div class="input-group-text" id="toggle-password">
                <!--<span class="fas fa-lock"></span>-->

                <span class="fas fa-eye" id="toggle-password-icon"></span>

              </div>
            </div>
          </div>
          <div class="input-group mb-3">
            <select name="almacen" id="almacen" class="form-control">
              <option value="">Seleccionar Almacen</option>
              <?php
              $registros = ControladorAlmacen::ctrMostrarRegistros();
              foreach ($registros as $value) {
              ?>
                <option value="<?php echo $value["nombre_almacen"] . "-" . $value["id_almacen"]; ?>"><?php echo $value["nombre_almacen"] . " - " . $value["descripcion"]; ?></option>
              <?php
              }
              ?>
            </select>
            <div class="input-group-append">
              <div class="input-group-text">
                <span class="fas fa-store"></span>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-3">
            </div>
            <!-- /.col -->
            <div class="col-6">
              <button type="submit" class="btn btn-primary btn-block">
              <span class="fas fa-sign-in-alt"></span>
              <b>ACCEDER</b></button>
            </div>
            <!-- /.col -->
          </div>
          <?php
          $login = new ControladorUsuario();
          $login->ctrIngresoUsuario();
          ?>

        </form>
      </div>
      <!-- /.login-card-body -->
    </div>
  </div>
  <!-- /.login-box -->

  <!-- jQuery -->
  <script src="assets/js/jquery.min.js"></script>
  <!-- Bootstrap 4 -->
  <script src="assets/js/bootstrap.bundle.min.js"></script>
  <!-- AdminLTE App -->
  <script src="assets/js/adminlte.min.js"></script>

  <script>
    document.getElementById('toggle-password').addEventListener('click', function(e) {
      const passwordInput = document.getElementById('password');
      const passwordIcon = document.getElementById('toggle-password-icon');
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        passwordIcon.classList.remove('fa-eye');
        passwordIcon.classList.add('fa-eye-slash');
      } else {
        passwordInput.type = 'password';
        passwordIcon.classList.remove('fa-eye-slash');
        passwordIcon.classList.add('fa-eye');
      }
    });
  </script>
</body>

</html>