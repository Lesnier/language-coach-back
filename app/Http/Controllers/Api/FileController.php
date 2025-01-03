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
            'name' => 'required|unique:files',
            'type' => 'required',
            'file' => 'required'
        ]);

        $file = new ModelFile();
        $file->name = $validatedData['name'];
        $file->type = $validatedData['type'];

        if ($request->hasFile('file'))
        {
            //$originalFilePath = $request->file('file')->getPathname();
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
        $file->move(public_path('/storage/files'),$final_name);

        return public_path('/storage/files') . "/". $final_name;
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
            //Storage::delete($file->file);
            unlink($file->file);
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

        //Storage::delete($file->file);
        unlink($file->file);
        $file->delete();

        return response()->json(['message' => 'File deleted']);
    }
}
