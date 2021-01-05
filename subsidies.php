<?php
/*"""
All aspects related to subsidies return are hosted here.
"""*/
// import types
// from copy import deepcopy

// from apps.therapies.subsidy.utils import TeethNumbersList
require_once('utils.php');


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
        if not isinstance(subsidy_code_startswith, list)
            $subsidy_code_startswith = [$subsidy_code_startswith];

        if not isinstance(subsidy_code, list)
            subsidy_code = [subsidy_code]

        for subsidy in self{
            for item_subsidy_code_startswith in subsidy_code_startswith {
                if not subsidy["subsidy"].startswith(
                    item_subsidy_code_startswith) {
                    continue;
                }
                $teeth_with_code.extend(subsidy["region"]);
                // Tooth can only be added once
                break;
            }

            for item_subsidy_code in subsidy_code {
                if not subsidy["subsidy"] == item_subsidy_code {
                    continue
                }
                $teeth_with_code.extend(subsidy["region"]);
                // Tooth can only be added once
                break;
            }
        }

        return $teeth_with_code;
    }

    function group(self, include_optionals=False) {
        /*"""
        Return the subsidy groups in the way it is expected by the other
        software, as a standard python object.
        """*/
        item = {
            "subsidy": NULL,
            "regions": [],
            "count": 0,
            "applied_rules": [],
            "optional": False,
        }
        subsidy_groups = {}

        for subsidy in self
            if not include_optionals and subsidy.get("optional", False)
                continue

            result = subsidy_groups.get(subsidy["subsidy"], deep_copy(item))
            subsidy_groups[subsidy["subsidy"]] = result
            result["subsidy"] = subsidy["subsidy"]
            result["applied_rules"].append(subsidy["applied_rule"])
            // Transforming Tooth into numbers
            if len(subsidy["region"]) == 1
                result["regions"].append(subsidy["region"][0].number)
            else
                result["regions"].append([i.number for i in subsidy["region"]])

            result["optional"] = subsidy.get("optional", False)
            // We count the quantity of times that the same subsidy
            // appears for different regions, that is, the ammount of cases
            // that will have it.
            result["count"] += 1

        // Creating teeth groups and rules representation
        for key in subsidy_groups
            subsidy_group = subsidy_groups[key]

            regions = []

            // We can have just one tooth or a list representing a tooth
            // region
            if isinstance(subsidy_group["regions"][0], int)
                regions = TeethNumbersList(subsidy_group["regions"]).sort()
            else
                for region in subsidy_group["regions"]
                    regions.extend(TeethNumbersList(region).group())
                // Regions comes like: [[18, 28], [34, 35]], we just order it
                // by using the teeth list order, considering the first tooth
                // number of the list as the most important informantion
                regions.sort(key=lambda x: TeethNumbersList.position(x[0]))

            subsidy_group["regions"] = regions

            rules_output = set()

            for rule in subsidy_group["applied_rules"]
                if isinstance(rule, types.FunctionType)
                    // Get a good representation for the rules that are functions
                    rules_output.add(rule.__qualname__)
                else
                    rules_output.add(str(rule))
            subsidy_group["applied_rules"] = ",".join(rules_output)

        return subsidy_groups;
    }

    function output(self, include_optionals=False) {
        /*"""
        Return the subsidy groups in the way it is expected by the other
        software, all the subsidy regions being strings.
        """*/
        subsidy_groups = $this->group(include_optionals=include_optionals)

        for key, value in subsidy_groups.items()
            subsidy_groups[key]["regions"] = $this->_region_to_str(
                value["regions"]
            )
        return subsidy_groups;
    }

    function subsidies(self) {
        return {i for i in $this->output()}
    }

    function rvs(self, include_optionals=False) {
        /*"""
        Return a list of the RV of all tooth.
        """*/
        rvs = [set()] * TeethNumbersList.max_length

        for subsidy in self
            if not include_optionals and subsidy.get("optional", False)
                continue

            for tooth in subsidy["region"]
                index = TeethNumbersList.position(tooth.number)
                rvs[index] = rvs[index].union(tooth.rv)

        return [
            {"tooth_number": number, "rv": rv}
            for number, rv in zip(TeethNumbersList.range(18, 48), rvs)
        ]
    }

    function _region_to_str(self, regions) {
        /*"""
        Returns the string representation of a region.
        """*/
        // Transforming the python objects generated as output in
        // something exactly as expected in the worksheet
        result = []

        for region in regions
            // Whole mouth
            if region == [18, 48]
                result.append("OK,UK")
            // Upper jaw
            elseif region == [18, 28]
                result.append("OK")
            // Mandible
            elseif region == [38, 48]
                result.append("UK")
            elseif isinstance(region, int)
                result.append(str(region))
            elseif isinstance(region, list)
                result.append(f"{region[0]}-{region[1]}")

        return ",".join(result);
    }
}
