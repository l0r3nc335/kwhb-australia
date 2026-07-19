<script setup>

import toast from '@/composable/toast.js';
import { useEmployeeStore } from "@/store/employeeStore";
const employeeStore = useEmployeeStore();

const props = defineProps({
  data: {
    type: null,
    required: true,
  },
})

let edit_tfn = ref(false)
let edit_abn = ref(false)
let edit_status = ref(false)

const updateRow = async (row, data)=>{

var update = await employeeStore.updateRow({
    id: props.data.id,
    data,
    row
  })
  toast('success',"Updated!")
  edit_tfn = false
  edit_abn = false
}
</script>

<template>
  <VCard class="mb-4">
    <VCardText>
      <p class="text-lg d-flex justify-space-between">
        <span>
          <b>Tax File Number</b>
        </span>
        <span class="cursor-pointer" @click="edit_tfn = !edit_tfn">
          <VIcon
            icon="tabler-pencil"
            size="25"
            class="me-2"
          />
        </span>
      </p>
      <VList class="card-list text-medium-emphasis">
        <VListItem>
          <VListItemTitle>
            <span class="font-weight-medium me-1"></span>
            <span v-if="!edit_tfn">{{ props.data.tax_number}}</span>
            <span v-else>
              <AppTextField
                v-model="props.data.tax_number"
                placeholder="123456"
                @keyup.enter="updateRow('tax_number', props.data.tax_number)"
              />
            </span>
          </VListItemTitle>
        </VListItem>
      </VList>
    </VCardText>

    <VCardText>
      <p class="text-lg d-flex justify-space-between">
        <span>
          <b>Australian Business Number (ABN)</b>
        </span>
        <span class="cursor-pointer" @click="edit_abn = !edit_abn">
          <VIcon
            icon="tabler-pencil"
            size="25"
            class="me-2"
          />
        </span>
      </p>
      <VList class="card-list text-medium-emphasis">
        <VListItem>
          <VListItemTitle>
            <span class="font-weight-medium me-1"></span>
            <span v-if="!edit_abn">{{ props.data.abn}}</span>
            <span v-else>
            <AppTextField
              v-model="props.data.abn"
              placeholder="123456"
              @keyup.enter="updateRow('abn', props.data.abn)"
            />
            </span>
          </VListItemTitle>
        </VListItem>
      </VList>
    </VCardText>
    <VCardText>
      <p class="text-lg d-flex justify-space-between">
        <span>
          <b>Employment </b>
        </span>
      </p>
      <VList class="card-list text-medium-emphasis">
        <VListItem>
          <VListItemTitle>
            <span class="font-weight-medium me-1"></span>
            <span>{{ props.data.status}}</span>
          </VListItemTitle>
        </VListItem>
      </VList>
    </VCardText>
  </VCard>
</template>

<style lang="scss" scoped>
.card-list {
  --v-card-list-gap: 16px;
}
</style>
