@extends('voyager::master')

@section('content')

    <style>
        .file-upload {
            border: 2px dashed #bbbcbf;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .file-upload:hover {
            background-color: #f0f0f0;
        }

        .file-upload input[type="file"] {
            display: none; /* Oculta el input de archivo */
        }

        .file-upload label {
            color: #1a202c;
            font-weight: bold;
            cursor: pointer;
            height: 100%;
            width: 100%;
            padding: 20px;
        }

        .file-upload label span.icon{
            font-size:25px;
        }
    </style>

    <div class="container">
        <h2>Importar CSV</h2>
        <form action="{{ route('admin.import.process') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <div class="file-upload">
                    <label for="upload-csv">
                        <span class="icon voyager-upload"></span>
                        <br>
                        <span class="file-name"> Seleccionar Archivo .CSV</span>
                    </label>
                    <input  onchange="updateFileName()" id="upload-csv" type="file" name="file" class="form-control" style="display: none;">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Importar</button>
        </form>
    </div>

    <script>
        function updateFileName() {
            const input = document.getElementById('upload-csv');
            const label = document.querySelector('.file-name');
            const fileName = input.files[0] ? input.files[0].name : 'Seleccionar Archivo CSV';
            label.textContent = fileName;
        }
    </script>
@endsection
