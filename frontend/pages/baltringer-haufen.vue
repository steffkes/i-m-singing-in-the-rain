<template>
  <div>
    <pre>data: {{ data }}</pre>
  </div>
</template>

<script setup lang="ts">
const { data } = await useKql({
  query: `page("${useRoute().path}")`,
  select: {
    blocks: "page.blocks.toBlocks"
  }
})

// Set the current page data for the global page context
const page = data.value?.result
setPage(page)

</script>
