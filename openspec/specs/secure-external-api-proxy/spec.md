# Spec: Secure External API Proxy

## Purpose

Ensure all third-party provider calls are routed through server-side Laravel endpoints so that provider API keys are never exposed to the frontend. The proxy also normalises provider responses before returning them to the client.

## Requirements

### Requirement: Server-side provider key isolation
The system SHALL route all third-party provider calls through Laravel endpoints and MUST keep provider API keys only in server-side configuration.

#### Scenario: Frontend requests provider data
- **WHEN** the frontend requests external resource search data
- **THEN** the request is handled by an internal Laravel endpoint that injects provider credentials server-side

### Requirement: Sanitized proxy response contract
The system SHALL return sanitized provider responses suitable for frontend display and metadata persistence without exposing provider secrets or internal configuration.

#### Scenario: Proxy returns resource search results
- **WHEN** Laravel receives a successful response from a provider
- **THEN** Laravel returns only the fields required for UI selection and normalized metadata mapping

#### Scenario: Proxy handles provider errors
- **WHEN** a provider returns an error or timeout
- **THEN** Laravel returns a controlled error response that allows the frontend to activate manual fallback
