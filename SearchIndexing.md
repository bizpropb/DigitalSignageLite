# Search Indexing & Elasticsearch

## What Is An Index?

Think of a book's index at the back:

```
Index:
  cats → pages 12, 47, 89
  dogs → pages 5, 23, 91
  rabbits → pages 34, 67
```

Instead of reading all 300 pages to find "cats", you look it up instantly.

**Database indexes work the same way.**

---

## Database Index (The Basics)

### Without Index

```sql
SELECT * FROM videos WHERE title LIKE '%cat%'
```

Database scans **every single row**:
```
Row 1: "Dog tricks" - no match
Row 2: "Cat fails" - MATCH!
Row 3: "Cooking tutorial" - no match
...
Row 800,000,000: "Gaming stream" - no match
```

**Time**: Could take minutes on large tables.

### With Index

```sql
CREATE INDEX idx_title ON videos(title)
SELECT * FROM videos WHERE title LIKE 'cat%'
```

Database has a pre-built lookup:
```
Index:
  "cat" → [row 2, row 45, row 892, row 1203, ...]
  "dog" → [row 1, row 78, row 234, ...]
```

**Time**: Milliseconds, even with millions of rows.

---

## The Trade-off

**Indexes make reads faster, writes slower.**

### Without Index
- **Insert**: Just add the row → fast
- **Search**: Scan all rows → slow

### With Index
- **Insert**: Add the row AND update the index → slower
- **Search**: Look up index → fast

**Rule of thumb**: Index columns you search frequently. Don't index everything.

---

## What Is Elasticsearch?

Elasticsearch is a **search engine** built on top of Apache Lucene. It's designed specifically for full-text search, unlike SQL databases which are designed for structured data.

### SQL Database vs Elasticsearch

| SQL (PostgreSQL, MySQL) | Elasticsearch |
|------------------------|---------------|
| Structured data (rows, columns) | Documents (JSON) |
| Exact matches work best | Fuzzy search, relevance scoring |
| `WHERE title = 'cat'` | Search "cat" finds "cats", "Cat", "c@t" |
| Fast for simple queries | Fast for complex text search |

---

## How Elasticsearch Indexing Works

### 1. You Add A Document

```json
POST /videos/_doc/1
{
  "title": "Cute cat fails compilation",
  "description": "Funny cats doing silly things",
  "tags": ["cats", "funny", "animals"],
  "views": 1000000
}
```

### 2. Elasticsearch Analyzes It

It breaks text into **tokens** (words):

```
Original: "Cute cat fails compilation"

Tokenization:
  1. Lowercase: "cute cat fails compilation"
  2. Split words: ["cute", "cat", "fails", "compilation"]
  3. Remove common words (optional): ["cute", "cat", "fails", "compilation"]
  4. Stem words (optional): ["cute", "cat", "fail", "compil"]
```

### 3. It Builds An Inverted Index

```
Index:
  "cute" → [doc1]
  "cat" → [doc1, doc3, doc5]
  "fail" → [doc1, doc7]
  "compilation" → [doc1, doc9]
```

### 4. When You Search

```
Search: "cat fails"

Elasticsearch:
  1. Tokenize search: ["cat", "fail"]
  2. Look up index:
     - "cat" → [doc1, doc3, doc5]
     - "fail" → [doc1, doc7]
  3. Find intersection: doc1 has both
  4. Score results by relevance
  5. Return ranked results
```

---

## Elasticsearch Features SQL Doesn't Have

### 1. Fuzzy Search

```
Search: "cta" (typo)
Elasticsearch finds: "cat" (close enough)
```

SQL would find nothing.

### 2. Relevance Scoring

```
Search: "cat videos"

Results ranked by:
  - How many times "cat" appears
  - If "cat" is in the title (higher weight)
  - How rare the word is (common words score lower)
  - Document popularity
```

SQL just returns matches, no ranking.

### 3. Autocomplete / Suggestions

```
You type: "ca"
Elasticsearch suggests: "cat", "car", "camera"
```

### 4. Synonyms

```
Search: "automobile"
Elasticsearch also finds: "car", "vehicle"
```

---

## When To Use Elasticsearch

### Use Elasticsearch When:
- Full-text search (searching articles, products, logs)
- Need fuzzy matching (typos, variations)
- Want relevance ranking
- Large amounts of text data
- Need fast autocomplete/suggestions

### Don't Use Elasticsearch When:
- Simple exact lookups (use SQL)
- Structured queries (joins, aggregations)
- Your data is small (< 10,000 items)
- You need ACID transactions

---

## Real-World Examples

### YouTube
- Videos indexed in Elasticsearch
- Search by title, description, tags
- Fuzzy matching: "tutrial" → "tutorial"
- Ranked by relevance + engagement

**Note**: YouTube's search is notortiously bad not because of indexing, but because their algorithm manipulates the search.
Directly querying YouTube's API (like NewPipe/Invidious does), gets you proper `LIKE '%search%'` results without algorithmic manipulation.

### E-commerce (Amazon)
- Products indexed
- Search: "wireless headphones"
- Finds: "Bluetooth earbuds", "wireless earphones"
- Filters: price, brand, rating

### Log Analysis (DevOps)
- Server logs indexed
- Search: "error 500"
- Find all errors across millions of log entries
- Filter by time, server, severity

---

## How Indexing Actually Works (Simplified)

### Inverted Index Structure

```
Document 1: "The quick brown fox"
Document 2: "The lazy brown dog"
Document 3: "Quick foxes are clever"

Inverted Index:
  "the" → [doc1:pos0, doc2:pos0]
  "quick" → [doc1:pos1, doc3:pos0]
  "brown" → [doc1:pos2, doc2:pos2]
  "fox" → [doc1:pos3, doc3:pos1]
  "lazy" → [doc2:pos1]
  "dog" → [doc2:pos3]
  "are" → [doc3:pos2]
  "clever" → [doc3:pos3]
```

When you search "quick fox":
1. Look up "quick" → [doc1, doc3]
2. Look up "fox" → [doc1, doc3]
3. Intersection → doc1, doc3 both have both words
4. Rank by how close words are, how often they appear, etc.

---

## Performance Numbers

### SQL Without Index
- 1 million rows
- Search time: **2-10 seconds** (full table scan)

### SQL With Index
- 1 million rows
- Search time: **10-50ms** (index lookup)

### Elasticsearch
- 1 million documents
- Full-text search: **10-100ms**
- Fuzzy search, ranking, highlights: **50-200ms**

---

## The Cost

### Storage
Indexes take up space. An Elasticsearch index can be **50-100% the size of your original data**.

100GB of documents → 50-100GB of index data = **150-200GB total**

### Memory
Elasticsearch loves RAM. It keeps indexes in memory for speed.

**Recommendation**: 64GB+ RAM for production systems with millions of documents.

### Complexity
SQL: One database, you're done.
Elasticsearch: Separate service, need to sync data, manage clusters, tune settings.

---

## Do You Need Elasticsearch?

### You Probably Don't Need It If:
- You have < 10,000 items
- Simple exact searches work (`WHERE title = 'cat'`)
- You're building a prototype

**Just use SQL with indexes. It's way simpler.**

### You Need It If:
- Full-text search across large text fields
- Fuzzy matching / typo tolerance
- Search millions of documents in real-time
- Need autocomplete / suggestions
- Building a search-heavy app (marketplace, content platform)

---

## Quick Comparison

| Need | Solution |
|------|----------|
| Search 1000 products by exact name | SQL with index |
| Search 1M products by name/description with typos | Elasticsearch |
| Search user by email | SQL with index |
| Search articles for keywords with ranking | Elasticsearch |
| Autocomplete product names | Elasticsearch |
| Filter products by price range | SQL |
| Search server logs for errors | Elasticsearch |
| Simple blog search (< 1000 posts) | SQL with `LIKE` |

---

## TL;DR

**Index**: Pre-built lookup table that makes searches fast.

**SQL Index**: Fast exact lookups. Add them to columns you search often.

**Elasticsearch**: Specialized search engine for full-text search, fuzzy matching, and relevance ranking. Overkill for small datasets, essential for search-heavy apps with lots of text.

**Your digital signage project**: Probably doesn't need Elasticsearch. SQL with a simple index (or even no index) is fine.
