<template>
  <VChip
    label
    size="default"
    variant="plain"
    :style="{
      background: 'transparent',
      border: '2px solid ' + (item.is_acknowledged ? '#2BC670' : '#F1B434'),
      borderRadius: '5px',
      opacity: '1 !important',
      cursor: 'default'
    }"
  >
    <div :style="{ color: (item.is_acknowledged ? '#2BC670' : '#F1B434'), fontWeight: '500' }">
      {{ statusLabel }}
    </div>
  </VChip>
</template>

<script>
export default {
  name: 'ResourceAcknowledgementChip',
  props: {
    item: {
      type: Object,
      required: true,
    },
  },
  computed: {
    statusLabel() {
      return this.item.is_acknowledged ? 'Acknowledged' : 'Not Viewed';
    },
  },
};
</script>
