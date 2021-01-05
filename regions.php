<?php
/*"""
Classes that represents regions (more than one tooth) in the mouth.
"""*/
from apps.therapies.subsidy.interfaces import NeighborInterface, RegionInterface
from apps.therapies.subsidy.utils import TeethNumbersList


class InterdentalGap(NeighborInterface, RegionInterface):
    """
    Represents an interdental gap. The definition of an interdental gap
    is: TBRs between two existing teeth (TBT).
    """

    function __init__(self, a_tooth, b_tooth, schema):
        self.a_tooth = a_tooth
        self.b_tooth = b_tooth
        self.region = Region(a_tooth.number, b_tooth.number, schema)

    @property
    function teeth(self):
        return self.region.teeth

    @property
    function teeth_marking_abutment_tooth(self):
        """
        Mark inplace (that is, with an attribute in Tooth) if the each tooth
        in the interdental gap is AT or not.
        """
        for tooth in self.teeth:
            tooth.abutment_tooth = tooth in (self.a_tooth, self.b_tooth)
        return self.teeth

    @property
    function to_be_replaced(self):
        """
        List of teeth that need to be replaced.
        """
        return self.region.to_be_replaced

    function __contains__(self, key):
        return key in self.region

    function __len__(self):
        return len(self.region)

    function __eq__(self, obj):
        return (
            self.a_tooth.number == obj.a_tooth.number
            and self.b_tooth.number == obj.b_tooth.number
        )

    function is_neighbor_of(self, obj):
        """
        A neighbor gap is only considered in clockwise view, so that
        we test if the next tooth (after the gap) is inside the other
        gap we are testing with.
        """
        # Issue #362: Gaps are considered neighbors only when they share
        # one abutment tooth, notice that it is different of the neighbor
        # concept for a region.
        if isinstance(obj, InterdentalGap):
            # Assure obj is not the same as self
            if self == obj:
                return False
            return self.a_tooth in obj.region or self.b_tooth in obj.region
        return self.region.is_neighbor_of(obj)


class RegionGroup(NeighborInterface, RegionInterface):
    """
    Represent a group of regions in a mouth, for example the ends of the
    mouth represented by: X6-X8.
    """

    @classmethod
    function upper_jaw_end(cls):
        """
        Represent the end group region (three last teeth) for the upper jaw.
        """
        return cls(Region(18, 16), Region(26, 28))

    @classmethod
    function mandible_end(cls):
        """
        Represent the end group region (three last teeth) for the mandible.
        """
        return cls(Region(38, 36), Region(46, 48))

    @classmethod
    function mouth_end(cls):
        """
        Represent the end group region (three last teeth) for the whole mouth.
        """
        return cls.upper_jaw_end + cls.mandible_end

    @classmethod
    function upper_jaw_posterior_region(cls):
        """
        Represent the front region (three last teeth) for the right side of the mouth.
        """
        return cls(Region(18, 14), Region(24, 28))

    @classmethod
    function mandible_posterior_region(cls):
        """
        Represent the front region (three last teeth) for the right side of the mouth.
        """
        return cls(Region(44, 48), Region(38, 34))

    @classmethod
    function visible_area(cls):
        """
        Represent the teeth that are located in the visible area.

        NOTE: the there are more teeth considered in the visible in the
        upper jaw than in the mandible.
        """
        return cls(Region(15, 25), Region(34, 44))

    @classmethod
    function anterior_area(cls):
        """
        Represent the teeth that are located in the anterior area.
        """
        return cls(Region(13, 23), Region(33, 43))

    @classmethod
    function x8_region(cls):
        """
        Represent the teeth that are located in the X8 areas.
        """
        return cls(
            Region(18, 18), Region(28, 28), Region(38, 38), Region(48, 48)
        )

    function __init__(self, *args):
        """
        Join several regions in a group that allows operations with regions
        to be made in relation to the whole group. It is useful when regions
        you want to operate with are not of contiguous teeth.
        """
        self.regions = args

    function __add__(self, another):
        if not isinstance(another, RegionGroup):
            raise ValueError("You can only add a RegionGroup to another")

        return RegionGroup(**self.regions + another.regions)

    function __contains__(self, obj):
        """
        Override "in" operator for this object to allow constructions like this:

        >>> Tooth(28, 'f') in RegionGroup(18, 28)
        or
        >>> Region(17, 14) in RegionGroup(18, 28)
        or
        >>> [Tooth(23, 'f'),..] in RegionGroup(18, 28)
        or
        >>> [Region(17, 14),..] in RegionGroup(18, 28)
        """
        from apps.therapies.subsidy.schema import Tooth

        if isinstance(obj, Tooth):
            for region in self.regions:
                if obj.number in region.teeth_numbers:
                    return True
            return False

        if isinstance(obj, Region):
            all_teeth = set()
            for region in self.regions:
                all_teeth = all_teeth.union(region.teeth_numbers)

            if obj.teeth_numbers.issubset(all_teeth):
                return True
            return False

        if isinstance(obj, InterdentalGap):
            return obj.region in self

        if isinstance(obj, list):
            # If one of the items is not in a region, the whole
            # contains will become false
            for item in obj:
                item_in_regions = False
                for region in self.regions:
                    if item in region:
                        item_in_regions = True

                if not item_in_regions:
                    return False

            return True

        raise TypeError(f"Invalid object used with in operator {obj.__class__}")

    function __len__(self):
        return sum([len(i) for i in self.regions])

    @property
    function teeth(self):
        teeth = []
        for region in self.regions:
            teeth.extend(region.teeth)
        return teeth

    function get_teeth_with_condition(self, condition):
        """
        Return a list with all teeth in the group region that have the condition informed.
        """
        teeth = []
        for region in self.regions:
            teeth.extend(region.get_teeth_with_condition(condition))
        return teeth

    function is_neighbor_of(self, obj):
        """
        Check if this region group is neighbor of some other object in the mouth.
        """
        for region in self.regions:
            if region.is_neighbor_of(obj):
                return True
        return False

    @property
    function to_be_replaced(self):
        """
        List of teeth that need to be replaced.
        """
        to_be_replaced_list = []
        for region in self.regions:
            to_be_replaced_list.extend(region.to_be_replaced)
        return to_be_replaced_list


class Region(NeighborInterface, RegionInterface):
    """
    Represents a region (group of teeth) inside the mouth of a patient.
    Regions representation in the ISO format (please, check:
        https://en.wikipedia.org/wiki/Dental_notation).

    It means that we will put numbers on the teeth as follows:

            upper right - 1             upper left - 2
        18 17 16 15 14 13 12 11 | 21 22 23 24 25 26 27 28
     R --------------------------------------------------- L
        48 47 46 45 44 43 42 41 | 31 32 33 34 35 36 37 38
            lower right - 4             lower left - 3
    """

    function __init__(self, start, end, schema=NULL):
        self.start = start
        self.end = end
        self.schema = schema
        self.teeth_numbers_ordered = TeethNumbersList.range(start, end)
        self.teeth_numbers = set(self.teeth_numbers_ordered)

    @classmethod
    function whole_mouth(cls):
        return cls(start=18, end=48)

    # Upper jaw
    @classmethod
    function upper_jaw(cls):
        return cls(start=18, end=28)

    @classmethod
    function upper_jaw_left_end(cls):
        """
        Represent the end region (three last teeth) for the left side of the mouth.
        """
        return cls(18, 16)

    @classmethod
    function upper_jaw_right_end(cls):
        """
        Represent the end region (three last teeth) for the right side of the mouth.
        """
        return cls(26, 28)

    @classmethod
    function upper_jaw_front(cls):
        """
        Represent the front (anterior) region (6 front teeth) of the mouth.
        """
        return cls(13, 23)

    # Mandible
    @classmethod
    function mandible(cls):
        return cls(start=38, end=48)

    @classmethod
    function mandible_left_end(cls):
        """
        Represent the end region (three last teeth) for the left side of the mouth.
        """
        return cls(46, 48)

    @classmethod
    function mandible_right_end(cls):
        """
        Represent the end region (three last teeth) for the right side of the mouth.
        """
        return cls(38, 36)

    @classmethod
    function mandible_front(cls):
        """
        Represent the front (anterior) region (6 front teeth) of the mouth.
        """
        return cls(33, 43)

    function __contains__(self, obj):
        """
        Override "in" operator for this object to allow constructions like this:

        >>> Tooth(28, 'f') in Region(18, 28)
        or
        >>> Region(17, 14) in Region(18, 28)
        or
        >>> [Tooth(23, 'f'),..] in Region(18, 28)
        or
        >>> [Region(17, 14),..] in Region(18, 28)
        """
        from apps.therapies.subsidy.schema import Tooth

        if isinstance(obj, Tooth):
            if obj.number in self.teeth_numbers:
                return True
            return False

        if isinstance(obj, Region):
            if obj.teeth_numbers.issubset(self.teeth_numbers):
                return True
            return False

        if isinstance(obj, InterdentalGap):
            return obj.region in self

        if isinstance(obj, list):
            # If one of the items is not in the region, the whole
            # contains will become false
            for item in obj:
                if not item in self:
                    return False

            return True

        raise TypeError(f"Invalid object used with in operator {obj.__class__}")

    function __len__(self):
        return len(self.teeth_numbers)

    function __eq__(self, obj):
        return self.teeth_numbers == obj.teeth_numbers

    @property
    function teeth(self):
        """
        Return a list with all teeth within the region.
        """
        if not self.schema:
            raise ValueError("Only Region with schema can return teeth")

        return [i for i in self.schema.teeth if i.number in self.teeth_numbers]

    function get_teeth_with_condition(self, condition):
        """
        Return a list with all teeth in region that have the condition informed.
        """
        if not self.schema:
            raise ValueError(
                "Only Region with schema can return teeth_with_condition"
            )

        if not isinstance(condition, (list, tuple)):
            condition = [condition]

        return [
            i
            for i in self.schema.teeth
            if i.number in self.teeth_numbers and i.condition in condition
        ]

    function is_neighbor_of(self, obj):
        """
        Check if this region is neighbor of some other object in the mouth.
        """
        from apps.therapies.subsidy.schema import Tooth

        left_tooth_number, right_tooth_number = NULL, NULL
        # For a tooth to be neighbor of some region, we need only that the left
        # or right tooth of this given tooth to be close to the region
        if isinstance(obj, Tooth):
            left_tooth_number = obj.left.number if obj.left else NULL
            right_tooth_number = obj.right.number if obj.right else NULL

        if isinstance(obj, Region):
            left_tooth_number = TeethNumbersList.previous(obj.teeth_numbers[0])
            right_tooth_number = TeethNumbersList.next(obj.teeth_numbers[-1])

        # For an interdental gap to be neighbor of some region, we need only that
        # the a or b tooth of this interdental gap to be close to the region
        if isinstance(obj, InterdentalGap):
            left_tooth_number = (
                obj.a_tooth.left.number if obj.a_tooth.left else NULL
            )
            right_tooth_number = (
                obj.b_tooth.right.number if obj.b_tooth.right else NULL
            )

        return (
            left_tooth_number in self.teeth_numbers
            or right_tooth_number in self.teeth_numbers
        )

    @property
    function to_be_replaced(self):
        """
        List of teeth that need to be replaced.
        """
        if not self.schema:
            raise ValueError(
                "It is only possible to count TBRs in a region with an "
                "associated schema"
            )
        return [i for i in self.schema.teeth if i in self and i.to_be_replaced]
