<?php

// require_once('new_rules_region.php');


$TOOTH_NUMBERS_ISO = array(
    18, 17, 16, 15, 14, 13, 12, 11,
    21, 22, 23, 24, 25, 26, 27, 28,
    38, 37, 36, 35, 34, 33, 32, 31,
    41, 42, 43, 44, 45, 46, 47, 48
);

$front_end = array(18 => 'f', 17 => 'f', 16 => 'f', 15 => 'f', 14 => 'f', 13 => 'f', 12 => 'f', 11 => 'f',
    21 => 'f', 22 => 'f', 23 => 'f', 24 => 'f', 25 => 'f', 26 => 'f', 27 => 'f', 28 => 'f',
    38 => 'f', 37 => 'f', 36 => '', 35 => '', 34 => '', 33 => '', 32 => '', 31 => '',
    41 => '', 42 => '', 43 => '', 44 => 'f', 45 => 'ww', 46 => 'ww', 47 => 'f', 48 => 'f');


$teeth_with_status = [];
$teeth_region_eveluate = [];
$teeth_subsidy_eveluate = [];
$teeth_inter_dental_gaps = [];


// print_r($front_end);
main_input($front_end);
function main_input($schema) {

    global $teeth_with_status, $teeth_region_eveluate, $teeth_subsidy_eveluate;

    // data validation
    if (! is_array($schema)) {
        echo json_encode(array("message" => "Error Processing as data parameter as a array."));
        return;
    }

    if (sizeof($schema) !== 32) {
        // throw new Exception("Each one of the 32 teeth should be represented in the tuple");
        echo json_encode(array("message" => "Each one of the 32 teeth should be represented in the tuple."));
        return;
    }

    // Check for teeth nos with a status
    foreach ($schema as $tooth_number => $status) {
        // print_r($status);
        // if($status[1] !== '') {
        if($status !== '') {
            // print_r($status[0] .', ');
            // array_push($teeth_with_status, intval($status[0]));
            array_push($teeth_with_status, intval($tooth_number));
        }
        
    }

    is_whole_mouth($teeth_with_status);
    is_upper_jaw($teeth_with_status);
    is_upper_jaw_left_end($teeth_with_status);
    is_upper_jaw_right_end($teeth_with_status);
    is_upper_jaw_front($teeth_with_status);
    is_mandible($teeth_with_status);
    is_mandible_left_end($teeth_with_status);
    is_mandible_right_end($teeth_with_status);
    is_mandible_front($teeth_with_status);

    // print_r(right(right(18, $schema)['tooth'], $schema));

    // print_r($teeth_region_eveluate);
    
    /** Execute the rules **/
    //4.x
    Exact16ToBeReplacedTeeth_upper($schema);
    Exact16ToBeReplacedTeeth_mandible($schema);
    Between13And15ToBeReplacedTeeth_upper($schema);
    Between13And15ToBeReplacedTeeth_mandible($schema);

    //3.x
    UnilateralFreeEndToBeReplacedTeethAtLeast1_upper($schema);
    UnilateralFreeEndToBeReplacedTeethAtLeast1_mandible($schema);
    Between5And12ToBeReplacedTeeth_upper($schema);
    Between5And12ToBeReplacedTeeth_mandible($schema);
    BilateralFreeEnd_upper($schema, $teeth_with_status);
    BilateralFreeEnd_mandible($schema, $teeth_with_status);

    // 2.x
    BiggestInterdentalGapInFrontRegionExactToBeReplaced4($schema);
    BiggestInterdentalGapExactToBeReplaced3($schema);
    BiggestInterdentalGapExactToBeReplaced2($schema);
    BiggestInterdentalGapExactToBeReplaced1($schema);
    Exact1ToBeReplacedTeethInterdentalGapInFrontRegion($schema);
    Exact2_3ToBeReplacedTeethInterdentalGapInFrontRegion($schema);

    //1.x
    StatusPwInPosteriorRegion($schema);
    StatusPwInFrontRegion($schema);
    ToBeTreatedWithNoAbutmentTeethIncluded($schema); //to-do

    // remove dependencies or duplicates
    // print_r($teeth_subsidy_eveluate);
    $remove_subsidies = [];

    for($len=0; $len<count($teeth_subsidy_eveluate); $len++) {
        // if($teeth_subsidy_eveluate[$len]['subsidy'] == '4.2') {
        if(startswith($teeth_subsidy_eveluate[$len]['subsidy'], '4')) {
            for($lenn=0; $lenn<count($teeth_subsidy_eveluate); $lenn++) {
                //UnilateralFreeEndToBeReplacedTeethAtLeast1_upper
                // if($teeth_subsidy_eveluate[$lenn]['subsidy'] == '3.1') {
                if(startswith($teeth_subsidy_eveluate[$lenn]['subsidy'], '3')) {
                    array_push($remove_subsidies, $lenn);
                }
            }
        }
    }

    for($rem=0; $rem<count($remove_subsidies); $rem++) {
        unset($teeth_subsidy_eveluate[$remove_subsidies[$rem]]);
    }

    $teeth_subsidy_eveluate = array_values(($teeth_subsidy_eveluate));

    echo (get_jaw(15));

    echo json_encode($teeth_subsidy_eveluate);
}

function startswith ($string, $startString) 
{ 
    $len = strlen($startString); 
    return (substr($string, 0, $len) === $startString); 
}

function sub_array_teeth($start, $end, $size) {
    // $teeth_region = array_slice($GLOBALS['TOOTH_NUMBERS_ISO'], $start, $size, TRUE);
    $teeth_region = array_slice($GLOBALS['teeth_with_status'], $start, $size);
    
    // echo nl2br("\n");

    if (count($teeth_region) == $size AND end($teeth_region) == $end )
        return TRUE;
    else
        return FALSE;
}


function is_whole_mouth($teeth) {
    global $teeth_region_eveluate;

    if(sub_array_teeth(position_selected(18), 48, 32) ) {
        array_push($teeth_region_eveluate, 'whole mouth');
        return TRUE;
    }
    else {
        return FALSE;
    }
}

# Upper jaw
function is_upper_jaw($teeth) {
    global $teeth_region_eveluate;

    if(sub_array_teeth(position_selected(18), 28, 16) ) {
        array_push($teeth_region_eveluate, 'upper_jaw');
        return TRUE;
    }
    else {
        return FALSE;
    }
}

function is_upper_jaw_left_end($teeth) {
    /*"""
    Represent the end region (three last teeth) for the left side of the mouth.
    """*/
    global $teeth_region_eveluate;

    if(sub_array_teeth(position_selected(18), 16, 3) ) {
        array_push($teeth_region_eveluate, 'upper_jaw_left_end');
        return TRUE;
    }
    else {
        return FALSE;
    }
}

function is_upper_jaw_right_end($teeth) {
    /*"""
    Represent the end region (three last teeth) for the right side of the mouth.
    """*/
    global $teeth_region_eveluate;

    if(sub_array_teeth(position_selected(26), 28, 3) ) {
        array_push($teeth_region_eveluate, 'upper_jaw_right_end');
        return TRUE;
    }
    else {
        return FALSE;
    }
}

function is_upper_jaw_front($teeth) {
    /*"""
    Represent the front (anterior) region (6 front teeth) of the mouth.
    """*/
    global $teeth_region_eveluate;

    if(sub_array_teeth(position_selected(13), 23, 6) ) {
        array_push($teeth_region_eveluate, 'upper_jaw_front');
        return TRUE;
    }
    else {
        return FALSE;
    }
}

# Mandible
function is_mandible($teeth) {
    global $teeth_region_eveluate;

    if(sub_array_teeth(position_selected(38), 48, 16) ) {
        array_push($teeth_region_eveluate, 'mandible');
        return TRUE;
    }
    else {
        return FALSE;
    }
}

function is_mandible_left_end($teeth) {
    /*"""
    Represent the end region (three last teeth) for the left side of the mouth.
    """*/
    global $teeth_region_eveluate;

    if(sub_array_teeth(position_selected(46), 48, 3) ) {
        array_push($teeth_region_eveluate, 'mandible_left_end');
        return TRUE;
    }
    else {
        return FALSE;
    }
}

function is_mandible_right_end($teeth) {
    /*"""
    Represent the end region (three last teeth) for the right side of the mouth.
    """*/
    global $teeth_region_eveluate;

    if(sub_array_teeth(position_selected(38), 36, 3) ) {
        array_push($teeth_region_eveluate, 'mandible_right_end');
        return TRUE;
    }
    else {
        return FALSE;
    }
}

function is_mandible_front($teeth) {
    /*"""
    Represent the front (anterior) region (6 front teeth) of the mouth.
    """*/
    global $teeth_region_eveluate;

    if(sub_array_teeth(position_selected(33), 43, 6) ) {
        array_push($teeth_region_eveluate, 'mandible front');
        return TRUE;
    }
    else {
        return FALSE;
    }
}

function is_interdental_gap($schema) {
    global $teeth_inter_dental_gaps;
    $teeth_inter_dental_gap_right = [];
    $teeth_inter_dental_gap_left = [];

    foreach ($schema as $tooth_number => $status) {
        if(to_be_treated($status)) {
            
            if(right($tooth_number, $schema) AND to_be_replaced(right($tooth_number, $schema)['status'], $tooth_number, $schema)) {
                array_push($teeth_inter_dental_gap_right, $tooth_number);
            }

            if(left($tooth_number, $schema) AND to_be_replaced(left($tooth_number, $schema)['status'], $tooth_number, $schema)) {
                array_push($teeth_inter_dental_gap_left, $tooth_number);
            }
        }
    }

    $teeth_inter_dental_gaps = array_combine($teeth_inter_dental_gap_right, $teeth_inter_dental_gap_left);

    if(count($teeth_inter_dental_gaps) > 0)
        return TRUE;
    else
        return FALSE;
}

function biggest_interdental_gap($teeth_inter_dental_gaps) {
    $biggest_interdental_gap = [];
    foreach($teeth_inter_dental_gaps as $start => $end) {
        $biggest = abs(intval(position_schema($start)) - intval(position_schema($end))) - 1;
        array_push($biggest_interdental_gap, $biggest);
    }

    return max($biggest_interdental_gap);
}

function position_schema($tooth_number) {
    $postition = array_search($tooth_number, $GLOBALS['TOOTH_NUMBERS_ISO'] );
    if(! $postition)
        $postition = 0;
    return $postition; //$TOOTH_NUMBERS_ISO.index($tooth_number);
}

function position_selected($tooth_number) {
    $postition = array_search($tooth_number, $GLOBALS['teeth_with_status'] );
    if(! $postition)
        $postition = 0;
    return $postition;
}

function get_condition($tooth, $schema) {
    return $schema[$tooth];
}

function get_jaw($tooth) {
    global $TOOTH_NUMBERS_ISO;
    $TOOTH_NUMBERS_ISO_UPPER = array_slice($TOOTH_NUMBERS_ISO,0,16);
    $TOOTH_NUMBERS_ISO_LOWER = array_slice($TOOTH_NUMBERS_ISO,16,32);

    if(in_array($tooth, $TOOTH_NUMBERS_ISO_UPPER)) {
        return 'upper_jaw';
    }

    if(in_array($tooth, $TOOTH_NUMBERS_ISO_LOWER)) {
        return 'lower_jaw';
    }
}




function right($tooth_number, $schema) {
    /*"""
    Return the tooth that is just in the right of this object
    """*/

    $position_schema = position_schema($tooth_number);

    if($position_schema == 31) {
        return false;
    }
    else {
        $position_schema = $position_schema + 1;

        $tooth = $GLOBALS['TOOTH_NUMBERS_ISO'][$position_schema];
        $status = $schema[$tooth];

        return ["status" => $status, "tooth" => $tooth];
    }
}

function left($tooth_number, $schema) {
    /*"""
    Return the tooth that is just in the left of this object
    """*/
    $position_schema = position_schema($tooth_number);

    if($position_schema == 0) {
        return false;
    }
    else {
        $position_schema = $position_schema - 1;

        $tooth = $GLOBALS['TOOTH_NUMBERS_ISO'][$position_schema];
        $status = $schema[$tooth];

        // return $status;
        return ["status" => $status, "tooth" => $tooth];
    }
}


function to_be_replaced_count($start, $end, $schema) {
    /*"""
    Count how many TBR teeth are in some specific region.
    """*/
    $to_be_replaced_count = [];

    // if ($region == NULL)
    // {
    //     $region = new Region();
    //     $region = $region->whole_mouth();
    // } 0 , 2

    if($start < $end)
    {
        foreach($schema as $key => $value) {
            if(to_be_replaced($value) AND position_schema($key)<= $end) {
                array_push($to_be_replaced_count, $value);
            }
        }
    }

    return count($to_be_replaced_count);
}

function to_be_replaced($condition, $tooth_number, $schema) {
    /*"""
    To Be Replaced. This can be:
        a) f
        b) x
        c) b + neighboring finding (ww/kw/tw/pw/rw/x)
        d) ew
        e) sw
        f) bw
    """*/
    if(!$condition) {
        $condition = '';
    }
    if ( in_array($condition, ["f", "x", "ew", "sw", "fi", "bw"])) {
        return True;
    }

    if ($condition == "b") {
        // If the teeth near to this one is a TBR
        if (to_be_treated(left($tooth_number, $schema)['status']) OR
            to_be_treated(right($tooth_number, $schema)['status'])
        ) {
            return True;
        }

        // If the teeth near to this one is an "x"
        if (left($tooth_number, $schema)['status'] == "x" OR
            right($tooth_number, $schema)['status'] == "x") {
            return True;
        }

        // If the teeth near to this one is also a "b" (bridge)
        // and the last "b" is at the side of a TBT
        // Check from the left side
        // $left = $this->left;

        // while ($left) {
        //     if ($left->condition == "b") {
        //         $left = $left->left;
        //     }
        //     else if ($left->to_be_treated) {
        //         return True;
        //     }
        //     else {
        //         break;
        //     }
        // }

        // // Check from the right side
        // $right = $this->right;

        // while ($right) {
        //     if ($right->condition == "b")
        //         $right = $right->right;
        //     else if ($right->to_be_treated)
        //         return True;
        //     else {
        //         break;
        //     }
        // }
    }

    return False;
}

function to_be_treated($condition) {
    /*"""
    To Be Treated are existing tooth with or without findings:
        ww/kw/tw/pw/rw.
    """*/
    if (in_array($condition, ["ww", "kw", "tw", "pw", "rw", "ur"])) {
        return True;
    }
    return False;
}


function Exact16ToBeReplacedTeeth_upper($schema) {
    global $teeth_subsidy_eveluate, $teeth_region_eveluate, $teeth_with_status;
    $to_be_replaced = [];

    if (in_array('upper_jaw', $teeth_region_eveluate) ) {
        $i =0;
        foreach($schema as $key => $value) {
            if(to_be_replaced($value, $key, $schema) AND $i<16) {
                array_push($to_be_replaced, $value);
            }
            $i++;
        }
    }
    
    if (count($to_be_replaced) == 16) {
        array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "4.2", "region"=> "upper_jaw", "applied_rule"=> "Exact16ToBeReplacedTeeth_upper"]
        );

        return True;
    }
}


function Exact16ToBeReplacedTeeth_mandible($schema) {
    global $teeth_subsidy_eveluate, $teeth_region_eveluate, $teeth_with_status;
    $to_be_replaced = [];

    if (in_array('mandible', $teeth_region_eveluate) ) {
        $i = 16;
        foreach($schema as $key => $value) {
            if(to_be_replaced($value, $key, $schema) AND position_schema($key)<32) {
                array_push($to_be_replaced, $value);
            }
            $i++;
        }
    }
    
    if (count($to_be_replaced) == 16) {
        array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "4.4", "region"=> "mandible", "applied_rule"=> "Exact16ToBeReplacedTeeth_mandible"]
        );

        return True;
    }
}


function Between13And15ToBeReplacedTeeth_upper($schema) {
    global $teeth_subsidy_eveluate, $teeth_region_eveluate, $teeth_with_status;
    $to_be_replaced = [];

    // if (in_array('upper_jaw', $teeth_region_eveluate) ) {
        foreach($schema as $key => $value) {
            if(to_be_replaced($value, $key, $schema) AND position_schema($key)<16) {
                array_push($to_be_replaced, $value);
            }
        }
    // }
    
    if (13 <= count($to_be_replaced) and count($to_be_replaced) <= 15) {
        array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "4.1", "region"=> "upper_jaw", "applied_rule"=> "Between13And15ToBeReplacedTeeth_upper"]
        );

        return True;
    }
}


function Between13And15ToBeReplacedTeeth_mandible($schema) {
    global $teeth_subsidy_eveluate, $teeth_region_eveluate, $teeth_with_status;
    $to_be_replaced = [];

    // if (in_array('mandible', $teeth_region_eveluate) ) {
        foreach($schema as $key => $value) {
            if(to_be_replaced($value, $key, $schema) AND position_schema($key)>15) {
                array_push($to_be_replaced, $value);
            }
        }
    // }
    
    if (13 <= count($to_be_replaced) and count($to_be_replaced) <= 15) {
        array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "4.3", "region"=> "mandible", "applied_rule"=> "Between13And15ToBeReplacedTeeth_mandible"]
        );

        return True;
    }
}

/** 3.x starts **/
function UnilateralFreeEndToBeReplacedTeethAtLeast1_upper($schema) {
    global $teeth_subsidy_eveluate, $teeth_region_eveluate, $teeth_with_status;

    if (in_array('upper_jaw_left_end', $teeth_region_eveluate)) {
        array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "3.1", "region"=> "upper_jaw_left_end", "applied_rule"=> "UnilateralFreeEndToBeReplacedTeethAtLeast1_upper"]
        );
    }

    if (in_array('upper_jaw_right_end', $teeth_region_eveluate)
    ) {
        array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "3.1", "region"=> "upper_jaw_right_end", "applied_rule"=> "UnilateralFreeEndToBeReplacedTeethAtLeast1_upper"]
        );
    }
}


function UnilateralFreeEndToBeReplacedTeethAtLeast1_mandible($schema) {
    global $teeth_subsidy_eveluate, $teeth_region_eveluate, $teeth_with_status;

    if (in_array('mandible_left_end', $teeth_region_eveluate)) {
        array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "3.1", "region"=> "mandible_left_end", "applied_rule"=> "UnilateralFreeEndToBeReplacedTeethAtLeast1_mandible"]
        );
    }

    if (in_array('mandible_right_end', $teeth_region_eveluate)
    ) {
        array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "3.1", "region"=> "mandible_right_end", "applied_rule"=> "UnilateralFreeEndToBeReplacedTeethAtLeast1_mandible"]
        );
    }
}

function Between5And12ToBeReplacedTeeth_upper($schema) {
    global $teeth_subsidy_eveluate, $teeth_region_eveluate, $teeth_with_status;
    $to_be_replaced = [];

    // $upper_jaw_without_x8 = new Region(17, 27);

    // if (in_array('upper_jaw', $teeth_region_eveluate) ) {
        foreach($schema as $key => $value) {
            
            if(to_be_replaced($value, $key, $schema) AND position_schema($key)>0 AND position_schema($key)<15) {
                array_push($to_be_replaced, $value);
            }
        }
    // }
    
    if (5 <= count($to_be_replaced) and count($to_be_replaced) <= 12) {
        array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "3.1", "region"=> "upper_jaw_without_x8", "applied_rule"=> "Between5And12ToBeReplacedTeeth_upper"]
        );

        return True;
    }
}

function Between5And12ToBeReplacedTeeth_mandible($schema) {
    global $teeth_subsidy_eveluate, $teeth_region_eveluate, $teeth_with_status;
    $to_be_replaced = [];

    // $mandible_without_x8 = new Region(37, 47);

    // if (in_array('upper_jaw', $teeth_region_eveluate) ) {
        foreach($schema as $key => $value) {
            
            if(to_be_replaced($value, $key, $schema) AND position_schema($key)>16 AND position_schema($key)<31) {
                array_push($to_be_replaced, $value);
            }
        }
    // }
    
    if (5 <= count($to_be_replaced) and count($to_be_replaced) <= 12) {
        array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "3.1", "region"=> "mandible_without_x8", "applied_rule"=> "Between5And12ToBeReplacedTeeth_upper"]
        );

        return True;
    }
}


function ExactToBeReplacedTeethInterdentalGapInFronRegion_upper($schema) {
    /**"""
    if upper jaw && interdental gap TBR=1// all in region 12-22.

    It is only fired when no other 3.X rule was fired before it.

    Notice that the 2.1 / 2.2 is only possible if 13 != PT -
    in other words: if we have a 3.2 situation (and this is needed for
    2.1+2.2 to happen) with the 13 or 23 being the first good tooth
    (would be a potential telescope = PT) it will NOT be a 2.1 case,
    as the 13/23 can only be a T (as part of the free end)
    or an AT for the gap.

    In Issue #133 (21/05/2019) we also added a new rule: X2 should be
    existing on both sides to this rule to be fired.
    """**/

    $to_be_replaced_count = [];
    $subsidy = NULL;

    // 2.1/2.2: if upper jaw && X6-X8 on one side and at
    // least X7-X8 as minimum covered on the other side

    // At least one side should have TBR=3
    if (! to_be_replaced_count(position_selected(18), position_selected(16), $schema) == 3 AND
        ! to_be_replaced_count(position_selected(26), position_selected(28), $schema) == 3
    ) {
        return False;
    }

    // If 18-16 is the one with TBR=3, the other side should have
    // minimum X7-X8
    if (to_be_replaced_count(position_selected(18), position_selected(16), $schema) == 3 AND
        ! to_be_replaced_count(position_selected(27), position_selected(28), $schema) == 2
    ) {
        return False;
    }

    // If 26-28 is the one with TBR=3, the other side should have
    // minimum X7-X8
    if (
        to_be_replaced_count(position_selected(26), position_selected(28), $schema) == 3 AND
        ! to_be_replaced_count(position_selected(18), position_selected(17), $schema) == 2
    ) {
        return False;
    }

    // As defined in Issue #133, missing tooth must be inside 12-22
    // area, that's the reason by us starting from 13-23 here.
    $interdental_gap = $this->schema.interdental_gap(
        $region=new Region(13, 23, $this->schema)
    );

    if (! $interdental_gap) {
        return False;
    }

    // Issue #133: we have found on this issue that we would not consider a
    // this rule as valid if we have two gaps in front region at the same
    // time. The only possibility for this case is a gap in 13-11 and another
    // in 11-22
    if (
        count(
            $this->schema.interdental_gaps($region=new Region(13, 23, $this->schema))
        )!= 1) {
        return False;
    }

    // X2 should be
    // existing on both sides to this rule to be fired. X2 can only be
    // missing when it is part of the interdental gap found by the rule.
    $tooth_12 = new Region(12, 12, $this->schema);
    $tooth_22 = new Region(22, 22, $this->schema);

    if (($tooth_12->to_be_replaced and ! in_array($tooth_12, $interdental_gap)) or (
        $tooth_22->to_be_replaced and ! in_array($tooth_22, $interdental_gap))
    ) {
        return False;
    }

    // As defined in Issue #133, a tooth in interdental gap can not be a
    // Telescope if it is the first TBT on a 3.1 case
    $teeth_32 = $this->identified_subsidies.teeth($subsidy_code="3.2");

    if (
        in_array($interdental_gap.a_tooth, $teeth_32) 
        or in_array($interdental_gap.b_tooth, $teeth_32)
    ) {
        return False;
    }

    if ($interdental_gap->to_be_replaced_count == $this->to_be_replaced_count) {
        array_push($this->identified_subsidies->list,
            [
                "subsidy"=> $this->subsidy,
                "region"=> $interdental_gap.teeth_marking_abutment_tooth,
                "applied_rule"=> self,
            ]
        );
        return True;
    }

    
}

function ExactToBeReplacedTeethInterdentalGapInFronRegion_mandible() {
    return False;
}


function Exact2ToBeReplacedTeethInterdentalGapInFrontRegion() {
    ExactToBeReplacedTeethInterdentalGapInFronRegion();
    $to_be_replaced_count = 2;
    $subsidy = "2.2";
    return ["to_be_replaced_count" => $to_be_replaced_count, "subsidy" => $subsidy];
}

function BilateralFreeEnd_upper($schema, $teeth_with_status) {
    global $teeth_subsidy_eveluate, $teeth_region_eveluate, $teeth_with_status;

    // if (in_array('upper_jaw_left_end', $teeth_region_eveluate) OR
    //     in_array('upper_jaw_right_end', $teeth_region_eveluate)
    // ) {
    //     array_push($teeth_subsidy_eveluate, 
    //             ["subsidy"=> "3.1", "region"=> "upper_jaw_end", "applied_rule"=> "BilateralFreeEnd_upper"]
    //     );
    // }

    // if (in_array('upper_jaw_right_end', $teeth_region_eveluate)
    // ) {
    //     array_push($teeth_subsidy_eveluate, 
    //             ["subsidy"=> "3.1", "region"=> "upper_jaw_right_end", "applied_rule"=> "BilateralFreeEnd_upper"]
    //     );
    // }

    $tooth_32_left = $teeth_with_status[0];
    $tooth_32_right = end($teeth_with_status);

    if ($tooth_32_left == 25 or $tooth_32_right == 15) {
        return True;
    }

    // if (to_be_replaced($tooth_32_left) or $tooth_32_right->to_be_replaced) {
    //     return True;
    // }

    // array_push($teeth_subsidy_eveluate, 
    //         ["subsidy"=> "3.2", "region"=> "upper_jaw".$tooth_32_left.' '.$tooth_32_right, "applied_rule"=> "BilateralFreeEnd_upper"]
    // );
}

function BilateralFreeEnd_mandible($schema, $teeth_with_status) {
    global $teeth_subsidy_eveluate, $teeth_region_eveluate, $teeth_with_status;

    // if (in_array('mandible_left_end', $teeth_region_eveluate) OR
    //     in_array('mandible_left_end', $teeth_region_eveluate)
    // ) {
    //     array_push($teeth_subsidy_eveluate, 
    //             ["subsidy"=> "3.1", "region"=> "upper_jaw_end", "applied_rule"=> "BilateralFreeEnd_upper"]
    //     );
    // }

    // if (in_array('mandible_left_end', $teeth_region_eveluate)
    // ) {
    //     array_push($teeth_subsidy_eveluate, 
    //             ["subsidy"=> "3.1", "region"=> "mandible_left_end", "applied_rule"=> "BilateralFreeEnd_upper"]
    //     );
    // }

    $tooth_32_left = $teeth_with_status[0];
    $tooth_32_right = end($teeth_with_status);

    if ($tooth_32_left == 45 or $tooth_32_right == 35) {
        return True;
    }

    // if (to_be_replaced($tooth_32_left) or $tooth_32_right->to_be_replaced) {
    //     return True;
    // }

    // array_push($teeth_subsidy_eveluate, 
    //     ["subsidy"=> "3.2", "region"=> "upper_jaw".$tooth_32_left.' '.$tooth_32_right, "applied_rule"=> "BilateralFreeEnd_upper"]
    // );
}

/** 3.x end **/

function Exact2_3ToBeReplacedTeethInterdentalGapInFrontRegion($schema) {
    global $teeth_subsidy_eveluate;
    $to_be_replaced_count = [];

    foreach($schema as $key => $value) {
        
        if(to_be_replaced($value, $key, $schema) AND right($key, $schema) AND to_be_replaced(right($key, $schema)['status'], $key, $schema)) {
            array_push($to_be_replaced_count, $key);
        }
    }
    

    if(count($to_be_replaced_count) == 2) {
        array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "2.3", "region"=> "biggest_interdental_gap", "applied_rule"=> "BiggestInterdentalGapExactToBeReplaced3"]
        );
    }

    if(count($to_be_replaced_count) == 1) {
        array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "2.2", "region"=> "biggest_interdental_gap", "applied_rule"=> "BiggestInterdentalGapExactToBeReplaced2"]
        );
    }
}

function Exact1ToBeReplacedTeethInterdentalGapInFrontRegion($schema) {
    global $teeth_subsidy_eveluate;
    $to_be_replaced_count = [];

    foreach($schema as $key => $value) {
        
        if(to_be_replaced($value, $key, $schema) AND right($key, $schema)['status'] == '' AND left($key, $schema)['status'] == '' ) {
            array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "2.1", "region"=> "biggest_interdental_gap", "applied_rule"=> "BiggestInterdentalGapExactToBeReplaced1"]
            );
        }
    }
}

/** 1.x start **/
function StatusPwInPosteriorRegion($schema) {
    global $teeth_subsidy_eveluate;

    // pw in 18-14, 24-28 (posterior region)

    $StatusPwInPosteriorRegion = [];

    foreach($schema as $teeth => $status) {
        if ($teeth == 18 OR $teeth == 17 OR $teeth == 16 OR $teeth == 15 OR $teeth == 14 OR
            $teeth == 24 OR $teeth == 25 OR $teeth == 26 OR $teeth == 27 OR $teeth == 28 OR
            $teeth == 38 OR $teeth == 37 OR $teeth == 36 OR $teeth == 35 OR $teeth == 34 OR
            $teeth == 44 OR $teeth == 45 OR $teeth == 46 OR $teeth == 47 OR $teeth == 48 
        ) {
            array_push($StatusPwInPosteriorRegion, get_condition($teeth, $schema));
        }
    }

    if (in_array('pw', $StatusPwInPosteriorRegion)) {
        array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "1.2", "region"=> "Posterior Region", "applied_rule"=> "StatusPwInPosteriorRegion"]
        );
    }
}

function StatusPwInFrontRegion($schema) {
    global $teeth_subsidy_eveluate;

    // pw in Region(13, 23, $this->schema), Region(33, 43, $this->schema) (posterior region)

    $StatusPwInFrontRegion = [];

    foreach($schema as $teeth => $status) {
        if ($teeth == 13 OR $teeth == 12 OR $teeth == 11 OR $teeth == 21 OR $teeth == 22 OR $teeth == 23 OR
            $teeth == 33 OR $teeth == 32 OR $teeth == 31 OR $teeth == 41 OR $teeth == 42 OR $teeth == 43 
        ) {
            array_push($StatusPwInFrontRegion, get_condition($teeth, $schema));
        }
    }

    if (in_array('pw', $StatusPwInFrontRegion)) {
        array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "1.1", "region"=> "Front Region", "applied_rule"=> "StatusPwInFrontRegion"]
        );
    }
}

function ToBeTreatedWithNoAbutmentTeethIncluded($schema) {
    global $teeth_subsidy_eveluate, $teeth_inter_dental_gaps;

    $teeth_included = [];

    foreach($teeth_inter_dental_gaps as $start => $end) {
        array_push($teeth_included, intval($start));
        array_push($teeth_included, intval($end));
    }

    foreach($schema as $tooth => $value) {
        if(to_be_treated($value) AND 
            (! to_be_replaced(right($tooth, $schema)['status'], $tooth, $schema) OR
            ! to_be_replaced(left($tooth, $schema)['status'], $tooth, $schema)
            )
        ) {
            $get_jaw = get_jaw($tooth);
            array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "1.1", "region"=> $get_jaw, "applied_rule"=> "ToBeTreatedWithNoAbutmentTeethIncluded"]
            );
        }
    }
}
/** 1.x end **/

/** 2.x start **/
function BiggestInterdentalGapInFrontRegionExactToBeReplaced4($schema) {
    global $teeth_inter_dental_gaps, $teeth_subsidy_eveluate, $teeth_region_eveluate;
    
    if(! is_interdental_gap($schema)) {
        return FALSE;
    }

    if(biggest_interdental_gap($teeth_inter_dental_gaps) == 4
    ) {
        array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "2.4", "region"=> "upper_jaw_front", "applied_rule"=> "BiggestInterdentalGapInFrontRegionExactToBeReplaced4"]
        );
    }
}


function BiggestInterdentalGapExactToBeReplaced3($schema) {
    global $teeth_inter_dental_gaps, $teeth_subsidy_eveluate, $teeth_region_eveluate;
    
    if(! is_interdental_gap($schema)) {
        return FALSE;
    }

    if(biggest_interdental_gap($teeth_inter_dental_gaps) == 3) {
        array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "2.3", "region"=> "biggest_interdental_gap", "applied_rule"=> "BiggestInterdentalGapExactToBeReplaced3"]
        );
    }
}


function BiggestInterdentalGapExactToBeReplaced2($schema) {
    global $teeth_inter_dental_gaps, $teeth_subsidy_eveluate, $teeth_region_eveluate;
    
    if(! is_interdental_gap($schema)) {
        return FALSE;
    }

    if(biggest_interdental_gap($teeth_inter_dental_gaps) == 2) {
        array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "2.2", "region"=> "biggest_interdental_gap", "applied_rule"=> "BiggestInterdentalGapExactToBeReplaced2"]
        );
    }
}

function BiggestInterdentalGapExactToBeReplaced1($schema) {
    global $teeth_inter_dental_gaps, $teeth_subsidy_eveluate, $teeth_region_eveluate;
    
    if(! is_interdental_gap($schema)) {
        return FALSE;
    }

    if(biggest_interdental_gap($teeth_inter_dental_gaps) == 1) {
        array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "2.1", "region"=> "biggest_interdental_gap", "applied_rule"=> "BiggestInterdentalGapExactToBeReplaced1"]
        );
    }
}


?>