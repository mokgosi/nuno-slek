# Slack-style Application — Entity Relationship Diagram

This ERD models the core data structures behind a Slack-like team messaging platform: workspaces, channels, members, messages, threads, reactions, and direct messages.

## Diagram

```mermaid
erDiagram
    WORKSPACE ||--o{ WORKSPACE_MEMBER : "has"
    USER ||--o{ WORKSPACE_MEMBER : "joins"
    WORKSPACE ||--o{ CHANNEL : "contains"
    CHANNEL ||--o{ CHANNEL_MEMBER : "has"
    USER ||--o{ CHANNEL_MEMBER : "joins"
    CHANNEL ||--o{ MESSAGE : "contains"
    USER ||--o{ MESSAGE : "authors"
    MESSAGE ||--o{ MESSAGE : "replies to (thread)"
    MESSAGE ||--o{ REACTION : "receives"
    USER ||--o{ REACTION : "gives"
    MESSAGE ||--o{ ATTACHMENT : "includes"
    USER ||--o{ CONVERSATION_MEMBER : "joins"
    CONVERSATION ||--o{ CONVERSATION_MEMBER : "has"
    CONVERSATION ||--o{ MESSAGE : "contains"
    WORKSPACE ||--o{ ROLE : "defines"
    ROLE ||--o{ WORKSPACE_MEMBER : "assigned to"

    WORKSPACE {
        uuid id PK
        string name
        string slug
        string domain
        datetime created_at
    }

    USER {
        uuid id PK
        string email
        string display_name
        string avatar_url
        string status
        boolean is_active
        datetime created_at
    }

    WORKSPACE_MEMBER {
        uuid id PK
        uuid workspace_id FK
        uuid user_id FK
        uuid role_id FK
        datetime joined_at
    }

    ROLE {
        uuid id PK
        uuid workspace_id FK
        string name
        json permissions
    }

    CHANNEL {
        uuid id PK
        uuid workspace_id FK
        string name
        string topic
        boolean is_private
        boolean is_archived
        uuid created_by FK
        datetime created_at
    }

    CHANNEL_MEMBER {
        uuid id PK
        uuid channel_id FK
        uuid user_id FK
        datetime joined_at
        datetime last_read_at
    }

    CONVERSATION {
        uuid id PK
        string type "dm or group_dm"
        datetime created_at
    }

    CONVERSATION_MEMBER {
        uuid id PK
        uuid conversation_id FK
        uuid user_id FK
        datetime joined_at
    }

    MESSAGE {
        uuid id PK
        uuid channel_id FK "nullable"
        uuid conversation_id FK "nullable"
        uuid user_id FK
        uuid parent_message_id FK "nullable, for thread replies"
        text body
        boolean is_edited
        datetime created_at
    }

    ATTACHMENT {
        uuid id PK
        uuid message_id FK
        string file_url
        string file_type
        int file_size
    }

    REACTION {
        uuid id PK
        uuid message_id FK
        uuid user_id FK
        string emoji
        datetime created_at
    }
```

## Entity Notes

- **WORKSPACE** — the top-level tenant/organization. All channels, members, and roles belong to exactly one workspace.
- **USER** — a person's account, shared across workspaces (a user can belong to multiple workspaces via `WORKSPACE_MEMBER`).
- **WORKSPACE_MEMBER** — join entity linking users to workspaces, carrying a `role_id` for permissions.
- **ROLE** — per-workspace permission sets (e.g. Owner, Admin, Member, Guest).
- **CHANNEL** — a public or private channel within a workspace.
- **CHANNEL_MEMBER** — join entity tracking channel membership and read state.
- **CONVERSATION** — a direct message or group DM thread, separate from channels since DMs aren't tied to a single channel row.
- **CONVERSATION_MEMBER** — join entity for DM/group DM participants.
- **MESSAGE** — a single message, belonging to either a channel or a conversation (not both). Supports threading via a self-referencing `parent_message_id`.
- **ATTACHMENT** — files/media attached to a message.
- **REACTION** — emoji reactions on a message, one row per user per emoji per message.

## Key Relationships

- A **Workspace** has many **Channels**, **Roles**, and **Members**.
- A **User** can be a member of many **Workspaces**, **Channels**, and **Conversations**.
- A **Message** belongs to exactly one of **Channel** or **Conversation**, and may optionally reply to another **Message** (thread).
- **Reactions** and **Attachments** both hang off **Message**, scoped one-to-many.

## Suggested Indexes / Constraints

- All primary and foreign keys use **UUID (v4)**. In Laravel, use `$table->uuid('id')->primary()` (or `HasUuids` trait on the model) instead of auto-incrementing IDs, and `$table->foreignUuid('workspace_id')->constrained()` for foreign keys.
- Unique constraint on (`workspace_id`, `slug`) for **WORKSPACE**.
- Unique constraint on (`channel_id`, `user_id`) for **CHANNEL_MEMBER**.
- Unique constraint on (`conversation_id`, `user_id`) for **CONVERSATION_MEMBER**.
- Unique constraint on (`message_id`, `user_id`, `emoji`) for **REACTION**.
- Check constraint ensuring **MESSAGE** has exactly one of `channel_id` or `conversation_id` set, not both.
- Index on `parent_message_id` in **MESSAGE** for fast thread lookups.
