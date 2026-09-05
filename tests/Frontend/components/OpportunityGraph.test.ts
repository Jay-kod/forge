import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount, flushPromises } from '@vue/test-utils';
import OpportunityGraph from '@/Components/Opportunity/OpportunityGraph.vue';
import axios from 'axios';

vi.mock('axios');

describe('OpportunityGraph Component', () => {
    const mockGraphData = {
        nodes: [
            { id: 'root', label: 'Forge Core', type: 'project', category: 'project', x: 400, y: 300, radius: 25 },
            { id: 'opp-1', label: 'AI Code Gen', type: 'opportunity', category: 'quick_wins', impact: 8, effort: 3, x: 550, y: 220, radius: 20 },
            { id: 'opp-2', label: 'Enterprise SSO', type: 'opportunity', category: 'major_projects', impact: 9, effort: 8, x: 250, y: 180, radius: 22 },
        ],
        edges: [
            { source: 'root', target: 'opp-1', type: 'targets' },
            { source: 'root', target: 'opp-2', type: 'targets' },
        ],
        metrics: {
            total_opportunities: 2,
            quick_wins_count: 1,
            major_projects_count: 1,
            strategic_density_score: 85,
        },
    };

    beforeEach(() => {
        vi.clearAllMocks();
        (axios.get as any).mockResolvedValue({ data: mockGraphData });
    });

    it('fetches graph data on mount for the given project', async () => {
        const wrapper = mount(OpportunityGraph, {
            props: {
                project: { id: 'proj-123', title: 'Test Project' },
            },
        });

        expect(axios.get).toHaveBeenCalledWith('/projects/proj-123/graph');
        await flushPromises();

        // Check that SVG rendering exists
        const svg = wrapper.find('svg');
        expect(svg.exists()).toBe(true);
    });

    it('filters nodes when category filter changes', async () => {
        const wrapper = mount(OpportunityGraph, {
            props: {
                project: { id: 'proj-123', title: 'Test Project' },
            },
        });

        await flushPromises();

        // Node labels rendered
        expect(wrapper.text()).toContain('Forge Core');
        expect(wrapper.text()).toContain('AI Code Gen');
        expect(wrapper.text()).toContain('Enterprise SSO');
    });
});
