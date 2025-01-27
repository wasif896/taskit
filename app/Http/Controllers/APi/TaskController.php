<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function createtask(Request $request)
    {

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'nullable|date',
            'starttime' => 'nullable|string',
            'endtime' => 'nullable|string',
            'is_high_priority' => 'nullable|boolean',
            'status' => 'nullable|in:completed,todo',
        ]);
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'date' => $request->date,
            'starttime' => $request->starttime,
            'endtime' => $request->endtime,
            'is_high_priority' => $request->is_high_priority ?? false,
            'status' => $request->status ?? 'todo',
            'user_id' =>  Auth::id(),
        ]);

        return response()->json([
            'message' => 'Task created successfully.',
            'task' => $task,
        ], 201);
    }
    public function updateTask(Request $request, $taskid)
    {
        // dd(Auth::id());
        // dd($request->title);
        $request->validate([
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'date' => 'date',
            'starttime' => 'string',
            'endtime' => 'string',
            'is_high_priority' => 'boolean',
            'status' => 'in:todo,completed',
        ]);

        $task = Task::where('id', $taskid)
                    ->where('user_id', auth()->id())
                    ->first();

        if (!$task) {
            return response()->json([
                'message' => 'Task not found or not authorized to update',
            ], 404);
        }

        $task->update($request->only([
            'title',
            'description',
            'date',
            'starttime',
            'endtime',
            'is_high_priority',
            'status',
        ]));

        return response()->json([
            'message' => 'Task updated successfully',
            'task' => $task,
        ], 200);
    }
    public function getTaskById($taskid)
    {
        $task = Task::find($taskid);
        if (!$task) {
            return response()->json([
                'message' => 'Task not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Task retrieved successfully',
            'task' => $task,
        ], 200);
    }
    public function getAllTask(){
        $userid = Auth::id();
        $tasks = Task::where('user_id', $userid)->get();

        if ($tasks->isEmpty()) {
            return response()->json([
                'message' => 'No tasks found for this user',
            ], 404);
        }

        return response()->json([
            'message' => 'Tasks retrieved successfully',
            'tasks' => $tasks,
        ], 200);
    }
    public function deleteTask($taskid)
    {
        $task = Task::where('id', $taskid)
                    ->where('user_id', auth()->id())
                    ->first();

        if (!$task) {
            return response()->json([
                'message' => 'Task not found or not authorized to delete',
            ], 404);
        }

        $task->delete();
        return response()->json([
            'message' => 'Task deleted successfully',
        ], 200);
    }
}
