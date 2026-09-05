import { test, expect } from '@playwright/test';

test.describe('End-to-End Project Workflow', () => {
    test.beforeEach(async ({ page }) => {
        // Authenticate via demo login
        await page.goto('/login');
        const demoButton = page.locator('button:has-text("Demo"), button:has-text("demo")');
        if (await demoButton.isVisible()) {
            await demoButton.click();
            await page.waitForURL(/.*projects/);
        }
    });

    test('authenticated user can navigate projects and initiate project creation', async ({ page }) => {
        await page.goto('/projects');
        await expect(page.locator('text=Projects, text=FORGE')).toBeVisible();

        const createButton = page.locator('a[href*="/projects/create"], button:has-text("New Project")');
        if (await createButton.isVisible()) {
            await createButton.click();
            await expect(page).toHaveURL(/.*projects\/create/);
        }
    });
});
