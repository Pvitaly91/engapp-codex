<?php

namespace App\Modules\GitDeployment\Http\Concerns;

trait ParsesDeploymentPaths
{
    /**
     * Парсить та валідує список шляхів для часткового деплою.
     *
     * @param string|array $input Сирий текст з textarea або масив шляхів
     * @return array{valid: array<int, string>, errors: array<int, string>}
     */
    protected function parseAndValidatePaths(string|array $input): array
    {
        $preservePaths = config('git-deployment.preserve_paths', []);
        
        // Якщо вхід - масив, використовуємо його напряму
        if (is_array($input)) {
            $rawPaths = $input;
        } else {
            // Розбиваємо по різних роздільниках: \r\n, \n, кома, крапка з комою
            $rawPaths = preg_split('/[\r\n,;]+/', $input);
        }
        
        $valid = [];
        $errors = [];
        
        foreach ($rawPaths as $path) {
            // Прибираємо зайві пробіли
            $path = trim($path);
            
            // Пропускаємо порожні значення
            if ($path === '') {
                continue;
            }
            
            // Нормалізуємо слеші до "/"
            $path = str_replace('\\', '/', $path);
            
            // Перевіряємо на абсолютні шляхи (до нормалізації ltrim)
            if (str_starts_with($path, '/')) {
                $errors[] = "Шлях \"{$path}\" не може бути абсолютним";
                continue;
            }
            
            // Прибираємо початковий "./" якщо є
            while (str_starts_with($path, './')) {
                $path = substr($path, 2);
            }
            
            // Прибираємо кінцевий "/" для директорій
            $path = rtrim($path, '/');
            
            // Перевіряємо на directory traversal patterns: "../" або "/.." 
            if (preg_match('#(^|/)\.\.(/|$)#', $path)) {
                $errors[] = "Шлях \"{$path}\" містить заборонені сегменти \"..\"";
                continue;
            }
            
            // Отримуємо top-level сегмент
            $topLevel = explode('/', $path)[0];
            
            // Перевіряємо чи не входить в preserve_paths
            if (in_array($topLevel, $preservePaths, true)) {
                $errors[] = "Шлях \"{$path}\" входить у захищені директорії ({$topLevel})";
                continue;
            }
            
            // Ще раз перевіряємо що шлях не порожній після нормалізації
            if ($path === '') {
                continue;
            }
            
            $valid[] = $path;
        }
        
        // Видаляємо дублікати
        $valid = array_values(array_unique($valid));
        
        return [
            'valid' => $valid,
            'errors' => $errors,
        ];
    }
}
