Forge 2 Qualifier — Sprint Kanban Board
A two-agent system (Hermes + OpenClaw) that built a Trello-style Kanban board.

Live URL
https://kanbnn.netlify.app

Backend API
https://kanban-qe4i.onrender.com/api/boards

Video Walkthrough
https://drive.google.com/file/d/1G6XNaRtAhirZTQ-kLVYNyeqEzNDgI2pn/view?usp=drivesdk

What it does
Boards → Lists → Cards (move cards between lists)
Card title + description (editable)
Coloured tags/labels (Bug, Design, Feature, Docs, Review)
Assign a member to a card
Due date with overdue visual flag (red border + ⚠)
Agents used
Agent	Tool	Model
Brain (planning)	Hermes (NousResearch)	Groq openai/gpt-oss-120b
Hands (coding)	OpenClaw	Groq llama-3.3-70b-versatile
Slack channels
Channel	Purpose
#sprint-main	Human talks to Hermes, plans land here
#agent-coder	Hermes assigns tasks to OpenClaw
#agent-log	Raw agent activity and autonomous runs
How to run locally
Frontend
Open in browser:

Backend
cd backend
php -S localhost:8000 index.php
API runs at http://localhost:8000/api/boards

Or use the live backend at:

Free models used
Hermes → Groq openai/gpt-oss-120b (planning and orchestration)
OpenClaw → Groq llama-3.3-70b-versatile (code execution)
No paid models used
Repo structure
kanban/

├── frontend/ → Kanban UI (index.html)

├── backend/ → PHP REST API (index.php + Dockerfile)

├── skills/

│ └── status-report/

│ └── SKILL.md → Hermes reusable skill

├── slack-export/ → Screenshots of agent chat loop

├── hermes.config.json → Hermes agent config (secrets removed)

├── openclaw.json → OpenClaw agent config (secrets removed)

├── README.md

├── ARCHITECTURE.md

├── agent-log.md

└── .env.example

How the agent loop worked
Human posted goal in #sprint-main
Hermes posted a plan
Hermes assigned task to OpenClaw in #agent-coder
OpenClaw wrote code, ran it, reported back:
What I Did
What's Left
What Needs Your Call
Human reviewed and approved in channel
Hermes ran autonomously on cron every 10 minutes and posted progress to #sprint-main
Anything to know to run or judge it
Frontend is live at https://kanbnn.netlify.app — open in any browser, no install needed.

Backend API is live at https://kanban-qe4i.onrender.com — returns real JSON data.

All 5 required features work:

Boards → Lists → Cards with move between lists
Card title + description editable
Coloured tags on cards
Member assignment
Due date with overdue flag
Free models only — Groq openai/gpt-oss-120b (Hermes) and llama-3.3-70b-versatile (OpenClaw). No paid APIs used.