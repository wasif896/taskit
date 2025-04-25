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
            'due_date' => 'nullable',
            'start_time' => 'nullable|string',
            'end_time' => 'nullable|string',
            'priority' => 'nullable|in:high,low,medium',
            'status' => 'nullable|in:completed,todo',
            'picture' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
            'video' => 'nullable|file|mimes:mp4,mov,avi|max:51200',
            'url' => 'nullable'
        ]);

        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $picturePath = null;
        if ($request->hasFile('picture')) {
            $picturePath = $this->handleImageUpload($request->file('picture'), 'pictures');
        }


        $videoPath = null;
        if ($request->hasFile('video')) {
            $videoPath = $this->handleImageUpload($request->file('video'), 'videos');
        }

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'priority'=> $request->priority ?? false,
            'status' => $request->status ?? 'todo',
            'user_id' => Auth::id(),
            'picture' => $picturePath,
            'video' => $videoPath,
            'url' => $request->url,
        ]);

        $createDate = strtotime($task->created_at);

        $task->create_date = strval($createDate);

        return response()->json([
            'message' => 'Task created successfully.',
            'task' => $task,
        ], 201);
    }


    public function handleImageUpload($image, $type)
    {
        $filename = uniqid() . '.' . $image->getClientOriginalExtension();
        $path = public_path('images/' . $type . '/' . $filename);
        $image->move(public_path('images/' . $type), $filename);
        return 'images/' . $type . '/' . $filename;
    }

    public function updateTask(Request $request, $taskid)
    {
        // Validate incoming data
        $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'start_time' => 'nullable|string',
            'end_time' => 'nullable|string',
            'priority' => 'nullable|in:high,low,medium',
            'status' => 'nullable|in:todo,completed',
            'picture' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
            'video' => 'nullable|file|mimes:mp4,mov,avi|max:51200',
            'url' => 'nullable'
        ]);

        $task = Task::where('id', $taskid)
                    ->where('user_id', auth()->id())
                    ->first();

        if (!$task) {
            return response()->json([
                'message' => 'Task not found or not authorized to update',
            ], 404);
        }

        if ($request->hasFile('picture')) {
            $picturePath = $this->handleImageUpload($request->file('picture'), 'pictures');
            $task->picture = $picturePath;
        }

        if ($request->hasFile('video')) {
            $videoPath = $this->handleImageUpload($request->file('video'), 'videos');
            $task->video = $videoPath;
        }

        $task->update($request->only([
            'title',
            'description',
            'due_date',
            'start_time',
            'end_time',
            'priority',
            'status',
            'url',
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
