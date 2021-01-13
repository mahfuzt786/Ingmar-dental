<?php
/*"""
All aspects related to subsidies return are hosted here.
"""*/
// import types
// from copy import deepcopy

// from apps.therapies.subsidy.utils import TeethNumbersList
require_once('utils.php');
use function lib\vendor\myclabs\deepcopy\src\DeepCopy\deep_copy;


class SubsidiesList {
    /*"""
    The subsidies list that is returned by the main function, returns this object.
    It have some simple and utils methods to simplify identifying/getting
    information inside subsidy list.
    """*/

    public $list;

    function __construct($list) {
    	$this->list = $list;
  	}

    function exists($subsidy_code_startswith=NULL, $region=NULL) {
        /*"""
        Check if there is some subsidy code filtering by something user wants.
        """*/
        foreach ($this->list as $subsidy) {
            if ($subsidy["subsidy"].startswith($subsidy_code_startswith)) {
                if ($region !== NULL) {
                    if (in_array($subsidy["region"], $region)) {
                        return True;
                    }
                }
                else {
                    return True;
                }
            }
        }
        return False;
    }

    function teeth($subsidy_code_startswith=[], $subsidy_code=[]) {
        /*"""
        Get teeth from subsidies filtering by something user wants.
        """*/
        $teeth_with_code = [];

        // Standardize to lists to simplify interface use
        if (! is_array($subsidy_code_startswith))
            $subsidy_code_startswith = [$subsidy_code_startswith];

        if (! is_array($subsidy_code))
            $subsidy_code = [$subsidy_code];

        foreach (self as $subsidy) {
            foreach ($subsidy_code_startswith as $item_subsidy_code_startswith) {
                if (! $subsidy->subsidy->startswith(
                    $item_subsidy_code_startswith)) {
                    continue;
                }
                array_push($teeth_with_code, $subsidy["region"]);
                // Tooth can only be added once
                break;
            }

            foreach ($subsidy_code as $item_subsidy_code) {
                if (! $subsidy["subsidy"] == $item_subsidy_code) {
                    continue;
                }
                // $teeth_with_code.extend(subsidy["region"]);
                array_push($teeth_with_code, $subsidy["region"]);

                // Tooth can only be added once
                break;
            }
        }

        return $teeth_with_code;
    }

    function group($include_optionals=False) {
        /*"""
        Return the subsidy groups in the way it is expected by the other
        software, as a standard python object.
        """*/
        $item = array(
            "subsidy" => NULL,
            "regions"=> [],
            "count"=> 0,
            "applied_rules"=> [],
            "optional"=> False,
        );
        $subsidy_groups = [];

        foreach(self as $subsidy) {
            if (! $include_optionals and $subsidy.get("optional", False))
                continue;

            $result = $subsidy_groups.get($subsidy["subsidy"], deep_copy($item));
            $subsidy_groups[$subsidy["subsidy"]] = $result;
            $result["subsidy"] = $subsidy["subsidy"];
            array_push($result["applied_rules"], $subsidy["applied_rule"]);
            // Transforming Tooth into numbers
            if (len($subsidy["region"]) == 1)
                array_push($result["regions"], $subsidy["region"][0].number);
            else {
                $iNumber = [];
                foreach ($subsidy["region"] as $i) {
                    array_push($iNumber, $i->number);
                }
                array_push($result["regions"], [$iNumber]);
            }

            $result["optional"] = $subsidy.get("optional", False);
            // We count the quantity of times that the same subsidy
            // appears for different regions, that is, the ammount of cases
            // that will have it.
            $result["count"] += 1;
        }

        // Creating teeth groups and rules representation
        foreach ($subsidy_groups as $key) {
            $subsidy_group = $subsidy_groups[$key];

            $regions = [];

            // We can have just one tooth or a list representing a tooth
            // region
            if (is_int($subsidy_group["regions"][0]))
                $regions = TeethNumbersList($subsidy_group["regions"]).sort();
            else {
                foreach ($subsidy_group["regions"] as $region)
                    array_push($regions, TeethNumbersList($region)->group());
                // Regions comes like: [[18, 28], [34, 35]], we just order it
                // by using the teeth list order, considering the first tooth
                // number of the list as the most important informantion
                $regions.sort($key=TeethNumbersList()->position(x[0]));
            }

            $subsidy_group["regions"] = $regions;

            $rules_output = [];//set()

            foreach ($subsidy_group["applied_rules"] as $rule) {
                if ($rule) {
                    // Get a good representation for the rules that are functions
                    array_push($rules_output, $rule->__qualname__);
                }
                else {
                    array_push($rules_output, str($rule));
                }
            }
            $subsidy_group["applied_rules"] = ",".join($rules_output);
        }

        return $subsidy_groups;
    }

    function output($include_optionals=False) {
        /*"""
        Return the subsidy groups in the way it is expected by the other
        software, all the subsidy regions being strings.
        """*/
        $subsidy_groups = $this->group($include_optionals=$include_optionals);

        foreach($subsidy_groups->items() as  $key => $value) {
            $subsidy_groups[$key]["regions"] = $this->_region_to_str(
                $value["regions"]
            );
        }
        return $subsidy_groups;
    }

    function subsidies() {
        $output = [];
        foreach ($this->output() as $i)
            array_push($output, $i);
        return $output;
    }

    // function rvs($include_optionals=False) {
    //     /*"""
    //     Return a list of the RV of all tooth.
    //     """*/
    //     $rvs = [set()] * TeethNumbersList()->max_length;

    //     for subsidy in self
    //         if not include_optionals and subsidy.get("optional", False)
    //             continue

    //         for tooth in subsidy["region"]
    //             index = TeethNumbersList.position(tooth.number)
    //             rvs[index] = rvs[index].union(tooth.rv)

    //     return [
    //         {"tooth_number": number, "rv": rv}
    //         for number, rv in zip(TeethNumbersList.range(18, 48), rvs)
    //     ]
    // }

    function _region_to_str($regions) {
        /*"""
        Returns the string representation of a region.
        """*/
        // Transforming the python objects generated as output in
        // something exactly as expected in the worksheet
        $result = [];

        foreach($regions as $region) {
            // Whole mouth
            if ($region == [18, 48])
                array_push($result, "OK,UK");
            // Upper jaw
            else if ($region == [18, 28])
                array_push($result, "OK");
            // Mandible
            else if ($region == [38, 48])
                array_push($result, "UK");
            else if (is_int($region))
                array_push($result, implode(', ', $region));
            else if (is_array($region))
                array_push($result, $region[0].'-'.$region[1]);
        }

        return $result;
    }
}
