<?php
// Useful functions for the subsidy package.

require_once('constants.php');
require_once('schema.php');


function startswith ($string, $startString) 
{ 
    $len = strlen($startString); 
    return (substr($string, 0, $len) === $startString); 
}


function endswith($string, $endString) { 
    $len = strlen($endString); 
    if ($len == 0) { 
        return true; 
    } 
    return (substr($string, -$len) === $endString); 
}


function tuple_to_schema($data) {
    $TOOTH_NUMBERS_ISO = array(
        array(18, 17, 16, 15, 14, 13, 12, 11),
        array(21, 22, 23, 24, 25, 26, 27, 28),
        array(38, 37, 36, 35, 34, 33, 32, 31),
        array(41, 42, 43, 44, 45, 46, 47, 48)
    );

    // Converts a tuple with findings and status in order of teeth numbers
    // to a schema.

    // from apps.therapies.subsidy.schema import Tooth, TeethSchema

    if (! is_array($data)) {
        throw new Exception("Error Processing as data parameter");
        // raise ValueError("Inform a tuple or a list as data parameter");
    }

    if (sizeof($data) !== 32) {
        throw new Exception("Each one of the 32 teeth should be represented in the tuple");
    }

    $returnTooth = [];
    foreach(array_map(NULL, $TOOTH_NUMBERS_ISO, $data) as $number => $status) {
        // print_r($status);
        // echo nl2br("\n");
        array_push($returnTooth, new Tooth($number, $status));
    }

    // var_dump(array_map(NULL, $TOOTH_NUMBERS_ISO, $data));
    echo nl2br("\n\n\n\n");

    return new TeethSchema($returnTooth);
}


function patient_therapy_to_schema($therapy) {
    // Converts a PatientTherapy model to a schema.
    // from apps.therapies.subsidy.schema import Tooth, TeethSchema

    $returnTooth = [];
    foreach($therapy->findings.all().order_by("tooth__order") as $finding) {
        array_push($returnTooth, Tooth(
            $finding->tooth->number, $finding->code_status, $finding->code_finding
        ));
    }

    return TeethSchema($returnTooth);
}


class TeethNumbersList {
    /*"""
    Represents a list of teeth numbers with some helpers related to it.

    TODO: This list is WIP and have to receive some improvements,
    such as allowing only valid numbers in it.
    """*/

    public $max_length; // = sizeof($TOOTH_NUMBERS_ISO);

    public $list;

    function __construct($list=NULL, $max_length=32) {
    	$this->list = $list;
    	$this->max_length = 32; //sizeof($TOOTH_NUMBERS_ISO);
  	}

    public static function previous($number) {
        /*"""
        Return the previous (clockwise ISO format) tooth number for a given tooth number.
        """*/
        $number_index = $TOOTH_NUMBERS_ISO.index($number);

        if ($number_index == 0) {
            return NULL;
        }

        return $TOOTH_NUMBERS_ISO[$number_index - 1];
    }

    public static function next($number) {
        /*"""
        Return the next (clockwise ISO format) tooth number for a given tooth number.
        """*/
        $number_index = $TOOTH_NUMBERS_ISO.index($number);

        if ($number_index == len($TOOTH_NUMBERS_ISO) - 1) {
            return NULL;
        }

        return $TOOTH_NUMBERS_ISO[$number_index + 1];
    }

    public static function range($start, $end) {
        /*"""
        Generate a range of teeth considering the ISO format
        representation.
        """*/
        $TOOTH_NUMBERS_ISO = array(
            array(18, 17, 16, 15, 14, 13, 12, 11),
            array(21, 22, 23, 24, 25, 26, 27, 28),
            array(38, 37, 36, 35, 34, 33, 32, 31),
            array(41, 42, 43, 44, 45, 46, 47, 48)
        );

        $start_index = array_search($start, $TOOTH_NUMBERS_ISO); //$TOOTH_NUMBERS_ISO.index($start);
        $end_index = array_search($end, $TOOTH_NUMBERS_ISO); //$TOOTH_NUMBERS_ISO.index($end);

        if (! in_array($start, $TOOTH_NUMBERS_ISO)) {
            // raise ValueError(f"Invalid region start tooth number: {start}");
            sprintf("Invalid region start tooth number: %s", $start);
        }

        if (! in_array($end, $TOOTH_NUMBERS_ISO)) {
            // raise ValueError(f"Invalid region end tooth number: {end}");
            sprintf("Invalid region end tooth number: %s", $end);

        }

        if ($start_index > $end_index) {
            throw new Exception(
                "Region should be created in clockwise order. Invalid representation.");
        }

        $teeth = [];
        $valid = False;

        foreach($TOOTH_NUMBERS_ISO as $i) {
            if ($i == $start) {
                $valid = True;
            }

            if ($valid) {
                $teeth.append($i);
            }

            if ($i == $end) {
                $valid = False;
            }
        }

        return new TeethNumbersList($teeth);
    }

    public static function position($tooth_number) {
        return array_search($tooth_number, $TOOTH_NUMBERS_ISO); //$TOOTH_NUMBERS_ISO.index($tooth_number);
    }

    function sort() {
        /*"""
        Given a list of teeth numbers or teeth intervals (in the format "18-14" or in
        the format [18, 14]), return it in order, following the ISO standard.
        """*/

        function key($value) {
            if (is_string($value)) {
                // When it is applied to the whole mouth, we just order
                // OK (upper jaw) first of UK (mandible)
                if (in_array($value, ["OK", "UK"])) {
                    if ($value == "OK") {
                        return 0;
                    }
                    return 1;
                }

                if ($value.find("-") != -1) {
                    // The interval case, for example 18-14. We just check the order
                    // by the start of the sequence
                    $value = int($value.split("-")[0]);
                }
                else {
                    // The case where we received an int as string
                    $value = int($value);
                }
            }

            if (is_array($value)) {
                $value = int($value[0]);
            }

            return $TOOTH_NUMBERS_ISO.index(int($value));
        }

        return TeethNumbersList(sorted(self, $key=$key));
    }

    function group() {
        /*"""
        From a list of teeth numbers, try to find at least 3 teeth that are
        side by side and, after finding them, generate a group to represent it.
        Teeth that are not part of any group are just returned alone.
        """*/
        $teeth_in_group = 1;
        $start = NULL;
        $end = NULL;
        $groups = TeethNumbersList();

        if (len(self) <= 2)
            return $this->sort();

        foreach(array_values(self) as  $i => $_) {
            $not_last_index = $i != len(self) - 1;
            // Our goal is to see if the teeth are contiguous in the mouth or not.
            // Notice that you can check if one tooth is side by side of other,
            // by just checking the index absolute difference between them.
            $contiguous_teeth = False;
            if ($not_last_index) {
                $contiguous_teeth = (
                    abs(
                        $TOOTH_NUMBERS_ISO.index(self[$i])
                        - $TOOTH_NUMBERS_ISO.index(self[$i + 1])
                    ) == 1
                );
            }

            if ($contiguous_teeth) {
                $end = self[$i + 1];
                if (! $start) {
                    $start = self[$i];
                    $teeth_in_group = 2;
                }
                else {
                    $teeth_in_group += 1;
                }
            }
            else {
                if ($teeth_in_group >= 3) {
                    array_push($groups, [$start, $end]);
                }
                else {
                    array_push($groups, array_slice(self[$i + 1 - $teeth_in_group], self[$i + 1]));
                }

                $start = NULL;
                $end = NULL;
                $teeth_in_group = 1;
            }
        }
        return $groups.sort();
    }
}

function memoize($func) {
    /*"""
    Decorator that caches results of a function.
    """*/
    $cache = dict();

    // function memoized_func(*$args) {
    function memoized_func($args) {
        if (in_array($args, $cache)) {
            return $cache[$args];
        }
        $result = func($args);
        $cache[$args] = $result;
        return $result;
    }

    return $memoized_func;
}


function subsidy_equivalent($subsidy_group_left, $subsidy_group_right) {
    /**"""
    Check if one subsidy_left and subsidy_right are equivalent.

    Possible cases:
       "1.1" = "1.1" -> True
       "1.x" = "1.1" -> True
       "1.1" = "1.x" -> True
       "1.x" = "1.x" -> True
       "2.1+1.1" = "2.1+1.1" -> True
       "2.1+1.1" = "2.x+1.1" -> True
       "2.1+1.1" = "2.1+1.x" -> True
    """**/
    if ($subsidy_group_left == $subsidy_group_right) {
        return True;
    }

    $subsidy_list_left = $subsidy_group_left.split("+");
    $subsidy_list_right = $subsidy_group_right.split("+");

    // Number of subsidies are different
    if (len($subsidy_list_left) != len($subsidy_list_right)) {
        return False;
    }

    foreach (array_map(NULL, $subsidy_list_left, $subsidy_list_right) as $subsidy_left => $subsidy_right) {
        if (in_array("x", $subsidy_right)) {
            // if (f"{subsidy_left.split('.')[0]}.x" != $subsidy_right) {
            if ($subsidy_left.split('.')[0].x != $subsidy_right) {
                return False;
            }
        }

        if (in_array("x", $subsidy_left)) {
            // if (f"{subsidy_right.split('.')[0]}.x" != $subsidy_left) {
            if ($subsidy_right.split('.')[0].x != $subsidy_left) {
                return False;
            }
        }

        if (! in_array("x", $subsidy_left) AND ! in_array("x",$subsidy_right)) {
            if ($subsidy_left != $subsidy_right) {
                return False;
            }
        }
    }

    return True;
}
