<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\File as ModelFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    public function index()
    {
        $files = ModelFile::all();
        return response()->json($files);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'type' => 'required',
            'file' => 'required'
        ]);

        $file = new ModelFile();
        $file->name = $validatedData['name'];
        $file->type = $validatedData['type'];

        if ($request->hasFile('file'))
        {
            $originalFilePath = $request->file('file')->getPathname();
            $newFilePath = $this->storeFile($originalFilePath);
            $file->file = $newFilePath;
        }

        $file->save();

        return response()->json([
            'message' => 'File created',
            'File' => $file
        ], 201);
    }

    private function storeFile($file)
    {
         //Extraer el nombre del archivo y la extensión
        $fileName = pathinfo($file, PATHINFO_FILENAME);
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        // Generar un nombre único para la imagen
        $uniqueFileName =  $fileName . '.' . $extension;

       // Determinar el directorio donde se guardará la imagen
        $directory = 'storage/app/public/files/';

        // Crear el directorio si no existe
        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        // Construir la ruta completa del archivo
        $filePath = $directory . '/' . $uniqueFileName;

        // Copiar la imagen al directorio de almacenamiento
        copy($file, $filePath);

        // Obtenir la ruta relativa de la imagen

        return $filePath;


    }

    public function update(Request $request, $id)
    {
        $file = ModelFile::find($id);
        if (!$file)
        {
            return response()->json(['error' => 'File not found'], 404);
        }
        $validatedData = $request->validate([
            'name' => 'required',
            'type' => 'required',
            'file' => 'required'
        ]);

        $file->name = $validatedData['name'];
        $file->type = $validatedData['type'];

        if ($request->hasFile('file'))
        {
            $originalFilePath = $request->file('file')->getPathName();
            $newFilePath = $this->storeFile($originalFilePath);
            $file->file = $newFilePath;
        }
        $file->save();

        return response()->json([
            'message' => 'File updated',
            'file' => $file]);
    }

    public function delete($id)
    {
        $file = ModelFile::find($id);

        if (!$file) {
            return response()->json(['error' => 'File not found'], 404);
        }

        Storage::delete($file->file);
        $file->delete();

        return response()->json(['message' => 'File deleted']);
    }
}
