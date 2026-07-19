<template>
  <VDialog
    :model-value="props.isDialogVisible"
    max-width="740"
    @update:model-value="dialogVisibleUpdate"
  >
    <DialogCloseBtn @click="$emit('update:isDialogVisible', false)" />
    <VCard class="refer-and-earn-dialog">
      <VCardText class="">
        <VCard class="preview-card" style="background-color:white; padding: 10px; width: 80%; margin: 10px auto; font-size: 12px; color: black; box-shadow: none; height: auto;">
          <div style="flex: 1; overflow-y: auto; padding-bottom: 10px;" v-html="props.content"></div>
        </VCard>
      </VCardText>
    </VCard>
  </VDialog>
</template>

<script setup>
const props = defineProps({
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
  content: {
    type: String,
    required: false,
    default: '',
  },
});

const emit = defineEmits(['update:isDialogVisible']);

const dialogVisibleUpdate = (val) => {
  emit('update:isDialogVisible', val);
};

console.log(props.content)
</script>

<style scoped>
.preview-card {
  max-height: 80vh;
  overflow-y: auto;
}
</style>



