import { expect, test } from '@playwright/test';

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
  if (Array.isArray(dades)) {
    return dades;
  }
  if (dades && Array.isArray(dades.data)) {
    return dades.data;
  }
  return [];
}

test('flux e2e: vincular recurs extern, editar i veure detalls a home', async ({ page, request }) => {
  const uniqueId = Date.now();
  const email = `e2e.external.${uniqueId}@loopy.test`;
  const password = 'Password123!';
  const nomHabit = `Habit E2E ${uniqueId}`;
  const titolManual = `Manual ${uniqueId}`;
  const urlManual = `https://img.test/manual-${uniqueId}.jpg`;

  const respostaRegister = await request.post(`${apiUrl}/api/auth/register`, {
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
  expect(respostaRegister.ok()).toBeTruthy();

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

  await page.goto('/auth/login');
  await page.locator('input[type="email"]').fill(email);
  await page.locator('input[type="password"]').fill(password);
  await page.locator('button[type="submit"]').click();
  await page.waitForURL('**/home');

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

  await expect
    .poll(async () => {
      const habits = await obtenirHabitsAmbToken(request, token);
      return habits.find((h) => h.titol === nomHabit);
    }, { timeout: 15000 })
    .toBeTruthy();

  const habitsDespresCrear = await obtenirHabitsAmbToken(request, token);
  const habitCreatObjecte = habitsDespresCrear.find((h) => h.titol === nomHabit);
  expect(habitCreatObjecte.metadata.tipus_api).toBe('google_books');

  await page.goto('/habits');
  await page.getByTestId(`habit-list-item-${habitCreatObjecte.id}`).click();
  await page.getByTestId('habit-category-home').click();
  await page.getByRole('button', { name: /sí, canviar|sí|si/i }).click();
  await page.getByTestId('manual-title-input').fill(titolManual);
  await page.getByTestId('manual-image-input').fill(urlManual);
  await page.getByTestId('habit-save-button').click();

  await expect
    .poll(async () => {
      const habits = await obtenirHabitsAmbToken(request, token);
      const h = habits.find((item) => item.id === habitCreatObjecte.id);
      if (!h) {
        return 'missing';
      }
      return `${h.categoria_id}|${h.metadata && h.metadata.tipus_api}|${h.metadata && h.metadata.titol}`;
    }, { timeout: 15000 })
    .toBe(`7|manual|${titolManual}`);

  await page.goto('/home');
  const card = page.locator('div.bg-white.rounded-lg').filter({ hasText: nomHabit }).first();
  await expect(card).toBeVisible();
  await card.getByTestId('habit-details-button').click();

  await expect(page.getByTestId('habit-details-modal')).toBeVisible();
  await expect(page.getByTestId('habit-details-metadata-title')).toContainText(titolManual);
  await expect(page.getByTestId('habit-details-weather-block')).toBeVisible();
});
