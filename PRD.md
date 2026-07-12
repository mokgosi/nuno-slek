# Product Requirements Document (PRD)

**Project:** NUNO-SLACK
**Version:** 1.0
**Last Updated:** 2026-07-12

---

## 1. Overview

NUNO-SLACK is a real-time team communication platform that enables teams to collaborate through workspaces, channels, direct messages, and threaded conversations. The goal is to provide a fast, intuitive, and reliable messaging experience.

## 2. Target Users

- Development teams needing real-time communication
- Remote and distributed teams
- Organizations looking for a self-hosted Slack alternative
- Small-to-medium teams (1–500 users per workspace)

## 3. Core Features

### 3.1 Authentication & User Management

**Description:** Users can register, log in, and manage their accounts.

| Requirement | Acceptance Criteria |
|-------------|---------------------|
| Email + password registration | User receives verification email, account is inactive until verified |
| Login with email/password | User is redirected to dashboard on success; error on failure |
| Two-factor authentication (TOTP) | User can enable 2FA; must provide TOTP code on login when enabled |
| Passkey support (WebAuthn) | User can register and authenticate with passkeys |
| Password reset | User receives reset link via email; link expires after 60 minutes |
| Profile management | User can update name, email, avatar, timezone |
| Account deletion | User can delete their account; data is soft-deleted |

### 3.2 Workspaces

**Description:** Workspaces are isolated organizations. A user can belong to multiple workspaces.

| Requirement | Acceptance Criteria |
|-------------|---------------------|
| Create workspace | Creator becomes the workspace owner |
| Workspace settings | Owner can update name, icon, description |
| Invite members | Owner/admin can invite users by email |
| Workspace roles | Owner, Admin, Member roles with appropriate permissions |
| Switch workspaces | User can switch between workspaces from the sidebar |
| Leave workspace | Member can leave; owner cannot leave (must transfer ownership first) |
| Workspace icon/avatar | Users can upload a workspace icon (JPEG, PNG, max 2MB) |

### 3.3 Channels

**Description:** Channels are persistent chat rooms within a workspace.

| Requirement | Acceptance Criteria |
|-------------|---------------------|
| Create channel | Any member can create a public channel |
| Create private channel | Any member can create a private channel; visible only to invitees |
| Channel naming | Lowercase, hyphens allowed, 2–80 characters, unique per workspace |
| Channel description | Creator can set/update channel description |
| Join/leave channel | Members can join public channels; leave any channel |
| Invite to private channel | Channel members can invite others to private channels |
| Channel members list | View all members of a channel |
| Default channel | `#general` is created with every workspace; all members auto-join |
| Archive channel | Admins can archive channels (read-only, hidden from browse) |

### 3.4 Direct Messages

**Description:** 1:1 and group direct messages outside of channels.

| Requirement | Acceptance Criteria |
|-------------|---------------------|
| Start 1:1 DM | Click a user to start/continue a DM conversation |
| Group DM | Add up to 8 people to a group DM |
| DM list | Sidebar shows recent DM conversations, sorted by last activity |
| Close DM | User can remove a DM from their sidebar (does not affect the other party) |

### 3.5 Messaging

**Description:** Core messaging functionality across channels and DMs.

| Requirement | Acceptance Criteria |
|-------------|---------------------|
| Send message | Message appears instantly for all channel/DM members |
| Edit message | Author can edit own message; "edited" indicator shown |
| Delete message | Author or admin can delete a message |
| Rich text formatting | Support **bold**, *italic*, `code`, ~~strikethrough~~, links, code blocks |
| @mention user | `@username` highlights and notifies the mentioned user |
| @channel mention | `@channel` notifies all members of a channel |
| Message reactions | Users can add/remove emoji reactions to messages |
| Emoji picker | Full emoji picker for reactions and messages |
| Pin message | Members can pin/unpin important messages in channels |
| Message history | Infinite scroll with pagination (load older messages on scroll up) |
| Message timestamps | Show relative time (e.g., "2 hours ago") with absolute time on hover |
| Message grouping | Consecutive messages from the same user within 5 minutes are grouped |
| Empty state | Show helpful empty state when channel has no messages yet |

### 3.6 Threads

**Description:** Reply threads to keep discussions organized without cluttering the main channel.

| Requirement | Acceptance Criteria |
|-------------|---------------------|
| Reply in thread | Click reply on any message to open thread sidebar |
| Thread indicator | Main channel shows "X replies" indicator on threaded messages |
| Thread notifications | User is notified of new replies in threads they've participated in |
| Thread view | Sidebar panel shows full thread with parent message at top |

### 3.7 File Sharing

**Description:** Users can upload and share files in messages.

| Requirement | Acceptance Criteria |
|-------------|---------------------|
| Upload file in message | Drag-and-drop or click to attach; upload progress shown |
| Supported formats | Images (JPEG, PNG, GIF, WebP), documents (PDF, DOCX, TXT), archives (ZIP) |
| Max file size | 25 MB per file |
| Image preview | Images render inline in the message |
| File download | Click file to download |
| File list per channel | View all files shared in a channel |
| Avatar upload | Users can upload profile avatars (JPEG, PNG, max 2MB, auto-cropped to circle) |

### 3.8 Search

**Description:** Full-text search across workspace content.

| Requirement | Acceptance Criteria |
|-------------|---------------------|
| Search messages | Search bar finds messages matching query across user's channels |
| Search filters | Filter by channel, user, date range |
| Search results | Show message snippet with channel name, sender, and timestamp |
| Click to jump | Click result to navigate to the message in context |

### 3.9 Notifications

**Description:** Keep users informed of new activity.

| Requirement | Acceptance Criteria |
|-------------|---------------------|
| Unread indicator | Sidebar channels show unread badge with message count |
| @mention notification | User receives in-app notification when @mentioned |
| Channel notification badge | Bold channel name + unread count for channels with new messages |
| Notification preferences | User can set per-channel notification level: All, Mentions, Nothing |
| Browser notifications | Request permission; show desktop notification on new message (optional) |

### 3.10 User Presence

**Description:** Show user availability status.

| Requirement | Acceptance Criteria |
|-------------|---------------------|
| Online status | Green dot when user is online |
| Offline status | Gray dot when user has been inactive for 5+ minutes |
| Custom status | User can set a status message with optional emoji (e.g., "🍕 Lunch") |
| Clear status | Status auto-clears after a set duration or manually |

### 3.11 Bookmarks

**Description:** Save important messages for quick access.

| Requirement | Acceptance Criteria |
|-------------|---------------------|
| Bookmark message | Click bookmark icon on any message |
| Bookmarks list | Dedicated bookmarks page shows all saved messages |
| Remove bookmark | Click again to unbookamark |
| Jump to message | Click bookmarked message to navigate to its location |

---

## 4. Non-Functional Requirements

| Category | Requirement |
|----------|-------------|
| **Performance** | Messages delivered in < 200ms for online users via WebSocket |
| **Performance** | Page loads in < 1 second for authenticated users |
| **Scalability** | Support 500 concurrent users per workspace |
| **Reliability** | Message delivery with at-least-once semantics via queue retries |
| **Security** | All data encrypted in transit (HTTPS/WSS) |
| **Security** | CSRF protection, XSS prevention, rate limiting on auth endpoints |
| **Security** | Workspace isolation — users cannot access data from other workspaces |
| **Accessibility** | Keyboard navigable; WCAG 2.1 AA compliance for core flows |
| **Responsive** | Fully functional on desktop and tablet; mobile-optimized views |
| **Browser Support** | Chrome, Firefox, Safari, Edge (latest 2 versions) |

---

## 5. Out of Scope (v1.0)

The following features are explicitly excluded from the initial release but may be considered for future versions:

- Voice and video calls
- Screen sharing
- Bot integrations / webhooks
- Third-party app integrations (GitHub, Jira, etc.)
- Message scheduling
- Custom emoji (workspace-level)
- Data export / compliance features
- Mobile apps (iOS/Android)
- Guest / limited-access users

---

## 6. Success Metrics

| Metric | Target |
|--------|--------|
| Message delivery latency (p95) | < 200ms |
| Page load time (p95) | < 1s |
| User registration to first message | < 2 minutes |
| Test coverage | > 80% |
| Zero critical security vulnerabilities | 0 |

---

## 7. Milestones

| Phase | Features | Target |
|-------|----------|--------|
| **Phase 1** | Auth, Workspaces, Users, Channels, Messaging | Core MVP |
| **Phase 2** | Threads, File sharing, Reactions | Enhanced collaboration |
| **Phase 3** | Search, Notifications, Presence, Bookmarks | Full feature set |
| **Phase 4** | Polish, performance, accessibility pass | Production ready |
