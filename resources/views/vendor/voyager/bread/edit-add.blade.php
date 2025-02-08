@php
    $edit = !is_null($dataTypeContent->getKey());
    $add  = is_null($dataTypeContent->getKey());
@endphp

@extends('voyager::master')

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('page_title', __('voyager::generic.'.($edit ? 'edit' : 'add')).' '.$dataType->getTranslatedAttribute('display_name_singular'))

@section('page_header')
    <h1 class="page-title">
        <i class="{{ $dataType->icon }}"></i>
        {{ __('voyager::generic.'.($edit ? 'edit' : 'add')).' '.$dataType->getTranslatedAttribute('display_name_singular') }}
    </h1>
    @include('voyager::multilingual.language-selector')
@stop

@section('content')
    <div class="page-content edit-add container-fluid">
        <div class="row">
            <div class="col-md-12">

                <div class="panel panel-bordered">
                    <!-- form start -->
                    <form role="form"
                          class="form-edit-add"
                          action="{{ $edit ? route('voyager.'.$dataType->slug.'.update', $dataTypeContent->getKey()) : route('voyager.'.$dataType->slug.'.store') }}"
                          method="POST" enctype="multipart/form-data">
                        <!-- PUT Method if we are editing -->
                        @if($edit)
                            {{ method_field("PUT") }}
                        @endif

                        <!-- CSRF TOKEN -->
                        {{ csrf_field() }}

                        <div class="panel-body">

                            @if (count($errors) > 0)
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- Adding / Editing -->
                            @php
                                $dataTypeRows = $dataType->{($edit ? 'editRows' : 'addRows' )};
                            @endphp

                            @foreach($dataTypeRows as $row)
                                <!-- GET THE DISPLAY OPTIONS -->
                                @php
                                    $display_options = $row->details->display ?? NULL;
                                    if ($dataTypeContent->{$row->field.'_'.($edit ? 'edit' : 'add')}) {
                                        $dataTypeContent->{$row->field} = $dataTypeContent->{$row->field.'_'.($edit ? 'edit' : 'add')};
                                    }
                                @endphp
                                @if (isset($row->details->legend) && isset($row->details->legend->text))
                                    <legend class="text-{{ $row->details->legend->align ?? 'center' }}" style="background-color: {{ $row->details->legend->bgcolor ?? '#f0f0f0' }};padding: 5px;">{{ $row->details->legend->text }}</legend>
                                @endif

                                <div class="form-group @if($row->type == 'hidden') hidden @endif col-md-{{ $display_options->width ?? 12 }} {{ $errors->has($row->field) ? 'has-error' : '' }}" @if(isset($display_options->id)){{ "id=$display_options->id" }}@endif>
                                    {{ $row->slugify }}
                                    <label class="control-label" for="name">{{ $row->getTranslatedAttribute('display_name') }}</label>
                                    @include('voyager::multilingual.input-hidden-bread-edit-add')
                                    @if ($add && isset($row->details->view_add))
                                        @include($row->details->view_add, ['row' => $row, 'dataType' => $dataType, 'dataTypeContent' => $dataTypeContent, 'content' => $dataTypeContent->{$row->field}, 'view' => 'add', 'options' => $row->details])
                                    @elseif ($edit && isset($row->details->view_edit))
                                        @include($row->details->view_edit, ['row' => $row, 'dataType' => $dataType, 'dataTypeContent' => $dataTypeContent, 'content' => $dataTypeContent->{$row->field}, 'view' => 'edit', 'options' => $row->details])
                                    @elseif (isset($row->details->view))
                                        @include($row->details->view, ['row' => $row, 'dataType' => $dataType, 'dataTypeContent' => $dataTypeContent, 'content' => $dataTypeContent->{$row->field}, 'action' => ($edit ? 'edit' : 'add'), 'view' => ($edit ? 'edit' : 'add'), 'options' => $row->details])
                                    @elseif ($row->type == 'relationship')
                                        @include('voyager::formfields.relationship', ['options' => $row->details])
                                    @else
                                        {!! app('voyager')->formField($row, $dataType, $dataTypeContent) !!}
                                    @endif

                                    @foreach (app('voyager')->afterFormFields($row, $dataType, $dataTypeContent) as $after)
                                        {!! $after->handle($row, $dataType, $dataTypeContent) !!}
                                    @endforeach
                                    @if ($errors->has($row->field))
                                        @foreach ($errors->get($row->field) as $error)
                                            <span class="help-block">{{ $error }}</span>
                                        @endforeach
                                    @endif
                                </div>
                            @endforeach

                        </div><!-- panel-body -->

                        <div class="panel-footer">
                            @section('submit-buttons')
                                <button type="submit" class="btn btn-primary save">{{ __('voyager::generic.save') }}</button>
                            @stop
                            @yield('submit-buttons')
                        </div>
                    </form>

                    <div style="display:none">
                        <input type="hidden" id="upload_url" value="{{ route('voyager.upload') }}">
                        <input type="hidden" id="upload_type_slug" value="{{ $dataType->slug }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade modal-danger" id="confirm_delete_modal">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"
                            aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="voyager-warning"></i> {{ __('voyager::generic.are_you_sure') }}</h4>
                </div>

                <div class="modal-body">
                    <h4>{{ __('voyager::generic.are_you_sure_delete') }} '<span class="confirm_delete_name"></span>'</h4>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('voyager::generic.cancel') }}</button>
                    <button type="button" class="btn btn-danger" id="confirm_delete">{{ __('voyager::generic.delete_confirm') }}</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Delete File Modal -->
@stop

@section('javascript')
    <script>
        var params = {};
        var $file;

        function deleteHandler(tag, isMulti) {
            return function() {
                $file = $(this).siblings(tag);

                params = {
                    slug:   '{{ $dataType->slug }}',
                    filename:  $file.data('file-name'),
                    id:     $file.data('id'),
                    field:  $file.parent().data('field-name'),
                    multi: isMulti,
                    _token: '{{ csrf_token() }}'
                }

                $('.confirm_delete_name').text(params.filename);
                $('#confirm_delete_modal').modal('show');
            };
        }

        $('document').ready(function () {
            $('.toggleswitch').bootstrapToggle();

            //Init datepicker for date fields if data-datepicker attribute defined
            //or if browser does not handle date inputs
            $('.form-group input[type=date]').each(function (idx, elt) {
                if (elt.hasAttribute('data-datepicker')) {
                    elt.type = 'text';
                    $(elt).datetimepicker($(elt).data('datepicker'));
                } else if (elt.type != 'date') {
                    elt.type = 'text';
                    $(elt).datetimepicker({
                        format: 'L',
                        extraFormats: [ 'YYYY-MM-DD' ]
                    }).datetimepicker($(elt).data('datepicker'));
                }
            });

            @if ($isModelTranslatable)
            $('.side-body').multilingual({"editing": true});
            @endif

            $('.side-body input[data-slug-origin]').each(function(i, el) {
                $(el).slugify();
            });

            $('.form-group').on('click', '.remove-multi-image', deleteHandler('img', true));
            $('.form-group').on('click', '.remove-single-image', deleteHandler('img', false));
            $('.form-group').on('click', '.remove-multi-file', deleteHandler('a', true));
            $('.form-group').on('click', '.remove-single-file', deleteHandler('a', false));

            $('#confirm_delete').on('click', function(){
                $.post('{{ route('voyager.'.$dataType->slug.'.media.remove') }}', params, function (response) {
                    if ( response
                        && response.data
                        && response.data.status
                        && response.data.status == 200 ) {

                        toastr.success(response.data.message);
                        $file.parent().fadeOut(300, function() { $(this).remove(); })
                    } else {
                        toastr.error("Error removing file.");
                    }
                });

                $('#confirm_delete_modal').modal('hide');
            });
            $('[data-toggle="tooltip"]').tooltip();
        });

        document.addEventListener("DOMContentLoaded", function() {

            let emitter = document.getElementsByName("emitter")[0];
            if(emitter.value ===""){
                emitter.value = 'Institution Name';
            }


            let emission_date = document.getElementsByName("emission_date")[0];
            let todayDate = new Date();
            if(emission_date){
                let day = String(todayDate.getDate()).padStart(2, '0');
                let month = String(todayDate.getMonth() + 1).padStart(2, '0');
                let year = todayDate.getFullYear();

                emission_date.value = `${year}-${month}-${day}`;
            }

            const suscripcionSelect = document.getElementsByName('subscription_id')[0];

            // Escuchar el evento de cambio en el campo de selección
            $(suscripcionSelect).on('change', function() {
                const suscripcionId = this.value;
                if (suscripcionId) {
                    // Hacer una solicitud AJAX para obtener los datos de la suscripción

                    fetch(`/subscription_price/${suscripcionId}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Error al obtener los datos de la suscripción.');
                            }
                            return response.json();
                        })
                        .then(data => {
                            // Actualizar los campos de precio y total
                            let subtotal = document.getElementsByName('subtotal')[0];
                            subtotal.value = data.total;
                        })
                        .catch(error => {
                            alert(error.message);
                        });
                } else {
                    document.getElementsByName('subtotal')[0].value = '';
                }
            });

            let taxSelect = document.getElementsByName('tax')[0];
            $(taxSelect).on('change', function () {
                let subtotal = document.getElementsByName('subtotal')[0].value;
                let amountTax = document.getElementsByName('amount_tax')[0];
                let total = document.getElementsByName('total')[0];
                amountTax.value = subtotal * taxSelect.value / 100;
                total.value = Number(amountTax.value) + Number(subtotal);

            });

            let productSelect = document.getElementsByName("bill_belongstomany_product_relationship[]")[0];
            let productosSeleccionados = [];
            $(productSelect).on('change', function() {
                // Obtener los IDs seleccionados actualmente
                const selectedIds = Array.from(productSelect.selectedOptions)
                    .map(option => option.value);

                // Identificar los productos que se han añadido o eliminado
                const productosAnadidos = selectedIds.filter(id => !productosSeleccionados.includes(id));
                const productosEliminados = productosSeleccionados.filter(id => !selectedIds.includes(id));

                // Actualizar la lista de productos seleccionados
                productosSeleccionados = selectedIds;

                // Si hay productos añadidos, obtener sus precios y sumarlos
                if (productosAnadidos.length > 0) {
                    obtenerPreciosProductos(productosAnadidos, 'sumar');
                    console.log('p add')
                }

                // Si hay productos eliminados, obtener sus precios y restarlos
                if (productosEliminados.length > 0) {
                    obtenerPreciosProductos(productosEliminados, 'restar');
                    console.log('p delete')
                }
            });
        });

        function obtenerPreciosProductos(ids, accion) {
            // Hacer la solicitud AJAX
            fetch('/product_prices', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ ids: ids })
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error al obtener los precios de los productos.');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Precios obtenidos:', data.prices);

                    // Obtener el total actual de la factura
                    const facturaInput = document.getElementsByName('subtotal')[0];
                    let total = parseFloat(facturaInput.value) || 0;

                    // Sumar o restar los precios según la acción
                    if (accion === 'sumar') {
                        total += data.prices.reduce((sum, price) => sum + price, 0);
                    } else if (accion === 'restar') {
                        total -= data.prices.reduce((sum, price) => sum + price, 0);
                    }

                    // Actualizar el input de la factura
                    facturaInput.value = total.toFixed(2); // Asegura 2 decimales
                    let tax = document.getElementsByName('tax')[0];
                    let amountTax = document.getElementsByName('amount_tax')[0];
                    let totalToPay = document.getElementsByName('total')[0];

                    amountTax.value = Number(facturaInput.value) * Number(tax.value) / 100;
                    totalToPay.value = Number(amountTax.value) + Number(facturaInput.value);
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert(error.message);
                });
        }

    </script>

@stop
