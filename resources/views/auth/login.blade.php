<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<title>Login - Sistem Posyandu</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<meta name="description" content="Login ke Sistem Posyandu - Manajemen data posyandu">
	<link rel="icon" type="image/png" href="{{ asset('images/home.png') }}">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@500;600;700;800&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
	<style>
		:root {
			--primary: #0D9488;
			--primary-dark: #0F766E;
			--primary-soft: #CCFBF1;
			--accent: #FDBA74;
			--ink: #134E4A;
			--muted: #5B7A76;
			--line: #D5EBE7;
			--field: #F0FDFA;
			--white: #ffffff;
			--danger: #B91C1C;
			--danger-bg: #FEF2F2;
			--danger-border: #FECACA;
			--shadow: 0 24px 60px rgba(13, 148, 136, 0.18);
			--radius: 24px;
		}

		* { box-sizing: border-box; margin: 0; padding: 0; }

		body {
			min-height: 100vh;
			font-family: 'Nunito', sans-serif;
			color: var(--ink);
			background:
				radial-gradient(ellipse 80% 60% at 10% 20%, rgba(253, 186, 116, 0.35), transparent 55%),
				radial-gradient(ellipse 70% 50% at 90% 80%, rgba(45, 212, 191, 0.28), transparent 50%),
				linear-gradient(145deg, #0F766E 0%, #0D9488 42%, #14B8A6 72%, #2DD4BF 100%);
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 24px 16px;
			overflow-x: hidden;
			position: relative;
		}

		body::before {
			content: '';
			position: fixed;
			inset: 0;
			background-image:
				radial-gradient(circle at 20% 30%, rgba(255,255,255,0.08) 0 2px, transparent 3px),
				radial-gradient(circle at 70% 60%, rgba(255,255,255,0.06) 0 1.5px, transparent 2.5px),
				radial-gradient(circle at 40% 80%, rgba(255,255,255,0.05) 0 2px, transparent 3px);
			background-size: 120px 120px, 90px 90px, 140px 140px;
			pointer-events: none;
			animation: drift 28s linear infinite;
		}

		@keyframes drift {
			from { transform: translate3d(0, 0, 0); }
			to { transform: translate3d(-40px, -24px, 0); }
		}

		@keyframes rise {
			from { opacity: 0; transform: translateY(18px) scale(0.98); }
			to { opacity: 1; transform: translateY(0) scale(1); }
		}

		@keyframes float {
			0%, 100% { transform: translateY(0); }
			50% { transform: translateY(-8px); }
		}

		.login-shell {
			width: min(920px, 100%);
			background: var(--white);
			border-radius: var(--radius);
			box-shadow: var(--shadow);
			display: grid;
			grid-template-columns: 1.05fr 1fr;
			overflow: hidden;
			position: relative;
			z-index: 1;
			animation: rise 0.55s ease-out both;
		}

		.login-visual {
			background:
				radial-gradient(circle at 30% 25%, rgba(253, 186, 116, 0.35), transparent 45%),
				linear-gradient(160deg, #CCFBF1 0%, #99F6E4 45%, #5EEAD4 100%);
			padding: 48px 40px;
			display: flex;
			flex-direction: column;
			justify-content: center;
			align-items: center;
			gap: 28px;
			position: relative;
			overflow: hidden;
		}

		.login-visual::after {
			content: '';
			position: absolute;
			width: 220px;
			height: 220px;
			border-radius: 50%;
			border: 1.5px solid rgba(13, 148, 136, 0.18);
			top: -40px;
			right: -50px;
			animation: float 7s ease-in-out infinite;
		}

		.visual-copy {
			text-align: center;
			max-width: 280px;
		}

		.visual-copy h2 {
			font-family: 'Outfit', sans-serif;
			font-size: 1.55rem;
			font-weight: 700;
			color: var(--ink);
			line-height: 1.25;
			margin-bottom: 10px;
		}

		.visual-copy p {
			font-size: 0.95rem;
			line-height: 1.55;
			color: var(--muted);
		}

		.visual-art {
			width: min(260px, 100%);
			aspect-ratio: 1;
			position: relative;
			display: grid;
			place-items: center;
		}

		.visual-art .ring {
			position: absolute;
			inset: 12%;
			border-radius: 50%;
			background: rgba(255, 255, 255, 0.45);
			border: 1px solid rgba(13, 148, 136, 0.12);
		}

		.visual-art svg {
			width: 58%;
			height: auto;
			position: relative;
			z-index: 1;
			filter: drop-shadow(0 10px 18px rgba(15, 118, 110, 0.15));
		}

		.dot {
			position: absolute;
			border-radius: 50%;
			background: var(--primary);
			opacity: 0.35;
		}
		.dot-1 { width: 10px; height: 10px; top: 18%; left: 12%; background: var(--accent); opacity: 0.7; }
		.dot-2 { width: 8px; height: 8px; bottom: 22%; right: 14%; }
		.dot-3 { width: 6px; height: 6px; top: 28%; right: 18%; background: #F97316; opacity: 0.55; }

		.login-form-wrap {
			padding: 48px 44px 40px;
			display: flex;
			flex-direction: column;
			justify-content: center;
		}

		.form-header {
			margin-bottom: 28px;
		}

		.form-header .eyebrow {
			display: inline-block;
			font-size: 0.75rem;
			font-weight: 700;
			letter-spacing: 0.06em;
			text-transform: uppercase;
			color: var(--primary);
			margin-bottom: 8px;
		}

		.form-header h1 {
			font-family: 'Outfit', sans-serif;
			font-size: clamp(1.65rem, 2.4vw, 2rem);
			font-weight: 700;
			color: var(--ink);
			line-height: 1.2;
			margin-bottom: 8px;
		}

		.form-header p {
			font-size: 0.95rem;
			color: var(--muted);
			line-height: 1.5;
		}

		.auth-alert {
			border: 1px solid var(--danger-border);
			background: var(--danger-bg);
			color: var(--danger);
			border-radius: 12px;
			padding: 12px 14px;
			margin-bottom: 18px;
			font-size: 0.875rem;
			line-height: 1.5;
		}

		.auth-success {
			border: 1px solid #A7F3D0;
			background: #ECFDF5;
			color: #047857;
			border-radius: 12px;
			padding: 12px 14px;
			margin-bottom: 18px;
			font-size: 0.875rem;
			line-height: 1.5;
		}

		.field {
			margin-bottom: 16px;
		}

		.field label {
			display: block;
			font-size: 0.8125rem;
			font-weight: 700;
			color: var(--ink);
			margin-bottom: 7px;
		}

		.input-shell {
			position: relative;
			display: flex;
			align-items: center;
		}

		.input-shell svg {
			position: absolute;
			left: 16px;
			width: 18px;
			height: 18px;
			color: var(--primary);
			pointer-events: none;
			opacity: 0.85;
		}

		.input-shell input {
			width: 100%;
			height: 50px;
			border: 1.5px solid var(--line);
			background: var(--field);
			border-radius: 14px;
			padding: 0 16px 0 46px;
			font-family: inherit;
			font-size: 0.95rem;
			font-weight: 600;
			color: var(--ink);
			outline: none;
			transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
		}

		.input-shell input::placeholder {
			color: #8AA8A4;
			font-weight: 500;
		}

		.input-shell input:focus {
			border-color: var(--primary);
			background: #fff;
			box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.14);
		}

		.field-error {
			margin-top: 6px;
			color: var(--danger);
			font-size: 0.75rem;
			font-weight: 600;
		}

		.btn-login {
			width: 100%;
			height: 52px;
			border: none;
			border-radius: 14px;
			background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
			color: #fff;
			font-family: 'Outfit', sans-serif;
			font-size: 0.95rem;
			font-weight: 600;
			letter-spacing: 0.02em;
			cursor: pointer;
			box-shadow: 0 10px 24px rgba(13, 148, 136, 0.28);
			transition: transform 0.15s, box-shadow 0.15s, filter 0.15s;
			margin-top: 8px;
		}

		.btn-login:hover {
			filter: brightness(1.05);
			box-shadow: 0 14px 28px rgba(13, 148, 136, 0.34);
			transform: translateY(-1px);
		}

		.btn-login:active {
			transform: translateY(0);
		}

		@media (max-width: 820px) {
			.login-shell {
				grid-template-columns: 1fr;
				max-width: 440px;
			}

			.login-visual {
				padding: 36px 28px 28px;
				gap: 18px;
			}

			.visual-art {
				width: 160px;
			}

			.visual-copy h2 { font-size: 1.25rem; }
			.visual-copy p { display: none; }

			.login-form-wrap {
				padding: 32px 28px 36px;
			}
		}

		@media (max-width: 420px) {
			body { padding: 12px; }
			.login-form-wrap { padding: 28px 20px 32px; }
			.login-visual { padding: 28px 20px 20px; }
		}

		@media (prefers-reduced-motion: reduce) {
			*, *::before, *::after {
				animation: none !important;
				transition: none !important;
			}
		}
	</style>
</head>
<body>
	<div class="login-shell">
		<aside class="login-visual" aria-hidden="true">
			<div class="visual-art">
				<span class="ring"></span>
				<span class="dot dot-1"></span>
				<span class="dot dot-2"></span>
				<span class="dot dot-3"></span>
				<svg viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
					<circle cx="60" cy="60" r="54" fill="#fff" fill-opacity="0.55"/>
					<path d="M38 78c0-12.15 9.85-22 22-22s22 9.85 22 22" stroke="#0D9488" stroke-width="5" stroke-linecap="round"/>
					<circle cx="60" cy="44" r="14" stroke="#0D9488" stroke-width="5"/>
					<path d="M28 92h64" stroke="#FDBA74" stroke-width="4" stroke-linecap="round"/>
					<path d="M48 28c6-8 18-8 24 0" stroke="#14B8A6" stroke-width="3.5" stroke-linecap="round"/>
				</svg>
			</div>
			<div class="visual-copy">
				<h2>Melayani keluarga lebih dekat</h2>
				<p>Kelola data sasaran, imunisasi, dan kegiatan posyandu dalam satu tempat.</p>
			</div>
		</aside>

		<div class="login-form-wrap">
			<header class="form-header">
				<span class="eyebrow">Selamat datang</span>
				<h1>Sistem Posyandu</h1>
				<p>Silakan masuk dengan email dan password Anda.</p>
			</header>

			@if (session('status'))
				<div class="auth-success" role="status">{{ session('status') }}</div>
			@endif

			@if ($errors->any())
				<div class="auth-alert" role="alert">
					@foreach ($errors->all() as $err)
						{{ $err }}@if (!$loop->last)<br>@endif
					@endforeach
				</div>
			@endif

			<form method="POST" action="{{ route('login') }}" novalidate>
				@csrf

				<div class="field">
					<label for="email">Email</label>
					<div class="input-shell">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
							<circle cx="12" cy="7" r="4"/>
						</svg>
						<input id="email" type="email" name="email" placeholder="nama@email.com" value="{{ old('email') }}" required autofocus autocomplete="username">
					</div>
					@error('email')
						<div class="field-error">{{ $message }}</div>
					@enderror
				</div>

				<div class="field">
					<label for="password">Password</label>
					<div class="input-shell">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<rect x="3" y="11" width="18" height="11" rx="2"/>
							<path d="M7 11V7a5 5 0 0 1 10 0v4"/>
						</svg>
						<input id="password" type="password" name="password" placeholder="Masukkan password" required autocomplete="current-password">
					</div>
					@error('password')
						<div class="field-error">{{ $message }}</div>
					@enderror
				</div>

				<button type="submit" class="btn-login">Masuk</button>
			</form>
		</div>
	</div>
</body>
</html>
