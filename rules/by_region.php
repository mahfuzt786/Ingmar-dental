<?php
/*"""
Rules for the subsidy that are evaluated by region.
"""*/
from apps.therapies.subsidy.regions import Region, RegionGroup
from apps.therapies.subsidy.interfaces import (
    SeparatedJawRuleSimpleInterface,
    SeparatedJawRuleCompleteInterface,
)
from apps.therapies.subsidy.rv import (
    rv_subsidy_4x,
    rv_subsidy_3x,
    rv_subsidy_2x,
)


class Exact16ToBeReplacedTeeth(SeparatedJawRuleCompleteInterface) {
    /*"""
    TBR=16// (Count inclusive X8).
    """*/

    function upper_jaw(self) {
        to_be_replaced = $this->schema.to_be_replaced(region=Region.upper_jaw())
        region = $this->schema.get_teeth_in_region(Region.upper_jaw())
        if len(to_be_replaced) == 16 {
            $this->identified_subsidies.append(
                rv_subsidy_4x(
                    {"subsidy": "4.2", "region": region, "applied_rule": self}
                )
            )
            return True;
        }
    }

    function mandible(self) {
        to_be_replaced = $this->schema.to_be_replaced(region=Region.mandible())
        region = $this->schema.get_teeth_in_region(Region.mandible())
        if len(to_be_replaced) == 16 {
            $this->identified_subsidies.append(
                rv_subsidy_4x(
                    {"subsidy": "4.4", "region": region, "applied_rule": self}
                )
            )
            return True;
        }
    }
}


$exact_16_to_be_replaced_teeth = Exact16ToBeReplacedTeeth();


class Between13And15ToBeReplacedTeeth($SeparatedJawRuleCompleteInterface) {
    /*"""
    TBR=13-15// (Count inclusive X8).
    """*/

    function upper_jaw(self) {
        $to_be_replaced = $this->schema.to_be_replaced($region=Region.upper_jaw())
        $region = $this->schema.get_teeth_in_region(Region.upper_jaw())
        if 13 <= len($to_be_replaced) <= 15:
            $this->identified_subsidies.append(
                rv_subsidy_4x(
                    {"subsidy": "4.1", "region": $region, "applied_rule": self}
                )
            )
            return True;
    }

    function mandible(self) {
        $to_be_replaced = $this->schema.to_be_replaced($region=Region.mandible())
        $region = $this->schema.get_teeth_in_region(Region.mandible())
        if 13 <= len($to_be_replaced) <= 15:
            $this->identified_subsidies.append(
                rv_subsidy_4x(
                    {"subsidy": "4.3", "region": region, "applied_rule": self}
                )
            )
            return True;
    }
}


$between_13_and_15_to_be_replaced_teeth = Between13And15ToBeReplacedTeeth();


class UnilateralFreeEndToBeReplacedTeethAtLeast1(
    SeparatedJawRuleCompleteInterface
) {
    /*"""
    Unilateral free end (TBR at least 1x X6-X8) and TBR>=3

    Now when a free end happens in both sides
    it is also considered unilateral free end.
    """*/

    function upper_jaw(self) {
        /*"""
        Check for unilateral free end in the upper jaw
        """*/
        $unilateral_free_end = (
            $this->schema.upper_jaw_left_free_end
            or $this->schema.upper_jaw_right_free_end
        )

        if $unilateral_free_end {
            $this->identified_subsidies.append(
                rv_subsidy_3x(
                    {
                        "subsidy": "3.1",
                        "region": $this->schema.get_teeth_in_region(
                            Region.upper_jaw()
                        ),
                        "applied_rule": self,
                    }
                )
            )
            return True;
        }
    }

    function mandible(self) {
        /*"""
        Check for unilateral free end in the mandible
        """*/
        $unilateral_free_end = (
            $this->schema.mandible_left_free_end
            or $this->schema.mandible_right_free_end
        )

        if unilateral_free_end {
            $this->identified_subsidies.append(
                rv_subsidy_3x(
                    {
                        "subsidy": "3.1",
                        "region": $this->schema.get_teeth_in_region(
                            Region.mandible()
                        ),
                        "applied_rule": self,
                    }
                )
            )
            return True;
        }
    }
}


$unilateral_free_end_to_be_replaced_teeth_at_least_1 = (
    UnilateralFreeEndToBeReplacedTeethAtLeast1()
)


class UnilateralFreeEndToBeReplacedX7andX8(SeparatedJawRuleCompleteInterface) {
    /*"""
    Applied
    when we have a X7/X8 free end only. The reason is that when we will
    introduce optional answers, we will later make those 3.1 optional
    which base on X7/X8 only and have those required, which rely on X6/X7/X8,
    but for now, it means that we also calculate a 3.1 when ONLY the X7/X8
    is missing for the unilateral free end.
    """*/

    function _unilateral_free_end_upper_jaw(self) {
        /*"""
        Unilateral free end only X7/X8 missing.
        """*/
        right_end_tbr, left_end_tbr = False, False
        to_be_replaced_right = $this->schema.to_be_replaced(region=Region(27, 28))
        to_be_replaced_left = $this->schema.to_be_replaced(region=Region(18, 17))

        if len(to_be_replaced_right) == 2 {
            // Just consider free end being X7/X8, if one more tooth
            // is also TBR it will not be considered
            if not $this->schema.to_be_replaced(region=Region(26, 26)) {
                right_end_tbr = True;
            }
        }

        if len(to_be_replaced_left) == 2 {
            if not $this->schema.to_be_replaced(region=Region(16, 16)) {
                left_end_tbr = True
            }
        }

        // Avoiding bilateral free end
        return len(set([left_end_tbr, right_end_tbr])) == 2;
    }

    function _unilateral_free_end_mandible(self) {
        /*"""
        Unilateral free end only X7/X8 missing.
        """*/
        $right_end_tbr, left_end_tbr = False, False;
        $to_be_replaced_right = $this->schema.to_be_replaced(region=Region(38, 37));
        $to_be_replaced_left = $this->schema.to_be_replaced(region=Region(47, 48));

        if len(to_be_replaced_right) == 2:
            // Just consider free end being X7/X8, if one more tooth
            // is also TBR it will not be considered
            if not $this->schema.to_be_replaced(region=Region(36, 36)):
                right_end_tbr = True

        if len(to_be_replaced_left) == 2:
            if not $this->schema.to_be_replaced(region=Region(46, 46)):
                left_end_tbr = True

        // Avoiding bilateral free end
        return len(set([left_end_tbr, right_end_tbr])) == 2;
    }

    function upper_jaw(self) {
        /*"""
        Check for unilateral free end in the upper jaw
        """*/
        unilateral_free_end = $this->_unilateral_free_end_upper_jaw()

        // Assure that this subsidy will not be applied twice. Remember
        // that it is optional and, also, that it can happen in both sides
        // of the mouth
        if $this->identified_subsidies.exists(
            subsidy_code_startswith="3.1", region=Region.upper_jaw()
        ):
            return False

        if unilateral_free_end:
            $this->identified_subsidies.append(
                rv_subsidy_3x(
                    {
                        "subsidy": "3.1",
                        "region": $this->schema.get_teeth_in_region(
                            Region.upper_jaw()
                        ),
                        "applied_rule": self,
                        "optional": True,
                    }
                )
            )
            // Optional rules do not stop the execution cicle
            return False
    }

    function mandible(self) {
        /*"""
        Check for unilateral free end in the mandible
        """*/
        $unilateral_free_end = $this->_unilateral_free_end_mandible()

        // Assure that this subsidy will not be applied twice. Remember
        // that it is optional and, also, that it can happen in both sides
        // of the mouth
        if $this->identified_subsidies.exists(
            $subsidy_code_startswith="3.1", $region=Region.mandible()
        ):
            return False

        if unilateral_free_end {
            $this->identified_subsidies.append(
                rv_subsidy_3x(
                    {
                        "subsidy": "3.1",
                        "region": $this->schema.get_teeth_in_region(
                            Region.mandible()
                        ),
                        "applied_rule": self,
                        "optional": True,
                    }
                )
            )
            // Optional rules do not stop the execution cicle
            return False;
        }
    }
}

$unilateral_free_end_to_be_replaced_x7_and_x8 = (
    UnilateralFreeEndToBeReplacedX7andX8();
)


class Between5And12ToBeReplacedTeeth(SeparatedJawRuleCompleteInterface) {
    /*"""
    TBR=5-12// without counting X8
    """*/

    function upper_jaw(self):
        upper_jaw_without_x8 = Region(17, 27)

        to_be_replaced = $this->schema.to_be_replaced(region=upper_jaw_without_x8)

        if 5 <= len(to_be_replaced) <= 12:
            $this->identified_subsidies.append(
                rv_subsidy_3x(
                    {
                        "subsidy": "3.1",
                        "region": $this->schema.get_teeth_in_region(
                            Region.upper_jaw()
                        ),
                        "applied_rule": self,
                    }
                )
            )
            return True

    function mandible(self):
        mandible_without_x8 = Region(37, 47)
        to_be_replaced = $this->schema.to_be_replaced(region=mandible_without_x8)

        if 5 <= len(to_be_replaced) <= 12:
            $this->identified_subsidies.append(
                rv_subsidy_3x(
                    {
                        "subsidy": "3.1",
                        "region": $this->schema.get_teeth_in_region(
                            Region.mandible()
                        ),
                        "applied_rule": self,
                    }
                )
            )
            return True;
}


$between_5_and_12_to_be_replaced_teeth = Between5And12ToBeReplacedTeeth()


class Exact4ToBeReplacedTeethInterdentalGapWithAtLeastOneInPosteriorRegion(
    SeparatedJawRuleCompleteInterface
) {
    /*"""
    Interdental gap of TBR=4// with at least one TBR tooth in posterior region
    """*/

    function upper_jaw(self) {
        $interdental_gap = $this->schema.interdental_gap_with_to_be_replaced_count(
            Region.upper_jaw(), 4
        )

        if interdental_gap {
            for tooth in interdental_gap.to_be_replaced:
                if tooth in RegionGroup.upper_jaw_posterior_region():
                    $this->identified_subsidies.append(
                        rv_subsidy_3x(
                            {
                                "subsidy": "3.1",
                                "region": $this->schema.get_teeth_in_region(
                                    Region.upper_jaw()
                                ),
                                "applied_rule": self,
                            }
                        )
                    )
                    return True;
        }
    }

    function mandible(self) {
        $interdental_gap = $this->schema.interdental_gap_with_to_be_replaced_count(
            Region.mandible(), 4
        );

        if $interdental_gap {
            for ($interdental_gap->to_be_replaced as $tooth) {
                if tooth in RegionGroup.mandible_posterior_region() {
                    $this->identified_subsidies.append(
                        rv_subsidy_3x(
                            {
                                "subsidy": "3.1",
                                "region": $this->schema.get_teeth_in_region(
                                    Region.mandible()
                                ),
                                "applied_rule": self,
                            }
                        )
                    )
                    return True;
                }
            }
        }
    }
}


$exact_4_to_be_replaced_teeth_interdental_gap_with_at_least_one_in_posterior_region = (
    Exact4ToBeReplacedTeethInterdentalGapWithAtLeastOneInPosteriorRegion()
);


class Between2And3ToBeReplacedTeethInterdentalGapNeighboringAnotherGap(
    SeparatedJawRuleSimpleInterface) {
    /*"""
    Interdental gap TBR=2/3// and neighboring gap with TBR>1#
    """*/

    function execute_for(self, region) {
        $interdental_gaps = $this->schema.all_interdental_gaps()

        for interdental_gap in interdental_gaps {
            if 2 <= $interdental_gap.to_be_replaced_count <= 3 {
                $neighboring_gap = $this->schema.neighboring_gap($interdental_gap)
                if not neighboring_gap:
                    return False

                if not neighboring_gap.to_be_replaced_count > 1:
                    return False

                if (
                    neighboring_gap not in region
                    or interdental_gap not in region
                ):
                    return False

                $this->identified_subsidies.append(
                    rv_subsidy_3x(
                        {
                            "subsidy": "3.1",
                            "region": $this->schema.get_teeth_in_region(region),
                            "applied_rule": self,
                        }
                    )
                )
                return True;
            }
        }
    }
}


$between_2_and_3_to_be_replaced_teeth_interdental_gap_neighboring_another_gap = (
    Between2And3ToBeReplacedTeethInterdentalGapNeighboringAnotherGap()
);


class NeighboringInterdentalGapsCountRelativeToTheMainGapGreaterThan1(
    SeparatedJawRuleSimpleInterface
) {
    /*"""
    Neighboring interdental gaps relative to the main gap > 1.
    Notice that, just for this case, neighbors are considered on
    the right or on the left.
    """*/

    function execute_for($region) {
        $main_interdental_gap = $this->schema.main_interdental_gap($region);
        $all_gaps = $this->schema.all_interdental_gaps($region=$region)
        $neighbors = []
        $check_neighborhood = [$main_interdental_gap]

        // Search for gaps that are neighbors of all gaps already selected.
        // It can be on the right or on the left, so that the concept of
        // neighbor gap was changed here.
        while $check_neighborhood {
            $gap_collected = check_neighborhood.pop()
            for neighbor_gap_candidate in all_gaps {
                if gap_collected != neighbor_gap_candidate {
                    // Here we check for left and right
                    if gap_collected.is_neighbor_of(
                        neighbor_gap_candidate
                    ) or neighbor_gap_candidate.is_neighbor_of(gap_collected) {
                        if (neighbor_gap_candidate not in neighbors) {
                            neighbors.append(neighbor_gap_candidate)
                            check_neighborhood.append(neighbor_gap_candidate)
                        }
                    }
                }
            }
        }

        // Neighbors here include also the main_gap, that's the reason
        // we check for neighbors greater than 2 and not for greater than 1
        // only
        if len($neighbors) > 2 {
            $this->identified_subsidies.append(
                rv_subsidy_3x(
                    {
                        "subsidy": "3.1",
                        "region": $this->schema.get_teeth_in_region(region),
                        "applied_rule": self,
                    }
                )
            )
            return True
        }
    }
}


$neighboring_interdental_gaps_count_relative_to_the_main_gap_greater_than_1 = (
    NeighboringInterdentalGapsCountRelativeToTheMainGapGreaterThan1()
);


class BilateralFreeEnd(SeparatedJawRuleCompleteInterface) {
    /*"""
    Bilateral free end with TBR covering 18-14/15, 24/25-28
    (means: It could be e.g. 18-14 and 25-28)

    CAVEAT: this is a very specific case. The BilateralFreeEnd rule might
    return 3.1 and 3.2. The problem is that the region should be different
    for each one of the rules. As it is a VERY specific case only for this
    rule, we need to add two more 3.2 subsidies in each jaw.
    """*/

    function upper_jaw(self) {
        if (
            $this->schema.upper_jaw_left_free_end
            and $this->schema.upper_jaw_right_free_end
        ):
            $this->identified_subsidies.append(
                rv_subsidy_3x(
                    {
                        "subsidy": "3.1",
                        "region": $this->schema.get_teeth_in_region(
                            Region.upper_jaw()
                        ),
                        "applied_rule": self,
                    }
                )
            )
            tooth_32_left = $this->schema.upper_jaw_right_free_end.teeth[0].left
            tooth_32_right = $this->schema.upper_jaw_left_free_end.teeth[-1].right

            // Issue #133: we added the concept of free end for upper jaw being
            // just both sides in X6-X8, but when it happens, a 3.2 must not be
            // added, so that we limit 3.2 to not be applied when teeth are
            // 15 and 25 that is the teeth just after X6.
            if tooth_32_left.number == 25 or tooth_32_right.number == 15:
                return True

            // 1) For 3.2 we should return the first TBT tooth after the
            // free end. Notice that if the next tooth is a TBR it will not
            // receive a 3.2
            // 2) 3.2 can only exist together (see Issue #346 for reference)
            if tooth_32_left.to_be_replaced or tooth_32_right.to_be_replaced:
                return True

            $this->identified_subsidies.append(
                {
                    "subsidy": "3.2",
                    "region": [tooth_32_left],
                    "applied_rule": self,
                }
            )
            $this->identified_subsidies.append(
                {
                    "subsidy": "3.2",
                    "region": [tooth_32_right],
                    "applied_rule": self,
                }
            )
            return True
    }

    function mandible(self) {
        if (
            $this->schema.mandible_left_free_end
            and $this->schema.mandible_right_free_end
        ) {
            $this->identified_subsidies.append(
                rv_subsidy_3x(
                    {
                        "subsidy": "3.1",
                        "region": $this->schema.get_teeth_in_region(
                            Region.mandible()
                        ),
                        "applied_rule": self,
                    }
                )
            )

            tooth_32_left = $this->schema.mandible_left_free_end.teeth[0].left
            tooth_32_right = $this->schema.mandible_right_free_end.teeth[-1].right

            // Issues #133,#357: we added the concept of free end for upper jaw being
            // just both sides in X6-X8, but when it happens, a 3.2 must not be
            // added, so that we limit 3.2 to not be applied when teeth are
            // 15 and 25 that is the teeth just after X6.
            if tooth_32_left.number == 45 or tooth_32_right.number == 35:
                return True

            // 1) For 3.2 we should return the first TBT tooth after the
            // free end. Notice that if the next tooth is a TBR it will not
            // receive a 3.2
            // 2) 3.2 can only exist together (see Issue #346 for reference)
            if tooth_32_left.to_be_replaced or tooth_32_right.to_be_replaced:
                return True

            $this->identified_subsidies.append(
                {
                    "subsidy": "3.2",
                    "region": [tooth_32_left],
                    "applied_rule": self,
                }
            )

            $this->identified_subsidies.append(
                {
                    "subsidy": "3.2",
                    "region": [tooth_32_right],
                    "applied_rule": self,
                }
            )

            return True
        }
    }
}


$bilateral_free_end = BilateralFreeEnd();


class ExactToBeReplacedTeethInterdentalGapInFronRegion(
    SeparatedJawRuleCompleteInterface
) {
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

    $to_be_replaced_count = NULL;
    $subsidy = NULL;

    function upper_jaw(self) {
        // Check if 13 or 23 is the first good tooth
        // if schema.to_be_replaced_count(RegionGroup(Region(14, 14), Region(24, 24))):
        //    return False

        // 2.1/2.2: if upper jaw && X6-X8 on one side and at
        // least X7-X8 as minimum covered on the other side

        // At least one side should have TBR=3
        if (
            not Region(18, 16, $this->schema).to_be_replaced_count == 3
            and not Region(26, 28, $this->schema).to_be_replaced_count == 3
        ) {
            return False;
        }

        // If 18-16 is the one with TBR=3, the other side should have
        // minimum X7-X8
        if (
            Region(18, 16, $this->schema).to_be_replaced_count == 3
            and not Region(27, 28, $this->schema).to_be_replaced_count == 2
        ) {
            return False;
        }

        // If 26-28 is the one with TBR=3, the other side should have
        // minimum X7-X8
        if (
            Region(26, 28, $this->schema).to_be_replaced_count == 3
            and not Region(18, 17, $this->schema).to_be_replaced_count == 2
        ) {
            return False;
        }

        // As defined in Issue #133, missing tooth must be inside 12-22
        // area, that's the reason by us starting from 13-23 here.
        interdental_gap = $this->schema.interdental_gap(
            region=Region(13, 23, $this->schema)
        )

        if not interdental_gap {
            return False;
        }

        // Issue #133: we have found on this issue that we would not consider a
        // this rule as valid if we have two gaps in front region at the same
        // time. The only possibility for this case is a gap in 13-11 and another
        // in 11-22
        if (
            len(
                $this->schema.interdental_gaps(region=Region(13, 23, $this->schema))
            )!= 1) {
            return False
        }

        // X2 should be
        // existing on both sides to this rule to be fired. X2 can only be
        // missing when it is part of the interdental gap found by the rule.
        $tooth_12 = Region(12, 12, $this->schema)
        $tooth_22 = Region(22, 22, $this->schema)

        if (tooth_12.to_be_replaced and tooth_12 not in interdental_gap) or (
            tooth_22.to_be_replaced and tooth_22 not in interdental_gap
        ) {
            return False;
        }

        // As defined in Issue #133, a tooth in interdental gap can not be a
        // Telescope if it is the first TBT on a 3.1 case
        teeth_32 = $this->identified_subsidies.teeth(subsidy_code="3.2");

        if (
            interdental_gap.a_tooth in teeth_32
            or interdental_gap.b_tooth in teeth_32
        ) {
            return False;
        }

        if ($interdental_gap.to_be_replaced_count == $this->to_be_replaced_count) {
            $this->identified_subsidies.append(
                {
                    "subsidy": $this->subsidy,
                    "region": interdental_gap.teeth_marking_abutment_tooth,
                    "applied_rule": self,
                }
            )
            return True;
        }
    }

    function mandible() {
        return False;
    }
}


class Exact2ToBeReplacedTeethInterdentalGapInFrontRegion(
    ExactToBeReplacedTeethInterdentalGapInFronRegion
) {
    $to_be_replaced_count = 2
    $subsidy = "2.2"
}


$exact_2_to_be_replaced_teeth_interdental_gap_in_front_region = (
    Exact2ToBeReplacedTeethInterdentalGapInFrontRegion()
)


class Exact1ToBeReplacedTeethInterdentalGapInFrontRegion(
    ExactToBeReplacedTeethInterdentalGapInFronRegion
) {
    $to_be_replaced_count = 1;
    $subsidy = "2.1";
}


$exact_1_to_be_replaced_teeth_interdental_gap_in_front_region = (
    Exact1ToBeReplacedTeethInterdentalGapInFrontRegion()
);


class UnilateralFreeEndColateralInterdentalGap(
    SeparatedJawRuleCompleteInterface
) {
    /*"""
    Unilateral free end on one side and a colateral interdental gap in another with
    with TBR=/>2#
    """*/

    function upper_jaw(self) {
        /*"""
        Unilateral free end TBR covering 18-14/15, 24/25-28 AND
        collateral interdental gap starting TBR from 14/15 or 24/25 with TBR=/>2#
        """*/
        // Unilateral LEFT free end with colateral interdental gap, that means,
        // a free end in the left and an interdental gap on the opposite side
        // of the mouth.
        $left_colateral_interdental_gap = $this->schema.interdental_gap(
            region=Region(23, 28, $this->schema)
        )

        if (
            $this->schema.upper_jaw_left_free_end
            and $left_colateral_interdental_gap
        ) {
            if left_colateral_interdental_gap.to_be_replaced_count >= 2 {
                if left_colateral_interdental_gap.to_be_replaced[0].number in [
                    24,
                    25,
                ] {
                    tooth_32_left = $this->schema.upper_jaw_left_free_end.teeth[
                        -1
                    ].right

                    // Issues #133,#357: 3.2 can not be at X5
                    if tooth_32_left.number == 15
                        return True

                    // We return the first TBT tooth after the free end and
                    // the first TBT tooth after the colateral interdental gap
                    // as our region
                    $this->identified_subsidies.append(
                        {
                            "subsidy": "3.2",
                            "region": [tooth_32_left],
                            "applied_rule": self,
                        }
                    )
                    $this->identified_subsidies.append(
                        {
                            "subsidy": "3.2",
                            "region": [left_colateral_interdental_gap.a_tooth],
                            "applied_rule": self,
                        }
                    )
                    return True;
                }
            }
        }

        // Unilateral RIGHT free end with colateral interdental gap, that means,
        // a free end in the right and an interdental gap on the opposite side
        // of the mouth.
        $right_colateral_interdental_gap = $this->schema.interdental_gap(
            region=Region(18, 13, $this->schema)
        )

        if (
            $this->schema.upper_jaw_right_free_end
            and right_colateral_interdental_gap
        ) {
            if right_colateral_interdental_gap.to_be_replaced_count >= 2 {
                if right_colateral_interdental_gap.to_be_replaced[
                    -1
                ].number in [14, 15] {
                    tooth_32_right = $this->schema.upper_jaw_right_free_end.teeth[
                        0
                    ].left

                    // Issues #133,#357: 3.2 can not be at X5
                    if tooth_32_right.number == 25
                        return True

                    // We return the first TBT tooth after the free end and
                    // the first TBT tooth after the colateral interdental gap
                    // as our region
                    $this->identified_subsidies.append(
                        {
                            "subsidy": "3.2",
                            "region": [tooth_32_right],
                            "applied_rule": self,
                        }
                    )
                    $this->identified_subsidies.append(
                        {
                            "subsidy": "3.2",
                            "region": [right_colateral_interdental_gap.b_tooth],
                            "applied_rule": self,
                        }
                    )
                    return True;
                }
            }
        }
    }

    function mandible(self) {
        /*"""
        Unilateral free end TBR covering 48-44/45, 34/35-38 AND
        collateral interdental gap starting TBR from 44/45 or 34/35 with TBR=/>2
        """*/
        // Unilateral LEFT free end with colateral interdental gap, that means,
        // a free end in the left and an interdental gap on the opposite side
        // of the mouth.
        left_colateral_interdental_gap = $this->schema.interdental_gap(
            region=Region(38, 33, $this->schema)
        )

        if (
            $this->schema.mandible_left_free_end
            and left_colateral_interdental_gap
        ) {
            if left_colateral_interdental_gap.to_be_replaced_count >= 2 {
                if left_colateral_interdental_gap.to_be_replaced[-1].number in [
                    34,
                    35,
                ] {
                    tooth_32_left = $this->schema.mandible_left_free_end.teeth[
                        0
                    ].left

                    // Issues #133,#357: 3.2 can not be at X5
                    if tooth_32_left.number == 45
                        return True

                    // We return the first TBT tooth after the free end as our
                    // region and also the first TBT of the colateral interdental
                    // gap
                    $this->identified_subsidies.append(
                        {
                            "subsidy": "3.2",
                            "region": [tooth_32_left],
                            "applied_rule": self,
                        }
                    )
                    $this->identified_subsidies.append(
                        {
                            "subsidy": "3.2",
                            "region": [left_colateral_interdental_gap.b_tooth],
                            "applied_rule": self,
                        }
                    )
                    return True;
                }
            }
        }

        // Unilateral RIGHT free end with colateral interdental gap, that means,
        // a free end in the right and an interdental gap on the opposite side
        // of the mouth.
        right_colateral_interdental_gap = $this->schema.interdental_gap(
            region=Region(43, 48, $this->schema)
        )

        if (
            $this->schema.mandible_right_free_end
            and right_colateral_interdental_gap
        ) {
            if right_colateral_interdental_gap.to_be_replaced_count >= 2 {
                if right_colateral_interdental_gap.to_be_replaced[0].number in [
                    44,
                    45,
                ] {
                    tooth_32_right = $this->schema.mandible_right_free_end.teeth[
                        -1
                    ].right

                    // Issues #133,#357: 3.2 can not be at X5
                    if tooth_32_right.number == 35
                        return True

                    // We return the first TBT tooth after the free end as our
                    // region and also the first TBT of the colateral interdental
                    // gap
                    $this->identified_subsidies.append(
                        {
                            "subsidy": "3.2",
                            "region": [tooth_32_right],
                            "applied_rule": self,
                        }
                    )
                    $this->identified_subsidies.append(
                        {
                            "subsidy": "3.2",
                            "region": [right_colateral_interdental_gap.a_tooth],
                            "applied_rule": self,
                        }
                    )
                    return True;
                }
            }
        }
    }
}


$unilateral_free_end_colateral_interdental_gap = (
    UnilateralFreeEndColateralInterdentalGap()
);


class BilateralInterdentalGap(SeparatedJawRuleCompleteInterface) {
    /*"""
    Bilateral interdental gap TBR at least one side TBR>2
    """*/

    function upper_jaw(self) {
        /*"""
        Bilateral interdental gap TBR covering 17-14/15, 24/25-27
        starting TBR from 14/15 or 24/25 (at least one side TBR>2)
        """*/
        if not $this->identified_subsidies.exists(
            subsidy_code_startswith="3.1", region=Region.upper_jaw()
        )
            return False
        // Check LEFT interdental gap
        $left_interdental_gap = $this->schema.interdental_gap(
            region=Region(18, 13, $this->schema)
        );

        if left_interdental_gap is NULL
            return False

        if left_interdental_gap.to_be_replaced[-1].number not in [14, 15]
            return False;

        // Check RIGHT interdental gap
        $right_interdental_gap = $this->schema.interdental_gap(
            region=Region(23, 28, $this->schema)
        );

        if $right_interdental_gap is NULL
            return False

        if right_interdental_gap.to_be_replaced[0].number not in [24, 25]
            return False;

        // Check if there is TBR>2 at least in one side
        if (
            left_interdental_gap.to_be_replaced_count > 2
            or right_interdental_gap.to_be_replaced_count > 2
        ) {
            // We return the first two TBT tooth after the free ends (notice that X8 teeth
            // are missing here)
            $this->identified_subsidies.append(
                {
                    "subsidy": "3.2",
                    "region": [left_interdental_gap.b_tooth],
                    "applied_rule": self,
                }
            )
            $this->identified_subsidies.append(
                {
                    "subsidy": "3.2",
                    "region": [right_interdental_gap.a_tooth],
                    "applied_rule": self,
                }
            )

            return True;
        }
    }

    function mandible(self) {
        /*"""
        Bilateral interdental gap TBR covering 47-44/45, 34/35-37
        starting TBR from 44/45 or 34/35 (at least one side TBR>2)
        """*/
        if not $this->identified_subsidies.exists(
            $subsidy_code_startswith="3.1", $region=Region.mandible()
        )
            return False

        // Check LEFT interdental gap
        $left_interdental_gap = $this->schema.interdental_gap(
            $region=Region(43, 48, $this->schema)
        );

        if left_interdental_gap is NULL
            return False;

        if left_interdental_gap.to_be_replaced[0].number not in [44, 45]
            return False;

        // Check RIGHT interdental gap
        right_interdental_gap = $this->schema.interdental_gap(
            region=Region(38, 33, $this->schema)
        )

        if right_interdental_gap is NULL
            return False

        if right_interdental_gap.to_be_replaced[-1].number not in [34, 35]
            return False

        // Check if there is TBR>2 at least in one side
        if (
            left_interdental_gap.to_be_replaced_count > 2
            or right_interdental_gap.to_be_replaced_count > 2
        ) {
            // We return the first two TBT tooth after the free ends (notice that X8 teeth
            // are missing here)
            $this->identified_subsidies.append(
                {
                    "subsidy": "3.2",
                    "region": [left_interdental_gap.a_tooth],
                    "applied_rule": self,
                }
            )
            $this->identified_subsidies.append(
                {
                    "subsidy": "3.2",
                    "region": [right_interdental_gap.b_tooth],
                    "applied_rule": self,
                }
            )
            return True;
        }
    }


$bilateral_interdental_gap = BilateralInterdentalGap();


class BiggestInterdentalGapInFrontRegionExactToBeReplaced4(
    SeparatedJawRuleCompleteInterface
) {
    /*"""
    Biggest interdental gap TBR=4// in 13-23 (TBRs must be all in front region)
    """*/

    function _check_rule_in_region(self, region) {
        biggest_interdental_gap = $this->schema.biggest_interdental_gap()

        if not biggest_interdental_gap
            return False

        // Issue 251: Only the TBR must be in the region, not the whole
        // interdental gap
        if biggest_interdental_gap.to_be_replaced not in region
            return False

        if biggest_interdental_gap.to_be_replaced_count == 4 {
            $this->identified_subsidies.append(
                rv_subsidy_2x(
                    {
                        "region": biggest_interdental_gap.teeth_marking_abutment_tooth,
                        "subsidy": "2.4",
                        "applied_rule": self,
                    }
                )
            )
            return True;
        }
    }

    function upper_jaw(self) {
        return $this->_check_rule_in_region(Region.upper_jaw_front());
    }

    function mandible(self) {
        return $this->_check_rule_in_region(Region.mandible_front());
    }
}


$biggest_interdental_gap_in_front_region_exact_to_be_replaced_4 = (
    BiggestInterdentalGapInFrontRegionExactToBeReplaced4()
);


class BiggestInterdentalGapExactToBeReplaced3(SeparatedJawRuleSimpleInterface) {
    /*"""
    Biggest interdental gap TBR=3#
    """*/

    function execute_for(self, region) {
        $biggest_interdental_gap = $this->schema.biggest_interdental_gap(region);

        if not biggest_interdental_gap
            return False

        if biggest_interdental_gap.to_be_replaced_count == 3
            $this->identified_subsidies.append(
                rv_subsidy_2x(
                    {
                        "region": biggest_interdental_gap.teeth_marking_abutment_tooth,
                        "subsidy": "2.3",
                        "applied_rule": self,
                    }
                )
            )
            return True;
        }
    }
}


$biggest_interdental_gap_exact_to_be_replaced_3 = (
    BiggestInterdentalGapExactToBeReplaced3()
);


class BiggestInterdentalGapExactToBeReplaced2(SeparatedJawRuleSimpleInterface) {
    /*"""
    Biggest interdental gap TBR=2#
    """*/

    function execute_for(self, region) {
        $biggest_interdental_gap = $this->schema.biggest_interdental_gap(region);

        if not biggest_interdental_gap
            return False

        if biggest_interdental_gap.to_be_replaced_count == 2 {
            $this->identified_subsidies.append(
                rv_subsidy_2x(
                    {
                        "subsidy": "2.2",
                        "region": biggest_interdental_gap.teeth_marking_abutment_tooth,
                        "applied_rule": self,
                    }
                )
            )
            return True
        }
    }
}


$biggest_interdental_gap_exact_to_be_replaced_2 = (
    BiggestInterdentalGapExactToBeReplaced2()
);


class BiggestInterdentalGapExactToBeReplaced1(SeparatedJawRuleSimpleInterface) {
    /*"""
    Biggest interdental gap TBR=1#
    """*/

    function execute_for(self, region) {
        biggest_interdental_gap = $this->schema.biggest_interdental_gap(region)

        if not biggest_interdental_gap
            return False

        if biggest_interdental_gap.to_be_replaced_count == 1 {
            $this->identified_subsidies.append(
                rv_subsidy_2x(
                    {
                        "subsidy": "2.1",
                        "region": $biggest_interdental_gap.teeth_marking_abutment_tooth,
                        "applied_rule": self,
                    }
                )
            )
            return True;
        }
    }
}


biggest_interdental_gap_exact_to_be_replaced_1 = (
    BiggestInterdentalGapExactToBeReplaced1()
)


class BiggestInterdentalGapNeighboringGapExactToBeReplaced1(
    SeparatedJawRuleSimpleInterface
) {
    /*"""
    Neighboring gap TBR=1#, in connection with 2.1/2.2/2.3 no
    clockwise definition of main gap (e.g. mandible kbbkbk)

    CAVEAT: notice that, specifically in this case:

        1) The neighboring gap concept does not need to be seen in
           clockwise view.

        2) Neighbor of a gap is only consider when they share at least one
           tooth.
    """*/

    function execute_for(self, region) {
        biggest_interdental_gap = $this->schema.biggest_interdental_gap(region)
        neighboring_gap = NULL

        if not biggest_interdental_gap
            return False

        // Limit for 2.1, 2.2 and 2.3 rules
        if biggest_interdental_gap.to_be_replaced_count not in [1, 2, 3]
            return False

        for gap in $this->schema.all_interdental_gaps(region) {
            if gap.region != biggest_interdental_gap.region {
                if biggest_interdental_gap.is_neighbor_of(gap) {
                    if gap.to_be_replaced_count == 1 {
                        neighboring_gap = gap
                        break
                    }
                }
            }
        }

        if not neighboring_gap
            return False

        $this->identified_subsidies.append(
            rv_subsidy_2x(
                {
                    "subsidy": "2.5",
                    "region": neighboring_gap.teeth_marking_abutment_tooth,
                    "applied_rule": self,
                }
            )
        )
        return True;
    }
}


$biggest_interdental_gap_neighboring_gap_exact_to_be_replaced_1 = (
    BiggestInterdentalGapNeighboringGapExactToBeReplaced1()
);


function evaluate_by_region($schema, $identified_subsidies) {
    /*"""
    Apply the "by region" rules for a given schema and return the subsidy that need to
    be used.
    """*/
    $rules = [
        {"rule": exact_16_to_be_replaced_teeth},
        {"rule": between_13_and_15_to_be_replaced_teeth},
        {
            "rule": bilateral_free_end,
            "dependents": [
                exact_2_to_be_replaced_teeth_interdental_gap_in_front_region,
                exact_1_to_be_replaced_teeth_interdental_gap_in_front_region,
            ],
        },
        {
            "rule": unilateral_free_end_to_be_replaced_teeth_at_least_1,
            "dependents": [
                unilateral_free_end_colateral_interdental_gap,
                exact_2_to_be_replaced_teeth_interdental_gap_in_front_region,
                exact_1_to_be_replaced_teeth_interdental_gap_in_front_region,
            ],
        },
        {
            "rule": between_5_and_12_to_be_replaced_teeth,
            "dependents": [
                bilateral_interdental_gap,
                exact_2_to_be_replaced_teeth_interdental_gap_in_front_region,
                exact_1_to_be_replaced_teeth_interdental_gap_in_front_region,
            ],
        },
        {"rule": unilateral_free_end_to_be_replaced_x7_and_x8},
        {
            "rule": exact_4_to_be_replaced_teeth_interdental_gap_with_at_least_one_in_posterior_region,
            "dependents": [
                bilateral_interdental_gap,
                exact_2_to_be_replaced_teeth_interdental_gap_in_front_region,
                exact_1_to_be_replaced_teeth_interdental_gap_in_front_region,
            ],
        },
        {
            "rule": between_2_and_3_to_be_replaced_teeth_interdental_gap_neighboring_another_gap,
            "dependents": [
                exact_2_to_be_replaced_teeth_interdental_gap_in_front_region,
                exact_1_to_be_replaced_teeth_interdental_gap_in_front_region,
            ],
        },
        {
            "rule": neighboring_interdental_gaps_count_relative_to_the_main_gap_greater_than_1,
            "dependents": [
                exact_2_to_be_replaced_teeth_interdental_gap_in_front_region,
                exact_1_to_be_replaced_teeth_interdental_gap_in_front_region,
            ],
        },
        {
            "rule": biggest_interdental_gap_in_front_region_exact_to_be_replaced_4,
            "dependents": [
                biggest_interdental_gap_neighboring_gap_exact_to_be_replaced_1
            ],
        },
        {
            "rule": biggest_interdental_gap_exact_to_be_replaced_3,
            "dependents": [
                biggest_interdental_gap_neighboring_gap_exact_to_be_replaced_1
            ],
        },
        {
            "rule": biggest_interdental_gap_exact_to_be_replaced_2,
            "dependents": [
                biggest_interdental_gap_neighboring_gap_exact_to_be_replaced_1
            ],
        },
        {
            "rule": biggest_interdental_gap_exact_to_be_replaced_1,
            "dependents": [
                biggest_interdental_gap_neighboring_gap_exact_to_be_replaced_1
            ],
        }
    ];

    foreach ($rules as $rule) {
        $rule_to_execute = $rule["rule"];
        $dependents = $rule.get("dependents");

        $result = rule_to_execute($schema, $identified_subsidies)

        if result {
            // There are not dependents and rule was evaluated to True:
            // just return, no need more
            if not dependents
                return True

            // If there are a list of dependents for the rule, all of them will
            // be executed. The first to be evaluated to True will stop
            // evaluation
            for dependent_to_execute in dependents {
                dependent_result = dependent_to_execute(
                    schema, identified_subsidies
                )

                if dependent_result
                    return True;
            }
            return True
        }
    }
}
