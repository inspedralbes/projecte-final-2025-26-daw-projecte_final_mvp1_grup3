/**
 * Modul JavaScript ES5: mode-focus.spec.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */

import { expect, test } from '@playwright/test';
import { execSync } from 'child_process';
import path from 'path';

const apiUrl = process.env.E2E_API_URL || 'http://localhost:8000';

async function obtenirHabitsAmbToken(request, token) {
  const resposta = await request.get(`${apiUrl}/api/habits/all`, {
    headers: {
      Accept: 'application/json',
      Authorization: `Bearer ${token}`
    }
  });

  if (!resposta.ok()) {
    return [];
  }

  const dades = await resposta.json();
  if (Array.isArray(dades)) return dades;
  if (dades && Array.isArray(dades.data)) return dades.data;
  return [];
}

async function obtenirUserIdAmbToken(request, token) {
  const resposta = await request.get(`${apiUrl}/api/users/self/profile`, {
    headers: {
      Accept: 'application/json',
      Authorization: `Bearer ${token}`
    }
  });

  expect(resposta.ok()).toBeTruthy();
  const dades = await resposta.json();
  expect(dades && dades.id).toBeTruthy();
  return dades.id;
}

test('mode focus: botó d\'entrada deshabilitat quan hàbit ja està completat avui', async ({ page, request }) => {
  const uniqueId = Date.now();
  const email = `e2e.focus.${uniqueId}@loopy.test`;
  const password = 'Password123!';
  const nomHabit = `Habit Focus E2E ${uniqueId}`;

  await page.route('**/api/external/books*', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        ok: true,
        items: [
          {
            api_id: 'book-e2e-1',
            titol: 'Atomic Habits E2E',
            url_imatge: 'https://img.test/atomic-e2e.jpg',
            tipus_api: 'google_books'
          }
        ]
      })
    });
  });

  await page.route('**/api/external/weather*', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        ok: true,
        city: 'Barcelona',
        temp: 21.5,
        weather: 'Clear',
        description: 'clear sky',
        suitable: true
      })
    });
  });

  await request.post(`${apiUrl}/api/auth/register`, {
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json'
    },
    data: {
      nom: `E2E User ${uniqueId}`,
      email: email,
      contrasenya: password,
      contrasenya_confirmation: password
    }
  });

  await page.goto('/auth/login');
  await page.locator('input[type="email"]').fill(email);
  await page.locator('input[type="password"]').fill(password);
  await page.locator('button[type="submit"]').click();
  await page.waitForURL('**/home');

  // Crear un hàbit ràpidament (entrada externa stubbejada).
  await page.goto('/habits');
  await page.getByTestId('habit-name-input').fill(nomHabit);
  await page.getByTestId('habit-category-reading').click();
  await page.getByTestId('external-search-input').fill('atomic');
  await page.getByTestId('external-search-button').click();
  await expect(page.getByTestId('external-result-item').first()).toBeVisible();
  await page.getByTestId('external-result-item').first().click();
  await page.getByTestId('habit-save-button').click();

  const token = await page.evaluate(() => localStorage.getItem('loopy_token'));
  expect(token).toBeTruthy();

  let habit = null;
  await expect.poll(async () => {
    const items = await obtenirHabitsAmbToken(request, token);
    habit = items.find((h) => h.titol === nomHabit) || null;
    return habit;
  }, { timeout: 15000 }).toBeTruthy();

  const habitId = habit.id;

  await page.goto('/home');
  const card = page.getByTestId(`home-habit-card-${habitId}`);

  // Completar l'hàbit des del modal de progrés.
  await card.getByTestId('habit-progress-button').click();
  await expect(page.getByTestId('habit-progress-modal')).toBeVisible();
  await page.getByTestId('habit-progress-plus').click();
  await page.getByTestId('habit-progress-confirm').click();

  await page.waitForTimeout(1000);

  // Refresquem estat del store per assegurar "completed_today".
  await page.reload();
  await page.goto('/home');

  await card.getByTestId('habit-details-button').click();
  const detailsModal = page.getByTestId('habit-details-modal');
  await expect(detailsModal).toBeVisible();
  const startFocus = detailsModal.getByTestId('start-focus-session-button');
  await expect(startFocus).toBeDisabled();
  await expect(startFocus).toContainText('Hàbit completat avui');
});

test('mode focus: selector de preset obligatori i Play/Pausa estable', async ({ page }) => {
  const uniqueId = Date.now();
  const email = `e2e.focus2.${uniqueId}@loopy.test`;
  const password = 'Password123!';
  const nomHabit = `Habit Focus E2E ${uniqueId}`;

  await page.route('**/api/external/books*', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        ok: true,
        items: [
          {
            api_id: 'book-e2e-1',
            titol: 'Atomic Habits E2E',
            url_imatge: 'https://img.test/atomic-e2e.jpg',
            tipus_api: 'google_books'
          }
        ]
      })
    });
  });

  await page.route('**/api/external/weather*', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        ok: true,
        city: 'Barcelona',
        temp: 21.5,
        weather: 'Clear',
        description: 'clear sky',
        suitable: true
      })
    });
  });

  // Registre (API) i login (UI)
  await page.request.post(`${apiUrl}/api/auth/register`, {
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json'
    },
    data: {
      nom: `E2E User ${uniqueId}`,
      email: email,
      contrasenya: password,
      contrasenya_confirmation: password
    }
  });

  await page.goto('/auth/login');
  await page.locator('input[type="email"]').fill(email);
  await page.locator('input[type="password"]').fill(password);
  await page.locator('button[type="submit"]').click();
  await page.waitForURL('**/home');

  // Crear hàbit.
  await page.goto('/habits');
  await page.getByTestId('habit-name-input').fill(nomHabit);
  await page.getByTestId('habit-category-reading').click();
  await page.getByTestId('external-search-input').fill('atomic');
  await page.getByTestId('external-search-button').click();
  await expect(page.getByTestId('external-result-item').first()).toBeVisible();
  await page.getByTestId('external-result-item').first().click();
  await page.getByTestId('habit-save-button').click();

  const token = await page.evaluate(() => localStorage.getItem('loopy_token'));
  expect(token).toBeTruthy();

  // Obtenir habitId via API.
  let habit = null;
  await expect
    .poll(async () => {
      const resposta = await page.request.get(`${apiUrl}/api/habits/all`, {
        headers: {
          Accept: 'application/json',
          Authorization: `Bearer ${token}`
        }
      });
      const dades = await resposta.json();
      const items = Array.isArray(dades) ? dades : (dades.data || []);
      habit = items.find((h) => h.titol === nomHabit) || null;
      return habit;
    }, { timeout: 15000 })
    .toBeTruthy();

  const habitId = habit.id;

  await page.goto('/home');
  const card = page.getByTestId(`home-habit-card-${habitId}`);
  await card.getByTestId('habit-details-button').click();

  await expect(page.getByTestId('habit-details-modal')).toBeVisible();
  const detailsModal = page.getByTestId('habit-details-modal');
  await detailsModal.getByTestId('start-focus-session-button').click();

  await page.waitForURL(`**/focus/${habitId}`);

  const playBtn = page.getByTestId('focus-play-pause-button');
  await expect(playBtn).toBeDisabled();
  await expect(playBtn).toHaveText('Play');

  await page.getByTestId('focus-preset-25_5').click();
  await expect(playBtn).not.toBeDisabled();

  await playBtn.click();
  await expect(playBtn).toHaveText('Pausa');

  await playBtn.click();
  await expect(playBtn).toHaveText('Play');
});

test('mode focus: flux e2e exit -> snapshot calendar focus metadata', async ({ page, request }) => {
  const uniqueId = Date.now();
  const email = `e2e.focus3.${uniqueId}@loopy.test`;
  const password = 'Password123!';
  const nomHabit = `Habit Focus E2E ${uniqueId}`;

  // Acelerar setInterval per reduir temps real (1 segon simulat ~= 1/60 de segon real).
  await page.addInitScript(() => {
    const originalSetInterval = window.setInterval;
    window.setInterval = function (fn, ms) {
      return originalSetInterval(fn, Math.max(5, (ms || 0) / 60));
    };
  });

  await page.route('**/api/external/books*', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        ok: true,
        items: [
          {
            api_id: 'book-e2e-1',
            titol: 'Atomic Habits E2E',
            url_imatge: 'https://img.test/atomic-e2e.jpg',
            tipus_api: 'google_books'
          }
        ]
      })
    });
  });

  await page.route('**/api/external/weather*', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        ok: true,
        city: 'Barcelona',
        temp: 21.5,
        weather: 'Clear',
        description: 'clear sky',
        suitable: true
      })
    });
  });

  await request.post(`${apiUrl}/api/auth/register`, {
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json'
    },
    data: {
      nom: `E2E User ${uniqueId}`,
      email: email,
      contrasenya: password,
      contrasenya_confirmation: password
    }
  });

  await page.goto('/auth/login');
  await page.locator('input[type="email"]').fill(email);
  await page.locator('input[type="password"]').fill(password);
  await page.locator('button[type="submit"]').click();
  await page.waitForURL('**/home');

  // Crear hàbit.
  await page.goto('/habits');
  await page.getByTestId('habit-name-input').fill(nomHabit);
  await page.getByTestId('habit-category-reading').click();
  await page.getByTestId('external-search-input').fill('atomic');
  await page.getByTestId('external-search-button').click();
  await expect(page.getByTestId('external-result-item').first()).toBeVisible();
  await page.getByTestId('external-result-item').first().click();
  await page.getByTestId('habit-save-button').click();

  const token = await page.evaluate(() => localStorage.getItem('loopy_token'));
  expect(token).toBeTruthy();

  let habit = null;
  await expect
    .poll(async () => {
      const items = await obtenirHabitsAmbToken(request, token);
      habit = items.find((h) => h.titol === nomHabit) || null;
      return habit;
    }, { timeout: 15000 })
    .toBeTruthy();

  const habitId = habit.id;

  await page.goto('/home');
  const card = page.getByTestId(`home-habit-card-${habitId}`);
  await card.getByTestId('habit-details-button').click();

  await expect(page.getByTestId('habit-details-modal')).toBeVisible();
  await page.getByTestId('start-focus-session-button').click();

  await page.waitForURL(`**/focus/${habitId}`);

  await page.getByTestId('focus-preset-25_5').click();
  await page.getByTestId('focus-play-pause-button').click();

  // Esperem prou per acumular >= 1 minut simulat.
  await page.waitForTimeout(1500);

  await page.getByTestId('focus-exit-button').click();
  await page.waitForURL('**/home');
  // Esperem a que Node -> Redis -> Laravel persisteixi l'actualització de focus.
  await page.waitForTimeout(2500);

  const today = await page.evaluate(() => {
    const pad = (n) => String(n).padStart(2, '0');
    const d = new Date();
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
  });

  // Assegurem que hi hagi snapshot pel dia abans de validar l'endpoint.
  const workspaceRoot = path.resolve(__dirname, '..', '..', '..');
  const backendDir = path.join(workspaceRoot, 'backend-laravel');
  execSync(`php artisan snapshot:run --date ${today}`, { cwd: backendDir, stdio: 'inherit' });

  const userId = await obtenirUserIdAmbToken(request, token);
  const respostaSnap = await request.get(`${apiUrl}/api/calendar/snapshot/${userId}/${today}`, {
    headers: {
      Accept: 'application/json',
      Authorization: `Bearer ${token}`
    }
  });

  expect(respostaSnap.ok()).toBeTruthy();
  const snap = await respostaSnap.json();

  const habitsJson = snap.habits_json || [];
  const focusHabit = habitsJson.find((h) => Number(h.id) === Number(habitId));
  expect(focusHabit).toBeTruthy();
  expect(focusHabit.predominant_focus_mode).toBe('25_5');
  expect(Boolean(focusHabit.completed_with_focus)).toBe(true);
});

