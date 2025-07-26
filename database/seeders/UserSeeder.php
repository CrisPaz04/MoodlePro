<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Usuario administrador/catedrático
        User::create([
            'name' => 'Prof. Carlos Mendoza',
            'email' => 'profesor@moodlepro.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // Estudiantes del equipo
        $teamMembers = [
            [
                'name' => 'Cristhian López',
                'email' => 'cristhian@moodlepro.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Oscar Rivera',
                'email' => 'oscar@moodlepro.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Anyeli Hernández',
                'email' => 'anyeli@moodlepro.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Walter García',
                'email' => 'walter@moodlepro.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Justin Flores',
                'email' => 'justin@moodlepro.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'David Martínez',
                'email' => 'david@moodlepro.com',
                'password' => Hash::make('password123'),
            ]
        ];

        foreach ($teamMembers as $member) {
            User::create([
                'name' => $member['name'],
                'email' => $member['email'],
                'password' => $member['password'],
                'email_verified_at' => now(),
            ]);
        }

        // Usuarios adicionales para demostración
        $additionalUsers = [
            [
                'name' => 'Ana Sofia Ruiz',
                'email' => 'ana@moodlepro.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Miguel Ángel Torres',
                'email' => 'miguel@moodlepro.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Isabella Castro',
                'email' => 'isabella@moodlepro.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Alejandro Morales',
                'email' => 'alejandro@moodlepro.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Valeria Sánchez',
                'email' => 'valeria@moodlepro.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Roberto Jiménez',
                'email' => 'roberto@moodlepro.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Carmen Delgado',
                'email' => 'carmen@moodlepro.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Fernando Vega',
                'email' => 'fernando@moodlepro.com',
                'password' => Hash::make('password123'),
            ]
        ];

        foreach ($additionalUsers as $user) {
            User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => $user['password'],
                'email_verified_at' => now(),
            ]);
        }

        // Usuario de prueba genérico
        User::create([
            'name' => 'Usuario Demo',
            'email' => 'demo@moodlepro.com',
            'password' => Hash::make('demo123'),
            'email_verified_at' => now(),
        ]);

        $this->command->info('✅ Usuarios creados exitosamente');
        $this->command->info('📧 Emails: profesor@moodlepro.com, cristhian@moodlepro.com, etc.');
        $this->command->info('🔑 Password: password123 (demo123 para usuario demo)');
    }
}