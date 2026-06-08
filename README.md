# Sistem Posyandu

Sistem manajemen Posyandu berbasis web menggunakan Laravel 12 dan Livewire 3.

## Persyaratan

- PHP >= 8.2
- Composer
- Node.js >= 18.x
- NPM

## Instalasi

1. Clone repository
```bash
git clone https://github.com/username/sistem-posyandu.git
cd sistem-posyandu
```

2. Install dependencies
```bash
composer install
npm install
```

3. Setup environment
```bash
copy .env.example .env
php artisan key:generate
```


5. Migrate dan seed database
```bash
php artisan migrate --seed
```

6. Build assets
```bash
npm run build
```

## Menjalankan Aplikasi

```bash
php artisan serve
```

Akses aplikasi di `http://localhost:8000`

## Teknologi

- Laravel 12
- Livewire 3
- Tailwind CSS
- Vite
