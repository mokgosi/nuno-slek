# NUNO-SLACK

A real-time team communication platform inspired by Slack, built with Laravel and React. Features workspaces, channels, direct messages, threaded conversations, file sharing, and real-time updates via WebSockets.

## Features

- **Workspaces** — Create and switch between multiple workspaces
- **Channels** — Public and private channels for team communication
- **Direct Messages** — 1:1 and group DMs
- **Threaded Conversations** — Reply in threads to keep conversations organized
- **Real-Time Messaging** — Instant message delivery via WebSockets
- **File Sharing** — Upload and share images, documents, and media
- **Message Reactions** — React to messages with emoji
- **@Mentions** — Mention users and groups with `@username` and `@channel`
- **Search** — Full-text search across messages, files, and channels
- **User Profiles & Status** — Customizable profiles with online status and status messages
- **Notifications** — In-app and browser notifications
- **Message Editing & Deletion** — Edit or delete your own messages
- **Pinned Messages** — Pin important messages to channels
- **Bookmarks** — Save important messages for later

## Tech Stack

### Backend

| Tool | Version | Purpose |
|------|---------|---------|
| PHP | 8.4 | Server-side language |
| Laravel | 13 | Application framework |
| Laravel Reverb | — | WebSocket server for real-time events |
| Laravel Broadcasting | — | Event broadcasting to WebSocket clients |
| Laravel Horizon | — | Queue dashboard and management |
| Laravel Fortify | 1 | Authentication (login, registration, 2FA, passkeys) |
| Laravel Wayfinder | — | Type-safe route generation for frontend |
| Laravel Telescope | — | Debugging and insights (dev) |
| Spatie Laravel Permission | — | Role and permission management |
| Pest | 4 | Testing framework |

### Frontend

| Tool | Version | Purpose |
|------|---------|---------|
| React | 19 | UI library |
| Inertia.js | 3 | SPA experience without API layer |
| Tailwind CSS | 4 | Utility-first CSS framework |
| Radix UI | — | Accessible UI primitives (dialogs, dropdowns, tooltips) |
| Lucide React | — | Icon library |
| Sonner | — | Toast notifications |
| TypeScript | 5 | Type safety |
| Vite | 8 | Build tool and dev server |

### Development & Quality

| Tool | Version | Purpose |
|------|---------|---------|
| Laravel Sail | 1 | Docker development environment |
| Larastan | 3 | PHP static analysis |
| Rector Laravel | 2 | Automated Laravel-specific refactoring and code upgrades |
| Laravel Pint | 1 | PHP code formatting |
| ESLint | 9 | JavaScript/TypeScript linting |
| Prettier | 3 | Code formatting |
| Laravel Boost | 2 | AI-assisted development tools |

## Getting Started

### Prerequisites

- PHP 8.4+
- Composer
- Node.js 20+
- pnpm (or npm)
- Docker (optional, for Laravel Sail)

### Installation

```bash
# Clone the repository
git clone <repo-url>
cd nuno-slack

# Install dependencies
composer install
npm install

# Set up environment
cp .env.example .env
php artisan key:generate

# Run database migrations
php artisan migrate

# Build frontend assets
npm run build
```

### Development

```bash
# Start full development environment (includes Vite, queue worker, Reverb, and Pail)
composer run dev

# Or start services individually:
php artisan serve          # HTTP server
npm run dev                # Vite dev server
php artisan queue:work     # Queue worker
php artisan reverb:start   # WebSocket server
```

### Testing

```bash
# Run all tests
composer run test

# Run tests only
php artisan test --compact

# Run specific test
php artisan test --compact --filter=SendMessageTest

# Type checking
composer run types:check

# Refactoring (dry-run to preview changes)
composer run refactor:check

# Apply refactoring
composer run refactor

# Linting
composer run lint
```

## Project Structure

```
nuno-slack/
├── app/
│   ├── Actions/          # Form request & action classes
│   ├── Console/          # Artisan commands
│   ├── Http/
│   │   ├── Controllers/  # Route controllers
│   │   ├── Middleware/    # HTTP middleware
│   │   └── Requests/     # Form request validation
│   ├── Models/           # Eloquent models
│   └── Providers/        # Service providers
├── config/               # Configuration files
├── database/
│   ├── factories/        # Model factories for testing
│   ├── migrations/       # Database migrations
│   └── seeders/          # Database seeders
├── resources/
│   ├── js/
│   │   ├── components/   # Reusable React components
│   │   ├── layouts/      # Inertia page layouts
│   │   ├── pages/        # Inertia page components
│   │   └── types/        # TypeScript type definitions
│   └── css/              # Stylesheets
├── routes/               # Route definitions
└── tests/                # Pest test suites
```

## Documentation

- [Product Requirements Document](PRD.md) — Feature specifications and acceptance criteria
- [Software Architecture Document](SAD.md) — System design, data models, and technical decisions
