<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Availability;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use TCG\Voyager\Facades\Voyager;

class ImportAvailabilitiesController extends Controller
{
    public function showImportForm()
    {
        return view('admin.import-csv');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt'
        ]);

        $file = $request->file('file');
        $handle = fopen($file, "r");

        fgetcsv($handle);// Ignorar la primera fila (encabezados)

        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {

            Log::info('fila ' . print_r($row, true));
            Availability::create([
                'user_id' => Auth::id(),
                'date' => $row[0], // Ajusta según los campos del CSV
                'start_time' => $row[1],
                'end_time' => $row[2],
                'is_available' => $row[3],
            ]);

        }

        fclose($handle);

        Session::flash('success', 'Datos importados correctamente');
        return redirect()->route('voyager.availabilities.index');
    }
}
