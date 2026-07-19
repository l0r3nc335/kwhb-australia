<script setup>
import logo from '@images/kwhb_logo1.png';
</script>

<template>
  <div class="loader_wrapper">
    <div class="loader_container text-center">
      <v-img :src="logo" width="100" class="mb-6"></v-img>

      <v-progress-circular
        :size="50"
        :width="7"
        color="primary"
        indeterminate
      ></v-progress-circular>
    </div>
  </div>
</template>

<style css>
.loader_wrapper {
  position: absolute;
  z-index: 12;
  display: flex;
  align-items: center;
  justify-content: center;
  background: transparent;
  block-size: 100%;
  inline-size: 100%;
}
</style>
