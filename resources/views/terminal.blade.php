@extends('layouts.app')
@section('title', 'Terminal')

@section('js')
    <script>
        $(function () {
            var storageKey = function () {
                var connection = $('#connections').val();
                return 'la-'+connection+'-history'
            };
            function History () {
                this.index = this.count() - 1;
            }
            History.prototype.store = function () {
                var history = localStorage.getItem(storageKey());
                if (!history) {
                    history = [];
                } else {
                    history = JSON.parse(history);
                }
                return history;
            };
            History.prototype.push = function (record) {
                var history = this.store();
                history.push(record);
                localStorage.setItem(storageKey(), JSON.stringify(history));
                this.index = this.count() - 1;
            };
            History.prototype.count = function () {
                return this.store().length;
            };
            History.prototype.up = function () {
                if (this.index > 0) {
                    this.index--;
                }
                return this.store()[this.index];
            };
            History.prototype.down = function () {
                if (this.index < this.count() - 1) {
                    this.index++;
                }
                return this.store()[this.index];
            };
            History.prototype.clear = function () {
                localStorage.removeItem(storageKey());
            };
            var history = new History;
            var send = function () {
                var $input = $('#terminal-query');
                $.ajax({
                    url:location.pathname,
                    method: 'post',
                    data: {
                        c: $input.val(),
                        _token: $('meta[name="csrf-token"]').attr('content'),
                    },
                    success: function (response) {
                        history.push($input.val());
                        $('#terminal-box')
                            .append('<div class="item"><small class="badge bg-secondary mb-2"> > php artisan '+$input.val()+'<\/small><\/div>')
                            .append('<div class="item">'+response+'<\/div>')
                            // .slimScroll({ scrollTo: $("#terminal-box")[0].scrollHeight });
                        $input.val('');
                    }
                });
            };
            $('#terminal-query').on('keyup', function (e) {
                e.preventDefault();
                if (e.keyCode == 13) {
                    send();
                }
                if (e.keyCode == 38) {
                    $(this).val(history.up());
                }
                if (e.keyCode == 40) {
                    $(this).val(history.down());
                }
            });
            $('#terminal-clear').click(function () {
                $('#terminal-box').text('');
                //history.clear();
            });
            $('.loaded-command').click(function () {
                $('#terminal-query').val($(this).html() + ' ');
                $('#terminal-query').focus();
            });
            $('#terminal-send').click(function () {
                send();
            });
        });
        </script>
@endsection

@section('content')

    <div class="card rounded-0 border-0">
        <div class="card-header">
            <span class="iconify me-1" data-icon="heroicons-solid:terminal"></span>
            Terminal
        </div>
        <div class="card-body bg-dark chat" id="terminal-box" style="height: calc(100vh - 215px);overflow: scroll;"></div>
        <div class="card-footer p-0">
            <div class="input-group rounded-0">
                <span class="input-group-text bg-light rounded-0">
                    <span class="iconify me-1" data-icon="heroicons-solid:terminal"></span>
                    <b>php artisan</b>
                </span>
                <input type="text" class="form-control" id="terminal-query" placeholder="eg. route:list">
                <button type="button" class="btn btn-success" id="terminal-send">
                    <span class="iconify" data-icon="heroicons-solid:arrow-circle-right"></span>
                    Send
                </button>
                <button type="button" class="btn btn-warning rounded-0" id="terminal-clear">
                    <span class="iconify" data-icon="heroicons-solid:x-circle"></span>
                    Clear
                </button>
            </div>
            <div class="bg-light p-2">
                <div class="btn-group ms-1">
                    @foreach($commands['groups'] as $group => $command)
                        <div class="dropup ms-n1">
                            <button type="button" class="btn btn-sm btn-secondary rounded-0 dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ $group }}
                            </button>
                            <div class="dropdown-menu">
                                @foreach($command as $item)
                                    <a href="#" class="dropdown-item loaded-command">{{$item}}</a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <div class="dropup ms-n1">
                        <button type="button" class="btn btn-sm btn-secondary rounded-0 dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            Others
                        </button>
                        <div class="dropdown-menu">
                            @foreach($commands['others'] as $item)
                                <a href="#" class="dropdown-item loaded-command">{{$item}}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection