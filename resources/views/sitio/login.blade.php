<!DOCTYPE html>
<html lang="en">

<head>
    <title>PRODUCTOS QUIMICOS MI FRAGANCIA</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--===============================================================================================-->
    <link rel="icon" type="image/png" href="{{ asset('plantilla/images/app/counter.png') }}" />
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="{{ asset('plantilla/login/vendor/bootstrap/css/bootstrap.min.css') }}">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css"
        href="{{ asset('plantilla/login/fonts/font-awesome-4.7.0/css/font-awesome.min.css') }}">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css"
        href="{{ asset('plantilla/login/fonts/Linearicons-Free-v1.0.0/icon-font.min.css') }}">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="{{ asset('plantilla/login/vendor/animate/animate.css') }}">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css"
        href="{{ asset('plantilla/login/vendor/css-hamburgers/hamburgers.min.css') }}">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css"
        href="{{ asset('plantilla/login/vendor/animsition/css/animsition.min.css') }}">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="{{ asset('plantilla/login/vendor/select2/select2.min.css') }}">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css"
        href="{{ asset('plantilla/login/vendor/daterangepicker/daterangepicker.css') }}">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="{{ asset('plantilla/login/css/util.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plantilla/login/css/main.css') }}">
    <!--===============================================================================================-->
</head>

<body>

    <div class="limiter">
        <div class="container-login100">
            <div class="wrap-login100" style="background: #ffffff">
                <div class="login100-form-title"
                    style="background-image: url({{ asset('plantilla/login/images/bg-01.jpg') }});">
                    <span class="login100-form-title-1">
                        Productos químicos mi fragancia
                    </span>
                </div>
                {{ Form::open(['method' => 'post', 'route' => 'usuario/auth', 'files' => true, 'class' => 'login100-form validate-form']) }}

                @if ($errors->first('error') != '')
                    <div style="width: 100%" class="alert alert-danger" id="alert_error">
                        {{ $errors->first('error') }}
                    </div>
                    <script>
                        setTimeout(function() {
                            $("#alert_error").fadeOut();
                        }, 3000);
                    </script>
                @endif
                <div class="wrap-input100 validate-input m-b-26" data-validate="Usuario obligatorio">
                    {{-- <span class="label-input100">Usuario</span> --}}
                    <input class="input100" style="text-align: center" type="text" name="usuario"
                        placeholder="Enter username">
                    <span class="focus-input100"></span>
                </div>

                <div class="wrap-input100 validate-input m-b-18" data-validate="Contraseña obligatoria">
                    {{-- <span class="label-input100">Contraseña</span> --}}
                    <input class="input100" style="text-align: center" type="password" name="clave"
                        placeholder="Enter password">
                    <span class="focus-input100"></span>
                </div>

                <div class="container-login100-form-btn">
                    <button class="login100-form-btn">
                        Ingresar
                    </button>
                </div>

                {{ Form::close() }}
            </div>
        </div>
    </div>

    <!--===============================================================================================-->
    <script src="{{ asset('plantilla/login/vendor/jquery/jquery-3.2.1.min.js') }}"></script>
    <!--===============================================================================================-->
    <script src="{{ asset('plantilla/login/vendor/animsition/js/animsition.min.js') }}"></script>
    <!--===============================================================================================-->
    <script src="{{ asset('plantilla/login/vendor/bootstrap/js/popper.js') }}"></script>
    <script src="{{ asset('plantilla/login/vendor/bootstrap/js/bootstrap.min.js') }}"></script>
    <!--===============================================================================================-->
    <script src="{{ asset('plantilla/login/vendor/select2/select2.min.js') }}"></script>
    <!--===============================================================================================-->
    <script src="{{ asset('plantilla/login/vendor/daterangepicker/moment.min.js') }}"></script>
    <script src="{{ asset('plantilla/login/vendor/daterangepicker/daterangepicker.js') }}"></script>
    <!--===============================================================================================-->
    <script src="{{ asset('plantilla/login/vendor/countdowntime/countdowntime.js') }}"></script>
    <!--===============================================================================================-->
    <script src="{{ asset('plantilla/login/js/main.js') }}"></script>

</body>

</html>
