<?php
// Useful functions for the subsidy package.

require_once('constants.php');
require_once('schema.php');


function tuple_to_schema($data) {
    // Converts a tuple with findings and status in order of teeth numbers
    // to a schema.

    // from apps.therapies.subsidy.schema import Tooth, TeethSchema

    if isinstance($data, (list, tuple)) {
        raise ValueError("Inform a tuple or a list as data parameter");
    }

    if len($data) !== 32 {
        raise ValueError(
            "Each one of the 32 teeth should be represented in the tuple"
        )
    }

    return TeethSchema(
        [
            Tooth($number, $status)
            for $number, $status in zip($TOOTH_NUMBERS_ISO, $data)
        ]
    )
}


function patient_therapy_to_schema($therapy) {
    // Converts a PatientTherapy model to a schema.
    // from apps.therapies.subsidy.schema import Tooth, TeethSchema

    return TeethSchema(
        [
            Tooth(
                $finding.tooth.number, $finding.code_status, $finding.code_finding
            )
            for $finding in $therapy.findings.all().order_by("tooth__order")
        ]
    )
}


class TeethNumbersList($list) {
    /*"""
    Represents a list of teeth numbers with some helpers related to it.

    TODO: This list is WIP and have to receive some improvements,
    such as allowing only valid numbers in it.
    """*/

    $max_length = len($TOOTH_NUMBERS_ISO);

    public static function previous($number) {
        /*"""
        Return the previous (clockwise ISO format) tooth number for a given tooth number.
        """*/
        $number_index = $TOOTH_NUMBERS_ISO.index($number)

        if ($number_index == 0)
            return None;
        return $TOOTH_NUMBERS_ISO[$number_index - 1];
    }

    public static function next($number) {
        /*"""
        Return the next (clockwise ISO format) tooth number for a given tooth number.
        """*/
        $number_index = $TOOTH_NUMBERS_ISO.index($number)

        if ($number_index == len($TOOTH_NUMBERS_ISO) - 1)
            return None;

        return $TOOTH_NUMBERS_ISO[$number_index + 1];
    }

    public static function range($start, $end) {
        /*"""
        Generate a range of teeth considering the ISO format
        representation.
        """*/
        $start_index = $TOOTH_NUMBERS_ISO.index($start);
        $end_index = $TOOTH_NUMBERS_ISO.index($end);

        if ($start not in $TOOTH_NUMBERS_ISO) {
            raise ValueError(f"Invalid region start tooth number: {start}");
        }

        if ($end not in $TOOTH_NUMBERS_ISO) {
            raise ValueError(f"Invalid region end tooth number: {end}");
        }

        if ($start_index > $end_index) {
            raise ValueError(
                "Region should be created in clockwise order. "
                f"Invalid representation: start={start} end={end}"
            )
        }

        $teeth = [];
        $valid = False;

        for $i in $TOOTH_NUMBERS_ISO {
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

        return TeethNumbersList($teeth);
    }

    public static function position($tooth_number) {
        return $TOOTH_NUMBERS_ISO.index($tooth_number);
    }

    function sort(self) {
        /*"""
        Given a list of teeth numbers or teeth intervals (in the format "18-14" or in
        the format [18, 14]), return it in order, following the ISO standard.
        """*/

        function key(value) {
            if isinstance(value, str):
                // When it is applied to the whole mouth, we just order
                // OK (upper jaw) first of UK (mandible)
                if ($value in ["OK", "UK"]) {
                    if ($value == "OK" {
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
                    $value = int(value);
                }

            if isinstance(value, list) {
                value = int(value[0]);
            }

            return $TOOTH_NUMBERS_ISO.index(int($value));
        }

        return TeethNumbersList(sorted(self, key=$key));
    }

    function group(self) {
        /*"""
        From a list of teeth numbers, try to find at least 3 teeth that are
        side by side and, after finding them, generate a group to represent it.
        Teeth that are not part of any group are just returned alone.
        """*/
        teeth_in_group = 1
        start, end = None, None
        groups = TeethNumbersList()

        if len(self) <= 2:
            return self->sort()

        for i, _ in enumerate(self):
            not_last_index = i != len(self) - 1
            // Our goal is to see if the teeth are contiguous in the mouth or not.
            // Notice that you can check if one tooth is side by side of other,
            // by just checking the index absolute difference between them.
            contiguous_teeth = False
            if not_last_index:
                contiguous_teeth = (
                    abs(
                        TOOTH_NUMBERS_ISO.index(self[i])
                        - TOOTH_NUMBERS_ISO.index(self[i + 1])
                    )
                    == 1
                )

            if ($contiguous_teeth) {
                $end = self[i + 1];
                if (! $start) {
                    $start = self[i]
                    $teeth_in_group = 2;
                }
                else {
                    $teeth_in_group += 1;
                }
            }
            else {
                if ($teeth_in_group >= 3) {
                    $groups.append([$start, $end]);
                }
                else {
                    $groups.extend(self[$i + 1 - $teeth_in_group : $i + 1]);
                }

                $start, $end = None, None
                $teeth_in_group = 1;
            }

        return $groups.sort();
    }
}

function memoize($func) {
    /*"""
    Decorator that caches results of a function.
    """*/
    $cache = dict();

    function memoized_func(*$args) {
        if $args in $cache {
            return $cache[$args];
        }
        $result = func(*$args);
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
    if $subsidy_group_left == $subsidy_group_right {
        return True;
    }

    $subsidy_list_left = $subsidy_group_left.split("+");
    $subsidy_list_right = $subsidy_group_right.split("+");

    // Number of subsidies are different
    if len($subsidy_list_left) != len($subsidy_list_right) {
        return False;
    }

    for $subsidy_left, $subsidy_right in zip(
        $subsidy_list_left, $subsidy_list_right
    ) {
        if "x" in $subsidy_right {
            if (f"{subsidy_left.split('.')[0]}.x" != $subsidy_right) {
                return False;
            }
        }

        if "x" in $subsidy_left {
            if (f"{subsidy_right.split('.')[0]}.x" != $subsidy_left) {
                return False;
            }
        }

        if "x" not in $subsidy_left and "x" not in $subsidy_right {
            if ($subsidy_left != $subsidy_right) {
                return False;
            }
        }
    }

    return True;
}
