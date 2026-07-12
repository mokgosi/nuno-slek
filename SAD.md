# Software Architecture Document (SAD)

**Project:** NUNO-SLACK
**Version:** 1.0
**Last Updated:** 2026-07-12

---

## 1. Architecture Overview

NUNO-SLACK follows a **monolithic architecture** using Laravel as the application layer. The frontend is a single-page application (SPA) powered by Inertia.js + React, eliminating the need for a separate API layer. Real-time communication is handled via Laravel Reverb (WebSocket server) with Laravel Broadcasting.

```
┌──────────────────────────────────────────────────────────┐
│                      Client (Browser)                    │
│              React 19 + Inertia.js v3 + Vite             │
└────────────────────────┬─────────────────────────────────┘
                         │ HTTP (Inertia protocol)
                         │ WSS (WebSocket)
┌────────────────────────▼─────────────────────────────────┐
│                    Laravel 13                             │
│  ┌─────────────┐  ┌───────────┐  ┌────────────────────┐  │
│  │  Fortify    │  │ Reverb    │  │  Horizon           │  │
│  │  (Auth)     │  │ (WebSkt)  │  │  (Queues)          │  │
│  └─────────────┘  └───────────┘  └────────────────────┘  │
│  ┌─────────────────────────────────────────────────────┐  │
│  │              Eloquent ORM + SQLite/MySQL             │  │
│  └─────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────┘
```

### Key Architectural Decisions

| Decision | Choice | Rationale |
|----------|--------|-----------|
| Server rendering | Inertia.js (not API + SPA) | No API layer overhead; shared validation; server-side routing |
| Real-time | Laravel Reverb + Broadcasting | Self-hosted WebSocket server; native Laravel integration; no Pusher dependency |
| Queue driver | Redis (via Horizon) | Reliable job processing; dashboard for monitoring |
| Database | SQLite (dev) / MySQL (prod) | SQLite for zero-config local dev; MySQL for production scalability |
| Authentication | Laravel Fortify | Battle-tested; supports 2FA, passkeys; frontend-agnostic |
| Testing | Pest v4 | Expressive syntax; Laravel integration; dataset support |

---

## 2. Technology Stack

### 2.1 Backend

| Layer | Technology | Purpose |
|-------|-----------|---------|
| Language | PHP 8.4 | Server runtime |
| Framework | Laravel 13 | Application framework |
| ORM | Eloquent | Database abstraction |
| Auth | Fortify v1 | Registration, login, 2FA, passkeys |
| WebSocket | Laravel Reverb | Real-time event broadcasting |
| Queue | Laravel Horizon | Background job processing |
| Routes | Wayfinder | Type-safe route generation for TypeScript |
| Validation | Form Requests | Request validation and authorization |

### 2.2 Frontend

| Layer | Technology | Purpose |
|-------|-----------|---------|
| Library | React 19 | UI rendering |
| Routing | Inertia.js v3 | SPA navigation without API |
| Styling | Tailwind CSS v4 | Utility-first CSS |
| Components | Radix UI | Accessible primitives (dialog, dropdown, tooltip) |
| Icons | Lucide React | Icon library |
| Toasts | Sonner | Notification toasts |
| Types | TypeScript 5 | Type safety |
| Build | Vite 8 | HMR, bundling |

### 2.3 Infrastructure

| Tool | Purpose |
|------|---------|
| Laravel Sail | Docker-based local development |
| Laravel Telescope | Debugging (dev) |
| Laravel Pint | PHP code formatting |
| Larastan | PHP static analysis |
| Rector Laravel | Automated Laravel-specific refactoring and code upgrades |
| ESLint + Prettier | JS/TS linting and formatting |
| Pest + PHPUnit | Testing |

---

## 3. Data Model

### 3.1 Entity Relationship Diagram

```
┌──────────┐       ┌──────────────┐       ┌──────────────┐
│   User   │──1:N──│ WorkspaceUser│──N:1──│  Workspace   │
└──────────┘       └──────────────┘       └──────────────┘
     │                    │                      │
     │                    │                      │
     │               ┌────▼─────┐          ┌────▼─────┐
     │               │ Channel  │──1:N─────│ Channel  │
     │               │  Member  │          │          │
     │               └──────────┘          └──────────┘
     │                                        │
     │    ┌──────────┐                  ┌─────▼──────┐
     ├───1│ Message  │──────────N:1─────│  Channel   │
     │    └──────────┘                  └────────────┘
     │         │
     │         │ 1:N
     │    ┌────▼────────┐
     │    │   Reply     │
     │    │  (Thread)   │
     │    └─────────────┘
     │         │
     │         │ N:1
     │    ┌────▼─────────┐
     │    │  Reaction    │
     │    └──────────────┘
     │
     ├──1:N── DirectMessage
     │
     ├──1:N── Bookmark
     │
     └──1:N── Notification
```

### 3.2 Database Tables

#### `users`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint (PK) | auto-increment |
| name | varchar(255) | |
| email | varchar(255) | unique |
| email_verified_at | timestamp | nullable |
| password | varchar(255) | hashed |
| avatar_path | varchar | nullable, disk: public |
| timezone | varchar(50) | default: UTC |
| status_emoji | varchar(10) | nullable |
| status_text | varchar(100) | nullable |
| status_cleared_at | timestamp | nullable |
| last_active_at | timestamp | nullable |
| remember_token | varchar(100) | nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

#### `workspaces`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint (PK) | auto-increment |
| name | varchar(100) | |
| slug | varchar(100) | unique |
| description | text | nullable |
| icon_path | varchar | nullable, disk: public |
| created_by | bigint (FK) | users.id |
| created_at | timestamp | |
| updated_at | timestamp | |

#### `workspace_users`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint (PK) | auto-increment |
| workspace_id | bigint (FK) | workspace_users.id |
| user_id | bigint (FK) | users.id |
| role | enum | owner, admin, member |
| joined_at | timestamp | |
| created_at | timestamp | |
| updated_at | timestamp | |

**Indexes:** unique(workspace_id, user_id)

#### `channels`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint (PK) | auto-increment |
| workspace_id | bigint (FK) | workspaces.id |
| name | varchar(80) | |
| description | text | nullable |
| is_private | boolean | default: false |
| is_archived | boolean | default: false |
| created_by | bigint (FK) | users.id |
| created_at | timestamp | |
| updated_at | timestamp | |

**Indexes:** unique(workspace_id, name)

#### `channel_members`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint (PK) | auto-increment |
| channel_id | bigint (FK) | channels.id |
| user_id | bigint (FK) | users.id |
| last_read_at | timestamp | nullable |
| notification_level | enum | all, mentions, nothing |
| created_at | timestamp | |
| updated_at | timestamp | |

**Indexes:** unique(channel_id, user_id)

#### `messages`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint (PK) | auto-increment |
| channel_id | bigint (FK) | channels.id |
| user_id | bigint (FK) | users.id |
| parent_id | bigint (FK) | nullable, messages.id (for threads) |
| body | text | |
| is_edited | boolean | default: false |
| is_deleted | boolean | default: false |
| is_pinned | boolean | default: false |
| created_at | timestamp | |
| updated_at | timestamp | |

**Indexes:** channel_id + created_at, parent_id

#### `reactions`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint (PK) | auto-increment |
| message_id | bigint (FK) | messages.id |
| user_id | bigint (FK) | users.id |
| emoji | varchar(10) | |
| created_at | timestamp | |
| updated_at | timestamp | |

**Indexes:** unique(message_id, user_id, emoji)

#### `attachments`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint (PK) | auto-increment |
| message_id | bigint (FK) | messages.id |
| path | varchar | storage path |
| original_name | varchar | |
| mime_type | varchar | |
| size | integer | bytes |
| created_at | timestamp | |

#### `direct_message_participants`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint (PK) | auto-increment |
| conversation_id | bigint (FK) | direct_message_conversations.id |
| user_id | bigint (FK) | users.id |
| created_at | timestamp | |

#### `direct_message_conversations`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint (PK) | auto-increment |
| workspace_id | bigint (FK) | workspaces.id |
| name | varchar(100) | nullable, for group DMs |
| created_at | timestamp | |
| updated_at | timestamp | |

#### `bookmarks`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint (PK) | auto-increment |
| user_id | bigint (FK) | users.id |
| message_id | bigint (FK) | messages.id |
| created_at | timestamp | |

**Indexes:** unique(user_id, message_id)

#### `notifications`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint (PK) | auto-increment |
| user_id | bigint (FK) | users.id |
| type | varchar | |
| data | json | |
| read_at | timestamp | nullable |
| created_at | timestamp | |

#### `cache`, `jobs`, `sessions`
Standard Laravel tables for queue, cache, and session management.

---

## 4. Real-Time Architecture

### 4.1 Event Flow

```
User A sends message
       │
       ▼
Controller stores message (DB)
       │
       ▼
Event class created (NewMessage)
       │
       ▼
Broadcast via Reverb (WebSocket)
       │
       ├──► User B receives event (browser)
       ├──► User C receives event (browser)
       └──► User D receives event (browser)
```

### 4.2 Broadcasting Channels

| Channel Pattern | Type | Purpose |
|-----------------|------|---------|
| `workspace.{id}` | Private | Workspace-level events (user joined, channel created) |
| `channel.{id}` | Private | Channel messages and events |
| `dm.{conversationId}` | Private | Direct message events |
| `user.{id}` | Private | User-specific notifications (mentions, DMs) |
| `presence-workspace.{id}` | Presence | Online status tracking |

### 4.3 Events to Broadcast

| Event | Channel | Payload |
|-------|---------|---------|
| `MessageSent` | channel.{id} | message, user, channel |
| `MessageEdited` | channel.{id} | message |
| `MessageDeleted` | channel.{id} | messageId |
| `ReactionAdded` | channel.{id} | reaction, message |
| `ReactionRemoved` | channel.{id} | reaction, message |
| `TypingStarted` | channel.{id} | user, channel |
| `TypingStopped` | channel.{id} | user, channel |
| `UserPresenceChanged` | presence-workspace.{id} | user, status |
| `NotificationCreated` | user.{id} | notification |

---

## 5. Application Architecture

### 5.1 Backend Structure

```
app/
├── Actions/
│   ├── Auth/               # Fortify action classes
│   ├── Channel/            # Channel CRUD actions
│   ├── Message/            # Message send/edit/delete actions
│   ├── Workspace/          # Workspace CRUD actions
│   └── ...
├── Events/                 # Broadcasting event classes
│   ├── MessageSent.php
│   ├── ReactionAdded.php
│   └── ...
├── Http/
│   ├── Controllers/
│   │   ├── ChannelController.php
│   │   ├── MessageController.php
│   │   ├── WorkspaceController.php
│   │   ├── SearchController.php
│   │   └── ...
│   └── Requests/           # Form request validation
├── Listeners/              # Event listeners (notifications, etc.)
├── Models/
│   ├── User.php
│   ├── Workspace.php
│   ├── Channel.php
│   ├── Message.php
│   ├── Reaction.php
│   ├── Attachment.php
│   ├── Bookmark.php
│   └── Notification.php
├── Notifications/          # Notification classes
├── Policies/               # Authorization policies
└── Providers/
```

### 5.2 Frontend Structure

```
resources/js/
├── components/
│   ├── ui/                 # Shared UI primitives (Radix + Tailwind)
│   ├── chat/
│   │   ├── MessageList.tsx
│   │   ├── MessageItem.tsx
│   │   ├── MessageInput.tsx
│   │   ├── ThreadPanel.tsx
│   │   └── TypingIndicator.tsx
│   ├── sidebar/
│   │   ├── WorkspaceSwitcher.tsx
│   │   ├── ChannelList.tsx
│   │   ├── DMList.tsx
│   │   └── SearchBar.tsx
│   └── ...
├── hooks/
│   ├── useChannel.ts
│   ├── useMessages.ts
│   ├── usePresence.ts
│   ├── useTyping.ts
│   └── useNotifications.ts
├── layouts/
│   ├── AppLayout.tsx       # Main app shell (sidebar + content)
│   └── AuthLayout.tsx      # Login/register pages
├── pages/
│   ├── channels/
│   │   ├── index.tsx       # Channel browser
│   │   ├── show.tsx        # Channel view
│   │   └── create.tsx      # Create channel form
│   ├── messages/
│   │   └── show.tsx        # DM conversation view
│   ├── bookmarks/
│   │   └── index.tsx       # Bookmarks list
│   ├── search/
│   │   └── index.tsx       # Search results
│   ├── settings/
│   │   ├── profile.tsx
│   │   └── workspace.tsx
│   └── dashboard.tsx
├── types/
│   ├── models.ts           # TypeScript interfaces for all models
│   └── index.d.ts
└── lib/
    ├── utils.ts            # Helper functions
    └── constants.ts        # App constants
```

### 5.3 Route Design

```
GET     /                              → Dashboard/Home
GET     /dashboard                     → Workspace dashboard
GET     /workspaces/create             → Create workspace form
POST    /workspaces                    → Store workspace
GET     /workspaces/{workspace}        → Switch to workspace
GET     /channels                      → Channel browser
GET     /channels/create               → Create channel form
POST    /channels                      → Store channel
GET     /channels/{channel}            → Channel view (messages)
POST    /channels/{channel}/messages   → Send message
PATCH   /channels/{channel}/messages/{message} → Edit message
DELETE  /channels/{channel}/messages/{message} → Delete message
POST    /channels/{channel}/join       → Join channel
DELETE  /channels/{channel}/leave      → Leave channel
POST    /channels/{channel}/pin/{message}      → Pin message
DELETE  /channels/{channel}/pin/{message}      → Unpin message
POST    /channels/{channel}/messages/{message}/reactions → Add reaction
DELETE  /channels/{channel}/messages/{message}/reactions/{reaction} → Remove reaction
GET     /channels/{channel}/messages/{message}/thread → Thread view
POST    /channels/{channel}/messages/{message}/thread/replies → Reply in thread
GET     /messages/{conversation}       → DM conversation view
POST    /messages                      → Send/start DM
GET     /search                        → Search results
GET     /bookmarks                     → Bookmarks page
POST    /bookmarks                     → Add bookmark
DELETE  /bookmarks/{bookmark}          → Remove bookmark
POST    /attachments                   → Upload file
GET     /settings/profile              → Profile settings
PUT     /settings/profile              → Update profile
GET     /settings/workspace            → Workspace settings
PUT     /settings/workspace            → Update workspace
POST    /workspaces/{workspace}/invite  → Invite member
```

---

## 6. Security

### 6.1 Authentication

- Laravel Fortify handles registration, login, password reset, 2FA, and passkeys
- Session-based authentication via Laravel's built-in session guards
- Rate limiting on login attempts (throttle middleware)
- Email verification required before workspace access

### 6.2 Authorization

- **Workspace level:** Users can only access workspaces they belong to
- **Channel level:** Private channel messages only visible to channel members
- **Message level:** Only author can edit/delete their own messages (admins can delete any)
- **Policy classes** for each model to centralize authorization logic

### 6.3 Data Protection

- All passwords hashed with bcrypt via Hash facade
- CSRF tokens on all forms (Laravel default)
- Content Security Policy headers
- File upload validation: MIME type, file size limits, stored outside public directory
- XSS prevention: React escapes by default; server-side output encoding

---

## 7. Performance Considerations

| Concern | Strategy |
|---------|----------|
| Message loading | Cursor-based pagination (load older messages on scroll up) |
| Channel list | Cache unread counts in Redis; refresh via broadcast |
| User presence | Redis-based presence tracking; broadcast join/leave events |
| Typing indicators | Debounced WebSocket events; ephemeral (not persisted) |
| Search | Database full-text search (MySQL FULLTEXT index) |
| File uploads | Store on local disk (dev) or S3-compatible storage (prod) |
| Avatar caching | Serve via CDN or cache headers |

---

## 8. Testing Strategy

| Level | Tool | Scope |
|-------|------|-------|
| Unit | Pest | Model methods, helpers, policies |
| Feature | Pest | Controller actions, form requests, events |
| Browser | Pest + Laravel Dusk | Critical user flows (register, send message, search) |
| Static | Larastan | PHP type analysis |
| Refactoring | Rector Laravel | Automated Laravel-specific code upgrades and refactoring |
| Linting | Pint + ESLint | Code style enforcement |

### Test Organization

```
tests/
├── Feature/
│   ├── Auth/
│   │   ├── RegistrationTest.php
│   │   ├── LoginTest.php
│   │   └── TwoFactorTest.php
│   ├── Workspace/
│   │   ├── CreateWorkspaceTest.php
│   │   ├── InviteMemberTest.php
│   │   └── SwitchWorkspaceTest.php
│   ├── Channel/
│   │   ├── CreateChannelTest.php
│   │   ├── JoinChannelTest.php
│   │   └── ChannelMessagesTest.php
│   ├── Message/
│   │   ├── SendMessageTest.php
│   │   ├── EditMessageTest.php
│   │   ├── DeleteMessageTest.php
│   │   ├── ThreadReplyTest.php
│   │   └── ReactionTest.php
│   ├── Search/
│   │   └── SearchMessagesTest.php
│   └── Bookmark/
│       └── BookmarkTest.php
└── Unit/
    ├── Models/
    └── Policies/
```

---

## 9. Deployment

### 9.1 Environment Requirements

| Component | Requirement |
|-----------|-------------|
| PHP | 8.4+ |
| Node.js | 20+ |
| Database | MySQL 8+ or SQLite (dev) |
| Cache/Queue | Redis (for Horizon + broadcasting) |
| WebSocket | Laravel Reverb (replaces Pusher/Socket.io) |

### 9.2 Deployment Target

Laravel Cloud (recommended) or any platform supporting:
- PHP 8.4 runtime
- Node.js build step
- Redis
- WebSocket support (for Reverb)

### 9.3 Build Commands

```bash
# Production build
composer install --no-dev --optimize-autoloader
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
