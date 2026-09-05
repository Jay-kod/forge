import { describe, it, expect, beforeEach, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import ThemeToggle from '@/Components/ThemeToggle.vue';

describe('ThemeToggle Component', () => {
    beforeEach(() => {
        localStorage.clear();
        document.documentElement.className = '';
        document.documentElement.removeAttribute('data-theme');
        vi.restoreAllMocks();
    });

    it('mounts and renders toggle button', () => {
        const wrapper = mount(ThemeToggle);
        const button = wrapper.find('button');
        expect(button.exists()).toBe(true);
        expect(button.attributes('aria-label')).toBe('Toggle visual theme');
    });

    it('toggles theme between dark and light when clicked', async () => {
        const wrapper = mount(ThemeToggle);
        const button = wrapper.find('button');

        // Initial state click -> switches to dark
        await button.trigger('click');
        expect(document.documentElement.classList.contains('dark')).toBe(true);
        expect(document.documentElement.getAttribute('data-theme')).toBe('dark');
        expect(localStorage.getItem('forge-theme')).toBe('dark');

        // Second click -> switches to light
        await button.trigger('click');
        expect(document.documentElement.classList.contains('dark')).toBe(false);
        expect(document.documentElement.getAttribute('data-theme')).toBe('light');
        expect(localStorage.getItem('forge-theme')).toBe('light');
    });

    it('initializes from localStorage if saved preference exists', () => {
        localStorage.setItem('forge-theme', 'dark');
        mount(ThemeToggle);
        expect(document.documentElement.classList.contains('dark')).toBe(true);
        expect(document.documentElement.getAttribute('data-theme')).toBe('dark');
    });
});
