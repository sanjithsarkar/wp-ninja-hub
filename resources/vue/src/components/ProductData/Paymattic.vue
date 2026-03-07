<template>
    <div>
        <!-- Header -->
        <div class="wpninja-page-header">
            <div class="wpninja-page-icon paymattic">
                <el-icon :size="22"><CreditCard /></el-icon>
            </div>
            <div>
                <h1 class="wpninja-page-title">Paymattic</h1>
                <p class="wpninja-page-subtitle">Payment processing and transaction management</p>
            </div>
        </div>

        <!-- Stat Cards -->
        <el-row :gutter="16" style="margin-bottom:24px;">
            <el-col :span="6">
                <el-card shadow="hover">
                    <el-statistic title="Total Revenue" :value="stats.totalRevenue" prefix="$" />
                    <div class="stat-desc">From {{ stats.completedCount }} completed payments</div>
                </el-card>
            </el-col>
            <el-col :span="6">
                <el-card shadow="hover">
                    <el-statistic title="Pending Payments" :value="stats.pendingAmount" prefix="$" />
                    <div class="stat-desc">Awaiting confirmation</div>
                </el-card>
            </el-col>
            <el-col :span="6">
                <el-card shadow="hover">
                    <el-statistic title="Total Transactions" :value="stats.totalCount" />
                    <div class="stat-desc">All time</div>
                </el-card>
            </el-col>
            <el-col :span="6">
                <el-card shadow="hover">
                    <el-statistic title="Success Rate" :value="stats.successRate" suffix="%" />
                    <div class="stat-desc">Completed payments</div>
                </el-card>
            </el-col>
        </el-row>

        <!-- Subscription vs One-Time -->
        <el-row :gutter="16" style="margin-bottom:24px;">
            <el-col :span="12">
                <el-card shadow="hover" class="summary-card border-purple">
                    <template #header>
                        <div class="card-header">
                            <el-icon><Refresh /></el-icon>
                            <span>Subscription Payments</span>
                        </div>
                    </template>
                    <div class="summary-value">
                        ${{ stats.subTotal }}
                        <el-tag size="small" type="info" round>{{ stats.subCount }} payments</el-tag>
                    </div>
                    <div class="stat-desc">Recurring revenue</div>
                    <el-descriptions :column="1" border size="small" style="margin-top:16px;">
                        <el-descriptions-item label="Monthly Subscriptions">
                            <el-tag type="success" round size="small">{{ stats.monthlyCount }}</el-tag>
                            ${{ stats.monthlyTotal }}
                        </el-descriptions-item>
                        <el-descriptions-item label="Yearly Subscriptions">
                            <el-tag type="success" round size="small">{{ stats.yearlyCount }}</el-tag>
                            ${{ stats.yearlyTotal }}
                        </el-descriptions-item>
                    </el-descriptions>
                    <div style="margin-top:16px;">
                        <div class="progress-label">
                            <span>Of total revenue</span>
                            <span>{{ stats.subPct }}%</span>
                        </div>
                        <el-progress :percentage="Number(stats.subPct)" :show-text="false" :stroke-width="8" />
                    </div>
                </el-card>
            </el-col>
            <el-col :span="12">
                <el-card shadow="hover" class="summary-card border-blue">
                    <template #header>
                        <div class="card-header">
                            <el-icon><Coin /></el-icon>
                            <span>One-Time Payments</span>
                        </div>
                    </template>
                    <div class="summary-value">
                        ${{ stats.oneTimeTotal }}
                        <el-tag size="small" type="info" round>{{ stats.oneTimeCount }} payments</el-tag>
                    </div>
                    <div class="stat-desc">Single transactions</div>
                    <el-card shadow="never" style="margin-top:16px;background:#f8fafc;">
                        <div class="stat-desc">Average Transaction</div>
                        <div style="font-size:20px;font-weight:700;color:#0f172a;">${{ stats.oneTimeAvg }}</div>
                        <div class="stat-desc">Per one-time payment</div>
                    </el-card>
                    <div style="margin-top:16px;">
                        <div class="progress-label">
                            <span>Of total revenue</span>
                            <span>{{ stats.oneTimePct }}%</span>
                        </div>
                        <el-progress :percentage="Number(stats.oneTimePct)" :show-text="false" :stroke-width="8" />
                    </div>
                </el-card>
            </el-col>
        </el-row>

        <!-- Transactions Table -->
        <el-card shadow="hover" class="transactions-card">
            <template #header>
                <div class="transactions-header">
                    <span class="transactions-title">Transactions</span>
                </div>
            </template>
            <el-radio-group v-model="tab" class="transactions-tabs">
                <el-radio-button value="all">All ({{ transactions.length }})</el-radio-button>
                <el-radio-button value="subscription">Subscriptions ({{ subTxns.length }})</el-radio-button>
                <el-radio-button value="onetime">One-Time ({{ oneTimeTxns.length }})</el-radio-button>
            </el-radio-group>
            <div class="transactions-toolbar">
                <div class="toolbar-filters">
                    <el-radio-group v-model="timeFilter" size="default" class="time-filters">
                        <el-radio-button value="all">All</el-radio-button>
                        <el-radio-button value="7d">Last 7 Days</el-radio-button>
                        <el-radio-button value="30d">Last 30 Days</el-radio-button>
                    </el-radio-group>
                </div>
                <div class="toolbar-actions">
                    <el-input
                        v-model="searchQuery"
                        placeholder="Search transactions..."
                        clearable
                        class="search-input"
                        :prefix-icon="Search"
                    />
                    <el-button type="primary" :icon="Download" @click="handleExport">Export</el-button>
                </div>
            </div>
            <el-table
                :data="paginatedTxns"
                stripe
                class="transactions-table"
                empty-text="No transactions found."
                @sort-change="handleSortChange"
            >
                <el-table-column label="ID" width="100" prop="id" sortable="custom">
                    <template #default="{ row }">PM{{ String(row.id).padStart(3, '0') }}</template>
                </el-table-column>
                <el-table-column label="Form" min-width="140">
                    <template #default="{ row }">{{ formName(row.form_id) }}</template>
                </el-table-column>
                <el-table-column label="Amount" width="110" prop="payment_total" sortable="custom">
                    <template #default="{ row }">${{ formatAmt(row.payment_total) }}</template>
                </el-table-column>
                <el-table-column label="Type" width="140">
                    <template #default="{ row }">
                        <span :class="['type-badge', 'type-badge--' + typeBadgeVariant(row)]">
                            <el-icon v-if="(row.transaction_type || '').toLowerCase() === 'subscription'" class="type-badge-icon"><Refresh /></el-icon>
                            <el-icon v-else-if="(row.transaction_type || '').toLowerCase() === 'refund'" class="type-badge-icon"><RefreshRight /></el-icon>
                            <el-icon v-else class="type-badge-icon"><Lightning /></el-icon>
                            <span class="type-badge-text">{{ typeBadgeLabel(row) }}</span>
                        </span>
                    </template>
                </el-table-column>
                <el-table-column prop="payment_method" label="Payment Method" min-width="130">
                    <template #default="{ row }">{{ row.payment_method || '-' }}</template>
                </el-table-column>
                <el-table-column label="Date" width="120" prop="created_at" sortable="custom">
                    <template #default="{ row }">{{ formatDate(row.created_at) }}</template>
                </el-table-column>
                <el-table-column label="Status" width="120">
                    <template #default="{ row }">
                        <el-tag :type="statusTagType(row.status)" size="small" round :class="'status-tag status-tag--' + (row.status || 'pending').toLowerCase().replace(/\s+/g, '-')">
                            {{ row.status || 'pending' }}
                        </el-tag>
                    </template>
                </el-table-column>
            </el-table>
            <div class="transactions-pagination">
                <el-pagination
                    v-model:current-page="currentPage"
                    v-model:page-size="pageSize"
                    :page-sizes="[10, 25, 50, 100]"
                    :total="totalFiltered"
                    layout="total, sizes, prev, pager, next, jumper"
                    background
                    @size-change="handleSizeChange"
                    @current-change="handleCurrentChange"
                />
            </div>
        </el-card>

        <!-- Payment Methods Distribution -->
        <el-card shadow="hover">
            <template #header>
                <span style="font-weight:600;font-size:16px;">Payment Methods Distribution</span>
            </template>
            <el-row :gutter="12">
                <el-col :span="6" v-for="m in paymentMethods" :key="m.name">
                    <el-card shadow="never" style="text-align:center;">
                        <div style="font-size:13px;color:#475569;margin-bottom:8px;">{{ m.name }}</div>
                        <div style="font-size:24px;font-weight:700;color:#0f172a;">{{ m.count }}</div>
                        <el-progress :percentage="Number(m.pct)" :show-text="false" :stroke-width="6" :color="m.color" style="margin:10px 0 6px;" />
                        <span style="font-size:12px;color:#94a3b8;">{{ m.pct }}% of total</span>
                    </el-card>
                </el-col>
            </el-row>
        </el-card>
    </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { CreditCard, Refresh, Coin, Search, Download, RefreshRight, Lightning } from '@element-plus/icons-vue';

const props = defineProps({ data: { type: Object, default: () => ({}) } });
const tab = ref('all');
const timeFilter = ref('all');
const searchQuery = ref('');
const currentPage = ref(1);
const pageSize = ref(10);
const sortProp = ref('created_at');
const sortOrder = ref('descending');

const submissions = computed(() => props.data?.submissions || []);
const transactions = computed(() => props.data?.transactions || []);
const subscriptions = computed(() => props.data?.subscriptions || []);
const formNamesMap = computed(() => props.data?.formNames || {});

const subTxns = computed(() => transactions.value.filter(t => t.transaction_type === 'subscription'));
const oneTimeTxns = computed(() => transactions.value.filter(t => t.transaction_type !== 'subscription' && t.transaction_type !== 'refund'));

const filteredTxns = computed(() => {
    let list = tab.value === 'subscription' ? subTxns.value : tab.value === 'onetime' ? oneTimeTxns.value : transactions.value;
    if (timeFilter.value === '7d') list = filterByDays(list, 7);
    else if (timeFilter.value === '30d') list = filterByDays(list, 30);
    const q = (searchQuery.value || '').trim().toLowerCase();
    if (q) {
        list = list.filter(t => {
            const id = String(t.id || '').toLowerCase();
            const form = (formName(t.form_id) || '').toLowerCase();
            const method = (t.payment_method || '').toLowerCase();
            const status = (t.status || '').toLowerCase();
            return id.includes(q) || form.includes(q) || method.includes(q) || status.includes(q);
        });
    }
    const arr = [...list];
    const prop = sortProp.value;
    const order = sortOrder.value;
    arr.sort((a, b) => {
        let va = a[prop];
        let vb = b[prop];
        if (prop === 'created_at' || prop === 'payment_total') {
            va = prop === 'payment_total' ? toNum(va) : new Date(va || 0).getTime();
            vb = prop === 'payment_total' ? toNum(vb) : new Date(vb || 0).getTime();
        }
        if (va === vb) return 0;
        const gt = va > vb ? 1 : -1;
        return order === 'ascending' ? gt : -gt;
    });
    return arr;
});

function filterByDays(list, days) {
    const cut = new Date();
    cut.setDate(cut.getDate() - days);
    return list.filter(t => new Date(t.created_at || 0) >= cut);
}

const totalFiltered = computed(() => filteredTxns.value.length);
const paginatedTxns = computed(() => {
    const list = filteredTxns.value;
    const start = (currentPage.value - 1) * pageSize.value;
    return list.slice(start, start + pageSize.value);
});

watch([tab, timeFilter, searchQuery], () => { currentPage.value = 1; });

const subIds = computed(() => new Set(subscriptions.value.map(s => s.submission_id)));
const oneTimeRows = computed(() => submissions.value.filter(s => !subIds.value.has(s.id)));

const stats = computed(() => {
    const txns = transactions.value.filter(t => t.transaction_type !== 'refund');
    const completed = txns.filter(t => ['paid', 'completed'].includes((t.status || '').toLowerCase()));
    const pending = txns.filter(t => (t.status || '').toLowerCase() === 'pending');
    const completedAmt = completed.reduce((a, t) => a + toNum(t.payment_total), 0);
    const pendingAmt = pending.reduce((a, t) => a + toNum(t.payment_total), 0);
    const subAmt = subscriptions.value.reduce((a, s) => a + toNum(s.payment_total), 0);
    const otAmt = oneTimeRows.value.reduce((a, s) => a + toNum(s.payment_total), 0);
    const monthly = subscriptions.value.filter(s => (s.billing_interval || '').toLowerCase() === 'monthly');
    const yearly = subscriptions.value.filter(s => (s.billing_interval || '').toLowerCase() === 'yearly');
    const grandTotal = subAmt + otAmt;

    return {
        totalRevenue: fmt(completedAmt),
        pendingAmount: fmt(pendingAmt),
        totalCount: txns.length,
        completedCount: completed.length,
        successRate: txns.length ? ((completed.length / txns.length) * 100).toFixed(1) : '0.0',
        subTotal: fmt(subAmt),
        subCount: subscriptions.value.length,
        subPct: grandTotal ? ((subAmt / grandTotal) * 100).toFixed(1) : '0.0',
        oneTimeTotal: fmt(otAmt),
        oneTimeCount: oneTimeRows.value.length,
        oneTimePct: grandTotal ? ((otAmt / grandTotal) * 100).toFixed(1) : '0.0',
        oneTimeAvg: oneTimeRows.value.length ? fmt(otAmt / oneTimeRows.value.length) : '0.00',
        monthlyCount: monthly.length,
        monthlyTotal: fmt(monthly.reduce((a, s) => a + toNum(s.payment_total), 0)),
        yearlyCount: yearly.length,
        yearlyTotal: fmt(yearly.reduce((a, s) => a + toNum(s.payment_total), 0)),
    };
});

const paymentMethods = computed(() => {
    const map = {};
    transactions.value.forEach(t => {
        const m = t.payment_method || 'Unknown';
        map[m] = (map[m] || 0) + 1;
    });
    const total = transactions.value.length || 1;
    const colors = ['#3b82f6', '#6366f1', '#8b5cf6', '#ec4899', '#f97316'];
    return Object.entries(map).map(([name, count], i) => ({
        name, count,
        pct: ((count / total) * 100).toFixed(1),
        color: colors[i % colors.length],
    }));
});

function toNum(v) { return (parseInt(v) || 0) / 100; }
function fmt(n) { return n.toFixed(2); }
function formatAmt(v) { return fmt(toNum(v)); }
function formatDate(d) { return d ? d.split(' ')[0] : '-'; }
function formName(id) { return formNamesMap.value[id] || (id ? 'Form #' + id : '-'); }

function txnTagType(type) {
    const t = (type || 'one_time').toLowerCase();
    if (t === 'subscription') return '';
    if (t === 'refund') return 'danger';
    return 'warning';
}

function typeBadgeVariant(row) {
    const t = (row.transaction_type || '').toLowerCase();
    if (t === 'subscription') return 'sub';
    if (t === 'refund') return 'refund';
    return 'onetime';
}

function typeBadgeLabel(row) {
    const t = (row.transaction_type || '').toLowerCase();
    if (t === 'refund') return 'refund';
    if (t === 'subscription') {
        const interval = (row.billing_interval || '').toLowerCase();
        if (interval === 'monthly') return 'monthly';
        if (interval === 'yearly') return 'yearly';
        return 'subscription';
    }
    return 'one-time';
}

function statusTagType(status) {
    const s = (status || '').toLowerCase();
    if (s === 'paid' || s === 'completed') return 'success';
    if (s === 'pending') return 'info';
    if (s === 'intented') return 'warning';
    if (s === 'failed' || s === 'refunded') return 'danger';
    return 'warning';
}

function handleSortChange({ prop, order }) {
    sortProp.value = prop || 'created_at';
    sortOrder.value = order || 'descending';
}

function handleSizeChange(size) {
    pageSize.value = size;
    currentPage.value = 1;
}

function handleCurrentChange(page) {
    currentPage.value = page;
}

function handleExport() {
    console.log('Export transactions');
}
</script>

<style scoped>
.stat-desc { font-size: 12px; color: #94a3b8; margin-top: 4px; }
.summary-value { font-size: 28px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px; }
.summary-card.border-purple { border-top: 3px solid #8b5cf6; }
.summary-card.border-blue { border-top: 3px solid #3b82f6; }
.card-header { display: flex; align-items: center; gap: 8px; font-weight: 600; }
.progress-label { display: flex; justify-content: space-between; font-size: 12px; color: #64748b; margin-bottom: 6px; }

.transactions-card { margin-bottom: 24px; }
.transactions-header { display: flex; align-items: center; justify-content: space-between; }
.transactions-title { font-weight: 600; font-size: 16px; color: #0f172a; }
.transactions-tabs { margin-bottom: 16px; }
.transactions-tabs :deep(.el-radio-button) { margin-right: 8px; }
.transactions-tabs :deep(.el-radio-button:last-child) { margin-right: 0; }
.transactions-toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 16px;
    padding: 12px 0;
    border-bottom: 1px solid #f1f5f9;
}
.toolbar-filters .time-filters { flex-wrap: wrap; }
.toolbar-filters :deep(.el-radio-button) { margin-right: 8px; }
.toolbar-filters :deep(.el-radio-button:last-child) { margin-right: 0; }
.toolbar-actions {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-shrink: 0;
}
.search-input { width: 260px; }
.transactions-table { width: 100%; }
.transactions-pagination {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid #f1f5f9;
    display: flex;
    justify-content: flex-end;
}

/* Type column: single badge with icon + text (white on colored background) */
.type-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
}
.type-badge-icon { font-size: 14px; color: #fff; }
.type-badge-text { color: #fff; }
.type-badge--sub {
    background: #6366f1;
    color: #fff;
}
.type-badge--sub .type-badge-icon,
.type-badge--sub .type-badge-text { color: #fff; }
.type-badge--onetime {
    background: #3b82f6;
    color: #fff;
}
.type-badge--onetime .type-badge-icon,
.type-badge--onetime .type-badge-text { color: #fff; }
.type-badge--refund {
    background: #64748b;
    color: #fff;
}
.type-badge--refund .type-badge-icon,
.type-badge--refund .type-badge-text { color: #fff; }

/* Status tags: ensure visible background colors */
.status-tag--pending { background: #f1f5f9 !important; color: #475569 !important; }
.status-tag--intented { background: #fef3c7 !important; color: #b45309 !important; }
.status-tag--paid,
.status-tag--completed { background: #dcfce7 !important; color: #15803d !important; }
.status-tag--failed { background: #fee2e2 !important; color: #dc2626 !important; }
.status-tag--refunded { background: #fef3c7 !important; color: #b45309 !important; }
</style>
