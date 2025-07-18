<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

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
    Route::get('/home', [HomeController::class, 'index'])->name('home'); // Alias para compatibilidad
    
    // APIs para dashboard (gráficos y actividad)
    Route::prefix('api/dashboard')->name('api.dashboard.')->group(function () {
        Route::get('/chart/{type}', [HomeController::class, 'getChartData'])->name('chart');
        Route::get('/activity', [HomeController::class, 'getRecentActivity'])->name('activity');
    });
    
    // ============================================
    // RUTAS DE PROYECTOS
    // ============================================
    Route::resource('projects', ProjectController::class);
    
    // Rutas adicionales para proyectos
    Route::prefix('projects/{project}')->name('projects.')->group(function () {
        // Gestión de miembros
        Route::get('/members', [ProjectController::class, 'members'])->name('members');
        Route::post('/members', [ProjectController::class, 'addMember'])->name('addMember');
        Route::delete('/members/{user}', [ProjectController::class, 'removeMember'])->name('removeMember');
        
        // Chat del proyecto
        Route::get('/chat', [MessageController::class, 'index'])->name('chat');
    });
    
    // ============================================
    // RUTAS DE TAREAS
    // ============================================
    Route::resource('tasks', TaskController::class);
    
    // API para actualizar tareas en Kanban
    Route::prefix('api/tasks')->name('api.tasks.')->group(function () {
        Route::patch('/{task}/status', [TaskController::class, 'updateStatus'])->name('updateStatus');
        Route::patch('/{task}/order', [TaskController::class, 'updateOrder'])->name('updateOrder');
    });
    
    // ============================================
    // RUTAS DE MENSAJES/CHAT
    // ============================================
    Route::prefix('messages')->name('messages.')->group(function () {
        Route::post('/', [MessageController::class, 'store'])->name('store');
        Route::get('/project/{project}/new', [MessageController::class, 'getNewMessages'])->name('new');
        Route::delete('/{message}', [MessageController::class, 'destroy'])->name('destroy');
    });
    
    // ============================================
    // RUTAS DE RECURSOS (BIBLIOTECA)
    // ============================================
    Route::resource('resources', ResourceController::class);
    
    // Rutas adicionales para recursos
    Route::prefix('resources')->name('resources.')->group(function () {
        Route::get('/{resource}/download', [ResourceController::class, 'download'])->name('download');
        Route::post('/{resource}/rate', [ResourceController::class, 'rate'])->name('rate');
        Route::post('/{resource}/favorite', [ResourceController::class, 'favorite'])->name('favorite');
    });
    
    // ============================================
    // RUTAS DE NOTIFICACIONES
    // ============================================
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/{notification}/unread', [NotificationController::class, 'markAsUnread'])->name('unread');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('readAll');
        Route::delete('/clear-all', [NotificationController::class, 'clearAll'])->name('clearAll');
    });
    
    // ============================================
    // RUTAS DE PERFIL
    // ============================================
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/update', [ProfileController::class, 'update'])->name('update');
        Route::post('/upload-document', [ProfileController::class, 'uploadDocument'])->name('uploadDocument');
        Route::delete('/document/{document}', [ProfileController::class, 'deleteDocument'])->name('deleteDocument');
    });
});

// ============================================
// RUTAS DE DESARROLLO (SOLO EN LOCAL)
// ============================================

if (app()->environment('local')) {
    Route::prefix('dev')->group(function () {
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