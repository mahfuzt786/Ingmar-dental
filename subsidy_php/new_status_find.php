<?php

    function right($tooth_number, $schema) {
        /*"""
        Return the tooth that is just in the right of this object
        """*/

        $position_schema = position_schema($tooth_number);

        if($position_schema == 31) {
            return false;
        }
        else {
            $position_schema = $position_schema + 1;

            $tooth = $GLOBALS['TOOTH_NUMBERS_ISO'][$position_schema];
            $status = $schema[$tooth];

            return ["status" => $status, "tooth" => $tooth];
        }
    }

    function left($tooth_number, $schema) {
        /*"""
        Return the tooth that is just in the left of this object
        """*/
        $position_schema = position_schema($tooth_number);

        if($position_schema == 0) {
            return false;
        }
        else {
            $position_schema = $position_schema - 1;

            $tooth = $GLOBALS['TOOTH_NUMBERS_ISO'][$position_schema];
            $status = $schema[$tooth];

            // return $status;
            return ["status" => $status, "tooth" => $tooth];
        }
    }


    function to_be_replaced_count($start, $end, $schema) {
        /*"""
        Count how many TBR teeth are in some specific region.
        """*/
        $to_be_replaced_count = [];

        // if ($region == NULL)
        // {
        //     $region = new Region();
        //     $region = $region->whole_mouth();
        // } 0 , 2

        if($start < $end)
        {
            foreach($schema as $key => $value) {
                if(to_be_replaced($value) AND position_schema($key)<= $end) {
                    array_push($to_be_replaced_count, $value);
                }
            }
        }

        return count($to_be_replaced_count);
    }

    function to_be_replaced($condition, $tooth_number, $schema) {
        /*"""
        To Be Replaced. This can be:
            a) f
            b) x
            c) b + neighboring finding (ww/kw/tw/pw/rw/x)
            d) ew
            e) sw
            f) bw
        """*/
        if(!$condition) {
            $condition = '';
        }
        if ( in_array($condition, ["f", "x", "ew", "sw", "fi", "bw"])) {
            return True;
        }

        if ($condition == "b") {
            // If the teeth near to this one is a TBR
            if (to_be_treated(left($tooth_number, $schema)['status']) OR
                to_be_treated(right($tooth_number, $schema)['status'])
            ) {
                return True;
            }

            // If the teeth near to this one is an "x"
            if (left($tooth_number, $schema)['status'] == "x" OR
                right($tooth_number, $schema)['status'] == "x") {
                return True;
            }

            // If the teeth near to this one is also a "b" (bridge)
            // and the last "b" is at the side of a TBT
            // Check from the left side
            // $left = $this->left;

            // while ($left) {
            //     if ($left->condition == "b") {
            //         $left = $left->left;
            //     }
            //     else if ($left->to_be_treated) {
            //         return True;
            //     }
            //     else {
            //         break;
            //     }
            // }

            // // Check from the right side
            // $right = $this->right;

            // while ($right) {
            //     if ($right->condition == "b")
            //         $right = $right->right;
            //     else if ($right->to_be_treated)
            //         return True;
            //     else {
            //         break;
            //     }
            // }
        }

        return False;
    }

    function to_be_treated($condition) {
        /*"""
        To Be Treated are existing tooth with or without findings:
            ww/kw/tw/pw/rw.
        """*/
        if (in_array($condition, ["ww", "kw", "tw", "pw", "rw", "ur"])) {
            return True;
        }
        return False;
    }

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

    function subsidy_equivalent($subsidy_group_left, $subsidy_group_right) {
        /**"""
        Check if one subsidy_left and subsidy_right are equivalent.
    
        Possible cases:
           "1.1" = "1.1" -> True
           "1.x" = "1.1" -> True
           "1.1" = "1.x" -> True
           "1.x" = "1.x" -> True
           "2.1+1.1" = "2.1+1.1" -> True
           "2.1+1.1" = "2.x+1.1" -> True
           "2.1+1.1" = "2.1+1.x" -> True
        """**/
        if ($subsidy_group_left == $subsidy_group_right) {
            return True;
        }
    
        $subsidy_list_left = $subsidy_group_left.split("+");
        $subsidy_list_right = $subsidy_group_right.split("+");
    
        // Number of subsidies are different
        if (len($subsidy_list_left) != len($subsidy_list_right)) {
            return False;
        }
    
        foreach (array_map(NULL, $subsidy_list_left, $subsidy_list_right) as $subsidy_left => $subsidy_right) {
            if (in_array("x", $subsidy_right)) {
                // if (f"{subsidy_left.split('.')[0]}.x" != $subsidy_right) {
                if ($subsidy_left.split('.')[0].x != $subsidy_right) {
                    return False;
                }
            }
    
            if (in_array("x", $subsidy_left)) {
                // if (f"{subsidy_right.split('.')[0]}.x" != $subsidy_left) {
                if ($subsidy_right.split('.')[0].x != $subsidy_left) {
                    return False;
                }
            }
    
            if (! in_array("x", $subsidy_left) AND ! in_array("x",$subsidy_right)) {
                if ($subsidy_left != $subsidy_right) {
                    return False;
                }
            }
        }
    
        return True;
    }

?>