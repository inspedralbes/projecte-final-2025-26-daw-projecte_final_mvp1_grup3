## 1. Backend - User Profile Creation

- [x] 1.1 Modify Laravel AuthService to create USUARIS profile on registration with nivel=1, xp=0, monedes=0
- [x] 1.2 Add database migration/seed for default missio_diaria_id values
- [x] 1.3 Create Laravel endpoint POST /api/habits/assign to persist selected habits

## 2. Frontend - Onboarding Questionnaire

- [ ] 2.1 Create pages/onboarding.vue with soft design claymorphism styling
- [ ] 2.2 Implement segmented progress bar component (25% per step)
- [ ] 2.3 Build Question 1 component: Goal selection (Salut/Productivitat/Ment/Aprenentatge) → categoria_id
- [ ] 2.4 Build Question 2 component: Energy time (Matí/Migdia/Tarda/Nit) → senyal
- [ ] 2.5 Build Question 3 component: Obstacle (Estrès/Temps/Memòria/Mandra) → difficulty
- [ ] 2.6 Build Question 4 component: Time available (<15m/30m/1h/+1h) → objectiu_vegades
- [ ] 2.7 Implement navigation between questions with state preservation
- [ ] 2.8 Add loading state while sending to AI service

## 3. AI Service - Gemini Integration

- [x] 3.1 Create Node.js endpoint POST /api/onboarding/generate
- [x] 3.2 Implement @google/generative-ai integration with structured prompt
- [x] 3.3 Define prompt template with questionnaire data (categoria, senyal, dificultat, temps)
- [x] 3.4 Parse Gemini JSON response and validate habit template fields
- [x] 3.5 Add fallback default habits if Gemini fails or returns invalid JSON
- [x] 3.6 Add error handling and logging

## 4. Frontend - Habit Selection & Review

- [ ] 4.1 Build habit card components with claymorphism styling
- [ ] 4.2 Display 3-4 AI-generated habits as selectable cards
- [ ] 4.3 Implement toggle selection (mark/unmark) functionality
- [ ] 4.4 Add "Confirmar" button to persist selections
- [ ] 4.5 Connect to Laravel POST /api/habits/assign endpoint

## 5. Frontend - Middleware & Redirect

- [ ] 5.1 Add Nuxt route guard or middleware to check USUARIS_HABITS
- [ ] 5.2 Redirect to /onboarding if user has no habits
- [ ] 5.3 Add condition to skip redirect if user has completed onboarding

## 6. Frontend - Home & First Mission

- [ ] 6.1 Modify pages/home.vue to load missio_diaria on mount
- [ ] 6.2 Auto-assign "Completa el teu primer hàbit!" mission if not set
- [ ] 6.3 Integrate mascot welcome dialog on first home visit
- [ ] 6.4 Add visual mission display in home page

## 7. Real-time - Socket.io XP System

- [ ] 7.1 Configure Socket.io connection in frontend
- [ ] 7.2 Add 'mission_completed' event handler
- [ ] 7.3 Implement XP animation component for visual feedback
- [ ] 7.4 Add bonus XP award on first onboarding habit completion

## 8. Backend - Laravel Tests

- [x] 8.1 Create Feature test: User profile is created on registration with default values (nivel=1, xp=0, monedes=0)
- [x] 8.2 Create Feature test: POST /api/habits/assign persists habits to HABITS table
- [x] 8.3 Create Feature test: POST /api/habits/assign creates USUARIS_HABITS links
- [x] 8.4 Create Feature test: Assigning habits returns success with habit IDs
- [x] 8.5 Create Feature test: Invalid habit data returns validation errors
- [x] 8.6 Create Unit test: Questionnaire answer mapping (categoria, senyal, dificultat, temps)
- [ ] 8.7 Create Unit test: Mission assignment logic for first-time users
- [x] 8.8 Create Feature test: User without habits is redirected to onboarding (middleware)

## 9. Backend - Node.js Tests

- [x] 9.1 Create Unit test: POST /api/onboarding/generate validates required fields
- [x] 9.2 Create Unit test: Gemini prompt is constructed correctly with questionnaire data
- [x] 9.3 Create Unit test: Gemini response is parsed into habit template format
- [x] 9.4 Create Unit test: Invalid Gemini JSON returns fallback habits
- [x] 9.5 Create Unit test: Empty/null questionnaire data returns error
- [x] 9.6 Create Integration test: Full flow from request to Gemini to response
- [x] 9.7 Create Unit test: Error handling when Gemini API fails

## 10. Frontend - E2E & Integration Tests

- [ ] 10.1 Test full onboarding flow end-to-end
- [ ] 10.2 Test middleware redirect behavior
- [ ] 10.3 Test Socket.io XP reward delivery
- [ ] 10.4 Test fallback habits when Gemini unavailable
- [ ] 10.5 Verify soft design/claymorphism consistency
