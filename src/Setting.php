<?php

namespace Nue\Setting;

use Novay\Nue\Extension;
use Novay\Nue\Nue;
use Novay\Nue\Models\Menu;

class Setting extends Extension
{
    public $name = 'settings';

    public $views = __DIR__.'/../resources/views';

    /**
     * Bootstrap this package.
     *
     * @return void
     */
    public static function boot()
    {
        Nue::extend('helpers', __CLASS__);
    }

    /**
     * Enable this function if you want to automatically inject menu & permission
     * for your package into nue.
     * 
     * {@inheritdoc}
     */
    public static function import()
    {
        $lastOrder = Menu::max('order') ?: 0;

        $root = [
            'parent_id' => 0,
            'order'     => $lastOrder++,
            'title'     => 'Helpers',
            'icon'      => 'icon-park-twotone:folder-code',
            'uri'       => 'helpers',
        ];

        $root = Menu::create($root);

        $menus = [
            [
                'title'     => 'Routes',
                'icon'      => 'icon-park-twotone:copy-link',
                'uri'       => 'helpers/routes',
            ],
            [
                'title'     => 'Terminal',
                'icon'      => 'icon-park-twotone:terminal',
                'uri'       => 'helpers/terminal',
            ],
            [
                'title'     => 'Generators',
                'icon'      => 'icon-park-twotone:game-console',
                'uri'       => 'helpers/generate',
            ],
        ];

        foreach ($menus as $menu) {
            $menu['parent_id'] = $root->id;
            $menu['order'] = $lastOrder++;

            Menu::create($menu);
        }

        parent::createPermission('Helpers', 'ext.helpers', 'helpers/*');
    }
}