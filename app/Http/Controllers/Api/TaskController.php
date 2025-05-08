<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::where('user_id', auth()->id())
            ->with('course')
            ->orderBy('created_at', 'desc')
            ->get();

        // Transform the collection to include full image URLs and hide the image property
        $tasks = $tasks->map(function($task) {
            if ($task->image) {
                $task->image_url = asset('storage/' . $task->image);
                unset($task->image); // Remove the image property
            }
            return $task;
        });

        return response()->json($tasks);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'teacher_note' => 'required',
            'course_id' => 'required|exists:courses,id',
            'professor_id' => 'required'
        ]);

        $task = new Task();
        $task->user_id = auth()->id();
        $task->course_id = $validatedData['course_id'];
        $task->teacher_note = $validatedData['teacher_note'];
        $task->professor_id = $validatedData['professor_id'];

        if ($request->hasFile('image'))
        {
            $fileAdd = $request->file('image');
            $newFile = $this->storeFile($fileAdd);
            $task->image = $newFile;
        }

        $task->save();

        // Add image URL to the response if image exists and hide the image property
        if ($task->image) {
            $task->image_url = asset('storage/' . $task->image);
            $originalTask = clone $task; // Create a copy to return in the response
            unset($originalTask->image); // Remove the image property from the response

            return response()->json([
                'message' => 'Task created',
                'Task' => $originalTask
            ], 201);
        }

        return response()->json([
            'message' => 'Task created',
            'Task' => $task
        ], 201);
    }

    private function storeFile($file)
    {
        try {
            // Make sure the directory exists
            if (!Storage::disk('public')->exists('tasks')) {
                Storage::disk('public')->makeDirectory('tasks');
            }

            $filename = $file->getClientOriginalName();
            $filename = pathinfo($filename, PATHINFO_FILENAME);
            $name_file = str_replace(" ", "_", $filename);
            $extension = $file->getClientOriginalExtension();
            $final_name = date("His") . "_" . $name_file . "." . $extension;

            // Store using Laravel's Storage facade
            $path = $file->storeAs('tasks', $final_name, 'public');

            // Log successful upload
            Log::info("File uploaded successfully: {$path}");

            return $path;
        } catch (Exception $e) {
            // Log the error
            Log::error("File upload failed: " . $e->getMessage());
            throw $e;
        }
    }

    public function update(Request $request, $id)
    {
        $task = Task::find($id);
        if (!$task) {
            return response()->json(['error' => 'Task not found'], 404);
        }
        $validatedData = $request->validate([
            'teacher_note' => 'required',
            'course_id' => 'required|exists:courses,id'
        ]);

        $task->course_id = $validatedData['course_id'];
        $task->teacher_note = $validatedData['teacher_note'];

        if ($request->hasFile('image'))
        {
            $fileAdd = $request->file('image');
            if($task->image)
            {
                // Use Storage facade instead of unlink
                Storage::disk('public')->delete($task->image);
            }
            $newFile = $this->storeFile($fileAdd);
            $task->image = $newFile;
        }

        $task->save();

        // Add image URL to the response if image exists and hide the image property
        if ($task->image) {
            $task->image_url = asset('storage/' . $task->image);
            $originalTask = clone $task; // Create a copy to return in the response
            unset($originalTask->image); // Remove the image property from the response

            return response()->json([
                'message' => 'Task updated',
                'task' => $originalTask
            ]);
        }

        return response()->json([
            'message' => 'Task updated',
            'task' => $task
        ]);
    }

    public function delete($id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['error' => 'Task not found'], 404);
        }
        if($task->image)
        {
            // Use Storage facade instead of unlink
            Storage::disk('public')->delete($task->image);
        }
        $task->delete();
        return response()->json(['message' => 'Task deleted']);
    }
}
