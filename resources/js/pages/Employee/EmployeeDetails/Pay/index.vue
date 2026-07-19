<template>
  <div>
      <AddNewBankDrawer
          v-model:isDrawerOpen="isAddNewBankDrawerVisible"
          @user-data="addNewBankInfo"
      />
      <VRow v-if="profileTabData">
          <VCol md="7" cols="12" v-if="hasPermissions('people.employee_pay_bank_information.view', employee_selected)">

              <VCard class="mb-4 bank-info-card">
                  <VCardText>
                      <p class="text-lg d-flex justify-space-between">
            <span>
              <b>Bank Information</b>
            </span>
                          <span class="cursor-pointer">
              <VBtn
                  variant="outlined"
                  size="default"
                  color="secondary"
                  @click="isAddNewBankDrawerVisible = true"
              >
               <b>Add Bank</b>
              </VBtn>
            </span>
                      </p>

                      <div class="pa-2 px-6 pay-theme-background rounded"
                           style="margin-top: 12px;"
                           v-for="(item, index) in bankInfo"
                           :key="item.id"
                      >
                          <VRow>
                              <VCol md="6" cols="12" class="mt-5 mb-2">
                                  <div class="text-lg mt-4">
                                      <span class="text-subtitle-1 font-weight-medium mr-1" v-if="!item.edit_fields">{{ item.account_name }}</span>
                                      <!-- {{ profileTabData.first_name }} {{ profileTabData.last_name }}  -->
                                      <span v-else>
                    <span style="color: #383E59; font-weight: bolder; font-size: 0.9375rem;">Account Name: </span>
                      <AppTextField
                          style="width: auto; background-color: white;"
                          v-model="item.account_name"
                          :rules="[requiredValidator]"
                          placeholder="123-234"
                          class="mr-1"
                      />
                    </span>
                                      <VChip
                                          class="pa-2 ml-3"
                                          label
                                          v-if="item.primary && !item.edit_fields"
                                          color="success"
                                          size="default"
                                          density="comfortable"
                                      >
                                          <span class="font-weight-bold">Primary</span>
                                      </VChip>
                                  </div>
                                  <table style="width: 100%" class="mt-1">
                                      <tr>
                                          <td>
                                              <b>BSB: </b>
                                              <span v-if="!item.edit_fields"> {{ item.bsb }} </span>
                                              <span v-else>
                      <AppTextField
                          style="width: auto; background-color: white;"
                          v-model="item.bsb"
                          :rules="[requiredValidator]"
                          placeholder="123-234"
                          class="mr-1"
                      />
                    </span>
                                          </td>
                                          <td>
                                              <b>Account: </b>
                                              <span v-if="!item.edit_fields">
                      {{ item.account }}
                    </span>
                                              <span v-else>
                      <AppTextField
                          style="width: auto; background-color: white;"
                          v-model="item.account"
                          :rules="[requiredValidator]"
                          placeholder="0864 54343"
                      />
                    </span>
                                          </td>
                                      </tr>
                                  </table>
                                  <span  v-if="item.edit_fields">
                  <VCheckbox
                      v-model="item.primary"
                      label="Primary"
                      :true-value="1"
                      :false-value="0"
                  />
                </span>
                                  <span>
                  <VCheckbox
                      :disabled="!item.edit_fields"
                      v-if="item.edit_fields || item.reimbursement"
                      v-model="item.reimbursement"
                      label="Reimbursement Account"
                      :true-value="1"
                      :false-value="0"
                  />
                </span>
                                  <span  v-if="item.edit_fields">
                  <VBtn
                      @click="updateBankInfo(item)"
                      :loading="loading"
                      class="mt-5"
                  >
                    Save
                  </VBtn>
                </span>
                              </VCol>
                              <VCol md="6" cols="12">
                                  <div class="text-right mt-3">
                                      <VBtn
                                          class="mr-2 font-weight-bold"
                                          @click="item.edit_fields = true"
                                          color="kwhb"
                                          variant="tonal"
                                          v-if="!item.edit_fields">Edit
                                      </VBtn>
                                      <VBtn
                                          class="mr-2 font-weight-bold"
                                          color="kwhb"
                                          variant="tonal"
                                          @click="cancel(item)"
                                          v-else>Cancel
                                      </VBtn>
                                      <v-dialog v-model="dialog" max-width="400">
                                          <v-card prepend-icon="mdi-warning" title="Confirm Delete" text="Are you sure you want to delete this item?">
                                              <v-card-actions>
                                                  <v-spacer></v-spacer>
                                                  <v-btn :loading="loadingDelete" @click="deleteBankInfo(item)" variant="tonal">Yes</v-btn>
                                                  <v-btn @click="dialog=false">No</v-btn>
                                              </v-card-actions>
                                          </v-card>
                                      </v-dialog>
                                      <VBtn
                                          class="font-weight-bold"
                                          color="kwhb-waring"
                                          @click="dialog=true"
                                      >Delete</VBtn>
                                  </div>
                                  <v-row class="ml-5 mt-2 mb-2">
                                      <v-col md="7" cols="12" class="px-0">
                                          <div class="text-subtitle-2 text-medium-emphasis">Pay Split</div>
                                          <AppSelect
                                              :disabled="!item.edit_fields && ((index > 0 && bankInfo[0].pay_split) || (index < 1 && !item.edit_fields))"
                                              style="background-color: white;"
                                              v-model="item.pay_split"
                                              placeholder="Percentage %"
                                              :items="(index > 0  && bankInfo[0].pay_split) ? [bankInfo[0].pay_split] : ['Percentage', 'Whole Number']"
                                              class="mr-2"
                                          />
                                      </v-col>
                                      <v-col md="5" cols="12" class="px-0">
                                          <div class="text-subtitle-2 text-medium-emphasis">Enter Value</div>
                                          <AppTextField
                                              :disabled="!item.edit_fields"
                                              v-model="item.pay_split_value "
                                              style="background-color: white;"
                                              :placeholder="item.pay_split == 'Whole Number'? '$0': '0%'"
                                              :prefix="item.pay_split == 'Whole Number' ? '$' : ''"
                                              :suffix="item.pay_split != 'Whole Number' ? '%' : ''"
                                              type="number"
                                              min="0"
                                              class="mr-3"
                                          />
                                      </v-col>
                                  </v-row>
                              </VCol>
                          </VRow>

                      </div>
                  </VCardText>
              </VCard>
          </VCol>

          <VCol
              cols="12"
              md="5"
              v-if="hasPermissions('people.employee_super_info.view', profileTabData)"
          >
              <SuperInformation :data="profileTabData" />
          </VCol>
      </VRow>
  </div>
</template>

<script>
import toast from '@/composable/toast.js';
import { useEmployeeStore } from "@/store/employeeStore";
import AddNewBankDrawer from "./AddNewBankDrawer.vue";
import SuperInformation from './SuperInformation.vue';

const employeeStore = useEmployeeStore()

export default {
    components: {
        AddNewBankDrawer,
        SuperInformation
    },

    data() {
        return {
            loading: false,
            loadingDelete: false,
            dialog: false,
            profileTabData: null,
            isAddNewBankDrawerVisible: false,
            bankInfo: [],
            bankInfoOriginal: null
        }
    },

    mounted() {
        this.profileTabData = employeeStore.getSelectedEmployee()
        this.bankInfo = Object.assign({}, this.profileTabData.bank_info)
        this.bankInfoOriginal = JSON.stringify(this.bankInfo)
    },

    methods: {
        cancel(item) {
            item.edit_fields = false
            this.bankInfo = JSON.parse(this.bankInfoOriginal)
        },
        async addNewBankInfo(data) {
            var result = await employeeStore.addBankInfo(data)
            if(!result) {
                toast('error',"Error please try again!")
            } else {
                toast('success',"Bank Information Added!")
                // Refresh local list!
                this.profileTabData = employeeStore.getSelectedEmployee()
                this.bankInfo = Object.assign({}, this.profileTabData.bank_info)
                this.bankInfoOriginal = JSON.stringify(this.bankInfo)
            }
        },

        async updateBankInfo(item) {
            this.loading = true
            let result = await employeeStore.updateBankInfo(item)
            let bankInfo = this.profileTabData.bank_info

            if(!result)  {
                toast('error',"Error please try again!")
                this.loading = false
            } else  {
                toast('success',"Bank Information Updated!")
                item.edit_fields = false
                this.loading = false

                if (bankInfo.length > 1) {
                    for (let i in bankInfo) {
                        if (this.bankInfo[0].pay_split) {
                            this.bankInfo[i].pay_split = bankInfo[0].pay_split
                        }
                    }
                }
            }
        },

        async deleteBankInfo() {
            this.loadingDelete = true
            let result = await employeeStore.deleteBankInfo(item)
            if(!result)  {
                toast('error',"Error please try again!")
                loadingDelete.value = false
            } else  {
                loadingDelete.value = false
                toast('success',"Bank Information Deleted!")
                item.edit_fields = false
                dialog.value = false
            }
        }
    }
}

</script>

<style lang="scss">
.pay-theme-background {
  background-color: #F8F8F9;
}
.bank-info-card {
  font-family: Arial, Helvetica, sans-serif;
  color: #383E59;
}
</style>
