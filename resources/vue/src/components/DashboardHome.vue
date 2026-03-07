<template>
    <div>
        <div class="wpninja-page-header">
            <div>
                <h1 class="wpninja-page-title">Plugin Dashboard</h1>
                <p class="wpninja-page-subtitle">Overview of all WPManageNinja plugins</p>
            </div>
        </div>

        <!-- Stat Cards -->
        <div class="dash-stats-row">
            <div class="dash-stat-card" v-for="s in statCards" :key="s.label">
                <div class="dash-stat-header">
                    <span class="dash-stat-label">{{ s.label }}</span>
                    <span class="dash-stat-icon" :style="{ color: s.iconColor }">
                        <el-icon :size="18"><component :is="s.icon" /></el-icon>
                    </span>
                </div>
                <div class="dash-stat-value">
                    {{ s.prefix }}{{ s.value }}
                    <span v-if="s.extra" class="dash-stat-extra">{{ s.extra }}</span>
                </div>
                <div class="dash-stat-trend" :class="s.trendUp ? 'up' : 'down'">
                    {{ s.trendUp ? '↗' : '↘' }} {{ s.trend }} vs last month
                </div>
            </div>
        </div>

        <!-- Recent Payments + Recent Form Submissions -->
        <div class="dash-grid-2">
            <div class="dash-panel">
                <div class="dash-panel-header">
                    <el-icon :size="18"><CreditCard /></el-icon>
                    <span>Recent Payments</span>
                </div>
                <div v-if="payments.length === 0" class="dash-empty">No payment data</div>
                <div v-for="p in payments" :key="p.id" class="dash-list-item">
                    <div>
                        <div class="dash-list-title">{{ p.customer_name || 'Customer' }}</div>
                        <div class="dash-list-meta">{{ p.payment_method || 'N/A' }} &middot; {{ formatDate(p.created_at) }}</div>
                    </div>
                    <div class="dash-list-right">
                        <div class="dash-list-amount">${{ formatAmt(p.payment_total) }}</div>
                        <span class="wpninja-badge" :class="payBadge(p.payment_status)">{{ p.payment_status || 'pending' }}</span>
                    </div>
                </div>
            </div>

            <div class="dash-panel">
                <div class="dash-panel-header">
                    <el-icon :size="18"><Document /></el-icon>
                    <span>Recent Form Submissions</span>
                </div>
                <div v-if="formEntries.length === 0" class="dash-empty">No form submissions</div>
                <div v-for="e in formEntries" :key="e.id" class="dash-list-item">
                    <div>
                        <div class="dash-list-title">Form #{{ e.form_id }}</div>
                        <div class="dash-list-meta">{{ e.user_email || 'User' }}</div>
                    </div>
                    <div class="dash-list-right">
                        <div class="dash-list-date">{{ formatDate(e.created_at) }}</div>
                        <span class="wpninja-badge" :class="e.status === 'read' ? 'gray' : 'dark'">{{ e.status || 'unread' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Board Tasks + Community Activity -->
        <div class="dash-grid-2">
            <div class="dash-panel">
                <div class="dash-panel-header">
                    <el-icon :size="18"><DataBoard /></el-icon>
                    <span>Active Board Tasks</span>
                </div>
                <div v-if="boardTasks.length === 0" class="dash-empty">No tasks</div>
                <div v-for="t in boardTasks" :key="t.id" class="dash-list-item">
                    <div>
                        <div class="dash-list-title">{{ t.title }}</div>
                        <div class="dash-list-meta">Board #{{ t.board_id }}</div>
                    </div>
                    <div class="dash-list-right">
                        <div class="dash-list-date">{{ formatDate(t.created_at) }}</div>
                        <span class="wpninja-badge" :class="taskBadge(t.status)">{{ t.status || 'todo' }}</span>
                    </div>
                </div>
            </div>

            <div class="dash-panel">
                <div class="dash-panel-header">
                    <el-icon :size="18"><ChatDotRound /></el-icon>
                    <span>Community Activity</span>
                </div>
                <div v-if="communityPosts.length === 0" class="dash-empty">No community posts</div>
                <div v-for="p in communityPosts" :key="p.id" class="dash-list-item">
                    <div>
                        <div class="dash-list-title">{{ p.title || '(Untitled)' }}</div>
                        <div class="dash-list-meta">{{ formatDate(p.created_at) }}</div>
                    </div>
                    <div class="dash-list-right">
                        <span v-if="p.status" class="wpninja-badge blue">{{ p.status }}</span>
                        <span class="dash-list-meta" style="margin-left:8px;">
                            👍 {{ p.reactions_count || 0 }} &nbsp; 💬 {{ p.comments_count || 0 }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Plugin Overview -->
        <div class="dash-panel">
            <div class="dash-panel-header">
                <el-icon :size="18"><Connection /></el-icon>
                <span>Plugin Overview</span>
            </div>
            <div class="dash-overview-grid">
                <div v-for="o in overview" :key="o.name" class="dash-overview-card">
                    <div class="dash-overview-name">{{ o.name }}</div>
                    <div class="dash-overview-value">{{ o.count }}</div>
                    <div class="dash-overview-label">{{ o.label }}</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { CreditCard, Document, DataBoard, ChatDotRound, Connection, User, Postcard } from '@element-plus/icons-vue';
import { getMenu, getProductData } from '../api/dashboard.js';

const plugins = ref([]);
const paymatticData = ref(null);
const fluentFormData = ref(null);
const fluentCrmData = ref(null);
const fluentBoardData = ref(null);
const fluentCommunityData = ref(null);

onMounted(async () => {
    try {
        plugins.value = await getMenu();
        const slugs = plugins.value.map(p => p.slug);
        const fetches = slugs.map(async (slug) => {
            try { return { slug, data: await getProductData(slug) }; }
            catch { return { slug, data: null }; }
        });
        const results = await Promise.all(fetches);
        results.forEach(({ slug, data }) => {
            if (slug === 'paymattic') paymatticData.value = data;
            if (slug === 'fluentform') fluentFormData.value = data;
            if (slug === 'fluentcrm') fluentCrmData.value = data;
            if (slug === 'fluentboard') fluentBoardData.value = data;
            if (slug === 'fluentcommunity') fluentCommunityData.value = data;
        });
    } catch (e) { console.error(e); }
});

// Stat cards
const payments = computed(() => (paymatticData.value?.submissions || []).slice(0, 4));
const formEntries = computed(() => (fluentFormData.value?.entries || []).slice(0, 4));
const boardTasks = computed(() => (fluentBoardData.value?.tasks || []).slice(0, 4));
const communityPosts = computed(() => (fluentCommunityData.value?.posts || []).slice(0, 4));

const totalRevenue = computed(() => {
    const txns = paymatticData.value?.transactions || [];
    return txns.filter(t => ['paid', 'completed'].includes((t.status || '').toLowerCase()))
        .reduce((a, t) => a + ((parseInt(t.payment_total) || 0) / 100), 0);
});

const formCount = computed(() => (fluentFormData.value?.entries || []).length);
const unreadForms = computed(() => (fluentFormData.value?.entries || []).filter(e => (e.status || 'unread') === 'unread').length);
const contactCount = computed(() => (fluentCrmData.value?.contact ? 1 : 0));
const taskCount = computed(() => (fluentBoardData.value?.tasks || []).length);
const postCount = computed(() => (fluentCommunityData.value?.posts || []).length);

const statCards = computed(() => [
    { label: 'Total Revenue', icon: CreditCard, iconColor: '#3b82f6', prefix: '$', value: totalRevenue.value.toFixed(2), trend: '+12.5%', trendUp: true },
    { label: 'Form Submissions', icon: Document, iconColor: '#3b82f6', prefix: '', value: formCount.value, extra: unreadForms.value + ' unread', trend: '+8.2%', trendUp: true },
    { label: 'Active Contacts', icon: User, iconColor: '#8b5cf6', prefix: '', value: contactCount.value, trend: '+5.1%', trendUp: true },
    { label: 'Active Tasks', icon: DataBoard, iconColor: '#ea580c', prefix: '', value: taskCount.value, trend: '-2.3%', trendUp: false },
    { label: 'Community Posts', icon: ChatDotRound, iconColor: '#3b82f6', prefix: '', value: postCount.value, trend: '+15.8%', trendUp: true },
]);

const overview = computed(() => [
    { name: 'Paymattic', count: (paymatticData.value?.transactions || []).length, label: 'transactions' },
    { name: 'Fluent Form', count: formCount.value, label: 'submissions' },
    { name: 'Fluent CRM', count: contactCount.value, label: 'contacts' },
    { name: 'Fluent Board', count: taskCount.value, label: 'tasks' },
    { name: 'Fluent Community', count: postCount.value, label: 'posts' },
]);

function formatDate(d) { return d ? d.split(' ')[0] : '-'; }
function formatAmt(v) { return ((parseInt(v) || 0) / 100).toFixed(2); }

function payBadge(s) {
    const v = (s || '').toLowerCase();
    if (v === 'paid' || v === 'completed') return 'green';
    if (v === 'pending') return 'orange';
    return 'gray';
}

function taskBadge(s) {
    const v = (s || '').toLowerCase();
    if (v === 'done' || v === 'completed') return 'green';
    if (v === 'in-progress' || v === 'in_progress') return 'blue';
    return 'dark';
}
</script>

<style scoped>
.dash-stats-row {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.dash-stat-card {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    border: 1px solid #e2e8f0;
}

.dash-stat-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}

.dash-stat-label {
    font-size: 13px;
    color: #64748b;
    font-weight: 500;
}

.dash-stat-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: center;
}

.dash-stat-value {
    font-size: 26px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.2;
    display: flex;
    align-items: baseline;
    gap: 8px;
}

.dash-stat-extra {
    font-size: 12px;
    font-weight: 500;
    color: #64748b;
    background: #f1f5f9;
    padding: 2px 8px;
    border-radius: 10px;
}

.dash-stat-trend {
    font-size: 12px;
    margin-top: 6px;
    font-weight: 500;
}

.dash-stat-trend.up { color: #22c55e; }
.dash-stat-trend.down { color: #ef4444; }

.dash-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 24px;
}

.dash-panel {
    background: #fff;
    border-radius: 12px;
    padding: 24px;
    border: 1px solid #e2e8f0;
}

.dash-panel-header {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 16px;
    font-weight: 600;
    color: #0f172a;
    margin-bottom: 20px;
}

.dash-list-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 0;
    border-bottom: 1px solid #f1f5f9;
}

.dash-list-item:last-child { border-bottom: none; }

.dash-list-title {
    font-size: 14px;
    font-weight: 600;
    color: #0f172a;
}

.dash-list-meta {
    font-size: 12px;
    color: #94a3b8;
    margin-top: 2px;
}

.dash-list-right {
    text-align: right;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
}

.dash-list-amount {
    font-size: 15px;
    font-weight: 700;
    color: #0f172a;
}

.dash-list-date {
    font-size: 13px;
    color: #64748b;
}

.dash-empty {
    text-align: center;
    color: #94a3b8;
    padding: 20px 0;
    font-size: 13px;
}

.dash-overview-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 12px;
}

.dash-overview-card {
    background: #f8fafc;
    border-radius: 10px;
    padding: 16px;
    border: 1px solid #e2e8f0;
}

.dash-overview-name {
    font-size: 13px;
    color: #475569;
    font-weight: 500;
    margin-bottom: 8px;
}

.dash-overview-value {
    font-size: 28px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.2;
}

.dash-overview-label {
    font-size: 12px;
    color: #94a3b8;
    margin-top: 2px;
}

@media (max-width: 1024px) {
    .dash-stats-row { grid-template-columns: repeat(3, 1fr); }
    .dash-grid-2 { grid-template-columns: 1fr; }
    .dash-overview-grid { grid-template-columns: repeat(3, 1fr); }
}

@media (max-width: 768px) {
    .dash-stats-row { grid-template-columns: repeat(2, 1fr); }
    .dash-overview-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>
