<template>
    <div class="tw-flex tw-flex-col tw-py-2 tw-rounded tw-bg-white">
        <div class="tw-flex tw-py-4 tw-flex-wrap">
            <BarChart :height="100"  :chart-data="chartData"></BarChart>
        </div>
        <div>
            <LineChart :height="100" :chart-data="graphData"></LineChart>
        </div>
    </div>
</template>

<script>
    import BarChart from "@core/libs/chartjs/components/BarChart";
    import LineChart from "@core/libs/chartjs/components/LineChart";

    export default {
        components: {
            BarChart,
            LineChart
        },

        props: {
            report: {
                required: true
            },

            graphReport: {
                required: true
            }
        },

        computed: {
            graphData() {
                let labels = []
                let graphs = []

                this.graphReport.forEach((v, i) => {
                    let chartData = v.data

                    for (const i in chartData) {
                        let cd = []

                        if (typeof chartData[i]?.value === 'object') {
                            for (const j in chartData[i].value) {
                                if (!labels.includes(j)) {
                                    labels.push(j)
                                }

                                cd.push(chartData[i].value[j])
                            }

                            graphs.push({
                                data: cd,
                                borderColor: "#" + ((1 << 24) * Math.random() | 0).toString(16).padStart(6, "0"),
                                fill: false,
                                label: chartData[i]?.label
                            })
                        }
                    }


                })

                console.log(labels)

                return {
                    labels: labels,
                    datasets: graphs
                }
            },

            chartData() {
                let data = {
                    labels: this.labels,
                    datasets: [
                        {
                            maxBarThickness: 15,
                            borderColor: 'transparent',
                            borderRadius: {
                                topRight: 15,
                                topLeft: 15,
                            },
                            backgroundColor: '#e352059e',
                            data: this.chartValues
                        }
                    ],
                }

                return data
            },

            chartValues() {
                let values = []
                for (let i in this.report.data) {
                    values.push(this.report.data[i].value)
                }
                return values
            },

            labels() {
                let labels = []

                for (let i in this.report.data) {
                    labels.push(this.report.data[i].label)
                }

                return labels
            }
        }
    }
</script>
