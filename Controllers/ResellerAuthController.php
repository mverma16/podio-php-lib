<?php

namespace App\Plugins\Reseller\Controllers;

use App\Http\Controllers\Controller;
use App\Model\plugin\reseller\Country;
use App\Plugins\Reseller\Model\Reseller;
use App\User;
use Auth;
//use Form;
use Exception;
use Hash;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Schema;

class ResellerAuthController extends Controller
{
    public function PostLogin()
    {
        try {
            $this->CreateUserTable();
            $email = \Input::get('email');
            $password = \Input::get('password');
            $reseller = new ResellerController();
            $club = new Reseller();
            $club = $club->where('id', '1')->first();
            $userid = $club->userid; //'619613';
            $apikey = $club->apikey; //'xr05pRZKUZJAiJUvdtrZgRr2TiXkDKFi';
            $ip = '122.171.164.172'; //$request->getClientIp();
            $user = new User();
            //check user in faveo
            $user = $user->where('email', $email)->first();
            //dd($user);
            //no user in faveo
            if (!$user) {
                //                dd('not a user');
                //check user in resller club
                $check_in_reseller = $reseller->GenerateToken($ip, $userid, $apikey, $email, $password);
                //dd($check_in_reseller);
                //yes user in reseller
                if ($check_in_reseller['status'] != 'ERROR') {
                    //create the user in faveo
                    $this->createUserInFaveo($email, $password);
                }
                //no user in reseller and faveo
                else {
                    //Throw an error
                    throw new Exception("I can't find you, please register");

//                    $password = self::getToken(8);
//                    $signup = $reseller->Signup($email, $userid, $apikey, $password);
//                    if($signup['status']=='ERROR'){
//                        throw new Exception($signup['message']." in reseller, check your password or fields");
//                    }
//                    $customer = $reseller->GetCustomerbyUserName($email, $userid, $apikey);
//                    dd($customer);
//
//                    $user = new User();
//                    $this->updateUser($customer, $user, $password);
                }
            }
            //yes such user in faveo
            else {
                if ($user->role == 'user') {

                    //check in reseller
                    $token = $reseller->GenerateToken($ip, $userid, $apikey, $email, $password);

                    // yes user in reseller

                    if ($token['status'] != 'ERROR') {
                        //get the fields
                        $customer = $reseller->GetCustomerbyUserName($email, $userid, $apikey);
                        //update in user
                        $this->updateUser($customer, $user, $password);
                    }
                    //no user in reseller club
                    else {
                        if ((preg_match('/\d/', $password) != true) || (strlen($password) < 8)) {
                            $password = self::getToken(8);
                        }
                        $email = $user->email;
                        $name = $user->first_name;
                        //$company = $user->company;
                        //dd($password);
                        $signup = $reseller->Signup($email, $userid, $apikey, $password, $name);
                        //$signup = $reseller->Signup($email, $userid, $apikey, $password1);
                        $customer = $reseller->GetCustomerbyUserName($email, $userid, $apikey);
                        if (is_array($customer) && array_key_exists('status', $customer)) {
                            if ($customer['status'] == 'ERROR') {
                                throw new Exception($customer['message']);
                            }
                        }
                        $this->updateUser($customer, $user, $password);
                    }
                }
            }
        } catch (\Exception $ex) {
            //dd($ex);
            throw new \Exception($ex->getMessage());
        }
    }

    public function FormRegister($flag)
    {
        $this->CreateUserTable();


        $path = app_path().'/Plugins/Reseller/views';
        \View::addNamespace('plugins', $path);
        echo \View::make('plugins::registerform')->render();
        exit();
    }

    public function PostRegister(Request $request)
    {
        $this->validate($request, [
                    'email'                 => 'required|max:50|email|unique:users',
                    'full_name'             => 'required',
                    'password'              => 'required|min:8|alpha_num|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                    'password_confirmation' => 'required|same:password',
                    'company'               => 'required',
                    'address-line-1'        => 'required',
                    'city'                  => 'required',
                    'state'                 => 'required',
                    //'country' => 'required',
                    'zipcode' => 'required',
                    //'phone-cc' => 'required',
                    'phone'     => 'required',
                    'lang-pref' => 'required',
        ]);


        $user = new User();
        $club = new Reseller();


        $name = $request->input('full_name');
        $email = $request->input('email');
        $company = $request->input('company');
        $address = $request->input('address-line-1');
        $city = $request->input('city');
        $state = $request->input('state');
        $country = $request->input('country');
        $zip = $request->input('zipcode');
        $phonecc = $request->input('phone-cc');
        $phone = $request->input('phone');
        $lang = $request->input('lang-pref');
        $password = $request->input('password');
        $club = $club->where('id', '1')->first();
        if ($club->userid && $club->apikey) {
            $userid = $club->userid; //'619613';
            $apikey = $club->apikey; //'xr05pRZKUZJAiJUvdtrZgRr2TiXkDKFi';
            $reseller = new ResellerController();
            $signup = $reseller->Signup($email, $userid, $apikey, $password, $name, $company, $address, $city, $state);
            //dd($signup);
            if (is_array($signup) && array_key_exists('status', $signup)) {
                if ($signup['status'] == 'ERROR') {
                    if ($signup['message'] != "$email is already a Customer.") {
                        throw new Exception($signup['message']);
                    }
                }
            }
            $customer = $reseller->GetCustomerbyUserName($email, $userid, $apikey);
            if (is_array($customer) && array_key_exists('status', $customer)) {
                if ($customer['status'] == 'ERROR') {
                    throw new Exception($customer['message']);
                }
            }

            $this->updateUser($customer, $user, $password);

            return redirect('auth/login');
        } else {
            throw new Exception('Invalid Reseller settings');
        }
    }

    public function CreateUserTable()
    {
        if (!Schema::hasColumn('users', 'resellerid')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('address1');
                $table->string('city');
                $table->string('state');
                $table->string('country');
                $table->string('zip');
                $table->string('telnocc');
                $table->string('langpref');
                $table->string('creationdt');
                $table->string('pin');
                $table->string('stateid');
                $table->string('resellerid');
                $table->string('customerid');
                $table->string('mobilenocc');
                $table->string('salescontactid');
                $table->string('totalreceipts');
            });
        }
    }

    public function createUserInFaveo($email, $password)
    {
        try {
            $reseller = new ResellerController();
            $club = new Reseller();
            $club = $club->where('id', '1')->first();
            $userid = $club->userid; //'619613';
            $apikey = $club->apikey; //'xr05pRZKUZJAiJUvdtrZgRr2TiXkDKFi';
            $customer = $reseller->GetCustomerbyUserName($email, $userid, $apikey);
            $user = new User();
            $this->updateUser($customer, $user, $password);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function updateUser($customer, $user, $password)
    {
        try {
            $user->creationdt = $customer['creationdt'];
            $user->email = $customer['username'];
            $user->langpref = $customer['langpref'];
            $user->pin = $customer['pin'];
            //$user->other_state = $customer['other_state'];
            $user->company = $customer['company'];
            $user->mobile = $customer['mobileno'];
            //$user->customerstatus=$customer['customerstatus'];
            $user->stateid = $customer['stateid'];
            $user->state = $customer['state'];
            $user->city = $customer['city'];
            $user->resellerid = $customer['resellerid'];
            $user->customerid = $customer['customerid'];
            $user->mobilenocc = $customer['mobilenocc'];
            $user->salescontactid = $customer['salescontactid'];
            $user->telnocc = $customer['telnocc'];
            $user->country = $customer['country'];
            $user->totalreceipts = $customer['totalreceipts'];
            $user->zip = $customer['zip'];
            $user->address1 = $customer['address1'];
            $user->first_name = $customer['name'];
            $user->role = 'user';
            $user->active = '1';

            //$pass = str_random(6);
            if (!$user->password) {
                $password = Hash::make($password);
                $user->password = $password;
            }
            $user->save();
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function crypto_rand_secure($min, $max)
    {
        $range = $max - $min;
        if ($range < 1) {
            return $min;
        } // not so random...
        $log = ceil(log($range, 2));
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd >= $range);

        return $min + $rnd;
    }

    public static function getToken($length)
    {
        $token = '';
        $codeAlphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $codeAlphabet .= 'abcdefghijklmnopqrstuvwxyz';
        $codeAlphabet .= '0123456789';
        $max = strlen($codeAlphabet) - 1;
        for ($i = 0; $i < $length; $i++) {
            $token .= $codeAlphabet[self::crypto_rand_secure(0, $max)];
        }

        return $token;
    }
}
