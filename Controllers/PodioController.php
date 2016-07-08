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
        require_once base_path().DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Plugins'.DIRECTORY_SEPARATOR.'Podio'.DIRECTORY_SEPARATOR.'podio-php'.DIRECTORY_SEPARATOR.'PodioAPI.php'; // Require Podio client Library file to work with Podio API
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
        // dd($u_id);
        $is_exist = $this->checkClientExists($u_id);
        if ($is_exist == 0) {
            $auth = $this->authenticate();
            if ($auth == true) {
                $data = \DB::table('podio')
                        ->select('client_app_id')
                        ->where('id', '=', 1)->first();
                $app_id = $data->client_app_id;
                $user_data = \DB::table('users')
                            ->select('user_name', 'first_name', 'last_name', 'email', 'country_code', 'phone_number', 'mobile')
                            ->where('id', '=', $u_id)->first();
                // dd($user_data->country_code);
                if ($user_data->first_name == '') {
                    $name = $user_data->user_name;
                } else {
                    $name = $user_data->first_name.' '.$user_data->last_name;
                }
                if ($user_data->country_code == 0 || $user_data->country_code == '') {
                    $phone = 'Not available';
                } else {
                    if ($user_data->phone_number == '') {
                        $phone = '+'.$user_data->country_code.' '.$user_data->mobile;
                    } elseif ($user_data->mobile == '') {
                        $phone = '+'.$user_data->country_code.' '.$user_data->phone_number;
                    } else {
                        $phone = '+'.$user_data->country_code.' '.$user_data->phone_number;
                    }
                }
                $attr = [
                    'external_id' => 'item1',
                    'fields'      => [
                        'name'  => $name,
                        'email' => [
                            'type'  => 'work',
                            'value' => $user_data->email,
                        ],
                        'phone' => [
                            'type'  => 'work',
                            'value' => $phone,
                        ],
                    ],
                ];
                $opt = [];
                $result = \PodioItem::create($app_id, $attr, $opt);
                $result = $result->item_id;
                \DB::table('podio_client_item')->insert(
                    ['user_id' => $u_id, 'podio_item_id' => $result]
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
    public function createPodioTicket($events)
    {
        $ticket_id = $events['ticket_number'];
        $u_id = $events['user_id'];
        $is_exist = $this->checkTicketExists($ticket_id);
        if ($is_exist == 0) {
            $auth = $this->authenticate();
            if ($auth == true) {
                $client_reference_id = (int) $this->createNewClient($u_id);
                // dd($events);
                $data = \DB::table('podio')
                        ->select('faveo_app_id')
                        ->where('id', '=', 1)->first();
                $app_id = $data->faveo_app_id;
                $subjetc = $events['subject'];
                $body = $events['body'];
                if ($events['status'] == null) {
                    $status = 1;
                } else {
                    $status = $events['status'];
                }
                $priority = (int) $events['Priority'];
                $attr = [
                    'external_id' => 'ticket1',
                    'fields'      => [
                        'ticket-number' => $ticket_id,
                        'subjetc'       => $subjetc,
                        'description'   => $body,
                        'from'          => $client_reference_id,
                        'created-at'    => [
                            'start' => '2011-12-31 11:27:10',
                            'end'   => '2012-01-31 11:28:20',
                        ],
                        'priority' => $priority,
                        'status'   => $status,
                    ],
                ];
                $opt = [];
                $result = \PodioItem::create($app_id, $attr, $opt);
                $result = $result->item_id;
                \DB::table('podio_ticket_item')->insert(
                    ['ticket_id' => $ticket_id, 'podio_item_id' => $result]
                );
            }
        } else {
            $user_data = \DB::table('users')
                            ->select('user_name', 'first_name', 'last_name')
                            ->where('id', '=', $u_id)->first();
            if ($user_data->first_name == '') {
                $name = $user_data->user_name;
            } else {
                $name = $user_data->first_name.' '.$user_data->last_name;
            }
            $auth = $this->authenticate();
            if ($auth == true) {
                $comment = $events['body'];
                // $comment = preg_replace("/<br\W*?\/>/", "\r\n", $comment);
                // $comment = preg_replace('/<[^>]*>/', '', $comment);
                $attr = [
                    'value' => $comment."\r\nby ".$name,
                ];
                \PodioComment::create('item', $is_exist, $attr);
            }
        }
    }

    public function replyTicket($data)
    {
        // dd($data);
        $id = $data['ticket_id'];
        $ticket_number = \DB::table('tickets')
                            ->select('ticket_number')
                            ->where('id', '=', $id)
                            ->first();
        $ticket_number = $ticket_number->ticket_number;
        $item_id = \DB::table('podio_ticket_item')
                        ->select('podio_item_id')
                        ->where('ticket_id', '=', $ticket_number)
                        ->first();
        $item_id = $item_id->podio_item_id;
        $comment = $data['body'];
        $name = $data['u_id'];
        $attr = [
            'value' => $comment."\r\nby ".$name,
        ];
        $auth = $this->authenticate();
        if ($auth == true) {
            \PodioComment::create('item', $item_id, $attr);
        }
    }
}
