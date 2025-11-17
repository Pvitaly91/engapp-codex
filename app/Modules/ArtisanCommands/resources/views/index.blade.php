@extends('layouts.app')

@section('title', 'Artisan команди')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Artisan команди</h1>
        <p class="text-gray-600">Керування Laravel Artisan командами через веб-інтерфейс</p>
    </div>

    <!-- Alert Container -->
    <div id="alert-container" class="mb-6"></div>

    @foreach($commands as $categoryKey => $categoryCommands)
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4 capitalize">
                @if($categoryKey === 'cache')
                    <i class="fa-solid fa-database mr-2 text-red-500"></i> Очистка кешу
                @elseif($categoryKey === 'optimization')
                    <i class="fa-solid fa-gauge-high mr-2 text-green-500"></i> Оптимізація
                @elseif($categoryKey === 'maintenance')
                    <i class="fa-solid fa-wrench mr-2 text-blue-500"></i> Обслуговування
                @else
                    {{ ucfirst($categoryKey) }}
                @endif
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($categoryCommands as $command)
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-lg transition-shadow">
                        <div class="flex items-start mb-3">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-{{ $command['color'] ?? 'gray' }}-100 flex items-center justify-center mr-3">
                                <i class="fa-solid {{ $command['icon'] ?? 'fa-terminal' }} text-{{ $command['color'] ?? 'gray' }}-600"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-800 mb-1">{{ $command['title'] }}</h3>
                                <p class="text-sm text-gray-600 mb-2">{{ $command['description'] }}</p>
                                <code class="text-xs bg-gray-100 px-2 py-1 rounded text-gray-700">{{ $command['command'] }}</code>
                            </div>
                        </div>
                        
                        <button 
                            onclick="executeCommand('{{ $command['key'] }}', {{ $command['confirmation_required'] ? 'true' : 'false' }})"
                            class="w-full mt-3 px-4 py-2 bg-{{ $command['color'] ?? 'blue' }}-500 text-white rounded-lg hover:bg-{{ $command['color'] ?? 'blue' }}-600 transition-colors font-medium"
                        >
                            <i class="fa-solid fa-play mr-2"></i>Виконати
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

<!-- Loading Overlay -->
<div id="loading-overlay" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 shadow-xl">
        <div class="flex items-center space-x-3">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <span class="text-lg font-medium text-gray-700">Виконується команда...</span>
        </div>
    </div>
</div>

@push('head-scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endpush

<script>
function showAlert(message, type = 'success') {
    const container = document.getElementById('alert-container');
    const alertId = 'alert-' + Date.now();
    
    const colors = {
        success: 'green',
        error: 'red',
        info: 'blue',
        warning: 'yellow'
    };
    
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        info: 'fa-info-circle',
        warning: 'fa-exclamation-triangle'
    };
    
    const color = colors[type] || 'blue';
    const icon = icons[type] || 'fa-info-circle';
    
    const alert = document.createElement('div');
    alert.id = alertId;
    alert.className = `bg-${color}-50 border border-${color}-200 text-${color}-800 px-4 py-3 rounded-lg mb-4 flex items-center justify-between`;
    alert.innerHTML = `
        <div class="flex items-center">
            <i class="fa-solid ${icon} mr-3 text-${color}-600"></i>
            <span>${message}</span>
        </div>
        <button onclick="document.getElementById('${alertId}').remove()" class="text-${color}-600 hover:text-${color}-800">
            <i class="fa-solid fa-times"></i>
        </button>
    `;
    
    container.appendChild(alert);
    
    // Auto remove after 10 seconds
    setTimeout(() => {
        const element = document.getElementById(alertId);
        if (element) {
            element.remove();
        }
    }, 10000);
}

function showOutput(output, exitCode) {
    if (!output || output.trim() === '') {
        return '';
    }
    
    const color = exitCode === 0 ? 'green' : 'red';
    return `
        <div class="mt-2 bg-gray-900 text-gray-100 p-3 rounded font-mono text-sm overflow-x-auto">
            <pre class="whitespace-pre-wrap">${output}</pre>
        </div>
    `;
}

function executeCommand(commandKey, requireConfirmation = false) {
    if (requireConfirmation) {
        if (!confirm('Ви впевнені, що хочете виконати цю команду?')) {
            return;
        }
    }
    
    const overlay = document.getElementById('loading-overlay');
    overlay.classList.remove('hidden');
    
    fetch('{{ route('artisan-commands.execute') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            command: commandKey
        })
    })
    .then(response => response.json())
    .then(data => {
        overlay.classList.add('hidden');
        
        if (data.success) {
            const outputHtml = showOutput(data.output, data.exit_code);
            showAlert(data.message + outputHtml, 'success');
        } else {
            const outputHtml = showOutput(data.output, data.exit_code);
            showAlert(data.message + outputHtml, 'error');
        }
    })
    .catch(error => {
        overlay.classList.add('hidden');
        showAlert('Помилка виконання запиту: ' + error.message, 'error');
    });
}
</script>
@endsection
