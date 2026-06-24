# Forge 2 Qualifier — Sprint Kanban Board

A two-agent system (Hermes + OpenClaw) that built a Trello-style Kanban board.

## Live URL
[https://your-netlify-url.netlify.app](https://kanbnn.netlify.app/)

## Video Walkthrough
[https://drive.google.com/your-video-link-here](https://drive.google.com/file/d/1G6XNaRtAhirZTQ-kLVYNyeqEzNDgI2pn/view?usp=drivesdk)

## What it does
- Boards → Lists → Cards (move cards between lists)
- Card title + description (editable)
- Coloured tags/labels (Bug, Design, Feature, Docs, Review)
- Assign a member to a card
- Due date with overdue visual flag (red border + ⚠)

## Agents used
| Agent | Tool | Model |
|---|---|---|
| Brain (planning) | Hermes (NousResearch) | Groq openai/gpt-oss-120b |
| Hands (coding) | OpenClaw | Groq llama-3.3-70b-versatile |

## Slack channels
| Channel | Purpose |
|---|---|
| #sprint-main | Human talks to Hermes, plans land here |
| #agent-coder | Hermes assigns tasks to OpenClaw |
| #agent-log | Raw agent activity and autonomous runs |

## How to run locally

### Frontend (no install needed)
Just open in browser:
### Backend (PHP REST API)
```bash
cd backend
php -S localhost:8000 index.php
```
API runs at http://localhost:8000/api/boards
Requires PHP 8.2+

## Free models used
- Hermes → Groq `openai/gpt-oss-120b` (planning)
- OpenClaw → Groq `llama-3.3-70b-versatile` (code execution)
- No paid models used

## Repo structure
kanban/

├── frontend/         → React-style Kanban UI (index.html)

├── backend/          → Plain PHP REST API (index.php)

├── skills/

│   └── status-report/

│       └── SKILL.md  → Hermes reusable skill

├── slack-export/     → Screenshots of agent chat loop

├── README.md

├── ARCHITECTURE.md

├── agent-log.md

├── openclaw.json

└── .env.example
## How the agent loop worked
1. Human posted goal in #sprint-main
2. Hermes posted a plan
3. Hermes assigned task to OpenClaw in #agent-coder
4. OpenClaw wrote code, ran it, reported back:
   - What I Did
   - What's Left
   - What Needs Your Call
5. Human reviewed and approved in channel
6. Hermes ran autonomously on cron every 10 minutes
   and posted progress to #sprint-main

## Anything to know to run or judge it
Frontend is live at the URL above — open in any browser, no install needed.

All 5 required features work:
- Boards → Lists → Cards with move between lists
- Card title + description editable
- Coloured tags on cards
- Member assignment
- Due date with overdue flag

Backend is plain PHP, no composer needed.
Run with: php -S localhost:8000 index.php
