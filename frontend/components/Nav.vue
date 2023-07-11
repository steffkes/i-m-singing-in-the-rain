<template>
  <nav
    class="fixed left-0 right-0 top-0 z-10 bg-white flex flex-col lg:flex-row justify-between items-center h-12 lg:border-b border-primary"
  >
    <div class="flex justify-between items-center w-full lg:w-auto">
      <a href="/"><img src="/images/logo.png" class="h-12 py-2" /></a>
      <i
        href="#"
        class="toggle-button lg:hidden flex flex-col justify-between bg-primary h-12 w-12 p-3"
        @click="toggleMenu"
      >
        <span class="h-1 bg-dark"></span>
        <span class="h-1 bg-dark"></span>
        <span class="h-1 bg-dark"></span>
      </i>
    </div>
    <nav
      v-if="site.data.content.nav"
      class="lg:block lg:h-full"
      :class="isMenuOpen ? 'block' : 'hidden lg:block'"
    >
      <ul
        class="h-screen bg-white w-screen lg:bg-none lg:w-auto lg:flex items-center lg:h-full"
      >
        <template v-for="entry in site.data.content.nav" :key="entry.id">
          <li
            class="border-b border-light lg:border-none lg:h-full"
          >
            <!-- @TODO 14 = 'http://backend' -->
            <NuxtLink
              :to="
                entry.url.slice(14) || '/'
              "
              class="px-8 py-6 h-full flex items-center space-x-8 lg:space-x-0"
              @click="toggleMenu"
            >
              <div class="lg:hidden">
                <img src="/images/chöreicon.svg" class="h-6" />
              </div>
              <div>{{ entry.text }}</div></NuxtLink
            >
          </li>
        </template>
      </ul>
    </nav>
  </nav>
</template>
<style scoped>
a.router-link-active {
  @apply bg-primary;
}
</style>

<script setup>

// @ TODO hardcoded
const { data: site } = await useFetch(
  "http://backend/api/site",
  {
    headers: {
      Authorization: "Basic cm9vdEBsb2NhbGhvc3QudGxkOlNlY3JldDEyIQ=="
    }
  }
);

const isMenuOpen = ref(false);
function toggleMenu() {
  isMenuOpen.value = !isMenuOpen.value;
}
</script>
