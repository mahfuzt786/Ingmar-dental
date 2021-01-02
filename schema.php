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

    function __construct__($teeth) {
        self->teeth = $teeth;
        foreach ($teeth as $i) {
            $i.set_schema(self);
        }
    }

    function to_be_replaced_count($region=Region.whole_mouth()) {
        /*"""
        Count how many TBR teeth are in some specific region.
        """*/
        return len(self->to_be_replaced($region=$region));
    }

    function to_be_replaced($region=Region.whole_mouth()) {
        /*"""
        Return TBR teeth are in some specific region.
        """*/
        $to_be_replaced = [];
        for $tooth in self->teeth {
            if $tooth in region {
                if $tooth.to_be_replaced {
                    $to_be_replaced.append(tooth);
                }
            }
        }
        return to_be_replaced;
    }

    function get_abutment_teeth(self) {
        /*"""
        Return a list of teeth that are abutment.
        Abutment tooth: Its the tooth next to a gap.
        The A is the left one, B is the one on the other side
        (check the definitions page)
        """*/
        abutment = []
        for region in self->interdental_gaps
            abutment.extend([region.first, region.last])
        return abutment;
    }

    function remove_findings(self, teeth) {
        /*"""
        Remove findings/status of teeth.
        """*/
        for tooth in self->teeth {
            if tooth in teeth {
                tooth.status = None;
                tooth.finding = None;
            }
        }
    }

    function get_teeth_in_region(self, region) {
        /*"""
        Return all teeth in some region.
        """*/
        return [tooth for tooth in self->teeth if tooth in region];
    }

    function get_tooth(self, tooth_number) {
        /*"""
        Given a tooth number, return the tooth object in the schema.
        """*/
        return [i for i in self->teeth if i.number == tooth_number][0];
    }

    function __str__(self) {
        return str([tooth.condition for tooth in self->teeth]);
    }
}

class Tooth {
    /*"""
    Represent a tooth in the mouth with its findings/status.
    It is also similar to a chain, were each tooth knows its left side
    and right side tooth.
    """*/

    function __init__(self, number, status, finding=None, schema=None) {
        self->number = number
        self->status = status
        self->finding = finding
        self->schema = schema
        // RV are the same as covered insurance therapies
        self->rv = set()
        // Abutment Tooth is the first and last tooth of an interdental
        // gap. There is three possibilities here. None, we have
        // not calculated it, True it is an AT and False it is not an AT.
        self->abutment_tooth = None
    }

    function set_schema(self, schema) {
        self->schema = schema
    }

    @property
    function right(self) {
        /*"""
        Return the tooth that is just in the right of this object
        """*/
        if not self->schema {
            raise ValueError("With no schema it is not possible to call right");
        }

        for $i, $tooth in enumerate(self->schema.teeth) {
            if $tooth == self {
                if $i < len($TOOTH_NUMBERS_ISO) - 1 {
                    return self->schema.teeth[i + 1];
                }
                return None
            }
        }

        return ValueError("Tooth not found in this schema.");
    }

    @property
    function left(self) {
        /*"""
        Return the tooth that is just in the left of this object
        """*/
        if not self->schema {
            raise ValueError("With no schema it is not possible to call left")
        }

        for i, tooth in enumerate(self->schema.teeth) {
            if tooth == self {
                if i == 0 {
                    return None;
                }
                return self->schema.teeth[i - 1];
            }
        }

        return ValueError("Tooth not found in this schema.")
    }

    @property
    function condition(self) {
        if not self->finding {
            return self->status;
        }
        return self->finding;
    }

    @property
    function to_be_replaced(self) {
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
        if self->condition in ["f", "x", "ew", "sw", "fi", "bw"]
            return True

        if self->condition == "b" {
            // If the teeth near to this one is a TBR
            if (self->left and self->left.to_be_treated) or (
                self->right and self->right.to_be_treated
            ) {
                return True;
            }

            // If the teeth near to this one is an "x"
            if (self->left and self->left.condition == "x") or (
                self->right and self->right.condition == "x"
            ) {
                return True;
            }

            // If the teeth near to this one is also a "b" (bridge)
            // and the last "b" is at the side of a TBT
            // Check from the left side
            left = self->left;

            while left {
                if left.condition == "b" {
                    left = left.left
                elseif left.to_be_treated
                    return True
                else
                    break;
                }
            }

            // Check from the right side
            right = self->right;

            while right {
                if right.condition == "b"
                    right = right.right
                elseif right.to_be_treated
                    return True
                else {
                    break
                }
            }
        }

        return False;
    }

    @property
    function to_be_treated(self) {
        /*"""
        To Be Treated are existing tooth with or without findings:
            ww/kw/tw/pw/rw.
        """*/
        if self->condition in ["ww", "kw", "tw", "pw", "rw", "ur"] {
            return True;
        }
        return False;
    }

    @property
    function potential_h(self) {
        /*"""
        H is an Retention element planned by dentists to keep it "safe"
        positioned. Hs belong to existing teeth - could be any tooth existing.

        PH (Potential H):
             1) As it must be existing, it can be all `Non-TBR` (all
               teeth which are either okay or TBT)
             2) To be an help to another tooth, there must be a `TBR` next
               to it.
        """*/
        if self->to_be_replaced is False {
            // Ok or TBT tooth
            if self->to_be_treated or self->condition is None {
                // Check for a near TBR tooth
                if self->left and self->left.to_be_replaced {
                    return True;
                }

                if self->right and self->right.to_be_replaced {
                    return True
                }
            }
        }

        return False;
    }

    @property
    function potential_t(self) {
        /*"""
        PT (Potential Telescope): if it is True, it means that this is the
        first tooth after a free end (if all teeths to the left or right = TBR)
        and the tooth itself is not a TBR.
        """*/
        if self->to_be_replaced {
            return False;
        }

        // Upper jaw
        if self in Region(15, 13)
            if Region(18, 16, self->schema).to_be_replaced_count == 3
                return True

        if self in Region(23, 25)
            if Region(26, 28, self->schema).to_be_replaced_count == 3
                return True

        // Mandible
        if self in Region(43, 45)
            if Region(46, 48, self->schema).to_be_replaced_count == 3
                return True

        if self in Region(35, 33)
            if Region(38, 36, self->schema).to_be_replaced_count == 3
                return True

        return False;
    }

    function mandatory_treatment(self, subsidy_group) {
        /*"""
        Given the subsidies associated with this tooth, it returns if
        it is mandatory to have the tooth planned or not.

            1) True: mandatory plan
            2) False: it is not mandatory
            3) None: should not be planned, plan is impossible.

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
        if self->tbt_or_tbr_subsidy_based(subsidy_group) == "tbt" {
            if subsidy_equivalent(subsidy_group, "1.x")
                return True
            if subsidy_equivalent(subsidy_group, "2.x")
                return True
            if subsidy_equivalent(subsidy_group, "2.x+2.x")
                return True
            if subsidy_equivalent(subsidy_group, "3.1")
                return False
            if subsidy_equivalent(subsidy_group, "3.1+1.x")
                return True
            if subsidy_equivalent(subsidy_group, "3.1+2.x")
                return True
            if subsidy_equivalent(subsidy_group, "3.1+3.x")
                return True
            if subsidy_equivalent(subsidy_group, "4.x") {
                return True
            }
        }
        else {
            if subsidy_equivalent(subsidy_group, "2.x")
                return True
            if subsidy_equivalent(subsidy_group, "3.1")
                if self in RegionGroup.x8_region()
                    return None
                return False
            if subsidy_equivalent(subsidy_group, "3.1+2.x")
                if self in RegionGroup.x8_region()
                    return None
                return True
            if subsidy_equivalent(subsidy_group, "4.x")
                if self in RegionGroup.x8_region()
                    return None
                return False
        }
        return False;
    }

    function tbt_or_tbr_subsidy_based(self, subsidy_group) {
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

        if subsidy_equivalent(main_subsidy, "1.x") {
            return "tbt";
        }

        if subsidy_equivalent(main_subsidy, "2.x") {
            if self->to_be_replaced
                return "tbr";
            return "tbt";
        }

        if subsidy_equivalent(main_subsidy, "3.x") or subsidy_equivalent(
            main_subsidy, "4.x"
        ) {
            if self in RegionGroup.x8_region() {
                // X8 can only be TBT, if it is TBR is the only case we will
                // return None
                if self->to_be_replaced
                    return None
                return "tbt"
            }

            if self->to_be_replaced {
                return "tbr";
            }
            return "tbt";
        }
    }

    function __eq__(self, other) {
        /*"""Make comparison between tooth possible"""*/
        if isinstance(other, Tooth)
            return self->number == other.number
        return False
    }

    function __str__(self) {
        return str(self->number);
    }

    __repr__ = __str__
}
