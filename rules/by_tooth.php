<?php
// Rules for the subsidy that are evaluated by tooth.

from apps.therapies.subsidy.regions import Region, RegionGroup
from apps.therapies.subsidy.interfaces import RuleByToothInteface

from apps.therapies.subsidy.rv import rv_subsidy_1x


class StatusPwInPosteriorRegion(RuleByToothInteface) {
    // pw in 14-18, 24-28 (posterior region)

    function execute_for(self, tooth):
        if tooth not in RegionGroup(
            Region(18, 14, self.schema),
            Region(24, 28, self.schema),
            Region(38, 34, self.schema),
            Region(44, 48, self.schema),
        ).get_teeth_with_condition("pw"):
            return False

        # No 1.2 can happen with a 3.2 subsidy, we just return it as an
        # optional because dentist can choose it in optional_subsidy_group
        # rules
        # Issue #427: make it also be applied for 4.1/4.3 cases
        if tooth in self.identified_subsidies.teeth(
            subsidy_code=["3.2", "4.1", "4.3"]
        ):
            self.identified_subsidies.append(
                {
                    "subsidy": "1.2",
                    "region": [tooth],
                    "applied_rule": self,
                    "optional": True,
                }
            )
            # Optional rules do not stop the execution cicle
            return False

        # Issue #261: if is an abutment tooth and a 2.1 is given for
        # this tooth, it can not receive a 1.X
        if tooth in self.identified_subsidies.teeth(subsidy_code="2.1"):
            return False

        self.identified_subsidies.append(
            rv_subsidy_1x(
                {"subsidy": "1.2", "region": [tooth], "applied_rule": self}
            )
        )
        return True
}

$status_pw_in_posterior_region = StatusPwInPosteriorRegion();


class ToBeTreatedWithNoAbutmentTeethIncluded(RuleByToothInteface) {
    // ww / ur / tw / kw / rw (=TBTs) --> no Abutment teeth included
    // (e.g. a ww/kw next to a interdental gap leading to 2.X)!

    // Abutment Tooth (AT) A is the first AT in clockwise view from main gap,
    // AT B secound AT from main gap, ATC is alwasy belonging to neighbouring
    // gap (2.5)

    function execute_for(self, tooth) {
        if not tooth.to_be_treated {
            return False;
        }

        // Assure that no abutment tooth included in 2.X will be identified by
        // this rule
        if tooth in self.identified_subsidies.teeth(
            subsidy_code_startswith="2") {
            return False;
        }

        // No 1.1 can happen with a 3.2 subsidy, we just return it as an
        // optional because dentist can choose it in optional_subsidy_group
        // rules
        // Issue #427: make it also be applied for 4.1/4.3 cases
        if tooth in self.identified_subsidies.teeth(
            subsidy_code=["3.2", "4.1", "4.3"]) {
            self.identified_subsidies.append(
                {
                    "subsidy": "1.1",
                    "region": [tooth],
                    "applied_rule": self,
                    "optional": True,
                }
            );
            // Optional rules do not stop the execution cicle
            return False;
        }

        // This rule can not be applied over a 4.X one, so that
        // we filter for the tooth that are not in a 4.X region
        if tooth in self.identified_subsidies.teeth(
            subsidy_code_startswith="4") {
            return False;
        }

        self.identified_subsidies.append(
            rv_subsidy_1x(
                {"subsidy": "1.1", "region": [tooth], "applied_rule": self}
            )
        );
        return True;
    }
}

$to_be_treated_with_no_abutment_teeth_included = (
    ToBeTreatedWithNoAbutmentTeethIncluded()
);


class StatusPwInFrontRegion(RuleByToothInteface) {
    // pw in 13-23 (front [anterior] region)

    function execute_for(self, $tooth):
        if tooth not in RegionGroup(
            Region(13, 23, self.schema), Region(33, 43, self.schema)
        ).get_teeth_with_condition("pw") {
            return False;
        }

        // No 1.1 can happen with a 3.2 subsidy, we just return it as an
        // optional because dentist can choose it in optional_subsidy_group
        // rules
        // Issue #427: make it also be applied for 4.1/4.3 cases
        if tooth in self.identified_subsidies.teeth(
            subsidy_code=["3.2", "4.1", "4.3"]
        ) {
            self.identified_subsidies.append(
                {
                    "subsidy": "1.1",
                    "region": [tooth],
                    "applied_rule": self,
                    "optional": True,
                }
            );
            // Optional rules do not stop the execution cicle
            return False;
        }
        // Issue #261: if is an abutment tooth and a 2.1 is given for
        // this tooth, it can not receive a 1.X
        if tooth in self.identified_subsidies.teeth(subsidy_code="2.1") {
            return False;
        }

        self.identified_subsidies.append(
            rv_subsidy_1x(
                {"subsidy": "1.1", "region": [tooth], "applied_rule": self}
            )
        );
        return True;
}

$status_pw_in_front_region = StatusPwInFrontRegion();


function evaluate_by_tooth($schema, $identified_subsidies) {
    // Apply the "by tooth" rules for a given schema and return the subsidy that
    // need to be used.

    $rules = [
        $status_pw_in_posterior_region,
        $to_be_treated_with_no_abutment_teeth_included,
        $status_pw_in_front_region,
    ];

    for rule_to_execute in rules {
        if rule_to_execute($schema, $identified_subsidies) {
            return True;
        }
    }
}
