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
        $files = ModelFile::orderBy('created_at', 'desc')->get();
        return response()->json($files);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|unique:files',
            'type' => 'required',
            'file' => 'required'
        ]);

        $file = new ModelFile();
        $file->name = $validatedData['name'];
        $file->type = $validatedData['type'];

        if ($request->hasFile('file'))
        {
            $fileAdd = $request->file('file');
            $newFile = $this->storeFile($fileAdd);
            $file->file = $newFile;
        }

        $file->save();

        return response()->json([
            'message' => 'File created',
            'File' => $file
        ], 201);
    }

    private function storeFile($file)
    {
        $filename = $file->getClientOriginalName();
        $filename = pathinfo($filename,PATHINFO_FILENAME);
        $name_file = str_replace(" ","_",$filename);
        $extension = $file->getClientOriginalExtension();
        $final_name = date("His") . "_" . $name_file . "." . $extension;
        $file->move(storage_path('app/public/files'),$final_name);

        return 'files/'. $final_name;
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
            $fileAdd = $request->file('file');
            if($file->file)
            {
                unlink(storage_path('app/public/' . $file->file));
            }
            $newFile = $this->storeFile($fileAdd);
            $file->file = $newFile;
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
        if($file->file)
        {
            unlink(storage_path('app/public/' . $file->file));
        }
        $file->delete();
        return response()->json(['message' => 'File deleted']);
    }

    public function download($id)
    {
        $file = ModelFile::find($id);
        
        if (!$file) {
            return response()->json(['error' => 'File not found'], 404);
        }
        
        $filePath = storage_path('app/public/' . $file->file);
        
        if (!file_exists($filePath)) {
            return response()->json(['error' => 'File not found on the server'], 404);
        }

        // Extract original file extension
        $extension = pathinfo($file->file, PATHINFO_EXTENSION);
        
        // Create a readable filename for download
        $downloadFilename = Str::slug($file->name) . '.' . $extension;
        
        return response()->download($filePath, $downloadFilename);
    }

    public function getByFilename($filename)
    {
        // Try to find the file in the database by its stored filename
        $file = ModelFile::where('file', 'LIKE', '%' . $filename)->first();
        
        if (!$file) {
            // If not found by exact match, try with the files/ prefix
            $file = ModelFile::where('file', 'LIKE', 'files/' . $filename)->first();
        }
        
        if (!$file) {
            return response()->json(['error' => 'File not found'], 404);
        }
        
        $filePath = storage_path('app/public/' . $file->file);
        
        if (!file_exists($filePath)) {
            return response()->json(['error' => 'File not found on the server'], 404);
        }

        // Return the file with appropriate content type
        return response()->file($filePath);
    }
}
