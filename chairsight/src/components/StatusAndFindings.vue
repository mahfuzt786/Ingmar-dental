<template>
  <v-container>
    <br>
    <!-- <v-row align-content="center" justify="center">
      <breadcrumbs class="breadcrumb"></breadcrumbs>
    </v-row> -->
    <br>
    <v-row align="center" justify="center">
      <!-- <v-col cols="3">
        <h3>Workflow {{isDebugOn ? "(Debug ON)" : ""}}</h3>
      </v-col> -->
      <v-col cols="10">
        <DentistTeethSchema></DentistTeethSchema>
      </v-col>
    </v-row>
    <!-- <v-form>
        <v-row class="text-center" align="center">
            <v-col
            cols="12"
            sm="6"
            md="3"
            >
                <v-text-field label="Tooth" v-model="toothValues"></v-text-field>
            </v-col>
            <v-col
            cols="12"
            sm="6"
            md="3"
            >
                <v-text-field label="Findings" v-model="findingsValues"></v-text-field>
            </v-col>
            <v-btn
            depressed
            color="primary"
            >
            Submit
            </v-btn>
        </v-row>
    </v-form> -->
  </v-container>
</template>

<script>
  // import breadcrumbs from '@/components/Breadcrumb.vue'
  import DentistTeethSchema from '@/components/DentistTeethSchema.vue'
// import Breadcrumb from './Breadcrumb.vue'
  export default {
    name: 'HelloWorld',
    components: {
      // breadcrumbs,
      DentistTeethSchema
    },
    data: () => ({
      toothValues: [],
      findingsValues: [],
      isDebugOn: true
    }),
    watch: {
        findingsValues () {
            console.log('****', typeof(this.findingsValues))
        }
    }
  }
</script>
<style scoped>
.breadcrumb {
  height: 32px !important;
  background-color: dimgrey;
  align-content: center;
  border-radius: 3px;
}
.breadcrumb >>> li, .breadcrumb >>> li a {
  color: white !important;
}
</style>
