<template>
    <div class="overflow-x-auto shadow-md rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th
                        v-for="column in columns"
                        :key="column.key"
                        scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        :class="column.headerClass || ''"
                        :style="column.style || ''"
                    >
                        {{ column.label }}
                    </th>
                    <th
                        v-if="hasActions"
                        scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr
                    v-for="(item, index) in data"
                    :key="item.id || index"
                    class="hover:bg-gray-50"
                >
                    <td
                        v-for="column in columns"
                        :key="column.key"
                        class="px-6 py-4 whitespace-nowrap text-sm"
                        :class="column.cellClass || 'text-gray-900'"
                    >
                        <template v-if="column.type === 'boolean'">
                            <span
                                :class="[
                                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                    item[column.key]
                                        ? 'bg-green-100 text-green-800'
                                        : 'bg-red-100 text-red-800',
                                ]"
                            >
                                {{ item[column.key] ? "Yes" : "No" }}
                            </span>
                        </template>

                        <template v-else-if="column.type === 'approval'">
                            <span
                                :class="[
                                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                    getApprovalClass(item[column.key]),
                                ]"
                            >
                                {{ formatApprovalStatus(item[column.key]) }}
                            </span>
                        </template>

                        <template v-else-if="column.type === 'number'">
                            {{ formatNumber(item[column.key], column.format) }}
                        </template>

                        <template v-else-if="column.type === 'date'">
                            {{ formatDate(item[column.key], column.format) }}
                        </template>

                        <template
                            v-else-if="
                                column.type === 'custom' && column.component
                            "
                        >
                            <component
                                :is="column.component"
                                :value="item[column.key]"
                                :row="item"
                                @change="
                                    (val) =>
                                        handleCustomChange(
                                            item,
                                            column.key,
                                            val
                                        )
                                "
                            />
                        </template>

                        <template v-else>
                            {{ item[column.key] }}
                        </template>
                    </td>

                    <td
                        v-if="hasActions"
                        class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                    >
                        <div class="flex space-x-2">
                            <button
                                v-if="actions.includes('view')"
                                @click="$emit('view', item)"
                                class="text-indigo-600 hover:text-indigo-900"
                                title="View"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                    />
                                </svg>
                            </button>

                            <button
                                v-if="actions.includes('edit')"
                                @click="$emit('edit', item)"
                                class="text-blue-600 hover:text-blue-900"
                                title="Edit"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                    />
                                </svg>
                            </button>

                            <button
                                v-if="actions.includes('delete')"
                                @click="$emit('delete', item)"
                                class="text-red-600 hover:text-red-900"
                                title="Delete"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                    />
                                </svg>
                            </button>

                            <slot name="actions" :item="item"></slot>
                        </div>
                    </td>
                </tr>

                <tr v-if="data.length === 0">
                    <td
                        :colspan="columns.length + (hasActions ? 1 : 0)"
                        class="px-6 py-4 text-center text-sm text-gray-500"
                    >
                        No data available
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <Pagination :paginationLinks="pagination" :meta="meta" />
</template>

<script setup>
import { computed } from "vue";
import Pagination from "@/Components/reusable/Pagination.vue";

const props = defineProps({
    columns: {
        type: Array,
        required: true,
        validator: (value) => value.every((col) => col.key && col.label),
    },
    data: {
        type: Array,
        required: true,
    },
    actions: {
        type: Array,
        default: () => ["view", "edit", "delete"],
    },
    pagination: {
        type: Object,
        default: null,
    },
    meta: {
        type: Object,
        default: () => ({}),
    },
    maxVisiblePages: {
        type: Number,
        default: 5,
    },
});

const emit = defineEmits(["custom-change", "page-change"]);

const hasActions = computed(() => {
    return props.actions.length > 0;
});

const currentPage = computed(() => props.pagination?.currentPage || 1);
const totalPages = computed(() => props.pagination?.totalPages || 1);

const visiblePages = computed(() => {
    if (!props.pagination) return [];

    const half = Math.floor(props.maxVisiblePages / 2);
    let start = Math.max(currentPage.value - half, 1);
    let end = Math.min(start + props.maxVisiblePages - 1, totalPages.value);

    if (end - start + 1 < props.maxVisiblePages) {
        start = Math.max(end - props.maxVisiblePages + 1, 1);
    }

    return Array.from({ length: end - start + 1 }, (_, i) => start + i);
});

function formatNumber(value, format) {
    if (value === null || value === undefined) return "";

    if (format === "currency") {
        return new Intl.NumberFormat("en-US", {
            style: "currency",
            currency: "USD",
        }).format(value);
    }

    if (format === "percent") {
        return `${(value * 100).toFixed(2)}%`;
    }

    return value.toString();
}

function formatDate(value, format) {
    if (!value) return "";

    const date = new Date(value);

    if (format === "short") {
        return date.toLocaleDateString();
    }

    if (format === "long") {
        return date.toLocaleString();
    }

    return date.toISOString().split("T")[0];
}

function getApprovalClass(status) {
    switch (status) {
        case "approved":
            return "bg-green-100 text-green-800";
        case "pending":
            return "bg-yellow-100 text-yellow-800";
        case "rejected":
            return "bg-red-100 text-red-800";
        default:
            return "bg-gray-100 text-gray-800";
    }
}

function formatApprovalStatus(status) {
    if (!status) return "N/A";
    return status.charAt(0).toUpperCase() + status.slice(1);
}

function handleCustomChange(item, key, value) {
    emit("custom-change", { item, key, value });
}

function changePage(page) {
    if (page >= 1 && page <= totalPages.value) {
        emit("page-change", page);
    }
}
</script>
