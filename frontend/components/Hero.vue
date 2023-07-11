<template>
  <header
    v-editable="blok"
    class="bg-cover bg-center mb-12 lg:mb-24"
    :class="{ 'h-screen': blok.fullscreen }"
    :style="`background-image: url(${blok.image.filename}/m/1920x0)`"
  >
    <div
      class="w-full h-full flex items-center border-b border-primary"
      :class="{ 'backdrop-brightness-50 text-white': blok.image.filename }"
    >
      <div
        class="container text-center"
        :class="{ 'py-24 lg:py-48': !blok.fullscreen }"
      >
        <h1>{{ blok.title }}</h1>
      </div>
    </div>
  </header>
</template>
