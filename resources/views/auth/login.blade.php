<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<title>Login - Sistem Posyandu</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<meta name="description" content="Login ke Sistem Posyandu - Manajemen data posyandu">
	<link rel="icon" type="image/jpeg" href="{{ asset('images/home.jpeg') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('Login_v1/vendor/bootstrap/css/bootstrap.min.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('Login_v1/fonts/font-awesome-4.7.0/css/font-awesome.min.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('Login_v1/vendor/animate/animate.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('Login_v1/css/util.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('Login_v1/css/main.css') }}">
	<style>
		/* Fallback minimal jika CSS eksternal gagal (Hostinger) */
		body { margin: 0; min-height: 100vh; background: linear-gradient(-135deg, #c850c0, #4158d0); }
	</style>
</head>
<body>

	<div class="limiter">
		<div class="container-login100">
			<div class="wrap-login100">
				<div class="login100-pic js-tilt" data-tilt>
					<img src="{{ asset('Login_v1/images/img-01.png') }}" alt="Sistem Posyandu" loading="lazy">
				</div>

				<form class="login100-form validate-form" method="POST" action="{{ route('login') }}">
					@csrf

					<span class="login100-form-title">Sistem Posyandu</span>
					<p class="txt1" style="text-align: center; margin-top: -40px; margin-bottom: 24px;">Silakan login dengan Email</p>

					@if (session('status'))
						<div class="alert alert-success mb-3" role="alert">{{ session('status') }}</div>
					@endif

					@if ($errors->any())
						<div class="alert alert-danger mb-3" role="alert">
							@foreach ($errors->all() as $err) {{ $err }}@if (!$loop->last)<br>@endif @endforeach
						</div>
					@endif

					<div class="wrap-input100 validate-input @error('email') alert-validate @enderror" data-validate="@error('email'){{ $message }}@else Email wajib diisi @enderror">
						<input class="input100" type="text" name="email" placeholder="Email" value="{{ old('email') }}" required autofocus autocomplete="username">
						<span class="focus-input100"></span>
						<span class="symbol-input100"><i class="fa fa-user" aria-hidden="true"></i></span>
					</div>

					<div class="wrap-input100 validate-input @error('password') alert-validate @enderror" data-validate="@error('password'){{ $message }}@else Password wajib diisi @enderror">
						<input class="input100" type="password" name="password" placeholder="Password" required autocomplete="current-password">
						<span class="focus-input100"></span>
						<span class="symbol-input100"><i class="fa fa-lock" aria-hidden="true"></i></span>
					</div>

					<div class="text-left p-b-10">
						<label class="txt1" for="remember_me" style="cursor: pointer;">
							<input id="remember_me" type="checkbox" name="remember" style="margin-right: 5px;">
							Ingat saya
						</label>
					</div>

					<div class="container-login100-form-btn">
						<button type="submit" class="login100-form-btn">Login</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<script src="{{ asset('Login_v1/vendor/jquery/jquery-3.2.1.min.js') }}"></script>
	<script src="{{ asset('Login_v1/vendor/bootstrap/js/popper.js') }}"></script>
	<script src="{{ asset('Login_v1/vendor/bootstrap/js/bootstrap.min.js') }}"></script>
	<script src="{{ asset('Login_v1/vendor/tilt/tilt.jquery.min.js') }}"></script>
	<script>
		if (typeof $ !== 'undefined' && $.fn.tilt) {
			$('.js-tilt').tilt({ scale: 1.1 });
		}
	</script>
	<script src="{{ asset('Login_v1/js/main.js') }}"></script>

</body>
</html>
