# WebSocket vs REST vs GraphQL

## The Core Difference

**REST**: Client asks, server responds, connection closes. Every interaction is a new HTTP request.

**GraphQL**: Client asks for exactly what it wants in one request, server responds with exactly that. Connection closes.

**WebSocket**: Client and server open a persistent two-way connection. Either side can send messages anytime.

---

## REST

### What It Actually Is
You make an HTTP request to a URL endpoint. Server sends back data. Connection ends.

```
GET /users/123 → { id: 123, name: "John", email: "..." }
GET /users/123/posts → [ { post1 }, { post2 } ]
```

### Use It When
- You're fetching or modifying resources (users, posts, products)
- You want browser/CDN caching to work automatically
- You need a public API that others will use

### Don't Use It When
- You need real-time updates from the server
- You're making 5+ requests to load one page
- Different clients need wildly different data shapes

### The Real Trade-off
REST is dead simple to implement and debug. But you get what the endpoint gives you - want user + posts + comments? That's 3 separate requests. Or you build a custom endpoint, now you have 100 endpoints.

---

## GraphQL

### What It Actually Is
Single endpoint. You send a query describing exactly what you need, you get exactly that back.

```
{
  user(id: 123) {
    name
    posts {
      title
      comments { author }
    }
  }
}
```

Gets you user + posts + comments in one request with only the fields you asked for.

### Use It When
- You have complex nested data (user → posts → comments → likes)
- Different clients need different fields (web vs mobile)
- You're tired of creating dozens of REST endpoints

### Don't Use It When
- Your data is simple and flat
- You need aggressive HTTP caching
- You're building a quick prototype

### The Real Trade-off
GraphQL solves the "too many requests" and "too much data" problems. But now you need to write resolvers, handle N+1 queries, implement your own caching layer, and prevent clients from writing expensive queries. It's more code upfront for flexibility later.

---

## WebSocket

### What It Actually Is
One persistent TCP connection. Server can send data whenever it wants without client asking.

```
Client → Server: "subscribe to room 5"
Server → Client: "new message in room 5" (pushed immediately)
```

### Use It When
- Server needs to push data to client instantly
- You're building chat, live notifications, collaborative editing
- You have frequent bi-directional communication

### Don't Use It When
- You just need to fetch data occasionally
- Your server is already struggling with resources
- You can live with polling every 5 seconds

### The Real Trade-off
WebSocket is the only way to get true server→client push. But every connected client holds an open connection, consuming server memory. 10,000 idle REST clients cost nothing. 10,000 idle WebSocket connections cost real RAM. Also harder to scale (need sticky sessions or Redis pub/sub).

---

## Direct Comparisons

### Getting User + Posts + Comments

**REST**: 3 separate network round trips
```
Device → Server → DB → Server → Device  (GET /users/123)
Device → Server → DB → Server → Device  (GET /users/123/posts)
Device → Server → DB → Server → Device  (GET /posts/456/comments)
```
On mobile 4G: 3 × 200ms = **600ms just waiting for network**

**GraphQL**: 1 network round trip, server does multiple DB queries
```
Device → Server → DB → Server → DB → Server → DB → Server → Device

Query: { user(id: 123) { name, posts { title, comments { text } } } }
```
On mobile 4G: 1 × 200ms network + server DB work = **~230ms total**

**Why GraphQL wins**: Network latency (device↔server) is way slower than server↔database. Even if GraphQL makes the server work harder, you save time by avoiding multiple slow network round trips.

**WebSocket**: Wrong tool. Don't use this for fetching data.

---

### Showing Live Stock Price Updates

**REST**: Poll every 1 second (wasteful, not real-time)
```
setInterval(() => fetch('/stock/AAPL'), 1000)
```

**GraphQL**: Still polling, just with GraphQL syntax. Subscriptions use WebSocket under the hood.

**WebSocket**: Server pushes price when it changes
```
socket.on('price-update', (data) => updateUI(data))
```

---

### Simple Blog API

**REST**: Perfect. `/posts`, `/posts/123`, `/posts/123/comments`

**GraphQL**: Overkill. You're writing resolvers for data that could be a simple JSON response.

**WebSocket**: Why?

---

## Scaling Reality

**REST**: Add more servers behind a load balancer. Done.

**GraphQL**: Same as REST, but watch out for expensive queries killing your DB.

**WebSocket**: Need sticky sessions (client must hit same server) OR Redis pub/sub so all servers share messages. More complicated.

---

## Caching Reality

**REST**: Free HTTP caching. CDNs understand it. `Cache-Control` headers work.

**GraphQL**: You have to implement it yourself. Each query is unique. Usually use DataLoader or Redis.

**WebSocket**: No caching. It's a live connection.

---

## When to Combine Them

Most real apps use multiple:

- **Chat app**: REST for loading history, WebSocket for live messages
- **Dashboard**: REST/GraphQL for initial data load, WebSocket for live updates
- **E-commerce**: REST for product catalog (cacheable), WebSocket for order status updates

---

## The Honest Take

**REST**: Use by default. Only switch if you have a specific problem REST can't solve.

**GraphQL**: Use when you have complex data relationships and multiple client types. Don't use it to look cool.

**WebSocket**: Use when you actually need real-time push. If polling every 5 seconds works, stick with REST.
