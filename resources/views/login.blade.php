<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Login | African Artistic Gymnastics – Yaoundé 2026</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/type" href="{{asset('adminTheme/img/competition.jpeg')}}">
    {{-- Bootstrap + SB Admin 2 --}}
    <link href="{{ asset('adminTheme/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('adminTheme/css/sb-admin-2.min.css') }}" rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            margin: 0;
            background: url('{{ asset("adminTheme/img/monument.jpg") }}') no-repeat center center fixed;
            background-size: cover;
            font-family: "Nunito", sans-serif;
        }

        /* Overlay sombre */
        .bg-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            z-index: 0;
        }

        /* Container centré */
        .login-wrapper {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.35);
            overflow: hidden;
            max-width: 1200px;
            height: 550px;
            width: 100%;
            
        }

        .login-left {
            padding: 20px;
        }

        .login-right {
            background: linear-gradient(135deg, #0a6ddf, #38a80c);
            color: #fff;
            padding: 50px 40px;
            text-align: center;
        }

        .login-input {
            background-color: #fff9c4;
            border-radius: 6px;
        }

        .btn-login {
            background-color: #f1c40f;
            border: none;
            color: #000;
            font-weight: 700;
            border-radius: 6px;
           
            
        }

        .logos img {
            height: 105px;
            margin: 10px;
        }

        @media (max-width: 768px) {
            .login-right {
                display: none;
            }
        }
    </style>
</head>

<body>

<div class="bg-overlay"></div>

<div class="login-wrapper">
    <div class="login-card row no-gutters">

        {{-- LEFT : FORM --}}
        <div class="col-md-6 login-left d-flex align-items-center">
            <div class="w-100">

                <div class="text-center mb-4">
                    <img src="{{ asset('adminTheme/img/competition.jpeg') }}" height="120" alt="Competition Logo">
                </div>

                <h4 class="mb-2 text-center">Sign In</h4>
                <p class="text-muted text-center mb-4">
                    Please input your email and password
                </p>

                {{-- Errors --}}
                @if($errors->any())
                    <div class="alert alert-danger text-center">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.submit') }}">
                    @csrf

                    <div class="form-group">
                        <input type="email"
                               name="email"
                               class="form-control login-input"
                               placeholder="Email"
                               required>
                    </div>

                    <div class="form-group">
                        <input type="password"
                               name="password"
                               class="form-control login-input"
                               placeholder="Password"
                               required>
                    </div>

                    <div class="text-right mb-3">
                        <a href="#" class="small text-warning">
                            Forgot Password ?
                        </a>
                    </div>

                    <button class="btn btn-login btn-block py-2">
                        Sign In
                    </button>
                </form>
            </div>
        </div>

        {{-- RIGHT : MESSAGE --}}
        <div class="col-md-6 login-right d-flex align-items-center">
            <div class="w-100">
                <h2 class="mb-3 font-weight-bold">
                    Move to Inspire
                </h2>

                <p class="mb-4">
                    Inspire to provide an unforgettable experience<br>
                    Inspire to increase gymnastic awareness & competence<br>
                    Inspire to unite in diversity
                </p>

                <div class="logos">
                    <img src="{{ asset('adminTheme/img/fig.png') }}" alt="FIG">
                    <img src="{{ asset('adminTheme/img/minsep.png') }}" alt="Ministry">
                    <img src="{{ asset('adminTheme/img/uag.png') }}" alt="Federation">
                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>
