<?php
/**
 * Dental RESTfull Intermediary Layer API for Dental
 * @author Mahfuz <mahfuzz786@gmail.com>
 * @copyright (c) 2020-21, Alegra Labs <https://www.alegralabs.com>
 
 * The logics uses `regions` of teeth, its `status` and `findings`.
 * - Returns : subsidy value
 */

//Include dental Class
require_once('subsidy_php/new.php');

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// var_dump($_POST);

if (isset($_POST['data'])) {
    // print_r($_POST['data']);

    $json_array = json_decode($_POST['data'], true);
    $assoc_array = array();

    // print_r($json_array);

    // echo nl2br("\n\n");


    for($i = 0; $i < sizeof($json_array); $i++)
    {
        $key = $json_array[$i];

        // print_r($key);
        
        foreach($key as $teeth => $status) {
            $assoc_array[$teeth] = $status;
        }
    }
    

    // print_r($assoc_array);

    main_input($assoc_array);
}
else {
    echo json_encode(array("message" => "Please provide a proper teeth schema"));
}

?>
