<template>
    <VDialog v-model="modelValue" width="1220" style="margin-left: 220px">
    <div class="tw-flex tw-flex-col tw-bg-white tw-p-2 tw-rounded-lg">
        <div>
            <div class="tw-h-20 tw-flex tw-items-center tw-justify-center tw-px-3" style="font-size: 1.5rem; font-weight: 400; margin: 0;">
                <strong>History</strong>
            </div>
            <!--div class="tw-py-2 tw-px-3" @click="$emit('update:modelValue', false)">
                <VIcon icon="tabler-x"></VIcon>
            </div-->
        </div>

        <div class="__dialog_message">

            <div class="__table_wrapper">
                <table class="__history_table">
                    <thead >
                    <tr>
                        <th class="__min-width-time">Time</th>
                        <th class="__min-width-user">User</th>
                        <th>Action</th>
                        <th class="__min-width-activity">Activity</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(row, index) in historyData" :key="index">
                        <td class="__min-width-time">{{ formatDate(row.created_at) }}</td>
                        <td class="__min-width-user">{{ row.user_logs.name }}</td>
                        <td>{{ capitalizeFirst(row.action) }}</td>
                        <td class="__min-width-activity">{{ row.activity }}</td>
                        <td>{{ row.status }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>

        </div>

        <div class="tw-flex tw-justify-center tw-my-5">
            <VBtn
                class="tw-px-4 tw-ms-3"
                style="height: 30px!important; width: 80px!important;"
                @click="$emit('update:modelValue', false)"
            >
                Close
            </VBtn>
        </div>
    </div>
</VDialog>
</template>

<script>
import { defineComponent } from "vue"
import contractApi from "@/api/contract/contractApi"
import { VDataTableServer } from 'vuetify/labs/VDataTable';

export default defineComponent({
    name: 'ContractLetterOfOfferHistory',
    components: {
        VDataTableServer,
    },
    props: {
        modelValue: {
            type: Boolean,
            required: true
        },
        contractId: {
            type: null,
            required: false
        },
    },
    data() {
        return {
            htmlSignaturePageContent: null,
            historyData: [],
            loadItems: false,
            pagination: {
                page: 1,
                per_page: 10,
                sortBy: null,
                sortDesc: false,
            },
            headers: [
                {
                    id: 0,
                    title: 'TIME',
                    key: 'created_at',
                    align: 'center',
                    sortable: false
                },
                {
                    id: 1,
                    title: 'USER',
                    key: 'user_logs.name',
                    align: 'center',
                    sortable: true
                },
                {
                    id: 2,
                    title: 'ACTION',
                    key: 'action',
                    align: 'center',
                    sortable: true
                },
                {
                    id: 3,
                    title: 'ACTIVITY',
                    key: 'activity',
                    align: 'center',
                    sortable: true
                },
                {
                    id: 4,
                    title: 'STATUS',
                    key: 'status',
                    align: 'center',
                    sortable: true
                },
            ],
            total: 0,
        }
    },
    watch: {
        contractId(newVal) {
            this.loadItemHistory()
            if (this.modelValue === true && newVal) {
                this.loadItemHistory()
            }
        }
    },
    methods: {
        formatDate(datetime) {
            if (!datetime) return '';
            const date = new Date(datetime);

            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are 0-based
            const year = date.getFullYear();

            let hours = date.getHours();
            const minutes = String(date.getMinutes()).padStart(2, '0');
            const seconds = String(date.getSeconds()).padStart(2, '0');
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12 || 12;

            return `${day}-${month}-${year} ${hours}:${minutes}:${seconds} ${ampm}`;
        },
        capitalizeFirst(text) {
            if (!text) return '';
            return text.charAt(0).toUpperCase() + text.slice(1);
        },
        handleContinueRejection() {
            this.$emit('reject-action');
            //this.$emit('update:modelValue', false);
        },
        async loadItemHistory() {
            this.loadItems = true;
            const formData = new FormData()
            formData.append('contract_id', this.contractId)
            this.historyData = await contractApi.fetchContractHistory(formData)
            this.loadItems = false;
        },
    }
})
</script>

<style scoped>

.__caution_icon {
    color: red!important;
    background-color: #FCF1E8;
    font-size: 32px;
    padding: 5px;
}

.__dialog_message {
    padding: 0 60px;
    font-family: Arial, sans-serif;
    font-size: 16px;
    line-height: 1.1;
}

.__table_wrapper {
    max-height: 400px!important;
    overflow-y: scroll!important;
}

.__history_table {
    border-collapse: collapse!important;
    width: 100%!important;
    border: 1px solid black!important;
}

.__history_table thead, tr, th, td {
    padding: 15px;
    border: 1px solid #F2F2F2;
    text-align: left;
    border-collapse: collapse;
}

.__min-width-activity {
    min-width: 250px!important;
    white-space: normal!important;
    word-break: break-word!important;
    overflow-wrap: anywhere!important;
}

.__min-width-user {
    min-width: 110px!important;
    white-space: normal!important;
    word-break: break-word!important;
    overflow-wrap: anywhere!important;
}

.__min-width-time {
    max-width: 80px!important;
    white-space: normal!important;
    word-break: break-word!important;
    overflow-wrap: anywhere!important;
}

</style>
