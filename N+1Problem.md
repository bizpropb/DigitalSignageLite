# The N+1 Problem

## The Archive Metaphor

Imagine you're an employee and your boss needs information about 100 clients.

**Bad Boss (N+1 Problem)**:
- Boss: "Go to the archive, find client #47's contract"
- You: *walks to archive, finds it, comes back*
- Boss: "Now go get client #28's contract"
- You: *walks to archive again, finds it, comes back*
- Boss: "Now go get client #91's contract"
- You: *walks to archive AGAIN...*

You make **100 trips to the archive** because the boss asks one at a time.

**Good Boss (Batching)**:
- Boss: "Go to the archive, bring me contracts for clients #47, #28, #91, and these 97 others"
- You: *makes ONE trip, grabs all 100 contracts, comes back*

**One trip instead of 100.** That's the N+1 problem in a nutshell.

---

## What It Is (Technical)

You need to fetch a list of items and related data for each item. Instead of doing it efficiently in 1-2 queries, you end up doing **1 query + N queries** (one per item).

**Example**: Fetch 100 users and their posts.
- 1 query: Get all users
- 100 queries: Get posts for each user (once per user)
- Total: **101 queries** instead of 2

The database is the archive. Each query is a trip. Don't be the bad boss.

---

## In SQL

### The Problem

```javascript
// Get all users
const users = db.query('SELECT * FROM users')  // 1 query

// Get posts for each user
for (const user of users) {
  user.posts = db.query('SELECT * FROM posts WHERE user_id = ?', [user.id])  // N queries
}
```

If you have 100 users: **1 + 100 = 101 queries**

### The Solution: JOIN

```sql
SELECT users.*, posts.*
FROM users
LEFT JOIN posts ON users.id = posts.user_id
```

**1 query total**. Database does the work efficiently.

---

## In NoSQL (MongoDB)

### The Problem

```javascript
// Get all users
const users = await User.find()  // 1 query

// Get posts for each user
for (const user of users) {
  user.posts = await Post.find({ userId: user._id })  // N queries
}
```

### The Solution: Aggregation Pipeline

```javascript
const users = await User.aggregate([
  {
    $lookup: {
      from: 'posts',
      localField: '_id',
      foreignField: 'userId',
      as: 'posts'
    }
  }
])
```

**1 query total**. MongoDB does the lookup.

Or use batch loading:

```javascript
const users = await User.find()  // 1 query
const userIds = users.map(u => u._id)
const posts = await Post.find({ userId: { $in: userIds } })  // 1 query

// Group posts by userId in JavaScript
const postsByUser = posts.reduce((acc, post) => {
  acc[post.userId] = acc[post.userId] || []
  acc[post.userId].push(post)
  return acc
}, {})

users.forEach(user => {
  user.posts = postsByUser[user._id] || []
})
```

**2 queries total** instead of 101.

---

## In REST

REST doesn't inherently have the N+1 problem because you design your endpoints.

### Bad Design (N+1 on client)

```javascript
// Client makes multiple requests
const users = await fetch('/users')  // 1 request

for (const user of users) {
  user.posts = await fetch(`/users/${user.id}/posts`)  // N requests
}
```

100 users = **101 HTTP requests**. Terrible.

### Good Design

Create an endpoint that returns everything:

```javascript
GET /users?include=posts

// Server does the JOIN/lookup internally, returns:
[
  { id: 1, name: "John", posts: [...] },
  { id: 2, name: "Jane", posts: [...] }
]
```

**1 HTTP request**. Server handles it efficiently.

**Trade-off**: Now you have custom endpoints. Want users with comments instead? Build `/users?include=comments`. This is why people move to GraphQL.

---

## In GraphQL

### The Problem (VERY EASY TO CREATE)

GraphQL makes N+1 **extremely easy to accidentally create**:

```graphql
{
  users {
    name
    posts {
      title
    }
  }
}
```

If your resolvers are naive:

```javascript
const resolvers = {
  Query: {
    users: () => db.query('SELECT * FROM users')  // 1 query
  },
  User: {
    posts: (user) => db.query('SELECT * FROM posts WHERE user_id = ?', [user.id])  // N queries!
  }
}
```

100 users = **101 queries**. GraphQL executes the `posts` resolver for each user independently.

### The Solution: DataLoader

```javascript
import DataLoader from 'dataloader'

const postLoader = new DataLoader(async (userIds) => {
  // Batch load all posts for all users at once
  const posts = await db.query('SELECT * FROM posts WHERE user_id IN (?)', [userIds])

  // Group by userId
  const postsByUser = posts.reduce((acc, post) => {
    acc[post.user_id] = acc[post.user_id] || []
    acc[post.user_id].push(post)
    return acc
  }, {})

  // Return in same order as userIds
  return userIds.map(id => postsByUser[id] || [])
})

const resolvers = {
  User: {
    posts: (user) => postLoader.load(user.id)  // Batched automatically
  }
}
```

DataLoader batches all the `posts` requests into one query: **2 queries total** (1 for users, 1 batched query for all posts).

---

## Summary

| Technology | N+1 Risk | Solution |
|------------|----------|----------|
| **SQL** | Medium - if you loop instead of JOIN | Use JOINs |
| **NoSQL** | Medium - if you loop instead of aggregate | Use `$lookup` or batch queries |
| **REST** | Low - you control endpoints | Design endpoints to return nested data |
| **GraphQL** | **HIGH** - default behavior causes N+1 | Use DataLoader or similar batching |

---

## Why GraphQL Is More Susceptible

With REST, you consciously design each endpoint. You decide what data it returns.

With GraphQL, resolvers execute **independently** and **automatically**. You can easily write a resolver that queries the database without realizing it'll be called 100 times.

**GraphQL doesn't cause N+1, but it makes it much easier to accidentally create it.**

---

## Real Performance Impact

**Without batching** (N+1):
- 101 database queries
- Each query: ~5ms
- Total: **505ms**

**With batching**:
- 2 database queries
- Total: **10ms**

That's a **50x performance difference**.

On a high-traffic API, N+1 queries can kill your database.
