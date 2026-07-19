<template>
    <VDialog v-model="modelValue" width="620">
        <div class="tw-flex tw-flex-col tw-bg-white tw-p-6 tw-rounded-lg">
            <div class="tw-flex tw-justify-end">
                <div class="tw-py-2 tw-px-3" @click="$emit('update:modelValue', false)">
                    <VIcon icon="tabler-x"></VIcon>
                </div>
            </div>

            <div class="tw-flex tw-justify-center tw-font-bold tw-text-[1.8rem]" style="margin-top: -20px">
                Decline to sign
            </div>

            <!-- Dialog message -->
            <div class="__dialog_message tw-mt-5">
                <p>Please provide a reason for declining.</p>
                <textarea
                    v-model="rejectMessage"
                    placeholder="Type reason here"
                    id="input-54"
                    aria-describedby="input-54-messages"
                    class="v-field__input border border-black p-2 w-full resize-none"
                    rows="4"
                    maxlength="255"
                    style="height: 250px; padding: 10px; font-size: large"
                ></textarea>
            </div>


            <div class="tw-flex tw-justify-between tw-my-5">
                <div>
                    <VBtn
                        class="tw-px-4"  @click="handleRejectContinue"
                        :disabled="rejectMessage === null || rejectMessage === ''"
                    >
                        Continue
                    </VBtn>
                </div>

                <div>
                    <VBtn
                        class="tw-px-4"
                        color="#CCCCCC"
                        @click="$emit('update:modelValue', false)"
                    >
                        Cancel
                    </VBtn>
                </div>
            </div>

        </div>
    </VDialog>
</template>

<script>
import { defineComponent } from "vue"
import contractApi from "@/api/contract/contractApi"
import toast from "@/composable/toast"
import { useAuthStore } from "@/store/authStore"

export default defineComponent({
    name: 'RejectCommentDialogBox',
    props: {
        modelValue: {
            type: Boolean,
            required: true
        },
        contractId: {
            type: null,
            required: true
        },
        contractType: {
            type: null,
            required: false
        }
    },
    data() {
        return {
            htmlSignaturePageContent: null,
            rejectMessage: null,
        }
    },
    watch: {
        modelValue(val) {
            if (val) {
                this.rejectMessage = null;
            }
        }
    },
    methods: {
        async handleRejectContinue() {
            try{
                this.loading = true
                const formData = new FormData()
                formData.append('id', this.contractId)

                formData.append('type', this.contractType)
                formData.append('updated_by_id', useAuthStore().userData.id)
                formData.append('updated_by_name', useAuthStore().userData.name)

                formData.append('ceo_reject_message', this.rejectMessage)
                formData.append('status', 'rejected')
                formData.append('type_label', 'Contract')
                const response = await contractApi.rejectContract(formData)
                toast('success', response.message)
                this.loading = false
                this.$emit('reject-action', this.rejectMessage);
            }catch (e){
                toast(e)
            }
        }
    }
})
</script>

<style>

</style>
