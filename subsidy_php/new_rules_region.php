<?php

function Exact16ToBeReplacedTeeth_upper() {
    $to_be_replaced = $this->schema->to_be_replaced($region=$regObj->upper_jaw());
    $region = $this->schema->get_teeth_in_region($regObj->upper_jaw());
    
    if (count($to_be_replaced) == 16) {
        array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "4.2", "region"=> "upper_jaw", "applied_rule"=> "Exact16ToBeReplacedTeeth_upper"]
        );

        return True;
    }
}

function Exact16ToBeReplacedTeeth_mandible() {

}

?>