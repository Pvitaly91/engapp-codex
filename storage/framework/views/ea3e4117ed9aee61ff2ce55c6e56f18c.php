<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Вхід до адмін-панелі</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white shadow rounded-lg p-8">
        <h1 class="text-2xl font-semibold text-center text-gray-800 mb-6">Вхід до адмін-панелі</h1>

        <?php if(session('status')): ?>
            <div class="mb-4 p-3 rounded bg-green-100 text-green-700 text-sm">
                <?php echo e(session('status')); ?>

            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('login.perform')); ?>" class="space-y-5">
            <?php echo csrf_field(); ?>

            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Логін</label>
                <input
                    id="username"
                    type="text"
                    name="username"
                    value="<?php echo e(old('username')); ?>"
                    required
                    autofocus
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
                <?php $__errorArgs = ['username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Пароль</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="flex items-center">
                <label for="remember" class="flex items-center text-sm text-gray-600">
                    <input
                        id="remember"
                        type="checkbox"
                        name="remember"
                        value="1"
                        class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        <?php if(old('remember')): echo 'checked'; endif; ?>
                    >
                    Запам'ятати мене
                </label>
            </div>

            <button
                type="submit"
                class="w-full py-2 px-4 text-white font-semibold bg-blue-600 hover:bg-blue-700 rounded-md shadow"
            >
                Увійти
            </button>
        </form>
    </div>
</body>
</html>
<?php /**PATH /var/www/pvital01/data/www/gramlyze.com/resources/views/auth/login.blade.php ENDPATH**/ ?>