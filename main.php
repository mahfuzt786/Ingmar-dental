<?php
    /*
    * The main execution for the subsidy package is hosted here.
    * You can just call `generate_all_subsidies` informing a schema
    * generated by the `utils.patient_therapy_to_schema` or `utils.tuple_to_schema`
    * and it will return you the subsidies.
    */
    require_once('subsidies.php');
    require_once('rules/by_region.php');
    require_once('rules/by_tooth.php');

    from copy import deepcopy

    function generate_all_subsidies ($schema)
    {
        /*
        * Generate all subsidy for the given schema.
        */
        // As we need to remove the findings in our evaluation loop,
        // it is better to keep the original schema with no changes
        $schema_by_region = deepcopy($schema);
        $schema_by_tooth = deepcopy($schema);
        $identified_subsidies = SubsidiesList();

        // Get all teeth involved in a rule to remove it from the schema, once
        // we will evaluate the schema recursively without the findings related
        // to the identified rules
        while evaluate_by_region($schema_by_region, $identified_subsidies) {
            for $subsidy in $identified_subsidies {
                // Optional subsidies should not be considered to remove
                // the teeth, it will only be kept as a mark but we could
                // generate other subsidies in the same teeth related to it
                if not $subsidy.get("optional", False) {
                    $schema_by_region.remove_findings($subsidy["region"])
                }
            }
        }

        $subsidies_identified_by_region = len($identified_subsidies)

        while evaluate_by_tooth($schema_by_tooth, $identified_subsidies) {
            // We only remove regions of the subsidies identified by_tooth
            for $subsidy in $identified_subsidies[$subsidies_identified_by_region]
                // // Optional subsidies should not be considered to remove
                // // the teeth, it will only be kept as a mark but we could
                // // generate other subsidies in the same teeth related to it
                if not $subsidy.get("optional", False)
                    $schema_by_tooth.remove_findings($subsidy["region"])
        }

        return $identified_subsidies
    }