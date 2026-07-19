<template>
    <input
        type="text"
        class="search-bar w-full __input"
        :value="formattedValue"
        disabled
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
        currency: {
            type: String,
            default: "USD" // allows switching to PHP, EUR, etc.
        },
        locale: {
            type: String,
            default: "en-US" // change locale if needed
        }
    },
    computed: {
        formattedValue() {
            const number = parseFloat(this.modelValue);
            if (isNaN(number)) return "";
            return new Intl.NumberFormat(this.locale, {
                style: "currency",
                currency: this.currency,
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(number);
        }
    }
};
</script>

<style scoped>
.search-bar {
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 6px;
    background-color: #f9f9f9;
    color: #333;
    font-size: 14px;
}

.__input {
    height: 43px!important;
    line-height: 24px!important;
    padding-block-end: 6px!important;
    padding-block-start: 6px!important;
    padding-bottom: 6px!important;
    padding-inline-end: 30px!important;
    padding-inline-start: 15px!important;
    padding-left: 15px!important;
    padding-right: 35px!important;
    padding-top: 6px!important;
}
</style>
