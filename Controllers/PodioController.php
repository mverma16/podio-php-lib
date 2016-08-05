<?php

namespace App\Plugins\Podio\Controllers;

//controllers
use App\Http\Controllers\Common\PhpMailController;
//models
use App\Http\Controllers\Controller;
use App\Model\helpdesk\Ticket\Ticket_Thread;
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
        $data = \DB::table('podio')
            ->select('client_app_id', 'faveo_app_id', 'faveo_app_token')
            ->where('id', '=', 1)
            ->first();
        if ($data->client_app_id == '' || $data->faveo_app_id || $data->faveo_app_token) {
            return false;
        }
    }

    /**
     *@category function to setup client_id and client secret for podio api
     *
     *@param null
     *
     *@return array
     */
    public function setup()
    {
        $podio_data = \DB::table('podio')
                      ->where('id', '=', 1)
                      ->first();
        $values = [];
        array_push($values, $podio_data->client_id);
        array_push($values, $podio_data->client_secret);
        array_push($values, $podio_data->username);
        array_push($values, $podio_data->password);
        \Podio::setup($values[0], $values[1]);
        $result = \Podio::authenticate_with_password($values[2], $values[3]);

        return $result;
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
                    'external_id' => 'clients',
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
     *@param array $events
     *
     *@return
     */
    public function createPodioTicket($events)
    {
        if ($this->checkPluginSetup()) {
            $ticket_id = $events['ticket_number'];
            $ticket_number = \DB::table('tickets')
                            ->select('id')
                            ->where('ticket_number', '=', $ticket_id)
                            ->first();
            $u_id = $events['user_id'];
            $is_exist = $this->checkTicketExists($ticket_id);
            if ($is_exist == 0) {
                $auth = $this->authenticate();
                if ($auth == true) {
                    $client_reference_id = (int) $this->createNewClient($u_id);
                    $data = \DB::table('podio')
                        ->select('faveo_app_id')
                        ->where('id', '=', 1)->first();
                    $app_id = $data->faveo_app_id;
                    $subjetc = $events['subject'];
                    $body = $events['body'];
                    $created_at = date('Y-m-d H:i:s');
                    if ($events['status'] == null) {
                        $status = 1;
                    } else {
                        $status = (int) $events['status'];
                    }
                    $priority = (int) $events['Priority'];
                    $attr = [
                        'external_id' => 'tickets',
                        'fields'      => [
                            'ticket-number' => $ticket_id,
                            'subject'       => $subjetc,
                            'description'   => $body,
                            'from'          => $client_reference_id,
                            'created-at'    => [
                                'start' => $created_at,
                                //'end'   => '2012-01-31 11:28:20',
                            ],
                            'priority' => (int) $priority,
                            'status'   => (int) $status,
                        ],
                    ];
                    $opt = [];
                    $result = \PodioItem::create($app_id, $attr, $opt);
                    $result = $result->item_id;
                    \DB::table('podio_ticket_item')->insert(
                        ['ticket_id' => $ticket_id, 'podio_item_id' => $result]
                    );
                    $url = route('ticket.thread', $ticket_number->id);
                    $attr = [
                        'value' => "Click on the following link to view this ticket in Faveo\r\n ".$url."\r\n*****************************",
                    ];
                    \PodioComment::create('item', $result, $attr);
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
                    // dd($comment);
                    $comment = str_replace('<br>', "\r\n", $comment);
                    $comment = str_replace('<br/>', "\r\n", $comment);
                    $comment = str_replace('</div>', "\r\n", $comment);
                    $comment = str_replace('</p>', "\r\n", $comment);
                    $comment = str_replace('".."', '', $comment);
                    $comment = preg_replace('/<[^>]*>/', '', $comment);
                    $attr = [
                        'value' => "\r\n".$comment."\r\nby ".$name."\r\n*****************************",
                    ];
                    \PodioComment::create('item', $is_exist, $attr);
                }
            }
        }
    }

    /**
     *@category function to post comment in podio on agents reply
     *
     *@param array $data
     *
     *@return null
     */
    public function replyTicket($data)
    {
        if ($this->checkPluginSetup()) {
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
            $comment = str_replace('<br>', "\r\n", $comment);
            $comment = str_replace('<br/>', "\r\n", $comment);
            $comment = str_replace('</div>', "\r\n", $comment);
            $comment = str_replace('</p>', "\r\n", $comment);
            $comment = str_replace('".."', '', $comment);
            $comment = preg_replace('/<[^>]*>/', '', $comment);
            $name = $data['u_id'];
            $attr = [
                'value' => "\r\n".$comment."\r\nby ".$name."\r\n*****************************",
            ];
            $auth = $this->authenticate();
            if ($auth == true) {
                \PodioComment::create('item', $item_id, $attr);
            }
        }
    }

    /**
     *@category function to handle webhook events from podio
     *
     *@param null
     *
     *@return null
     */
    public function handleHook()
    {
        switch ($_POST['type']) {
        case 'hook.verify':
              $auth = $this->authenticate();
            if ($auth == true) {
                // Validate the webhook
               \PodioHook::validate($_POST['hook_id'], ['code' => $_POST['code']]);
            }
            break;
        case 'comment.create':
            $item = $_POST['item_id'];
            $comment = $_POST['comment_id'];
            $auth = $this->authenticate();
            if ($auth == true) {
                $comment = \PodioComment::get($comment);
                $body = $comment->value;
                $comment = $comment->created_by;
                $created_by = $comment->id;
                $user = $this->postCommentInFaveo($created_by, $body, $item); //call to the function to create cinternal thread in FAVEO
            }
            break;
        case 'item.update':
                $item_id = $_POST['item_id'];
                $auth = $this->authenticate();
                if ($auth == true) {
                    $item = \PodioItem::get_basic($item_id); // Get item with item_id
                    // Get the field with the external_id=sample-external-id
                    $field = $item->fields['priority'];
                    $field2 = $item->fields['status'];
                    //get the value of field "Priority"
                    $value = \PodioItem::get_field_value($item_id, $field->field_id);
                    $value = $value[0]['value']['id'];
                    //get the value of field "Status"
                    $value2 = \PodioItem::get_field_value($item_id, $field2->field_id);
                    $value2 = $value2[0]['value']['id'];
                    //get the ticket number associated with the updated item
                    $item_data = \DB::table('podio_ticket_item')
                                ->select('ticket_id')
                                ->where('podio_item_id', '=', $item_id)
                                ->first();
                    //
                    \DB::table('tickets')
                        ->where('ticket_number', '=', $item_data->ticket_id)
                        ->update([
                            'priority_id' => $value,
                            'status'      => $value2,
                        ]);
                    $status_id = \DB::table('ticket_status')
                    ->select('name')
                    ->where('id', '=', $value2)
                    ->first();
                    $ticket = \DB::table('tickets')
                        ->select('id', 'dept_id', 'user_id', 'ticket_number')
                        ->where('ticket_number', '=', $item_data->ticket_id)
                        ->first();
                    $reply = "Stauts of this ticket has been changed to <b>'".$status_id->name."'</b> by a Podio user.";
                   //saving internal thread in ticket_thread table
               $thread = new Ticket_Thread();
                    $thread->ticket_id = $ticket->id;
                    $thread->user_id = null;
                    $thread->is_internal = 1;
                    $thread->body = $reply;
                    $thread->save();
                    if ($status_id->name == 'Closed') {
                        $PhpMailController = new PhpMailController();

                        $ticket = \DB::table('tickets')->where('ticket_number', '=', 'AAAC-0002-0000002')->first();
                        $user = \DB::table('users')->select('user_name', 'email')->where('id', '=', $ticket->user_id)->first();
                        $title = \DB::table('ticket_thread')->select('title')->where('ticket_id', '=', $ticket->id)->first();
        //dd($title);
    $PhpMailController->sendmail($from = $PhpMailController->mailfrom('0', $ticket->dept_id), $to = ['name' => $user->user_name, 'email' => $user->email], $message = ['subject' => $title->title.'[#'.$ticket->ticket_number.']', 'scenario' => 'close-ticket'], $template_variables = ['ticket_number' => $ticket->ticket_number]);
                    }
                }
                break;
        case 'item.delete':
             // Do something. item_id is available in $_POST['item_id']
        }
    }

    public function createHook()
    {
        $PhpMailController = new PhpMailController();

        $ticket = \DB::table('tickets')->where('ticket_number', '=', 'AAAC-0002-0000002')->first();
        $user = \DB::table('users')->select('user_name', 'email')->where('id', '=', $ticket->user_id)->first();
        $title = \DB::table('ticket_thread')->select('title')->where('ticket_id', '=', $ticket->id)->first();
        //dd($title);
    $PhpMailController->sendmail($from = $PhpMailController->mailfrom('0', $ticket->dept_id), $to = ['name' => $user->user_name, 'email' => $user->email], $message = ['subject' => $title->title.'[#'.$ticket->ticket_number.']', 'scenario' => 'close-ticket'], $template_variables = ['ticket_number' => $ticket->ticket_number]);
    }

    /**
     *@category function to create inetrnal thread in Faveo when a comment is done in Podio
     *
     *@param int $created_at(user profile id), string $body(comment body), int $item(id of the commented item)
     */
    public function postCommentInFaveo($created_by, $body, $item)
    {
        $auth = $this->setup();
        if ($auth == true) {
            $user = \PodioContact::get_for_user($created_by); //Get contact details by profile id
           $user = [$user]; //convert into array
           foreach ($user as  $value) {
               $data2 = (array) $value;
               foreach ($data2 as $key => $value) {
                   $phone = $value['phone'];
                   $name = $value['name'];
                   $mail = $value['mail'];
                   break;
               }
           }
            if (count($mail) && count($phone)) {//if mail and phone have values
                   $phone = $phone[0];
                $email = $mail[0];
            } elseif (count($mail) || count($phone)) {// if either has value
                if (count($mail)) {
                    $email = $mail[0];
                    $phone = 'Not available';
                } else {
                    $phone = $phone[0];
                    $email = 'Not available';
                }
            } else { // if both are not available
                $phone = 'Not available';
                $email = 'Not available';
            }

            $reply = $body.'<br/><br/><br/>Comment in Podio by:<br/><b>'.$name.'</b><br/>email: '.$email.'<br/>phone: '.$phone; //formating internal thread message
            //get ticket number using $item_id
            $item = \DB::table('podio_ticket_item')
                ->select('ticket_id')
                ->where('podio_item_id', '=', $item)
                ->first();
            $item = $item->ticket_id;
            //Get ticket id using the ticket number
            $ticket = \DB::table('tickets')
                ->select('id')
                ->where('ticket_number', '=', $item)
                ->first();
            //saving internal thread in ticket_thread table
            $thread = new Ticket_Thread();
            $thread->ticket_id = $ticket->id;
            $thread->user_id = null;
            $thread->is_internal = 1;
            $thread->body = $reply;
            $thread->save();
        }
    }

    /**
     *@category function to change the status of item in Podio when status of ticket get change in Faveo
     *
     *@param array $events (information about ticket, user and the status)
     *
     *@return null
     */
    public function changeStatus($events)
    {
        if ($this->checkPluginSetup()) {
            $item = \DB::table('podio_ticket_item')
                ->select('podio_item_id')
                ->where('ticket_id', '=', $events['id'])
                ->first();
            $status_id = \DB::table('ticket_status')
                ->select('id')
                ->where('name', '=', $events['status'])
                ->first();
            if (count($item)) {
                $auth = $this->authenticate();
                if ($auth == true) {
                    $item2 = \PodioItem::get_basic($item->podio_item_id); // Get item with item_id
                    // Get the field with the external_id=sample-external-id
                    $field = $item2->fields['status'];
                    $field_id = $field->field_id;
                    $attr = [
                        'value' => (int) $status_id->id,
                    ];
                    $opt = [
                        'hook' => false,
                    ];
                    \PodioItemField::update($item->podio_item_id, $field_id, $attr, $opt);
                    $attr2 = [
                        'value' => "\r\n".$events['first_name'].'  '.$events['last_name']." has changed the status.\r\n*****************************",
                    ];
                    \PodioComment::create('item', $item->podio_item_id, $attr2);
                }
            }
        }
    }

    /**
     *@category function to check that plugin has set up or not
     *
     *@param null
     *
     *@return bool
     */
    public function checkPluginSetup()
    {
        $data = \DB::table('podio')
            ->select('client_app_id', 'faveo_app_id', 'faveo_app_token')
            ->where('id', '=', 1)
            ->first();
        if ($data->client_app_id == '' || $data->faveo_app_id == '' || $data->faveo_app_token == '') {
            return false;
        }

        return true;
    }
}
