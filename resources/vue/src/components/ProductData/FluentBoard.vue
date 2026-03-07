<template>
    <div>
        <div class="wpninja-page-header">
            <div class="wpninja-page-icon fluentboard">
                <el-icon :size="22"><DataBoard /></el-icon>
            </div>
            <div>
                <h1 class="wpninja-page-title">Fluent Board</h1>
                <p class="wpninja-page-subtitle">Project management and task tracking</p>
            </div>
        </div>

        <!-- Stat Cards -->
        <el-row :gutter="16" style="margin-bottom:24px;">
            <el-col :span="6">
                <el-card shadow="hover"><el-statistic title="Total Tasks" :value="tasks.length" /></el-card>
            </el-col>
            <el-col :span="6">
                <el-card shadow="hover"><el-statistic title="To Do" :value="todoTasks.length" /></el-card>
            </el-col>
            <el-col :span="6">
                <el-card shadow="hover"><el-statistic title="In Progress" :value="inProgressTasks.length" /></el-card>
            </el-col>
            <el-col :span="6">
                <el-card shadow="hover"><el-statistic title="Completed" :value="doneTasks.length" /></el-card>
            </el-col>
        </el-row>

        <!-- Kanban Board -->
        <el-row :gutter="16" style="margin-bottom:24px;">
            <el-col :span="8">
                <el-card shadow="hover" style="background:#f8fafc;min-height:200px;">
                    <template #header><div class="card-header"><el-icon><List /></el-icon> To Do ({{ todoTasks.length }})</div></template>
                    <div v-for="t in todoTasks" :key="t.id" class="kanban-card">
                        <div style="font-weight:600;font-size:13px;">{{ t.title }}</div>
                        <div style="display:flex;justify-content:space-between;margin-top:8px;">
                            <el-tag size="small" type="info">{{ t.priority || 'Normal' }}</el-tag>
                            <span style="font-size:12px;color:#94a3b8;">{{ formatDate(t.created_at) }}</span>
                        </div>
                    </div>
                    <el-empty v-if="todoTasks.length === 0" description="No tasks" :image-size="40" />
                </el-card>
            </el-col>
            <el-col :span="8">
                <el-card shadow="hover" style="background:#eff6ff;min-height:200px;">
                    <template #header><div class="card-header"><el-icon><Timer /></el-icon> In Progress ({{ inProgressTasks.length }})</div></template>
                    <div v-for="t in inProgressTasks" :key="t.id" class="kanban-card">
                        <div style="font-weight:600;font-size:13px;">{{ t.title }}</div>
                        <div style="display:flex;justify-content:space-between;margin-top:8px;">
                            <el-tag size="small" type="warning">{{ t.priority || 'Normal' }}</el-tag>
                            <span style="font-size:12px;color:#94a3b8;">{{ formatDate(t.created_at) }}</span>
                        </div>
                    </div>
                    <el-empty v-if="inProgressTasks.length === 0" description="No tasks" :image-size="40" />
                </el-card>
            </el-col>
            <el-col :span="8">
                <el-card shadow="hover" style="background:#f0fdf4;min-height:200px;">
                    <template #header><div class="card-header"><el-icon><CircleCheck /></el-icon> Done ({{ doneTasks.length }})</div></template>
                    <div v-for="t in doneTasks" :key="t.id" class="kanban-card">
                        <div style="font-weight:600;font-size:13px;">{{ t.title }}</div>
                        <div style="display:flex;justify-content:space-between;margin-top:8px;">
                            <el-tag size="small" type="success">{{ t.priority || 'Normal' }}</el-tag>
                            <span style="font-size:12px;color:#94a3b8;">{{ formatDate(t.created_at) }}</span>
                        </div>
                    </div>
                    <el-empty v-if="doneTasks.length === 0" description="No tasks" :image-size="40" />
                </el-card>
            </el-col>
        </el-row>

        <!-- All Tasks Table -->
        <el-card shadow="hover" style="margin-bottom:24px;">
            <template #header><span style="font-weight:600;font-size:16px;">All Tasks</span></template>
            <el-table :data="tasks" stripe empty-text="No tasks found.">
                <el-table-column label="Task ID" width="100">
                    <template #default="{ row }">FB{{ String(row.id).padStart(3, '0') }}</template>
                </el-table-column>
                <el-table-column prop="title" label="Title" />
                <el-table-column label="Board">
                    <template #default="{ row }"><el-tag size="small" type="info">Board #{{ row.board_id }}</el-tag></template>
                </el-table-column>
                <el-table-column label="Priority">
                    <template #default="{ row }">{{ row.priority || '-' }}</template>
                </el-table-column>
                <el-table-column label="Date">
                    <template #default="{ row }">{{ formatDate(row.created_at) }}</template>
                </el-table-column>
                <el-table-column label="Status">
                    <template #default="{ row }">
                        <el-tag :type="statusTagType(row.status)" size="small">{{ row.status || 'todo' }}</el-tag>
                    </template>
                </el-table-column>
            </el-table>
        </el-card>

        <!-- Tasks by Board -->
        <el-card shadow="hover">
            <template #header><span style="font-weight:600;font-size:16px;">Tasks by Board</span></template>
            <el-row :gutter="12">
                <el-col :span="6" v-for="b in boardDist" :key="b.name">
                    <el-card shadow="never" style="text-align:center;">
                        <div style="font-size:13px;color:#475569;margin-bottom:8px;">{{ b.name }}</div>
                        <div style="font-size:24px;font-weight:700;">{{ b.count }}</div>
                        <el-progress :percentage="Number(b.pct)" :show-text="false" :stroke-width="6" color="#ef4444" style="margin:10px 0 6px;" />
                        <span style="font-size:12px;color:#94a3b8;">{{ b.pct }}% of tasks</span>
                    </el-card>
                </el-col>
            </el-row>
        </el-card>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { DataBoard, List, Timer, CircleCheck } from '@element-plus/icons-vue';

const props = defineProps({ data: { type: Object, default: () => ({}) } });
const tasks = computed(() => props.data?.tasks || []);

const todoTasks = computed(() => tasks.value.filter(t => normalize(t.status) === 'todo'));
const inProgressTasks = computed(() => tasks.value.filter(t => ['in-progress', 'in_progress'].includes(normalize(t.status))));
const doneTasks = computed(() => tasks.value.filter(t => ['done', 'completed'].includes(normalize(t.status))));

const boardDist = computed(() => {
    const map = {};
    tasks.value.forEach(t => { const n = 'Board #' + t.board_id; map[n] = (map[n] || 0) + 1; });
    const total = tasks.value.length || 1;
    return Object.entries(map).map(([name, count]) => ({ name, count, pct: ((count / total) * 100).toFixed(1) }));
});

function normalize(s) { return (s || 'todo').toLowerCase().trim(); }
function formatDate(d) { return d ? d.split(' ')[0] : '-'; }

function statusTagType(status) {
    const s = normalize(status);
    if (s === 'done' || s === 'completed') return 'success';
    if (s === 'in-progress' || s === 'in_progress') return 'warning';
    return 'info';
}
</script>

<style scoped>
.card-header { display: flex; align-items: center; gap: 8px; font-weight: 600; }
.kanban-card { background: #fff; border-radius: 8px; padding: 12px; margin-bottom: 8px; border: 1px solid #e2e8f0; }
</style>
