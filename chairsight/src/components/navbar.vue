<template>
  <nav>
    <v-app-bar flat app dark color="accent-4" clipped-left elevation="2">
      <div class="grey--text text-h5 welcomeMessage"><span class="font-weight-bold">Chairsight</span></div>
      <v-spacer></v-spacer>
      <v-divider vertical class="pr-4"></v-divider>
      <v-menu offset-y>
        <template v-slot:activator="{ on, attrs }">
          <v-btn text nuxt v-bind="attrs" v-on="on">
            <span text-capitalize>More</span>
            <v-icon right>keyboard_arrow_down</v-icon>
          </v-btn>
        </template>
        <v-list>
          <v-list-item v-for="link in list_item" :key="link.title" router :to="link.route">
            <v-list-item-title v-text="link.title"></v-list-item-title>
          </v-list-item>
        </v-list>
      </v-menu>
    </v-app-bar>
  </nav>
</template>

<script>
export default {
  data() {
    return {
      logo_url: '',
      drawer: false,
      setting_active: false,
      account_active: false,
      active: false,
      list_item: [
        {
          title: 'Settings',
          route: '/'
        },
        {
          title: 'Logout',
          route: '/'
        }
      ]
    }
  },

  mounted() {

  },

  computed: {
    isLoggedIn: function() {
      return this.$store.getters.isLoggedIn
    },
    isPassword: function() {
      return this.$store.getters.isPassword
    },
  },

  watch: {

  },

  methods: {

  },

}
</script>

<style lang="css" scoped>
 .welcomeMessage {
   font-size: 16px;
 }
 .navDrawerItem.v-list-item--active {
   border-right: 4px solid #1976d2;
 }
</style>
