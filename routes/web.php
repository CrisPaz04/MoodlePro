<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;

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
    // RUTAS DE NOTIFICACIONES
    // ============================================
    Route::prefix('notifications')->name('notifications.')->group(function () {
        // Vista principal de notificaciones
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        
        // Marcar como leída
        Route::patch('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-multiple-read', [NotificationController::class, 'markMultipleAsRead'])->name('mark-multiple-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        
        // Eliminar notificaciones
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::post('/destroy-multiple', [NotificationController::class, 'destroyMultiple'])->name('destroy-multiple');
        Route::post('/clear-read', [NotificationController::class, 'clearRead'])->name('clear-read');
        
        // APIs para AJAX
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::get('/recent', [NotificationController::class, 'recent'])->name('recent');
        Route::get('/grouped', [NotificationController::class, 'grouped'])->name('grouped');
        Route::get('/stats', [NotificationController::class, 'stats'])->name('stats');
        
        // Preferencias
        Route::patch('/preferences', [NotificationController::class, 'updatePreferences'])->name('update-preferences');
        
        // Notificación de prueba (solo desarrollo)
        if (app()->environment('local')) {
            Route::post('/test', [NotificationController::class, 'test'])->name('test');
        }
    });
    
    // ============================================
    // RUTAS DE PERFIL Y CONFIGURACIÓN
    // ============================================
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/update', [ProfileController::class, 'update'])->name('update');
        Route::delete('/delete', [ProfileController::class, 'destroy'])->name('delete');
        
        // Rutas adicionales para el perfil
        Route::post('/upload-document', [ProfileController::class, 'uploadDocument'])->name('upload-document');
        Route::delete('/document/{document}', [ProfileController::class, 'deleteDocument'])->name('delete-document');
        Route::patch('/preferences', [ProfileController::class, 'updatePreferences'])->name('update-preferences');
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
                'notifications' => App\Models\Notification::count(),
            ];
        });
        
        Route::get('/seed-data', function () {
            // Crear datos de prueba rápido
            $user = auth()->user();
            
            if (!$user) {
                return redirect()->route('login')->with('error', 'Debes iniciar sesión primero');
            }
            
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
            
            $task = App\Models\Task::create([
                'project_id' => $project->id,
                'title' => 'Tarea de ejemplo',
                'description' => 'Esta es una tarea de prueba',
                'status' => 'todo',
                'priority' => 'medium',
                'created_by' => $user->id,
                'assigned_to' => $user->id,
                'due_date' => now()->addDays(7)
            ]);
            
            // Crear notificación de prueba
            $user->notifyTaskAssigned($task);
            
            return redirect()->route('projects.show', $project)
                ->with('success', 'Datos de prueba creados con notificación');
        });
    });
}