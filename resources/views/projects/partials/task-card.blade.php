<div class="task-card priority-{{ $task->priority }}" 
    data-task-id="{{ $task->id }}"
    draggable="true">
   
   <div class="task-header">
       <h6 class="task-title">{{ $task->title }}</h6>
       <span class="task-priority priority-{{ $task->priority }}">
           {{ ucfirst($task->priority) }}
       </span>
   </div>
   
   @if($task->description)
   <p class="task-description">{{ $task->description }}</p>
   @endif
   
   <div class="task-footer">
       <div class="task-assignee">
           @if($task->assignedUser)
               <img src="https://ui-avatars.com/api/?name={{ urlencode($task->assignedUser->name) }}&background=4e73df&color=fff&size=24" 
                    alt="{{ $task->assignedUser->name }}" 
                    class="assignee-avatar"
                    title="{{ $task->assignedUser->name }}">
               <span>{{ Str::limit($task->assignedUser->name, 15) }}</span>
           @else
               <span class="text-muted">Sin asignar</span>
           @endif
       </div>
       
       @if($task->due_date)
       <div class="task-due-date {{ $task->due_date->isPast() && $task->status !== 'done' ? 'overdue' : '' }}">
           <i class="fas fa-calendar-alt"></i>
           @if($task->due_date->isToday())
               Hoy
           @elseif($task->due_date->isTomorrow())
               Mañana
           @else
               {{ $task->due_date->format('d M') }}
           @endif
       </div>
       @endif
   </div>
   
   <div class="task-actions">
       <button class="task-action-btn" onclick="editTask({{ $task->id }})" title="Editar">
           <i class="fas fa-edit"></i>
       </button>
       <button class="task-action-btn" onclick="deleteTask({{ $task->id }})" title="Eliminar">
           <i class="fas fa-trash"></i>
       </button>
   </div>
</div>