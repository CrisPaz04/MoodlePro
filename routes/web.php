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
        Route::get('/stats', [HomeController::class, 'getStats'])->name('stats');
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
    
    // API para tareas (RUTAS COMPLETAS AGREGADAS)
    Route::prefix('api/tasks')->name('api.tasks.')->group(function () {
        Route::patch('/{task}/status', [TaskController::class, 'updateStatus'])->name('updateStatus');
        Route::patch('/{task}/order', [TaskController::class, 'updateOrder'])->name('updateOrder');
        Route::post('/{task}/complete', [TaskController::class, 'markComplete'])->name('complete');
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
    
    // ============================================
    // BÚSQUEDA GLOBAL
    // ============================================
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
            
        $resources = App\Models\Resource::where('title', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->get();
        
        return view('search.results', compact('query', 'projects', 'tasks', 'resources'));
    })->name('search');
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
            
            // Crear proyecto de prueba
            $project = App\Models\Project::create([
                'title' => 'Proyecto de Prueba ' . now()->format('Y-m-d H:i'),
                'description' => 'Este es un proyecto de prueba creado automáticamente',
                'start_date' => now(),
                'deadline' => now()->addMonths(2),
                'status' => 'active',
                'creator_id' => $user->id,
            ]);
            
            // Agregar al usuario como miembro
            $project->members()->attach($user->id, ['role' => 'coordinator']);
            
            // Crear algunas tareas
            for ($i = 1; $i <= 5; $i++) {
                App\Models\Task::create([
                    'project_id' => $project->id,
                    'title' => "Tarea de prueba {$i}",
                    'description' => "Descripción de la tarea {$i}",
                    'status' => ['todo', 'in_progress', 'done'][rand(0, 2)],
                    'priority' => ['low', 'medium', 'high'][rand(0, 2)],
                    'due_date' => now()->addDays(rand(1, 30)),
                    'assigned_to' => $user->id,
                    'creator_id' => $user->id,
                    'order' => $i,
                ]);
            }
            
            return redirect()->route('dashboard')->with('success', 'Datos de prueba creados exitosamente');
        });
        
        Route::get('/clear-data', function () {
            if (!auth()->check()) {
                return redirect()->route('login');
            }
            
            // Limpiar datos de prueba
            App\Models\Task::truncate();
            App\Models\Message::truncate();
            App\Models\Resource::truncate();
            App\Models\Notification::truncate();
            DB::table('project_members')->truncate();
            App\Models\Project::truncate();
            
            return redirect()->route('dashboard')->with('success', 'Datos limpiados exitosamente');
        });
    });
}