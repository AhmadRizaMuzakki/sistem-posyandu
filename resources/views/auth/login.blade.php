<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<title>Login - {{ config('app.name', 'Laravel') }}</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">
<!--===============================================================================================-->
	<link rel="icon" type="image/png" href="{{ asset('Login_v1/images/icons/favicon.ico') }}"/>
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="{{ asset('Login_v1/vendor/bootstrap/css/bootstrap.min.css') }}">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="{{ asset('Login_v1/fonts/font-awesome-4.7.0/css/font-awesome.min.css') }}">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="{{ asset('Login_v1/vendor/animate/animate.css') }}">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="{{ asset('Login_v1/vendor/css-hamburgers/hamburgers.min.css') }}">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="{{ asset('Login_v1/vendor/select2/select2.min.css') }}">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="{{ asset('Login_v1/css/util.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('Login_v1/css/main.css') }}">
<!--===============================================================================================-->
</head>
<body>

	<div class="limiter">
		<div class="container-login100">
			<div class="wrap-login100">
				<div class="login100-pic js-tilt" data-tilt>
					<img src="{{ asset('Login_v1/images/img-01.png') }}" alt="IMG">
				</div>

				<form class="login100-form validate-form" method="POST" action="{{ route('login') }}">
					@csrf

					<span class="login100-form-title">
						Member Login
					</span>

					<!-- Session Status -->
					@if (session('status'))
						<div class="alert alert-success mb-3" role="alert">
							{{ session('status') }}
						</div>
					@endif

					<!-- Email Address -->
					<div class="wrap-input100 validate-input @error('email') alert-validate @enderror" data-validate="@error('email') {{ $message }} @else Valid email is required: ex@abc.xyz @enderror">
						<input class="input100" type="email" name="email" placeholder="Email" value="{{ old('email') }}" required autofocus autocomplete="username">
						<span class="focus-input100"></span>
						<span class="symbol-input100">
							<i class="fa fa-envelope" aria-hidden="true"></i>
						</span>
					</div>

					<!-- Password -->
					<div class="wrap-input100 validate-input @error('password') alert-validate @enderror" data-validate="@error('password') {{ $message }} @else Password is required @enderror">
						<input class="input100" type="password" name="password" placeholder="Password" required autocomplete="current-password">
						<span class="focus-input100"></span>
						<span class="symbol-input100">
							<i class="fa fa-lock" aria-hidden="true"></i>
						</span>
					</div>

					<!-- Remember Me -->
					<div class="text-left p-b-10">
						<label class="txt1" for="remember_me" style="cursor: pointer;">
							<input id="remember_me" type="checkbox" name="remember" style="margin-right: 5px;">
							Remember me
						</label>
					</div>

					<div class="container-login100-form-btn">
						<button type="submit" class="login100-form-btn">
							Login
						</button>
					</div>

					@if (Route::has('password.request'))
					<div class="text-center p-t-12">
						<span class="txt1">
							Forgot
						</span>
						<a class="txt2" href="{{ route('password.request') }}">
							Username / Password?
						</a>
					</div>
					@endif

					@if (Route::has('register'))
					<div class="text-center p-t-136">
						<a class="txt2" href="{{ route('register') }}">
							Create your Account
							<i class="fa fa-long-arrow-right m-l-5" aria-hidden="true"></i>
						</a>
					</div>
					@endif
				</form>
			</div>
		</div>
	</div>

<!--===============================================================================================-->
	<script src="{{ asset('Login_v1/vendor/jquery/jquery-3.2.1.min.js') }}"></script>
<!--===============================================================================================-->
	<script src="{{ asset('Login_v1/vendor/bootstrap/js/popper.js') }}"></script>
	<script src="{{ asset('Login_v1/vendor/bootstrap/js/bootstrap.min.js') }}"></script>
<!--===============================================================================================-->
	<script src="{{ asset('Login_v1/vendor/select2/select2.min.js') }}"></script>
<!--===============================================================================================-->
	<script src="{{ asset('Login_v1/vendor/tilt/tilt.jquery.min.js') }}"></script>
	<script>
		$('.js-tilt').tilt({
			scale: 1.1
		})
	</script>
<!--===============================================================================================-->
	<script src="{{ asset('Login_v1/js/main.js') }}"></script>

</body>
</html>
