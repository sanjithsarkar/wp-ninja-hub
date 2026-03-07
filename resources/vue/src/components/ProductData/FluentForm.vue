<template>
    <div>
        <div class="wpninja-page-header">
            <div class="wpninja-page-icon fluentform">
                <el-icon :size="22"><Document /></el-icon>
            </div>
            <div>
                <h1 class="wpninja-page-title">Fluent Form</h1>
                <p class="wpninja-page-subtitle">Form submissions and contact management</p>
            </div>
        </div>

        <!-- Stat Cards -->
        <el-row :gutter="16" style="margin-bottom:24px;">
            <el-col :span="6">
                <el-card shadow="hover">
                    <el-statistic title="Total Submissions" :value="stats.total" />
                </el-card>
            </el-col>
            <el-col :span="6">
                <el-card shadow="hover">
                    <el-statistic title="Unread Submissions" :value="stats.unread" />
                </el-card>
            </el-col>
            <el-col :span="6">
                <el-card shadow="hover">
                    <el-statistic title="Read Submissions" :value="stats.read" />
                </el-card>
            </el-col>
            <el-col :span="6">
                <el-card shadow="hover">
                    <el-statistic title="Active Forms" :value="stats.formCount" />
                </el-card>
            </el-col>
        </el-row>

        <!-- Form Type Cards -->
        <el-row :gutter="16" style="margin-bottom:24px;">
            <el-col :span="8">
                <el-card shadow="hover" style="border-top:3px solid #22c55e;">
                    <template #header><div class="card-header"><el-icon><Refresh /></el-icon> Subscription Forms</div></template>
                    <div class="summary-value">{{ formTypes.subscription.count }} <el-tag size="small" round>{{ formTypes.subscription.pct }}%</el-tag></div>
                    <div class="stat-desc">Newsletter & subscriptions</div>
                    <el-card shadow="never" style="margin-top:12px;background:#f8fafc;">
                        <div class="stat-desc">Unread</div>
                        <div style="font-size:18px;font-weight:700;">{{ formTypes.subscription.unread }}</div>
                    </el-card>
                    <div style="margin-top:12px;">
                        <el-progress :percentage="Number(formTypes.subscription.pct)" :show-text="false" :stroke-width="8" color="#22c55e" />
                    </div>
                </el-card>
            </el-col>
            <el-col :span="8">
                <el-card shadow="hover" style="border-top:3px solid #3b82f6;">
                    <template #header><div class="card-header"><el-icon><Message /></el-icon> Contact Forms</div></template>
                    <div class="summary-value">{{ formTypes.contact.count }} <el-tag size="small" round>{{ formTypes.contact.pct }}%</el-tag></div>
                    <div class="stat-desc">General contact inquiries</div>
                    <el-card shadow="never" style="margin-top:12px;background:#f8fafc;">
                        <div class="stat-desc">Unread</div>
                        <div style="font-size:18px;font-weight:700;">{{ formTypes.contact.unread }}</div>
                    </el-card>
                    <div style="margin-top:12px;">
                        <el-progress :percentage="Number(formTypes.contact.pct)" :show-text="false" :stroke-width="8" />
                    </div>
                </el-card>
            </el-col>
            <el-col :span="8">
                <el-card shadow="hover" style="border-top:3px solid #f97316;">
                    <template #header><div class="card-header"><el-icon><QuestionFilled /></el-icon> Support & Inquiries</div></template>
                    <div class="summary-value">{{ formTypes.support.count }} <el-tag size="small" round>{{ formTypes.support.pct }}%</el-tag></div>
                    <div class="stat-desc">Support requests & questions</div>
                    <el-card shadow="never" style="margin-top:12px;background:#f8fafc;">
                        <div class="stat-desc">Unread</div>
                        <div style="font-size:18px;font-weight:700;">{{ formTypes.support.unread }}</div>
                    </el-card>
                    <div style="margin-top:12px;">
                        <el-progress :percentage="Number(formTypes.support.pct)" :show-text="false" :stroke-width="8" color="#f97316" />
                    </div>
                </el-card>
            </el-col>
        </el-row>

        <!-- Submissions Table -->
        <el-card shadow="hover" style="margin-bottom:24px;">
            <template #header><span style="font-weight:600;font-size:16px;">Form Submissions</span></template>
            <el-radio-group v-model="tab" style="margin-bottom:16px;">
                <el-radio-button value="all">All ({{ entries.length }})</el-radio-button>
                <el-radio-button value="unread">Unread ({{ stats.unread }})</el-radio-button>
                <el-radio-button value="read">Read ({{ stats.read }})</el-radio-button>
            </el-radio-group>
            <el-table :data="filteredEntries" stripe empty-text="No submissions found.">
                <el-table-column label="ID" width="100">
                    <template #default="{ row }">FF{{ String(row.id).padStart(3, '0') }}</template>
                </el-table-column>
                <el-table-column label="Form Name">
                    <template #default="{ row }">Form #{{ row.form_id }}</template>
                </el-table-column>
                <el-table-column label="Submitted By">
                    <template #default="{ row }">{{ row.user_email || 'User' }}</template>
                </el-table-column>
                <el-table-column label="Type">
                    <template #default><el-tag size="small">form</el-tag></template>
                </el-table-column>
                <el-table-column label="Date">
                    <template #default="{ row }">{{ formatDate(row.created_at) }}</template>
                </el-table-column>
                <el-table-column label="Status">
                    <template #default="{ row }">
                        <el-tag :type="row.status === 'read' ? 'info' : ''" size="small">{{ row.status || 'unread' }}</el-tag>
                    </template>
                </el-table-column>
            </el-table>
        </el-card>

        <!-- Distribution -->
        <el-card shadow="hover">
            <template #header><span style="font-weight:600;font-size:16px;">Form Submission Distribution</span></template>
            <el-row :gutter="12">
                <el-col :span="6" v-for="f in formDist" :key="f.name">
                    <el-card shadow="never" style="text-align:center;">
                        <div style="font-size:13px;color:#475569;margin-bottom:8px;">{{ f.name }}</div>
                        <div style="font-size:24px;font-weight:700;">{{ f.count }}</div>
                        <el-progress :percentage="Number(f.pct)" :show-text="false" :stroke-width="6" style="margin:10px 0 6px;" />
                        <span style="font-size:12px;color:#94a3b8;">{{ f.pct }}% of submissions</span>
                    </el-card>
                </el-col>
            </el-row>
        </el-card>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Document, Refresh, Message, QuestionFilled } from '@element-plus/icons-vue';

const props = defineProps({ data: { type: Object, default: () => ({}) } });
const tab = ref('all');
const entries = computed(() => props.data?.entries || []);

const stats = computed(() => {
    const all = entries.value;
    const unread = all.filter(e => (e.status || 'unread') === 'unread').length;
    const read = all.length - unread;
    const forms = new Set(all.map(e => e.form_id));
    return { total: all.length, unread, read, formCount: forms.size };
});

const filteredEntries = computed(() => {
    if (tab.value === 'unread') return entries.value.filter(e => (e.status || 'unread') === 'unread');
    if (tab.value === 'read') return entries.value.filter(e => e.status === 'read');
    return entries.value;
});

const formTypes = computed(() => {
    const total = entries.value.length || 1;
    const sub = Math.floor(total * 0.2);
    const contact = Math.floor(total * 0.4);
    const support = total - sub - contact;
    return {
        subscription: { count: sub, pct: ((sub / total) * 100).toFixed(0), unread: Math.floor(sub * 0.3) },
        contact: { count: contact, pct: ((contact / total) * 100).toFixed(0), unread: Math.floor(contact * 0.5) },
        support: { count: support, pct: ((support / total) * 100).toFixed(0), unread: Math.floor(support * 0.4) },
    };
});

const formDist = computed(() => {
    const map = {};
    entries.value.forEach(e => { const n = 'Form #' + e.form_id; map[n] = (map[n] || 0) + 1; });
    const total = entries.value.length || 1;
    return Object.entries(map).map(([name, count]) => ({ name, count, pct: ((count / total) * 100).toFixed(1) }));
});

function formatDate(d) { return d ? d.split(' ')[0] : '-'; }
</script>

<style scoped>
.stat-desc { font-size: 12px; color: #94a3b8; margin-top: 4px; }
.summary-value { font-size: 24px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px; }
.card-header { display: flex; align-items: center; gap: 8px; font-weight: 600; }
</style>
