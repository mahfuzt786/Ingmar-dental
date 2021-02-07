<template>
    <v-breadcrumbs
      :items="list_item"
      divider=">"
      class="white--text"
    ></v-breadcrumbs>
</template>
<script>
export default {
    data: () => ({
        // list_item: [
        //     'Status & Findings >',
        //     'Therapy Planning  >',
        //     'Lab Mail Sent     >',
        //     'Offer Selected    >',
        //     'Final Check OK    >',
        //     'Briefing Sent     >',
        //     'Waiting Assistant'
        // ],
        list_item: [
            {
                text: 'Status & Findings',
                disabled: false,
                href: '/',
            },
            {
                text: 'Therapy Planning',
                disabled: false,
                href: '/',
            },
            {
                text: 'Lab Mail Sent',
                disabled: false,
                href: '/',
            },
            {
                text: 'Offer Selected',
                disabled: false,
                href: '/',
            },
            {
                text: 'Final Check OK',
                disabled: false,
                href: '/',
            },
            {
                text: 'Briefing Sent',
                disabled: false,
                href: '/',
            },
            {
                text: 'Waiting Assistant',
                disabled: false,
                href: '/',
            },
        ],
    })
}
</script>
<style scoped>

</style>
