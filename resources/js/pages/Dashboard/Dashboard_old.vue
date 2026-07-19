<template>
    <div>
        <VCard
            title="Employee Activity Summaries"
            subtitle="Leave, Timesheets and Certifications"
        >
            <VCardText>
                <VSlideGroup
                    v-model="currentTab"
                    show-arrows
                    mandatory
                >
                    <VSlideGroupItem
                        v-for="(report, index) in chartConfigs"
                        :key="report.title"
                        v-slot="{ isSelected, toggle }"
                        :value="index"
                    >
                        <div
                            style="block-size: 120px; inline-size: 210px;"
                            :style="isSelected ? 'border-color:rgb(var(--v-theme-primary)) !important' : ''"
                            :class="isSelected ? 'border' : 'border border-dashed'"
                            class="d-flex flex-column justify-center align-center cursor-pointer rounded px-5 py-4 me-6"
                            @click="toggle"
                        >
                            <VAvatar
                                rounded
                                size="38"
                                :color="isSelected ? 'primary' : 'secondary'"
                                variant="tonal"
                                class="mb-4"
                            >
                                <VIcon :icon="report.icon" />
                            </VAvatar>
                            <p class="mb-0 font-weight-medium">
                                {{ report.title }}
                            </p>
                        </div>
                    </VSlideGroupItem>
                </VSlideGroup>
                <VueApexCharts
                    ref="refVueApexChart"
                    v-if="!loading"
                    :key="currentTab"
                    :options="chartConfigs[Number(currentTab)].chartOptions"
                    :series="chartConfigs[Number(currentTab)].series"
                    height="240"
                    class="mt-12"
                />
            </VCardText>
        </VCard>
    </div>
</template>

<script>
    import dashboardApi from "@/api/dashboard/dashboardApi";
import { hexToRgb } from '@layouts/utils';
import VueApexCharts from 'vue3-apexcharts';
import { useTheme } from 'vuetify';

    export default {
        components: {
            VueApexCharts
        },

        data() {
            return {
                loading: false,
                currentTab: 0,
                refVueApexChart: null,
                summaries: {
                    leave_requests: [],
                    timesheets: [],
                    expired_vaccinations: [],
                    expired_compliance: []
                }
            }
        },

        computed: {
            chartConfigs() {
                let vuetifyTheme = useTheme()
                const currentTheme = vuetifyTheme.current.value.colors
                const variableTheme = vuetifyTheme.current.value.variables
                const labelPrimaryColor = `rgba(${ hexToRgb(currentTheme.primary) },${ variableTheme['dragged-opacity'] })`
                const legendColor = `rgba(${ hexToRgb(currentTheme['on-background']) },${ variableTheme['high-emphasis-opacity'] })`
                const borderColor = `rgba(${ hexToRgb(String(variableTheme['border-color'])) },${ variableTheme['border-opacity'] })`
                const labelColor = `rgba(${ hexToRgb(currentTheme['on-surface']) },${ variableTheme['disabled-opacity'] })`

                return [
                    {
                        title: 'Leave Requests',
                        icon: 'tabler-shopping-cart',
                        chartOptions: {
                            chart: {
                                parentHeightOffset: 0,
                                type: 'bar',
                                toolbar: { show: false },
                            },
                            plotOptions: {
                                bar: {
                                    columnWidth: '32%',
                                    startingShape: 'rounded',
                                    borderRadius: 4,
                                    distributed: true,
                                    dataLabels: { position: 'top' },
                                },
                            },
                            grid: {
                                show: false,
                                padding: {
                                    top: 0,
                                    bottom: 0,
                                    left: -10,
                                    right: -10,
                                },
                            },
                            colors: [
                                labelPrimaryColor,
                                labelPrimaryColor,
                                `rgba(${ hexToRgb(currentTheme.primary) },1)`,
                                labelPrimaryColor,
                                labelPrimaryColor,
                                labelPrimaryColor,
                                labelPrimaryColor,
                                labelPrimaryColor,
                                labelPrimaryColor,
                            ],
                            dataLabels: {
                                enabled: true,
                                formatter(val) {
                                    return `${ val }`
                                },
                                offsetY: -25,
                                style: {
                                    fontSize: '15px',
                                    colors: [legendColor],
                                    fontWeight: '600',
                                    fontFamily: 'Public Sans',
                                },
                            },
                            legend: { show: false },
                            tooltip: { enabled: false },
                            xaxis: {
                                categories: [
                                    'Jan',
                                    'Feb',
                                    'Mar',
                                    'Apr',
                                    'May',
                                    'Jun',
                                    'Jul',
                                    'Aug',
                                    'Sep',
                                    'Oct',
                                    'Nov',
                                    'Dec',
                                ],
                                axisBorder: {
                                    show: true,
                                    color: borderColor,
                                },
                                axisTicks: { show: false },
                                labels: {
                                    style: {
                                        colors: labelColor,
                                        fontSize: '13px',
                                        fontFamily: 'Public Sans',
                                    },
                                },
                            },
                            yaxis: {
                                labels: {
                                    offsetX: -15,
                                    formatter(val) {
                                        return `${ Number.parseInt(String(val / 1)) }`
                                    },
                                    style: {
                                        fontSize: '13px',
                                        colors: labelColor,
                                        fontFamily: 'Public Sans',
                                    },
                                    min: 0,
                                    max: 60000,
                                    tickAmount: 6,
                                },
                            },
                            responsive: [
                                {
                                    breakpoint: 1441,
                                    options: { plotOptions: { bar: { columnWidth: '41%' } } },
                                },
                                {
                                    breakpoint: 590,
                                    options: {
                                        plotOptions: { bar: { columnWidth: '61%' } },
                                        yaxis: { labels: { show: false } },
                                        grid: {
                                            padding: {
                                                right: 0,
                                                left: -20,
                                            },
                                        },
                                        dataLabels: {
                                            style: {
                                                fontSize: '12px',
                                                fontWeight: '400',
                                            },
                                        },
                                    },
                                },
                            ],
                        },
                        series: [{
                            data: this.summaries.leave_requests
                        }],
                    },
                    {
                        title: 'Timesheets Submitted',
                        icon: 'tabler-clock',
                        chartOptions: {
                            chart: {
                                parentHeightOffset: 0,
                                type: 'bar',
                                toolbar: { show: false },
                            },
                            plotOptions: {
                                bar: {
                                    columnWidth: '32%',
                                    startingShape: 'rounded',
                                    borderRadius: 4,
                                    distributed: true,
                                    dataLabels: { position: 'top' },
                                },
                            },
                            grid: {
                                show: false,
                                padding: {
                                    top: 0,
                                    bottom: 0,
                                    left: -10,
                                    right: -10,
                                },
                            },
                            colors: [
                                labelPrimaryColor,
                                labelPrimaryColor,
                                labelPrimaryColor,
                                labelPrimaryColor,
                                labelPrimaryColor,
                                labelPrimaryColor,
                                currentTheme.primary,
                                labelPrimaryColor,
                                labelPrimaryColor,
                            ],
                            dataLabels: {
                                enabled: true,
                                formatter(val) {
                                    return `${ val }`
                                },
                                offsetY: -25,
                                style: {
                                    fontSize: '15px',
                                    colors: [legendColor],
                                    fontWeight: '600',
                                    fontFamily: 'Public Sans',
                                },
                            },
                            legend: { show: false },
                            tooltip: { enabled: false },
                            xaxis: {
                                categories: [
                                    'Jan',
                                    'Feb',
                                    'Mar',
                                    'Apr',
                                    'May',
                                    'Jun',
                                    'Jul',
                                    'Aug',
                                    'Sep',
                                    'Oct',
                                    'Nov',
                                    'Dec',
                                ],
                                axisBorder: {
                                    show: true,
                                    color: borderColor,
                                },
                                axisTicks: { show: false },
                                labels: {
                                    style: {
                                        colors: labelColor,
                                        fontSize: '13px',
                                        fontFamily: 'Public Sans',
                                    },
                                },
                            },
                            yaxis: {
                                labels: {
                                    offsetX: -15,
                                    formatter(val) {
                                        return `${ Number.parseInt(String(val / 1)) }`
                                    },
                                    style: {
                                        fontSize: '13px',
                                        colors: labelColor,
                                        fontFamily: 'Public Sans',
                                    },
                                    min: 0,
                                    max: 60000,
                                    tickAmount: 6,
                                },
                            },
                            responsive: [
                                {
                                    breakpoint: 1441,
                                    options: { plotOptions: { bar: { columnWidth: '41%' } } },
                                },
                                {
                                    breakpoint: 590,
                                    options: {
                                        plotOptions: { bar: { columnWidth: '61%' } },
                                        grid: { padding: { right: 0 } },
                                        dataLabels: {
                                            style: {
                                                fontSize: '12px',
                                                fontWeight: '400',
                                            },
                                        },
                                        yaxis: { labels: { show: false } },
                                    },
                                },
                            ],
                        },
                        series: [{
                            data: this.summaries.timesheets,
                        }],
                    },
                    {
                        title: 'Expired Vaccinations',
                        icon: 'tabler-currency-dollar',
                        chartOptions: {
                            chart: {
                                parentHeightOffset: 0,
                                type: 'bar',
                                toolbar: { show: false },
                            },
                            plotOptions: {
                                bar: {
                                    columnWidth: '32%',
                                    startingShape: 'rounded',
                                    borderRadius: 4,
                                    distributed: true,
                                    dataLabels: { position: 'top' },
                                },
                            },
                            grid: {
                                show: false,
                                padding: {
                                    top: 0,
                                    bottom: 0,
                                    left: -10,
                                    right: -10,
                                },
                            },
                            colors: [
                                labelPrimaryColor,
                                labelPrimaryColor,
                                labelPrimaryColor,
                                labelPrimaryColor,
                                currentTheme.primary,
                                labelPrimaryColor,
                                labelPrimaryColor,
                                labelPrimaryColor,
                                labelPrimaryColor,
                            ],
                            dataLabels: {
                                enabled: true,
                                formatter(val) {
                                    return `${ val }`
                                },
                                offsetY: -25,
                                style: {
                                    fontSize: '15px',
                                    colors: [legendColor],
                                    fontWeight: '600',
                                    fontFamily: 'Public Sans',
                                },
                            },
                            legend: { show: false },
                            tooltip: { enabled: false },
                            xaxis: {
                                categories: [
                                    'Jan',
                                    'Feb',
                                    'Mar',
                                    'Apr',
                                    'May',
                                    'Jun',
                                    'Jul',
                                    'Aug',
                                    'Sep',
                                    'Oct',
                                    'Nov',
                                    'Dec',
                                ],
                                axisBorder: {
                                    show: true,
                                    color: borderColor,
                                },
                                axisTicks: { show: false },
                                labels: {
                                    style: {
                                        colors: labelColor,
                                        fontSize: '13px',
                                        fontFamily: 'Public Sans',
                                    },
                                },
                            },
                            yaxis: {
                                labels: {
                                    offsetX: -15,
                                    formatter(val) {
                                        return `${ Number.parseInt(String(val / 1)) }`
                                    },
                                    style: {
                                        fontSize: '13px',
                                        colors: labelColor,
                                        fontFamily: 'Public Sans',
                                    },
                                    min: 0,
                                    max: 60000,
                                    tickAmount: 6,
                                },
                            },
                            responsive: [
                                {
                                    breakpoint: 1441,
                                    options: { plotOptions: { bar: { columnWidth: '41%' } } },
                                },
                                {
                                    breakpoint: 590,
                                    options: {
                                        plotOptions: { bar: { columnWidth: '61%' } },
                                        grid: { padding: { right: 0 } },
                                        dataLabels: {
                                            style: {
                                                fontSize: '12px',
                                                fontWeight: '400',
                                            },
                                        },
                                        yaxis: { labels: { show: false } },
                                    },
                                },
                            ],
                        },
                        series: [{
                            data: this.summaries.expired_vaccinations,
                        }],
                    },
                    {
                        title: 'Expired Certifications',
                        icon: 'tabler-chart-pie-2',
                        chartOptions: {
                            chart: {
                                parentHeightOffset: 0,
                                type: 'bar',
                                toolbar: { show: false },
                            },
                            plotOptions: {
                                bar: {
                                    columnWidth: '32%',
                                    startingShape: 'rounded',
                                    borderRadius: 4,
                                    distributed: true,
                                    dataLabels: { position: 'top' },
                                },
                            },
                            grid: {
                                show: false,
                                padding: {
                                    top: 0,
                                    bottom: 0,
                                    left: -10,
                                    right: -10,
                                },
                            },
                            colors: [
                                labelPrimaryColor,
                                labelPrimaryColor,
                                labelPrimaryColor,
                                labelPrimaryColor,
                                labelPrimaryColor,
                                labelPrimaryColor,
                                labelPrimaryColor,
                                labelPrimaryColor,
                                currentTheme.primary,
                            ],
                            dataLabels: {
                                enabled: true,
                                formatter(val) {
                                    return `${ val }`
                                },
                                offsetY: -25,
                                style: {
                                    fontSize: '15px',
                                    colors: [legendColor],
                                    fontWeight: '600',
                                    fontFamily: 'Public Sans',
                                },
                            },
                            legend: { show: false },
                            tooltip: { enabled: false },
                            xaxis: {
                                categories: [
                                    'Jan',
                                    'Feb',
                                    'Mar',
                                    'Apr',
                                    'May',
                                    'Jun',
                                    'Jul',
                                    'Aug',
                                    'Sep',
                                    'Oct',
                                    'Nov',
                                    'Dec',
                                ],
                                axisBorder: {
                                    show: true,
                                    color: borderColor,
                                },
                                axisTicks: { show: false },
                                labels: {
                                    style: {
                                        colors: labelColor,
                                        fontSize: '13px',
                                        fontFamily: 'Public Sans',
                                    },
                                },
                            },
                            yaxis: {
                                labels: {
                                    offsetX: -15,
                                    formatter(val) {
                                        return `${ Number.parseInt(String(val / 1)) }`
                                    },
                                    style: {
                                        fontSize: '13px',
                                        colors: labelColor,
                                        fontFamily: 'Public Sans',
                                    },
                                    min: 0,
                                    max: 60000,
                                    tickAmount: 6,
                                },
                            },
                            responsive: [
                                {
                                    breakpoint: 1441,
                                    options: { plotOptions: { bar: { columnWidth: '41%' } } },
                                },
                                {
                                    breakpoint: 590,
                                    options: {
                                        plotOptions: { bar: { columnWidth: '50%' } },
                                        dataLabels: {
                                            style: {
                                                fontSize: '12px',
                                                fontWeight: '400',
                                            },
                                        },
                                        grid: { padding: { right: 0 } },
                                        yaxis: { labels: { show: false } },
                                    },
                                },
                            ],
                        },
                        series: [{
                            data: this.summaries.expired_compliance,
                        }],
                    },
                ]
            }
        },

        mounted() {
            this.loadSummaries()
        },

        methods: {
            async loadSummaries() {
                this.loading = true
                let summaries = await dashboardApi.getSummaries()
                this.summaries.leave_requests = summaries?.leave_requests || []
                this.summaries.timesheets = summaries?.timesheets || []
                this.summaries.expired_vaccinations = summaries?.expired_vaccinations || []
                this.summaries.expired_compliance = summaries?.expired_compliance || []
                this.loading = false

            }
        }
    }
</script>
