<template>
  <v-app>
    <v-main>
      <Navbar></Navbar>
      <router-view></router-view>
    </v-main>
  </v-app>
</template>

<script>
import Navbar from './components/navbar.vue';

export default {
  name: 'App',

  components: {
    Navbar,
  },

  data: () => ({
    //
  }),
};
</script>
