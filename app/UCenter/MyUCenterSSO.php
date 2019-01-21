 <?php

 namespace App\UCenter;

 use MyController\UCClient\Contracts\UCenterSSOContract;

 class MyUCenterSSO implements UCenterSSOContract
 {
     public function synLogin($uid, $username = '')
     {
         /** 同步登录代码 **/
     }

     public function synLogout()
     {
         /** 同步注销代码 **/
     }
 }