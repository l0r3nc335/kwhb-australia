<template>
  <div class="tw-p-4">
    <h2 class="tw-text-lg tw-font-semibold tw-mb-4">Colour</h2>
    <div class="tw-grid tw-grid-cols-5 tw-gap-4">
      <label
        v-for="color in colors"
        :key="color.name"
        class="tw-flex tw-flex-col tw-items-center tw-cursor-pointer"
      >
        <input
          type="radio"
          class="tw-sr-only"
          :value="color.name"
          v-model="selected"
          @change="$emit('update:color', selected)"
        />
        <div
          :class="[
            'tw-w-8 tw-h-8 tw-rounded-full tw-border tw-border-gray-300',
            color.class,
            selected === color.name
              ? 'tw-ring-2 tw-ring-sky-400 tw-ring-offset-2'
              : ''
          ]"
        ></div>
        <span class="tw-text-sm tw-mt-2">{{ color.label }}</span>
      </label>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      selected: 'black',
      colors: [
        {
          name: 'policy',
          label: 'Yellow',
          class: 'tw-bg-[#FBB001]',
        },
        {
          name: 'payroll',
          label: 'Purple',
          class: 'tw-bg-[#9B5DE5]',
        },
        {
          name: 'procedure',
          label: 'Green',
          class: 'tw-bg-[#2BC670]',
        },
        {
          name: 'process',
          label: 'Red',
          class: 'tw-bg-[#F64300]',
        }
      ]
    };
  }
};
</script>
