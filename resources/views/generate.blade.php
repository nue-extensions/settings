@extends('layouts.app')
@section('title', $title)
    
@section('css')
@endsection

@section('js')
    <script>
        $(function () {
            $('#add-table-field').click(function (event) {
                $('#table-fields tbody').append($('#table-field-tpl').html().replace(/__index__/g, $('#table-fields tr').length - 1));
            });
            $('#table-fields').on('click', '.table-field-remove', function(event) {
                $(event.target).closest('tr').remove();
            });
            $('#scaffold').on('submit', function (event) {
                if ($('#inputTableName').val() == '') {
                    $('#inputTableName').addClass('is-invalid');
                    return false;
                }
                return true;
            });
            $('#inputTableName').on('input', function() {
                $('#inputTableName').removeClass('is-invalid');
            });
        });
    </script>
@endsection

@section('content')
    @include('nue::partials.breadcrumb', ['lists' => [
        'Extensions' => 'javascript:;', 
        $title => 'active'
    ]])

    @include('nue::partials.datatable.header', [
        'title' => $title, 
        'description' => 'Kamu bisa membuat CRUD otomatis melalui halaman ini.'
    ])

    <div class="card rounded-0">
        <div class="card-body p-0">

            <form method="post" action="{{ $action }}" id="scaffold">
                @csrf

                <div class="p-3">
                    <div class="row">
                        <div class="col-sm-3 pe-1">
                            <div class="mb-3">
                                <label class="form-label text-uppercase fw-semi-bold text-muted mb-1 ms-1" for="inputTableName">
                                    <small>Nama Tabel <span class="text-danger">*</span></small>
                                </label>
                                <input type="text" name="table_name" class="form-control" id="inputTableName" placeholder="Tentukan nama tabel" value="{{ old('table_name') }}">
                                <span class="invalid-feedback">Nama tabel tidak boleh kosong!</span>
                            </div>
                        </div>
                        <div class="col-sm-4 ps-1 pe-1">
                            <div class="mb-3">
                                <label class="form-label text-uppercase fw-semi-bold text-muted mb-1 ms-1" for="inputModelName">
                                    <small>Model</small>
                                </label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text bg-light fw-semi-bold">App\Models\</span>
                                    <input type="text" name="model_name" class="form-control" id="inputModelName" placeholder="Model" value="{{ old('model_name', '') }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-5 ps-1">
                            <div class="mb-3">
                                <label class="form-label text-uppercase fw-semi-bold text-muted mb-1 ms-1" for="inputControllerName">
                                    <small>Controller</small>
                                </label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text bg-light fw-semi-bold">App\Http\Controllers\</span>
                                    <input type="text" name="controller_name" class="form-control" id="inputControllerName" placeholder="YourController" value="{{ old('controller_name', '') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-0">
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="check-migration" class="form-check-input" checked value="migration" name="create[]" /> 
                            <label class="form-check-label" for="check-migration">Buat file migration</label>
                        </div>

                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="check-model" class="form-check-input" checked value="model" name="create[]" /> 
                            <label class="form-check-label" for="check-model">Buat file model</label>
                        </div>

                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="check-controller" class="form-check-input" checked value="controller" name="create[]" /> 
                            <label class="form-check-label" for="check-controller">Buat file controller</label>
                        </div>

                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="check-views" class="form-check-input" checked value="views" name="create[]" /> 
                            <label class="form-check-label" for="check-views">Buat file views</label>
                        </div>

                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="check-migrate" class="form-check-input" checked value="migrate" name="create[]" /> 
                            <label class="form-check-label" for="check-migrate">Eksekusi <b>php artisan migrate</b></label>
                        </div>

                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="check-menu" class="form-check-input" checked value="menu" name="create[]" /> 
                            <label class="form-check-label" for="check-menu">Tambahkan menu</label>
                        </div>
                    </div>
                </div>

                <table id="table-fields" class="table table-hover table-align-middle table-sm border-top">
                    <thead class="thead-light">
                        <tr>
                            <th>Field name</th>
                            <th width="22%">Type</th>
                            <th width="1">Nullable</th>
                            <th width="15%">Key</th>
                            <th>Default value</th>
                            <th width="1">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(old('fields'))
                            @foreach(old('fields') as $index => $field)
                                <tr>
                                    <td>
                                        <input type="text" name="fields[{{$index}}][name]" class="form-control" placeholder="field name" value="{{$field['name']}}" />
                                    </td>
                                    <td>
                                        <div class="tom-select-custom">
                                            <select name="fields[{{$index}}][type]" class="js-select form-select">
                                                @foreach($dbTypes as $type)
                                                    <option value="{{ $type }}" {{$field['type'] == $type ? 'selected' : '' }}>{{$type}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" class="form-check-input" name="fields[{{$index}}][nullable]" {{ \Illuminate\Support\Arr::get($field, 'nullable') == 'on' ? 'checked': '' }}/>
                                            <label class="form-check-label"></label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="tom-select-custom">
                                            <select name="fields[{{$index}}][key]" class="js-select form-select">
                                                <option value="" {{$field['key'] == '' ? 'selected' : '' }}>NULL</option>
                                                <option value="unique" {{$field['key'] == 'unique' ? 'selected' : '' }}>Unique</option>
                                                <option value="index" {{$field['key'] == 'index' ? 'selected' : '' }}>Index</option>
                                            </select>
                                        </div>
                                    </td>
                                    <td><input type="text" class="form-control" placeholder="default value" name="fields[{{$index}}][default]" value="{{$field['default']}}"/></td>
                                    <td><a class="btn btn-sm btn-danger table-field-remove"><i class="fa fa-trash"></i> remove</a></td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td>
                                    <input type="text" name="fields[0][name]" class="form-control form-control-sm" placeholder="Nama field" />
                                </td>
                                <td>
                                    <select name="fields[0][type]" class="js-select form-select">
                                        @foreach($dbTypes as $type)
                                            <option value="{{ $type }}">{{$type}}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td align="center">
                                    <input type="checkbox" class="form-check-input" name="fields[0][nullable]" />
                                </td>
                                <td>
                                    <select name="fields[0][key]" class="js-select form-select" data-nue-tom-select-options='{
                                        "hideSearch": true
                                    }'>
                                        <option value="NULL" selected>NULL</option>
                                        <option value="unique">Unique</option>
                                        <option value="index">Index</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm" placeholder="Nilai default" name="fields[0][default]">
                                </td>
                                <td align="center">
                                    <a class="h2 text-danger table-field-remove">
                                        <span class="iconify" data-icon="heroicons-solid:x"></span>
                                    </a>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>

                <div class="d-flex justify-content-between p-2">
                    <div class='form-group'>
                        <button type="button" class="btn btn-sm btn-success" id="add-table-field">
                            <span class="iconify" data-icon="heroicons-solid:plus"></span>
                            Add field
                        </button>
                    </div>
                    <div class="text-end">
                        <div class="d-flex align-items-center">
                            <div class="form-group d-flex align-items-center me-3">
                                <label for="inputPrimaryKey" class="me-2">Primary key</label>
                                <input type="text" name="primary_key" class="form-control form-control-sm" id="inputPrimaryKey" placeholder="Primary key" value="id" style="width: 100px;">
                            </div>

                            <div class="form-check form-check-inline">
                                <input type="checkbox" id="check-timestamps" class="form-check-input" checked name="timestamps" /> 
                                <label class="form-check-label" for="check-timestamps">
                                    Timestamps
                                </label>
                            </div>

                            <div class="form-check form-check-inline">
                                <input type="checkbox" id="check-soft_deletes" class="form-check-input" checked name="soft_deletes" /> 
                                <label class="form-check-label" for="check-soft_deletes">
                                    Soft deletes
                                </label>
                            </div>
                            
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-info pull-right">
                        Buat Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>

    <template id="table-field-tpl">
        <tr>
            <td>
                <input type="text" name="fields[__index__][name]" class="form-control form-control-sm" placeholder="Nama field" />
            </td>
            <td>
                <select name="fields[__index__][type]" class="js-select-custom form-select">
                    @foreach($dbTypes as $type)
                        <option value="{{ $type }}">{{$type}}</option>
                    @endforeach
                </select>
            </td>
            <td align="center">
                <input type="checkbox" class="form-check-input" name="fields[__index__][nullable]" />
            </td>
            <td>
                <select name="fields[__index__][key]" class="js-select-custom form-select" data-nue-tom-select-options='{
                    "hideSearch": true
                }'>
                    <option value="NULL" selected>NULL</option>
                    <option value="unique">Unique</option>
                    <option value="index">Index</option>
                </select>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" placeholder="Nilai default" name="fields[__index__][default]">
            </td>
            <td align="center">
                <a class="h2 text-danger table-field-remove">
                    <span class="iconify" data-icon="heroicons-solid:x"></span>
                </a>
            </td>
        </tr>
    </template>
@endsection