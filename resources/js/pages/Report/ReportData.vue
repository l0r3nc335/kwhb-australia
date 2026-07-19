<template>
    <div class="tw-flex tw-flex-col tw-py-2 tw-shadow-lg tw-mr-4 tw-px-4 tw-mb-6 tw-border tw-border-gray-100">
        <div class="tw-flex tw-w-full tw-justify-left tw-text-[2.2rem] tw-font-bold">
            {{reportData.value}}
        </div>
        <div class="tw-py-2 tw-flex tw-justify-center tw-text-blue-900 tw-font-bold tw-text-sm">
            {{reportData.label}}
        </div>
    </div>
</template>

<script>
    import BarChart from "@core/libs/chartjs/components/BarChart";

    export default {
        components: {
            BarChart
        },

        props: {
            reportData: {
                required: true
            }
        },

        data() {
            return {

            }
        },

        computed: {
            chartData() {
                return [
                    {
                        labels: this.labels,
                        datasets: [
                            {
                                maxBarThickness: 15,
                                borderColor: 'transparent',
                                borderRadius: {
                                    topRight: 15,
                                    topLeft: 15,
                                },
                                data: this.chartValues
                            },
                        ]
                    }
                ]
            },

            chartValues() {
                return []
            },

            labels() {
                let labels = []



                return [

                ]
            }
        }
    }
</script>
