<script setup>
import Signature from "@/pages/Contract/components/Signature.vue";

const props = defineProps({
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
})

const emit = defineEmits(['update:isDialogVisible'])

const dialogVisibleUpdate = val => {
  emit('update:isDialogVisible', val)
}

</script>

<template>
  <VDialog
    :model-value="props.isDialogVisible"
    max-width="740"
    @update:model-value="dialogVisibleUpdate"
  >
    <!-- 👉 Dialog close btn -->
    <!-- <VCardTitle>
          <div style="display: flex; justify-content: space-between;">
            <span>Signature</span>
            <VBtn icon @click="emit('update:modelValue', false)">
              <VIcon>mdi-close</VIcon>
            </VBtn>
          </div>     
    </VCardTitle> -->
    <DialogCloseBtn @click="$emit('update:isDialogVisible', false)" />
    <VCard class="refer-and-earn-dialog">
      <VCardText class="px-5 px-sm-16 pt-16 pb-10">
        <h2 style="text-align: center; font-size: 18px;">Signature</h2>
        <Signature></Signature>
      </VCardText>
    </VCard>
  </VDialog>
</template>

<style lang="scss">
.refer-link-input {
  .v-field--appended {
    padding-inline-end: 0;
  }

  .v-field__append-inner {
    padding-block-start: 0.125rem;
  }
}
</style>