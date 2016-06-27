<?php

namespace App\Plugins\Reseller\Controllers;

use App\Http\Controllers\Controller;
use App\Plugins\Reseller\Model\Reseller;
use App\User;

class ResellerOrderController extends Controller
{
    public function __construct()
    {
        $club = new ResellerController();
        $reseller = new Reseller();
        $this->club = $club;
        $this->reseller = $reseller->where('id', '1')->first();
    }

    public function ResellerOrderButton($user)
    {
        return "<button class='btn btn-primary' id=reseller onclick=reseller(".$user.")>Show All Orders</button>
            <script type=text/javascript>
                function reseller(id)
                            {
                                var id = id;
                            $.ajax({
                            url:'../order-search/'+id,
                                    type: 'get',
                                    beforeSend: function() {
                                            $('#gifshow').show();
                                    },
                                    success: function(html) {
                                        $('#gifshow').hide();
                                    $('#resultdiv').html(html);
                                    }
                            });
                                    }
            </script>";
    }

    /**
     * Search for the order.
     *
     * @param type                                           $id
     * @param \App\Http\Controllers\plugin\resellerclub\User $user
     */
    public function getSingleDomain($id, User $user)
    {
        $user = $user->where('id', $id)->first();
        //dd($user);

        if ($user->customerid) {
            $products = [];
            $singleus = $this->club->getsingleDomain_LinuxUS($this->reseller->userid, $this->reseller->apikey, $user->customerid);

            if (is_array($singleus)) {
                if (array_key_exists('1', $singleus)) {
                    $products = array_merge($products, ['single_linux_us' => $singleus[1]]);
                }
                $singleuk = $this->club->getsingleDomain_LinuxUK($this->reseller->userid, $this->reseller->apikey, $user->customerid);
                if (array_key_exists('1', $singleuk)) {
                    $products = array_merge($products, ['single_linux_uk' => $singleuk[1]]);
                }
                $singlein = $this->club->getsingleDomain_LinuxIN($this->reseller->userid, $this->reseller->apikey, $user->customerid);
                //dd($singlein['1']);
                if (array_key_exists('1', $singlein)) {
                    $products = array_merge($products, ['single_linux_in' => $singlein[1]]);
                }
                $singlehk = $this->club->getsingleDomain_LinuxHK($this->reseller->userid, $this->reseller->apikey, $user->customerid);
                if (array_key_exists('1', $singlehk)) {
                    $products = array_merge($products, ['single_linux_hk' => $singlehk[1]]);
                }
                $singletr = $this->club->getsingleDomain_LinuxTR($this->reseller->userid, $this->reseller->apikey, $user->customerid);
                if (array_key_exists('1', $singletr)) {
                    $products = array_merge($products, ['single_linux_tr' => $singletr[1]]);
                }

                $webservice = $this->club->getWebservices($this->reseller->userid, $this->reseller->apikey, $user->customerid);
                if (array_key_exists('1', $webservice)) {
                    $products = array_merge($products, ['webservices' => $webservice]);
                }
                $singleWindowsus = $this->club->getsingleDomain_WindowsUS($this->reseller->userid, $this->reseller->apikey, $user->customerid);
                if (array_key_exists('1', $singleWindowsus)) {
                    $products = array_merge($products, ['single_windows_us' => $singleWindowsus[1]]);
                }

                $singleWindowsuk = $this->club->getsingleDomain_WindowsUK($this->reseller->userid, $this->reseller->apikey, $user->customerid);
                if (array_key_exists('1', $singleWindowsuk)) {
                    $products = array_merge($products, ['single_windows_uk' => $singleWindowsuk[1]]);
                }
                $singleWindowsin = $this->club->getsingleDomain_WindowsIN($this->reseller->userid, $this->reseller->apikey, $user->customerid);
                if (array_key_exists('1', $singleWindowsin)) {
                    $products = array_merge($products, ['single_windows_in' => $singleWindowsin[1]]);
                }
                $singleWindowshk = $this->club->getsingleDomain_WindowsHK($this->reseller->userid, $this->reseller->apikey, $user->customerid);
                if (array_key_exists('1', $singleWindowshk)) {
                    $products = array_merge($products, ['single_windows_hk' => $singleWindowshk[1]]);
                }
                $singleWindowstr = $this->club->getsingleDomain_WindowsTR($this->reseller->userid, $this->reseller->apikey, $user->customerid);
                if (array_key_exists('1', $singleWindowstr)) {
                    $products = array_merge($products, ['single_windows_tr' => $singleWindowstr[1]]);
                }
                $multiLinuxus = $this->club->getmultiDomain_LinuxUS($this->reseller->userid, $this->reseller->apikey, $user->customerid);
                if (array_key_exists('1', $multiLinuxus)) {
                    $products = array_merge($products, ['multi_linux_us' => $multiLinuxus[1]]);
                }
                $multiLinuxuk = $this->club->getmultiDomain_LinuxUK($this->reseller->userid, $this->reseller->apikey, $user->customerid);
                if (array_key_exists('1', $multiLinuxuk)) {
                    $products = array_merge($products, ['multi_linux_uk' => $multiLinuxuk[1]]);
                }
                $multiLinuxin = $this->club->getmultiDomain_LinuxIN($this->reseller->userid, $this->reseller->apikey, $user->customerid);
                if (array_key_exists('1', $multiLinuxin)) {
                    $products = array_merge($products, ['multi_linux_in' => $multiLinuxin[1]]);
                }
                $multiLinuxhk = $this->club->getmultiDomain_LinuxHK($this->reseller->userid, $this->reseller->apikey, $user->customerid);
                if (array_key_exists('1', $multiLinuxhk)) {
                    $products = array_merge($products, ['multi_linux_hk' => $multiLinuxhk[1]]);
                }
                $multiLinuxtr = $this->club->getmultiDomain_LinuxTR($this->reseller->userid, $this->reseller->apikey, $user->customerid);
                if (array_key_exists('1', $multiLinuxtr)) {
                    $products = array_merge($products, ['multi_linux_tr' => $multiLinuxtr[1]]);
                }
                $multiWindowsus = $this->club->getmultiDomain_WindowsUS($this->reseller->userid, $this->reseller->apikey, $user->customerid);
                if (array_key_exists('1', $multiWindowsus)) {
                    $products = array_merge($products, ['multi_windows_us' => $multiWindowsus[1]]);
                }
                $multiWindowsuk = $this->club->getmultiDomain_WindowsUK($this->reseller->userid, $this->reseller->apikey, $user->customerid);
                if (array_key_exists('1', $multiWindowsuk)) {
                    $products = array_merge($products, ['multi_windows_uk' => $multiWindowsuk[1]]);
                }
                $multiWindowsin = $this->club->getmultiDomain_WindowsIN($this->reseller->userid, $this->reseller->apikey, $user->customerid);
                if (array_key_exists('1', $multiWindowsin)) {
                    $products = array_merge($products, ['multi_windows_in' => $multiWindowsin[1]]);
                }
                $multiWindowshk = $this->club->getmultiDomain_WindowsHK($this->reseller->userid, $this->reseller->apikey, $user->customerid);
                if (array_key_exists('1', $multiWindowshk)) {
                    $products = array_merge($products, ['multi_windows_hk' => $multiWindowshk[1]]);
                }
                $multiWindowstr = $this->club->getmultiDomain_WindowsTR($this->reseller->userid, $this->reseller->apikey, $user->customerid);
                if (array_key_exists('1', $multiWindowstr)) {
                    $products = array_merge($products, ['multi_windows_tr' => $multiWindowstr[1]]);
                }
                $resellerLinuxus = $this->club->getReseller_LinuxUS($this->reseller->userid, $this->reseller->apikey, $user->customerid);
                if (array_key_exists('1', $resellerLinuxus)) {
                    $products = array_merge($products, ['reseller_linux_us' => $resellerLinuxus[1]]);
                }
                $resellerLinuxuk = $this->club->getReseller_LinuxUK($this->reseller->userid, $this->reseller->apikey, $user->customerid);
                if (array_key_exists('1', $resellerLinuxuk)) {
                    $products = array_merge($products, ['reseller_linux_uk' => $resellerLinuxuk[1]]);
                }
                $resellerLinuxin = $this->club->getReseller_LinuxIN($this->reseller->userid, $this->reseller->apikey, $user->customerid);
                if (array_key_exists('1', $resellerLinuxin)) {
                    $products = array_merge($products, ['reseller_linux_in' => $resellerLinuxin[1]]);
                }
                $resellerLinuxtr = $this->club->getReseller_LinuxTR($this->reseller->userid, $this->reseller->apikey, $user->customerid);
                if (array_key_exists('1', $resellerLinuxtr)) {
                    $products = array_merge($products, ['reseller_linux_tr' => $resellerLinuxtr[1]]);
                }
                $resellerWindowsus = $this->club->getReseller_WindowsUS($this->reseller->userid, $this->reseller->apikey, $user->customerid);
                if (array_key_exists('1', $resellerWindowsus)) {
                    $products = array_merge($products, ['reseller_windows_us' => $resellerWindowsus[1]]);
                }
                $resellerWindowsuk = $this->club->getReseller_WindowsUK($this->reseller->userid, $this->reseller->apikey, $user->customerid);
                //dd($resellerWindowsuk);
                if (array_key_exists('1', $resellerWindowsuk)) {
                    $products = array_merge($products, ['reseller_windows_uk' => $resellerWindowsuk[1]]);
                }
                $resellerWindowsin = $this->club->getReseller_WindowsIN($this->reseller->userid, $this->reseller->apikey, $user->customerid);
                if (array_key_exists('1', $resellerWindowsin)) {
                    $products = array_merge($products, ['reseller_windows_in' => $resellerWindowsin[1]]);
                }
                $resellerWindowstr = $this->club->getReseller_WindowsTR($this->reseller->userid, $this->reseller->apikey, $user->customerid);
                if (array_key_exists('1', $resellerWindowstr)) {
                    $products = array_merge($products, ['reseller_windows_in' => $resellerWindowstr[1]]);
                }
                $VPSus = $this->club->getVPS_LinuxUS($this->reseller->userid, $this->reseller->apikey, $user->customerid);
                if (array_key_exists('1', $VPSus)) {
                    $products = array_merge($products, ['VPS_us' => $VPSus[1]]);
                }
                $VPSin = $this->club->getVPS_LinuxIN($this->reseller->userid, $this->reseller->apikey, $user->customerid);
                if (array_key_exists('1', $VPSin)) {
                    $products = array_merge($products, ['VPS_in' => $VPSin[1]]);
                }
                $enterpriseEmail = $this->club->EnterpriseEmail($this->reseller->userid, $this->reseller->apikey, $user->customerid);
                if (array_key_exists('1', $enterpriseEmail)) {
                    $products = array_merge($products, ['enterprise_Email' => $enterpriseEmail[1]]);
                }
                $businessEmail = $this->club->BusinessEmail($this->reseller->userid, $this->reseller->apikey, $user->customerid);
                if (array_key_exists('1', $businessEmail)) {
                    $products = array_merge($products, ['enterprise_Email' => $businessEmail[1]]);
                }
                $dedicatedServer = $this->club->DedicatedServer($this->reseller->userid, $this->reseller->apikey, $user->customerid);
                if (array_key_exists('1', $dedicatedServer)) {
                    $products = array_merge($products, ['dedicated_server' => $dedicatedServer[1]]);
                }
                $managedServer = $this->club->ManagedServer($this->reseller->userid, $this->reseller->apikey, $user->customerid);
                if (array_key_exists('1', $managedServer)) {
                    $products = array_merge($products, ['Managed_server' => $managedServer[1]]);
                }
                $siteLock = $this->club->SiteLock($this->reseller->userid, $this->reseller->apikey, $user->customerid);
                if (array_key_exists('1', $siteLock)) {
                    $products = array_merge($products, ['site_lock' => $siteLock[1]]);
                }
                $codeGuard = $this->club->CodeGuard($this->reseller->userid, $this->reseller->apikey, $user->customerid);
                if (array_key_exists('1', $codeGuard)) {
                    $products = array_merge($products, ['code_guard' => $codeGuard[1]]);
                }
                $getDomainReg = $this->club->GetDomainReg($this->reseller->userid, $this->reseller->apikey, $user->customerid);
                if (array_key_exists('1', $getDomainReg)) {
                    $products = array_merge($products, ['domain_reg' => $getDomainReg[1]]);
                }
                $ssl = $this->club->Ssl($this->reseller->userid, $this->reseller->apikey, $user->customerid);
                if (array_key_exists('1', $ssl)) {
                    $products = array_merge($products, ['ssl' => $ssl[1]]);
                }
            } else {
                echo '<div class =box>';
                echo '<div class =box-header>';
                echo "<h1 class = box-title style='color:red'>Sorry! Not able to connect to Resellerclub ! Please check your Connection .</h1>";
                echo '</div>';
                echo '<div class =box-body>';
                echo '</div>';
                echo '</div>';
            }


            echo '<div class =box>';
            echo '<div class =box-header>';
            echo '<h3 class = box-title>Reseller Product List</h3>';
            echo '</div>';
            echo '<div class =box-body>';
            echo "<table class='table table-bordered table-striped' id='example1'>";
            echo '<thead>';
            echo '<tr>';
            echo '<th>Domain Name</th>';
            echo '<th>Product Name</th>';
            echo '<th>Expiry</th>';
            echo '<th>Status</th>';
            echo '<th>Customer ID</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';


            if (count($products) > 0) {
                foreach ($products as $product) {
                    echo '<tr>';
                    echo '<td>'.$product['entity.description'].'</td>';
                    echo '<td>'.$product['entitytype.entitytypename'].'</td>';
                    if (array_key_exists('orders.endtime', $product)) {
                        echo '<td>'.date('Y-m-d', $product['orders.endtime']).'</td>';
                    } else {
                        echo '<td>---</td>';
                    }


                    echo '<td>'.$product['entity.currentstatus'].'</td>';
                    echo '<td>'.$product['entity.customerid'].'</td>';
                //echo "<td>".$values."<td>";
                echo '</tr>';
                }
            } else {
                echo "<tr><td>$user->first_name $user->last_name has no orders</td></tr>";
            }

            echo '</tbody>';
            echo '</table>';
            echo '</div>';
            echo '</div>';
        } else {
            echo '<div class =box>';
            echo '<div class =box-header>';
            echo "<h1 class = box-title style='color:red'>Reseller Customer id is not available</h1>";
            echo '</div>';
            echo '<div class =box-body>';
            echo '</div>';
            echo '</div>';
        }
    }
}
