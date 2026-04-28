const { getOnboardingGenerateHandler, FALLBACK_HABITS } = require('../src/handlers/user/onboardingHandlers');

describe('Onboarding Handler', () => {
  let mockGenAI;
  let mockModel;
  let mockRes;

  beforeEach(() => {
    mockModel = {
      generateContent: jest.fn(),
    };
    mockGenAI = {
      getGenerativeModel: jest.fn().mockReturnValue(mockModel),
    };
    mockRes = {
      writeHead: jest.fn(),
      end: jest.fn(),
    };
  });

  describe('getOnboardingGenerateHandler', () => {
    it('should return 400 if required fields are missing', () => {
      const handler = getOnboardingGenerateHandler(null);
      const req = { body: {} };

      handler(req, mockRes);

      expect(mockRes.writeHead).toHaveBeenCalledWith(400, { 'Content-Type': 'application/json' });
      expect(mockRes.end).toHaveBeenCalledWith(
        expect.stringContaining('Falten camps obligatoris')
      );
    });

    it('should return fallback habits if Gemini is not configured', () => {
      const handler = getOnboardingGenerateHandler(null);
      const req = {
        body: {
          categoria: 'salut',
          senyal: 'matí',
          dificultat: 'estrès',
          temps: '30min',
        },
      };

      handler(req, mockRes);

      expect(mockRes.writeHead).toHaveBeenCalledWith(200, { 'Content-Type': 'application/json' });
      expect(mockRes.end).toHaveBeenCalledWith(
        expect.stringContaining('"success":true')
      );
    });

    it('should call Gemini API with correct prompt', async () => {
      const mockResult = {
        response: {
          text: () => JSON.stringify([
            { titol: 'Test Habit', categoria: 'salut', senyal: 'matí', rutina: 'Test', recompensa: 'Test' },
          ]),
        },
      };
      mockModel.generateContent.mockResolvedValue(mockResult);

      const handler = getOnboardingGenerateHandler(mockGenAI);
      const req = {
        body: {
          categoria: 'salut',
          senyal: 'matí',
          dificultat: 'estrès',
          temps: '30min',
        },
      };

      await handler(req, mockRes);

      expect(mockModel.generateContent).toHaveBeenCalled();
      const prompt = mockModel.generateContent.mock.calls[0][0];
      expect(prompt).toContain('salut');
      expect(prompt).toContain('matí');
    });

    it('should return fallback habits on Gemini API error', async () => {
      mockModel.generateContent.mockRejectedValue(new Error('API Error'));

      const handler = getOnboardingGenerateHandler(mockGenAI);
      const req = {
        body: {
          categoria: 'salut',
          senyal: 'matí',
          dificultat: 'estrès',
          temps: '30min',
        },
      };

      await handler(req, mockRes);

      expect(mockRes.writeHead).toHaveBeenCalledWith(200, { 'Content-Type': 'application/json' });
      expect(mockRes.end).toHaveBeenCalledWith(
        expect.stringContaining('"success":true')
      );
      expect(mockRes.end).toHaveBeenCalledWith(
        expect.stringContaining('"habits":')
      );
    });

    it('should return fallback habits if JSON parsing fails', async () => {
      const mockResult = {
        response: {
          text: () => 'Invalid JSON response',
        },
      };
      mockModel.generateContent.mockResolvedValue(mockResult);

      const handler = getOnboardingGenerateHandler(mockGenAI);
      const req = {
        body: {
          categoria: 'salut',
          senyal: 'matí',
          dificultat: 'estrès',
          temps: '30min',
        },
      };

      await handler(req, mockRes);

      expect(mockRes.writeHead).toHaveBeenCalledWith(200, { 'Content-Type': 'application/json' });
      expect(mockRes.end).toHaveBeenCalledWith(
        expect.stringContaining('"success":true')
      );
    });

    it('should return at most 4 habits', async () => {
      const manyHabits = Array(6).fill({
        titol: 'Test Habit',
        categoria: 'salut',
        senyal: 'matí',
        rutina: 'Test',
        recompensa: 'Test',
      });
      const mockResult = {
        response: {
          text: () => JSON.stringify(manyHabits),
        },
      };
      mockModel.generateContent.mockResolvedValue(mockResult);

      const handler = getOnboardingGenerateHandler(mockGenAI);
      const req = {
        body: {
          categoria: 'salut',
          senyal: 'matí',
          dificultat: 'estrès',
          temps: '30min',
        },
      };

      await handler(req, mockRes);

      const response = JSON.parse(mockRes.end.mock.calls[0][0]);
      expect(response.habits.length).toBeLessThanOrEqual(4);
    });
  });

  describe('FALLBACK_HABITS', () => {
    it('should have 3 fallback habits', () => {
      expect(FALLBACK_HABITS.length).toBe(3);
    });

    it('each fallback habit should have required fields', () => {
      FALLBACK_HABITS.forEach(habit => {
        expect(habit).toHaveProperty('titol');
        expect(habit).toHaveProperty('categoria');
        expect(habit).toHaveProperty('senyal');
        expect(habit).toHaveProperty('rutina');
        expect(habit).toHaveProperty('recompensa');
      });
    });
  });
});
