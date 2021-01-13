<?php
// Classes which defines the mouth schema, translating the real world
// mouth to a virtual one that will be used for subsidy calculations.

require_once('utils.php');
require_once('regions.php');
require_once('mixins.php');


class TeethSchema {
    public $SchemaInterdentalGapHandlingMixin;
    public $SchemaRegionIdentifierHandlingMixin;
    /*"""
    Represents a mouth with its teeth and the findings in each one.
    """*/

    // function __construct__($SchemaInterdentalGapHandlingMixin, $SchemaRegionIdentifierHandlingMixin, $teeth) {
    function __construct__($teeth) {
        // $this->SchemaInterdentalGapHandlingMixin = $SchemaInterdentalGapHandlingMixin;
        // $this->SchemaRegionIdentifierHandlingMixin = $SchemaRegionIdentifierHandlingMixin;

        $this->teeth = $teeth;
        foreach ($teeth as $i) {
            $i->set_schema(self);
        }
    }

    function to_be_replaced_count($region=NULL) {
        /*"""
        Count how many TBR teeth are in some specific region.
        """*/
        if ($region == NULL)
        {
            $region = new Region();
            $region = $region->whole_mouth();
        }

        return len($this->to_be_replaced($region=$region));
    }

    // function to_be_replaced($region=Region.whole_mouth()) {
    function to_be_replaced($region=NULL) {
        /*"""
        Return TBR teeth are in some specific region.
        """*/
        $to_be_replaced = [];
        if ($region == NULL)
        {
            $region = new Region();
            $region = $region->whole_mouth();
        }

        foreach ($this->teeth as $tooth) {
            if (in_array($tooth, $region)) {
                if ($tooth->to_be_replaced) {
                    array_push($to_be_replaced, $tooth);
                }
            }
        }
        return $to_be_replaced;
    }

    function get_abutment_teeth() {
        /*"""
        Return a list of teeth that are abutment.
        Abutment tooth: Its the tooth next to a gap.
        The A is the left one, B is the one on the other side
        (check the definitions page)
        """*/
        $abutment = [];
        foreach ($this->interdental_gaps as $region) {
            array_push($abutment, [$region['first'], $region['last']]);
        }
        return $abutment;
    }

    function remove_findings($teeth) {
        /*"""
        Remove findings/status of teeth.
        """*/
        foreach ($this->teeth as $tooth) {
            if (in_array($tooth, $teeth)) {
                $tooth['status'] = NULL;
                $tooth['finding'] = NULL;
            }
        }
    }

    function get_teeth_in_region($region) {
        /*"""
        Return all teeth in some region.
        """*/
        $toothes = [];
        foreach ($this->teeth as $tooth) {
            if (in_array($tooth, $region)) {
                array_push($toothes, $tooth);
            }
        }

        return $toothes;
    }

    function get_tooth($tooth_number) {
        /*"""
        Given a tooth number, return the tooth object in the schema.
        """*/

        $i_var = [];
        foreach ($this->teeth as $i) {
            if ($i['number'] == $tooth_number['0']) {
                array_push($i_var, $i['number']);
            }
        }

        return $i_var;
    }

    function __str__() {
        $condition = [];
        foreach ($this->teeth as $tooth) {
            array_push($condition, $tooth['condition']);
        }

        return implode(", ", $condition); //str([tooth.condition for tooth in $this->teeth]);
    }
}

class Tooth {
    /*"""
    Represent a tooth in the mouth with its findings/status.
    It is also similar to a chain, were each tooth knows its left side
    and right side tooth.
    """*/

    function __construct__($number, $status, $finding=NULL, $schema=NULL) {
        $this->number = $number;
        $this->status = $status;
        $this->finding = $finding;
        $this->schema = $schema;
        // RV are the same as covered insurance therapies
        $this->rv = []; //set();
        // Abutment Tooth is the first and last tooth of an interdental
        // gap. There is three possibilities here. NULL, we have
        // not calculated it, True it is an AT and False it is not an AT.
        $this->abutment_tooth = NULL;
    }

    function set_schema($schema) {
        $this->schema = $schema;
    }

    // @property
    function right() {
        /*"""
        Return the tooth that is just in the right of this object
        """*/
        if (!$this->schema) {
            throw new Exception("With no schema it is not possible to call right");
        }

        foreach (array_values ($this->schema['teeth']) as $i => $tooth) {
            if ($tooth == self) {
                if ($i < len($TOOTH_NUMBERS_ISO) - 1) {
                    return $this->schema['teeth'][$i + 1];
                }
                return NULL;
            }
        }

        return throw new Exception("Tooth not found in this schema.");
    }

    // @property
    function left() {
        /*"""
        Return the tooth that is just in the left of this object
        """*/
        if (! $this->schema) {
            throw new Exception("With no schema it is not possible to call left");
        }

        foreach (array_values ($this->schema['teeth']) as $i => $tooth) {
            if (tooth == self) {
                if ($i == 0) {
                    return NULL;
                }
                return $this->schema['teeth'][$i - 1];
            }
        }

        return throw new Exception("Tooth not found in this schema.");
    }

    // @property
    function condition() {
        if (!$this->finding) {
            return $this->status;
        }
        return $this->finding;
    }

    // @property
    function to_be_replaced() {
        /*"""
        To Be Replaced. This can be:
            a) f
            b) x
            c) b + neighboring finding (ww/kw/tw/pw/rw/x)
            d) ew
            e) sw
            f) bw
        """*/
        // bw added because of Issue #358
        if ( in_array($this->condition, ["f", "x", "ew", "sw", "fi", "bw"])) {
            return True;
        }

        if ($this->condition == "b") {
            // If the teeth near to this one is a TBR
            if (($this->left and $this->left->to_be_treated) or (
                $this->right and $this->right->to_be_treated
            )) {
                return True;
            }

            // If the teeth near to this one is an "x"
            if (($this->left and $this->left->condition == "x") or (
                $this->right and $this->right->condition == "x")) {
                return True;
            }

            // If the teeth near to this one is also a "b" (bridge)
            // and the last "b" is at the side of a TBT
            // Check from the left side
            $left = $this->left;

            while ($left) {
                if ($left->condition == "b") {
                    $left = $left->left;
                }
                else if ($left->to_be_treated) {
                    return True;
                }
                else {
                    break;
                }
            }

            // Check from the right side
            $right = $this->right;

            while ($right) {
                if ($right->condition == "b")
                    $right = $right->right;
                else if ($right->to_be_treated)
                    return True;
                else {
                    break;
                }
            }
        }

        return False;
    }

    // @property
    function to_be_treated() {
        /*"""
        To Be Treated are existing tooth with or without findings:
            ww/kw/tw/pw/rw.
        """*/
        if (in_array($this->condition, ["ww", "kw", "tw", "pw", "rw", "ur"])) {
            return True;
        }
        return False;
    }

    // @property
    function potential_h() {
        /*"""
        H is an Retention element planned by dentists to keep it "safe"
        positioned. Hs belong to existing teeth - could be any tooth existing.

        PH (Potential H):
             1) As it must be existing, it can be all `Non-TBR` (all
               teeth which are either okay or TBT)
             2) To be an help to another tooth, there must be a `TBR` next
               to it.
        """*/
        if (! $this->to_be_replaced) {
            // Ok or TBT tooth
            if ($this->to_be_treated || $this->condition === NULL) {
                // Check for a near TBR tooth
                if ($this->left AND $this->left->to_be_replaced) {
                    return True;
                }

                if ($this->right and $this->right->to_be_replaced) {
                    return True;
                }
            }
        }

        return False;
    }

    // @property
    function potential_t() {
        /*"""
        PT (Potential Telescope): if it is True, it means that this is the
        first tooth after a free end (if all teeths to the left or right = TBR)
        and the tooth itself is not a TBR.
        """*/
        if ($this->to_be_replaced) {
            return False;
        }

        // Upper jaw
        if (in_array(self, Region(15, 13))) {
            if (Region(18, 16, $this->schema)->to_be_replaced_count == 3)
                return True;
        }

        if (in_array(self, Region(23, 25))) {
            if (Region(26, 28, $this->schema)->to_be_replaced_count == 3)
                return True;
        }

        // Mandible
        if (in_array(self, Region(43, 45))) {
            if (Region(46, 48, $this->schema)->to_be_replaced_count == 3)
                return True;
        }

        if (in_array(self, Region(35, 33))) {
            if (Region(38, 36, $this->schema)->to_be_replaced_count == 3)
                return True;
        }

        return False;
    }

    function mandatory_treatment($subsidy_group) {
        /*"""
        Given the subsidies associated with this tooth, it returns if
        it is mandatory to have the tooth planned or not.

            1) True: mandatory plan
            2) False: it is not mandatory
            3) NULL: should not be planned, plan is impossible.

        To return the mandatory information we will follow this table:

        Tooth  |  Subsidy group |  Mandatory  | X8 handling
        -----------------------------------------------------
         TBT   |      1.x       |     True    |
         TBT   |      2.x       |     True    |
         TBT   |    2.x+2.x     |     True    |
         TBT   |      3.1       |     False   |
         TBT   |    3.1+1.x     |     True    |
         TBT   |    3.1+2.x     |     True    |
         TBT   |    3.1+3.x     |     True    |
         TBT   |      4.x       |     True    |
         -----------------------------------------------------
         TBR   |      1.x       | Not possible|
         TBR   |      2.x       |     True    |
         TBR   |      3.1       |     False   | Not plannable
         TBR   |    3.1+1.x     | Not possible|
         TBR   |    3.1+2.x     |     True    | Not plannable
         TBR   |    3.1+3.x     | Not possible|
         TBR   |      4.x       |     False   | Not plannable
        """*/
        if ($this->tbt_or_tbr_subsidy_based($subsidy_group) == "tbt") {
            if (subsidy_equivalent($subsidy_group, "1.x"))
                return True;
            if (subsidy_equivalent($subsidy_group, "2.x"))
                return True;
            if (subsidy_equivalent($subsidy_group, "2.x+2.x"))
                return True;
            if (subsidy_equivalent($subsidy_group, "3.1"))
                return False;
            if (subsidy_equivalent($subsidy_group, "3.1+1.x"))
                return True;
            if (subsidy_equivalent($subsidy_group, "3.1+2.x"))
                return True;
            if (subsidy_equivalent($subsidy_group, "3.1+3.x"))
                return True;
            if (subsidy_equivalent($subsidy_group, "4.x")) {
                return True;
            }
        }
        else {
            if (subsidy_equivalent($subsidy_group, "2.x"))
                return True;
            if (subsidy_equivalent($subsidy_group, "3.1")) {
                if (in_array(self, $RegionGroup->x8_region())) {
                    return NULL;
                }
                return False;
            }
            if (subsidy_equivalent($subsidy_group, "3.1+2.x")) {
                if (in_array(self, $RegionGroup->x8_region())) {
                    return NULL;
                }
                return True;
            }
            if (subsidy_equivalent($subsidy_group, "4.x")) {
                if (in_array(self, $RegionGroup->x8_region())) {
                    return NULL;
                }
                return False;
            }
        }
        return False;
    }

    function tbt_or_tbr_subsidy_based($subsidy_group) {
        /*"""
        Return if a tooth should be considered as TBT or TBR depending on
        the main subsidy. The rules we are following are these ones:

        1.x -> All are considered TBT
        2.x -> Missing teeth are TBR, neighbors are TBT
        3.x -> Missing teeth are TBR, others are TBT, X8 can only be TBT
        4.x -> Missing teeth are TBR, others are TBT, X8 can only be TBT

        Notice that, when we call something main subsidy, we are trying to
        say that 2.2+2.5, for example, will be considered true if we are
        comparing it with 2.x, but 3.2+2.1 will not be considered true
        comparing with 2.x, because its main subsidy is 3.2.
        """*/
        $main_subsidy = $subsidy_group.split("+")[0];

        if (subsidy_equivalent($main_subsidy, "1.x")) {
            return "tbt";
        }

        if (subsidy_equivalent($main_subsidy, "2.x")) {
            if ($this->to_be_replaced)
                return "tbr";
            return "tbt";
        }

        if (subsidy_equivalent($main_subsidy, "3.x") or subsidy_equivalent(
            $main_subsidy, "4.x")) {
            if (in_array(self, RegionGroup.x8_region())) {
                // X8 can only be TBT, if it is TBR is the only case we will
                // return NULL
                if ($this->to_be_replaced)
                    return NULL;
                return "tbt";
            }

            if ($this->to_be_replaced) {
                return "tbr";
            }
            return "tbt";
        }
    }

    function __eq__($other) {
        /*"""Make comparison between tooth possible"""*/
        // if isinstance(other, Tooth)
        if (is_array($other))
            return $this->number == $other->number;
        return False;
    }

    function __str__() {
        return str($this->number);
    }

    // __repr__ = __str__;
}
