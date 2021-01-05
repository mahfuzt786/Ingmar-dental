<?php
/*"""
Mixins for the subsidy package.
"""*/
// from apps.therapies.subsidy.regions import InterdentalGap, Region
require_once('regions.php');

class SchemaInterdentalGapHandlingMixin {
    /*"""
    Adds to the schema methods related to interdental gaps.

    The definition of an interdental gap is: TBRs between two existing teeth
    (TBT).
    """*/

    function _generate_interdental_gaps($region) {
        /*"""
        Generate interdental gaps for a given VALID region, by valid you must read
        a region only with teeth that can be used for an interdental gap (that is,
        teeth only in one of mouth sides)
        """*/
        $not_to_be_replaced = [$i for $i in $this->teeth if not $i.to_be_replaced];
        $interdental_gaps = [];

        for $to_be_treated_left in $not_to_be_replaced {
            $right = $to_be_treated_left.right;
            while $right and $right.to_be_replaced {
                $right = $right.right;

                if $right and $right in $not_to_be_replaced {
                    $interdental_gap = InterdentalGap(
                        $to_be_treated_left, $right, self)

                    if $interdental_gap in $region {
                        $interdental_gaps.append($interdental_gap);
                    }
                }
            }

        return $interdental_gaps;
    }

    function all_interdental_gaps(self, region=NULL) {
        /*"""
        Return the region of all interdental gaps in order. The first one
        will be the main one for the region.
        """*/
        // For the whole mouth you need to split in the two parts, once there
        // is no gap between them.
        if region is NULL {
            interdental_gaps = []
            interdental_gaps.extend(
                $this->_generate_interdental_gaps(Region.upper_jaw())
            )
            interdental_gaps.extend(
                $this->_generate_interdental_gaps(Region.mandible())
            )
            return interdental_gaps
        }

        return $this->_generate_interdental_gaps(region);
    }

    function main_interdental_gap($region) {
        /*"""
        The main interdental gap is the first one that appears on the mouth following
        the ISO teeth order clockwise.

        This concept was changed in 25/01/2019. Now the main interdental
        gap is the same as the biggest one.
        """*/
        return $this->biggest_interdental_gap($region);
    }

    function biggest_interdental_gap(self, region=Region.whole_mouth()) {
        $interdental_gaps = $this->all_interdental_gaps($region);
        if interdental_gaps {
            return max(interdental_gaps, key=lambda item: len(item));
        }
    }

    function interdental_gap(self, $region) {
        /*"""
        Check if there is an interdental gap in some specific region. If there are,
        it will return the first found.
        """*/
        $interdental_gaps = $this->all_interdental_gaps();
        for interdental_gap in interdental_gaps {
            if interdental_gap in region {
                return interdental_gap;
            }
        }
    }

    function interdental_gaps(self, $region) {
        /*"""
        Check if there is an interdental gap in some specific region. If there are,
        it will return the all that we found.
        """*/
        $gaps_in_region = [];
        $interdental_gaps = $this->all_interdental_gaps();
        for interdental_gap in interdental_gaps {
            if $interdental_gap in region {
                $gaps_in_region.append($interdental_gap);
            }
        }
        return $gaps_in_region;
    }

    function interdental_gap_with_to_be_replaced_count(self, $region, $count) {
        /*"""
        Check if there is an interdental gap with some specific TBR count.
        """*/
        $interdental_gaps = $this->all_interdental_gaps();

        for $interdental_gap in $interdental_gaps {
            if $interdental_gap in $region {
                if $this->to_be_replaced_count($region=$interdental_gap) == $count {
                    return $interdental_gap;
                }
            }
        }
    }

    function neighboring_gap(self, $obj, $region=Region.whole_mouth()) {
        /*"""
        Return the first neighboring gap clockwise for the object informed
        """*/
        for $interdental_gap in $this->all_interdental_gaps() {
            if $interdental_gap.is_neighbor_of($obj) {
                if $interdental_gap in $region {
                    return $interdental_gap;
                }
            }
        }
        return NULL;
    }
}


class SchemaRegionIdentifierHandlingMixin {
    /*"""
    Mixin to add methods that are capable of identify common regions in
    the schema.
    """*/

    // Upper Jaw
    @property
    function upper_jaw_left_free_end(self) {
        /*"""
        Return True if there is a free end in the left side of upper jaw.
        """*/
        $full_free_end = Region(18, 14, self)
        $four_free_end = Region(18, 15, self)
        $tree_free_end = Region(18, 16, self)

        if $full_free_end.to_be_replaced_count == 5
            // Issue #357: free end must not surpass X6, that is,
            // it should not have a TBR X3 ahead
            if Region(13, 13, self).to_be_replaced
                return NULL
            return $full_free_end

        if four_free_end.to_be_replaced_count == 4
            return $four_free_end

        // Added because Issue #133
        if tree_free_end.to_be_replaced_count == 3
            return $tree_free_end

        return NULL
    }

    @property
    function upper_jaw_right_free_end(self) {
        /*"""
        Return True if there is a free end in the right side of upper jaw.

        The free end needs to finish in 24/25, if it extends to 23 or more, it
        will not be considered a free end anymore.
        """*/
        $full_free_end = Region(24, 28, self)
        $four_free_end = Region(25, 28, self)
        $tree_free_end = Region(26, 28, self)

        if full_free_end.to_be_replaced_count == 5
            // Issue #357: free end must not surpass X6, that is,
            // it should not have a TBR X3 ahead
            if Region(23, 23, self).to_be_replaced
                return NULL
            return full_free_end

        if four_free_end.to_be_replaced_count == 4
            return four_free_end

        // Added because Issue #133. We had not considered 3 teeth
        // free end before it.
        if tree_free_end.to_be_replaced_count == 3
            return tree_free_end

        return NULL
    }

    // Mandible
    @property
    function mandible_left_free_end(self) {
        /*"""
        Return True if there is a free end in the left side of mandible.
        """*/
        $full_free_end = Region(44, 48, self)
        $four_free_end = Region(45, 48, self)
        $tree_free_end = Region(46, 48, self)

        if $full_free_end.to_be_replaced_count == 5
            // Issue #357: free end must not surpass X6, that is,
            // it should not have a TBR X3 ahead
            if Region(43, 43, self).to_be_replaced
                return NULL
            return $full_free_end

        if $four_free_end.to_be_replaced_count == 4
            return $four_free_end;

        // Added because Issue #357.
        if $tree_free_end.to_be_replaced_count == 3
            return $tree_free_end

        return NULL;
    }

    @property
    function mandible_right_free_end(self) {
        /*"""
        Return True if there is a free end in the right side of mandible.
        """*/
        $full_free_end = Region(38, 34, self);
        $four_free_end = Region(38, 35, self);
        $tree_free_end = Region(38, 36, self);

        if $full_free_end.to_be_replaced_count == 5
            // Issue #357: free end must not surpass X6, that is,
            // it should not have a TBR X3 ahead
            if Region(33, 33, self).to_be_replaced
                return NULL
            return full_free_end

        if $four_free_end.to_be_replaced_count == 4 {
            return $four_free_end;
        }

        // Added because Issue #357.
        if $tree_free_end.to_be_replaced_count == 3 {
            return $tree_free_end;
        }

        return NULL;
    }
}
