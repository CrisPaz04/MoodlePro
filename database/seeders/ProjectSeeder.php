<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $users = User::all();
        
        if ($users->count() < 3) {
            $this->command->error('❌ Se necesitan al menos 3 usuarios. Ejecuta UserSeeder primero.');
            return;
        }

        // Proyectos principales del equipo
        $projects = [
            [
                'title' => 'MoodlePro - Proyecto Final',
                'description' => 'Desarrollo de una plataforma académica moderna que mejore la experiencia de Moodle tradicional. Incluye gestión de proyectos, tablero Kanban, biblioteca de recursos y sistema de notificaciones.',
                'status' => 'active',
                'start_date' => Carbon::now()->subDays(30),
                'deadline' => Carbon::now()->addDays(15),
                'creator_id' => 2, // Cristhian
            ],
            [
                'title' => 'Diseño UI/UX - MoodlePro',
                'description' => 'Creación del sistema de diseño, wireframes, prototipos y guías de estilo para MoodlePro. Enfoque en mejorar la experiencia del usuario.',
                'status' => 'active',
                'start_date' => Carbon::now()->subDays(25),
                'deadline' => Carbon::now()->addDays(10),
                'creator_id' => 4, // Anyeli
            ],
            [
                'title' => 'Backend API - MoodlePro',
                'description' => 'Desarrollo del backend con Laravel, APIs REST, base de datos y lógica de negocio para todas las funcionalidades del sistema.',
                'status' => 'active',
                'start_date' => Carbon::now()->subDays(28),
                'deadline' => Carbon::now()->addDays(12),
                'creator_id' => 3, // Oscar
            ],
            [
                'title' => 'Frontend & Integración',
                'description' => 'Implementación del frontend con Bootstrap, JavaScript y integración con el backend. Responsabilidad de la experiencia del usuario final.',
                'status' => 'active',
                'start_date' => Carbon::now()->subDays(20),
                'deadline' => Carbon::now()->addDays(18),
                'creator_id' => 6, // Justin
            ],
            [
                'title' => 'Testing & Documentación',
                'description' => 'Pruebas del sistema, documentación técnica y preparación de la presentación final del proyecto.',
                'status' => 'planning',
                'start_date' => Carbon::now()->addDays(5),
                'deadline' => Carbon::now()->addDays(25),
                'creator_id' => 7, // David
            ],
            [
                'title' => 'Investigación de Tecnologías',
                'description' => 'Proyecto completado de investigación sobre tecnologías web modernas, frameworks y mejores prácticas para el desarrollo.',
                'status' => 'completed',
                'start_date' => Carbon::now()->subDays(60),
                'deadline' => Carbon::now()->subDays(30),
                'creator_id' => 5, // Walter
            ],
            [
                'title' => 'Prototipo Inicial',
                'description' => 'Primer prototipo del sistema desarrollado para validar conceptos y obtener feedback inicial.',
                'status' => 'completed',
                'start_date' => Carbon::now()->subDays(45),
                'deadline' => Carbon::now()->subDays(35),
                'creator_id' => 2, // Cristhian
            ]
        ];

        // Proyectos académicos adicionales
        $academicProjects = [
            [
                'title' => 'Sistema de Inventario',
                'description' => 'Desarrollo de un sistema web para gestión de inventarios usando PHP y MySQL.',
                'status' => 'active',
                'start_date' => Carbon::now()->subDays(15),
                'deadline' => Carbon::now()->addDays(30),
                'creator_id' => 8, // Ana Sofia
            ],
            [
                'title' => 'App Móvil de Noticias',
                'description' => 'Aplicación móvil para consulta de noticias locales usando React Native.',
                'status' => 'planning',
                'start_date' => Carbon::now()->addDays(7),
                'deadline' => Carbon::now()->addDays(45),
                'creator_id' => 9, // Miguel Ángel
            ],
            [
                'title' => 'E-commerce Básico',
                'description' => 'Tienda en línea con carrito de compras, pagos y gestión de productos.',
                'status' => 'active',
                'start_date' => Carbon::now()->subDays(10),
                'deadline' => Carbon::now()->addDays(35),
                'creator_id' => 10, // Isabella
            ],
            [
                'title' => 'Dashboard Analytics',
                'description' => 'Panel de control con gráficos y estadísticas para análisis de datos.',
                'status' => 'completed',
                'start_date' => Carbon::now()->subDays(50),
                'deadline' => Carbon::now()->subDays(20),
                'creator_id' => 11, // Alejandro
            ],
            [
                'title' => 'Sistema de Reservas',
                'description' => 'Plataforma para reservas de espacios y recursos universitarios.',
                'status' => 'active',
                'start_date' => Carbon::now()->subDays(12),
                'deadline' => Carbon::now()->addDays(28),
                'creator_id' => 12, // Valeria
            ]
        ];

        // Crear proyectos principales
        foreach ($projects as $projectData) {
            $project = Project::create($projectData);
            
            // Agregar miembros al proyecto
            $this->addProjectMembers($project, $users);
            
            $this->command->info("✅ Proyecto creado: {$project->title}");
        }

        // Crear proyectos académicos adicionales
        foreach ($academicProjects as $projectData) {
            $project = Project::create($projectData);
            
            // Agregar algunos miembros aleatorios
            $randomMembers = $users->random(rand(2, 4));
            foreach ($randomMembers as $member) {
                if ($member->id !== $project->creator_id) {
                    $project->members()->attach($member->id, [
                        'role' => rand(1, 3) == 1 ? 'coordinator' : 'member',
                        'joined_at' => Carbon::now()->subDays(rand(1, 15))
                    ]);
                }
            }
            
            $this->command->info("✅ Proyecto académico creado: {$project->title}");
        }

        $this->command->info('🎉 Todos los proyectos creados exitosamente');
    }

    /**
     * Agregar miembros específicos a los proyectos principales
     */
    private function addProjectMembers(Project $project, $users)
    {
        // Agregar el creador como coordinador
        $project->members()->attach($project->creator_id, [
            'role' => 'coordinator',
            'joined_at' => $project->start_date
        ]);

        // Definir miembros según el tipo de proyecto
        $membersByProject = [
            'MoodlePro - Proyecto Final' => [2, 3, 4, 5, 6, 7], // Todo el equipo
            'Diseño UI/UX - MoodlePro' => [4, 5, 6], // Anyeli, Walter, Justin
            'Backend API - MoodlePro' => [2, 3, 7], // Cristhian, Oscar, David
            'Frontend & Integración' => [6, 7, 4], // Justin, David, Anyeli
            'Testing & Documentación' => [7, 2, 3], // David, Cristhian, Oscar
            'Investigación de Tecnologías' => [5, 2, 4], // Walter, Cristhian, Anyeli
            'Prototipo Inicial' => [2, 3, 6] // Cristhian, Oscar, Justin
        ];

        $projectMembers = $membersByProject[$project->title] ?? [];
        
        foreach ($projectMembers as $userId) {
            if ($userId !== $project->creator_id) {
                $role = in_array($userId, [2, 3, 4]) ? 'coordinator' : 'member'; // Team leads como coordinadores
                
                $project->members()->attach($userId, [
                    'role' => $role,
                    'joined_at' => $project->start_date->addDays(rand(0, 3))
                ]);
            }
        }
    }
}