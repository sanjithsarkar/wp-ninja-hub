<template>
    <div class="wpninja-dashboard">
        <Sidebar
            :items="menuItems"
            :active="activeSlug"
            :loading="menuLoading"
            @select="selectProduct"
        />
        <ContentArea
            :slug="activeSlug"
            :label="activeLabel"
        />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { getMenu } from './api/dashboard.js';
import Sidebar from './components/Sidebar.vue';
import ContentArea from './components/ContentArea.vue';

const menuItems = ref([]);
const activeSlug = ref('dashboard');
const menuLoading = ref(true);

const activeLabel = computed(() => {
    if (activeSlug.value === 'dashboard') return 'Dashboard';
    if (activeSlug.value === 'shortcodes') return 'Shortcodes';
    const item = menuItems.value.find(i => i.slug === activeSlug.value);
    return item ? item.label : '';
});

function selectProduct(slug) {
    activeSlug.value = slug;
}

onMounted(async () => {
    try {
        menuItems.value = await getMenu();
    } catch (e) {
        console.error('Failed to load dashboard menu:', e);
    } finally {
        menuLoading.value = false;
    }
});
</script>
