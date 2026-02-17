# Surjence. Mindful News, Brewed Slowly

Surjence blends 'surj' (Armenian for coffee) with 'presence', a quiet ritual of reading the world with care.

Surjence is a newsreader that slows things down.
Instead of endless feeds, it offers just a few headlines each day, presented with space and warmth. 
The goal is to make reading the news feel like sharing a quiet cup of coffee, calm and complete when you are done.

## Core Belief

**Every headline deserves a breath.**

## Philosophy

Surjence is built as **calm technology**:
- ❌ No infinite scroll
- ❌ No urgency cues
- ❌ No dopamine mechanics
- ✅ Limited, intentional daily content
- ✅ Clear session ending
- ✅ Large typography and generous spacing

Surjence should feel like reading the news with a cup of coffee, not consuming a feed.

## Architecture

### Tech Stack

- **Backend**: Laravel 12 (PHP 8.2+)
- **Microservice**: Go 1.22 (Gin framework)
- **Frontend**: Vue.js 3 + Tailwind CSS
- **Database**: PostgreSQL
- **Cache**: Redis (optional, graceful fallback)
- **DevOps**: Docker + Docker Compose
- **CI/CD**: GitHub Actions (PHPStan, Laravel Pint, Go vet/lint)

### Services

1. **Go Microservice** (`go-service/`)
   - Fetches headlines from NewsData.io (free plan: 10,000 requests/month)
   - Filters articles by keywords: "mindfulness", "meditation", "mental health", "wellness"
   - Fetches full article content
   - Deduplicates headlines using hash comparison
   - Rule-based sentiment analysis
   - Keyword extraction (TF-IDF-like frequency)
   - Endpoint: `GET /api/v1/headlines/raw`

2. **Laravel Backend** (`backend/`)
   - Consumes Go service output
   - Attaches themes and reflections
   - Enforces daily limit (5-7 headlines)
   - Aggressive caching to respect rate limits
   - Endpoints:
     - `GET /api/v1/headlines`
     - `GET /api/v1/themes`

3. **Vue.js Frontend** (`frontend/`)
   - Mindful reading interface with calm technology principles
   - Coffee-inspired color palette (warm, soothing tones)
   - Large, readable typography (display, headline, body-large sizes)
   - Generous spacing and subtle transitions
   - No infinite scroll
   - Clear end state: "That's enough for today"
   - Minimalist card layout with soft shadows

## Prerequisites

- Docker and Docker Compose
- Make (optional, for convenience commands)
- NewsData.io free API key ([Get one here](https://newsdata.io/register))

## Quick Start

1. **Clone the repository**
   ```bash
   git clone git@github.com:dmitryravilov/surjence.git
   cd `surjence`
   ```

2. **Set up environment**
   ```bash
   # Root .env (for Docker Compose and Go service)
   cp .env.example .env
   # Edit .env and add your NEWSDATA_API_KEY
   
   # Laravel .env (for backend runtime)
   cp backend/.env.example backend/.env
   # Edit backend/.env for Laravel-specific settings (DB, Redis, Mail, etc.)
   ```

3. **Run setup**
   ```bash
   make setup
   ```

4. **Start services**
   ```bash
   make up
   ```

5. **Access the application**
   - Frontend: http://localhost:3000
   - Laravel API: http://localhost:8000
   - Go Service: http://localhost:8080

## Environment Variables

Surjence requires **two `.env` files**:

1. **Root `.env**  
   Located in the project root.  
   Used by Docker Compose to inject variables into all services.  
   Must include `NEWSDATA_API_KEY` for the Go microservice.
2. **backend/.env **
   Located in the `backend/` directory.  
   Required for Laravel itself.  
   Contains full Laravel configuration (database, Redis, mail, queues, etc.).

## Make Commands

- `make setup` - Initial setup (builds images, runs migrations, seeds database)
- `make build` - Build Docker images
- `make up` - Start all services
- `make down` - Stop all services
- `make restart` - Restart all services
- `make logs` - View logs from all services
- `make clean` - Stop services and remove volumes
- `make test` - Run PHPUnit tests

## API Documentation

OpenAPI/Swagger documentation is available in `backend/openapi.yaml`.

### Endpoints

#### GET /api/v1/headlines

Returns daily headlines with reflections and themes.

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Example Headline",
      "source": "Example News",
      "url": "https://example.com/article",
      "description": "Article description",
      "published_at": "2026-01-01T12:00:00Z",
      "sentiment": "neutral",
      "keywords": ["news", "example"],
      "theme": {
        "id": 1,
        "name": "General",
        "color": "#6366f1"
      },
      "reflection": "An update worth noting, without urgency."
    }
  ],
  "count": 1,
  "meta": {
    "daily_limit": 7,
    "fetched_at": "2026-01-01T12:00:00Z"
  }
}
```

#### GET /api/v1/themes

Returns all available themes.

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Technology",
      "description": "Tech news and innovations",
      "color": "#3b82f6",
      "headlines_count": 5
    }
  ],
  "count": 1
}
```

## Development

### Running Tests

```bash
make test
# or
docker-compose exec laravel php artisan test
```

### Database Migrations

```bash
docker-compose exec laravel php artisan migrate
```

### Seeding Database

```bash
docker-compose exec laravel php artisan db:seed
```

### Viewing Logs

```bash
make logs
# or for specific service
docker-compose logs -f laravel
docker-compose logs -f go-service
docker-compose logs -f frontend
```

## Hard Constraints

This project adheres to strict constraints:

- ✅ **NewsData.io FREE PLAN** - Uses free plan (10,000 requests/month)
- ✅ **Keyword-filtered content** - Focuses on mindfulness, meditation, mental health, wellness
- ✅ **Open-source software only** - No paid services
- ✅ **No analytics or tracking** - Privacy-first
- ✅ **Optimized for simplicity** - Low token usage, minimal dependencies

## Project Structure

```
surjence/
├── backend/              # Laravel application
│   ├── app/
│   │   ├── Http/Controllers/Api/
│   │   ├── Models/
│   │   └── Services/
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   └── tests/
├── go-service/           # Go microservice
│   └── main.go
├── frontend/             # Vue.js application
│   └── src/
│       ├── views/
│       └── App.vue
├── docker-compose.yml
├── Makefile
└── README.md
```

## Design Principles

1. **Calm Technology**: No urgency, no infinite scroll, clear boundaries
2. **Intentional Consumption**: Limited daily content (5-7 headlines)
3. **Mindful Reflections**: Each headline includes a thoughtful reflection
4. **Respect for Attention**: Large typography, generous spacing, subtle transitions
5. **Complete Sessions**: Clear end state encourages closing the app
6. **Coffee-Inspired Aesthetics**: Warm, soothing color palette that promotes calm
7. **Mindfulness Theme**: Dedicated theme category with soothing accent color (#A78BFA)

## License

MIT

## Contributing

This is a demonstration project of mindful software design. Contributions that align with the calm technology principles are welcome.

---

**Remember**: Every headline deserves a breath. Take your time. Close the app when you're done.
