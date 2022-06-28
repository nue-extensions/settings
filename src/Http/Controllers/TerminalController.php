<?php

namespace Nue\Setting\Http\Controllers;

use Exception;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Request;

use Symfony\Component\Console\Input\ArgvInput;
use Nue\Setting\Services\StringOutput;

class TerminalController extends Controller
{
    public function index(Request $request)
    {
        $commands = $this->organizedCommands();

        return view('nue-settings::terminal', compact('commands'));
    }

    public function store()
    {
        $command = Request::get('c', 'list');

        if (1 === Artisan::handle(
            new ArgvInput(explode(' ', 'artisan '.trim($command))),
            $output = new StringOutput()
        )) {
            return $this->renderException(new Exception($output->getContent()));
        }

        return sprintf('<pre style="color:#00c9a6">%s</pre>', $output->getContent());
    }

    protected function renderException(Exception $exception)
    {
        return sprintf(
            "<div class='alert bg-warning rounded-0 mt-1'>
                <span class=\"iconify\" data-icon=\"heroicons-solid:information-circle\"></span>
                &nbsp;&nbsp;&nbsp;%s
            </div>",
            str_replace("\n", '<br />', $exception->getMessage())
        );
    }

    protected function organizedCommands()
    {
        $commands = array_keys(Artisan::all());

        $groups = $others = [];

        foreach ($commands as $command) {
            $parts = explode(':', $command);

            if (isset($parts[1])) {
                $groups[$parts[0]][] = $command;
            } else {
                $others[] = $command;
            }
        }

        foreach ($groups as $key => $group) {
            if (count($group) === 1) {
                $others[] = $group[0];

                unset($groups[$key]);
            }
        }

        ksort($groups);
        sort($others);

        return compact('groups', 'others');
    }
}