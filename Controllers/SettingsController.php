<?php

namespace App\Plugins\Podio\Controllers;

use App\Http\Controllers\Controller;
use Schema;

/**
 *@version 1.0.0
 */
class SettingsController extends Controller
{
    /**
     * @category constructor fucntion to call createPodioTable()
     */
    public function __construct()
    {
        require_once base_path().
            DIRECTORY_SEPARATOR.
            'App\Plugins\Podio\podio-php\PodioAPI.php'; // Require Podio client Library file to work with Podio API
        if (!Schema::hasTable('social-login')) {
            $this->createPodioTable();
        } else {
            $podio_user = DB::table('podio')
            ->where('id', '=', 1)->first();
            dd($podio_user);
        }
    }

    public function index()
    {
        // \\require_once(base_path().DIRECTORY_SEPARATOR.'App\Plugins\Podio\podio-php\PodioAPI.php');
        // $path = app_path() . '/Plugins/Podio/views';
        // \View::addNamespace('plugins', $path);
        // return view('plugins::settings');
        \Podio::setup('faveo', 'eolBpcfIblNo8KC5A44qvyzMRDgklV18rTWkDmQJZdYbe0u86yrFyYIj8csAey0l');
        \Podio::authenticate_with_app('16046199', '6166341015f049b48a7f4ef143e79cff');
        $items = \PodioItem::filter('16046199');

        echo 'My app has '.count($items).' items';
    }

    /**
     *@category function to create a table "Podio" in database if does not exist
     *
     *@param null
     *
     *@author manish.verma@ladybirdweb.com
     *
     *@return null;
     */
    public function createPodioTable()
    {
        $this->seedPodio();
    }

    /**
     *@category function to seed Podio table if exists
     *
     *@param null
     *
     *@author manish.verma@ladybirdweb.com
     *
     *@return null
     */
    public function seedPodio()
    {
        //do something
    }
}
