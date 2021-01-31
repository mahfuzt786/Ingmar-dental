<?php

// $schema = ['f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
// $TOOTH_NUMBERS_ISO = array(
//     18, 17, 16, 15, 14, 13, 12, 11,
//     21, 22, 23, 24, 25, 26, 27, 28,
//     38, 37, 36, 35, 34, 33, 32, 31,
//     41, 42, 43, 44, 45, 46, 47, 48
// );

// $returnTooth = array_map(NULL, $TOOTH_NUMBERS_ISO, $schema);

// var_dump($returnTooth);


// $front_end = [['18', 'pw'], ['17', ''], ['16', ''], ['15', ''], ['14', 'f'], ['13', ''], ['12', ''], ['11', ''],
//     ['21', ''], ['22', ''], ['23', ''], ['24', 'ww'], ['25', ''], ['26', ''], ['27', ''], ['28', ''],
//     ['38', ''], ['37', ''], ['36', ''], ['35', ''], ['34', ''], ['33', ''], ['32', ''], ['31', 'x'],
//     ['41', ''], ['42', ''], ['43', 'x'], ['44', ''], ['45', ''], ['46', 'ww'], ['47', 'f'], ['48', 'x']];


$front_end = [18 => 'pw', 17 => '', 16 => '', 15 => '', 14 => 'f', 13 => '', 12 => '', 11 => '',
    21 => '', 22 => '', 23 => '', 24 => 'ww', 25 => '', 26 => '', 27 => '', 28 => '',
    38 => '', 37 => '', 36 => '', 35 => '', 34 => '', 33 => '', 32 => '', 31 => 'x',
    41 => '', 42 => '', 43 => 'x', 44 => '', 45 => '', 46 => 'ww', 47 => 'f', 48 => 'x'];


// var_dump($front_end[0]);

$teeth_with_status = [];
$teeth_region_eveluate = [];
$teeth_subsidy_eveluate = [];

// main_input($front_end);
function main_input($schema) {

    global $teeth_with_status, $teeth_region_eveluate;

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

    print_r($teeth_with_status);
    // if(is_whole_mouth($teeth_with_status) OR is_upper_jaw($teeth_with_status)
    //     OR is_upper_jaw_left_end($teeth_with_status) OR is_upper_jaw_right_end($teeth_with_status) 
    //     OR is_upper_jaw_front($teeth_with_status) OR is_mandible($teeth_with_status) 
    //     OR is_mandible_left_end($teeth_with_status) OR is_mandible_right_end($teeth_with_status)
    //     OR is_mandible_front($teeth_with_status)) {
    //         array_push($teeth_region_eveluate, 'standalone teeth');
    // }

    is_whole_mouth($teeth_with_status);
    is_upper_jaw($teeth_with_status);
    is_upper_jaw_left_end($teeth_with_status);
    is_upper_jaw_right_end($teeth_with_status);
    is_upper_jaw_front($teeth_with_status);
    is_mandible($teeth_with_status);
    is_mandible_left_end($teeth_with_status);
    is_mandible_right_end($teeth_with_status);
    is_mandible_front($teeth_with_status);

    print_r($teeth_region_eveluate);

    // return '';
    echo json_encode($teeth_subsidy_eveluate);
}

function sub_array_teeth($start, $end, $size) {
    // $teeth_region = array_slice($GLOBALS['TOOTH_NUMBERS_ISO'], $start, $size, TRUE);
    $teeth_region = array_slice($GLOBALS['teeth_with_status'], $start, $size);
    
    echo nl2br("\n");

    // var_dump($teeth_region);

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
        array_push($teeth_region_eveluate, 'upper jaw');
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
        array_push($teeth_region_eveluate, 'upper jaw left end');
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
        array_push($teeth_region_eveluate, 'upper jaw right end');
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
        array_push($teeth_region_eveluate, 'upper jaw front');
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
        array_push($teeth_region_eveluate, 'mandible left end');
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
        array_push($teeth_region_eveluate, 'mandible right end');
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