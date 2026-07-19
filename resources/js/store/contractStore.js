import { defineStore } from "pinia";

export const useContractStore = defineStore('contract', {
    state: () => {
        return {
            contract: []
        };
    },

    persist: true,

    actions: {
        setContract(data) {
            this.contract = data;
        }
    }
});