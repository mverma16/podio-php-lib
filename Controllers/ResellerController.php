<?php

namespace App\Plugins\Reseller\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Plugins\Reseller\Model\Reseller;

class ResellerController extends Controller {

    protected $reseller;

    public function __construct() {
        $reseller = new Reseller();
        $this->reseller = $reseller->find(1);
    }

    /**
     * Get domain name details  
     * @param type $domain
     * @param type $userid
     * @param type $apikey
     * @return type
     */
    function GetDomain($domain, $userid, $apikey) {

        $url = $this->reseller->url . "api/domains/search.json?auth-userid=" . $userid . "&api-key=" . $apikey . "&no-of-records=10&page-no=1&domain-name=" . $domain . "";
        $response = $this->file_contents($url);
        return $response;
    }

    /**
     * Get customer details from customer id
     * @param type $customer_id
     * @param type $userid
     * @param type $apikey
     * @return type
     */
    function GetCustomerbyId($customer_id, $userid, $apikey) {
        $url = $this->reseller->url . "api/customers/details-by-id.json?auth-userid=" . $userid . "&api-key=" . $apikey . "&customer-id=" . $customer_id . "";
        $response = $this->file_contents($url);
        return $response;
    }

    /**
     * Get customer details using user name
     * @param type $customer_username
     * @param type $userid
     * @param type $apikey
     * @return type
     */
    function GetCustomerbyUserName($customer_username, $userid, $apikey) {
        $url = $this->reseller->url . "api/customers/details.json?auth-userid=" . $userid . "&api-key=" . $apikey . "&username=" . $customer_username . "";

        $response = $this->file_contents($url);
        return $response;
    }

    /**
     * reseller club sign up
     * @param type $name
     * @param type $email
     * @param type $company
     * @param type $password
     * @param type $address
     * @param type $city
     * @param type $state
     * @param type $country
     * @param type $zip
     * @param type $phonecc
     * @param type $phone
     * @param type $lang
     * @param type $userid
     * @param type $apikey
     * @return type Array
     */
    function Signup($email, $userid, $apikey, $password, $name, $company = 'not available', $address = 'not avalable', $city = 'not available', $state = 'not avalable', $country = 'IN', $zip = '560076', $phonecc = '+91', $phone = '9999999999', $lang = 'en') {

        $url = $this->reseller->url . "api/customers/signup.json?auth-userid=$userid&api-key=$apikey&username=$email&passwd=$password&name=$name&company=$company&address-line-1=$address&city=$city&state=$state&country=$country&zipcode=$zip&phone-cc=$phonecc&phone=$phone&lang-pref=$lang";
        //dd($url);
        $response = $this->file_contents($url);
        return $response;
    }

    /**
     * Generate token from reseller club 
     * @param type $ip
     * @param type $userid
     * @param type $apikey
     * @param type $username
     * @param type $password
     * @return type
     */
    function GenerateToken($ip, $userid, $apikey, $username, $password) {
        $url = $this->reseller->url . "api/customers/generate-token.json?auth-userid=$userid&api-key=$apikey&username=$username&passwd=$password&ip=$ip";
        $data = $this->file_contents($url);
        return $data;
    }

    /**
     * Excecute file_get_contents option to read false option
     * @param type $url
     * @return string
     */
    public function file_contents($url) {
        $url = str_replace(" ", '%20', $url);
        $str = $this->curl($url);
        //dd($str);
        return $str;
    }

    public function curl($url) {
        $url = str_replace(" ", '%20', $url);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json = curl_exec($ch);
        if (curl_exec($ch) === false) {
            throw new \Exception(curl_error($ch));
            
        }
        if (json_last_error() === JSON_ERROR_NONE) {
            $result = json_decode($json, true);
        } else {
            $result = $json;
        }
        curl_close($ch);
        return $result;
    }

    /**
     * Change the password of resellerclub
     * @param type $userid
     * @param type $apikey
     * @param type $customerid
     * @param type $password
     * @return type
     */
    function ChangePassword($userid, $apikey, $customerid, $password) {


        $url = $this->reseller->url . "api/customers/change-password.json?auth-userid=$userid&api-key=$apikey&customer-id=$customerid&new-passwd=$password";
        $response = $this->file_contents($url);
        return $response;
    }

    /**
     * Get forgot password url from reseller club
     * @param type $userid
     * @param type $apikey
     * @param type $username
     * @return type
     */
    function ForgotPassword($userid, $apikey, $username) {

        $url = $this->reseller->url . "api/customers/forgot-password.xml?auth-userid=$userid&api-key=$apikey&username=$username";
        $response = $this->file_contents($url);
        return $response;
    }

    /**
     * Get trasactional details of reseller club
     * @param type $userid
     * @param type $apikey
     * @param type $username
     * @return type
     */
    function getTrasaction($userid, $apikey, $username) {

        $url = $this->reseller->url . "api/billing/customer-transactions/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&username=$username";
        $response = $this->file_contents($url);
        return $response;
    }

    /**
     * Get single domain details of US Linux server
     * @param type $userid
     * @param type $apikey
     * @param type $customerid
     * @return type
     */
    function getsingleDomain_LinuxUS($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/singledomainhosting/linux/us/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid&status=Active";
        $response = $this->file_contents($url);
        return $response;
    }

    /**
     * Get single domain details of INDIA Linux server
     * @param type $userid
     * @param type $apikey
     * @param type $customerid
     * @return type
     */
    function getsingleDomain_LinuxIN($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/singledomainhosting/linux/in/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid&status=Active";
        $response = $this->file_contents($url);
        return $response;
    }

    /**
     * Get single domain details of UK Linux server
     * @param type $userid
     * @param type $apikey
     * @param type $customerid
     * @return type
     */
    function getsingleDomain_LinuxUK($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/singledomainhosting/linux/uk/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid&status=Active";
        $response = $this->file_contents($url);
        return $response;
    }

    /**
     * Get single domain details of Hong Kong  Linux server
     * @param type $userid
     * @param type $apikey
     * @param type $customerid
     * @return type
     */
    function getsingleDomain_LinuxHK($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/singledomainhosting/linux/hk/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid&status=Active";
        $response = $this->file_contents($url);
        return $response;
    }

    /**
     * Get single domain details of Turkey Linux server
     * @param type $userid
     * @param type $apikey
     * @param type $customerid
     * @return type
     */
    function getsingleDomain_LinuxTR($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/singledomainhosting/linux/tr/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid&status=Active";
        $response = $this->file_contents($url);
        return $response;
    }

    /**
     * Get Webservices of the perticular customer
     * @param type $userid
     * @param type $apikey
     * @param type $customerid
     * @return type
     */
    function getWebservices($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/webservices/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid&status=Active";
        $response = $this->file_contents($url);
        return $response;
    }

    /**
     * Get details of windows single domain in US
     * @param type $userid
     * @param type $apikey
     * @param type $customerid
     * @return type
     */
    function getsingleDomain_WindowsUS($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/singledomainhosting/windows/us/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid&status=Active";
        $response = $this->file_contents($url);
        return $response;
    }

    /**
     * Get details of windows single domain in UK
     * @param type $userid
     * @param type $apikey
     * @param type $customerid
     * @return type
     */
    function getsingleDomain_WindowsUK($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/singledomainhosting/windows/uk/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid&status=Active";
        $response = $this->file_contents($url);
        return $response;
    }

    /**
     * Get details of windows single domain in INDIA
     * @param type $userid
     * @param type $apikey
     * @param type $customerid
     * @return type
     */
    function getsingleDomain_WindowsIN($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/singledomainhosting/windows/in/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid&status=Active";
        $response = $this->file_contents($url);
        return $response;
    }

    /**
     * Get details of windows single domain in Hong Kong
     * @param type $userid
     * @param type $apikey
     * @param type $customerid
     * @return type
     */
    function getsingleDomain_WindowsHK($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/singledomainhosting/windows/hk/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid&status=Active";
        $response = $this->file_contents($url);
        return $response;
    }

    /**
     * Get details of windows single domain in Turkey
     * @param type $userid
     * @param type $apikey
     * @param type $customerid
     * @return type
     */
    function getsingleDomain_WindowsTR($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/singledomainhosting/windows/tr/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid&status=Active";
        $response = $this->file_contents($url);
        return $response;
    }

    /**
     * Get details of linux multiple domain in US
     * @param type $userid
     * @param type $apikey
     * @param type $customerid
     * @return type
     */
    function getmultiDomain_LinuxUS($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/multidomainhosting/linux/us/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid&status=Active";
        $response = $this->file_contents($url);
        return $response;
    }

    /**
     * Get details of linux multiple domain in UK
     * @param type $userid
     * @param type $apikey
     * @param type $customerid
     * @return type
     */
    function getmultiDomain_LinuxUK($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/multidomainhosting/linux/uk/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid&status=Active";
        $response = $this->file_contents($url);
        return $response;
    }

    /**
     * Get details of linux multiple domain in India
     * @param type $userid
     * @param type $apikey
     * @param type $customerid
     * @return type
     */
    function getmultiDomain_LinuxIN($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/multidomainhosting/linux/in/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid&status=Active";
        $response = $this->file_contents($url);
        return $response;
    }

    /**
     * Get details of linux multiple domain in Hong kong
     * @param type $userid
     * @param type $apikey
     * @param type $customerid
     * @return type
     */
    function getmultiDomain_LinuxHK($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/multidomainhosting/linux/hk/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid&status=Active";
        $response = $this->file_contents($url);
        return $response;
    }

    /**
     * Get details of linux multiple domain in Turkey
     * @param type $userid
     * @param type $apikey
     * @param type $customerid
     * @return type
     */
    function getmultiDomain_LinuxTR($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/multidomainhosting/linux/tr/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid&status=Active";
        $response = $this->file_contents($url);
        return $response;
    }

    /**
     * Get details of Windows multiple domain in US
     * @param type $userid
     * @param type $apikey
     * @param type $customerid
     * @return type
     */
    function getmultiDomain_WindowsUS($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/multidomainhosting/windows/us/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid&status=Active";
        $response = $this->file_contents($url);
        return $response;
    }

    /**
     * Get details of Windows multiple domain in UK
     * @param type $userid
     * @param type $apikey
     * @param type $customerid
     * @return type
     */
    function getmultiDomain_WindowsUK($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/multidomainhosting/windows/uk/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid&status=Active";
        $response = $this->file_contents($url);
        return $response;
    }

    /**
     * Get details of Windows multiple domain in India
     * @param type $userid
     * @param type $apikey
     * @param type $customerid
     * @return type
     */
    function getmultiDomain_WindowsIN($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/multidomainhosting/windows/in/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid&status=Active";
        $response = $this->file_contents($url);
        return $response;
    }

    /**
     * Get details of Windows multiple domain in Hong Kong
     * @param type $userid
     * @param type $apikey
     * @param type $customerid
     * @return type
     */
    function getmultiDomain_WindowsHK($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/multidomainhosting/windows/hk/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid&status=Active";
        $response = $this->file_contents($url);
        return $response;
    }

    /**
     * Get details of Windows multiple domain in Turkey
     * @param type $userid
     * @param type $apikey
     * @param type $customerid
     * @return type
     */
    function getmultiDomain_WindowsTR($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/multidomainhosting/windows/tr/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid&status=Active";
        $response = $this->file_contents($url);
        return $response;
    }

    /**
     * Get details of Reseller Linux domain in US
     * @param type $userid
     * @param type $apikey
     * @param type $customerid
     * @return type
     */
    function getReseller_LinuxUS($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/resellerhosting/linux/us/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid&status=Active";
        $response = $this->file_contents($url);
        return $response;
    }

    /**
     * Get details of Reseller Linux domain in UK
     * @param type $userid
     * @param type $apikey
     * @param type $customerid
     * @return type
     */
    function getReseller_LinuxUK($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/resellerhosting/linux/uk/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid&status=Active";
        $response = $this->file_contents($url);
        return $response;
    }

    /**
     * Get details of Reseller Linux domain in India
     * @param type $userid
     * @param type $apikey
     * @param type $customerid
     * @return type
     */
    function getReseller_LinuxIN($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/resellerhosting/linux/in/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid&status=Active";
        $response = $this->file_contents($url);
        return $response;
    }

    /**
     * Get details of Reseller Linux domain in Turkey
     * @param type $userid
     * @param type $apikey
     * @param type $customerid
     * @return type
     */
    function getReseller_LinuxTR($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/resellerhosting/linux/tr/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid&status=Active";
        $response = $this->file_contents($url);
        return $response;
    }

    /**
     * Get details of Reseller Windows domain in US
     * @param type $userid
     * @param type $apikey
     * @param type $customerid
     * @return type
     */
    function getReseller_WindowsUS($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/resellerhosting/windows/us/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid&status=Active";
        $response = $this->file_contents($url);
        return $response;
    }

    /**
     * Get details of Reseller Windows domain in UK
     * @param type $userid
     * @param type $apikey
     * @param type $customerid
     * @return type
     */
    function getReseller_WindowsUK($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/resellerhosting/windows/uk/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid&status=Active";
        $response = $this->file_contents($url);
        return $response;
    }

    /**
     * Get details of Reseller Windows domain in India
     * @param type $userid
     * @param type $apikey
     * @param type $customerid
     * @return type
     */
    function getReseller_WindowsIN($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/resellerhosting/windows/in/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid&status=Active";
        $response = $this->file_contents($url);
        return $response;
    }

    /**
     * Get details of Reseller Windows domain in Turkey
     * @param type $userid
     * @param type $apikey
     * @param type $customerid
     * @return type
     */
    function getReseller_WindowsTR($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/resellerhosting/windows/tr/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid&status=Active";
        $response = $this->file_contents($url);
        return $response;
    }

    /**
     * Get details of VPS Linux domain in US
     * @param type $userid
     * @param type $apikey
     * @param type $customerid
     * @return type
     */
    function getVPS_LinuxUS($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/vps/linux/us/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid&status=Active";
        $response = $this->file_contents($url);
        return $response;
    }

    /**
     * Get details of VPS Linux domain in IN
     * @param type $userid
     * @param type $apikey
     * @param type $customerid
     * @return type
     */
    function getVPS_LinuxIN($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/vps/linux/in/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid&status=Active";
        $response = $this->file_contents($url);
        return $response;
    }

    function EnterpriseEmail($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/enterpriseemail/us/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid&status=Active";
        $response = $this->file_contents($url);
        return $response;
    }

    function BusinessEmail($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/eelite/us/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid&status=Active";
        $response = $this->file_contents($url);
        return $response;
    }

    function DedicatedServer($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/dedicatedserver/linux/us/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid&status=Active";
        $response = $this->file_contents($url);
        return $response;
    }

    function ManagedServer($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/managedserver/linux/us/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid";
        $response = $this->file_contents($url);
        return $response;
    }

    function SiteLock($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/sitelock/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid&status=Active";
        $response = $this->file_contents($url);
        return $response;
    }

    function CodeGuard($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/codeguard/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid&status=Active";
        $response = $this->file_contents($url);
        return $response;
    }

    function GetDomainReg($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/domains/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid&status=Active";
        $response = $this->file_contents($url);
        return $response;
    }

    function Ssl($userid, $apikey, $customerid) {
        $url = $this->reseller->url . "api/sslcert/search.json?auth-userid=$userid&api-key=$apikey&no-of-records=20&page-no=1&customer-id=$customerid";
        $response = $this->file_contents($url);
        return $response;
    }
    
    function test($userid, $apikey,$url,$domain) {
        try{
        $url = $url . "api/domains/search.json?auth-userid=" . $userid . "&api-key=" . $apikey . "&no-of-records=10&page-no=1&domain-name=" . $domain . "";
        $result = $this->curl($url);
        return $result;
        }catch(\Exception $ex){
            return $ex->getMessage();
        }
    }

}
