<?php
// Rules for the subsidy that are evaluated by tooth.

// from apps.therapies.subsidy.regions import Region, RegionGroup
// from apps.therapies.subsidy.interfaces import RuleByToothInteface

// from apps.therapies.subsidy.rv import rv_subsidy_1x
require_once('regions.php');
require_once('interfaces.php');
require_once('rv.php');


class StatusPwInPosteriorRegion {
    // pw in 14-18, 24-28 (posterior region)

    public $RuleByToothInteface;

    function execute_for($tooth) {
        if (! in_array($tooth, RegionGroup(
            Region(18, 14, $this->schema),
            Region(24, 28, $this->schema),
            Region(38, 34, $this->schema),
            Region(44, 48, $this->schema),
        ).get_teeth_with_condition("pw"))) {
            return False;
        }

        // No 1.2 can happen with a 3.2 subsidy, we just return it as an
        // optional because dentist can choose it in optional_subsidy_group
        // rules
        // Issue #427: make it also be applied for 4.1/4.3 cases
        if (in_array($tooth, $this->identified_subsidies.teeth(
            $subsidy_code=["3.2", "4.1", "4.3"]))) {
            array_push($this->identified_subsidies, [
                    "subsidy" => "1.2",
                    "region" => [$tooth],
                    "applied_rule" => self,
                    "optional" => True,
                ]
            );
            // Optional rules do not stop the execution cicle
            return False;
        }

        // Issue #261: if is an abutment tooth and a 2.1 is given for
        // this tooth, it can not receive a 1.X
        if (in_array($this->identified_subsidies.teeth($subsidy_code="2.1"), $tooth))
            return False;

        array_push($this->identified_subsidies,
            rv_subsidy_1x(
                ["subsidy"=> "1.2", "region"=> [$tooth], "applied_rule"=> self]
            )
        );
        return True;
    }
}

$status_pw_in_posterior_region = new StatusPwInPosteriorRegion();


class ToBeTreatedWithNoAbutmentTeethIncluded {
    // ww / ur / tw / kw / rw (=TBTs) --> no Abutment teeth included
    // (e.g. a ww/kw next to a interdental gap leading to 2.X)!

    // Abutment Tooth (AT) A is the first AT in clockwise view from main gap,
    // AT B secound AT from main gap, ATC is alwasy belonging to neighbouring
    // gap (2.5)

    public $RuleByToothInteface;

    function execute_for($tooth) {
        if (! $tooth->to_be_treated) {
            return False;
        }

        // Assure that no abutment tooth included in 2.X will be identified by
        // this rule
        if (in_array($tooth, $this->identified_subsidies.teeth(
            $subsidy_code_startswith="2"))) {
            return False;
        }

        // No 1.1 can happen with a 3.2 subsidy, we just return it as an
        // optional because dentist can choose it in optional_subsidy_group
        // rules
        // Issue #427: make it also be applied for 4.1/4.3 cases
        if (in_array($tooth, $this->identified_subsidies.teeth(
            $subsidy_code=["3.2", "4.1", "4.3"]))) {
            array_push($this->identified_subsidies,
                [
                    "subsidy"=> "1.1",
                    "region"=> [$tooth],
                    "applied_rule"=> self,
                    "optional"=> True,
                ]
            );
            // Optional rules do not stop the execution cicle
            return False;
        }

        // This rule can not be applied over a 4.X one, so that
        // we filter for the tooth that are not in a 4.X region
        if (in_array($tooth, $this->identified_subsidies.teeth(
            $subsidy_code_startswith="4"))) {
            return False;
        }

        array_push($this->identified_subsidies,
            rv_subsidy_1x(
                ["subsidy"=> "1.1", "region"=> [$tooth], "applied_rule"=> self]
            )
        );
        return True;
    }
}

$to_be_treated_with_no_abutment_teeth_included = new ToBeTreatedWithNoAbutmentTeethIncluded();


class StatusPwInFrontRegion {
    // pw in 13-23 (front [anterior] region)

    public $RuleByToothInteface;

    function execute_for($tooth) {
        // if tooth not in RegionGroup(
        //     Region(13, 23, $this->schema), Region(33, 43, $this->schema)
        // ).get_teeth_with_condition("pw") {
        //     return False;
        // }

        // // No 1.1 can happen with a 3.2 subsidy, we just return it as an
        // // optional because dentist can choose it in optional_subsidy_group
        // // rules
        // // Issue #427: make it also be applied for 4.1/4.3 cases
        // if tooth in $this->identified_subsidies.teeth(
        //     $subsidy_code=["3.2", "4.1", "4.3"]
        // ) {
        //     array_push($this->identified_subsidies, 
        //         [
        //             "subsidy"=> "1.1",
        //             "region"=> [$tooth],
        //             "applied_rule"=> self,
        //             "optional"=> True,
        //         ]
        //     );
        //     // Optional rules do not stop the execution cicle
        //     return False;
        // }
        // // Issue #261: if is an abutment tooth and a 2.1 is given for
        // // this tooth, it can not receive a 1.X
        // if (in_array($tooth, $this->identified_subsidies.teeth($subsidy_code="2.1"))) {
        //     return False;
        // }

        // array_push($this->identified_subsidies, [
        //     rv_subsidy_1x(
        //         {"subsidy"=> "1.1", "region"=> [$tooth], "applied_rule"=> self}
        //     )]
        // );
        return True;
    }
}

$status_pw_in_front_region = new StatusPwInFrontRegion();


function evaluate_by_tooth($schema, $identified_subsidies) {
    // Apply the "by tooth" rules for a given schema and return the subsidy that
    // need to be used.

    $rules = [
        $status_pw_in_posterior_region,
        $to_be_treated_with_no_abutment_teeth_included,
        $status_pw_in_front_region,
    ];

    foreach($rules as $rule_to_execute) {
        if (rule_to_execute($schema, $identified_subsidies)) {
            return True;
        }
    }
}
