<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\HomeController;

// ============================================
// RUTAS PÚBLICAS
// ============================================

// Landing page
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Rutas de autenticación (Laravel UI)
Auth::routes();

// ============================================
// RUTAS PROTEGIDAS (REQUIEREN LOGIN)
// ============================================

Route::middleware(['auth'])->group(function () {
    
    // Dashboard principal
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
    Route::get('/home', [HomeController::class, 'index'])->name('home'); // Alias
    
    // APIs para dashboard
    Route::prefix('dashboard/api')->name('dashboard.api.')->group(function () {
        Route::get('/stats', [HomeController::class, 'getStats'])->name('stats');
        Route::get('/activity', [HomeController::class, 'getActivity'])->name('activity');
        Route::get('/charts', [HomeController::class, 'getChartData'])->name('charts');
    });
    
    // ============================================
    // RUTAS DE PROYECTOS
    // ============================================
    Route::resource('projects', ProjectController::class);
    
    // Rutas adicionales para proyectos
    Route::prefix('projects/{project}')->name('projects.')->group(function () {
        // Gestión de miembros
        Route::get('/members', [ProjectController::class, 'members'])->name('members');
        Route::post('/members', [ProjectController::class, 'addMember'])->name('add-member');
        Route::delete('/members/{user}', [ProjectController::class, 'removeMember'])->name('remove-member');
        
        // Chat del proyecto
        Route::get('/chat', [MessageController::class, 'index'])->name('chat');
    });
    
    // ============================================
    // RUTAS DE TAREAS
    // ============================================
    Route::resource('tasks', TaskController::class);
    
    // API para Kanban (AJAX)
    Route::prefix('api/tasks')->name('api.tasks.')->group(function () {
        Route::patch('/{task}/status', [TaskController::class, 'updateStatus'])->name('update-status');
        Route::patch('/{task}/order', [TaskController::class, 'updateOrder'])->name('update-order');
    });
    
    // ============================================
    // RUTAS DE MENSAJES/CHAT
    // ============================================
    Route::prefix('messages')->name('messages.')->group(function () {
        Route::post('/', [MessageController::class, 'store'])->name('store');
        Route::patch('/{message}', [MessageController::class, 'update'])->name('update');
        Route::delete('/{message}', [MessageController::class, 'destroy'])->name('destroy');
        
        // API para polling de mensajes nuevos
        Route::get('/project/{project}/new', [MessageController::class, 'getNewMessages'])->name('new');
    });
    
    // ============================================
    // RUTAS DE RECURSOS (BIBLIOTECA)
    // ============================================
    Route::resource('resources', ResourceController::class);
    
    // Rutas adicionales para recursos
    Route::prefix('resources')->name('resources.')->group(function () {
        Route::get('/{resource}/download', [ResourceController::class, 'download'])->name('download');
        Route::post('/{resource}/rate', [ResourceController::class, 'rate'])->name('rate');
        
        // Filtros y búsqueda
        Route::get('/category/{category}', [ResourceController::class, 'index'])->name('category');
        Route::get('/search', [ResourceController::class, 'index'])->name('search');
    });
    
    // ============================================
    // RUTAS DE PERFIL Y CONFIGURACIÓN
    // ============================================
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', function () {
            return view('profile.show');
        })->name('show');
        
        Route::get('/edit', function () {
            return view('profile.edit');
        })->name('edit');
    });
    
    // ============================================
    // RUTAS ADICIONALES/UTILIDADES
    // ============================================
    
    // Búsqueda global
    Route::get('/search', function (Illuminate\Http\Request $request) {
        $query = $request->get('q');
        
        if (!$query) {
            return redirect()->back();
        }
        
        $projects = auth()->user()->projects()
            ->where('title', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->get();
            
        $tasks = auth()->user()->assignedTasks()
            ->where('title', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->get();
            
        $resources = App\Models\Resource::public()
            ->where('title', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->get();
        
        return view('search.results', compact('query', 'projects', 'tasks', 'resources'));
    })->name('search');
    
    // Notificaciones (futuro)
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', function () {
            return view('notifications.index');
        })->name('index');
        
        Route::post('/mark-read', function () {
            // Implementar más tarde
            return response()->json(['success' => true]);
        })->name('mark-read');
    });
});

// ============================================
// RUTAS DE FALLBACK Y MANTENIMIENTO
// ============================================

// Ruta para páginas no encontradas personalizadas
Route::fallback(function () {
    return view('errors.404');
});

// Ruta de mantenimiento (cuando sea necesario)
Route::get('/maintenance', function () {
    return view('maintenance');
})->name('maintenance');

// ============================================
// RUTAS DE DESARROLLO (SOLO EN LOCAL)
// ============================================

if (app()->environment('local')) {
    // Rutas para testing y desarrollo
    Route::prefix('dev')->name('dev.')->group(function () {
        Route::get('/test-models', function () {
            return [
                'projects' => App\Models\Project::count(),
                'tasks' => App\Models\Task::count(),
                'messages' => App\Models\Message::count(),
                'resources' => App\Models\Resource::count(),
                'users' => App\Models\User::count(),
            ];
        });
        
        Route::get('/seed-data', function () {
            // Crear datos de prueba rápido
            $user = auth()->user();
            
            $project = App\Models\Project::create([
                'title' => 'Proyecto de Prueba',
                'description' => 'Este es un proyecto de prueba para MoodlePro',
                'start_date' => now(),
                'deadline' => now()->addDays(30),
                'creator_id' => $user->id,
                'status' => 'active'
            ]);
            
            $project->members()->attach($user->id, [
                'role' => 'coordinator',
                'joined_at' => now()
            ]);
            
            App\Models\Task::create([
                'project_id' => $project->id,
                'title' => 'Tarea de ejemplo',
                'description' => 'Esta es una tarea de prueba',
                'status' => 'todo',
                'priority' => 'medium',
                'created_by' => $user->id,
                'assigned_to' => $user->id,
                'due_date' => now()->addDays(7)
            ]);
            
            return redirect()->route('projects.show', $project)
                ->with('success', 'Datos de prueba creados');
        });
    });
}