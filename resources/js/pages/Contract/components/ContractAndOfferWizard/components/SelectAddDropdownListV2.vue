<!-- MultiSelectWithCheckbox.vue -->
<template>
    <v-select
        v-model="internalValueComputed"
        :items="items"
        item-title="name"
        item-value="id"
        multiple
        :label="placeholder"
        v-model:menu="menuOpen"
    >
        <!-- Custom rendering of dropdown items -->
        <template #item="{ props, item }">
            <v-list-item v-bind="props" class="custom-list-item">
                <template #prepend>
                    <!--v-checkbox
                        :model-value="internalValue.includes(item.raw.id)"
                        :ripple="false"
                        class="mr-2"
                    ></v-checkbox-->
                    <!--input
                        type="checkbox"
                        :checked="internalValue.includes(item.raw.id)"
                        class="mr-2 __custom_checkbox"
                    /-->
                    <div
                        class="proxy-checkbox"
                        :class="{ checked: internalValueComputed.includes(item.raw.id) }"
                        @click.stop="toggleItem(item.raw.id)"
                        role="checkbox"
                        :aria-checked="internalValueComputed.includes(item.raw.id)"
                        tabindex="0"
                    />
                </template>
            </v-list-item>
        </template>

        <!-- Custom rendering of selected items -->
        <template #selection="{ item, index }">
            <v-chip
                class="me-1 __chip"
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
            internalValue: this.modelValue,
            menuOpen: false
        };
    },
    /*watch: {
        modelValue(val) {
            this.internalValue = val;
        },
        internalValue(val) {
            const selectedObjects = this.items.filter(item => val.includes(item.id));
            this.$emit("update:modelValue", selectedObjects);
        }
    },*/
    computed: {
            internalValueComputed: {
                get() {
                    // Return an array of IDs from the objects in modelValue
                    return Array.isArray(this.modelValue)
                        ? this.modelValue.map(obj => obj.id)
                        : [];
                },
                set(val) {
                    // Convert IDs to full objects
                    const selectedObjects = this.items.filter(item => val.includes(item.id));
                    this.$emit("update:modelValue", selectedObjects);
                }
            }
    },
    methods: {
        /*removeItem(id) {
            this.internalValue = this.internalValue.filter(item => item !== id);
        },
        toggleItem(id) {
            if (this.internalValue.includes(id)) {
                this.internalValue = this.internalValue.filter(item => item !== id);
            } else {
                this.internalValue = [...this.internalValue, id];
            }
        },*/
        toggleItem(id) {
            const ids = this.internalValueComputed.slice(); // copy of IDs array
            const index = ids.indexOf(id);
            if (index !== -1) {
                ids.splice(index, 1);
            } else {
                ids.push(id);
            }
            this.internalValueComputed = ids; // setter will emit full objects
        },
        removeItem(id) {
            this.internalValueComputed = this.internalValueComputed.filter(itemId => itemId !== id);
        }
    }
}
</script>

<style scoped>
    .custom-list-item {
        color: #808494!important;
    }
    .custom-list-item.v-list-item--active {
        background-color: white!important;
        color: #808494!important;
    }
    .custom-list-item.v-list-item--active .v-list-item-title {
        color: #808494 !important;
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

<style>
.__chip {
    color: #808494!important;
}
.v-chip__content {
    color: black!important;
}
input[type="checkbox"] {
    width: 18px!important;
    height: 18px!important;
    accent-color: seagreen!important; /*#60C378*/
}

/*
.v-list-item .v-list-item--active .v-list-item--link .v-theme--light .text-primary .v-list-item--density-comfortable .v-list-item--one-line .v-list-item--variant-text .custom-list-item {
    background-color: white!important;
}


.v-list-item--active {
    background-color: white!important;
}

.__chip {
    background-color: white!important;
}*/
.v-list-item--active {
    background-color: red!important;
}


/* CUSTOM CHECKBOX */
.proxy-checkbox {
    width: 18px;
    height: 18px;
    border-radius: 4px;
    background-color: #eee;
    border: 2px solid #aaa;
    display: inline-block;
    position: relative;
    cursor: pointer;
    transition: background-color 0.2s, border-color 0.2s;
    margin-right: 12px;
}

.proxy-checkbox.checked {
    background-color: #60C378; /* custom green */
    border-color: #60C378;
}

.proxy-checkbox.checked::after {
    content: "";
    position: absolute;
    top: 1px;
    left: 4px;
    width: 5px;
    height: 10px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}
</style>
