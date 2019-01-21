<?php

namespace App\Models\Chain\Tender;

use App\Models\Chain\AbstractHandler;
use App\Models\Chain\Tender\CheckBorrowScaleHandler;

class DoInvestHandler extends AbstractHandler
{

    private $parms = array();

    public function __construct($parms)
    {
        $this->parms = $parms;
    }

    public function handleRequest()
    {
        $this->setSuccessor(new CheckBorrowScaleHandler($this->parms));
        return $this->getSuccessor()->handleRequest();
    }

}
