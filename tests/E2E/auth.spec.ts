import { test, expect } from '@playwright/test';

test.describe('Authentication & Guest Access', () => {
    test('guest visiting root is redirected to login', async ({ page }) => {
        await page.goto('/');
        await expect(page).toHaveURL(/.*login/);
        await expect(page.locator('h1, h2, form')).toBeVisible();
    });

    test('login page contains brand identity and authentication methods', async ({ page }) => {
        await page.goto('/login');
        await expect(page.locator('text=FORGE')).toBeVisible();
        await expect(page.locator('button[type="submit"]')).toBeVisible();
    });

    test('demo login allows quick testing access', async ({ page }) => {
        await page.goto('/login');
        const demoButton = page.locator('button:has-text("Demo"), button:has-text("demo")');
        if (await demoButton.isVisible()) {
            await demoButton.click();
            await expect(page).toHaveURL(/.*projects/);
        }
    });
});
