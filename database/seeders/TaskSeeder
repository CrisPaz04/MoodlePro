
<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $projects = Project::with('members')->get();
        
        if ($projects->count() == 0) {
            $this->command->error('❌ Se necesitan proyectos. Ejecuta ProjectSeeder primero.');
            return;
        }

        // Tareas específicas para MoodlePro
        $moodleProProject = $projects->where('title', 'MoodlePro - Proyecto Final')->first();
        if ($moodleProProject) {
            $this->createMoodleProTasks($moodleProProject);
        }

        // Tareas para proyecto de Diseño
        $designProject = $projects->where('title', 'Diseño UI/UX - MoodlePro')->first();
        if ($designProject) {
            $this->createDesignTasks($designProject);
        }

        // Tareas para proyecto Backend
        $backendProject = $projects->where('title', 'Backend API - MoodlePro')->first();
        if ($backendProject) {
            $this->createBackendTasks($backendProject);
        }

        // Tareas para proyecto Frontend
        $frontendProject = $projects->where('title', 'Frontend & Integración')->first();
        if ($frontendProject) {
            $this->createFrontendTasks($frontendProject);
        }

        // Tareas genéricas para otros proyectos
        foreach ($projects as $project) {
            if (!in_array($project->title, [
                'MoodlePro - Proyecto Final',
                'Diseño UI/UX - MoodlePro', 
                'Backend API - MoodlePro',
                'Frontend & Integración'
            ])) {
                $this->createGenericTasks($project);
            }
        }

        $this->command->info('✅ Tareas creadas exitosamente para todos los proyectos');
    }

    /**
     * Crear tareas específicas para MoodlePro
     */
    private function createMoodleProTasks(Project $project)
    {
        $tasks = [
            [
                'title' => 'Configurar entorno de desarrollo',
                'description' => 'Instalar Laravel, configurar base de datos y dependencias del proyecto.',
                'priority' => 'high',
                'status' => 'done',
                'due_date' => Carbon::now()->subDays(25),
                'order' => 1
            ],
            [
                'title' => 'Diseñar base de datos',
                'description' => 'Crear modelos, migraciones y relaciones para usuarios, proyectos, tareas y recursos.',
                'priority' => 'high',
                'status' => 'done',
                'due_date' => Carbon::now()->subDays(20),
                'order' => 2
            ],
            [
                'title' => 'Implementar autenticación',
                'description' => 'Sistema de login, registro y middleware de autenticación.',
                'priority' => 'high',
                'status' => 'done',
                'due_date' => Carbon::now()->subDays(18),
                'order' => 3
            ],
            [
                'title' => 'Desarrollar dashboard principal',
                'description' => 'Dashboard con estadísticas, gráficos y actividad reciente del usuario.',
                'priority' => 'high',
                'status' => 'done',
                'due_date' => Carbon::now()->subDays(15),
                'order' => 4
            ],
            [
                'title' => 'Sistema de gestión de proyectos',
                'description' => 'CRUD completo para proyectos con gestión de miembros y roles.',
                'priority' => 'high',
                'status' => 'in_progress',
                'due_date' => Carbon::now()->addDays(3),
                'order' => 5
            ],
            [
                'title' => 'Tablero Kanban para tareas',
                'description' => 'Implementar drag & drop, actualización de estados y ordenamiento.',
                'priority' => 'high',
                'status' => 'in_progress',
                'due_date' => Carbon::now()->addDays(5),
                'order' => 6
            ],
            [
                'title' => 'Sistema de notificaciones',
                'description' => 'Notificaciones en tiempo real para tareas, deadlines y actividad.',
                'priority' => 'medium',
                'status' => 'in_progress',
                'due_date' => Carbon::now()->addDays(7),
                'order' => 7
            ],
            [
                'title' => 'Biblioteca de recursos',
                'description' => 'Upload de archivos, categorización, ratings y sistema de descarga.',
                'priority' => 'medium',
                'status' => 'todo',
                'due_date' => Carbon::now()->addDays(10),
                'order' => 8
            ],
            [
                'title' => 'Chat por proyecto',
                'description' => 'Sistema de mensajería integrado para comunicación del equipo.',
                'priority' => 'medium',
                'status' => 'todo',
                'due_date' => Carbon::now()->addDays(12),
                'order' => 9
            ],
            [
                'title' => 'Testing y optimización',
                'description' => 'Pruebas del sistema, corrección de bugs y optimización de rendimiento.',
                'priority' => 'high',
                'status' => 'todo',
                'due_date' => Carbon::now()->addDays(15),
                'order' => 10
            ]
        ];

        $this->createTasksForProject($project, $tasks);
        $this->command->info("✅ Tareas de MoodlePro creadas");
    }

    /**
     * Crear tareas para proyecto de diseño
     */
    private function createDesignTasks(Project $project)
    {
        $tasks = [
            [
                'title' => 'Research de competidores',
                'description' => 'Análisis de Moodle, Canvas, Blackboard y otras plataformas educativas.',
                'priority' => 'medium',
                'status' => 'done',
                'due_date' => Carbon::now()->subDays(15),
                'order' => 1
            ],
            [
                'title' => 'Wireframes principales',
                'description' => 'Wireframes para dashboard, proyectos, tareas y perfil de usuario.',
                'priority' => 'high',
                'status' => 'done',
                'due_date' => Carbon::now()->subDays(10),
                'order' => 2
            ],
            [
                'title' => 'Sistema de colores y tipografía',
                'description' => 'Definir paleta de colores, fuentes y guía de estilo visual.',
                'priority' => 'high',
                'status' => 'in_progress',
                'due_date' => Carbon::now()->addDays(2),
                'order' => 3
            ],
            [
                'title' => 'Componentes UI reutilizables',
                'description' => 'Diseñar botones, cards, formularios y elementos comunes.',
                'priority' => 'medium',
                'status' => 'todo',
                'due_date' => Carbon::now()->addDays(6),
                'order' => 4
            ],
            [
                'title' => 'Prototipo interactivo',
                'description' => 'Crear prototipo navegable en Figma para testing de usabilidad.',
                'priority' => 'medium',
                'status' => 'todo',
                'due_date' => Carbon::now()->addDays(8),
                'order' => 5
            ]
        ];

        $this->createTasksForProject($project, $tasks);
        $this->command->info("✅ Tareas de Diseño creadas");
    }

    /**
     * Crear tareas para proyecto backend
     */
    private function createBackendTasks(Project $project)
    {
        $tasks = [
            [
                'title' => 'API endpoints para proyectos',
                'description' => 'Crear APIs REST para CRUD de proyectos y gestión de miembros.',
                'priority' => 'high',
                'status' => 'done',
                'due_date' => Carbon::now()->subDays(12),
                'order' => 1
            ],
            [
                'title' => 'API endpoints para tareas',
                'description' => 'APIs para tareas, actualización de estados y ordenamiento Kanban.',
                'priority' => 'high',
                'status' => 'in_progress',
                'due_date' => Carbon::now()->addDays(4),
                'order' => 2
            ],
            [
                'title' => 'Sistema de archivos',
                'description' => 'Upload, storage y download de recursos con validaciones de seguridad.',
                'priority' => 'medium',
                'status' => 'todo',
                'due_date' => Carbon::now()->addDays(8),
                'order' => 3
            ],
            [
                'title' => 'WebSockets para chat',
                'description' => 'Implementar WebSockets para mensajería en tiempo real.',
                'priority' => 'medium',
                'status' => 'todo',
                'due_date' => Carbon::now()->addDays(10),
                'order' => 4
            ],
            [
                'title' => 'Seeders y datos de prueba',
                'description' => 'Crear seeders para poblar la base de datos con datos realistas.',
                'priority' => 'low',
                'status' => 'in_progress',
                'due_date' => Carbon::now()->addDays(6),
                'order' => 5
            ]
        ];

        $this->createTasksForProject($project, $tasks);
        $this->command->info("✅ Tareas de Backend creadas");
    }

    /**
     * Crear tareas para proyecto frontend
     */
    private function createFrontendTasks(Project $project)
    {
        $tasks = [
            [
                'title' => 'Setup de Bootstrap y assets',
                'description' => 'Configurar Bootstrap, SCSS y estructura de assets del frontend.',
                'priority' => 'high',
                'status' => 'done',
                'due_date' => Carbon::now()->subDays(14),
                'order' => 1
            ],
            [
                'title' => 'Layout principal y navegación',
                'description' => 'Implementar sidebar, header y estructura base de la aplicación.',
                'priority' => 'high',
                'status' => 'done',
                'due_date' => Carbon::now()->subDays(10),
                'order' => 2
            ],
            [
                'title' => 'Páginas de autenticación',
                'description' => 'Login, registro y páginas relacionadas con diseño responsivo.',
                'priority' => 'medium',
                'status' => 'done',
                'due_date' => Carbon::now()->subDays(8),
                'order' => 3
            ],
            [
                'title' => 'Dashboard interactivo',
                'description' => 'Gráficos con Chart.js, widgets dinámicos y actividad reciente.',
                'priority' => 'high',
                'status' => 'in_progress',
                'due_date' => Carbon::now()->addDays(3),
                'order' => 4
            ],
            [
                'title' => 'Kanban drag & drop',
                'description' => 'Implementar funcionalidad de arrastrar y soltar para el tablero.',
                'priority' => 'high',
                'status' => 'todo',
                'due_date' => Carbon::now()->addDays(7),
                'order' => 5
            ],
            [
                'title' => 'Chat interface',
                'description' => 'Interfaz de chat con scroll infinito y notificaciones visuales.',
                'priority' => 'medium',
                'status' => 'todo',
                'due_date' => Carbon::now()->addDays(12),
                'order' => 6
            ]
        ];

        $this->createTasksForProject($project, $tasks);
        $this->command->info("✅ Tareas de Frontend creadas");
    }

    /**
     * Crear tareas genéricas para otros proyectos
     */
    private function createGenericTasks(Project $project)
    {
        $genericTasks = [
            'Análisis de requerimientos',
            'Diseño de arquitectura',
            'Implementación inicial',
            'Testing básico',
            'Documentación',
            'Revisión de código',
            'Optimización',
            'Entrega final'
        ];

        $priorities = ['low', 'medium', 'high'];
        $statuses = ['todo', 'in_progress', 'done'];

        $taskCount = rand(4, 8);
        for ($i = 0; $i < $taskCount; $i++) {
            $taskTitle = $genericTasks[array_rand($genericTasks)];
            
            // Evitar tareas duplicadas
            $suffix = rand(1, 100);
            if (Task::where('project_id', $project->id)->where('title', 'like', "%{$taskTitle}%")->exists()) {
                $taskTitle .= " #{$suffix}";
            }

            $task = [
                'title' => $taskTitle,
                'description' => "Descripción detallada de la tarea: {$taskTitle} para el proyecto {$project->title}.",
                'priority' => $priorities[array_rand($priorities)],
                'status' => $statuses[array_rand($statuses)],
                'due_date' => Carbon::now()->addDays(rand(1, 30)),
                'order' => $i + 1
            ];

            $this->createTasksForProject($project, [$task]);
        }

        $this->command->info("✅ Tareas genéricas creadas para: {$project->title}");
    }

    /**
     * Crear tareas para un proyecto específico
     */
    private function createTasksForProject(Project $project, array $tasks)
    {
        $members = $project->members->pluck('id')->toArray();
        if (empty($members)) {
            $members = [$project->creator_id];
        }

        foreach ($tasks as $taskData) {
            $taskData['project_id'] = $project->id;
            $taskData['created_by'] = $project->creator_id;
            $taskData['assigned_to'] = $members[array_rand($members)];

            Task::create($taskData);
        }
    }
}