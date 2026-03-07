<template>
    <div>
        <div class="wpninja-page-header">
            <div class="wpninja-page-icon fluentcommunity">
                <el-icon :size="22"><ChatDotRound /></el-icon>
            </div>
            <div>
                <h1 class="wpninja-page-title">Fluent Community</h1>
                <p class="wpninja-page-subtitle">Community engagement and discussion forums</p>
            </div>
        </div>

        <!-- Stat Cards -->
        <el-row :gutter="16" style="margin-bottom:24px;">
            <el-col :span="6">
                <el-card shadow="hover"><el-statistic title="Total Posts" :value="posts.length" /></el-card>
            </el-col>
            <el-col :span="6">
                <el-card shadow="hover"><el-statistic title="Total Likes" :value="totalLikes" /></el-card>
            </el-col>
            <el-col :span="6">
                <el-card shadow="hover"><el-statistic title="Total Comments" :value="comments.length" /></el-card>
            </el-col>
            <el-col :span="6">
                <el-card shadow="hover"><el-statistic title="Avg Engagement" :value="avgEngagement" /></el-card>
            </el-col>
        </el-row>

        <!-- Community Posts Table -->
        <el-card shadow="hover" style="margin-bottom:24px;">
            <template #header><span style="font-weight:600;font-size:16px;">Community Posts</span></template>
            <el-table :data="posts" stripe empty-text="No posts found.">
                <el-table-column label="Post ID" width="100">
                    <template #default="{ row }">FC{{ String(row.id).padStart(3, '0') }}</template>
                </el-table-column>
                <el-table-column label="Title">
                    <template #default="{ row }">{{ row.title || '(Untitled)' }}</template>
                </el-table-column>
                <el-table-column label="Status">
                    <template #default="{ row }">
                        <el-tag :type="row.status === 'published' ? 'success' : 'info'" size="small">{{ row.status || 'draft' }}</el-tag>
                    </template>
                </el-table-column>
                <el-table-column label="Comments">
                    <template #default="{ row }">{{ postCommentCount(row.id) }}</template>
                </el-table-column>
                <el-table-column label="Date">
                    <template #default="{ row }">{{ formatDate(row.created_at) }}</template>
                </el-table-column>
            </el-table>
        </el-card>

        <!-- Top Engaged Posts -->
        <el-card v-if="topPosts.length" shadow="hover" style="margin-bottom:24px;">
            <template #header><span style="font-weight:600;font-size:16px;">Top Engaged Posts</span></template>
            <div v-for="(p, i) in topPosts" :key="p.id" class="engaged-item">
                <el-avatar :size="36" style="background:#3b82f6;flex-shrink:0;">{{ i + 1 }}</el-avatar>
                <div style="flex:1;min-width:0;">
                    <div style="font-weight:600;font-size:14px;">{{ p.title || '(Untitled)' }}</div>
                    <div style="font-size:12px;color:#94a3b8;">{{ formatDate(p.created_at) }}</div>
                </div>
                <el-tag size="small" round>{{ postCommentCount(p.id) }} comments</el-tag>
            </div>
        </el-card>

        <!-- Your Comments -->
        <el-card v-if="comments.length" shadow="hover">
            <template #header><span style="font-weight:600;font-size:16px;">Your Comments</span></template>
            <el-table :data="comments" stripe>
                <el-table-column label="Post">
                    <template #default="{ row }">#{{ row.post_id }}</template>
                </el-table-column>
                <el-table-column label="Comment">
                    <template #default="{ row }">{{ truncate(row.message_rendered) }}</template>
                </el-table-column>
                <el-table-column label="Date">
                    <template #default="{ row }">{{ formatDate(row.created_at) }}</template>
                </el-table-column>
            </el-table>
        </el-card>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { ChatDotRound } from '@element-plus/icons-vue';

const props = defineProps({ data: { type: Object, default: () => ({}) } });
const posts = computed(() => props.data?.posts || []);
const comments = computed(() => props.data?.comments || []);
const totalLikes = computed(() => posts.value.reduce((a, p) => a + (parseInt(p.likes) || 0), 0));
const avgEngagement = computed(() => posts.value.length ? (comments.value.length / posts.value.length).toFixed(1) : '0');

const commentMap = computed(() => {
    const map = {};
    comments.value.forEach(c => { map[c.post_id] = (map[c.post_id] || 0) + 1; });
    return map;
});

function postCommentCount(postId) { return commentMap.value[postId] || 0; }

const topPosts = computed(() =>
    [...posts.value].sort((a, b) => postCommentCount(b.id) - postCommentCount(a.id)).slice(0, 3)
);

function formatDate(d) { return d ? d.split(' ')[0] : '-'; }
function truncate(text, len = 80) {
    if (!text) return '-';
    const stripped = text.replace(/<[^>]+>/g, '');
    return stripped.length > len ? stripped.substring(0, len) + '...' : stripped;
}
</script>

<style scoped>
.engaged-item { display: flex; align-items: center; gap: 14px; padding: 12px; border-radius: 8px; background: #f8fafc; margin-bottom: 8px; }
</style>
