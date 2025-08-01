<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Mostrar perfil del usuario
     */
    public function show()
    {
        $user = Auth::user();
        
        // Para compatibilidad con las vistas que esperan $profile
        $profile = $user;
        
        // Cargar estadísticas del usuario
        $stats = [
            'projects' => $user->projects()->count(),
            'tasks' => $user->assignedTasks()->count(),
            'completed' => $user->assignedTasks()->where('status', 'done')->count(),
            'completion_rate' => 0,
        ];
        
        // Calcular tasa de completitud
        if ($stats['tasks'] > 0) {
            $stats['completion_rate'] = round(($stats['completed'] / $stats['tasks']) * 100);
        }
        
        // Actividad reciente
        $recentProjects = $user->projects()
            ->latest()
            ->take(5)
            ->get();
            
        $recentTasks = $user->assignedTasks()
            ->with('project')
            ->latest()
            ->take(5)
            ->get();
        
        return view('profile.show', compact('user', 'profile', 'stats', 'recentProjects', 'recentTasks'));
    }
    
    /**
     * Mostrar formulario de edición
     */
    public function edit()
    {
        $user = Auth::user();
        
        // Para evitar errores en la vista, pasamos el usuario como 'profile'
        // ya que la vista espera $profile->campo
        $profile = $user;
        
        return view('profile.edit', compact('user', 'profile'));
    }
    
    /**
     * Actualizar perfil del usuario
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        try {
            // Validar datos
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    Rule::unique('users')->ignore($user->id),
                ],
                'phone' => 'nullable|string|max:20',
                'birth_date' => 'nullable|date',
                'bio' => 'nullable|string|max:1000',
                'institution' => 'nullable|string|max:255',
                'career' => 'nullable|string|max:255',
                'semester' => 'nullable|string|max:50',
                'student_id' => 'nullable|string|max:50',
                'languages' => 'nullable|string|max:255',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'password' => 'nullable|string|min:8|confirmed',
                'email_notifications' => 'nullable|boolean',
                'public_profile' => 'nullable|boolean',
            ], [
                'name.required' => 'El nombre es obligatorio',
                'email.required' => 'El correo electrónico es obligatorio',
                'email.email' => 'El correo debe ser válido',
                'email.unique' => 'Este correo ya está registrado',
                'password.min' => 'La contraseña debe tener al menos 8 caracteres',
                'password.confirmed' => 'Las contraseñas no coinciden',
                'avatar.image' => 'El archivo debe ser una imagen',
                'avatar.max' => 'La imagen no debe superar los 2MB',
            ]);
            
            // Actualizar datos básicos
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            
            // Actualizar campos adicionales
            $user->phone = $validated['phone'] ?? null;
            $user->birth_date = $validated['birth_date'] ?? null;
            $user->bio = $validated['bio'] ?? null;
            $user->institution = $validated['institution'] ?? null;
            $user->career = $validated['career'] ?? null;
            $user->semester = $validated['semester'] ?? null;
            $user->student_id = $validated['student_id'] ?? null;
            $user->languages = $validated['languages'] ?? null;
            
            // Actualizar preferencias (checkboxes)
            $user->email_notifications = $request->has('email_notifications');
            $user->public_profile = $request->has('public_profile');
            
            // Manejar actualización de contraseña
            if (!empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }
            
            // Manejar subida de avatar
            if ($request->hasFile('avatar')) {
                // Eliminar avatar anterior si existe
                if ($user->avatar && Storage::exists($user->avatar)) {
                    Storage::delete($user->avatar);
                }
                
                // Guardar nuevo avatar
                $path = $request->file('avatar')->store('avatars', 'public');
                $user->avatar = $path;
            }
            
            // Guardar cambios
            $user->save();
            
            return redirect()->route('profile.show')
                ->with('success', 'Perfil actualizado exitosamente');
                
        } catch (\Exception $e) {
            \Log::error('Error al actualizar perfil: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar el perfil. Por favor intenta nuevamente.');
        }
    }
    
    /**
     * Subir documento académico
     */
    public function uploadDocument(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,doc,docx|max:10240', // 10MB máximo
            'document_type' => 'required|string|in:transcript,certificate,other',
            'document_name' => 'required|string|max:255',
        ]);
        
        try {
            $user = Auth::user();
            
            // Guardar documento
            $path = $request->file('document')->store('documents/' . $user->id, 'private');
            
            // Aquí podrías guardar la información en una tabla de documentos
            // Por ahora solo retornamos éxito
            
            return response()->json([
                'success' => true,
                'message' => 'Documento subido exitosamente',
                'path' => $path
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al subir el documento'
            ], 500);
        }
    }
    
    /**
     * Eliminar cuenta de usuario
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
            'confirm_delete' => 'required|accepted',
        ]);
        
        $user = Auth::user();
        
        // Verificar contraseña
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'La contraseña es incorrecta']);
        }
        
        try {
            // Eliminar avatar si existe
            if ($user->avatar && Storage::exists($user->avatar)) {
                Storage::delete($user->avatar);
            }
            
            // Cerrar sesión antes de eliminar
            Auth::logout();
            
            // Eliminar usuario
            $user->delete();
            
            return redirect()->route('welcome')
                ->with('success', 'Tu cuenta ha sido eliminada exitosamente');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar la cuenta. Por favor contacta al administrador.');
        }
    }
}