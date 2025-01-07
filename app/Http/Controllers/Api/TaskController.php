<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::where('user_id', auth()->id())
            ->with('course')
            ->get();

        return response()->json($tasks);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'teacher_note' => 'required',
            'course_id' => 'required|exists:courses,id'
        ]);

        $task = new Task();
        $task->user_id = auth()->id(); // Asumiendo que estás usando autenticación
        $task->course_id = $validatedData['course_id'];
        $task->teacher_note = $validatedData['teacher_note'];

        if ($request->hasFile('image'))
        {
            //$originalFilePath = $request->file('file')->getPathname();
            $fileAdd = $request->file('image');
            $newFile = $this->storeFile($fileAdd);
            dd(response()->json($newFile));
            $task->image = $newFile;
        }

        $task->save();

        return response()->json([
            'message' => 'Task created',
            'Task' => $task
        ], 201);
    }

    private function storeFile($file)
    {
        $filename = $file->getClientOriginalName();
        $filename = pathinfo($filename,PATHINFO_FILENAME);
        $name_file = str_replace(" ","_",$filename);
        $extension = $file->getClientOriginalExtension();
        $final_name = date("His") . "_" . $name_file . "." . $extension;
        $file->move(public_path('/storage/tasks'),$final_name);
        return '/tasks' . "/". $final_name;
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
            unlink(public_path('storage/' . $task->image));
            $newFile = $this->storeFile($fileAdd);
            $task->image = $newFile;
        }

        $task->save();

        return response()->json([
            'message' => 'Task updated',
            'task' => $task]);
    }

    public function delete($id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        unlink(public_path('storage/' .$task->image));
        $task->delete();

        return response()->json(['message' => 'Task deleted']);
    }
}
