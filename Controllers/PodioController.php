<?php

namespace App\Plugins\Podio\Controllers;

use App\Http\Controllers\Controller;
use App\Plugins\Podio\Model\Podio;

/**
 *@version 1.0.0
 */
class PodioController extends Controller
{
    /**
     * @category constructor fucntion include library file PodioApi.php
     */
    public function __construct()
    {
        require_once base_path().
            DIRECTORY_SEPARATOR.
            'App\Plugins\Podio\podio-php\PodioAPI.php'; // Require Podio client Library file to work with Podio API
    }

    /**
     *@category function to authenicate system using app authentication
     *
     *@param null
     *
     *@return String/boolean $result
     */
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

    /**
     *@category function to check if user already exists as a client in podio or not
     *
     *@param int $u_id
     *
     *@return int 0/1
     */
    public function checkClientExists($u_id)
    {
        $item = \DB::table('podio_client_item')
                ->select('podio_item_id')
                ->where('user_id', '=', $u_id)->get();
        if (count($item) == 0) {
            return 0;
        } else {
            $item_id = $item[0];

            return $item_id->podio_item_id;
        }
    }

    /**
     *@category function to check if ticket is reply or a new ticket
     *
     *@param int $ticket_id
     *
     *@return int 1/0;
     */
    public function checkTicketExists($ticket_id)
    {
        $item = \DB::table('podio_ticket_item')
                ->select('podio_item_id')
                ->where('ticket_id', '=', $ticket_id)->get();
        if (count($item) == 0) {
            return 0;
        } else {
            $item_id = $item[0];

            return $item_id->podio_item_id;
        }
    }

    /**
     *@category function to create a new User(item) in Podio's Client app
     *
     *@param int $u_id
     *
     *@return int $result(id of newly created item)
     */
    public function createNewClient($u_id)
    {
        $is_exist = $this->checkClientExists($u_id);
        if ($is_exist == 0) {
            $auth = $this->authenticate();
            if ($auth == true) {
                $data = \DB::table('podio')
                        ->select('client_app_id')
                        ->where('id', '=', 1)->first();
                $app_id = $data->client_app_id;
                $attr = [
                    'external_id' => 'item1',
                    'fields'      => [
                        'name'  => 'Manish',
                        'email' => [
                            'type'  => 'work',
                            'value' => 'mansa@gmail.com',
                        ],
                        'phone' => [
                            'type'  => 'work',
                            'value' => '8233077144',
                        ],
                    ],
                ];
                $opt = [];
                $result = \PodioItem::create($app_id, $attr, $opt);
                $result = $result->item_id;
                \DB::table('podio_client_item')->insert(
                    ['user_id' => 11, 'podio_item_id' => $result]
                );

                return $result;
            }
        } else {
            return $is_exist;
        }
    }

    /**
     *@category function to create a new tickets or comment on available tickets in the app
     *
     *@param
     *
     *@return
     */
    public function createPodioTicket()
    {
        $is_exist = $this->checkTicketExists(112);
        if ($is_exist == 0) {
            $auth = $this->authenticate();
            if ($auth == true) {
                $client_reference_id = (int) $this->createNewClient(11);
                $data = \DB::table('podio')
                        ->select('faveo_app_id')
                        ->where('id', '=', 1)->first();
                $app_id = $data->faveo_app_id;
                $attr = [
                    'external_id' => 'ticket1',
                    'fields'      => [
                        'ticket-number' => 'AVCD123',
                        'subjetc'       => 'Tester',
                        'description'   => 'testing ticket for podio',
                        'from'          => $client_reference_id,
                        'created-at'    => [
                            'start' => '2011-12-31 11:27:10',
                            'end'   => '2012-01-31 11:28:20',
                        ],
                        'priority' => 1,
                        'status'   => 2,
                    ],
                ];
                $opt = [];
                $result = \PodioItem::create($app_id, $attr, $opt);
                $result = $result->item_id;
                \DB::table('podio_ticket_item')->insert(
                    ['ticket_id' => 112, 'podio_item_id' => $result]
                );
            }
        } else {
            // modify ticket
        }
    }
}
