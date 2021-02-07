<template>
    <v-container>
        <span v-if="responseData">{{responseData}}</span>
        <v-row>
            <v-col cols="10">
                <h3>Teeth Schema</h3>
            </v-col>
            <!-- <v-col cols="2">
                <v-btn>Import Status</v-btn>
            </v-col> -->
            <v-col cols="2">
                <v-btn class="primary" @click="calculateValues">Calculate Cases</v-btn>
            </v-col>
        </v-row>
        <br/>
        <v-card elevation="2" min-width="980">
            <!-- Top Case -->
            <v-row align="center">
                <v-col class="pl-10" cols="2">Cases</v-col>
                <v-col cols="10">
                    
                </v-col>
            </v-row>
            <!-- Top Findings -->
            <v-row align="center" justify="center">
                <v-col class="pl-10" cols="2">Findings</v-col>
                <v-col cols="10">
                    <v-btn-toggle>
                        <template v-for="(label, idx) in teethSchema.top.labels">
                            <v-btn :key="idx" @click="onClickSetStatusAndFindings(teethSchema.top, idx)">
                                <!-- <v-badge v-show="label!=null && label.length > 0">
                                </v-badge> -->
                                {{label}}
                            </v-btn>
                        </template>
                    </v-btn-toggle>
                </v-col>
            </v-row>
            <!-- Top teeth -->
            <v-row align="center">
                <v-col cols="2"></v-col>
                <v-col cols="10">
                    <v-btn-toggle>
                        <template v-for="(label, idx) in teethSchema.top.teeth">
                            <v-btn :key="idx" disabled>
                                <!-- <v-badge v-show="label!=null && label.length > 0">
                                </v-badge> -->
                                {{label}}
                            </v-btn>
                        </template>
                    </v-btn-toggle>
                </v-col>
            </v-row>
            <!-- Bottom teeth -->
            <v-row align="center">
                <v-col cols="2"></v-col>
                <v-col cols="10">
                    <v-btn-toggle>
                        <template v-for="(label, idx) in teethSchema.bottom.teeth">
                            <v-btn :key="idx" disabled>
                                <!-- <v-badge v-show="label!=null && label.length > 0">
                                </v-badge> -->
                                {{label}}
                            </v-btn>
                        </template>
                    </v-btn-toggle>
                </v-col>
            </v-row>
            <!-- Bottom Findings -->
            <v-row align="center">
                <v-col class="pl-10" cols="2">Findings</v-col>
                <v-col cols="10">
                    <v-btn-toggle>
                        <template v-for="(label, idx) in teethSchema.bottom.labels">
                            <v-btn :key="idx" @click="onClickSetStatusAndFindings(teethSchema.bottom, idx)">
                                <!-- <v-badge v-show="label!=null && label.length > 0">
                                </v-badge> -->
                                {{label}}
                            </v-btn>
                        </template>
                    </v-btn-toggle>
                </v-col>
            </v-row>
            <!-- Bottom Case -->
            <v-row align="center">
                <v-col class="pl-10" cols="2">Cases</v-col>
                <v-col cols="10">
                    
                </v-col>
            </v-row>
        </v-card>
        
        <!--Modal to define tooth status and finding -->
        <v-dialog v-model="toothStatusFindingsDialog" width="750">
            <v-card>
                <v-form ref="statusAndFindings">
                    <br/>
                    <v-row>
                        <v-col>
                            <h6>Status</h6>
                            <v-radio-group v-model="selectedStatus">
                                <v-radio
                                v-for="statusOption in statusOptions"
                                :value="statusOption.value"
                                :label="`${statusOption.value} ${statusOption.text}`"
                                @mouseover="onHoverStatusOption(statusOption.value)"
                                @click="onClickStatusOrFinding(true)" :key="statusOption.value">
                                    <!-- <v-badge>{{statusOption.value}}</v-badge> {{statusOption.text}} -->
                                </v-radio>
                            </v-radio-group>
                        </v-col>
                        <v-col>
                            <h6>Findings</h6>
                            <v-radio-group v-model="selectedFinding">
                                <v-radio
                                v-for="findingOption in findingsOptions[selectedStatus]"
                                :value="findingOption.value"
                                :label="`${findingOption.value} ${findingOption.text}`"
                                @mouseover="onHoverFindingOption(findingOption.value)"
                                @click="onClickStatusOrFinding(false)" :key="findingOption.value">
                                    <!-- <v-badge>{{findingOption.value}}</v-badge> {{findingOption.text}} -->
                                </v-radio>
                            </v-radio-group>
                        </v-col>
                    </v-row>
                </v-form>
                <v-card-actions>
                    <v-spacer></v-spacer>

                    <v-btn
                        color="green darken-1"
                        text
                        @click="toothStatusFindingsDialog = false"
                    >
                        Cancel
                    </v-btn>

                    <v-btn
                        color="green darken-1"
                        text
                        @click="onClickSaveStatusAndFindings"
                    >
                        Ok
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>
<script>
import {
  STATUS_OPTIONS,
  FINDINGS_OPTIONS,
  TOP_TEETH,
  BOTTOM_TEETH
} from "@/consts/teeth_schema";
// import { mapGetters, mapActions, mapMutations } from 'vuex'
import { mapActions } from 'vuex'
export default {
    data: () => ({
        // Options for findings and status models
        statusOptions: STATUS_OPTIONS,
        findingsOptions: FINDINGS_OPTIONS,

        // Control the setting of status and findings on teeth
        selectedTooth: { tooth: null },
        selectedStatus: null,
        selectedFinding: null,

        // Stores the imported status string
        importedStatusRaw: "",

        // Represents the teeth schem with teeth, findings and cases
        teethSchema: {
            top: {
                cases: [{ isSpace: true }],
                status: [
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    ""
                ],
                findings: [
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    ""
                ],
                labels: [
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    ""
                ],
                tps: ["", "", "", "", "", "", "", "", "", "", "", "", "", "", "", ""],
                rvs: ["", "", "", "", "", "", "", "", "", "", "", "", "", "", "", ""],
                ids: ["", "", "", "", "", "", "", "", "", "", "", "", "", "", "", ""],
                teeth: TOP_TEETH
            },
            bottom: {
                cases: [{ isSpace: true }],
                status: [
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    ""
                ],
                findings: [
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    ""
                ],
                labels: [
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    ""
                ],
                tps: ["", "", "", "", "", "", "", "", "", "", "", "", "", "", "", ""],
                rvs: ["", "", "", "", "", "", "", "", "", "", "", "", "", "", "", ""],
                ids: ["", "", "", "", "", "", "", "", "", "", "", "", "", "", "", ""],
                teeth: BOTTOM_TEETH
            }
        },

        // Cases List
        casesWereCalculated: false,
        subsidiesList: [],
        toothStatusFindingsDialog: false,
        inputArray: [{ 18: '' }, { 17: '' }, { 16: '' }, { 15: '' }, { 14: '' }, { 13: '' }, { 12: '' }, { 11: '' }, { 21: '' }, { 22: '' }, { 23: '' }, { 24: '' }, { 25: '' }, { 26: '' },
                    { 27: '' }, { 28: '' }, { 38: '' }, { 37: '' }, { 36: '' }, { 35: '' }, { 34: '' }, { 33: '' }, { 32: '' }, { 31: '' }, { 41: '' }, { 42: '' }, { 43: '' }, { 44: '' },
                    { 45: '' }, { 46: '' }, { 47: '' }, { 48: '' }],
        // inputArray: [{ "teeth": 18, "status" : "" }, {  "teeth": 17, "status" : '' }, {  "teeth": 16, "status" : '' }, {  "teeth": 15, "status" : '' }, {  "teeth": 14, "status" : '' }, {  "teeth": 13, "status" : '' }, {  "teeth": 12, "status" : '' }, {  "teeth": 11, "status" : '' }, 
        //         { "teeth": 21, "status" : '' }, {  "teeth": 22, "status" : '' }, {  "teeth": 23, "status" : '' }, {  "teeth": 24, "status" : '' }, {  "teeth": 25, "status" : '' }, {  "teeth": 26, "status" : '' }, {  "teeth": 27, "status" : '' }, {  "teeth": 28, "status" : '' }, 
        //         { "teeth": 38, "status" : '' }, {  "teeth": 37, "status" : '' }, {  "teeth": 36, "status" : '' }, {  "teeth": 35, "status" : '' }, {  "teeth": 34, "status" : '' }, {  "teeth": 33, "status" : '' }, {  "teeth": 32, "status" : '' }, {  "teeth": 31, "status" : '' }, 
        //         { "teeth": 41, "status" : '' }, {  "teeth": 42, "status" : '' }, {  "teeth": 43, "status" : '' }, {  "teeth": 44, "status" : '' }, {  "teeth": 45, "status" : '' }, {  "teeth": 46, "status" : '' }, {  "teeth": 47, "status" : '' }, {  "teeth": 48, "status" : '' }],
        bottomValues: [],
        topValues: [],
        responseData: ''
    }),
    methods: {
        ...mapActions([
            'calculateValuesApi'
        ]),
        /**************************************************************************/
        /* HOVER AND CLICK HANDLERS
        /**************************************************************************/
        /*
        * Shows findings when user is hovering a status
        */
        onHoverStatusOption(statusOption) {
            if (statusOption != null && statusOption != undefined) {
                this.selectedStatus = statusOption;
            }
        },
        /*
        * Closes status and findings modal
        */
        onClickStatusOrFinding(clearFinding) {
            this.selectedFinding = clearFinding == true ? null : this.selectedFinding;
            this.onClickSaveStatusAndFindings()
        },

        /*
        * Selects findings when user is hovering a finding
        */
        onHoverFindingOption(findingOption) {
            if (findingOption != null && findingOption != undefined) {
                this.selectedFinding = findingOption;
            }
        },
        /*
        * Sets which tooth is being edited
        */
        onClickSetStatusAndFindings(quadrant, toothIdx) {
            // Sets Select Tooth, status and findings
            this.selectedTooth = {
                tooth: quadrant.teeth[toothIdx],
                quadrant: quadrant,
                idx: toothIdx
            };

            this.selectedStatus = quadrant.status[toothIdx];
            this.selectedFinding = quadrant.findings[toothIdx];
            this.toothStatusFindingsDialog = true
        },
        /*
        * Saves the tooth status and findings on tooth schema
        */
        onClickSaveStatusAndFindings() {
            this.$nextTick(function() {
                this.selectedTooth.quadrant.status.splice(
                this.selectedTooth.idx,
                1,
                this.selectedStatus
                );
                // Updates status and findings of tooth
                this.selectedTooth.quadrant.findings.splice(
                this.selectedTooth.idx,
                1,
                this.selectedFinding
                );

                if(this.selectedFinding) {
                    this.inputArray.forEach((element) => {
                        // if (element['teeth'] == this.selectedTooth.tooth) {
                        //      element['status'] = this.selectedFinding
                        // }
                        for(let key in element) {
                            if(key == this.selectedTooth.tooth) {
                                element[key] = this.selectedFinding
                            }
                        }
                    })
                } else {
                    this.inputArray.forEach((element) => {
                        // if (element['teeth'] == this.selectedTooth.tooth) {
                        //      element['status'] = this.selectedStatus
                        // }
                        for(let key in element) {
                            if(key == this.selectedTooth.tooth) {
                                element[key] = this.selectedStatus
                            }
                        }
                    })
                }

                // Reset selected status and finding
                this.selectedStatus = null;
                this.selectedFinding = null;
            });

            // Refresh UI
            this.$nextTick(this.refreshTeethSchema);
        },

        /**************************************************************************/
        /* INTERNAL METHODS
        /**************************************************************************/
        /*
        * Update teethSchema
        */
        refreshTeethSchema() {
            // Update schema status
            let label;
            let topFindings = this.teethSchema.top.findings;
            let bottomFindings = this.teethSchema.bottom.findings;
            let topStatus = this.teethSchema.top.status;
            let bottomStatus = this.teethSchema.bottom.status;
            for (let idx = 0; idx < 16; idx++) {
                // Top
                label =
                topFindings[idx] != null && topFindings[idx].length > 0
                    ? topFindings[idx]
                    : topStatus[idx];
                this.teethSchema.top.labels.splice(idx, 1, label);
                // Bottom
                label =
                bottomFindings[idx] != null && bottomFindings[idx].length > 0
                    ? bottomFindings[idx]
                    : bottomStatus[idx];
                this.teethSchema.bottom.labels.splice(idx, 1, label);
            }
            this.toothStatusFindingsDialog = false
        },

        calculateValues() {
            console.log(this.inputArray)
            this.calculateValuesApi(this.inputArray)
            .then((response) => {
                console.log('response', response)
                this.responseData = response.data
            })
        },

        checkValueExist(array, idx) {
            let found = null
            array.forEach((element, index) => {
                if(idx == element[0]) {
                    found = index
                }
            });
            return found
        }
    }
}
</script>
<style scoped>
.theme--light.v-btn.v-btn--disabled.v-btn--has-bg {
    background-color: transparent !important;
}

.theme--light.v-btn-toggle:not(.v-btn-toggle--group) .v-btn.v-btn--disabled {
    border-color: transparent !important;
}

.theme--light.v-btn.v-btn--disabled {
    color: rgba(0, 0, 0, 0.87) !important;
}

/* .theme--light.v-btn.v-btn--has-bg {
    background-color: transparent !important;
} */
</style>
