<?php

namespace Nue\Setting\Scaffold;

class ControllerCreator
{
    /**
     * Controller full name.
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

    protected $DummyIndex = '';

    protected $DummyStore = '';

    protected $DummyUpdate = '';

    /**
     * ControllerCreator constructor.
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
     * Create a controller.
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

        if ($this->files->exists($path)) {
            throw new \Exception("Controller [$this->name] already exists!");
        }

        $this->generateIndex($fields);
        $this->generateStore($fields);
        $this->generateUpdate($fields);

        $stub = $this->files->get($this->getStub());

        $this->files->put($path, $this->replace($stub, $this->name, $model));

        $this->generateRoute($model);

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
            ['DummyModelNamespace', 'DummyModel', 'DummyViewName', 'DummyIndex', 'DummyStore', 'DummyUpdate'],
            [$model, class_basename($model), strtolower(class_basename($model)), $this->DummyIndex, $this->DummyStore, $this->DummyUpdate],
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
     * Get file path from giving controller name.
     *
     * @param $name
     *
     * @return string
     */
    public function getPath($name)
    {
        $segments = explode('\\', $name);

        $path = app_path('Http/Controllers/'.implode('/', $segments)).'.php';

        if (!$this->files->isDirectory($dir = dirname($path))) {
            $this->files->makeDirectory($dir, 0777, true);
        }

        return $path;
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

    public function generateIndex($fields = [])
    {
        $fields = array_filter($fields, function ($field) {
            return isset($field['name']) && !empty($field['name']);
        });

        if (empty($fields)) {
            throw new \Exception('Table fields can\'t be empty');
        }
        foreach ($fields as $field) {
            $rows[] = "->editColumn('{$field['name']}', function(\$data) {
            return \$data->{$field['name']};
        })\n";
        }

        $this->DummyIndex = trim(implode(str_repeat(' ', 12), $rows), "\n");

        return $this;
    }

    public function generateStore($fields = [])
    {
        $fields = array_filter($fields, function ($field) {
            return isset($field['name']) && !empty($field['name']);
        });

        if (empty($fields)) {
            throw new \Exception('Table fields can\'t be empty');
        }
        foreach ($fields as $field) {
            $rows[] = "'{$field['name']}' => 'required',\n";
        }

        $this->DummyStore = trim(implode(str_repeat(' ', 12), $rows), "\n");

        return $this;
    }

    public function generateUpdate($fields = [])
    {
        $fields = array_filter($fields, function ($field) {
            return isset($field['name']) && !empty($field['name']);
        });

        if (empty($fields)) {
            throw new \Exception('Table fields can\'t be empty');
        }

        foreach ($fields as $field) {
            $rows[] = "'{$field['name']}' => 'required',\n";
        }

        $this->DummyUpdate = trim(implode(str_repeat(' ', 12), $rows), "\n");

        return $this;
    }

    public function generateRoute($model)
    {
        $stub = $this->files->get(__DIR__.'/stubs/routes.stub');

        $this->files->append(base_path('routes/web.php'), $this->replace($stub, $this->name, $model));

        return $this;
    }
}