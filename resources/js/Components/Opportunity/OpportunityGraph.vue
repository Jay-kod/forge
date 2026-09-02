<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
    project: {
        type: Object,
        required: true,
    },
})

const loading = ref(true)
const graphData = ref({ nodes: [], edges: [], metrics: {} })
const selectedNode = ref(null)
const filter = ref('all')
const zoom = ref(1)
const panX = ref(0)
const panY = ref(0)

const fetchGraph = async () => {
    loading.value = true
    try {
        const res = await axios.get(`/projects/${props.project.id}/graph`)
        graphData.value = res.data
        // Select root project by default
        if (res.data.nodes?.length > 0) {
            selectedNode.value = res.data.nodes[0]
        }
    } catch (err) {
        console.error('Failed to load opportunity graph', err)
    } finally {
        loading.value = false
    }
}

const filteredNodes = computed(() => {
    if (filter.value === 'all') return graphData.value.nodes
    if (filter.value === 'opportunities') {
        return graphData.value.nodes.filter(n => ['quick_wins', 'major_projects', 'fill_ins', 'thankless_tasks'].includes(n.category))
    }
    return graphData.value.nodes.filter(n => n.category === filter.value || n.type === filter.value)
})

const filteredEdges = computed(() => {
    const activeIds = new Set(filteredNodes.value.map(n => n.id))
    return graphData.value.edges.filter(e => activeIds.has(e.source) && activeIds.has(e.target))
})

const nodeMap = computed(() => {
    const map = {}
    for (const node of graphData.value.nodes) {
        map[node.id] = node
    }
    return map
})

const selectNode = (node) => {
    selectedNode.value = node
}

const zoomIn = () => { zoom.value = Math.min(zoom.value + 0.15, 2.2) }
const zoomOut = () => { zoom.value = Math.max(zoom.value - 0.15, 0.5) }
const resetView = () => { zoom.value = 1; panX.value = 0; panY.value = 0 }

onMounted(() => {
    fetchGraph()
})
</script>

<template>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm space-y-6">
        <!-- Header & Controls -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span>🕸️</span>
                    <span>Interactive Opportunity Graph</span>
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Visual network linking opportunities, verified evidence, competitor moves & codebase technical debt
                </p>
            </div>

            <!-- Filter Pills -->
            <div class="flex items-center gap-1.5 flex-wrap">
                <button
                    v-for="cat in [
                        { id: 'all', label: 'All Nodes' },
                        { id: 'opportunities', label: 'Opportunities' },
                        { id: 'quick_wins', label: 'Quick Wins' },
                        { id: 'competitor', label: 'Competitors' },
                        { id: 'codebase', label: 'Codebase' },
                    ]"
                    :key="cat.id"
                    @click="filter = cat.id"
                    class="px-2.5 py-1 text-xs font-semibold rounded-lg transition-colors"
                    :class="filter === cat.id ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                >
                    {{ cat.label }}
                </button>
            </div>
        </div>

        <!-- Canvas Area with Inspector Split -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Graph Canvas -->
            <div class="lg:col-span-2 relative bg-slate-950 rounded-2xl border border-slate-800 overflow-hidden h-[480px] flex items-center justify-center">
                <div v-if="loading" class="text-xs text-slate-400 font-mono flex items-center gap-2">
                    <span>⏳</span>
                    <span>Mapping Opportunity Topology...</span>
                </div>

                <!-- SVG Graph Network -->
                <svg
                    v-else
                    viewBox="0 0 900 600"
                    class="w-full h-full cursor-grab active:cursor-grabbing transition-transform select-none"
                    :style="{ transform: `scale(${zoom}) translate(${panX}px, ${panY}px)` }"
                >
                    <defs>
                        <!-- Glow filters -->
                        <filter id="glow-indigo" x="-20%" y="-20%" width="140%" height="140%">
                            <feGaussianBlur stdDeviation="4" result="blur" />
                            <feComposite in="SourceGraphic" in2="blur" operator="over" />
                        </filter>
                    </defs>

                    <!-- Edges -->
                    <g class="edges">
                        <line
                            v-for="(edge, idx) in filteredEdges"
                            :key="idx"
                            :x1="nodeMap[edge.source]?.x || 0"
                            :y1="nodeMap[edge.source]?.y || 0"
                            :x2="nodeMap[edge.target]?.x || 0"
                            :y2="nodeMap[edge.target]?.y || 0"
                            stroke="#334155"
                            stroke-width="1.5"
                            stroke-dasharray="3,3"
                            class="transition-opacity"
                            :opacity="selectedNode && (selectedNode.id === edge.source || selectedNode.id === edge.target) ? 0.9 : 0.4"
                            :stroke="selectedNode && (selectedNode.id === edge.source || selectedNode.id === edge.target) ? '#818cf8' : '#334155'"
                        />
                    </g>

                    <!-- Nodes -->
                    <g class="nodes">
                        <g
                            v-for="node in filteredNodes"
                            :key="node.id"
                            :transform="`translate(${node.x}, ${node.y})`"
                            @click="selectNode(node)"
                            class="cursor-pointer group"
                        >
                            <!-- Selection aura -->
                            <circle
                                v-if="selectedNode?.id === node.id"
                                :r="node.radius + 7"
                                fill="none"
                                stroke="#818cf8"
                                stroke-width="2"
                                stroke-dasharray="4,4"
                                class="animate-spin-slow origin-center"
                            />

                            <!-- Main Node Circle -->
                            <circle
                                :r="node.radius"
                                :fill="node.color"
                                class="transition-transform duration-200 group-hover:scale-110"
                                :filter="selectedNode?.id === node.id ? 'url(#glow-indigo)' : undefined"
                            />

                            <!-- Node Icon / Letter -->
                            <text
                                text-anchor="middle"
                                dy=".3em"
                                fill="#ffffff"
                                font-size="11"
                                font-weight="bold"
                                class="pointer-events-none font-sans"
                            >
                                {{ node.type === 'project' ? '🚀' : node.type === 'repository' ? '🐙' : node.type === 'competitor' ? '⚔️' : node.type === 'recommendation' ? '⚡' : '💡' }}
                            </text>

                            <!-- Node Label Text -->
                            <text
                                text-anchor="middle"
                                :y="node.radius + 14"
                                fill="#cbd5e1"
                                font-size="10"
                                font-weight="600"
                                class="pointer-events-none drop-shadow-md"
                            >
                                {{ node.label.length > 20 ? node.label.slice(0, 18) + '…' : node.label }}
                            </text>
                        </g>
                    </g>
                </svg>

                <!-- Zoom Controls Overlay -->
                <div class="absolute bottom-3 left-3 flex items-center gap-1.5 bg-slate-900/80 backdrop-blur-md p-1.5 rounded-xl border border-slate-800">
                    <button @click="zoomIn" class="w-7 h-7 flex items-center justify-center text-xs font-bold text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-700 rounded-lg">
                        +
                    </button>
                    <button @click="zoomOut" class="w-7 h-7 flex items-center justify-center text-xs font-bold text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-700 rounded-lg">
                        -
                    </button>
                    <button @click="resetView" class="px-2 h-7 flex items-center justify-center text-[11px] font-medium text-slate-400 hover:text-white bg-slate-800 hover:bg-slate-700 rounded-lg">
                        Reset
                    </button>
                </div>

                <!-- Legend Overlay -->
                <div class="absolute top-3 left-3 flex items-center gap-3 bg-slate-900/80 backdrop-blur-md px-3 py-1.5 rounded-xl border border-slate-800 text-[11px] text-slate-400">
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Quick Win</span>
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-blue-500"></span> Major Project</span>
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-rose-500"></span> Competitor</span>
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-sky-500"></span> Codebase</span>
                </div>
            </div>

            <!-- Right 1 Col: Node Details Inspector -->
            <div class="bg-slate-50 dark:bg-slate-850 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 flex flex-col justify-between space-y-4">
                <div v-if="selectedNode" class="space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md text-white" :style="{ backgroundColor: selectedNode.color }">
                            {{ selectedNode.type }}
                        </span>
                        <span class="text-xs font-mono text-slate-400">
                            Node ID: {{ selectedNode.id }}
                        </span>
                    </div>

                    <div>
                        <h4 class="text-base font-bold text-slate-900 dark:text-white">
                            {{ selectedNode.label }}
                        </h4>
                        <p v-if="selectedNode.details?.description" class="text-xs text-slate-600 dark:text-slate-300 mt-1.5 leading-relaxed">
                            {{ selectedNode.details.description }}
                        </p>
                    </div>

                    <!-- Details Breakdown -->
                    <div class="space-y-2 text-xs">
                        <div v-if="selectedNode.details?.quadrant" class="flex justify-between py-1 border-b border-slate-200/60 dark:border-slate-800/60">
                            <span class="text-slate-500">Quadrant:</span>
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ selectedNode.details.quadrant }}</span>
                        </div>
                        <div v-if="selectedNode.details?.impact" class="flex justify-between py-1 border-b border-slate-200/60 dark:border-slate-800/60">
                            <span class="text-slate-500">Impact / Difficulty:</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">{{ selectedNode.details.impact }} / {{ selectedNode.details.difficulty }}</span>
                        </div>
                        <div v-if="selectedNode.details?.priority_score" class="flex justify-between py-1 border-b border-slate-200/60 dark:border-slate-800/60">
                            <span class="text-slate-500">Priority Score:</span>
                            <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ selectedNode.details.priority_score }} / 100</span>
                        </div>
                        <div v-if="selectedNode.details?.suggested_action" class="pt-1">
                            <span class="text-slate-500 block mb-1 font-semibold">Recommended Action:</span>
                            <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-[11px] text-slate-700 dark:text-slate-300">
                                {{ selectedNode.details.suggested_action }}
                            </div>
                        </div>
                        <div v-if="selectedNode.details?.health_score !== undefined" class="space-y-1.5 pt-1">
                            <div class="flex justify-between">
                                <span class="text-slate-500">Code Health:</span>
                                <span class="font-bold text-emerald-500">{{ selectedNode.details.health_score }} / 100</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Technical Debt:</span>
                                <span class="font-bold text-rose-500">{{ selectedNode.details.debt_score }} / 100</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-12 text-xs text-slate-400">
                    Click any node on the graph to inspect strategic connections.
                </div>

                <!-- Network Metrics Footer -->
                <div class="pt-3 border-t border-slate-200 dark:border-slate-800 text-[11px] text-slate-400 flex items-center justify-between">
                    <span>{{ graphData.metrics?.total_nodes || 0 }} Nodes</span>
                    <span>{{ graphData.metrics?.total_edges || 0 }} Edges</span>
                    <span class="text-emerald-500 font-semibold">{{ graphData.metrics?.quick_wins || 0 }} Quick Wins</span>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes spin-slow {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.animate-spin-slow {
    animation: spin-slow 16s linear infinite;
}
</style>
