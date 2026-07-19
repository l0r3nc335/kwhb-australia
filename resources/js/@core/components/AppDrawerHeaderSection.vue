<script setup>
const props = defineProps({
  title: {
    type: String,
    required: true,
  },
})

const emit = defineEmits(['cancel'])
</script>

<template>
  <div class="px-6 py-6 d-flex align-center">
    <span
      class="text-h5"
      style="color: #333954 !important; font-weight: bolder !important;"
    >
      {{ props.title }}
    </span>
    <VSpacer />

    <slot name="beforeClose" />
    <div class="icon-wrapper">
      <IconBtn class="icon" @click="$emit('cancel')">
        <VIcon
          size="18"
          icon="tabler-x"
          
        />
      </IconBtn>
    </div>
  </div>
</template>
<style scoped>
.icon-wrapper {
  background-color: rgba(211, 211, 211, 0.575);
  border-radius: 5px;
}

.icon {
  width: 40px; /* Adjust size as needed */
  height: 40px; /* Adjust size as needed */
  
}
</style>