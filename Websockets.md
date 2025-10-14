# WebSockets - Technical Overview

## What It Is
A persistent TCP connection between client and server that stays open. Unlike HTTP (request→response→close), WebSockets maintain a two-way pipe that never hangs up.

WebSockets are a communication protocol (like HTTP, HTTPS) that runs over TCP. Any client (browser, mobile app, server, CLI tool) can establish WebSocket connections. They're designed to stay open for bidirectional communication instead of closing after each request/response cycle.

## How It Works

### Connection Establishment
1. **Handshake**: Client sends HTTP request with "Upgrade: websocket" header
2. **Protocol Switch**: Server responds with 101 Switching Protocols
3. **Pipe Open**: Persistent connection established, both sides can send messages anytime
4. **Memory Cost**: Server allocates ~4-16KB RAM per connection

### Security Model
- **REVERB_APP_KEY**: Shared secret that acts as the password
- Anyone with this key can connect
- No additional authentication at connection level
- If key leaks: attacker can spam connections and exhaust server RAM

### Attack Vector
- Leaked key + server address = unlimited connection spam
- Each connection costs ~5KB RAM
- 10,000 rogue connections = ~50MB RAM consumed
- Server crashes due to memory exhaustion

## Example 1: Digital Signage Broadcaster (Current System)

### Setup
- 100 displays = 100 persistent connections to Reverb server
- 1 channel: `program-5` (broadcast channel all displays subscribe to)
- Each connection: ~5KB RAM

### Traffic Pattern
- Per display per minute:
  - 2 heartbeats
  - 2 messages
  - 1 content update
- Total: 5 messages/minute/display

### Actual Costs
- **RAM**: 100 displays × 5KB = 0.5MB constantly allocated
- **Message frequency**: 5 messages/minute × 100 displays = 500 messages/minute total
- **RAM per message**: 0.5MB ÷ 500 = 1KB RAM per actual message

### Update Distribution (Content Updates Only)
- Server calls `broadcast(new ProgramContentUpdate())` once per update
- Reverb replicates to all 100 connected displays
- Laravel app: 1 broadcast operation
- Reverb: handles fan-out to 100 connections
- Network: 1 content update from server → 100 displays receive it
- Efficiency: Server sends once, not 100 times

### The Trade-off
**Benefit**: One update reaches all displays instantly without 100 separate HTTP requests
**Cost**: 100 persistent connections consuming RAM 24/7 for occasional updates

### Alternative: HTTP Polling
- 100 displays × 3 requests/minute = 300 requests/minute
- Server handles 100 individual request/response cycles per update
- No persistent connections, no RAM overhead
- Increased server processing per update

## Example 2: 6v6 Online Shooter

### Setup
- 12 players = 12 persistent connections to game server
- Each connection: ~5KB RAM

### Channels
- `game-123`: All players receive game state
- `team-red`: Red team private chat
- `team-blue`: Blue team private chat
- `player-{id}`: Individual player messages

### Traffic Pattern
- Game loop broadcasts at 60Hz (60 updates/second)
- Player positions, health, actions sent continuously
- Total: 60 updates/sec × 12 players = 720 messages/second

### Actual Costs
- **RAM**: 12 players × 5KB = 0.06MB constantly allocated
- **Message frequency**: 720 messages/second
- **RAM per message**: 0.06MB ÷ 720 = 0.08KB RAM per message

### The Trade-off
**Benefit**: Real-time synchronization at 60Hz, impossible with HTTP polling
**Cost**: Justified - high message frequency makes persistent connections efficient

## Cost Comparison

| System | Connections | RAM Cost | Message Rate | RAM/Message | Efficiency |
|--------|------------|----------|--------------|-------------|------------|
| Digital Signage | 100 | 0.5MB | 500/min | 1KB | Medium |
| 6v6 Shooter | 12 | 0.06MB | 720/sec | 0.08KB | High |

## When to Use What: REST API vs GraphQL vs WebSockets

### REST API
**Use when:**
- Infrequent updates (< 1 request/minute per client)
- Client-initiated requests only
- Simple CRUD operations
- Stateless interactions
- Caching benefits from HTTP

**Example:** Display checks for config changes every 5 minutes

**Cost Model:**
- No persistent connections = 0 RAM overhead
- Server processes request only when called
- 100 displays × 1 request/5min = 20 requests/minute total
- Each request: connection→process→close

### GraphQL
**Use when:**
- Client needs specific data subsets
- Complex data relationships
- Multiple resources in one request
- Client controls response shape
- Avoiding over-fetching

**Example:** Display fetches program + items + media metadata in single query

**Cost Model:**
- Same as REST (HTTP request/response)
- Flexible queries reduce total requests
- Single endpoint vs multiple REST endpoints

### WebSockets
**Use when:**
- Server needs to push updates to clients
- Frequent bidirectional communication (> 1 message/minute)
- Real-time requirements (< 1 second latency)
- Broadcast to many clients simultaneously
- Event-driven architecture

**Example:** Display receives content updates pushed from server

**Cost Model:**
- Persistent connections = RAM overhead per client
- Server pushes updates without client polling
- 1 broadcast → N clients receive instantly

## Heuristic Decision Matrix

| Metric | REST API | GraphQL | WebSockets |
|--------|----------|---------|------------|
| Update frequency | < 1/min | < 1/min | > 1/min |
| Initiation | Client-pull | Client-pull | Server-push |
| Connection type | Stateless | Stateless | Stateful |
| RAM overhead | None | None | ~5KB per client |
| Latency | Polling delay | Polling delay | Instant |
| Best for | Occasional checks | Complex queries | Real-time push |
| Scales to | 100,000+ clients | 100,000+ clients | ~10,000 clients |

## Current System Analysis

**Your Setup:** 100 displays, 500 messages/minute total (5 per display)

### If Using REST API:
- 100 displays poll every 12 seconds = 500 requests/minute
- Server handles 500 individual request/response cycles
- 0 RAM overhead for connections
- ~12 second average latency per update

### If Using GraphQL:
- Same as REST but with flexible queries
- Display fetches only needed fields
- Reduces payload size, not request count

### If Using WebSockets (Current):
- 0.5MB RAM for persistent connections
- Server broadcasts once, Reverb fans out to 100 displays
- < 1 second latency per update
- 1 broadcast operation vs 100 HTTP responses

## Conclusion

**WebSockets are efficient when**: Message frequency justifies the persistent connection overhead (> 1/min per client)

**WebSockets are overkill when**: Updates are infrequent and connection sits idle most of the time (< 1/min per client)

**Current system reality**: 500 messages/minute across 100 displays with 1KB RAM cost per message - reasonable efficiency for moderate-frequency updates

**Key insight**: The central channel architecture (1 broadcast → 100 displays) is efficient for distribution. At 5 messages/minute per display, WebSockets are justified over REST polling

## Files Involved

### WebSocket Establishment
- `backend/config/broadcasting.php` (line 18, 33-47): Defines broadcast driver and Reverb connection config
- `backend/.env` (line 35-41): Contains `BROADCAST_DRIVER=reverb` and Reverb credentials

### Broadcasting Events
- `backend/app/Events/ProgramContentUpdate.php:12`: Main content update event
  - `broadcastOn()` (line 48): Returns channel `program-{programId}`
  - `broadcastAs()` (line 55): Returns event name `content-update`
  - `broadcastWith()` (line 61): Returns signed message data

### Scheduler
- `backend/app/Services/ContentSchedulerService.php:141`: Calls `broadcast(new ProgramContentUpdate())`

### Message Signing
- `backend/app/Services/MessageSigningService.php`: Signs messages with JWT before broadcast
