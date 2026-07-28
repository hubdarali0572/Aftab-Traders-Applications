<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="overflow-x-hidden">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta
        name="description"
        content="Decoration Lights Wholesale & Retail ERP System by Aftab Traders — manage inventory, sales, purchases, warehouses, expenses, and customers in one platform."
    >
    <meta name="robots" content="index, follow">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title inertia>{{ config('app.name', 'Sample Application') }}</title>

    <link rel="icon" type="image/png" href="/storage/images/logo.png">
    <link rel="shortcut icon" type="image/png" href="/storage/images/logo.png">
    <link rel="apple-touch-icon" href="/storage/images/logo.png">

    {{-- Apply theme before paint (default: Light Mode) --}}
    <script>
        (function () {
            try {
                var theme = localStorage.getItem('theme');
                if (theme === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            } catch (e) {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

    <!-- Fonts (HTTPS only; no third-party cookies) -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
</head>

<body class="font-sans antialiased overflow-x-hidden">
    @inertia
</body>

</html>
