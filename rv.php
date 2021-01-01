<!-- /*"""
RV are the same as covered insurance therapies. They are calculated
as a bunch of rules applied over each tooth depending on the subsidy
applied to the tooth.

There are 6 main codes that a tooth can assume (they are not exclusive
between them):

    1) E: A tooth which needs to be replaced (e.g. for 4.x you get a
       prothese so all existing teeth are removed)
    2) K: A crown (a treatment for an existing teeth)
    3) T: A telescope crown (works as support)
    4) H: an retention element planned by dentists to keep it "safe"
       positioned, Hs belong to existing teeth - could be any tooth existing
    5) B: Part of a bridge (abutment teeth will become K, even if they have
       been OK before)
    6) V: A blending (added to other RV codes)

The rules here will receive a subsidy recently applied, check if the teeth
in the region needs an RV to be added into it and, if it has, add it into the
tooth RV attribute.
"""*/ -->
<?php
require_once('regions.php');

function rv_subsidy_4x($subsidy) {
    // """
    // B+K never possible
    // E: If TBR
    // T: If TBT
    // +V: Veneer grant (when 4.7 was calculated, in the visible area and if T)
    // """
    foreach ($subsidy["region"] as $tooth) {
        if ($tooth.to_be_replaced)
            $tooth.rv.add("E");

        if ($tooth.to_be_treated) {
            $tooth.rv.add("T");

            if ($subsidy["subsidy"] == "4.7") {
                $tooth.rv.add("V");
            }
        }
    }
    return $subsidy;
}


function rv_subsidy_3x($subsidy) {
    // """
    // B: if 2.1+2.2 region + TBR
    // K: If 2.1+2.2 region and Abutment tooth
    // E: If TBR
    // H: If potential H
    // As it must be existing, it can be all `Non-TBR` (all teeth which are either okay or TBT)
    // To be an help to another tooth, there must be a `TBR` next to it
    // T: if potential T:
    // thats the first tooth after a free end (if all teeths to the left or right = TBR
    // +V: Veneer grant (if visible area + either K, B or T)
    // """
    for $tooth in $subsidy["region"]:
        if $subsidy["subsidy"] in ["2.1", "2.2"] {
            if $tooth.to_be_replaced
                $tooth.rv.add("B")

            if $tooth.abutment_tooth
                $tooth.rv.add("K")
        }

        if $tooth.to_be_replaced:
            $tooth.rv.add("E")

        if $tooth.potential_h:
            $tooth.rv.add("H")

        if $tooth.potential_t:
            $tooth.rv.add("T")

        if $tooth in RegionGroup.visible_area() and {"K", "B", "T"}.intersection(
            set($tooth.rv)
        )
            $tooth.rv.add("V")

    return $subsidy
}


function rv_subsidy_2x($subsidy) {
    // """
    // E and T not possible
    // B: if TBR
    // K: If Abutment tooth
    // +V: if 2.7 was calculated (visible area)
    // OK teeth get no RV info
    // """
    for $tooth in subsidy["region"] {
        if $tooth.to_be_replaced {
            $tooth.rv.add("B")
        }
        if $tooth.abutment_tooth {
            $tooth.rv.add("K")
        }
        if $tooth in RegionGroup.visible_area() {
            $tooth.rv.add("V")
        }
    }

    return $subsidy;
}


function rv_subsidy_1x($subsidy) {
    // """
    // B, E and T not possible
    // K: If TBT
    // +P: If 1.2
    // +V: If 1.3 / visible area
    // OK teeth get no RV info
    // """
    // # Normally we have only one tooth in 1.X subsidy,
    // # I just kept the for to follow a pattern
    for $tooth in $subsidy["region"] {
        if $tooth.to_be_treated:
            $tooth.rv.add("K")

        if $subsidy["subsidy"] == "1.2":
            $tooth.rv.add("P")

        if ($subsidy["subsidy"] == "1.3" or $tooth in RegionGroup.visible_area()) {
            tooth.rv.add("V");
        }
    }

    return $subsidy;
}
