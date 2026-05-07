## Why
Loopy currently lacks an intelligent onboarding experience. Users must manually create habits without guidance, leading to low engagement and high abandonment rates. Implementing AI-powered onboarding with Gemini will personalize habit suggestions based on user goals, energy patterns, and obstacles—driving better outcomes and retention.

## What Changes

- Create new `pages/onboarding.vue` with dynamic questionnaire (4 questions, 25% progress each)
- Add middleware to force onboarding redirect for users without habits
- Create Node.js handler to integrate `@google/generative-ai` for personalized habit generation
- Add revision view with selectable habit cards
- Modify `pages/home.vue` to assign first mission and trigger pet welcome dialog
- Configure Socket.io for real-time XP奖励 on mission completion
- **BREAKING**: Modify `AuthService` post-registration flow to create user profile with default values

## Capabilities

### New Capabilities

- `onboarding-questionnaire`: Dynamic onboarding flow with 4-step questionnaire mapping user inputs to habit preferences
- `ai-habit-generator`: Gemini-powered service generating 3-4 personalized habit templates based on questionnaire responses
- `onboarding-mission`: First mission assignment system with real-time XP rewards

### Modified Capabilities

- (none - this is a net-new feature set)

## Impact

- **Frontend**: New onboarding.vue page, modified pages/home.vue for mission/pet
- **Backend Core**: Modified Laravel AuthService post-registration
- **Real-time & IA**: New Node.js endpoint for Gemini integration, Socket.io events
- **Database**: User profile creation in USUARIS table
- **Dependencies**: @google/generative-ai package, Socket.io