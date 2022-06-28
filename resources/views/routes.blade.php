@extends('layouts.app')
@section('title', 'Route List')

@section('js')
    <script>
        var table = Nue.components.NueDatatables.init('.js-datatable', {
        	order: [['1', 'asc']], 
            scrollY: 'calc(100vh - 250px)',
            scrollX: true,
            scrollCollapse: true,
            fixedColumns: true,
            ajax : '{!! request()->fullUrl() !!}?datatable=true', 
            columns: [
                { data: 'method', name: 'method', className: 'bg-white border-bottom-0' },
                { data: 'uri', name: 'uri' },
                { data: 'name', name: 'name' },
                { data: 'action', name: 'action' },
                { data: 'middleware', name: 'middleware' }
            ],
            @include('nue::partials.datatable.script')
        });
    </script>
@endsection

@section('content')

	@include('nue::partials.breadcrumb', ['lists' => [
        'Extensions' => 'javascript:;', 
        $title => 'active'
    ]])

    @include('nue::partials.datatable.header', [
        'title' => 'Route List', 
        'description' => 'Here is a list of all your data from your database.', 
        'datatable' => true
    ])

	<div class="card card-bordered shadow-none rounded-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="datatable" class="js-datatable table table-sm table-hover table-nowrap table-thead-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Method</th>
                            <th>URI</th>
                            <th>Name</th>
                            <th>Action</th>
                            <th>Middleware</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        @include('nue::partials.datatable.footer')
    </div>
@endsection