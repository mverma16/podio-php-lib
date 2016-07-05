<?php

namespace App\Plugins\Podio\Controllers;

use App\Http\Controllers\Controller;
use App\Plugins\Podio\Model\Podio;
use Illuminate\Http\Request;
use Input;
use Schema;

/**
 *@version 1.0.0
 */
class PodioController extends Controller
{
	 /**
     * @category constructor fucntion to call createPodioTable()
     */
    public function __construct()
    {
        require_once base_path().
            DIRECTORY_SEPARATOR.
            'App\Plugins\Podio\podio-php\PodioAPI.php'; // Require Podio client Library file to work with Podio API
        if (!Schema::hasTable('podio')) {
            $this->createPodioTable();
        } else {
            $podio_user = \DB::table('podio')
            ->where('id', '=', 1)->first();
            // dd($podio_user);
        }
    }

	public function show()
	{
		$auth = $this->authenticate();
		if ($auth == true) {
			$app_id = 16199969;
			$attr = [
  				"external_id" => "item1",
  				"fields" => [
    				"name" => "value",
    				"email" => [
    				    "type" => "work",
    				    "value" => "mansa@gmail.com"
    				],
    				"phone" => [
    				    "type" => "work",
    				    "value" => "78978978798"
    				],
    			],
			];
			$opt = [];
			$result = \PodioItem::create( $app_id, $attr, $opt);
			return $result;
		}
	}

	public function authenticate()
	{
		$podio = Podio::select('client_secret', 'client_id',
			'faveo_app_id', 'faveo_app_token')->where('id', '=', 1)->first();
		$client_id = $podio->client_id;
		$client_secret = $podio->client_secret;
		$app_id = $podio->faveo_app_id;
		$app_token = $podio->faveo_app_token;
		\Podio::setup($client_id, $client_secret);
        $result = \Podio::authenticate_with_app($app_id, $app_token);
        return $result;
	}
}