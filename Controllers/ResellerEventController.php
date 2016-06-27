<?php

namespace App\Plugins\Reseller\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Form;
use App\Model\helpdesk\Ticket\Ticket_Thread;
//use App\Model\plugin\reseller\Reseller;
use App\User;
use App\Model\helpdesk\Ticket\Tickets;
use App\Model\helpdesk\Ticket\Ticket_attachments;
use App\Http\Controllers\Agent\helpdesk\TicketController;
use Auth;
use Mail;
use App\Model\helpdesk\Ticket\Ticket_Collaborator;
use App\Plugins\Reseller\Model\ResellerDepartment;
use App\Plugins\Reseller\Model\ResellerCustomField;
use App\Plugins\Reseller\Model\Reseller;
use App\Plugins\Reseller\Controllers\ResellerController;
use Illuminate\Database\Schema\Blueprint;

use Schema;

class ResellerEventController extends Controller {

    /**
     * Create Button
     * @param type $conversation
     * @param type $role
     * @param type $user
     * @return type
     */
    public function SendButton($conversation, $role, $user) {

        return "<button class='pull-right btn btn-primary' id=rc" . $conversation->id . " data-toggle='modal' data-target=#" . $conversation->id . ">" . $this->user($role) . "</button>
            <div class='modal fade' id=" . $conversation->id . ">
    <div class='modal-dialog'>
        <div class='modal-content'>
            <div class='modal-header'>
                <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
                <h4 class='modal-title'>Edit For Reseller Club</h4>
            </div>
            <div class='modal-body'>" .
                Form::open(['url' => 'resellers-reply/' . $conversation->id, 'files' => true])
                . "<div class='col-md-2'>" .
                Form::label('To', 'To:')
                . "</div>
                <div class='col-md-10'>
                   " . $this->rolecheck($role, $user) . "  
                </div>
               
                   " . $this->department($conversation) . "  
                
                <div id=new_select>
                </div>
                <div id='newtextarea'>
                    <textarea style='width:98%;height:20%;' name='ReplyContent' class='form-control' id='ReplyContent'>" . $this->changeUrl($conversation->body) . "</textarea>
                </div> 
                <div>
                    <label for=attachments>Attachments</label><input type='file' name='attachment[]' multiple>
                </div> 
            </div>

            <div class='modal-footer'>
                <button type='button' class='btn btn-default pull-left' data-dismiss='modal' id='dismis2'>Close</button>
                <button type='submit' class='btn btn-warning pull-right'>Send</button>
            </div>
            " . Form::close() . "
        </div>
    </div>
</div>
<script type='text/javascript'>

function fetch_select(val)
{
   $.ajax({
     type: 'post',
     url: '../reseller/fetch-fields',
     data: {
       get_option:val
     },
     success: function (response) {
       document.getElementById('new_select').innerHTML=response; 
     }
   });
}

</script>";
    }

    public function changeUrl($body) {
        $reseller = new Reseller;
        $reseller = $reseller->where('id', '1')->first();
        if($reseller)
        {
            if ($reseller->find_url && $reseller->replace_url) {

                $find = $reseller->find_url;
                $replace = $reseller->replace_url;
                //dd($replace);
                if (strpos($body, $find) !== false) {
                    //$body = strpos($body,$find);
                    $body = str_replace($find, $replace, $body);
                }
            }
            //dd($body);
            return $body;
        }
    }

    /**
     * Check the role of the reply
     * @param type $role
     * @param type $user
     * @return type
     */
    public function rolecheck($role, $user) {
        if ($role->email == 'support@flyhi.kayako.com') {
            return Form::text('To', $user->email, ['id' => 'email', 'class' => 'form-control', 'style' => 'width:55%', 'dissabled' => true]);
        } else {
            return Form::text('To', 'Reseller Club Support', ['id' => 'email', 'class' => 'form-control', 'style' => 'width:55%', 'dissabled' => true]);
        }
    }

    /**
     * Check the role of the reply
     * @param type $role
     * @param type $user
     * @return type
     */
    public function department($conversation) {
        //dd($conversation)
        $ticket = Tickets::where('id', $conversation->ticket_id)->first();
        if ($ticket->rc_ticketid == '0') {
            $departments = new ResellerDepartment;
            $departments = $departments->get();
            return "<div class='col-md-2'>" .
                    Form::label('department', 'Department:')
                    . "</div>
                    <div class='col-md-10'>"
                    . Form::select('department', ['' => 'Choose one Department', 'Departments' => $departments->lists('rcdpt_name', 'middledpt_id')], null, ['id' => 'email', 'class' => 'form-control', 'style' => 'width:55%', 'onchange' => 'fetch_select(this.value)']) . "
                   </div>";
        }
    }

    /**
     * Checking the user
     * @param type $user
     * @return string
     */
    public function user($user) {
        if ($user->email == 'support@flyhi.kayako.com') {
            return "Send to Client!";
        } else {
            return "Send to Reseller Club!";
        }
    }

    /**
     * Send Reply
     * @param type $id
     * @param Request $request
     * @param Ticket_Thread $thread
     * @param User $user
     * @param Tickets $ticket
     */
    public function Reply($id, Request $request, Ticket_Thread $thread, User $user, Tickets $ticket) {
        //dd($request);
        $threads = $thread->where('id', $id)->first();
        $subject = $threads->title;
        $userid = $threads->user_id;
        $ticket = $ticket->where('id', $threads->ticket_id)->first();
        $departmentid = $request->input('department');

        $reques = $request->except('department', 'ReplyContent', 'To', '_token', '_wysihtml5_mode');
        $requests=$request->except('department', 'ReplyContent', 'To', '_token', '_wysihtml5_mode');
        //dd($reques['attachment'][0]);
        if ($reques['attachment'][0] != null) {
            //$requests = array();
            for ($i = 0; $i < count($reques['attachment']); $i++) {
                $requests[$i]['originalName'] = $reques['attachment'][$i]->getClientOriginalName();
                $requests[$i]['originalPath'] = base64_encode(file_get_contents($reques['attachment'][$i]->getRealPath()));
            }
        } 
//        else {
//            $requests = array();
//        }
        //$requests = 
        $user = $user->where('id', $userid)->first();
        if ($user->username) {
            $fullname = $user->username;
        } else {
            $fullname = "From Faveo";
        }
        $to = 'faveo.reseller@gmail.com';
        $content = $request->input('ReplyContent');
        if ($request->input('To') == 'Reseller Club Support') {

            if ($ticket->rc_ticketid==0) {
                //dd('cre');
                $rcticket = $this->CallCreateBridge($subject, $departmentid, $fullname, $to, $content, $ticket->ticket_number, $requests);
                //dd('<pre>'.$rcticket.'<pre>');
                //dd($rcticket);
            } else {
                //dd('rep');
                $rc_ticketid = $ticket->rc_ticketid;
                $rcticket = $this->CallReplyBridge($rc_ticketid, $content, $requests);
            }
        } else {
            $rcticket = '';
        }
        $result = $this->attachToFaveoTicket($rcticket, $thread, $threads, $ticket, $content, $request);


        //$result = $this->attachToFaveoTicket($rcticket='', $thread, $ticket,$content);

        if ($result == 1) {
            return redirect()->back()->with('success', 'Replied Successfully');
        } else {
            return redirect()->back()->with('fails', $result);
        }
    }

    /**
     * Call the create function in Faveo Bridge
     * @param type $subject
     * @param type $departmentid
     * @param type $fullname
     * @param type $to
     * @param type $content
     * @return type
     */
    public function CallCreateBridge($subject, $departmentid, $fullname, $to, $content, $number, $requests) {
        //dd($requests);
        $reseller = new Reseller;
        $reseller = $reseller->where('id', '1')->first();
        if ($reseller) {
            $userid = $reseller->userid;
            $apikey = $reseller->apikey;
            $data = [
                "rcapi" => $apikey,
                "rcauth_userid" => $userid,
                "subject" => '[#' . $number . '] ' . $subject,
                "fullname" => $fullname,
                "email" => $to,
                "contents" => $content,
                "departmentid" => $departmentid, // Faveo Department Id
            ];
            $data = array_merge($data, $requests);
            //dd($data);
            $url = 'http://faveo.support-tools.com/create_ticket';
            return $this->PostApi($data, $url);
        } else {
            return redirect()->back()->with('fails', 'Not a reseller Club System');
        }
    }

    public function CallReplyBridge($rc_ticketid, $content, $requests) {
        $reseller = new Reseller;
        $reseller = $reseller->where('id', '1')->first();
        if ($reseller) {
            $userid = $reseller->userid;
            $apikey = $reseller->apikey;
            $data = [
                "rcapi" => $apikey,
                "rcauth_userid" => $userid,
                "contents" => $content,
                "ticketid" => $rc_ticketid,
            ];
            $data = array_merge($data, $requests);
            //dd($data);
            $url = 'http://faveo.support-tools.com/reply-ticket';
            return $this->PostApi($data, $url);
        } else {
            return redirect()->back()->with('fails', 'Not a reseller Club System');
        }
    }

    /**
     * Call Post Curl function
     * @param type $data
     * @param type $url
     */
    public function PostApi($data, $url) {

        $post_data = http_build_query($data, '', '&');

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            echo 'error:' . curl_error($curl);
        }

        return $response;
        curl_close($curl);
    }

    /**
     * Call Post Curl function
     * @param type $data
     * @param type $url
     */
    public function GetApi($data, $url) {
        if ($data != '') {
            $post_data = http_build_query($data, '', '&');
            $url = $url . $post_data;
        }
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            echo 'error:' . curl_error($curl);
        }

        return $response;
        curl_close($curl);
    }

    public function attachToFaveoTicket($rcticket, $thread, $threads, $ticket, $content, $request) {
        //dd($request);
        if ($request->hasFile('attachment')) {
            $attachments = $request->file('attachment');
            //dd($attachments);
        } else {
            $attachments = '';
        }

        if (is_numeric($rcticket)) {

            if ($ticket->rc_ticketid == 0) {
                $ticket->rc_ticketid = $rcticket;
                $ticket->save();
                //$thread = new App\Model\helpdesk\Ticket\Ticket_Thread;
                $thread->ticket_id = $ticket->id;
                $thread->poster = 'support';
                $thread->body = $content;
                //dd(Auth::user());
                $thread->user_id = Auth::user()->id;
                $thread->is_internal = 1;
                $thread->body = "This Ticket have been send to reseller club by " . ucfirst(Auth::user()->first_name) . " " . ucfirst(Auth::user()->last_name);
                $thread->save();
                $ta = new Ticket_attachments;
                if ($attachments != '') {
                    foreach ($attachments as $attachment) {
                        if ($attachment != null) {
                            $name = $attachment->getClientOriginalName();
                            $type = $attachment->getClientOriginalExtension();
                            $size = $attachment->getSize();
                            $data = file_get_contents($attachment->getRealPath());
                            $attachPath = $attachment->getRealPath();
                            $ta->create(['thread_id' => $thread->id, 'name' => $name, 'size' => $size, 'type' => $type, 'file' => $data, 'poster' => 'ATTACHMENT']);
                            $check_attachment = 1;
                        } else {
                            $check_attachment = null;
                        }
                    }
                }
                return 1;
            } else {
                $result = $this->faveoReply($attachments, $content, $ticket->id, $rcticket);
                return $result;
            }
        } elseif ($rcticket == '') {
            $result = $this->faveoReply($attachments, $content, $ticket->id, $rcticket);
            //dd($result);
            return $result;
        } else {
            return $rcticket;
        }
    }

    public function faveoReply($attachments, $reply_content, $ticketid, $rc_postid) {
        $thread = new Ticket_Thread;
        $ta = new Ticket_attachments;
        $check_attachment = null;
        $thread->ticket_id = $ticketid;
        $thread->poster = 'support';
        $thread->body = $reply_content;
        //dd(Auth::user());
        $thread->user_id = Auth::user()->id;
        $ticket_id = $ticketid;
        $tickets = Tickets::where('id', '=', $ticket_id)->first();
        $tickets->isanswered = '1';
        $tickets->save();
        $ticket_user = User::where('id', '=', $tickets->user_id)->first();
        if ($tickets->assigned_to == 0) {
            $tickets->assigned_to = Auth::user()->id;
            $tickets->save();
            $thread2 = New Ticket_Thread;
            $thread2->ticket_id = $thread->ticket_id;
            $thread2->user_id = Auth::user()->id;
            $thread2->is_internal = 1;
            $thread2->body = "This Ticket have been assigned to " . Auth::user()->first_name . " " . Auth::user()->last_name;
            $thread2->save();
        }
        if ($rc_postid != '') {
            $thread2 = New Ticket_Thread;
            $thread2->ticket_id = $thread->ticket_id;
            $thread2->user_id = Auth::user()->id;
            $thread2->is_internal = 1;
            $thread2->body = "This Ticket have been send to reseller club by " . ucfirst(Auth::user()->first_name) . " " . ucfirst(Auth::user()->last_name);
            $thread2->rc_postid = $rc_postid;
            $thread2->save();
        }
        if ($tickets->status > 1) {
            $tickets->status = '1';
            $tickets->isanswered = '1';
            $tickets->save();
        }
        $thread->save();
        if ($attachments != '') {
            foreach ($attachments as $attachment) {
                if ($attachment != null) {
                    $name = $attachment->getClientOriginalName();
                    $type = $attachment->getClientOriginalExtension();
                    $size = $attachment->getSize();
                    $data = file_get_contents($attachment->getRealPath());
                    $attachPath = $attachment->getRealPath();
                    $ta->create(['thread_id' => $thread->id, 'name' => $name, 'size' => $size, 'type' => $type, 'file' => $data, 'poster' => 'ATTACHMENT']);
                    $check_attachment = 1;
                } else {
                    $check_attachment = null;
                }
            }
        }
        $sub = $thread->ticket_id;
        //dd($sub);
        $ticket_subject = $thread->where('ticket_id', $sub)->first();
        ;
        $ticket_subject = $ticket_subject->title;
        $user_id = $tickets->user_id;
        $user = User::where('id', '=', $user_id)->first();
        $email = $user->email;
        $user_name = $user->user_name;
        $ticket_number = $tickets->ticket_number;
        $ticketController = new TicketController;
        $company = $ticketController->company();
        $username = $ticket_user->user_name;
        if (!empty(Auth::user()->agent_sign)) {
            $agentsign = Auth::user()->agent_sign;
        } else {
            $agentsign = null;
        }
        Mail::send(array('html' => 'emails.ticket_re-reply'), ['content' => $reply_content, 'ticket_number' => $ticket_number, 'From' => $company, 'name' => $username, 'Agent_Signature' => $agentsign], function ($message) use ($email, $user_name, $ticket_number, $ticket_subject, $attachments, $check_attachment) {
            $message->to($email, $user_name)->subject($ticket_subject . '[#' . $ticket_number . ']');
            if ($check_attachment == 1) {
                $size = sizeOf($attachments);
                for ($i = 0; $i < $size; $i++) {
                    $message->attach($attachments[$i]->getRealPath(), ['as' => $attachments[$i]->getClientOriginalName(), 'mime' => $attachments[$i]->getClientOriginalExtension()]);
                }
            }
        }, true);
        $collaborators = Ticket_Collaborator::where('ticket_id', '=', $ticket_id)->get();
        foreach ($collaborators as $collaborator) {
            if ($collaborator) {
                $collab_user_id = $collaborator->user_id;
                $user_id_collab = User::where('id', '=', $collab_user_id)->first();
                $collab_email = $user_id_collab->email;
                if ($user_id_collab->role == "user") {
                    $collab_user_name = $user_id_collab->user_name;
                } else {
                    $collab_user_name = $user_id_collab->first_name . " " . $user_id_collab->last_name;
                }
                Mail::send('emails.ticket_re-reply', ['content' => $reply_content, 'ticket_number' => $ticket_number, 'From' => $company, 'name' => $collab_user_name, 'Agent_Signature' => $agentsign], function ($message) use ($collab_email, $collab_user_name, $ticket_number, $ticket_subject) {
                    $message->to($collab_email, $collab_user_name)->subject($ticket_subject . '[#' . $ticket_number . ']');
                });
            }
        }
        return 1;
    }

    public function TestView() {
        $path = app_path() . '/Plugins/Reseller/views';
        \View::addNamespace('plugins', $path);
        return view('plugins::department');
    }

    public function FetchCustomFieldsOnForm(Request $request, ResellerCustomField $field) {
        //dd($request->input('get_option'));
        $option = $request->input('get_option');
        //dd($option);
        $fields = $field->where('department_id', $option)->get();
        foreach ($fields as $field) {
            $isrequired = $field->isrequired;
            if ($isrequired == 1) {
                $fieldtype = $field->fieldtype;
                $fieldname = $field->field_name;
                $fieldtitle = $field->title;
                $fieldid  = $field->custom_id;

                switch ($fieldtype) {
                    case 1:
                        echo "<label for=" . $fieldname . ">" . $fieldtitle . "</label><input type='text' name=" . $fieldname . " class='form-control'>";
                        break;
                    case 2:
                        echo "<label for=" . $fieldname . ">" . $fieldtitle . "</label><textarea name=" . $fieldname . " class='form-control'></textarea>";

                        break;
                    case 3:
                        echo "<label for=" . $fieldname . ">" . $fieldtitle . "</label><input type='password' name=" . $fieldname . " class='form-control'>";

                        break;
                    case 4:
                        echo "Checkbox";
                        break;
                    case 5:
                        echo "Radio";
                        break;
                    case 6:
                        echo "<label for=" . $fieldname . ">" . $fieldtitle . "</label><select name=" . $fieldname . " class='form-control'>".$this->GetDropDown($fieldid)."</select>";
                        break;
                    case 7:
                        echo "Multi select";
                        break;
                    case 8:
                        echo "Custom";
                        break;
                    case 9:
                        echo "Linked select fields";
                        break;
                    case 10:
                        echo "Date";
                        break;
                    case 11:
                        echo "File";
                        break;
                }
            }
        }
    }
    
    public function GetDropDown($fieldid)
    {
        $values = \DB::table('reseller_custom_values')->where('customfieldid','=',$fieldid)->get();
        $result='';
        foreach($values as $value)
        {
            $result.= "<option value=".$value->optionvalue.">".ucfirst($value->optionvalue)."</option>";
        }
        return $result;
    }

    public function ResellerAttach($rcticket, $rcpost, $filename, $attach) {
        $reseller = new Reseller;
        $reseller = $reseller->where('id', '1')->first();
        if ($reseller) {
            $userid = $reseller->userid;
            $apikey = $reseller->apikey;
            $data = [
                "rcapi" => $apikey,
                "rcauth_userid" => $userid,
                "ticketid" => (int) $rcticket,
                "ticketpostid" => (int) $rcpost,
                "filename" => $filename,
                "contents" => $attach
            ];
            $data = array_merge($data, $requests);
            //dd($data);
            $url = 'http://faveo.support-tools.com/create_attachment';
            return $this->PostApi($data, $url);
        } else {
            return redirect()->back()->with('fails', 'Not a reseller Club System');
        }
    }

    public function TicketDetails($threadid) {
        //dd($threadid);
        $ticket = new Tickets();
        $thread = new Ticket_Thread;
        $thread = $thread->where('id', $threadid)->first();
        //dd($thread);
        $ticketid = $thread->ticket_id;
        $ticket = $ticket->where('id', $ticketid)->first();
        //dd($ticket);
        if ($ticket->rc_ticketid != 0) {
            return "<tr><td><b>Reseller Ticket Id:</b></td>   <td>" . $ticket->rc_ticketid . "</td></tr>";
        }
    }

    public function ReadMail($userid, $password) {

        $this->createTicketIdThreadId();

        $user = new User;
        $reseller = new ReseLler;
        $reseller = $reseller->where('id', '1')->first();
        if ($reseller) {
            $user = $user->where('id', $userid)->first();
            //dd($user);
            $userid = $reseller->userid;
            $apikey = $reseller->apikey;
            $name = $user->user_name;
            $email = $user->email;
            $reseller = new ResellerController;
            $signup = $reseller->Signup($name, $email, $company = 'N/A', $password, $address = 'N/A', $city = 'N/A', $state = 'N/A', $country = 'IN', $zip = 'N/A', $phonecc = '91', $phone = '9999999999', $lang = 'en', $userid, $apikey);
            //dd($signup);
            if ($signup != false) {
                $customer = $reseller->GetCustomerbyUserName($email, $userid, $apikey);
                if ($customer != false) {
                    $user->creationdt = $customer['creationdt'];
                    //$user->email = $customer['username'];
                    $user->langpref = $customer['langpref'];
                    $user->pin = $customer['pin'];
                    $user->company = $customer['company'];
                    $user->stateid = $customer['stateid'];
                    $user->state = $customer['state'];
                    $user->city = $customer['city'];
                    $user->resellerid = $customer['resellerid'];
                    $user->customerid = $customer['customerid'];
                    if (array_key_exists('mobilenocc', $customer)) {
                        $user->mobilenocc = $customer['mobilenocc'];
                    }
                    $user->salescontactid = $customer['salescontactid'];
                    $user->telnocc = $customer['telnocc'];
                    $user->country = $customer['country'];
                    $user->totalreceipts = $customer['totalreceipts'];
                    $user->zip = $customer['zip'];
                    $user->address1 = $customer['address1'];
                    $user->first_name = $customer['name'];
                    //$user->role = 'user';
                    $user->save();
                }
            } else {
                return FALSE;
            }
        }
    }
    
    public function createTicketIdThreadId()
    {
        if(Schema::hasColumn('tickets', 'rc_ticketid')!=true)  
        {
          Schema::table('tickets', function(Blueprint $table)
           {
                        //$table->string('company')->after('vocation_mode');
			$table->integer('rc_ticketid')->after('ticket_number');
			
           });
          
        }
        if(Schema::hasColumn('ticket_thread', 'rc_postid')!=true)  
        {
          Schema::table('ticket_thread', function(Blueprint $table)
           {
                        //$table->string('company')->after('vocation_mode');
			$table->integer('rc_postid')->after('pid');
			
           });
          
        }
        if (!Schema::hasTable('reseller')) {
            Schema::create('reseller', function($table) {
                $table->increments('id');
                $table->string('userid');
                $table->string('apikey');
                $table->string('find_url');
                $table->string('replace_url');
                $table->timestamps();
            });
        }
    }

}