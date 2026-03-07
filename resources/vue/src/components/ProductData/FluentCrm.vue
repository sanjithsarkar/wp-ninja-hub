<template>
    <div>
        <div class="wpninja-page-header">
            <div class="wpninja-page-icon fluentcrm">
                <el-icon :size="22"><User /></el-icon>
            </div>
            <div>
                <h1 class="wpninja-page-title">Fluent CRM</h1>
                <p class="wpninja-page-subtitle">Customer relationship management and contacts</p>
            </div>
        </div>

        <!-- Stat Cards -->
        <el-row :gutter="16" style="margin-bottom:24px;">
            <el-col :span="6">
                <el-card shadow="hover">
                    <el-statistic title="Total Contacts" :value="contact ? 1 : 0" />
                </el-card>
            </el-col>
            <el-col :span="6">
                <el-card shadow="hover">
                    <el-statistic title="Active Contacts" :value="contact && contact.status === 'subscribed' ? 1 : 0" />
                </el-card>
            </el-col>
            <el-col :span="6">
                <el-card shadow="hover">
                    <el-statistic title="Subscribers" :value="contact && contact.status === 'subscribed' ? 1 : 0" />
                </el-card>
            </el-col>
            <el-col :span="6">
                <el-card shadow="hover">
                    <el-statistic title="Activities" :value="activities.length" />
                </el-card>
            </el-col>
        </el-row>

        <!-- Contact List -->
        <el-card shadow="hover" style="margin-bottom:24px;">
            <template #header><span style="font-weight:600;font-size:16px;">Contact List</span></template>
            <el-table v-if="contact" :data="[contact]" stripe>
                <el-table-column label="ID" width="100">
                    <template #default="{ row }">CRM{{ String(row.id).padStart(3, '0') }}</template>
                </el-table-column>
                <el-table-column label="Name">
                    <template #default="{ row }">{{ row.first_name }} {{ row.last_name }}</template>
                </el-table-column>
                <el-table-column prop="email" label="Email" />
                <el-table-column label="Last Activity">
                    <template #default="{ row }">{{ formatDate(row.created_at) }}</template>
                </el-table-column>
                <el-table-column label="Status">
                    <template #default="{ row }">
                        <el-tag :type="statusTagType(row.status)" size="small">{{ row.status }}</el-tag>
                    </template>
                </el-table-column>
            </el-table>
            <el-empty v-else description="No CRM contact found for your account." :image-size="60" />
        </el-card>

        <!-- Activities -->
        <el-card v-if="activities.length" shadow="hover" style="margin-bottom:24px;">
            <template #header><span style="font-weight:600;font-size:16px;">Activities</span></template>
            <el-table :data="activities" stripe>
                <el-table-column prop="title" label="Title" />
                <el-table-column label="Type">
                    <template #default="{ row }"><el-tag type="warning" size="small">{{ row.type }}</el-tag></template>
                </el-table-column>
                <el-table-column label="Date">
                    <template #default="{ row }">{{ formatDate(row.created_at) }}</template>
                </el-table-column>
            </el-table>
        </el-card>

        <!-- Status Distribution -->
        <el-card v-if="contact" shadow="hover">
            <template #header><span style="font-weight:600;font-size:16px;">Contact Status Distribution</span></template>
            <el-row :gutter="12">
                <el-col :span="6">
                    <el-card shadow="never" style="text-align:center;">
                        <div style="font-size:13px;color:#475569;margin-bottom:8px;">{{ contact.status }}</div>
                        <div style="font-size:24px;font-weight:700;">1</div>
                        <el-progress :percentage="100" :show-text="false" :stroke-width="6" color="#8b5cf6" style="margin:10px 0 6px;" />
                        <span style="font-size:12px;color:#94a3b8;">100% of contacts</span>
                    </el-card>
                </el-col>
            </el-row>
        </el-card>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { User } from '@element-plus/icons-vue';

const props = defineProps({ data: { type: Object, default: () => ({}) } });
const contact = computed(() => props.data?.contact || null);
const activities = computed(() => props.data?.activities || []);

function formatDate(d) { return d ? d.split(' ')[0] : '-'; }

function statusTagType(status) {
    const s = (status || '').toLowerCase();
    if (s === 'subscribed' || s === 'active') return 'success';
    if (s === 'unsubscribed') return 'danger';
    return 'info';
}
</script>
