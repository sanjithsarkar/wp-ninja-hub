<template>
    <div class="wpninja-content">
        <DashboardHome v-if="slug === 'dashboard'" />
        <ShortcodesPage v-else-if="slug === 'shortcodes'" />
        <template v-else-if="slug">
            <div v-if="loading" style="text-align:center;padding:60px 0;">
                <el-icon class="is-loading" :size="32"><Loading /></el-icon>
                <p style="color:#64748b;margin-top:12px;">Loading data...</p>
            </div>
            <el-alert v-else-if="error" :title="error" type="error" show-icon :closable="false" style="margin-bottom:20px;" />
            <template v-else>
                <Paymattic v-if="slug === 'paymattic'" :data="data" />
                <FluentForm v-else-if="slug === 'fluentform'" :data="data" />
                <FluentCrm v-else-if="slug === 'fluentcrm'" :data="data" />
                <FluentBoard v-else-if="slug === 'fluentboard'" :data="data" />
                <FluentCommunity v-else-if="slug === 'fluentcommunity'" :data="data" />
            </template>
        </template>
        <el-empty v-else description="Select a product from the sidebar." />
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { Loading } from '@element-plus/icons-vue';
import { getProductData } from '../api/dashboard.js';
import DashboardHome from './DashboardHome.vue';
import ShortcodesPage from './ShortcodesPage.vue';
import Paymattic from './ProductData/Paymattic.vue';
import FluentForm from './ProductData/FluentForm.vue';
import FluentCrm from './ProductData/FluentCrm.vue';
import FluentBoard from './ProductData/FluentBoard.vue';
import FluentCommunity from './ProductData/FluentCommunity.vue';

const props = defineProps({
    slug: { type: String, default: '' },
    label: { type: String, default: '' },
});

const data = ref(null);
const loading = ref(false);
const error = ref('');

const specialPages = ['dashboard', 'shortcodes'];

watch(() => props.slug, async (newSlug) => {
    if (!newSlug || specialPages.includes(newSlug)) return;
    loading.value = true;
    error.value = '';
    data.value = null;
    try {
        data.value = await getProductData(newSlug);
    } catch (e) {
        error.value = 'Failed to load data. Please try again.';
    } finally {
        loading.value = false;
    }
}, { immediate: true });
</script>
