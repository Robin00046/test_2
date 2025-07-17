<template>
    <nav
        class="flex items-center flex-column flex-wrap md:flex-row justify-between pt-4"
        aria-label="Table navigation"
    >
        <span
            class="text-sm font-normal text-gray-500 dark:text-gray-400 mb-4 md:mb-0 block w-full md:inline md:w-auto"
            >Showing
            <span class="font-semibold text-gray-900 dark:text-white">
                {{ meta.from }} - {{ meta.to }}
            </span>
            of
            <span class="font-semibold text-gray-900 dark:text-white">{{
                meta.total
            }}</span></span
        >
        <ul class="inline-flex -space-x-px rtl:space-x-reverse text-sm h-8">
            <li v-for="(link, index) in paginationLinks" :key="index">
                <a
                    v-show="
                        !(link.label === 'Previous' && !link.url) &&
                        !(link.label === 'Next' && !link.url)
                    "
                    :href="link.url"
                    :class="
                        link.active
                            ? 'flex items-center justify-center px-3 h-8 text-blue-600 border border-gray-300 bg-blue-50 hover:bg-blue-100 hover:text-blue-700 dark:border-gray-700 dark:bg-gray-700 dark:text-white'
                            : 'flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white'
                    "
                >
                    <div v-html="link.label"></div>
                </a>
            </li>
        </ul>
    </nav>
</template>

<script setup>
import { computed } from "vue";
import { defineProps } from "vue";

const props = defineProps({
    paginationLinks: {
        type: Object,
        required: true,
    },
    meta: {
        type: Object,
        required: true,
    },
});

const MAX_LINKS = 3;

const paginationLinks = computed(() => {
    const links = props.paginationLinks;

    const prev = links[0];
    const next = links[links.length - 1];

    const firstPage = links[1];
    const lastPage = links[links.length - 2];

    // Ambil hanya link numerik berdasarkan label yang bisa dikonversi ke angka
    const numberLinks = links
        .slice(1, links.length - 1)
        .filter((link) => !isNaN(Number(link.label)));

    const activeIndex = numberLinks.findIndex((link) => link.active);

    let start = Math.max(0, activeIndex - Math.floor(MAX_LINKS / 2));
    let end = start + MAX_LINKS;

    if (end > numberLinks.length) {
        end = numberLinks.length;
        start = Math.max(0, end - MAX_LINKS);
    }

    const limited = numberLinks.slice(start, end);

    return [
        { ...firstPage, label: "First" },
        { ...prev, label: "Previous" },
        ...limited,
        { ...next, label: "Next" },
        { ...lastPage, label: "Last" },
    ];
});
</script>

<style></style>
