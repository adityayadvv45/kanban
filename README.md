# Kanban Board App

This repository contains a lightweight Kanban board app with:
- plain PHP backend using SQLite
- frontend UI in `frontend/index.html`
- agent architecture with Hermes planning and OpenClaw coding

## How to run locally

1. Open `frontend/index.html` in your browser.
2. Start the backend server from the `backend/` directory:

```bat
php -S localhost:8000 index.php
```

3. Use the API at:

```txt
http://localhost:8000/api/boards
```

## Live URL

Live deployment placeholder:

```txt
https://your-live-url.example.com
```

## Models used
- Hermes (brain/planning) → Groq `openai/gpt-oss-120b`
- OpenClaw (coding/execution) → Groq `llama-3.3-70b-versatile`
