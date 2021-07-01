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
$is_gap_closure = 'N';
$is_gap_closure_arr = [];


// print_r($front_end);
// main_input($front_end);
function main_input($schema) {

    global $teeth_with_status, $teeth_region_eveluate, $teeth_subsidy_eveluate, $is_gap_closure, $is_gap_closure_arr, $TOOTH_NUMBERS_ISO;

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

    //refine the schema for gap closure
    $schema_old = ($schema);
    $schema = gap_closure($schema);
    // $schema_old = ($schema);

    $chunks = array_chunk($schema, ceil(count($schema) / 2), true);

    for ($in=0; $in<count($chunks); $in++)
    {
        $schema = $chunks[$in];

        // Check for teeth nos with a status
        foreach ($schema as $tooth_number => $status) {
            // print_r($status);
            // if($status[1] !== '') {
            if($status !== '') {
                array_push($teeth_with_status, intval($tooth_number));
            }
            
        }

        is_whole_mouth($teeth_with_status);
        is_upper_jaw($teeth_with_status);
        is_upper_jaw_left_end($schema);
        is_upper_jaw_right_end($schema);
        is_upper_jaw_front($teeth_with_status);
        is_mandible($teeth_with_status);
        is_mandible_left_end($teeth_with_status);
        is_mandible_right_end($teeth_with_status);
        is_mandible_front($teeth_with_status);
        is_X7_X8($schema);
        atleastOneInPostRegion($schema);

        /** Execute the rules **/
        //4.x
        Exact16ToBeReplacedTeeth_upper($schema);
        Exact16ToBeReplacedTeeth_mandible($schema);
        Between13And15ToBeReplacedTeeth_upper($schema);
        Between13And15ToBeReplacedTeeth_mandible($schema);

        //3.x
        Between5And12ToBeReplacedTeeth_upper($schema);
        Between5And12ToBeReplacedTeeth_mandible($schema);
        UnilateralFreeEndToBeReplacedTeethAtLeast1_upper($schema);
        UnilateralFreeEndToBeReplacedTeethAtLeast1_mandible($schema);
        BilateralFreeEnd_upper($schema, $teeth_with_status);
        BilateralFreeEnd_mandible($schema, $teeth_with_status);
        special_ob_f($schema);
        special_case_b($schema);

        // 2.x
        BiggestInterdentalGapInFrontRegionExactToBeReplaced4($schema);
        BiggestInterdentalGapExactToBeReplaced3($schema);
        BiggestInterdentalGapExactToBeReplaced2($schema);
        BiggestInterdentalGapExactToBeReplaced1($schema);
        // Exact1ToBeReplacedTeethInterdentalGapInFrontRegion($schema); // moved to below 2_3
        Exact2_3ToBeReplacedTeethInterdentalGapInFrontRegion($schema);
        Exact1ToBeReplacedTeethInterdentalGapInFrontRegion($schema);

        //1.x
        StatusPwInPosteriorRegion($schema);
        StatusPwInFrontRegion($schema);
        ToBeTreatedWithNoAbutmentTeethIncluded($schema);
    }

    // remove dependencies or duplicates
    // sort array in output_order
    $remove_subsidies = [];

    // $output_order = array_column($teeth_subsidy_eveluate, 'output_order');
    // array_multisort($output_order, SORT_ASC, $teeth_subsidy_eveluate);

    for($len=0; $len<count($teeth_subsidy_eveluate); $len++) {
        // unset($teeth_subsidy_eveluate[$len]['applied_rule']);

        if( startswith($teeth_subsidy_eveluate[$len]['subsidy'], '4.1')
            OR startswith($teeth_subsidy_eveluate[$len]['subsidy'], '4.2')
            OR startswith($teeth_subsidy_eveluate[$len]['subsidy'], '4.3')
            OR startswith($teeth_subsidy_eveluate[$len]['subsidy'], '4.4')
        ) {
            for($lenn=0; $lenn<count($teeth_subsidy_eveluate); $lenn++) {
                if(startswith($teeth_subsidy_eveluate[$lenn]['subsidy'], '3')) {
                    array_push($remove_subsidies, $lenn);
                }
            }
        }
    }

    for($rem=0; $rem<count($remove_subsidies); $rem++) {
        unset($teeth_subsidy_eveluate[$remove_subsidies[$rem]]);
    }

    // var_dump($teeth_subsidy_eveluate);
    
    // $teeth_subsidy_eveluate = array_values(($teeth_subsidy_eveluate));

    if($is_gap_closure == 'Y') {
        $new_arr_gap = [];
        $teeth_with_status_old = [];

        foreach ($schema_old as $tooth_number => $status) {
            if($status !== '') {
                // array_push($teeth_with_status_old, intval($tooth_number));
            }
        }

        // var_dump($teeth_with_status_old);

        // if(get_condition(end($teeth_with_status_old), $schema_old) !== ')(' 
            ////AND count($is_gap_closure_arr) !== 1
        // )
        {
            foreach($teeth_subsidy_eveluate as $arr_gap) {
                // echo $arr_gap['region'];
                // var_dump($is_gap_closure_arr);

                $temp = explode('-' ,$arr_gap['region']);
                $iter = 0;

                if(count($temp) > 1) {
                    for($z=position_schema($temp[0]); $z<=position_schema(end($temp)); $z++ ) {
                        array_push($new_arr_gap, $TOOTH_NUMBERS_ISO[$z]);
                    }
                }

                $new_arr_gap = array_unique($new_arr_gap);
                
                // sort the positions
                $sort_temp_arr = [];
                for($gap_arr=0; $gap_arr<count($new_arr_gap); $gap_arr++) {
                    array_push($sort_temp_arr, position_schema($new_arr_gap[$gap_arr]));
                }
                sort($sort_temp_arr);

                $sort_temp_arr_2 = [];
                for($gap_arr=0; $gap_arr<count($sort_temp_arr); $gap_arr++) {
                    array_push($sort_temp_arr_2, $TOOTH_NUMBERS_ISO[$sort_temp_arr[$gap_arr]]);
                }
                $new_arr_gap = $sort_temp_arr_2;
                
                for($gap=0; $gap<count($is_gap_closure_arr); $gap++) {

                    if( ($is_gap_closure_arr[$gap] == '15' OR $is_gap_closure_arr[$gap] == '25' 
                            OR $is_gap_closure_arr[$gap] == '34' OR $is_gap_closure_arr[$gap] == '44')
                        // AND to_be_treated(get_condition($is_gap_closure_arr[$gap]+1, $schema_old))
                        AND subsidy_exists(1)
                    ) {
                        array_push($teeth_subsidy_eveluate, 
                            ["subsidy"=> "1.3", "output_order"=> "17", "region"=> $is_gap_closure_arr[$gap], "quantity"=> "1", "applied_rule"=> "veneering_grants 12 edit"]
                        );
                    }

                    if( ($is_gap_closure_arr[$gap] == '15' OR $is_gap_closure_arr[$gap] == '25' 
                            OR $is_gap_closure_arr[$gap] == '34' OR $is_gap_closure_arr[$gap] == '44'
                            OR in_array('15', $new_arr_gap)  )
                        // AND to_be_replaced(get_condition($is_gap_closure_arr[$gap]+1, $schema_old), $is_gap_closure_arr[$gap], $schema_old)
                        AND subsidy_exists(2)
                    ) {
                        array_push($teeth_subsidy_eveluate, 
                            //["subsidy"=> "2.7", "output_order"=> "15", "region"=> $is_gap_closure_arr[$gap], "quantity"=> "1", "applied_rule"=> "veneering_grants 2.7 edit"]
                            ["subsidy"=> "2.7", "output_order"=> "15", "region"=> '16', "quantity"=> "1", "applied_rule"=> "veneering_grants 2.7 edit"]
                        );
                    }
                    

                    if(array_key_exists($is_gap_closure_arr[$gap], $schema_old) 
                        // AND in_array($is_gap_closure_arr[$gap], $new_arr_gap) 
                        AND to_be_replaced(get_condition($TOOTH_NUMBERS_ISO[position_schema($is_gap_closure_arr[$gap])+1], $schema_old), $is_gap_closure_arr[$gap], $schema_old)
                    ) {

                        for($iter=0; $iter<count($teeth_subsidy_eveluate); $iter++) {
                            if(strpos($teeth_subsidy_eveluate[$iter]['region'], '-')
                            ) {
                                // $teeth_subsidy_eveluate[$iter]['region'] = $TOOTH_NUMBERS_ISO[position_schema($new_arr_gap[0])].'-'.$TOOTH_NUMBERS_ISO[position_schema(end($new_arr_gap))+1];
                                $teeth_subsidy_eveluate[$iter]['region'] = $TOOTH_NUMBERS_ISO[position_schema($new_arr_gap[0])].'-'.$TOOTH_NUMBERS_ISO[position_schema(end($new_arr_gap))];
                            }
                            else {
                                if($teeth_subsidy_eveluate[$iter]['region'] == $is_gap_closure_arr[$gap]) 
                                {
                                    // $teeth_subsidy_eveluate[$iter]['region'] = $TOOTH_NUMBERS_ISO[position_schema($new_arr_gap[0])].','.$TOOTH_NUMBERS_ISO[position_schema(end($new_arr_gap))+1];
                                    $teeth_subsidy_eveluate[$iter]['region'] = $TOOTH_NUMBERS_ISO[position_schema($new_arr_gap[0])].','.$TOOTH_NUMBERS_ISO[position_schema(end($new_arr_gap))-1];
                                }
                            }
                        }
                    }

                    if(get_condition($TOOTH_NUMBERS_ISO[position_schema($is_gap_closure_arr[$gap])+1], $schema_old) == '' ) {
                        for($iter=0; $iter<count($teeth_subsidy_eveluate); $iter++) {

                            if(strpos($teeth_subsidy_eveluate[$iter]['region'], '-')
                                // AND get_condition($TOOTH_NUMBERS_ISO[position_schema(end($teeth_with_status_old))], $schema_old) !== ')('
                            ) {
                                //no changes if )( is in last not before any status
                                $is_greater = 0;
                                for($bigger=0; $bigger<count($is_gap_closure_arr); $bigger++) {
                                    // TO fix. another loop required for $teeth_with_status[$zz]
                                    if(position_schema($teeth_with_status[0]) > position_schema($is_gap_closure_arr[$bigger]))
                                    {
                                        $is_greater += 1;
                                    }
                                }

                                if($is_greater > 0
                                    AND count($is_gap_closure_arr) !== 1
                                ) {
                                    // $teeth_subsidy_eveluate[$iter]['region'] = $TOOTH_NUMBERS_ISO[position_schema($new_arr_gap[0])+$is_greater].'-'.$TOOTH_NUMBERS_ISO[position_schema(end($new_arr_gap))+1+$is_greater];
                                    $teeth_subsidy_eveluate[$iter]['region'] = $TOOTH_NUMBERS_ISO[position_schema($new_arr_gap[0])+$is_greater].'-'.$TOOTH_NUMBERS_ISO[position_schema(end($new_arr_gap))+$is_greater];

                                }

                                if($is_greater > 0
                                    AND count($is_gap_closure_arr) == 1
                                ) {
                                    // $teeth_subsidy_eveluate[$iter]['region'] = $TOOTH_NUMBERS_ISO[position_schema($new_arr_gap[0])+$is_greater].'-'.$TOOTH_NUMBERS_ISO[position_schema(end($new_arr_gap))+$is_greater];
                                    $teeth_subsidy_eveluate[$iter]['region'] = $TOOTH_NUMBERS_ISO[position_schema($new_arr_gap[0])+$is_greater].'-'.$TOOTH_NUMBERS_ISO[position_schema(end($new_arr_gap))];
                                }
                            }
                            else {
                                // if($teeth_subsidy_eveluate[$iter]['region'] !== $is_gap_closure_arr[$gap])
                                if(is_numeric($teeth_subsidy_eveluate[$iter]['region']) AND
                                    to_be_treated(get_condition($teeth_subsidy_eveluate[$iter]['region']+1, $schema_old))
                                ) {
                                    $teeth_subsidy_eveluate[$iter]['region'] = $TOOTH_NUMBERS_ISO[position_schema(end($new_arr_gap))+1];
                                }
                                else {
                                    // $teeth_subsidy_eveluate[$iter]['region'] .= ','. $TOOTH_NUMBERS_ISO[position_schema(end($new_arr_gap))+1];
                                    $teeth_subsidy_eveluate[$iter]['region'] .= ','. $TOOTH_NUMBERS_ISO[position_schema(end($new_arr_gap))+count($is_gap_closure_arr)-1];
                                }
                            }
                        }
                    }
                }
                
                $temp_arr_2 =[];
                for($iter=0; $iter<count($teeth_subsidy_eveluate); $iter++) {
                    if(! strpos($teeth_subsidy_eveluate[$iter]['region'], '-') )
                    {
                        $teeth_subsidy_eveluate[$iter]['region'] = array_values(array_unique(explode(',' ,$teeth_subsidy_eveluate[$iter]['region'])));

                        for($z=0; $z<count($teeth_subsidy_eveluate[$iter]['region']); $z++) {
                            if(!in_array($teeth_subsidy_eveluate[$iter]['region'][$z], $is_gap_closure_arr) 
                            ) {
                                array_push($temp_arr_2, $teeth_subsidy_eveluate[$iter]['region'][$z]);
                            }
                        }

                        $temp_arr_2 = array_unique($temp_arr_2);

                        $teeth_subsidy_eveluate[$iter]['region'] = implode(',', $temp_arr_2);
                    }
                }
            }
        }
    }


    $final = [];

    foreach($teeth_subsidy_eveluate as $arr) {
        
        $final[$arr['subsidy']]['subsidy'] = $arr['subsidy'];
        
        $final[$arr['subsidy']]['region'] = (isset($final[$arr['subsidy']]['region']) AND ($final[$arr['subsidy']]['region'] !== $arr['region']) ) ? $final[$arr['subsidy']]['region'].','. $arr['region'] : $arr['region'];
        
        $final[$arr['subsidy']]['quantity'] = (isset($final[$arr['subsidy']]['quantity']) AND ($final[$arr['subsidy']]['region'] !== $arr['region'])) ?  $final[$arr['subsidy']]['quantity']+ $arr['quantity'] : $arr['quantity'];
    }

    // veneering grant calculation fixes
    foreach($final as $arr_final => $values) {
        $region_order_arr = [];

        if( ($arr_final == '1.3'
            OR $arr_final == '2.7'
            OR $arr_final == '4.7')
            // AND veneering_grants($values['region'])
        ) {
            $final[$arr_final]['region'] = array_values(array_unique(explode(',', $final[$arr_final]['region'])));
            
            // Display the output in order start
            for($az=0; $az<count($final[$arr_final]['region']); $az++)
            {
                array_push($region_order_arr, position_schema($final[$arr_final]['region'][$az]));
            }
            sort($region_order_arr);
            for($ax=0; $ax<count($region_order_arr); $ax++)
            {
                $region_order_arr[$ax] = $TOOTH_NUMBERS_ISO[$region_order_arr[$ax]];
            }

            $final[$arr_final]['region'] = $region_order_arr;
            // Display the output in order end

            $final[$arr_final]['quantity'] = count($final[$arr_final]['region']);
            $final[$arr_final]['region'] = implode(",", $final[$arr_final]['region']);
        }
    }


    echo json_encode(array_values($final));

    // echo json_encode($teeth_subsidy_eveluate);
}

function startswith ($string, $startString) { 
    $len = strlen($startString);
    return (substr($string, 0, $len) == $startString);
}

function sub_array_teeth($start, $end, $size) {
    global $TOOTH_NUMBERS_ISO;

    // var_dump($start);
    // var_dump($end);
    // var_dump($size);
    // $teeth_region = array_slice($GLOBALS['TOOTH_NUMBERS_ISO'], $start, $size, TRUE);
    // $teeth_region = array_slice($GLOBALS['TOOTH_NUMBERS_ISO'], $start, $size);
    $teeth_region = array_slice($GLOBALS['teeth_with_status'], $start, $size);
    
    // echo nl2br("\n");

    if (count($teeth_region) == $size AND end($teeth_region) == $end AND $teeth_region[0] == $TOOTH_NUMBERS_ISO[$start])
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

function is_upper_jaw_left_end($schema) {
    /*"""
    Represent the end region (three last teeth) for the left side of the mouth.
    """*/
    global $teeth_region_eveluate;

    // array_key_exists(18, $schema) AND 

    if(sub_array_teeth(position_schema(18), 16, 3)
    ) {
        if(array_key_exists(18, $schema) AND to_be_replaced(get_condition(18, $schema), 18, $schema)
            AND array_key_exists(17, $schema) AND to_be_replaced(get_condition(17, $schema), 17, $schema)
            AND array_key_exists(16, $schema) AND to_be_replaced(get_condition(16, $schema), 16, $schema)
        ) {
            array_push($teeth_region_eveluate, 'upper_jaw_left_end');
            return TRUE;
        }
        
    }
    else {
        return FALSE;
    }
}

function is_upper_jaw_right_end($schema) {
    /*"""
    Represent the end region (three last teeth) for the right side of the mouth.
    """*/
    global $teeth_region_eveluate;

    if(sub_array_teeth(position_schema(26), 28, 3) ) {
        if(array_key_exists(28, $schema) AND to_be_replaced(get_condition(28, $schema), 28, $schema)
            AND array_key_exists(27, $schema) AND to_be_replaced(get_condition(27, $schema), 27, $schema)
            AND array_key_exists(26, $schema) AND to_be_replaced(get_condition(26, $schema), 26, $schema)
        ) {
            array_push($teeth_region_eveluate, 'upper_jaw_right_end');

            return TRUE;
        }
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

function is_X7_X8($schema) {
    /*"""
    Represent the front (anterior) region (6 front teeth) of the mouth.
    """*/
    global $teeth_region_eveluate;

    if(sub_array_teeth(position_selected(18), 17, 2) OR
        sub_array_teeth(position_selected(27), 28, 2) OR
        sub_array_teeth(position_selected(38), 37, 2) OR
        sub_array_teeth(position_selected(47), 48, 2)
    ) {
        if( (array_key_exists(18, $schema) AND to_be_replaced(get_condition(18, $schema), 18, $schema)
            AND array_key_exists(17, $schema) AND to_be_replaced(get_condition(17, $schema), 17, $schema) )

            OR (array_key_exists(28, $schema) AND to_be_replaced(get_condition(28, $schema), 28, $schema)
            AND array_key_exists(27, $schema) AND to_be_replaced(get_condition(27, $schema), 27, $schema))

            OR (array_key_exists(38, $schema) AND to_be_replaced(get_condition(38, $schema), 38, $schema)
            AND array_key_exists(37, $schema) AND to_be_replaced(get_condition(37, $schema), 37, $schema))

            OR (array_key_exists(48, $schema) AND to_be_replaced(get_condition(48, $schema), 48, $schema)
            AND array_key_exists(47, $schema) AND to_be_replaced(get_condition(47, $schema), 47, $schema))
        ) {
            array_push($teeth_region_eveluate, 'in_X7_X8');
            return TRUE;
        }
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
        if(to_be_treated($status) OR $status == 'pw') {
            
            if(right($tooth_number, $schema) AND to_be_replaced(right($tooth_number, $schema)['status'], $tooth_number, $schema)) {
                array_push($teeth_inter_dental_gap_right, $tooth_number);
            }

            if(left($tooth_number, $schema) AND to_be_replaced(left($tooth_number, $schema)['status'], $tooth_number, $schema)) {
                array_push($teeth_inter_dental_gap_left, $tooth_number);
            }
        }
    }

    if(count($teeth_inter_dental_gap_right) == count($teeth_inter_dental_gap_left) )
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
        return 'OK';
    }

    if(in_array($tooth, $TOOTH_NUMBERS_ISO_LOWER)) {
        return 'UK';
    }
}

function subsidy_exists($value) {
    global $teeth_subsidy_eveluate;

    for($len=0; $len<count($teeth_subsidy_eveluate); $len++) {
        if(startswith($teeth_subsidy_eveluate[$len]['subsidy'], $value)) {
            return TRUE;
        }
    }
}

function subsidy_exists_name($applied_rule) {
    global $teeth_subsidy_eveluate;

    for($len=0; $len<count($teeth_subsidy_eveluate); $len++) {
        if(startswith($teeth_subsidy_eveluate[$len]['applied_rule'], $applied_rule)) {
            return TRUE;
        }
    }
}

function subsidy_remove_name($applied_rule) {
    global $teeth_subsidy_eveluate;

    // remove dependencies or duplicates
    // print_r($teeth_subsidy_eveluate);
    $remove_subsidies = [];

    for($len=0; $len<count($teeth_subsidy_eveluate); $len++) {
        if(startswith($teeth_subsidy_eveluate[$len]['applied_rule'], $applied_rule)) {
            array_push($remove_subsidies, $len);
        }
    }

    for($rem=0; $rem<count($remove_subsidies); $rem++) {
        unset($teeth_subsidy_eveluate[$remove_subsidies[$rem]]);
    }

    $teeth_subsidy_eveluate = array_values(($teeth_subsidy_eveluate));
}

function atleastOneInPostRegion($schema) {
    /*"""
    Represent the Posterior region (X4-X8) of the mouth.
    """*/
    global $teeth_region_eveluate;

    $in_posterior = [];

    foreach($schema as $tooth_number => $status) {
        // var_dump($tooth_number);
        // var_dump($status);

        if( in_array($tooth_number, [18,17,16,15,14,24,25,26,27,28,38,37,36,35,34,44,45,46,47,48])
            AND (to_be_treated($status) OR $status == 'pw' 
                OR to_be_replaced(get_condition($tooth_number, $schema), $tooth_number, $schema)
                )
        ) {
            array_push($in_posterior, $tooth_number);
        }
    } 

    if(count($in_posterior) > 0 )
    {
        array_push($teeth_region_eveluate, 'atleastOneInPostRegion');
        return TRUE;
    }
    else {
        return FALSE;
    }
}

function is_neighbor_gap($tooth_number, $schema) {
    $left   = left(left($tooth_number, $schema)['tooth'], $schema);
    $right  = right(right($tooth_number, $schema)['tooth'], $schema);

    $neighbour_gap = '';

    if((right($tooth_number, $schema)['status'] == '' OR to_be_treated(right($tooth_number, $schema)['status'])) AND
        (left($tooth_number, $schema)['status'] == '' OR to_be_treated(left($tooth_number, $schema)['status']) )
    // if((!to_be_replaced(right($tooth_number, $schema)['status'], $tooth_number, $schema)) AND
        // (!to_be_replaced(left($tooth_number, $schema)['status'], $tooth_number, $schema) )
    ) {
        $neighbour_gap = 1;
    }
    
    // if( $neighbour_gap == 1 AND
    //     ($right['status'] == '' AND
    //     $left['status'] == '')
    // ) {
    //     $neighbour_gap = 2;
    // }

    if( $neighbour_gap == 1 AND
        (! to_be_replaced($right['status'], $tooth_number, $schema) AND
        ! to_be_replaced($left['status'], $tooth_number, $schema) )
    ) {
        $neighbour_gap = 2;
    }

    // if( $neighbour_gap == 1 AND
    //     ($right['status'] !== '' OR
    //     $left['status'] !== '')
    // ) {
    //     $neighbour_gap = 3;
    // }

    return $neighbour_gap;
}

function veneering_grants($tooth_number) {
    $veneering_grants = false;

    if( in_array($tooth_number, [15,14,13,12,11,21,22,23,24,25,34,33,32,31,41,42,43,44]) ) {
        $veneering_grants = true;
    }

    return $veneering_grants;

}

function right($tooth_number, $schema) {
    /*"""
    Return the tooth that is just in the right of this object
    """*/

    $position_schema = position_schema($tooth_number);

    if($position_schema == 31) {
        var_dump($position_schema);
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

    /*if ($condition == "b") {
        // If the teeth near to this one is a TBR
        if (to_be_treated(left($tooth_number, $schema)['status']) OR
            to_be_treated(right($tooth_number, $schema)['status'])
        ) {
            return True;
        }

        // // If the teeth near to this one is an "x"
        if (left($tooth_number, $schema)['status'] == "x" OR
            right($tooth_number, $schema)['status'] == "x") 
        {
            return True;
        }

        // If the teeth near to this one is also a "b" (bridge)
        // and the last "b" is at the side of a TBT
        // Check from the left side
        // $left = left($tooth_number, $schema);
        // $left = left(left($tooth_number, $schema)['tooth'], $schema);

        // while ($left) {
        //     if (left($tooth_number, $schema)['status'] == "b") {
        //         $left = left(left($tooth_number, $schema)['tooth'], $schema);
        //     }
        //     else if (to_be_treated(left($tooth_number, $schema)['status'])) {
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
    }*/

    return False;
}

function to_be_treated($condition) {
    /*"""
    To Be Treated are existing tooth with or without findings:
        ww/kw/tw/pw/rw.
    """*/
    if (in_array($condition, ["ww", "kw", "tw", "pw", "rw", "ur"])) {
    // if (in_array($condition, ["ww", "kw", "tw", "rw", "ur", "k"])) {
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
                ["subsidy"=> "4.2", "output_order"=> "1", "region"=> get_jaw(16), "quanity"=> "1", "applied_rule"=> "Exact16ToBeReplacedTeeth_upper"]
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
                ["subsidy"=> "4.4", "output_order"=> "2", "region"=> get_jaw(36), "quantity"=> "1", "applied_rule"=> "Exact16ToBeReplacedTeeth_mandible"]
        );

        return True;
    }
}

function Between13And15ToBeReplacedTeeth_upper($schema) {
    global $teeth_subsidy_eveluate, $teeth_region_eveluate, $teeth_with_status;
    $to_be_replaced = [];
    $upper_all = [18, 17, 16, 15, 14, 13, 12, 11, 21, 22, 23, 24, 25, 26, 27, 28];

    // if (in_array('upper_jaw', $teeth_region_eveluate) ) {
        foreach($schema as $key => $value) {
            if(to_be_replaced($value, $key, $schema) AND position_schema($key)<16) {
                array_push($to_be_replaced, $key);
            }
        }
    // }

    $remaining_assoc = array_diff($upper_all, $to_be_replaced);
    $remaining = [];
    foreach($remaining_assoc as $pos => $val)
    {
        array_push($remaining, $val);
    }
    
    if (13 <= count($to_be_replaced) and count($to_be_replaced) <= 15) {
        array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "4.1", "output_order"=> "3", "region"=> get_jaw(16), "quantity"=> "1", "applied_rule"=> "Between13And15ToBeReplacedTeeth_upper"]
        );

        array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "4.6", "output_order"=> "5", "region"=> $remaining, "quantity"=> count($remaining), "applied_rule"=> "Between13And15ToBeReplacedTeeth_upper"]
        );

        for($x=0; $x<count($remaining); $x++) {
            if( veneering_grants($remaining[$x]) ){
                array_push($teeth_subsidy_eveluate, 
                    ["subsidy"=> "4.7", "output_order"=> "6", "region"=> $remaining[$x], "quantity"=> "1", "applied_rule"=> "veneering_grants 1"]
                );
            }
        }

        return True;
    }
}

function Between13And15ToBeReplacedTeeth_mandible($schema) {
    global $teeth_subsidy_eveluate, $teeth_region_eveluate, $teeth_with_status;
    $to_be_replaced = [];
    $lower_all = [38, 37, 36, 35, 34, 33, 32, 31, 41, 42, 43, 44, 45, 46, 47, 48];

    // if (in_array('mandible', $teeth_region_eveluate) ) {
        foreach($schema as $key => $value) {
            if(to_be_replaced($value, $key, $schema) AND position_schema($key)>15) {
                array_push($to_be_replaced, $key);
            }
        }
    // }

    $remaining_assoc = array_diff($lower_all, $to_be_replaced);
    $remaining = [];
    foreach($remaining_assoc as $pos => $val)
    {
        array_push($remaining, $val);
    }
    
    if (13 <= count($to_be_replaced) and count($to_be_replaced) <= 15) {
        array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "4.3", "output_order"=> "4", "region"=> get_jaw(36), "quantity"=> "1", "applied_rule"=> "Between13And15ToBeReplacedTeeth_mandible"]
        );

        array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "4.6", "output_order"=> "5", "region"=> $remaining, "quantity"=> count($remaining), "applied_rule"=> "Between13And15ToBeReplacedTeeth_upper"]
        );

        for($x=0; $x<count($remaining); $x++) {
            if( veneering_grants($remaining[$x]) ){
                array_push($teeth_subsidy_eveluate, 
                    ["subsidy"=> "4.7", "output_order"=> "6", "region"=> $remaining[$x], "quantity"=> "1", "applied_rule"=> "veneering_grants 2"]
                );
            }
        }

        return True;
    }
}

/** 3.x starts **/
function UnilateralFreeEndToBeReplacedTeethAtLeast1_upper($schema) {
    global $teeth_subsidy_eveluate, $teeth_region_eveluate, $teeth_with_status;

    if(subsidy_exists("3.1"))
    {
        subsidy_remove_name('Between5And12ToBeReplacedTeeth');
        // return FALSE;
    }

    if (in_array('upper_jaw_left_end', $teeth_region_eveluate) OR in_array('upper_jaw_right_end', $teeth_region_eveluate)
    ) {
        array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "3.1", "output_order"=> "7", "region"=> get_jaw(16), "quantity"=> "1", "applied_rule"=> "UnilateralFreeEndToBeReplacedTeethAtLeast1_upper"]
        );
    }
}

function UnilateralFreeEndToBeReplacedTeethAtLeast1_mandible($schema) {
    global $teeth_subsidy_eveluate, $teeth_region_eveluate, $teeth_with_status;

    if(subsidy_exists(3.1))
    {
        return FALSE;
    }

    if (in_array('mandible_left_end', $teeth_region_eveluate) OR in_array('mandible_right_end', $teeth_region_eveluate)
    ) {
        array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "3.1", "output_order"=> "7", "region"=> get_jaw(36), "quantity"=> "1", "applied_rule"=> "UnilateralFreeEndToBeReplacedTeethAtLeast1_mandible"]
        );
    }

    // if (in_array('mandible_right_end', $teeth_region_eveluate)
    // ) {
    //     array_push($teeth_subsidy_eveluate, 
    //             ["subsidy"=> "3.1", "region"=> "mandible_right_end", "applied_rule"=> "UnilateralFreeEndToBeReplacedTeethAtLeast1_mandible"]
    //     );
    // }
}

function Between5And12ToBeReplacedTeeth_upper($schema) {
    global $teeth_subsidy_eveluate, $teeth_region_eveluate, $teeth_with_status;
    $to_be_replaced = [];

    // $upper_jaw_without_x8 = new Region(17, 27);

    // if (in_array('upper_jaw', $teeth_region_eveluate) ) {
        foreach($schema as $key => $value) {
            if(to_be_replaced($value, $key, $schema) AND position_schema($key)>=0 AND position_schema($key)<15) {
                array_push($to_be_replaced, $key);
            }
        }
    // }

    
    if (5 <= count($to_be_replaced) and count($to_be_replaced) <= 12) {
        array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "3.1", "output_order"=> "7", "region"=> get_jaw(16), "quantity"=> "1", "applied_rule"=> "Between5And12ToBeReplacedTeeth_upper"]
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
                ["subsidy"=> "3.1", "output_order"=> "7", "region"=> get_jaw(36), "quantity"=> "1", "applied_rule"=> "Between5And12ToBeReplacedTeeth_upper"]
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
    global $teeth_subsidy_eveluate, $teeth_region_eveluate, $teeth_with_status, $teeth_inter_dental_gaps, $TOOTH_NUMBERS_ISO;

    $q1 = 0;
    $q2 = 0;

    $q1_arr = [];
    $q2_arr = [];

    $q2_op_right    = 0;
    $q1_op_left     = 0;
    $interdental    = 0;

    $q2_op_right_arr    = [];
    $q1_op_left_arr     = [];
    $interdental_arr    = [];

    $a1 = 0;
    $b1 = 0;
    $a1_up = 0;
    $b1_up = 0;

    $a1_arr     = [];
    $b1_arr     = [];
    $a1_up_arr  = [];
    $b1_up_arr  = [];

    $a2 = 0;
    $b2 = 0;
    $a2_up = 0;
    $b2_up = 0;

    $a2_arr     = [];
    $b2_arr     = [];
    $a2_up_arr  = [];
    $b2_up_arr  = [];

    foreach($schema as $teeth => $status) {
        if(in_array($teeth, [18,17,16])) {
            if(to_be_replaced($status, $teeth, $schema)) {
                $q1 ++;
                array_push($q1_arr, $teeth);
            }
        }

        if(in_array($teeth, [26,27,28])) {
            if(to_be_replaced($status, $teeth, $schema)) {
                $q2++;
                array_push($q2_arr, $teeth);
            }
        }

        if(in_array($teeth, [15, 14])) {
            if(to_be_replaced($status, $teeth, $schema)) {
                $q1_op_left ++;
                array_push($q1_op_left_arr, $teeth);
            }
        }

        if(in_array($teeth, [24, 25])) {
            if(to_be_replaced($status, $teeth, $schema)) {
                $q2_op_right ++;
                array_push($q2_op_right_arr, $teeth);
            }
        }

        if(in_array($teeth, [25, 26])) {
            if(to_be_replaced($status, $teeth, $schema)) {
                $a1 ++;
                array_push($a1_arr, $teeth);
            }
        }

        if(in_array($teeth, [27, 28])) {
            if(to_be_replaced($status, $teeth, $schema)) {
                $a1_up ++;
                array_push($a1_up_arr, $teeth);
            }
        }

        if(in_array($teeth, [35, 36])) {
            if(to_be_replaced($status, $teeth, $schema)) {
                $b1 ++;
            }
        }

        if(in_array($teeth, [37, 38])) {
            if(to_be_replaced($status, $teeth, $schema)) {
                $b1_up ++;
                array_push($b1_up_arr, $teeth);
            }
        }

        if(in_array($teeth, [24, 25])) {
            if(to_be_replaced($status, $teeth, $schema)) {
                $a2 ++;
            }
        }

        if(in_array($teeth, [26, 27, 28])) {
            if(to_be_replaced($status, $teeth, $schema)) {
                $a2_up ++;
                array_push($a2_up_arr, $teeth);
            }
        }

        if(in_array($teeth, [34, 35])) {
            if(to_be_replaced($status, $teeth, $schema)) {
                $b2 ++;
            }
        }

        if(in_array($teeth, [36, 37, 38])) {
            if(to_be_replaced($status, $teeth, $schema)) {
                $b2_up ++;
                array_push($b2_up_arr, $teeth);
            }
        }

        if(in_array($teeth, [12,11,21,22])) {
            if(to_be_replaced($status, $teeth, $schema)) {
                $interdental ++;
                array_push($interdental_arr, $teeth);
            }
        }
    }


    if($q1 >= 3 AND $q2 >= 3) {
        subsidy_remove_name('Between5And12ToBeReplacedTeeth');

        array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "3.1", "output_order"=> "7", "region"=> "OK", "quantity"=> "1", "applied_rule"=> "BilateralFreeEnd_upper"]
        );
    }

    if($q1 == 3 AND $q2 == 3 AND $q1_op_left>=1 AND $q2_op_right >= 1 
        AND $interdental == 0
    ) {
        array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "3.2", "output_order"=> "8", "region"=> $TOOTH_NUMBERS_ISO[position_schema(end($q1_op_left_arr)) +1].', '.$TOOTH_NUMBERS_ISO[position_schema($q2_op_right_arr[0])-1], "quantity"=> "2", "applied_rule"=> "BilateralFreeEnd_upper"]
        );
        if( veneering_grants($TOOTH_NUMBERS_ISO[position_schema(end($q1_op_left_arr)) +1]) ){
            array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "4.7", "output_order"=> "9", "region"=> $TOOTH_NUMBERS_ISO[position_schema(end($q1_op_left_arr)) +1], "quantity"=> "1", "applied_rule"=> "veneering_grants 3"]
            );
        }
        if( veneering_grants($TOOTH_NUMBERS_ISO[position_schema($q2_op_right_arr[0])-1]) ){
            array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "4.7", "output_order"=> "9", "region"=> $TOOTH_NUMBERS_ISO[position_schema($q2_op_right_arr[0])-1], "quantity"=> "1", "applied_rule"=> "veneering_grants 4"]
            );
        }
    }

    if( (($q1 == 3 AND $q1_op_left>=1) AND ($q2 ==3 AND $q2_op_right >= 1))
        // AND $interdental == 0
        AND !subsidy_exists(4.1)
    ) {
        array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "3.2", "output_order"=> "8", "region"=> $TOOTH_NUMBERS_ISO[position_schema(end($q1_op_left_arr)) +1].', '.$TOOTH_NUMBERS_ISO[position_schema($q2_op_right_arr[0])-1], "quantity"=> "2", "applied_rule"=> "unilateral free end_upper"]
        );
        if( veneering_grants($TOOTH_NUMBERS_ISO[position_schema(end($q1_op_left_arr)) +1]) ){
            array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "4.7", "output_order"=> "9", "region"=> $TOOTH_NUMBERS_ISO[position_schema(end($q1_op_left_arr)) +1], "quantity"=> "1", "applied_rule"=> "veneering_grants 5"]
            );
        }
        if( veneering_grants($TOOTH_NUMBERS_ISO[position_schema($q2_op_right_arr[0])-1]) ){
            array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "4.7", "output_order"=> "9", "region"=> $TOOTH_NUMBERS_ISO[position_schema($q2_op_right_arr[0])-1], "quantity"=> "1", "applied_rule"=> "veneering_grants 6"]
            );
        }
    }

    if(($a1 == 2 AND $a1_up<=1) 
        AND $interdental == 0 AND !subsidy_exists_name('Between5And12ToBeReplacedTeeth') AND subsidy_exists(3.1)
    ) {
        array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "3.2", "output_order"=> "8", "region"=> $TOOTH_NUMBERS_ISO[position_schema(end($q1_op_left_arr)) +1] .', '. $TOOTH_NUMBERS_ISO[position_schema($q2_op_right_arr[0])-1], "quantity"=> "2", "applied_rule"=> "kollateral interdental gap_upper 1."]
        );
        if( veneering_grants($TOOTH_NUMBERS_ISO[position_schema(end($q1_op_left_arr)) +1]) ) {
            array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "4.7", "output_order"=> "9", "region"=> $TOOTH_NUMBERS_ISO[position_schema(end($q1_op_left_arr)) +1], "quantity"=> "1", "applied_rule"=> "veneering_grants 7"]
            );
        }
        if( veneering_grants($TOOTH_NUMBERS_ISO[position_schema($q2_op_right_arr[0])-1]) ) {
            array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "4.7", "output_order"=> "9", "region"=> $TOOTH_NUMBERS_ISO[position_schema($q2_op_right_arr[0])-1], "quantity"=> "1", "applied_rule"=> "veneering_grants 8"]
            );
        }
    }

    if(($b1 == 2 AND $b1_up<=1) 
        AND $interdental == 0
    ) {
        array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "3.2", "output_order"=> "8", "region"=> "34, 44", "quantity"=> "2", "applied_rule"=> "kollateral interdental gap_lower"]
        );

        array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "4.7", "output_order"=> "9", "region"=> "34, 44", "quantity"=> "2", "applied_rule"=> "veneering_grants 9"]
        );
        
    }

    if(($a2 == 2 AND $a2_up<=2) 
        AND $interdental == 0 AND subsidy_exists(3.1) AND !subsidy_exists_name('Between5And12ToBeReplacedTeeth')
    ) {
        subsidy_remove_name('kollateral interdental gap');
        array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "3.2", "output_order"=> "8", "region"=> $TOOTH_NUMBERS_ISO[position_schema(end($q1_op_left_arr)) +1] .', '. $TOOTH_NUMBERS_ISO[position_schema($q2_op_right_arr[0])-1], "quantity"=> "2", "applied_rule"=> "kollateral interdental gap_upper"]
        );
        if( veneering_grants($TOOTH_NUMBERS_ISO[position_schema(end($q1_op_left_arr)) +1]) ){
            array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "4.7", "output_order"=> "9", "region"=> $TOOTH_NUMBERS_ISO[position_schema(end($q1_op_left_arr)) +1], "quantity"=> "1", "applied_rule"=> "veneering_grants 10"]
            );
        }
        if( veneering_grants($TOOTH_NUMBERS_ISO[position_schema($q2_op_right_arr[0])-1]) ){
            array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "4.7", "output_order"=> "9", "region"=> $TOOTH_NUMBERS_ISO[position_schema($q2_op_right_arr[0])-1], "quantity"=> "1", "applied_rule"=> "veneering_grants 11"]
            );
        }
    }

    if(($b2 == 2 AND $b2_up<=2) 
        AND $interdental == 0
    ) {
        subsidy_remove_name('kollateral interdental gap');
        array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "3.2", "output_order"=> "8", "region"=> "34, 44", "quantity"=> "2", "applied_rule"=> "kollateral interdental gap_lower"]
        );
        array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "4.7", "output_order"=> "9", "region"=> "34, 44", "quantity"=> "2", "applied_rule"=> "veneering_grants 9"]
        );
    }

        
    // check option 2.2
    if( subsidy_exists_name('BilateralFreeEnd') AND
        // // count(array_intersect($teeth_with_status, [12,11,21,22])) == count($teeth_with_status)- ($q1 + $q2) AND
        // // is_interdental_gap($schema) AND 
        // !subsidy_exists(3.2) AND 
        !subsidy_exists(4)
    ) {
        // if(biggest_interdental_gap($teeth_inter_dental_gaps) == 2) {
        if($interdental == 2) {
            array_push($teeth_subsidy_eveluate, 
                    ["subsidy"=> "2.2", "output_order"=> "12", "region"=> $TOOTH_NUMBERS_ISO[position_schema($interdental_arr[0]) -1] .'-'. $TOOTH_NUMBERS_ISO[position_schema(end($interdental_arr)) +1], "quantity"=> "1", "applied_rule"=> "BiggestInterdentalGapExactToBe Replaced 2"]
            );

            for($v= position_schema($interdental_arr[0]) -1; $v<= position_schema(end($interdental_arr)) +1; $v++) {
                if(veneering_grants($TOOTH_NUMBERS_ISO[$v])) {
                    array_push($teeth_subsidy_eveluate, 
                        ["subsidy"=> "2.7", "output_order"=> "15", "region"=> $TOOTH_NUMBERS_ISO[$v], "quantity"=> "1", "applied_rule"=> "Veneering grants 2.x 1"]
                    );
                }
            }
        }

        // if(biggest_interdental_gap($teeth_inter_dental_gaps) == 1) {
        if($interdental == 1) {
            array_push($teeth_subsidy_eveluate, 
                    ["subsidy"=> "2.1", "output_order"=> "13", "region"=> $TOOTH_NUMBERS_ISO[position_schema($interdental_arr[0]) -1] .'-'. $TOOTH_NUMBERS_ISO[position_schema(end($interdental_arr)) +1], "quantity"=> "1", "applied_rule"=> "Biggest InterdentalGapExactToBe Replaced 1"]
            );

            for($v= position_schema($interdental_arr[0]) -1; $v<= position_schema(end($interdental_arr)) +1; $v++) {
                if(veneering_grants($TOOTH_NUMBERS_ISO[$v])) {
                    array_push($teeth_subsidy_eveluate, 
                        ["subsidy"=> "2.7", "output_order"=> "15", "region"=> $TOOTH_NUMBERS_ISO[$v], "quantity"=> "1", "applied_rule"=> "Veneering grants 2.x 2"]
                    );
                }
            }
        }
    }

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

    // $tooth_32_left = $teeth_with_status[0];
    // $tooth_32_right = end($teeth_with_status);

    // if ($tooth_32_left == 45 or $tooth_32_right == 35) {
    //     return True;
    // }

    // if (to_be_replaced($tooth_32_left) or $tooth_32_right->to_be_replaced) {
    //     return True;
    // }

    // array_push($teeth_subsidy_eveluate, 
    //     ["subsidy"=> "3.2", "region"=> "upper_jaw".$tooth_32_left.' '.$tooth_32_right, "applied_rule"=> "BilateralFreeEnd_upper"]
    // );
}

function special_ob_f($schema) {
    global $teeth_subsidy_eveluate, $teeth_region_eveluate;
    $to_be_replaced_count = [];

    if(subsidy_exists(3.1)) {
        return FALSE;
    }

    foreach($schema as $key => $value) {
        
        if(to_be_replaced($value, $key, $schema) AND right($key, $schema)['status'] == '' AND is_neighbor_gap($key, $schema) == '1'
        ) {
            array_push($to_be_replaced_count, $key);
        }
    }

    if(count($to_be_replaced_count) >= 3 AND (get_jaw($to_be_replaced_count[0]) == get_jaw(end($to_be_replaced_count))) AND
        ! (in_array('upper_jaw_left_end', $teeth_region_eveluate) OR
        in_array('upper_jaw_right_end', $teeth_region_eveluate) OR
        in_array('mandible_left_end', $teeth_region_eveluate) OR
        in_array('mandible_right_end', $teeth_region_eveluate))
    ) {
        array_push($teeth_subsidy_eveluate,
                ["subsidy"=> "3.1", "output_order"=> "7", "region"=> get_jaw($to_be_replaced_count[0]), "quantity"=> "1", "applied_rule"=> "BiggestInterdentalGapExactToBeReplaced=3"]
        );
    }

}

/** 3.x end **/

function special_case_b($schema) {
    global $teeth_subsidy_eveluate, $teeth_region_eveluate, $TOOTH_NUMBERS_ISO;
    $to_be_replaced_count   = [];
    $to_be_teated_count     = [];

    $right_b = 0;
    $left_b = 0;

    $bs_array = [];

    
    if(subsidy_exists_name('BilateralFreeEnd') OR subsidy_exists_name('kollateral interdental gap')) {
        return FALSE;
    }

    foreach($schema as $teeth => $status) {
        if($status == 'b'){
            if(to_be_replaced(right($teeth, $schema)['status'], $teeth, $schema) OR
                to_be_replaced(left($teeth, $schema)['status'], $teeth, $schema)
            ) {
                array_push($to_be_replaced_count, $teeth);
            }

            if(to_be_treated(right($teeth, $schema)['status']) OR
                to_be_treated(left($teeth, $schema)['status']) OR
                right($teeth, $schema)['status'] == 'pw' OR
                left($teeth, $schema)['status'] == 'pw'
            ) {
                array_push($to_be_teated_count, $teeth);
            }

            if(right($teeth, $schema)['status'] == 'b') {
                array_push($bs_array, position_schema($teeth));
                $right_b++; //= right_b($teeth, $schema);
            }

            if(left($teeth, $schema)['status'] == 'b') {
                array_push($bs_array, position_schema($teeth));
                $left_b++; //= left_b($teeth, $schema);
            }
        }
    }

    sort($bs_array);

    $bs = $right_b < $left_b ? $right_b : $left_b;

    if( count($to_be_replaced_count) > 0) {
        if(count($bs_array) > 0) {
            $pos_sch_right = end($bs_array) + 1;
        }
        else {
            $pos_sch_right = position_schema(end($to_be_replaced_count)) + 1;
        }

        $pos_sch       = position_schema($to_be_replaced_count[0]) - 2;

        if( to_be_replaced(right(end($to_be_replaced_count), $schema)['status'], $teeth, $schema) ) {
            $pos_sch        = position_schema($to_be_replaced_count[0]) - 1;
            $pos_sch_right  = position_schema(end($to_be_replaced_count)) + 2;
        }
    }

    if(count($to_be_replaced_count) == 0 AND count($bs_array) > 0)
    {
        $pos_sch_right  = end($bs_array) + 1;
        $pos_sch        = $bs_array[0] - 1;
    }

    if(count($to_be_replaced_count) + $bs == 1) {
        array_push($teeth_subsidy_eveluate,
            ["subsidy"=> "2.2", "output_order"=> "12", "region"=> $TOOTH_NUMBERS_ISO[$pos_sch] .'-'. $TOOTH_NUMBERS_ISO[$pos_sch_right], "quantity"=> "1", "applied_rule"=> "Biggest InterdentalGapExactToBeReplaced2"]
        );

        for($v= ($pos_sch); $v<= ($pos_sch_right); $v++) {
            if(veneering_grants($TOOTH_NUMBERS_ISO[$v])) {
                array_push($teeth_subsidy_eveluate, 
                    ["subsidy"=> "2.7", "output_order"=> "15", "region"=> $TOOTH_NUMBERS_ISO[$v], "quantity"=> "1", "applied_rule"=> "Veneering grants 2.x 3"]
                );
            }
        }
    }

    if(count($to_be_replaced_count) + $bs == 2) {
        array_push($teeth_subsidy_eveluate,
            ["subsidy"=> "2.3", "output_order"=> "11", "region"=> $TOOTH_NUMBERS_ISO[$pos_sch] .'-'. $TOOTH_NUMBERS_ISO[$pos_sch_right], "quantity"=> "1", "applied_rule"=> "Biggest InterdentalGapExactToBeReplaced3"]
        );

        for($v= ($pos_sch); $v<= ($pos_sch_right); $v++) {
            if(veneering_grants($TOOTH_NUMBERS_ISO[$v])) {
                array_push($teeth_subsidy_eveluate, 
                    ["subsidy"=> "2.7", "output_order"=> "15", "region"=> $TOOTH_NUMBERS_ISO[$v], "quantity"=> "1", "applied_rule"=> "Veneering grants 2.x 4"]
                );
            }
        }
    }

    if(count($to_be_teated_count) == 1 AND !subsidy_exists(2)) {
        if(count($bs_array) > 0) {
            $pos_sch_right = end($bs_array) + 1;
        }
        else {
            $pos_sch_right = position_schema(end($to_be_teated_count)) + 1;
        }

        $pos_sch       = position_schema($to_be_teated_count[0]) - 1;

        array_push($teeth_subsidy_eveluate,
            ["subsidy"=> "2.1", "output_order"=> "13", "region"=> $TOOTH_NUMBERS_ISO[$pos_sch] .'-'. $TOOTH_NUMBERS_ISO[$pos_sch_right], "quantity"=> "1", "applied_rule"=> "BiggestInterdentalGapExactToBeReplaced2"]
        );

        for($v= ($pos_sch); $v<= ($pos_sch_right); $v++) {
            if(veneering_grants($TOOTH_NUMBERS_ISO[$v])) {
                array_push($teeth_subsidy_eveluate, 
                    ["subsidy"=> "2.7", "output_order"=> "15", "region"=> $TOOTH_NUMBERS_ISO[$v], "quantity"=> "1", "applied_rule"=> "Veneering grants 2.x 5"]
                );
            }
        }
    }
}

function right_bs($teeth, $schema) {
    $status = '';
    $b = 0;

    if(right($teeth, $schema)['status'] == 'b') {
        $b++;
        // right_b($teeth, $schema);
    }
    else {
        return ["status" => $status, "bs" => $b];
    }
    // print_r($b);
}

function left_bs($teeth, $schema) {
    $status = '';
    $b = 0;
    if(left($teeth, $schema)['status'] == 'b') {
        $b++;
        // left_b($teeth, $schema);
    }
    else {
        return ["status" => $status, "bs" => $b];
    }
}


function Exact2_3ToBeReplacedTeethInterdentalGapInFrontRegion($schema) {
    global $teeth_subsidy_eveluate, $teeth_region_eveluate, $TOOTH_NUMBERS_ISO, $is_gap_closure_arr;
    $to_be_replaced_count = [];
    // $is_neighbor_gap = 0;

    if(subsidy_exists_name('BilateralFreeEnd') OR subsidy_exists_name('kollateral interdental gap')) {
        return FALSE;
    }

    foreach($schema as $key => $value) {
        
        // if(to_be_replaced($value, $key, $schema) AND right($key, $schema) AND to_be_replaced(right($key, $schema)['status'], $key, $schema)) {
        if(to_be_replaced($value, $key, $schema) AND (to_be_replaced(right($key, $schema)['status'], $key, $schema) 
                                                        OR right($key, $schema)['status'] == '' ) 
                                                        AND is_neighbor_gap($key, $schema) == ''
        ) {
            array_push($to_be_replaced_count, $key);
        }

        if(to_be_replaced($value, $key, $schema) AND (to_be_replaced(left($key, $schema)['status'], $key, $schema) 
                                                        OR left($key, $schema)['status'] == '' ) 
                                                        AND is_neighbor_gap($key, $schema) == ''
        ) {
            array_push($to_be_replaced_count, $key);
        }
    }
    $to_be_replaced_count = array_values(array_unique($to_be_replaced_count));


    if(count($to_be_replaced_count) == 4 AND !subsidy_exists(3.1) AND
        ! in_array('in_X7_X8', $teeth_region_eveluate) AND 
        (position_schema($to_be_replaced_count[2]) - position_schema($to_be_replaced_count[1]) > 2) 
    ) {
        array_push($teeth_subsidy_eveluate,
                ["subsidy"=> "2.2", "output_order"=> "12", "region"=> $TOOTH_NUMBERS_ISO[position_schema($to_be_replaced_count[0])-1] .'-'. $TOOTH_NUMBERS_ISO[position_schema($to_be_replaced_count[1])+1], "quantity"=> "1", "applied_rule"=> "BiggestInterdental GapExactToBeReplaced2"]
        );

        array_push($teeth_subsidy_eveluate,
                ["subsidy"=> "2.2", "output_order"=> "12", "region"=> $TOOTH_NUMBERS_ISO[position_schema($to_be_replaced_count[2])-1] .'-'. $TOOTH_NUMBERS_ISO[position_schema($to_be_replaced_count[3])+1], "quantity"=> "1", "applied_rule"=> "BiggestInterdentalGapExactToBeReplaced2"]
        );

        for($v= position_schema($to_be_replaced_count[0])-1; $v<= position_schema($to_be_replaced_count[3])+1; $v++) {
            if(veneering_grants($TOOTH_NUMBERS_ISO[$v])) {
                array_push($teeth_subsidy_eveluate, 
                    ["subsidy"=> "2.7", "output_order"=> "15", "region"=> $TOOTH_NUMBERS_ISO[$v], "quantity"=> "1", "applied_rule"=> "Veneering grants 2.x 6"]
                );
            }
        }

        return FALSE;
    }

    if(count($to_be_replaced_count) == 4 AND !subsidy_exists(3.1) AND
        ! in_array('in_X7_X8', $teeth_region_eveluate) AND 
        (position_schema($to_be_replaced_count[2]) - position_schema($to_be_replaced_count[1]) == 2) 
    ) {
        array_push($teeth_subsidy_eveluate,
                ["subsidy"=> "3.1", "output_order"=> "7", "region"=> get_jaw($to_be_replaced_count[0]), "quantity"=> "1", "applied_rule"=> "BiggestInterdentalGapExactToBeReplaced 4 (!3.1)"]
        );

        return FALSE;
    }

    
    if(count($to_be_replaced_count) == 4 AND !subsidy_exists('3.1')) {
        if( in_array('atleastOneInPostRegion', $teeth_region_eveluate) )
        {
            array_push($teeth_subsidy_eveluate,
                ["subsidy"=> "3.1", "output_order"=> "7", "region"=> get_jaw($to_be_replaced_count[0]), "quantity"=> "1", "applied_rule"=> "BiggestInterdentalGapExactToBeReplaced--4"]
            );
        }
        else {
            array_push($teeth_subsidy_eveluate,
                    ["subsidy"=> "2.4", "output_order"=> "10", "region"=> $TOOTH_NUMBERS_ISO[position_schema($to_be_replaced_count[0])-1] .'-'. $TOOTH_NUMBERS_ISO[position_schema(end($to_be_replaced_count))+1], "quantity"=> "1", "applied_rule"=> "BiggestInterdentalGapExactToBeReplaced4"]
            );

            for($v= position_schema($to_be_replaced_count[0])-1; $v<= position_schema(end($to_be_replaced_count))+1; $v++) {
                if(veneering_grants($TOOTH_NUMBERS_ISO[$v])) {
                    array_push($teeth_subsidy_eveluate, 
                        ["subsidy"=> "2.7", "output_order"=> "15", "region"=> $TOOTH_NUMBERS_ISO[$v], "quantity"=> "1", "applied_rule"=> "Veneering grants 2.x 7"]
                    );
                }
            }
        }
    }

    if(count($to_be_replaced_count) == 3 //AND !subsidy_exists('2.4') 
        AND
        ! (in_array('upper_jaw_left_end', $teeth_region_eveluate) OR
        in_array('upper_jaw_right_end', $teeth_region_eveluate) OR
        in_array('mandible_left_end', $teeth_region_eveluate) OR
        in_array('mandible_right_end', $teeth_region_eveluate))
    ) {
        $value_add_right = 1;
        //check for gap closure
        for($val= position_schema($to_be_replaced_count[0])-1; $val<= position_schema(end($to_be_replaced_count))+1; $val++) {

            if(in_array($TOOTH_NUMBERS_ISO[$val], $is_gap_closure_arr))
            {
                $value_add_right += 1;
            }

        }
        $new_right_val = $TOOTH_NUMBERS_ISO[position_schema(end($to_be_replaced_count))+$value_add_right];

        array_push($teeth_subsidy_eveluate,
                ["subsidy"=> "2.3", "output_order"=> "11", "region"=> $TOOTH_NUMBERS_ISO[position_schema($to_be_replaced_count[0])-1] .'-'. $new_right_val, "quantity"=> "1", "applied_rule"=> "BiggestInterdentalGapExactToBeReplaced 3"]
                // ["subsidy"=> "2.3", "output_order"=> "11", "region"=> $TOOTH_NUMBERS_ISO[position_schema($to_be_replaced_count[0])-1] .'-'. $TOOTH_NUMBERS_ISO[position_schema(end($to_be_replaced_count))+1], "quantity"=> "1", "applied_rule"=> "BiggestInterdentalGapExactToBeReplaced 3"]
        );

        for($v= position_schema($to_be_replaced_count[0])-1; $v<= position_schema(end($to_be_replaced_count))+1; $v++) {
            if(veneering_grants($TOOTH_NUMBERS_ISO[$v])) {
                array_push($teeth_subsidy_eveluate, 
                    ["subsidy"=> "2.7", "output_order"=> "15", "region"=> $TOOTH_NUMBERS_ISO[$v], "quantity"=> "1", "applied_rule"=> "Veneering grants 2.x 8"]
                );
            }
        }
    }

    if(count($to_be_replaced_count) == 2 AND !subsidy_exists('2.3') AND
        ! in_array('in_X7_X8', $teeth_region_eveluate)
    ) {
        $value_add_right = 1;
        //check for gap closure
        for($val= position_schema($to_be_replaced_count[0])-1; $val<= position_schema(end($to_be_replaced_count))+1; $val++) {

            if(in_array($TOOTH_NUMBERS_ISO[$val], $is_gap_closure_arr))
            {
                $value_add_right += 1;
            }

        }
        $new_right_val = $TOOTH_NUMBERS_ISO[position_schema(end($to_be_replaced_count))+$value_add_right];

        array_push($teeth_subsidy_eveluate,
                ["subsidy"=> "2.2", "output_order"=> "12", "region"=> $TOOTH_NUMBERS_ISO[position_schema($to_be_replaced_count[0])-1] .'-'. $new_right_val, "quantity"=> "1", "applied_rule"=> "BiggestInterdentalGapExactToBeReplaced-2 3"]
        );

        for($v= position_schema($to_be_replaced_count[0])-1; $v<= position_schema(end($to_be_replaced_count))+1; $v++) {
            if(veneering_grants($TOOTH_NUMBERS_ISO[$v])) {
                array_push($teeth_subsidy_eveluate, 
                    ["subsidy"=> "2.7", "output_order"=> "15", "region"=> $TOOTH_NUMBERS_ISO[$v], "quantity"=> "1", "applied_rule"=> "Veneering grants 2.x 9"]
                );
            }
        }
    }

    if(count($to_be_replaced_count) == 2 AND
        in_array('in_X7_X8', $teeth_region_eveluate)
    ) {
        array_push($teeth_subsidy_eveluate,
                ["subsidy"=> "3.1", "output_order"=> "7", "region"=> get_jaw($to_be_replaced_count[0]), "quantity"=> "1", "applied_rule"=> "BiggestInterdentalGapExactToBeReplaced2_X7_X8"]
        );
    }
}

function Exact1ToBeReplacedTeethInterdentalGapInFrontRegion($schema) {
    global $teeth_subsidy_eveluate, $teeth_with_status, $TOOTH_NUMBERS_ISO, $is_gap_closure_arr;
    $to_be_replaced_count = [];
    $fs = [];

    if(subsidy_exists_name('BilateralFreeEnd_upper')
        OR subsidy_exists_name('BiggestInterdentalGapExactToBeReplaced2_X7_X8')
    ) {
    // if(subsidy_exists('3.1')) { // newly added; may have issue, check later
        return FALSE;
    }

    foreach($schema as $key => $value) {
        if(to_be_replaced($value, $key, $schema)) {
            array_push($fs, $key);
        }
        if(! in_array($key, [18, 28, 38, 48])) {
            if(to_be_replaced($value, $key, $schema) AND 
                ( right($key, $schema)['status'] == '' OR to_be_treated(right($key, $schema)['status']) ) AND 
                ( left($key, $schema)['status'] == '' OR to_be_treated(left($key, $schema)['status']) )
            ) {
                array_push($to_be_replaced_count, $key);
                // var_dump($key);
                //start checking 3.1: 5_to_12 
                for($len=0; $len<count($teeth_subsidy_eveluate); $len++) {
                    if(($teeth_subsidy_eveluate[$len]['subsidy'] == '3.1' //OR
                        // $teeth_subsidy_eveluate[$len]['applied_rule'] == 'Between5And12ToBeReplacedTeeth_mandible'
                        )
                        // AND is_neighbor_gap($key, $schema) > 1
                    ) {
                        return FALSE;
                    }
                }
                //end checking 3.1: 5_to_12
                // var_dump($fs);
                if(is_neighbor_gap($key, $schema) == 1 
                    // AND count($fs) > 1
                ) {
                    $pos_sch_right = position_schema($key) + 1;
                    $pos_sch       = position_schema($key) - 1;

                    if(to_be_treated(right($key, $schema)['status'])) {
                        $pos_sch        = position_schema($key) - 1;
                        $pos_sch_right  = position_schema($key) + 1;
                    }
 
                    if( ! subsidy_exists(2.5) //AND
                        // get_jaw($fs[0]) !== get_jaw(end($fs))
                    ) {
                        array_push($teeth_subsidy_eveluate, 
                            ["subsidy"=> "2.5", "output_order"=> "14", "region"=> $TOOTH_NUMBERS_ISO[$pos_sch] .'-'. $TOOTH_NUMBERS_ISO[$pos_sch_right], "quantity"=> "1", "applied_rule"=> "2.X_with_neighbor"]
                        );
                        
                        for($v= ($pos_sch); $v<= ($pos_sch_right); $v++) {
                            if(veneering_grants($TOOTH_NUMBERS_ISO[$v])) {
                                array_push($teeth_subsidy_eveluate, 
                                    ["subsidy"=> "2.7", "output_order"=> "15", "region"=> $TOOTH_NUMBERS_ISO[$v], "quantity"=> "1", "applied_rule"=> "Veneering grants 2.x 10"]
                                );
                            }
                        }
                    }

                    if(count($fs) == 2)
                    {
                        array_push($teeth_subsidy_eveluate, 
                            ["subsidy"=> "2.1", "output_order"=> "13", "region"=> $TOOTH_NUMBERS_ISO[$pos_sch] .'-'. $TOOTH_NUMBERS_ISO[$pos_sch_right], "quantity"=> "1", "applied_rule"=> "BiggestInterdentalGapExactToBe Replaced 1"]
                        );

                        for($v= ($pos_sch); $v<= ($pos_sch_right); $v++) {
                            if(veneering_grants($TOOTH_NUMBERS_ISO[$v])) {
                                array_push($teeth_subsidy_eveluate, 
                                    ["subsidy"=> "2.7", "output_order"=> "15", "region"=> $TOOTH_NUMBERS_ISO[$v], "quantity"=> "1", "applied_rule"=> "Veneering grants 2.x 11"]
                                );
                            }
                        }
                    }

                    // if(count($teeth_with_status) == 3)
                    // {
                    //     subsidy_remove_name('2.X_with_neighbor');
                    //     array_push($teeth_subsidy_eveluate, 
                    //         ["subsidy"=> "3.1", "output_order"=> "7", "region"=> "biggest_interdental_gap", "applied_rule"=> "BiggestInterdentalGapExactToBeReplaced 1"]
                    //     );
                    // }
                    // return FALSE;
                }
                else {
                    $pos_sch_right = position_schema($key) + 1;
                    $pos_sch       = position_schema($key) - 1;

                    if(to_be_treated(right($key, $schema)['status'])) {
                        $pos_sch        = position_schema($key) - 1;
                        $pos_sch_right  = position_schema($key) + 1;
                    }

                    //check for gap closure
                    if(in_array($TOOTH_NUMBERS_ISO[position_schema($key) + 1], $is_gap_closure_arr))
                    {
                        $pos_sch_right = position_schema($key) + 2;
                    }

                    // var_dump($TOOTH_NUMBERS_ISO[position_schema($key)]);

                    if(in_array($TOOTH_NUMBERS_ISO[position_schema($key)], $is_gap_closure_arr))
                    {
                        $pos_sch_right  = position_schema($key) + 2;

                        // var_dump($pos_sch_right);
                    }

                    $is_greater = 0;
                    for($bigger=0; $bigger<count($is_gap_closure_arr); $bigger++) {
                        // TO fix. another loop required for $teeth_with_status[$zz]
                        if(position_schema($key) > position_schema($is_gap_closure_arr[$bigger]))
                        {
                            $is_greater += 1;
                        }
                    }

                    // $pos_sch        = $pos_sch + $is_greater;
                    $pos_sch_right  = $pos_sch_right + $is_greater;

                    array_push($teeth_subsidy_eveluate, 
                        ["subsidy"=> "2.1", "output_order"=> "13", "region"=> $TOOTH_NUMBERS_ISO[$pos_sch] .'-'. $TOOTH_NUMBERS_ISO[$pos_sch_right], "quantity"=> "1", "applied_rule"=> "BiggestInterdentalGapExactToBeReplaced-1"]
                    );

                    for($v= ($pos_sch); $v<= ($pos_sch_right); $v++) {
                        if(veneering_grants($TOOTH_NUMBERS_ISO[$v])) {
                            array_push($teeth_subsidy_eveluate, 
                                ["subsidy"=> "2.7", "output_order"=> "15", "region"=> $TOOTH_NUMBERS_ISO[$v], "quantity"=> "1", "applied_rule"=> "Veneering grants 2.x 15"]
                            );
                        }

                        /*if(in_array($TOOTH_NUMBERS_ISO[$v], $is_gap_closure_arr)) {

                            if(in_array($TOOTH_NUMBERS_ISO[$v], [18,17,16,15,14,13,12,11])) {

                            }

                            array_push($teeth_subsidy_eveluate, 
                                ["subsidy"=> "2.7", "output_order"=> "15", "region"=> $TOOTH_NUMBERS_ISO[$v], "quantity"=> "1", "applied_rule"=> "Veneering grants 2.x 15 2"]
                            );
                        }*/
                    }
                    // return FALSE;
                }
            }
        }
    }
    // insert into subsidy array outside the array
}

/** 1.x start **/
function StatusPwInPosteriorRegion($schema) {
    global $teeth_subsidy_eveluate;

    // pw in 18-14, 24-28 (posterior region)

    $StatusPwInPosteriorRegion = [];

    foreach($schema as $teeth => $status) {
        if (($teeth == 18 OR $teeth == 17 OR $teeth == 16 OR $teeth == 15 OR $teeth == 14 OR
            $teeth == 24 OR $teeth == 25 OR $teeth == 26 OR $teeth == 27 OR $teeth == 28 OR
            $teeth == 38 OR $teeth == 37 OR $teeth == 36 OR $teeth == 35 OR $teeth == 34 OR
            $teeth == 44 OR $teeth == 45 OR $teeth == 46 OR $teeth == 47 OR $teeth == 48 ) AND
            !to_be_replaced($status, $teeth, $schema)
        ) {
            // array_push($StatusPwInPosteriorRegion, get_condition($teeth, $schema));
            if(get_condition($teeth, $schema) == 'pw')
                array_push($StatusPwInPosteriorRegion, $teeth);
        }
    }

    // if (!subsidy_exists(2.1))
    {
        for($r=0; $r<count($StatusPwInPosteriorRegion); $r++ ) {
            array_push($teeth_subsidy_eveluate, 
                    ["subsidy"=> "1.2", "output_order"=> "18", "region"=> $StatusPwInPosteriorRegion[$r].' ', "quantity"=> "1", "applied_rule"=> "StatusPwInPosteriorRegion"]
            );
        }
    }
}

function StatusPwInFrontRegion($schema) {
    global $teeth_subsidy_eveluate;

    // pw in Region(13, 23, $this->schema), Region(33, 43, $this->schema) (posterior region)

    $StatusPwInFrontRegion = [];
    $region = [];

    foreach($schema as $teeth => $status) {
        if ($teeth == 13 OR $teeth == 12 OR $teeth == 11 OR $teeth == 21 OR $teeth == 22 OR $teeth == 23 OR
            $teeth == 33 OR $teeth == 32 OR $teeth == 31 OR $teeth == 41 OR $teeth == 42 OR $teeth == 43 
        ) {
            // array_push($StatusPwInFrontRegion, get_condition($teeth, $schema));
            if(get_condition($teeth, $schema) == 'pw')
                array_push($StatusPwInFrontRegion, $teeth);
        }
    }


    // if (in_array('pw', $StatusPwInFrontRegion) AND !subsidy_exists(2.2)) {
    for($r=0; $r<count($StatusPwInFrontRegion); $r++ ) {
        array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "1.1", "output_order"=> "16", "region"=> $StatusPwInFrontRegion[$r] .' ' , "quantity"=> "1", "applied_rule"=> "StatusPwInFrontRegion"]
        );
        if( veneering_grants($StatusPwInFrontRegion[$r]) ){
            array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "1.3", "output_order"=> "17", "region"=> $StatusPwInFrontRegion[$r], "quantity"=> "1", "applied_rule"=> "veneering_grants 12"]
            );
        }
    }
}

function ToBeTreatedWithNoAbutmentTeethIncluded($schema) {
    global $teeth_subsidy_eveluate, $teeth_inter_dental_gaps, $TOOTH_NUMBERS_ISO;

    $teeth_included = [];
    $get_jaw = '';
    $is_next_tbr = '';

    /* to check neighbouring interdental gap : todo */
    foreach($teeth_inter_dental_gaps as $start => $end) {
        array_push($teeth_included, intval($start));
        array_push($teeth_included, intval($end));
    }

    foreach($schema as $tooth => $value) {
        if( to_be_treated($value) ) {
            if((to_be_replaced(right($tooth, $schema)['status'], $tooth, $schema) OR
             to_be_replaced(left($tooth, $schema)['status'], $tooth, $schema)) AND
             (right(right($tooth, $schema)['tooth'], $schema)['status'] == '' AND
             left(left($tooth, $schema)['tooth'], $schema)['status'] == '') AND !subsidy_exists_name('BiggestInterdentalGapExactToBeReplaced=3')
             AND !subsidy_exists(2.1)
            ) {
                $get_jaw = get_jaw($tooth);
                // if(subsidy_exists(2.1))
                // {
                    // return FALSE;
                // }

                $pos_sch_right = position_schema($tooth);
                $pos_sch       = position_schema($tooth) - 2;

                if(to_be_replaced(right($tooth, $schema)['status'], $tooth, $schema)) {
                    $pos_sch        = position_schema($tooth);
                    $pos_sch_right  = position_schema($tooth) + 2;
                }

                array_push($teeth_subsidy_eveluate, 
                    ["subsidy"=> "2.1", "output_order"=> "13", "region"=> $TOOTH_NUMBERS_ISO[$pos_sch] .'-'. $TOOTH_NUMBERS_ISO[$pos_sch_right], "quantity"=> "1", "applied_rule"=> "TBT_next_to_TBR"]
                );

                for($v= ($pos_sch); $v<= ($pos_sch_right); $v++) {
                    if(veneering_grants($TOOTH_NUMBERS_ISO[$v])) {
                        array_push($teeth_subsidy_eveluate, 
                            ["subsidy"=> "2.7", "output_order"=> "15", "region"=> $TOOTH_NUMBERS_ISO[$v], "quantity"=> "1", "applied_rule"=> "Veneering grants 2.x 13"]
                        );
                    }
                }
            }
            // else if(is_interdental_gap($schema)) {
            else if(subsidy_exists_name('BiggestInterdentalGapExactToBeReplaced=3')) {
                array_push($teeth_subsidy_eveluate, 
                    ["subsidy"=> "1.1", "output_order"=> "16", "region"=> $tooth, "quantity"=> "1", "applied_rule"=> "ToBeTreatedWithNoAbutmentTeethIncluded"]
                );
                if( veneering_grants($tooth) ){
                    array_push($teeth_subsidy_eveluate, 
                        ["subsidy"=> "1.3", "output_order"=> "17", "region"=> $tooth, "quantity"=> "1", "applied_rule"=> "veneering_grants 13"]
                    );
                }
            }
            else if( ((right($tooth, $schema)['status'] == '' AND left($tooth, $schema)['status'] == '') OR
                (to_be_treated(left($tooth, $schema)['status']) OR left($tooth, $schema)['status'] == 'pw')) AND
                !to_be_replaced(right($tooth, $schema)['status'], $tooth, $schema)
                // ( to_be_treated(right($tooth, $schema)['status']) OR to_be_treated(left($tooth, $schema)['status']) )
            ) {
                // $get_jaw = get_jaw($tooth);
                // if(subsidy_exists(2.2) OR subsidy_exists(2.3) OR subsidy_exists(1.1))
                if(subsidy_exists(2.2) OR subsidy_exists(2.3))
                {
                    // return FALSE;
                }
                array_push($teeth_subsidy_eveluate, 
                    ["subsidy"=> "1.1", "output_order"=> "16", "region"=> $tooth, "quantity"=> "1", "applied_rule"=> "ToBeTreatedWithNoAbutmentTeethIncluded"]
                );
                if( veneering_grants($tooth) ){
                    array_push($teeth_subsidy_eveluate, 
                        ["subsidy"=> "1.3", "output_order"=> "17", "region"=> $tooth, "quantity"=> "1", "applied_rule"=> "veneering_grants 14"]
                    );
                }
            }
            else if( ((right($tooth, $schema)['status'] == '' AND left($tooth, $schema)['status'] == '') OR
                (to_be_treated(right($tooth, $schema)['status']) OR right($tooth, $schema)['status'] == 'pw')) AND
                !to_be_replaced(left($tooth, $schema)['status'], $tooth, $schema)
                // ( to_be_treated(right($tooth, $schema)['status']) OR to_be_treated(left($tooth, $schema)['status']) )
            ) {
                // $get_jaw = get_jaw($tooth);
                // if(subsidy_exists(2.2) OR subsidy_exists(2.3) OR subsidy_exists(1.1))
                // if(subsidy_exists(2.2) OR subsidy_exists(2.3) OR subsidy_exists_name('StatusPwInPosteriorRegion') OR subsidy_exists_name('StatusPwInFrontRegion'))
                if(subsidy_exists(2.2) OR subsidy_exists(2.3))
                {
                    // return FALSE;
                }
                array_push($teeth_subsidy_eveluate, 
                    ["subsidy"=> "1.1", "output_order"=> "16", "region"=> $tooth, "quantity"=> "1", "applied_rule"=> "ToBeTreatedWithNoAbutmentTeethIncluded"]
                );
                if( veneering_grants($tooth) ){
                    array_push($teeth_subsidy_eveluate, 
                        ["subsidy"=> "1.3", "output_order"=> "17", "region"=> $tooth, "quantity"=> "1", "applied_rule"=> "veneering_grants 15"]
                    );
                }
            }
        }
    }
}
/** 1.x end **/

/** 2.x start **/
function BiggestInterdentalGapInFrontRegionExactToBeReplaced4($schema) {
    global $teeth_inter_dental_gaps, $teeth_subsidy_eveluate, $teeth_region_eveluate, $TOOTH_NUMBERS_ISO;

    $region_arr = [];
    
    if(! is_interdental_gap($schema)) {
        return FALSE;
    }

    if(biggest_interdental_gap($teeth_inter_dental_gaps) == 4
    ) {
        foreach($teeth_inter_dental_gaps as $key => $value) {
            array_push($region_arr, $key);
            array_push($region_arr, $value);
        }

        array_push($teeth_subsidy_eveluate, 
                ["subsidy"=> "2.4", "output_order"=> "10", "region"=> $region_arr[0] ."-". $region_arr[1], "quantity"=> "1", "applied_rule"=> "Biggest InterdentalGapInFrontRegionExactToBeReplaced4"]
        );

        for($v= position_schema($region_arr[0]); $v<= position_schema($region_arr[1]); $v++) {
            if(veneering_grants($TOOTH_NUMBERS_ISO[$v])) {
                array_push($teeth_subsidy_eveluate, 
                    ["subsidy"=> "2.7", "output_order"=> "15", "region"=> $TOOTH_NUMBERS_ISO[$v], "quantity"=> "1", "applied_rule"=> "Veneering grants 2.x 14"]
                );
            }
        }
    }
}


function BiggestInterdentalGapExactToBeReplaced3($schema) {
    global $teeth_inter_dental_gaps, $teeth_subsidy_eveluate, $teeth_region_eveluate, $TOOTH_NUMBERS_ISO;

    $region_arr = [];

    
    if(! is_interdental_gap($schema)) {
        return FALSE;
    }

    if(biggest_interdental_gap($teeth_inter_dental_gaps) == 3
    ) {
        foreach($teeth_inter_dental_gaps as $key => $value) {
            array_push($region_arr, $key);
            array_push($region_arr, $value);
        }

        array_push($teeth_subsidy_eveluate, 
                // ["subsidy"=> "2.3", "output_order"=> "11", "region"=> $region_arr[0] ."-". end($region_arr), "quantity"=> "1", "applied_rule"=> "Biggest Interdental GapExactToBeReplaced3"]
                ["subsidy"=> "2.3", "output_order"=> "11", "region"=> $region_arr[0] ."-". $region_arr[1], "quantity"=> "1", "applied_rule"=> "Biggest Interdental GapExactToBeReplaced3"]
        );

        for($v= position_schema($region_arr[0]); $v<= position_schema($region_arr[1]); $v++) {
            if(veneering_grants($TOOTH_NUMBERS_ISO[$v])) {
                array_push($teeth_subsidy_eveluate, 
                    ["subsidy"=> "2.7", "output_order"=> "15", "region"=> $TOOTH_NUMBERS_ISO[$v], "quantity"=> "1", "applied_rule"=> "Veneering grants 2.x 15"]
                );
            }
        }
    }
}


function BiggestInterdentalGapExactToBeReplaced2($schema) {
    global $teeth_inter_dental_gaps, $teeth_subsidy_eveluate, $teeth_region_eveluate, $TOOTH_NUMBERS_ISO;

    $region_arr = [];
    
    if(! is_interdental_gap($schema)) {
        return FALSE;
    }

    if(biggest_interdental_gap($teeth_inter_dental_gaps) == 2
    ) {
        foreach($teeth_inter_dental_gaps as $key => $value) {
            array_push($region_arr, $key);
            array_push($region_arr, $value);
        }
        // var_dump($region_arr);
        array_push($teeth_subsidy_eveluate, 
                // ["subsidy"=> "2.2", "output_order"=> "12", "region"=> $region_arr[0] ."-". end($region_arr), "quantity"=> "1", "applied_rule"=> "BiggestInterdentalGapExactToBeReplaced2 5"]
                ["subsidy"=> "2.2", "output_order"=> "12", "region"=> $region_arr[0] ."-". $region_arr[1], "quantity"=> "1", "applied_rule"=> "BiggestInterdentalGapExactToBeReplaced2 5"]
        );

        for($v= position_schema($region_arr[0]); $v<= position_schema($region_arr[1]); $v++) {
            if(veneering_grants($TOOTH_NUMBERS_ISO[$v])) {
                array_push($teeth_subsidy_eveluate, 
                    ["subsidy"=> "2.7", "output_order"=> "15", "region"=> $TOOTH_NUMBERS_ISO[$v], "quantity"=> "1", "applied_rule"=> "Veneering grants 2.x 16"]
                );
            }
        }
    }
}

function BiggestInterdentalGapExactToBeReplaced1($schema) {
    global $teeth_inter_dental_gaps, $teeth_subsidy_eveluate, $teeth_region_eveluate;

    $region_arr = [];
    
    if(! is_interdental_gap($schema)) {
        return FALSE;
    }

    if(subsidy_exists_name('BilateralFreeEnd_upper') OR subsidy_exists(2.1)) {
        return FALSE;
    }

    // if(biggest_interdental_gap($teeth_inter_dental_gaps) == 1) {
    //    foreach($teeth_inter_dental_gaps as $key => $value) {
    //        array_push($region_arr, $key);
    //        array_push($region_arr, $value);
    //    }
    //     array_push($teeth_subsidy_eveluate, 
    //             ["subsidy"=> "2.1", "region"=> $region_arr[0] ."-". end($region_arr), "quantity"=> "1", "applied_rule"=> "Biggest InterdentalGapExactToBeReplaced1"]
    //     );
    // }
}

function gap_closure($schema) {
    global $TOOTH_NUMBERS_ISO, $is_gap_closure, $is_gap_closure_arr;
    $gap_count = 0;
    $teeth_status = [];
    $temp_schema = [];

    foreach($schema as $tooth => $status) {
        if($status !== ')(' ) {
            array_push($teeth_status, $status);
            // $is_gap_closure = 'Y';
            // array_push($is_gap_closure_arr, $tooth);
        }
        else {
            $is_gap_closure = 'Y';
            array_push($is_gap_closure_arr, $tooth);
        }
    }

    //make status again 32 array
    // if(count($teeth_status) > 0) {
        $gap_count = 32 - count($teeth_status);
        for($i=0; $i<$gap_count; $i++) {
            array_push($teeth_status, '');
        }

        //create the new schema array
        for($i=0; $i<count($TOOTH_NUMBERS_ISO); $i++) {
            $temp_schema[$TOOTH_NUMBERS_ISO[$i]] = $teeth_status[$i];
        }
    // }

    // if(count($temp_schema) == 32) {
    //     $schema = $temp_schema;
    // }

    return $temp_schema;
}


?>