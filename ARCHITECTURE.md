# Architecture

## Two-Agent System

The app uses two specialized agents:

- **Hermes** — brain and planner
  - responsible for high-level architecture, task breakdown, and coordination
  - uses Groq model `openai/gpt-oss-120b`
- **OpenClaw** — coder and executor
  - responsible for writing code, debugging, and reporting progress
  - uses Groq model `llama-3.3-70b-versatile`

## Slack Channel Structure

| Channel | Purpose |
|---|---|
| #sprint-main | Human posts the goal and receives the plan from Hermes |
| #agent-coder | Hermes assigns coding tasks to OpenClaw |
| #agent-log | Agent execution logs, autonomous updates, and summary records |

## Workflow Loop

1. Human posts a goal in `#sprint-main`.
2. Hermes reads the goal and posts a structured plan.
3. Hermes assigns a concrete coding task to OpenClaw in `#agent-coder`.
4. OpenClaw writes or updates code, then posts a progress report.
5. Human reviews the report and approves or requests changes.

## Status Report Format

OpenClaw reports in three sections:
- **What I Did** — completed work
- **What's Left** — remaining work
- **What Needs Your Call** — decisions or approvals required from the human

## Agent Timing

- Hermes can run interactively on demand.
- The system also supports autonomous cron-style runs where Hermes evaluates progress and posts an update to `#agent-log`.

## Models Used
- Hermes: Groq `openai/gpt-oss-120b`
- OpenClaw: Groq `llama-3.3-70b-versatile`

## App Stack
- Backend: plain PHP + SQLite REST API
- Frontend: static HTML/JavaScript UI
- Slack/agent orchestration: Slack channels and Groq model routing
