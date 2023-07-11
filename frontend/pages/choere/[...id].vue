<template>
  <section class="container my-20 grid grid-cols-1 lg:grid-cols-2 gap-16">
    <div class="col-span-1">
      <h2>{{ data.result.title }}</h2>
      <div
        v-html="data.result.description"
        class="prose"
        :class="{ 'max-w-lg mb-8': data.result.cover.url }"
      ></div>
    </div>
    <div>
      <img
        v-if="data.result.cover.url"
        class="mb-12"
        :src="data.result.cover.url"
      />
      <div class="grid lg:grid-cols-2 gap-6">
        @TODO Sidebar
        <!--
        <div v-if="blok.time || blok.location" class="bg-primary/10 p-4">
          <h3>Proben</h3>
          <p v-if="blok.time">
            {{ blok.time }}
          </p>
          <p v-if="blok.location">
            {{ blok.location }}
          </p>
        </div>
        <div v-if="blok.contact" class="bg-primary/10 p-4">
          <h3>Kontakt</h3>
          <p>{{ blok.contact }}</p>
          <p v-if="blok.phone">{{ blok.phone }}</p>
          <p v-if="blok.mail">{{ blok.mail }}</p>
        </div>
        -->
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
const { data } = await useKql({
  query: `page("${useRoute().path}")`,
  select: {
    id: true,
    title: true,
    intendedTemplate: true,
    description: true,
    cover: {
      query: 'page.content.cover.toFile',
      select: ['url']
    }
  },
}
, {
headers: {
host: 'localhost:4488'
}
})

// Set the current page data for the global page context
const page = data.value?.result
setPage(page)
</script>
