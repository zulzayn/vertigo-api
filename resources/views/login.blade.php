<!--
=========================================================
* Argon Design System - v1.2.0
=========================================================

* Product Page: https://www.creative-tim.com/product/argon-design-system
* Copyright 2020 Creative Tim (http://www.creative-tim.com)

Coded by www.creative-tim.com

=========================================================

* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software. -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
     <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{ url('img/logoMain2.png') }}">
    <title>Vertigo</title>
    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
    <link href="https://use.fontawesome.com/releases/v5.0.6/css/all.css" rel="stylesheet">
    <!-- Nucleo Icons -->
    <link type="text/css" href="{{ url('css/nucleo-icons.css') }}" rel="stylesheet" />
    <link href="{{ url('/css/nucleo-svg.css') }}" rel="stylesheet" />
    <!-- Font Awesome Icons -->
    <link href="{{ url('/css/font-awesome.css')}}" rel="stylesheet" />
    <link href="{{ url('/css/nucleo-svg.css')}}" rel="stylesheet" />
    <!-- CSS Files -->
    <link href="{{( url('/css/argon-design-system.css?v=1.2.0'))}}" rel="stylesheet" />
    <link href="{{( url('/css/generalCustom.css'))}}" rel="stylesheet" />
    <!-- Toastr style -->
    <link href="{{ url('/js/plugins/toastr/toastr.min.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">

    <!-- The core Firebase JS SDK is always required and must be listed first -->
    <script src="https://www.gstatic.com/firebasejs/7.9.2/firebase-app.js"></script>

    <script src="https://www.gstatic.com/firebasejs/7.9.1/firebase-messaging.js"></script>

    <link rel="manifest" href="manifest.json">

</head>
<img src="{{ url('/img/header.png') }}" alt="Avatar" style="width:10%;position:fixed">

<body class="login-page mainColor">

    <section class="section section-shaped section-lg">
        <div class="shape">

            <img src="{{ url('/img/sideLogo.png') }}" alt="Avatar" style="width:12%;margin-left:44%;margin-top:5%">
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
        </div>
       
        <div class="container pt-lg-7" style="margin-top: 13%;">
            <div class="row justify-content-center">
                <div class="col-lg-5">
                    <div class="card shadow border-0">
                        <div class="card-body px-lg-5 py-lg-5">
                            {!! Form::open(['action' => 'Auth\LoginController@login', 'method' => 'POST','class' => 'form-horizontal', 'enctype' => 'multipart/form-data']) !!}
                            @csrf

                                <div class="form-group mb-3">
                                    <div class="input-group input-group-alternative">
                                        <input class="form-control" id="customBorderBottomLine" name="email" placeholder="Email" type="email">
                                    </div>
                                </div>
                                <div class="form-group focused">
                                    <div class="input-group input-group-alternative">
                                        <input class="form-control" id="customBorderBottomLine" name="password" placeholder="Password" type="password">
                                    </div>
                                </div>
                                <input type="hidden" name="device_token" id="device_token" value="NOTOKEN">
                                <div class="text-center">
                                    <button type="submit" class="btn btn-icon mainColor mainFontColor my-4" style="text-transform: unset">
                                        <span class="btn-inner--icon"><i class="ni ni-check-bold"></i></span>
                                        <span class="btn-inner--text">Login</span>
                                    </button>
                                </div>

                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
       
    </section>
    
     <!-- Toastr script -->
     <script src="{{ url('/js/plugins/toastr/toastr.min.js') }}"></script>
    <script src="{{ url('/js/core/jquery.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('/js/core/popper.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('/js/core/bootstrap.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('/js/plugins/perfect-scrollbar.jquery.min.js') }}"></script>
    <!--  Plugin for Switches, full documentation here: http://www.jque.re/plugins/version3/bootstrap.switch/ -->
    <script src="{{url('/js/plugins/bootstrap-switch.js') }}"></script>
    <!--  Plugin for the Sliders, full documentation here: http://refreshless.com/nouislider/ -->
    <script src="{{ url('/js/plugins/nouislider.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('/js/plugins/moment.min.js') }}"></script>
    <script src="{{ url('/js/plugins/datetimepicker.js') }}" type="text/javascript"></script>
    <script src="{{ url('/js/plugins/bootstrap-datepicker.min.js') }}"></script>
    <!-- Control Center for Argon UI Kit: parallax effects, scripts for the example pages etc -->
    <!--  Google Maps Plugin    -->
    <script src="{{ url('/js/argon-design-system.min.js?v=1.2.0') }}" type="text/javascript"></script>
    <script src="https://cdn.trackjs.com/agent/v3/latest/t.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="{{secure_asset('js/firebase.js')}}"></script>
    <script>
         toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": false,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
            }
            
        @if($errors->any())

            @foreach($errors->all() as $error)
                toastr["error"]('{{$error}}') 
            @endforeach

        @endif

        @if(session('success'))
            toastr["success"]('{{session("success")}}');
        @endif

        @if(session('error'))
            toastr["error"]('{{session("error")}}')       
        @endif

  
    
    </script>
</body>

</html>