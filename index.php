<?php
/**
 * Dental RESTfull Intermediary Layer API for Dental
 * @author Mahfuz <mahfuzz786@gmail.com>
 * @copyright (c) 2020-21, Alegra Labs <https://www.alegralabs.com>
 
 * The logics uses `regions` of teeth, its `status` and `findings`.
 * - Returns : subsidy value
 */

//Include dental Class
require 'subsidy_php/new.php';

if (isset($_GET['teeth_input'])) {
    main_input($_GET['teeth_input']);
}
else {
    echo json_encode(array("message" => "Please provide a proper teeth schema"));
}
