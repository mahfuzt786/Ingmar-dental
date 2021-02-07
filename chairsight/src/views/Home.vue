<template>
  <div class="home">
    <Status/>
  </div>
</template>

<script>
// @ is an alias to /src
import Status from '@/components/StatusAndFindings.vue'

export default {
  name: 'Home',
  components: {
    Status
  }
}
</script>
