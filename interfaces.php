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
        $this->schema = $schema;
        $this->identified_subsidies = $identified_subsidies;
        return $this->run();
    }

    function run() {
        /*"""
        Method that runs the rule code.
        """*/
        throw new Exception(
            "You need to implement run method"
        );
    }

    function __eq__($other) {
        /*"""
        When comparing rules, what I want to compare is if they are based on
        the same base class. It simplify tests and equal checks.
        """*/
        if ($other instanceof RuleInterface) {
            return $this->__class__ == $other->__class__;
        }        
        return False;
    }

    function __str__() {
        return $this->__class__->__qualname__;
    }
}

class SeparatedJawRuleCompleteInterface {
    /*"""
    Some rules execute twice, one for upper jaw and other for mandible, this
    interface simplify this process implementation.
    """*/
    public $RuleInterface;

    function __construct__($RuleInterface) {
        $this->RuleInterface = $RuleInterface;
    }

    function run() {
        // Method that actually runs the rule code.

        $upper_jaw_result = $this->upper_jaw();
        $mandible_result = $this->mandible();
        return $upper_jaw_result or $mandible_result;
    }

    function upper_jaw() {
        /*"""
        If some subsidy is found, return the region where it was found.
        """*/
        throw new Exception("You need to implement upper_jaw method");
    }

    function mandible() {
        /*"""
        If some subsidy is found, return the region where it was found.
        """*/
        throw new Exception("You need to implement mandible method");
    }
}


class SeparatedJawRuleSimpleInterface {
    public $SeparatedJawRuleCompleteInterface;

    function __construct__($SeparatedJawRuleCompleteInterface) {
        $this->SeparatedJawRuleCompleteInterface = $SeparatedJawRuleCompleteInterface;
    }

    function execute_for($region) {
        throw new Exception(
            "You need to implement execute_for in");
    }

    function upper_jaw() {
        // from apps.therapies.subsidy.regions import Region
        require_once('regions.php');

        $Region = new Region();
        $Region = $Region->upper_jaw();

        return $this->execute_for($Region);
    }

    function mandible() {
        // from apps.therapies.subsidy.regions import Region
        require_once('regions.php');

        $Region = new Region();
        $Region = $Region->mandible();

        return $this->execute_for($Region);
    }
}


class RuleByToothInteface {
    
    public $RuleInterface;

    function __construct__($RuleInterface) {
        $this->RuleInterface = $RuleInterface;
    }

    function run() {
        /*"""
        Get the next tooth that have any condition in it and execute
        the rules for this one.
        """*/
        foreach ($this->schema->teeth as $tooth) {
            if ($tooth->condition) {
                if ($this->execute_for($tooth)) {
                    return True;
                }
            }
        }
    }

    function execute_for($tooth) {
        throw new Exception("You need to implement execute_for");
    }
}


class NeighborInterface {
    /*"""
    Classes that can check if it is neighbor of something else, should
    implement this interface.
    """*/

    function is_neighbor_of($obj) {
        throw new Exception(
            "You need to implement is_neighbor_of method");
    }
}


class RegionInterface {
    /*"""
    Classes that represents any kind of region (more than one tooth) in the 
    mouth should implement this interface.
    """*/

    // @property
    function teeth() {
        /*"""
        Return list of Tooth that composes the region.
        """*/
        throw new Exception(
            "You need to implement teeth property in ");
    }

    // @property
    function to_be_replaced() {
        throw new Exception(
            "You need to implement to_be_replaced property in ");
    }

    // @property
    function to_be_replaced_count() {
        return len($this->to_be_replaced);
    }

    function __contains__($key) {
        throw new Exception(
            "You need to implement __contains__ method in ");
    }

    function __len__() {
        throw new Exception(
            "You need to implement __contains__ method in ");
    }
}
