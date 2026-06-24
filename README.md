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
