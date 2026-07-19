<template>
    <input
        type="text"
        class="search-bar w-full"
        :value="formattedValue"
        @input="onInput"
        @keypress="onlyAllowNumbers"
        @blur="formatDisplay"
    />
</template>

<script>
export default {
    name: "CurrencyInput",
    props: {
        modelValue: {
            type: [String, Number],
            default: ""
        },
        max: {
            type: Number,
            default: Infinity
        },
        min: {
            type: Number,
            default: 0 // changed from Infinity
        }
    },
    data() {
        return {
            rawValue: "",
            formattedValue: ""
        };
    },
    watch: {
        modelValue: {
            immediate: true,
            handler(newVal) {
                this.rawValue = newVal?.toString() || "";
                this.formatDisplay();
            }
        }
    },
    methods: {
        onInput(e) {
            // strip everything except digits and "."
            let value = e.target.value.replace(/[^0-9.]/g, "");
            const parts = value.split(".");
            if (parts.length > 2) {
                value = parts[0] + "." + parts[1];
            }

            this.rawValue = value;

            // clamp to min/max
            let numericValue = parseFloat(value);
            if (!isNaN(numericValue)) {
                if (numericValue > this.max) numericValue = this.max;
                if (numericValue < this.min) numericValue = this.min;
            }

            // keep raw numeric value for parent
            this.$emit("update:modelValue", numericValue || "");

            // temporary display while typing (not formatted yet)
            this.formattedValue = value;
        },
        formatDisplay() {
            if (this.rawValue === "") {
                this.formattedValue = "";
                return;
            }

            const number = parseFloat(this.rawValue);
            if (!isNaN(number)) {
                this.formattedValue = new Intl.NumberFormat("en-US", {
                    style: "currency",
                    currency: "USD",
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(number);
            }
        },
        onlyAllowNumbers(e) {
            const allowed = /[0-9.]/;
            if (!allowed.test(e.key)) {
                e.preventDefault();
            }
        }
    }
};
</script>
