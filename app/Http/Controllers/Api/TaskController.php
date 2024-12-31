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

        if ($request->hasFile('image')) {
            $originalImagePath = $request->file('image')->getPathName();
            $newImagePath = $this->storeImage($originalImagePath);

            // Actualiza la tarea con la nueva ruta de la imagen
            $task->image = $newImagePath;
            $task->save();
        }

        $task->save();

        return response()->json([
            'message' => 'Task created',
            'Task' => $task
        ], 201);
    }

    private function storeImage($imagePath)
    {
        // Extraer el nombre del archivo y la extensión
        $fileName = pathinfo($imagePath, PATHINFO_FILENAME);
        $extension = pathinfo($imagePath, PATHINFO_EXTENSION);

        // Generar un nombre único para la imagen
        $uniqueFileName = md5(time() . $fileName) . '.' . $extension;

        // Determinar el directorio donde se guardará la imagen
        $directory = 'storage/app/public/tasks';

        // Crear el directorio si no existe
        if (!File::isDirectory(public_path($directory))) {
            File::makeDirectory(public_path($directory), 0755, true);
        }

        // Construir la ruta completa del archivo
        $filePath = public_path($directory) . '/' . $uniqueFileName;

        // Copiar la imagen al directorio de almacenamiento
        copy($imagePath, $filePath);

        // Obtenir la ruta relativa de la imagen
        $relativePath = str_replace(public_path(), '', $filePath);

        return $relativePath;
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

        if ($request->hasFile('image')) {
            $originalImagePath = $request->file('image')->getPathName();
            $newImagePath = $this->storeImage($originalImagePath);

            // Actualiza la tarea con la nueva ruta de la imagen
            $task->image = $newImagePath;
            $task->save();
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

        Storage::delete($task->image);
        $task->delete();

        return response()->json(['message' => 'Task deleted']);
    }
}
