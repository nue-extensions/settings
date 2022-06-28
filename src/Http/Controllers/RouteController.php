<?php

namespace Nue\Setting\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;

class RouteController extends Controller
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Route List';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() 
    {
        view()->share([
            'title' => $this->title
        ]);
    }

    /**
     * Index interface.
     *
     * @param Request $request
     *
     * @return Illuminate\View\View
     */
    public function index(Request $request)
    {
        $data = $this->getRoutes();

        if($request->has('datatable')):
            return $this->datatable($data);
        endif;

        return view('nue-settings::routes', compact('data'));
    }

    public function colors()
    {
        return [
            'GET'    => 'success',
            'HEAD'   => 'secondary',
            'POST'   => 'primary',
            'PUT'    => 'warning',
            'DELETE' => 'danger',
            'PATCH'  => 'info',
            'OPTIONS'=> 'dark',
        ];
    }

    public function getRoutes()
    {
        $routes = app('router')->getRoutes();

        $routes = collect($routes)->map(function ($route) {
            return $this->getRouteInformation($route);
        })->all();

        return array_filter($routes);
    }

    /**
     * Get the route information for a given route.
     *
     * @param \Illuminate\Routing\Route $route
     *
     * @return array
     */
    protected function getRouteInformation(Route $route)
    {
        return [
            'host'       => $route->domain(),
            'method'     => $route->methods(),
            'uri'        => $route->uri(),
            'name'       => $route->getName(),
            'action'     => $route->getActionName(),
            'middleware' => $this->getRouteMiddleware($route),
        ];
    }

    /**
     * Get before filters.
     *
     * @param \Illuminate\Routing\Route $route
     *
     * @return string
     */
    protected function getRouteMiddleware($route)
    {
        return collect($route->gatherMiddleware())->map(function ($middleware) {
            return $middleware instanceof \Closure ? 'Closure' : $middleware;
        });
    }

    /**
     * Datatable API
     * 
     * @param  $data
     * @return Datatable
     */
    public function datatable($data) 
    {
        return datatables()->of($data)
            ->editColumn('method', function($data) {
                $method = $data['method'];
                $colors = $this->colors();

                return collect($method)->map(function ($name) {
                    return strtoupper($name);
                })->map(function ($name) use ($colors) {
                    return "<span class='badge bg-{$colors[$name]}'>{$name}</span>";
                })->implode('&nbsp;');
            })
            ->editColumn('uri', function($data) {
                return preg_replace('/\{.+?\}/', '<small><code>$0</span></small>', $data['uri']);
            })
            ->editColumn('name', function($data) {
                return $data['name'];
            })
            ->editColumn('action', function($data) {
                return preg_replace('/@.+/', '<code>$0</code>', $data['action']);
            })
            ->editColumn('middleware', function($data) {
                if ($data['middleware'] instanceof \Illuminate\Contracts\Support\Arrayable) {
                    $data['middleware'] = $data['middleware']->toArray();
                }

                return collect((array) $data['middleware'])->map(function ($name) {
                    return "<span class='badge bg-warning'>$name</span>";
                })->implode('&nbsp;');
            })
            ->escapeColumns(['*'])->toJson();
    }
}