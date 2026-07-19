<script setup>
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

const referAndEarnSteps = [
  {
    icon: 'custom-paper',
    title: 'Send Invitation 👍🏻',
    subtitle: 'Send your referral link to your friend',
  },
  {
    icon: 'custom-keyboard',
    title: 'Registration 😎',
    subtitle: 'Let them register to our services',
  },
  {
    icon: 'custom-rocket',
    title: 'Free Trial  🎉',
    subtitle: 'Your friend will get 30 days free trial',
  },
]
</script>

<template>
  <VDialog
    :model-value="props.isDialogVisible"
    max-width="740"
    @update:model-value="dialogVisibleUpdate"
  >
    <!-- 👉 Dialog close btn -->
    <VCardTitle>
          <div style="display: flex; justify-content: space-between;">
            <span>Caution</span>
            <VBtn icon @click="emit('update:modelValue', false)">
              <VIcon>mdi-close</VIcon>
            </VBtn>
          </div>     
    </VCardTitle>

    <VCard class="refer-and-earn-dialog">
      <VCardText class="px-5 px-sm-16 pt-16 pb-10">
        <p>If you select <strong>CONTINUE</strong>, this document will be declared void.</p>
        <br>
        <p>To request changes to this document, please select <strong>CANCEL</strong> and contact the sender directly with your request.
        </p>
        <br>
        <p>You can exit signing and save therrr information that you've entered by using the <strong>FINISH LATER</strong> option in the <strong>MORE OPTIONS</strong> menu.</p>
        <br>
        <div class="buttons" style="display: flex;justify-content: center;">
          <button class="cancel">Cancel</button>
          <button class="continue">Continue</button>
        </div>
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
