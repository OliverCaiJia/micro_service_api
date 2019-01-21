<?php
namespace App\Models\Chain\Tender;
use \DB;
use App\Helpers\Utils\Utils;
use App\Models\Chain\AbstractHandler;


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TenderChain
 *
 * @author Administrator
 */
class AddBorrowTenderTypeHandler extends AbstractHandler
{

    private $tenderId = 0;
    private $error = [];

    public function __construct($tenderId,$from)
    {
        $this->tenderId = $tenderId;
        $this->from = $from;
    }

    public function handleRequest()
    {
        return $this->putBorrowTenderType();
    }

    //添加投资来源
    public function putBorrowTenderType(){
        $borrowTypeForm = [
            'tender_id' => $this->tenderId,
            //1为app端 2、Android 3、iOS 4、H5/微信
            'from' => $this->from,
        ];
        return DB::table('diyou_borrow_tender_type_from')->insertGetId($borrowTypeForm);
    }


}
