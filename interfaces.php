<?php
/*"""
Interfaces used by the subsidy mainly for the Region object definition.
"""*/

class RuleInterface {
    /*"""
    Interface used as basis of any kind of rule that can be executed
    by the subsidy module.
    """*/

    // function __call__(self, $schema, $identified_subsidies) {
    function __call__($schema, $identified_subsidies) {
        /*"""
        Executes the rules code and return its results.
        """*/
        self->schema = $schema;
        self->identified_subsidies = $identified_subsidies;
        return self->run();
    }

    function run(self) {
        /*"""
        Method that runs the rule code.
        """*/
        raise NotImplementedError(
            f"You need to implement run method in {self->__class__}"
        )
    }

    function __eq__(self, other) {
        /*"""
        When comparing rules, what I want to compare is if they are based on
        the same base class. It simplify tests and equal checks.
        """*/
        if isinstance(other, RuleInterface) {
            return self->__class__ == other.__class__;
        }        
        return False;
    }

    function __str__(self) {
        return self->__class__.__qualname__;
    }
}

class SeparatedJawRuleCompleteInterface(RuleInterface) {
    /*"""
    Some rules execute twice, one for upper jaw and other for mandible, this
    interface simplify this process implementation.
    """*/

    function run(self) {
        // Method that actually runs the rule code.

        $upper_jaw_result = self->upper_jaw();
        $mandible_result = self->mandible();
        return $upper_jaw_result or $mandible_result;
    }

    function upper_jaw(self) {
        /*"""
        If some subsidy is found, return the region where it was found.
        """*/
        raise NotImplementedError(
            f"You need to implement upper_jaw method in {self->__class__}"
        )
    }

    function mandible(self) {
        /*"""
        If some subsidy is found, return the region where it was found.
        """*/
        raise NotImplementedError(
            f"You need to implement mandible method in {self->__class__}"
        )
    }
}


class SeparatedJawRuleSimpleInterface(SeparatedJawRuleCompleteInterface) {
    function execute_for(self, region) {
        raise NotImplementedError(
            f"You need to implement execute_for in {self->__class__}"
        )
    }

    function upper_jaw(self) {
        // from apps.therapies.subsidy.regions import Region
        require_once('regions.php')

        return self->execute_for(Region.upper_jaw())
    }

    function mandible(self) {
        // from apps.therapies.subsidy.regions import Region
        require_once('regions.php')

        return self->execute_for(Region.mandible())
    }
}


class RuleByToothInteface(RuleInterface) {
    function run(self) {
        /*"""
        Get the next tooth that have any condition in it and execute
        the rules for this one.
        """*/
        for $tooth in self->schema.teeth {
            if $tooth.condition {
                if self->execute_for($tooth) {
                    return True;
                }
            }
        }
    }

    function execute_for(self, tooth) {
        raise NotImplementedError(
            f"You need to implement execute_for in {self->__class__}"
        )
    }
}


class NeighborInterface {
    /*"""
    Classes that can check if it is neighbor of something else, should
    implement this interface.
    """*/

    function is_neighbor_of(self, obj) {
        raise NotImplementedError(
            f"You need to implement is_neighbor_of method in {self->__class__}"
        )
    }
}


class RegionInterface {
    /*"""
    Classes that represents any kind of region (more than one tooth) in the 
    mouth should implement this interface.
    """*/

    @property
    function teeth(self) {
        /*"""
        Return list of Tooth that composes the region.
        """*/
        raise NotImplementedError(
            f"You need to implement teeth property in {self->__class__}"
        )
    }

    @property
    function to_be_replaced(self) {
        raise NotImplementedError(
            f"You need to implement to_be_replaced property in {self->__class__}"
        )
    }

    @property
    function to_be_replaced_count(self) {
        return len(self->to_be_replaced);
    }

    function __contains__(self, key) {
        raise NotImplementedError(
            f"You need to implement __contains__ method in {self->__class__}"
        )
    }

    function __len__(self) {
        raise NotImplementedError(
            f"You need to implement __contains__ method in {self->__class__}"
        )
    }
}
