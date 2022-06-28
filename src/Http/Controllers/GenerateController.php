<?php

namespace Nue\Setting\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\MessageBag;

use Nue\Setting\Scaffold\ControllerCreator;
use Nue\Setting\Scaffold\MigrationCreator;
use Nue\Setting\Scaffold\ModelCreator;
use Nue\Setting\Scaffold\ViewCreator;
use Novay\Nue\Extension;

class GenerateController extends Controller
{
    protected $title = 'Scaffold';

    public function __construct() 
    {
        view()->share([
            'title' => $this->title
        ]);
    }

    public function index()
    {
        $dbTypes = [
            'string', 'integer', 'text', 'float', 'double', 'decimal', 'boolean', 
            'date', 'time', 'dateTime', 'timestamp', 'char', 'mediumText', 'longText', 
            'tinyInteger', 'smallInteger', 'mediumInteger', 'bigInteger', 
            'unsignedTinyInteger', 'unsignedSmallInteger', 'unsignedMediumInteger', 
            'unsignedInteger', 'unsignedBigInteger', 'enum', 'json', 
            // 'jsonb', 'dateTimeTz', 'timeTz', 'timestampTz', 'nullableTimestamps', 'binary', 
            // 'ipAddress', 'macAddress',
        ];

        $action = URL::current();

        return view('nue-settings::generate', compact('dbTypes', 'action'));
    }

    public function store(Request $request)
    {
        $paths = [];
        $message = '';

        try {

            // 1. Create model.
            if (in_array('model', $request->get('create'))) {
                $modelCreator = new ModelCreator($request->get('table_name'), $request->get('model_name'));

                $paths['model'] = $modelCreator->create(
                    $request->get('primary_key'),
                    $request->get('fields'), 
                    $request->get('timestamps') == 'on',
                    $request->get('soft_deletes') == 'on'
                );
            }

            // 2. Create controller.
            if (in_array('controller', $request->get('create'))) {
                $paths['controller'] = (new ControllerCreator($request->get('controller_name')))
                    ->create($request->get('model_name'), $request->get('fields'));
            }

            // 3. Create migration.
            if (in_array('migration', $request->get('create'))) {
                $migrationName = 'create_'.$request->get('table_name').'_table';

                $paths['migration'] = (new MigrationCreator(app('files')))->buildBluePrint(
                    $request->get('fields'),
                    $request->get('primary_key', 'id'),
                    $request->get('timestamps') == 'on',
                    $request->get('soft_deletes') == 'on'
                )->create($migrationName, database_path('migrations'), $request->get('table_name'));
            }

            // 4. Run migrate.
            if (in_array('migrate', $request->get('create'))) {
                Artisan::call('migrate');
                $message = Artisan::output();
            }

            // 5. Create views
            if (in_array('views', $request->get('create'))) {
                $paths['views'] = (new ViewCreator($request->get('table_name')))
                    ->create($request->get('model_name'), $request->get('fields'));
            }

            // 6. Adding menu inside nue
            if (in_array('menu', $request->get('create'))) {
                Extension::createMenu(ucfirst($request->table_name), "nue/{$request->table_name}", 'entypo:new');
                Extension::createPermission(ucfirst($request->table_name), "ext.{$request->table_name}", "{$request->table_name}*");
            }

        } catch (\Exception $exception) {

            // Delete generated files if exception thrown.
            app('files')->delete($paths);

            return $this->backWithException($exception);
        }

        return $this->backWithSuccess($paths, $message);
    }

    protected function backWithException(\Exception $exception)
    {
        notify()->flash($exception->getMessage(), 'danger');

        return redirect()->back()->withInput();
    }

    protected function backWithSuccess($paths, $message)
    {
        $messages = [];

        foreach ($paths as $name => $path) {
            $messages[] = ucfirst($name).": $path";
        }

        $messages[] = "<br />$message";

        $success = new MessageBag([
            'title'   => 'Success',
            'message' => implode('<br />', $messages),
        ]);

        notify()->flash(implode('<br />', $messages), 'success');

        return back()->with(compact('success'));
    }
}