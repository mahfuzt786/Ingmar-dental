<?php
/*"""
Rules for the subsidy that are evaluated by region.
"""*/
require_once('regions.php');
require_once('interfaces.php');
require_once('rv.php');

// from apps.therapies.subsidy.regions import Region, RegionGroup
// from apps.therapies.subsidy.interfaces import (
//     SeparatedJawRuleSimpleInterface,
//     SeparatedJawRuleCompleteInterface,
// )
// from apps.therapies.subsidy.rv import (
//     rv_subsidy_4x,
//     rv_subsidy_3x,
//     rv_subsidy_2x,
// )


class Exact16ToBeReplacedTeeth {
    /*"""
    TBR=16// (Count inclusive X8).
    """*/
    // public $SeparatedJawRuleCompleteInterface;

    function __construct($schema, $identified_subsidies, $SeparatedJawRuleCompleteInterface=NULL) {
        $this->schema = $schema;
        $this->identified_subsidies = $identified_subsidies;
        $this->SeparatedJawRuleCompleteInterface = new SeparatedJawRuleCompleteInterface($schema, $identified_subsidies);
    }

    function upper_jaw() {
        $to_be_replaced = $this->schema.to_be_replaced($region=new Region.upper_jaw());
        $region = $this->schema.get_teeth_in_region(new Region.upper_jaw());
        if (count($to_be_replaced) == 16) {
            array_push($this->identified_subsidies, 
                rv_subsidy_4x(
                    ["subsidy"=> "4.2", "region"=> $region, "applied_rule"=> $this]
                )
            );

            return True;
        }
    }

    function mandible() {
        $to_be_replaced = $this->schema.to_be_replaced($region=new Region.mandible());
        $region = $this->schema.get_teeth_in_region(new Region.mandible());
        if (count($to_be_replaced) == 16) {
            array_push($this->identified_subsidies,
                rv_subsidy_4x(
                    ["subsidy"=> "4.4", "region"=> $region, "applied_rule"=> $this]
                )
            );
            return True;
        }
    }
}


// $exact_16_to_be_replaced_teeth = new Exact16ToBeReplacedTeeth($schema, $identified_subsidies);


class Between13And15ToBeReplacedTeeth {
    /*"""
    TBR=13-15// (Count inclusive X8).
    """*/
    // public $SeparatedJawRuleCompleteInterface;
    function __construct($schema, $identified_subsidies, $SeparatedJawRuleCompleteInterface=NULL) {
        $this->schema = $schema;
        $this->identified_subsidies = $identified_subsidies;
        $this->SeparatedJawRuleCompleteInterface = new SeparatedJawRuleCompleteInterface($schema, $identified_subsidies);
    }

    function upper_jaw() {
        $to_be_replaced = $this->schema.to_be_replaced($region=new Region.upper_jaw());
        $region = $this->schema.get_teeth_in_region(new Region.upper_jaw());
        if (13 <= count($to_be_replaced) and count($to_be_replaced) <= 15) {
            array_push($this->identified_subsidies,
                rv_subsidy_4x(
                    ["subsidy"=> "4.1", "region"=> $region, "applied_rule"=> $this]
                )
            );
            return True;
        }
    }

    function mandible() {
        $to_be_replaced = $this->schema.to_be_replaced($region=new Region.mandible());
        $region = $this->schema.get_teeth_in_region(new Region.mandible());
        if (13 <= count($to_be_replaced) and count($to_be_replaced) <= 15) {
            array_push($this->identified_subsidies,
                rv_subsidy_4x(
                    ["subsidy"=> "4.3", "region"=> $region, "applied_rule"=> $this]
                )
            );
            return True;
        }
    }
}


// $between_13_and_15_to_be_replaced_teeth = new Between13And15ToBeReplacedTeeth;


class UnilateralFreeEndToBeReplacedTeethAtLeast1 {
    /*"""
    Unilateral free end (TBR at least 1x X6-X8) and TBR>=3

    Now when a free end happens in both sides
    it is also considered unilateral free end.
    """*/
    // public $SeparatedJawRuleCompleteInterface;
    function __construct($schema, $identified_subsidies, $SeparatedJawRuleCompleteInterface=NULL) {
        $this->schema = $schema;
        $this->identified_subsidies = $identified_subsidies;
        $this->SeparatedJawRuleCompleteInterface = new SeparatedJawRuleCompleteInterface($schema, $identified_subsidies);
    }

    function upper_jaw() {
        /*"""
        Check for unilateral free end in the upper jaw
        """*/
        $unilateral_free_end = (
            $this->schema->upper_jaw_left_free_end
            or $this->schema->upper_jaw_right_free_end
        );

        if ($unilateral_free_end) {
            array_push($this->identified_subsidies,
                rv_subsidy_3x(
                    [
                        "subsidy"=> "3.1",
                        "region"=> $this->schema.get_teeth_in_region(
                            new Region.upper_jaw()
                        ),
                        "applied_rule"=> $this,
                    ]
                )
            );
            return True;
        }
    }

    function mandible() {
        /*"""
        Check for unilateral free end in the mandible
        """*/
        $unilateral_free_end = (
            $this->schema->mandible_left_free_end
            or $this->schema->mandible_right_free_end
        );

        if ($unilateral_free_end) {
            array_push($this->identified_subsidies,
                rv_subsidy_3x(
                    [
                        "subsidy"=> "3.1",
                        "region"=> $this->schema.get_teeth_in_region(
                            new Region.mandible()
                        ),
                        "applied_rule"=> $this,
                    ]
                )
            );
            return True;
        }
    }
}


// $unilateral_free_end_to_be_replaced_teeth_at_least_1 = new UnilateralFreeEndToBeReplacedTeethAtLeast1;


class UnilateralFreeEndToBeReplacedX7andX8 {
    /*"""
    Applied
    when we have a X7/X8 free end only. The reason is that when we will
    introduce optional answers, we will later make those 3.1 optional
    which base on X7/X8 only and have those required, which rely on X6/X7/X8,
    but for now, it means that we also calculate a 3.1 when ONLY the X7/X8
    is missing for the unilateral free end.
    """*/

    // public $SeparatedJawRuleCompleteInterface;
    function __construct($schema, $identified_subsidies, $SeparatedJawRuleCompleteInterface=NULL) {
        $this->schema = $schema;
        $this->identified_subsidies = $identified_subsidies;
        $this->SeparatedJawRuleCompleteInterface = new SeparatedJawRuleCompleteInterface($schema, $identified_subsidies);
    }

    function _unilateral_free_end_upper_jaw() {
        /*"""
        Unilateral free end only X7/X8 missing.
        """*/
        $right_end_tbr = False;
        $left_end_tbr = False;
        $to_be_replaced_right = $this->schema.to_be_replaced($region=new Region(27, 28));
        $to_be_replaced_left = $this->schema.to_be_replaced($region=new Region(18, 17));

        if (count($to_be_replaced_right) == 2) {
            // Just consider free end being X7/X8, if one more tooth
            // is also TBR it will not be considered
            if (! $this->schema.to_be_replaced($region=new Region(26, 26))) {
                $right_end_tbr = True;
            }
        }

        if (count($to_be_replaced_left) == 2) {
            if (! $this->schema.to_be_replaced($region=new Region(16, 16))) {
                $left_end_tbr = True;
            }
        }

        // Avoiding bilateral free end
        return count(array_unique([$left_end_tbr, $right_end_tbr])) == 2;
    }

    function _unilateral_free_end_mandible() {
        /*"""
        Unilateral free end only X7/X8 missing.
        """*/
        $right_end_tbr = False;
        $left_end_tbr = False;
        $to_be_replaced_right = $this->schema.to_be_replaced($region=new Region(38, 37));
        $to_be_replaced_left = $this->schema.to_be_replaced($region=new Region(47, 48));

        if (count($to_be_replaced_right) == 2) {
            // Just consider free end being X7/X8, if one more tooth
            // is also TBR it will not be considered
            if (! $this->schema->to_be_replaced($region=new Region(36, 36))) {
                $right_end_tbr = True;
            }
        }

        if (count($to_be_replaced_left) == 2) {
            if (! $this->schema->to_be_replaced($region=new Region(46, 46))) {
                $left_end_tbr = True;
            }
        }

        // Avoiding bilateral free end
        return count(array_unique([$left_end_tbr, $right_end_tbr])) == 2;
    }

    function upper_jaw() {
        /*"""
        Check for unilateral free end in the upper jaw
        """*/
        $unilateral_free_end = $this->_unilateral_free_end_upper_jaw();

        // Assure that this subsidy will not be applied twice. Remember
        // that it is optional and, also, that it can happen in both sides
        // of the mouth
        if ($this->identified_subsidies.exists(
            $subsidy_code_startswith="3.1", $region=new Region.upper_jaw())) {
            return False;
        }

        if ($unilateral_free_end) {
            array_push($this->identified_subsidies,
                rv_subsidy_3x(
                    [
                        "subsidy"=> "3.1",
                        "region"=> $this->schema.get_teeth_in_region(
                            new Region.upper_jaw()
                        ),
                        "applied_rule"=> self,
                        "optional"=> True,
                    ]
                )
                );
            // Optional rules do not stop the execution cicle
            return False;
        }
    }

    function mandible() {
        /*"""
        Check for unilateral free end in the mandible
        """*/
        $unilateral_free_end = $this->_unilateral_free_end_mandible();

        // Assure that this subsidy will not be applied twice. Remember
        // that it is optional and, also, that it can happen in both sides
        // of the mouth
        if ($this->identified_subsidies.exists(
            $subsidy_code_startswith="3.1", $region=new Region.mandible()
        )) {
            return False;
        }

        if ($unilateral_free_end) {
            array_push($this->identified_subsidies,
                rv_subsidy_3x(
                    [
                        "subsidy"=> "3.1",
                        "region"=> $this->schema.get_teeth_in_region(
                            new Region.mandible()
                        ),
                        "applied_rule"=> self,
                        "optional"=> True,
                    ]
                )
            );
            // Optional rules do not stop the execution cicle
            return False;
        }
    }
}

// $unilateral_free_end_to_be_replaced_x7_and_x8 = new UnilateralFreeEndToBeReplacedX7andX8;


class Between5And12ToBeReplacedTeeth {
    /*"""
    TBR=5-12// without counting X8
    """*/
    // public $SeparatedJawRuleCompleteInterface;
    function __construct($schema, $identified_subsidies, $SeparatedJawRuleCompleteInterface=NULL) {
        $this->schema = $schema;
        $this->identified_subsidies = $identified_subsidies;
        $this->SeparatedJawRuleCompleteInterface = new SeparatedJawRuleCompleteInterface($schema, $identified_subsidies);
    }

    function upper_jaw() {
        $upper_jaw_without_x8 = new Region(17, 27);

        $to_be_replaced = $this->schema.to_be_replaced($region=$upper_jaw_without_x8);

        if (5 <= count($to_be_replaced) and count($to_be_replaced) <= 12) {
            array_push($this->identified_subsidies,
                rv_subsidy_3x(
                    [
                        "subsidy"=> "3.1",
                        "region"=> $this->schema.get_teeth_in_region(
                            new Region.upper_jaw()
                        ),
                        "applied_rule"=> self,
                    ]
                )
            );
            return True;
        }
    }

    function mandible() {
        $mandible_without_x8 = new Region(37, 47);
        $to_be_replaced = $this->schema.to_be_replaced($region=$mandible_without_x8);

        if (5 <= count($to_be_replaced) AND count($to_be_replaced) <= 12) {
            array_push($this->identified_subsidies,
                rv_subsidy_3x(
                    [
                        "subsidy"=> "3.1",
                        "region"=> $this->schema.get_teeth_in_region(
                            new Region.mandible()
                        ),
                        "applied_rule"=> self,
                    ]
                )
            );
            return True;
        }
    }
}


// $between_5_and_12_to_be_replaced_teeth = new Between5And12ToBeReplacedTeeth;


class Exact4ToBeReplacedTeethInterdentalGapWithAtLeastOneInPosteriorRegion {
    /*"""
    Interdental gap of TBR=4// with at least one TBR tooth in posterior region
    """*/

    // public $SeparatedJawRuleCompleteInterface;
    function __construct($schema, $identified_subsidies, $SeparatedJawRuleCompleteInterface=NULL) {
        $this->schema = $schema;
        $this->identified_subsidies = $identified_subsidies;
        $this->SeparatedJawRuleCompleteInterface = new SeparatedJawRuleCompleteInterface($schema, $identified_subsidies);
    }

    function upper_jaw() {
        $interdental_gap = $this->schema.interdental_gap_with_to_be_replaced_count(
            new Region.upper_jaw(), 4
        );

        if ($interdental_gap) {
            foreach ($interdental_gap->to_be_replaced as $tooth ) {
                if ( in_array ($tooth, new RegionGroup.upper_jaw_posterior_region() )){
                    array_push($this->identified_subsidies,
                        rv_subsidy_3x(
                            [
                                "subsidy"=> "3.1",
                                "region"=> $this->schema.get_teeth_in_region(
                                    new Region.upper_jaw()
                                ),
                                "applied_rule"=> self,
                            ]
                        )
                    );
                    return True;
                }
            }
                    
        }
    }

    function mandible() {
        $interdental_gap = $this->schema.interdental_gap_with_to_be_replaced_count(
            new Region.mandible(), 4
        );

        if ($interdental_gap) {
            foreach ($interdental_gap->to_be_replaced as $tooth) {
                if( in_array($tooth, new RegionGroup.mandible_posterior_region())) {
                    array_push($this->identified_subsidies,
                        rv_subsidy_3x(
                            [
                                "subsidy"=> "3.1",
                                "region"=> $this->schema.get_teeth_in_region(
                                    new Region.mandible()
                                ),
                                "applied_rule"=> self,
                            ]
                        )
                    );
                    return True;
                }
            }
        }
    }
}


// $exact_4_to_be_replaced_teeth_interdental_gap_with_at_least_one_in_posterior_region = new Exact4ToBeReplacedTeethInterdentalGapWithAtLeastOneInPosteriorRegion;


class Between2And3ToBeReplacedTeethInterdentalGapNeighboringAnotherGap {
    /*"""
    Interdental gap TBR=2/3// and neighboring gap with TBR>1#
    """*/

    // public $SeparatedJawRuleSimpleInterface;
    function __construct($schema, $identified_subsidies, $SeparatedJawRuleSimpleInterface=NULL) {
        $this->schema = $schema;
        $this->identified_subsidies = $identified_subsidies;
        $this->SeparatedJawRuleSimpleInterface = new SeparatedJawRuleSimpleInterface;
    }

    function execute_for($region) {
        $interdental_gaps = $this->schema.all_interdental_gaps();

        foreach ($interdental_gaps as $interdental_gap ) {
            if (2 <= $interdental_gap->to_be_replaced_count and $interdental_gap->to_be_replaced_count <= 3) {
                $neighboring_gap = $this->schema.neighboring_gap($interdental_gap);
                if (! $neighboring_gap)
                    return False;

                if (! $neighboring_gap->to_be_replaced_count > 1)
                    return False;

                if (
                    ! in_array($neighboring_gap, $region) OR
                    ! in_array($interdental_gap, $region)
                ) {
                    return False;
                }

                array_push($this->identified_subsidies,
                    rv_subsidy_3x(
                        [
                            "subsidy"=> "3.1",
                            "region"=> $this->schema.get_teeth_in_region($region),
                            "applied_rule"=> self,
                        ]
                    )
                );
                return True;
            }
        }
    }
}


// $between_2_and_3_to_be_replaced_teeth_interdental_gap_neighboring_another_gap = (
//     new Between2And3ToBeReplacedTeethInterdentalGapNeighboringAnotherGap
// );


class NeighboringInterdentalGapsCountRelativeToTheMainGapGreaterThan1 {
    /*"""
    Neighboring interdental gaps relative to the main gap > 1.
    Notice that, just for this case, neighbors are considered on
    the right or on the left.
    """*/

    // public $SeparatedJawRuleSimpleInterface;
    function __construct($schema, $identified_subsidies, $SeparatedJawRuleSimpleInterface=NULL) {
        $this->schema = $schema;
        $this->identified_subsidies = $identified_subsidies;
        $this->SeparatedJawRuleSimpleInterface = new SeparatedJawRuleSimpleInterface;
    }

    function execute_for($region) {
        $main_interdental_gap = $this->schema.main_interdental_gap($region);
        $all_gaps = $this->schema.all_interdental_gaps($region=$region);
        $neighbors = [];
        $check_neighborhood = [$main_interdental_gap];

        // Search for gaps that are neighbors of all gaps already selected.
        // It can be on the right or on the left, so that the concept of
        // neighbor gap was changed here.
        while ($check_neighborhood) {
            $gap_collected = array_pop($check_neighborhood);
            foreach ($all_gaps as $neighbor_gap_candidate) {
                if ($gap_collected != $neighbor_gap_candidate) {
                    // Here we check for left and right
                    if ($gap_collected.is_neighbor_of(
                        $neighbor_gap_candidate
                    ) or $neighbor_gap_candidate.is_neighbor_of($gap_collected)) {
                        if (in_array($neighbor_gap_candidate, $neighbors)) {
                            array_push($neighbors, $neighbor_gap_candidate);
                            array_push($check_neighborhood, $neighbor_gap_candidate);
                        }
                    }
                }
            }
        }

        // Neighbors here include also the main_gap, that's the reason
        // we check for neighbors greater than 2 and not for greater than 1
        // only
        if (count($neighbors) > 2) {
            array_push($this->identified_subsidies,
                rv_subsidy_3x(
                    [
                        "subsidy"=> "3.1",
                        "region"=> $this->schema.get_teeth_in_region($region),
                        "applied_rule"=> self,
                    ]
                )
            );
            return True;
        }
    }
}


// $neighboring_interdental_gaps_count_relative_to_the_main_gap_greater_than_1 = (
//     new NeighboringInterdentalGapsCountRelativeToTheMainGapGreaterThan1
// );


class BilateralFreeEnd {
    /*"""
    Bilateral free end with TBR covering 18-14/15, 24/25-28
    (means { It could be e.g. 18-14 and 25-28)

    CAVEAT: this is a very specific case. The BilateralFreeEnd rule might
    return 3.1 and 3.2. The problem is that the region should be different
    for each one of the rules. As it is a VERY specific case only for this
    rule, we need to add two more 3.2 subsidies in each jaw.
    """*/
    // public $SeparatedJawRuleCompleteInterface;
    function __construct($schema, $identified_subsidies, $SeparatedJawRuleCompleteInterface=NULL) {
        $this->schema = $schema;
        $this->identified_subsidies = $identified_subsidies;
        $this->SeparatedJawRuleCompleteInterface = new SeparatedJawRuleCompleteInterface($schema, $identified_subsidies);
    }

    function upper_jaw() {
        if (
            $this->schema->upper_jaw_left_free_end
            and $this->schema->upper_jaw_right_free_end
        ) {
            array_push($this->identified_subsidies,
                rv_subsidy_3x(
                    [
                        "subsidy"=> "3.1",
                        "region"=> $this->schema.get_teeth_in_region(
                            new Region.upper_jaw()
                        ),
                        "applied_rule"=> self,
                    ]
                )
            );
            $tooth_32_left = $this->schema.upper_jaw_right_free_end.teeth[0].left;
            $tooth_32_right = $this->schema.upper_jaw_left_free_end.teeth[-1].right;

            // Issue #133: we added the concept of free end for upper jaw being
            // just both sides in X6-X8, but when it happens, a 3.2 must not be
            // added, so that we limit 3.2 to not be applied when teeth are
            // 15 and 25 that is the teeth just after X6.
            if ($tooth_32_left->number == 25 or $tooth_32_right->number == 15) {
                return True;
            }

            // 1) For 3.2 we should return the first TBT tooth after the
            // free end. Notice that if the next tooth is a TBR it will not
            // receive a 3.2
            // 2) 3.2 can only exist together (see Issue #346 for reference)
            if ($tooth_32_left->to_be_replaced or $tooth_32_right->to_be_replaced) {
                return True;
            }

            array_push($this->identified_subsidies,
                [
                    "subsidy"=> "3.2",
                    "region"=> [$tooth_32_left],
                    "applied_rule"=> self,
                ]
            );
            array_push($this->identified_subsidies,
                [
                    "subsidy"=> "3.2",
                    "region"=> [$tooth_32_right],
                    "applied_rule"=> self,
                ]
            );
            return True;
        }
    }

    function mandible() {
        if ($this->schema.mandible_left_free_end
            and $this->schema.mandible_right_free_end) {
            array_push($this->identified_subsidies,
                rv_subsidy_3x(
                    [
                        "subsidy"=> "3.1",
                        "region"=> $this->schema.get_teeth_in_region(
                            new Region.mandible()
                        ),
                        "applied_rule"=> self,
                    ]
                )
            );

            $tooth_32_left = $this->schema.mandible_left_free_end.teeth[0].left;
            $tooth_32_right = $this->schema.mandible_right_free_end.teeth[-1].right;

            // Issues #133,#357: we added the concept of free end for upper jaw being
            // just both sides in X6-X8, but when it happens, a 3.2 must not be
            // added, so that we limit 3.2 to not be applied when teeth are
            // 15 and 25 that is the teeth just after X6.
            if ($tooth_32_left.number == 45 or $tooth_32_right.number == 35) {
                return True;
            }

            // 1) For 3.2 we should return the first TBT tooth after the
            // free end. Notice that if the next tooth is a TBR it will not
            // receive a 3.2
            // 2) 3.2 can only exist together (see Issue #346 for reference)
            if ($tooth_32_left->to_be_replaced or $tooth_32_right->to_be_replaced) {
                return True;
            }

            array_push($this->identified_subsidies,
                [
                    "subsidy"=> "3.2",
                    "region"=> [$tooth_32_left],
                    "applied_rule"=> self,
                ]
            );

            array_push($this->identified_subsidies,
                [
                    "subsidy"=> "3.2",
                    "region"=> [$tooth_32_right],
                    "applied_rule"=> self,
                ]
            );

            return True;
        }
    }
}


// $bilateral_free_end = new BilateralFreeEnd();


class ExactToBeReplacedTeethInterdentalGapInFronRegion {
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

    // public $SeparatedJawRuleCompleteInterface;
    function __construct($schema, $identified_subsidies, $SeparatedJawRuleCompleteInterface=NULL) {
        $this->schema = $schema;
        $this->identified_subsidies = $identified_subsidies;
        $this->SeparatedJawRuleCompleteInterface = new SeparatedJawRuleCompleteInterface($schema, $identified_subsidies);
    }

    public $to_be_replaced_count = NULL;
    public $subsidy = NULL;

    function upper_jaw() {
        // Check if 13 or 23 is the first good tooth
        // if schema->to_be_replaced_count(new RegionGroup(new Region(14, 14), new Region(24, 24))) {
        //    return False

        // 2.1/2.2: if upper jaw && X6-X8 on one side and at
        // least X7-X8 as minimum covered on the other side

        // At least one side should have TBR=3
        if (
            ! new Region(18, 16, $this->schema).to_be_replaced_count == 3
            and ! new Region(26, 28, $this->schema).to_be_replaced_count == 3
        ) {
            return False;
        }

        // If 18-16 is the one with TBR=3, the other side should have
        // minimum X7-X8
        if (
            new Region(18, 16, $this->schema).to_be_replaced_count == 3
            and ! new Region(27, 28, $this->schema).to_be_replaced_count == 2
        ) {
            return False;
        }

        // If 26-28 is the one with TBR=3, the other side should have
        // minimum X7-X8
        if (
            new Region(26, 28, $this->schema).to_be_replaced_count == 3
            and ! new Region(18, 17, $this->schema).to_be_replaced_count == 2
        ) {
            return False;
        }

        // As defined in Issue #133, missing tooth must be inside 12-22
        // area, that's the reason by us starting from 13-23 here.
        $interdental_gap = $this->schema.interdental_gap(
            $region=new Region(13, 23, $this->schema)
        );

        if (! $interdental_gap) {
            return False;
        }

        // Issue #133: we have found on this issue that we would not consider a
        // this rule as valid if we have two gaps in front region at the same
        // time. The only possibility for this case is a gap in 13-11 and another
        // in 11-22
        if (
            count(
                $this->schema.interdental_gaps($region=new Region(13, 23, $this->schema))
            )!= 1) {
            return False;
        }

        // X2 should be
        // existing on both sides to this rule to be fired. X2 can only be
        // missing when it is part of the interdental gap found by the rule.
        $tooth_12 = new Region(12, 12, $this->schema);
        $tooth_22 = new Region(22, 22, $this->schema);

        if (($tooth_12->to_be_replaced and ! in_array($tooth_12, $interdental_gap)) or (
            $tooth_22->to_be_replaced and ! in_array($tooth_22, $interdental_gap))
        ) {
            return False;
        }

        // As defined in Issue #133, a tooth in interdental gap can not be a
        // Telescope if it is the first TBT on a 3.1 case
        $teeth_32 = $this->identified_subsidies.teeth($subsidy_code="3.2");

        if (
            in_array($interdental_gap.a_tooth, $teeth_32) 
            or in_array($interdental_gap.b_tooth, $teeth_32)
        ) {
            return False;
        }

        if ($interdental_gap->to_be_replaced_count == $this->to_be_replaced_count) {
            array_push($this->identified_subsidies,
                [
                    "subsidy"=> $this->subsidy,
                    "region"=> $interdental_gap.teeth_marking_abutment_tooth,
                    "applied_rule"=> self,
                ]
            );
            return True;
        }
    }

    function mandible() {
        return False;
    }
}


class Exact2ToBeReplacedTeethInterdentalGapInFrontRegion {
    // public $ExactToBeReplacedTeethInterdentalGapInFronRegion;

    function __construct($schema, $identified_subsidies, $ExactToBeReplacedTeethInterdentalGapInFronRegion=NULL) {
        $this->schema = $schema;
        $this->identified_subsidies = $identified_subsidies;
        $this->ExactToBeReplacedTeethInterdentalGapInFronRegion = new ExactToBeReplacedTeethInterdentalGapInFronRegion;
    }

    public $to_be_replaced_count = 2;
    public $subsidy = "2.2";
}


// $exact_2_to_be_replaced_teeth_interdental_gap_in_front_region = (
//     new Exact2ToBeReplacedTeethInterdentalGapInFrontRegion()
// );


class Exact1ToBeReplacedTeethInterdentalGapInFrontRegion {
    public $ExactToBeReplacedTeethInterdentalGapInFronRegion;

    function __construct($schema, $identified_subsidies, $ExactToBeReplacedTeethInterdentalGapInFronRegion=NULL) {
        $this->schema = $schema;
        $this->identified_subsidies = $identified_subsidies;
        $this->ExactToBeReplacedTeethInterdentalGapInFronRegion = new ExactToBeReplacedTeethInterdentalGapInFronRegion;
    }

    public $to_be_replaced_count = 1;
    public $subsidy = "2.1";
}


// $exact_1_to_be_replaced_teeth_interdental_gap_in_front_region = (
//     new Exact1ToBeReplacedTeethInterdentalGapInFrontRegion()
// );


class UnilateralFreeEndColateralInterdentalGap {
    /*"""
    Unilateral free end on one side and a colateral interdental gap in another with
    with TBR=/>2#
    """*/

    // public $SeparatedJawRuleCompleteInterface;
    function __construct($schema, $identified_subsidies, $SeparatedJawRuleCompleteInterface=NULL) {
        $this->schema = $schema;
        $this->identified_subsidies = $identified_subsidies;
        $this->SeparatedJawRuleCompleteInterface = new SeparatedJawRuleCompleteInterface($schema, $identified_subsidies);
    }

    function upper_jaw() {
        /*"""
        Unilateral free end TBR covering 18-14/15, 24/25-28 AND
        collateral interdental gap starting TBR from 14/15 or 24/25 with TBR=/>2#
        """*/
        // Unilateral LEFT free end with colateral interdental gap, that means,
        // a free end in the left and an interdental gap on the opposite side
        // of the mouth.
        $left_colateral_interdental_gap = $this->schema.interdental_gap(
            $region=new Region(23, 28, $this->schema)
        );

        if (
            $this->schema.upper_jaw_left_free_end
            and $left_colateral_interdental_gap
        ) {
            if ($left_colateral_interdental_gap->to_be_replaced_count >= 2) {
                if (in_array($left_colateral_interdental_gap->to_be_replaced[0].number, 
                    [24, 25]))
                {
                    $tooth_32_left = end($this->schema.upper_jaw_left_free_end.teeth).right;

                    // Issues #133,#357: 3.2 can not be at X5
                    if ($tooth_32_left.number == 15)
                        return True;

                    // We return the first TBT tooth after the free end and
                    // the first TBT tooth after the colateral interdental gap
                    // as our region
                    array_push($this->identified_subsidies,
                        [
                            "subsidy"=> "3.2",
                            "region"=> [$tooth_32_left],
                            "applied_rule"=> self,
                        ]
                    );
                    array_push($this->identified_subsidies,
                        [
                            "subsidy"=> "3.2",
                            "region"=> [$left_colateral_interdental_gap.a_tooth],
                            "applied_rule"=> self,
                        ]
                    );
                    return True;
                }
            }
        }

        // Unilateral RIGHT free end with colateral interdental gap, that means,
        // a free end in the right and an interdental gap on the opposite side
        // of the mouth.
        $right_colateral_interdental_gap = $this->schema.interdental_gap(
            $region=new Region(18, 13, $this->schema)
        );

        if (
            $this->schema.upper_jaw_right_free_end
            and $right_colateral_interdental_gap
        ) {
            if ($right_colateral_interdental_gap->to_be_replaced_count >= 2) {
                if (in_array(end($right_colateral_interdental_gap->to_be_replaced).number,[14, 15])) {
                    $tooth_32_right = $this->schema.upper_jaw_right_free_end.teeth[0].left;

                    // Issues #133,#357=> 3.2 can not be at X5
                    if ($tooth_32_right.number == 25)
                        return True;

                    // We return the first TBT tooth after the free end and
                    // the first TBT tooth after the colateral interdental gap
                    // as our region
                    array_push($this->identified_subsidies,
                        [
                            "subsidy"=> "3.2",
                            "region"=> [$tooth_32_right],
                            "applied_rule"=> self,
                        ]
                    );
                    array_push($this->identified_subsidies,
                        [
                            "subsidy"=> "3.2",
                            "region"=> [$right_colateral_interdental_gap.b_tooth],
                            "applied_rule"=> self,
                        ]
                    );
                    return True;
                }
            }
        }
    }

    function mandible() {
        /*"""
        Unilateral free end TBR covering 48-44/45, 34/35-38 AND
        collateral interdental gap starting TBR from 44/45 or 34/35 with TBR=/>2
        """*/
        // Unilateral LEFT free end with colateral interdental gap, that means,
        // a free end in the left and an interdental gap on the opposite side
        // of the mouth.
        $left_colateral_interdental_gap = $this->schema.interdental_gap(
            $region=new Region(38, 33, $this->schema)
        );

        if (
            $this->schema.mandible_left_free_end
            and $left_colateral_interdental_gap
        ) {
            if ($left_colateral_interdental_gap->to_be_replaced_count >= 2) {
                if (in_array(end($left_colateral_interdental_gap->to_be_replaced).number, [
                    34,
                    35,
                ])) {
                    $tooth_32_left = $this->schema.mandible_left_free_end.teeth[
                        0
                    ].left;

                    // Issues #133,#357=> 3.2 can not be at X5
                    if ($tooth_32_left.number == 45)
                        return True;

                    // We return the first TBT tooth after the free end as our
                    // region and also the first TBT of the colateral interdental
                    // gap
                    array_push($this->identified_subsidies,
                        [
                            "subsidy"=> "3.2",
                            "region"=> [$tooth_32_left],
                            "applied_rule"=> self,
                        ]
                    );
                    array_push($this->identified_subsidies,
                        [
                            "subsidy"=> "3.2",
                            "region"=> [$left_colateral_interdental_gap.b_tooth],
                            "applied_rule"=> self,
                        ]
                    );
                    return True;
                }
            }
        }

        // Unilateral RIGHT free end with colateral interdental gap, that means,
        // a free end in the right and an interdental gap on the opposite side
        // of the mouth.
        $right_colateral_interdental_gap = $this->schema.interdental_gap(
            $region=new Region(43, 48, $this->schema)
        );

        if (
            $this->schema.mandible_right_free_end
            and $right_colateral_interdental_gap
        ) {
            if ($right_colateral_interdental_gap->to_be_replaced_count >= 2) {
                if (in_array($right_colateral_interdental_gap->to_be_replaced[0].number, [
                    44,
                    45,
                ])) {
                    $tooth_32_right = end($this->schema.mandible_right_free_end.teeth).right;

                    // Issues #133,#357=> 3.2 can not be at X5
                    if ($tooth_32_right.number == 35)
                        return True;

                    // We return the first TBT tooth after the free end as our
                    // region and also the first TBT of the colateral interdental
                    // gap
                    array_push($this->identified_subsidies,
                        [
                            "subsidy"=> "3.2",
                            "region"=> [$tooth_32_right],
                            "applied_rule"=> self,
                        ]
                    );
                    array_push($this->identified_subsidies,
                        [
                            "subsidy"=> "3.2",
                            "region"=> [$right_colateral_interdental_gap.a_tooth],
                            "applied_rule"=> self,
                        ]
                    );
                    return True;
                }
            }
        }
    }
}


// $unilateral_free_end_colateral_interdental_gap = (
//     new UnilateralFreeEndColateralInterdentalGap()
// );


class BilateralInterdentalGap {
    /*"""
    Bilateral interdental gap TBR at least one side TBR>2
    """*/
    // public $SeparatedJawRuleCompleteInterface;
    function __construct($schema, $identified_subsidies, $SeparatedJawRuleCompleteInterface=NULL) {
        $this->schema = $schema;
        $this->identified_subsidies = $identified_subsidies;
        $this->SeparatedJawRuleCompleteInterface = new SeparatedJawRuleCompleteInterface($schema, $identified_subsidies);
    }

    function upper_jaw() {
        /*"""
        Bilateral interdental gap TBR covering 17-14/15, 24/25-27
        starting TBR from 14/15 or 24/25 (at least one side TBR>2)
        """*/
        if (! $this->identified_subsidies.exists(
            $subsidy_code_startswith="3.1", $region=new Region.upper_jaw()
        )) {
            return False;
        }

        // Check LEFT interdental gap
        $left_interdental_gap = $this->schema.interdental_gap(
            $region=new Region(18, 13, $this->schema)
        );

        if ($left_interdental_gap == NULL)
            return False;

        if (! in_array(end($left_interdental_gap->to_be_replaced).number, [14, 15]))
            return False;

        // Check RIGHT interdental gap
        $right_interdental_gap = $this->schema.interdental_gap(
            $region=new Region(23, 28, $this->schema)
        );

        if ($right_interdental_gap == NULL)
            return False;

        if (! in_array($right_interdental_gap->to_be_replaced[0].number, [24, 25]))
            return False;

        // Check if there is TBR>2 at least in one side
        if (
            $left_interdental_gap->to_be_replaced_count > 2
            or $right_interdental_gap->to_be_replaced_count > 2
        ) {
            // We return the first two TBT tooth after the free ends (notice that X8 teeth
            // are missing here)
            array_push($this->identified_subsidies,
                [
                    "subsidy"=> "3.2",
                    "region"=> [$left_interdental_gap.b_tooth],
                    "applied_rule"=> self,
                ]
            );
            array_push($this->identified_subsidies,
                [
                    "subsidy"=> "3.2",
                    "region"=> [$right_interdental_gap.a_tooth],
                    "applied_rule"=> self,
                ]
            );

            return True;
        }
    }

    function mandible() {
        /*"""
        Bilateral interdental gap TBR covering 47-44/45, 34/35-37
        starting TBR from 44/45 or 34/35 (at least one side TBR>2)
        """*/
        if (! $this->identified_subsidies.exists(
            $subsidy_code_startswith="3.1", $region=new Region.mandible()
        ))
            return False;

        // Check LEFT interdental gap
        $left_interdental_gap = $this->schema.interdental_gap(
            $region=new Region(43, 48, $this->schema)
        );

        if ($left_interdental_gap == NULL)
            return False;

        if (! in_array($left_interdental_gap->to_be_replaced[0].number, [44, 45]))
            return False;

        // Check RIGHT interdental gap
        $right_interdental_gap = $this->schema.interdental_gap(
            $region=new Region(38, 33, $this->schema)
        );

        if ($right_interdental_gap == NULL)
            return False;

        if (! in_array($right_interdental_gap->to_be_replaced[-1].number, [34, 35]))
            return False;

        // Check if there is TBR>2 at least in one side
        if (
            $left_interdental_gap->to_be_replaced_count > 2
            or $right_interdental_gap->to_be_replaced_count > 2
        ) {
            // We return the first two TBT tooth after the free ends (notice that X8 teeth
            // are missing here)
            array_push($this->identified_subsidies,
                [
                    "subsidy"=> "3.2",
                    "region"=> [$left_interdental_gap.a_tooth],
                    "applied_rule"=> self,
                ]
            );

            array_push($this->identified_subsidies,
                [
                    "subsidy"=> "3.2",
                    "region"=> [$right_interdental_gap.b_tooth],
                    "applied_rule"=> self,
                ]
            );
            return True;
        }
    }
}


// $bilateral_interdental_gap = new BilateralInterdentalGap();


class BiggestInterdentalGapInFrontRegionExactToBeReplaced4 {
    /*"""
    Biggest interdental gap TBR=4// in 13-23 (TBRs must be all in front region)
    """*/

    // public $SeparatedJawRuleCompleteInterface;
    function __construct($schema, $identified_subsidies, $SeparatedJawRuleCompleteInterface=NULL) {
        $this->schema = $schema;
        $this->identified_subsidies = $identified_subsidies;
        $this->SeparatedJawRuleCompleteInterface = new SeparatedJawRuleCompleteInterface($schema, $identified_subsidies);
    }

    function _check_rule_in_region($region) {
        $biggest_interdental_gap = $this->schema.biggest_interdental_gap();

        if (! $biggest_interdental_gap)
            return False;

        // Issue 251=> Only the TBR must be in the region, not the whole
        // interdental gap
        if (! in_array(biggest_interdental_gap->to_be_replaced, $region))
            return False;

        if ($biggest_interdental_gap->to_be_replaced_count == 4) {
            array_push($this->identified_subsidies,
                rv_subsidy_2x(
                    [
                        "region"=> $biggest_interdental_gap.teeth_marking_abutment_tooth,
                        "subsidy"=> "2.4",
                        "applied_rule"=> self,
                    ]
                )
            );
            return True;
        }
    }

    function upper_jaw() {
        return $this->_check_rule_in_region(new Region.upper_jaw_front());
    }

    function mandible() {
        return $this->_check_rule_in_region(new Region.mandible_front());
    }
}


// $biggest_interdental_gap_in_front_region_exact_to_be_replaced_4 = (
//     new BiggestInterdentalGapInFrontRegionExactToBeReplaced4()
// );


class BiggestInterdentalGapExactToBeReplaced3 {
    /*"""
    Biggest interdental gap TBR=3#
    """*/
    // public $SeparatedJawRuleSimpleInterface;
    function __construct($schema, $identified_subsidies, $SeparatedJawRuleSimpleInterface=NULL) {
        $this->schema = $schema;
        $this->identified_subsidies = $identified_subsidies;
        $this->SeparatedJawRuleSimpleInterface = new SeparatedJawRuleSimpleInterface;
    }

    function execute_for($region) {
        $biggest_interdental_gap = $this->schema.biggest_interdental_gap($region);

        if (! $biggest_interdental_gap)
            return False;

        if ($biggest_interdental_gap->to_be_replaced_count == 3) {
            array_push($this->identified_subsidies,
                rv_subsidy_2x(
                    [
                        "region"=> $biggest_interdental_gap.teeth_marking_abutment_tooth,
                        "subsidy"=> "2.3",
                        "applied_rule"=> self,
                    ]
                )
            );
            return True;
        }
    }
}


// $biggest_interdental_gap_exact_to_be_replaced_3 = (
//     new BiggestInterdentalGapExactToBeReplaced3()
// );


class BiggestInterdentalGapExactToBeReplaced2 {
    /*"""
    Biggest interdental gap TBR=2#
    """*/

    public $SeparatedJawRuleSimpleInterface;
    function __construct($schema, $identified_subsidies, $SeparatedJawRuleSimpleInterface=NULL) {
        $this->schema = $schema;
        $this->identified_subsidies = $identified_subsidies;
        $this->SeparatedJawRuleSimpleInterface = new SeparatedJawRuleSimpleInterface;
    }

    function execute_for($region) {
        $biggest_interdental_gap = $this->schema.biggest_interdental_gap($region);

        if (! $biggest_interdental_gap)
            return False;

        if ($biggest_interdental_gap->to_be_replaced_count == 2) {
            array_push($this->identified_subsidies,
                rv_subsidy_2x(
                    [
                        "subsidy"=> "2.2",
                        "region"=> $biggest_interdental_gap.teeth_marking_abutment_tooth,
                        "applied_rule"=> self,
                    ]
                )
            );
            return True;
        }
    }
}


// $biggest_interdental_gap_exact_to_be_replaced_2 = (
//     new BiggestInterdentalGapExactToBeReplaced2()
// );


class BiggestInterdentalGapExactToBeReplaced1 {
    /*"""
    Biggest interdental gap TBR=1#
    """*/

    // public $SeparatedJawRuleSimpleInterface;
    function __construct($schema, $identified_subsidies, $SeparatedJawRuleSimpleInterface=NULL) {
        $this->schema = $schema;
        $this->identified_subsidies = $identified_subsidies;
        $this->SeparatedJawRuleSimpleInterface = new SeparatedJawRuleSimpleInterface;
    }

    function execute_for($region) {
        $biggest_interdental_gap = $this->schema.biggest_interdental_gap($region);

        if (! $biggest_interdental_gap)
            return False;

        if ($biggest_interdental_gap->to_be_replaced_count == 1) {
            array_push($this->identified_subsidies,
                rv_subsidy_2x(
                    [
                        "subsidy"=> "2.1",
                        "region"=> $biggest_interdental_gap.teeth_marking_abutment_tooth,
                        "applied_rule"=> self,
                    ]
                )
            );
            return True;
        }
    }
}


// $biggest_interdental_gap_exact_to_be_replaced_1 = (
//     new BiggestInterdentalGapExactToBeReplaced1()
// );


class BiggestInterdentalGapNeighboringGapExactToBeReplaced1 {
    /*"""
    Neighboring gap TBR=1#, in connection with 2.1/2.2/2.3 no
    clockwise definition of main gap (e.g. mandible kbbkbk)

    CAVEAT: notice that, specifically in this case:

        1) The neighboring gap concept does not need to be seen in
           clockwise view.

        2) Neighbor of a gap is only consider when they share at least one
           tooth.
    """*/
    // public $SeparatedJawRuleSimpleInterface;
    function __construct($schema, $identified_subsidies, $SeparatedJawRuleSimpleInterface=NULL) {
        $this->schema = $schema;
        $this->identified_subsidies = $identified_subsidies;
        $this->SeparatedJawRuleSimpleInterface = new SeparatedJawRuleSimpleInterface;
    }

    function execute_for($region) {
        $biggest_interdental_gap = $this->schema.biggest_interdental_gap($region);
        $neighboring_gap = NULL;

        if (! $biggest_interdental_gap)
            return False;

        // Limit for 2.1, 2.2 and 2.3 rules
        if (! in_array($biggest_interdental_gap->to_be_replaced_count, [1, 2, 3]))
            return False;

        foreach ($this->schema.all_interdental_gaps($region) as $gap) {
            if ($gap->region != $biggest_interdental_gap->region) {
                if ($biggest_interdental_gap.is_neighbor_of($gap)) {
                    if ($gap->to_be_replaced_count == 1) {
                        $neighboring_gap = $gap;
                        break;
                    }
                }
            }
        }

        if (! $neighboring_gap)
            return False;

        array_push($this->identified_subsidies,
            rv_subsidy_2x(
                [
                    "subsidy"=> "2.5",
                    "region"=> $neighboring_gap.teeth_marking_abutment_tooth,
                    "applied_rule"=> self,
                ]
            )
        );
        return True;
    }
}


// $biggest_interdental_gap_neighboring_gap_exact_to_be_replaced_1 = (
//     new BiggestInterdentalGapNeighboringGapExactToBeReplaced1()
// );


function evaluate_by_region($schema, $identified_subsidies) {
    /*"""
    Apply the "by region" rules for a given schema and return the subsidy that need to
    be used.
    """*/
    $rules = [
        ["rule"=> new Exact16ToBeReplacedTeeth($schema, $identified_subsidies)], //$GLOBALS['exact_16_to_be_replaced_teeth']],
        ["rule"=> new Between13And15ToBeReplacedTeeth($schema, $identified_subsidies)], //$GLOBALS['between_13_and_15_to_be_replaced_teeth']],
        [
            "rule"=> new BilateralFreeEnd($schema, $identified_subsidies), //$GLOBALS['bilateral_free_end'],
            "dependents"=> [
                new Exact2ToBeReplacedTeethInterdentalGapInFrontRegion($schema, $identified_subsidies),
                new Exact1ToBeReplacedTeethInterdentalGapInFrontRegion($schema, $identified_subsidies),
            ],
        ],
        [
            "rule"=> new UnilateralFreeEndToBeReplacedTeethAtLeast1($schema, $identified_subsidies), //$GLOBALS['unilateral_free_end_to_be_replaced_teeth_at_least_1'],
            "dependents"=> [
                new UnilateralFreeEndColateralInterdentalGap($schema, $identified_subsidies),
                new Exact2ToBeReplacedTeethInterdentalGapInFrontRegion($schema, $identified_subsidies),
                new Exact1ToBeReplacedTeethInterdentalGapInFrontRegion($schema, $identified_subsidies),
            ],
        ],
        [
            "rule"=> new Between5And12ToBeReplacedTeeth($schema, $identified_subsidies),
            "dependents"=> [
                new BilateralInterdentalGap($schema, $identified_subsidies),
                new Exact2ToBeReplacedTeethInterdentalGapInFrontRegion($schema, $identified_subsidies),
                new Exact1ToBeReplacedTeethInterdentalGapInFrontRegion($schema, $identified_subsidies),
            ],
        ],
        ["rule"=> new UnilateralFreeEndToBeReplacedX7andX8($schema, $identified_subsidies)],
        [
            "rule"=> new Exact4ToBeReplacedTeethInterdentalGapWithAtLeastOneInPosteriorRegion($schema, $identified_subsidies),
            "dependents"=> [
                new BilateralInterdentalGap($schema, $identified_subsidies),
                new Exact2ToBeReplacedTeethInterdentalGapInFrontRegion($schema, $identified_subsidies),
                new Exact1ToBeReplacedTeethInterdentalGapInFrontRegion($schema, $identified_subsidies),
            ],
        ],
        [
            "rule"=> new Between2And3ToBeReplacedTeethInterdentalGapNeighboringAnotherGap($schema, $identified_subsidies),
            "dependents"=> [
                new Exact2ToBeReplacedTeethInterdentalGapInFrontRegion($schema, $identified_subsidies),
                new Exact1ToBeReplacedTeethInterdentalGapInFrontRegion($schema, $identified_subsidies),
            ],
        ],
        [
            "rule"=> new NeighboringInterdentalGapsCountRelativeToTheMainGapGreaterThan1($schema, $identified_subsidies),
            "dependents"=> [
                new Exact2ToBeReplacedTeethInterdentalGapInFrontRegion($schema, $identified_subsidies),
                new Exact1ToBeReplacedTeethInterdentalGapInFrontRegion($schema, $identified_subsidies),
            ],
        ],
        [
            "rule"=> new BiggestInterdentalGapInFrontRegionExactToBeReplaced4($schema, $identified_subsidies),
            "dependents"=> [
                new BiggestInterdentalGapNeighboringGapExactToBeReplaced1($schema, $identified_subsidies)
            ],
        ],
        [
            "rule"=> new BiggestInterdentalGapExactToBeReplaced3($schema, $identified_subsidies),
            "dependents"=> [
                new BiggestInterdentalGapNeighboringGapExactToBeReplaced1($schema, $identified_subsidies)
            ],
        ],
        [
            "rule"=> new BiggestInterdentalGapExactToBeReplaced2($schema, $identified_subsidies),
            "dependents"=> [
                new BiggestInterdentalGapNeighboringGapExactToBeReplaced1($schema, $identified_subsidies)
            ],
        ],
        [
            "rule"=> new BiggestInterdentalGapExactToBeReplaced1($schema, $identified_subsidies),
            "dependents"=> [
                new BiggestInterdentalGapNeighboringGapExactToBeReplaced1($schema, $identified_subsidies)
            ],
        ]
    ];
    

    foreach ($rules as $rule) {
        $result = $rule["rule"];
        // $rule_to_execute = $rule["rule"];

        $dependents = array_key_exists('dependents', $rule) ? $rule['dependents'] : FALSE;

        // print_r($result);

        // $result = $rule_to_execute($schema, $identified_subsidies);

        if ($result) {
            // There are not dependents and rule was evaluated to True:
            // just return, no need more
            if (! $dependents)
                return True;

            // If there are a list of dependents for the rule, all of them will
            // be executed. The first to be evaluated to True will stop
            // evaluation
            foreach ($dependents as $dependent_to_execute) {
                $dependent_result = dependent_to_execute(
                    $schema, $identified_subsidies
                );

                if ($dependent_result)
                    return True;
            }
            return True;
        }
    }
}
