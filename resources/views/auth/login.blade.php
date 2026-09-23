<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SIM-BUMDes</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    <main class="mx-auto flex min-h-screen max-w-md items-center px-6">
        <section class="w-full rounded-lg bg-white p-8 shadow-sm">
            <h1 class="text-2xl font-semibold">Masuk ke SIM-BUMDes</h1>
            <form method="POST" action="{{ route('login.attempt') }}" class="mt-6 space-y-4">
                @csrf
                <label class="block">
                    <span class="text-sm font-medium">Username</span>
                    <input name="username" value="{{ old('username') }}" required autofocus class="mt-1 w-full rounded border-slate-300">
                </label>
                <label class="block">
                    <span class="text-sm font-medium">Password</span>
                    <input type="password" name="password" required class="mt-1 w-full rounded border-slate-300">
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="remember" value="1" class="rounded border-slate-300">
                    Ingat saya
                </label>
                @if ($errors->any())
                    <p class="text-sm text-red-600">{{ $errors->first() }}</p>
                @endif
                <button type="submit" class="w-full rounded bg-slate-900 px-4 py-2 font-medium text-white">Masuk</button>
            </form>
        </section>
    </main>
</body>
</html>
