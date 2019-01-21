 <?php

 namespace App\UCenter;

 use MyController\UCClient\Contracts\UCenterAPIExecuteFilterContract;

 class MyUCenterAPIExecuteFilter implements UCenterAPIExecuteFilterContract
 {
     public function beforeRun()
     {
         //
     }

     public function afterRun()
     {
         //
         // \Debugbar::disable(); //Runtime 关闭 debugbar
     }
 }