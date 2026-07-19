<!-- MultiSelectWithCheckbox.vue -->
<template>
    <v-select
        v-model="internalValue"
        :items="items"
        item-title="name"
        item-value="id"
        multiple
        :label="placeholder"
        menu-icon=""
    >
        <!-- Custom rendering of dropdown items -->
        <template #item="{ props, item }">
            <v-list-item v-bind="props" class="custom-list-item">
                <template #prepend>
                    <v-checkbox
                        :model-value="internalValue.includes(item.raw.id)"
                        :ripple="false"
                        class="mr-2"
                    ></v-checkbox>
                </template>
            </v-list-item>
        </template>

        <!-- Custom rendering of selected items -->
        <template #selection="{ item, index }">
            <v-chip
                class="me-1"
                color="primary"
                text-color="white"
                small
                closable
                @click:close="removeItem(item.value)"
            >
                {{ item.title }}
            </v-chip>
        </template>
    </v-select>
</template>

<script>
export default {
    name: "SelectAddDropdownList",
    props: {
        modelValue: {
            type: Array,
            required: true
        },
        items: {
            type: Array,
            required: true
        },
        placeholder: {
            required: false
        }
    },
    data() {
        return {
            internalValue: this.modelValue
        };
    },
    watch: {
        modelValue(val) {
            this.internalValue = val;
        },
        internalValue(val) {
            this.$emit("update:modelValue", val);
        }
    },
    methods: {
        removeItem(id) {
            this.internalValue = this.internalValue.filter(item => item !== id);
        }
    }
}
</script>

<style scoped>
    .custom-list-item.v-list-item--active {
        background-color: #E3F2FD !important;
    }
    .custom-list-item.v-list-item--active .v-list-item-title {
        color: #000 !important;
    }

    .custom-list-item {
        min-height: 32px !important; /* default is ~48px */
        padding-top: 1px !important;
        padding-bottom: 1px !important;
    }

    .custom-list-item .v-list-item-title {
        font-size: 5px; /* optional: smaller text */
        line-height: 1.2;
    }
</style>
