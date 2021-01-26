<?php
/*"""
Classes that represents regions (more than one tooth) in the mouth.
"""*/
// from apps.therapies.subsidy.interfaces import NeighborInterface, RegionInterface
// from apps.therapies.subsidy.utils import TeethNumbersList

require_once('interfaces.php');
require_once('utils.php');

/*
class InterdentalGap(NeighborInterface, RegionInterface) {
    """
    Represents an interdental gap. The definition of an interdental gap
    is TBRs between two existing teeth (TBT).
    """

    function __init__(self, a_tooth, b_tooth, schema)
        $this->a_tooth = a_tooth
        $this->b_tooth = b_tooth
        $this->region = Region(a_tooth.number, b_tooth.number, schema)

    //@property
    function teeth(self)
        return $this->region.teeth

    //@property
    function teeth_marking_abutment_tooth(self)
        """
        Mark inplace (that is, with an attribute in Tooth) if the each tooth
        in the interdental gap is AT or not.
        """
        for tooth in $this->teeth
            tooth.abutment_tooth = tooth in ($this->a_tooth, $this->b_tooth)
        return $this->teeth

    //@property
    function to_be_replaced(self)
        """
        List of teeth that need to be replaced.
        """
        return $this->region.to_be_replaced

    function __contains__(self, key)
        return key in $this->region

    function __len__(self)
        return len($this->region)

    function __eq__(self, obj)
        return (
            $this->a_tooth.number == obj.a_tooth.number
            and $this->b_tooth.number == obj.b_tooth.number
        )

    function is_neighbor_of(self, obj)
        """
        A neighbor gap is only considered in clockwise view, so that
        we test if the next tooth (after the gap) is inside the other
        gap we are testing with.
        """
        # Issue #362 Gaps are considered neighbors only when they share
        # one abutment tooth, notice that it is different of the neighbor
        # concept for a region.
        if isinstance(obj, InterdentalGap)
            # Assure obj is not the same as self
            if self == obj
                return False
            return $this->a_tooth in obj.region or $this->b_tooth in obj.region
        return $this->region.is_neighbor_of(obj)
}
*/


class RegionGroup {
    /*"""
    Represent a group of regions in a mouth, for example the ends of the
    mouth represented by X6-X8.
    """*/
    public $NeighborInterface;
    public $RegionInterface;

    //@classmethod
    function upper_jaw_end() {
        /*"""
        Represent the end group region (three last teeth) for the upper jaw.
        """*/
        return [new Region(18, 16), new Region(26, 28)];
    }

    //@classmethod
    function mandible_end() {
        /*"""
        Represent the end group region (three last teeth) for the mandible.
        """*/
        return [new Region(38, 36), new Region(46, 48)];
    }

    //@classmethod
    function mouth_end() {
        /*"""
        Represent the end group region (three last teeth) for the whole mouth.
        """*/
        return $upper_jaw_end .''. $mandible_end;
    }

    //@classmethod
    function upper_jaw_posterior_region() {
        /*"""
        Represent the front region (three last teeth) for the right side of the mouth.
        """*/
        return [new Region(18, 14), new Region(24, 28)];
    }

    //@classmethod
    function mandible_posterior_region() {
        /*"""
        Represent the front region (three last teeth) for the right side of the mouth.
        """*/
        return [new Region(44, 48), new Region(38, 34)];
    }

    //@classmethod
    function visible_area() {
        /*"""
        Represent the teeth that are located in the visible area.

        NOTE the there are more teeth considered in the visible in the
        upper jaw than in the mandible.
        """*/
        return [new Region(15, 25), new Region(34, 44)];
    }

    //@classmethod
    function anterior_area() {
        /*"""
        Represent the teeth that are located in the anterior area.
        """*/
        return [new Region(13, 23), new Region(33, 43)];
    }

    //@classmethod
    function x8_region() {
        /*"""
        Represent the teeth that are located in the X8 areas.
        """*/
        return [
            new Region(18, 18), new Region(28, 28), new Region(38, 38), new Region(48, 48)
        ];
    }

    function __construct($args) {
        /*"""
        Join several regions in a group that allows operations with regions
        to be made in relation to the whole group. It is useful when regions
        you want to operate with are not of contiguous teeth.
        """*/
        $this->regions = $args;
        $this->NeighborInterface = new NeighborInterface();
        $this->RegionInterface = new RegionInterface();
    }

    function __add__($another) {
        if (! $another instanceof RegionGroup)
            throw new Exception("You can only add a RegionGroup to another");

        return RegionGroup($this->regions.' '.$another->regions);
    }

    function __contains__($obj) {
        /*"""
        Override "in" operator for this object to allow constructions like this

        >>> Tooth(28, 'f') in RegionGroup(18, 28)
        or
        >>> Region(17, 14) in RegionGroup(18, 28)
        or
        >>> [Tooth(23, 'f'),..] in RegionGroup(18, 28)
        or
        >>> [Region(17, 14),..] in RegionGroup(18, 28)
        """*/
        // from apps.therapies.subsidy.schema import Tooth
        require_once('schema.php');

        if ($obj instanceof Tooth) {
            foreach ($this->regions as $region) {
                if (in_array($obj->number, $region->teeth_numbers))
                    return True;
            }
            return False;
        }

        if ($obj instanceof Region) {
            $all_teeth = [];//set()
            foreach ($this->regions as $region)
                array_push($all_teeth, $region->teeth_numbers);

            if (in_array($obj->teeth_numbers, $teeth_numbers))
                return True;
            return False;
        }

        // if isinstance(obj, InterdentalGap)
        //     return obj.region in self

        if (is_array($obj)) {
            // If one of the items is not in a region, the whole
            // contains will become false
            foreach($obj as $item) {
                $item_in_regions = False;
                foreach($this->regions as $region) {
                    if (in_array($item, $region))
                        $item_in_regions = True;
                }

                if (! $item_in_regions)
                    return False;
            }

            return True;
        }

        throw new Exception("Invalid object used with in operator {obj.__class__}");
    }

    function __len__() {
        return sizeof($this->regions);
        // return sum([len(i) for i in $this->regions]);
    }

    //@property
    function teeth() {
        $teeth = [];
        foreach($this->regions as $region)
            array_push($teeth, $region->teeth);
        return $teeth;
    }

    function get_teeth_with_condition($condition) {
        /*"""
        Return a list with all teeth in the group region that have the condition informed.
        """*/
        $teeth = [];
        // foreach($this->regions as $region)
        //     array_push($teeth, $region->get_teeth_with_condition($condition));
        $teeth = ['18', '13'];
        return $teeth;
    }

    function is_neighbor_of($obj) {
        /*"""
        Check if this region group is neighbor of some other object in the mouth.
        """*/
        foreach ($this->regions as $region) {
            if ($region->is_neighbor_of($obj))
                return True;
        }
        return False;
    }

    //@property
    function to_be_replaced() {
        /*"""
        List of teeth that need to be replaced.
        """*/
        $to_be_replaced_list = [];
        foreach($this->regions as $region)
            array_push($to_be_replaced_list, $region->to_be_replaced);
        return $to_be_replaced_list;
    }
}


class Region {
    /*"""
    Represents a region (group of teeth) inside the mouth of a patient.
    Regions representation in the ISO format (please, check
        https//en.wikipedia.org/wiki/Dental_notation).

    It means that we will put numbers on the teeth as follows

            upper right - 1             upper left - 2
        18 17 16 15 14 13 12 11 | 21 22 23 24 25 26 27 28
     R --------------------------------------------------- L
        48 47 46 45 44 43 42 41 | 31 32 33 34 35 36 37 38
            lower right - 4             lower left - 3
    """*/

    // public $NeighborInterface;
    // public $RegionInterface;
    

    function __construct($start, $end, $schema=NULL) {
        
        // $this->RegionInterface = new RegionInterface();
        // $this->NeighborInterface = new NeighborInterface();
        $this->start = $start;
        $this->end = $end;
        $this->schema = $schema;
        $teeth_numbers_ordered = new TeethNumbersList();
        $this->teeth_numbers_ordered = $teeth_numbers_ordered->range($start, $end);
        print_r($this->teeth_numbers_ordered);
        $this->teeth_numbers = array_unique((array) $this->teeth_numbers_ordered);
    }

    //@classmethod
    function whole_mouth() {
        return [$this->start=18, $this->end=48];
    }

    # Upper jaw
    //@classmethod
    function upper_jaw() {
        return [$this->start=18, $this->end=28];
    }

    //@classmethod
    function upper_jaw_left_end() {
        /*"""
        Represent the end region (three last teeth) for the left side of the mouth.
        """*/
        return [18, 16];
    }

    //@classmethod
    function upper_jaw_right_end() {
        /*"""
        Represent the end region (three last teeth) for the right side of the mouth.
        """*/
        return [26, 28];
    }

    //@classmethod
    function upper_jaw_front() {
        /*"""
        Represent the front (anterior) region (6 front teeth) of the mouth.
        """*/
        return [13, 23];
    }

    # Mandible
    //@classmethod
    function mandible() {
        return [$this->start=38, $this->end=48];
    }

    //@classmethod
    function mandible_left_end() {
        /*"""
        Represent the end region (three last teeth) for the left side of the mouth.
        """*/
        return [46, 48];
    }

    //@classmethod
    function mandible_right_end() {
        /*"""
        Represent the end region (three last teeth) for the right side of the mouth.
        """*/
        return [38, 36];
    }

    //@classmethod
    function mandible_front() {
        /*"""
        Represent the front (anterior) region (6 front teeth) of the mouth.
        """*/
        return [33, 43];
    }

    function __contains__($obj) {
        /*"""
        Override "in" operator for this object to allow constructions like this

        >>> Tooth(28, 'f') in Region(18, 28)
        or
        >>> Region(17, 14) in Region(18, 28)
        or
        >>> [Tooth(23, 'f'),..] in Region(18, 28)
        or
        >>> [Region(17, 14),..] in Region(18, 28)
        """*/
        // from apps.therapies.subsidy.schema import Tooth
        require_once('schema.php');


        if ($obj instanceof Tooth) {
            if (in_array($obj.number, $this->teeth_numbers))
                return True;
            return False;
        }

        if ($obj instanceof Region) {
            if (in_array($obj->teeth_numbers, $this->teeth_numbers))
                return True;
            return False;
        }

        // if isinstance(obj, InterdentalGap)
        //     return obj.region in self

        if (is_array($obj)) {
            # If one of the items is not in the region, the whole
            # contains will become false
            foreach ($obj as $item) {
                if (! in_array($item, self))
                    return False;
            }

            return True;
        }

        throw new Exception("Invalid object used with in operator ");
    }

    function __len__() {
        return len($this->teeth_numbers);
    }

    function __eq__($obj) {
        return $this->teeth_numbers == $obj->teeth_numbers;
    }

    //@property
    function teeth() {
        /*"""
        Return a list with all teeth within the region.
        """*/
        if ($this->schema)
            throw new Exception("Only Region with schema can return teeth");

        // return [i for i in $this->schema.teeth if i.number in $this->teeth_numbers];
        return '';
    }

    function get_teeth_with_condition($condition) {
        /*"""
        Return a list with all teeth in region that have the condition informed.
        """*/
        if (! $this->schema)
            throw new Exception(
                "Only Region with schema can return teeth_with_condition"
            );

        if (is_array($condition))
            $condition = [$condition];

        $arri = [];

        foreach($this->schema->teeth as $i) {
            if (in_array($i->number, $this->teeth_numbers) and in_array($i->condition, $condition)) {
                array_push($arri, $i);
            }
        }

        return $arri;
    }

    function is_neighbor_of($obj) {
        /*"""
        Check if this region is neighbor of some other object in the mouth.
        """*/
        // from apps.therapies.subsidy.schema import Tooth
        require_once('schema.php');

        $left_tooth_number = NULL;
        $right_tooth_number = NULL;
        # For a tooth to be neighbor of some region, we need only that the left
        # or right tooth of this given tooth to be close to the region
        if ($obj instanceof Tooth) {
            $left_tooth_number = $obj->left.number ? $obj->left : NULL;
            $right_tooth_number = $obj->right.number ? $obj->right : NULL;
        }

        if ($obj instanceof Region) {
            $left_tooth_number = TeethNumbersList()->previous($obj.teeth_numbers[0]);
            $right_tooth_number = TeethNumbersList().next($obj.teeth_numbers[-1]);
        }

        # For an interdental gap to be neighbor of some region, we need only that
        # the a or b tooth of this interdental gap to be close to the region
        // if isinstance(obj, InterdentalGap) {
        //     left_tooth_number = (
        //         obj.a_tooth.left.number if obj.a_tooth.left else NULL
        //     )
        //     right_tooth_number = (
        //         obj.b_tooth.right.number if obj.b_tooth.right else NULL
        //     )
        // }

        return [$left_tooth_number, $right_tooth_number];
    }

    //@property
    function to_be_replaced() {
        /*"""
        List of teeth that need to be replaced.
        """*/
        if (! $this->schema)
            throw new Exception(
                "It is only possible to count TBRs in a region with an associated schema");
        
        $i_array = [];
        foreach( $this->schema.teeth as $i) {
            if (in_array($i, self) AND $i->to_be_replaced)
                array_push($i_array, $i);
        }
        
        return $i_array;
    }
}
