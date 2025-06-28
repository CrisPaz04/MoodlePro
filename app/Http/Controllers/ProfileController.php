<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        
        $stats = [
            'projects' => $user->projects()->count(),
            'tasks' => $user->assignedTasks()->count(),
            'completed' => $user->assignedTasks()->where('status', 'done')->count(),
        ];
        
        return view('profile.show', compact('stats'));
    }
    
    public function edit()
    {
        return view('profile.edit');
    }
    
    public function update(Request $request)
    {
        // Lógica de actualización
    }
}