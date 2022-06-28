<?php

namespace Nue\Setting\Scaffold;

class ViewCreator
{
    /**
     * Table name.
     *
     * @var string
     */
    protected $name;

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    protected $DummyColumnScript = '';
    protected $DummyColumnTable = '';
    protected $DummyForms = '';

    /**
     * ViewCreator constructor.
     *
     * @param string $name
     * @param null   $files
     */
    public function __construct($name, $files = null)
    {
        $this->name = $name;

        $this->files = $files ?: app('files');
    }

    /**
     * Create a views.
     *
     * @param string $model
     *
     * @throws \Exception
     *
     * @return string
     */
    public function create($model, $fields)
    {
        $path = $this->getPath($this->name);

        $this->generateColumnScript($fields, $model, $path);
        $this->generateForms($fields, $model, $path);

        $this->files->put($path . "/create.blade.php", $this->files->get(__DIR__.'/stubs/views/create.blade.stub'));
        $this->files->put($path . "/edit.blade.php", $this->files->get(__DIR__.'/stubs/views/edit.blade.stub'));

        return $path;
    }

    /**
     * Get file path from giving controller name.
     *
     * @param $name
     *
     * @return string
     */
    public function getPath($name)
    {
        $path = resource_path('views/nue/'.$name);

        if (!$this->files->isDirectory($dir = $path)) {
            $this->files->makeDirectory($dir, 0777, true);
        }

        return $path;
    }

    /**
     * @param string $stub
     * @param string $name
     * @param string $model
     *
     * @return string
     */
    protected function replace($stub, $name, $model)
    {
        $stub = $this->replaceClass($stub, $name);

        return str_replace(
            ['DummyModelNamespace', 'DummyModel', 'DummyViewName', 'DummyColumnScript', 'DummyColumnTable', 'DummyForms'],
            [$model, class_basename($model), strtolower(class_basename($model)), $this->DummyColumnScript, $this->DummyColumnTable, $this->DummyForms],
            $stub
        );
    }

    /**
     * Get controller namespace from giving name.
     *
     * @param string $name
     *
     * @return string
     */
    protected function getNamespace($name)
    {
        return trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param string $stub
     * @param string $name
     *
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $class = str_replace($this->getNamespace($name).'\\', '', $name);

        return str_replace(['DummyClass', 'DummyNamespace'], [$class, $this->getNamespace($name)], $stub);
    }

    /**
     * Get stub file path.
     *
     * @return string
     */
    public function getStub()
    {
        return __DIR__.'/stubs/controller.stub';
    }

    public function generateColumnScript($fields = [], $model, $path)
    {
        $fields = array_filter($fields, function ($field) {
            return isset($field['name']) && !empty($field['name']);
        });

        if (empty($fields)) {
            throw new \Exception('Table fields can\'t be empty');
        }
        
        foreach ($fields as $field) {
            $rows[] = "{ data: '{$field['name']}', name: '{$field['name']}' },\n";
            $table[] = "<th>".strtoupper($field['name'])."</th>\n";
        }

        $this->DummyColumnScript = trim(implode(str_repeat(' ', 12), $rows), "\n");
        $this->DummyColumnTable = trim(implode(str_repeat(' ', 12), $table), "\n");

        $stub = $this->files->get(__DIR__.'/stubs/views/index.blade.stub');
        $this->files->put($path . "/index.blade.php", $this->replace($stub, $this->name, $model));

        return $this;
    }

    public function generateForms($fields = [], $model, $path)
    {
        $fields = array_filter($fields, function ($field) {
            return isset($field['name']) && !empty($field['name']);
        });

        if (empty($fields)) {
            throw new \Exception('Table fields can\'t be empty');
        }

        foreach ($fields as $field) {
                $rows[] = "<div class=\"form-group mb-1 row align-items-center\">
        <label class=\"col-sm-3 fw-semi-bold text-muted\" for=\"{$field['name']}\">
            ".ucwords($field['name'])." <span class=\"text-danger\">*</span>
        </label>
        <div class=\"col-sm-9\">
            {!! Form::text('{$field['name']}', null, ['class' => 'form-control form-control-sm' . \$errors->first('{$field['name']}', ' is-invalid')]) !!}
            {!! \$errors->first('{$field['name']}', '<small class=\"text-danger\">:message</small>') !!}
        </div>
    </div>\n";
        }

        $this->DummyForms = trim(implode(str_repeat(' ', 4), $rows), "\n");

        $stub = $this->files->get(__DIR__.'/stubs/views/form.blade.stub');
        $this->files->put($path . "/form.blade.php", $this->replace($stub, $this->name, $model));

        return $this;
    }
}