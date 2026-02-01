.PHONY: setup build up down restart logs clean test

setup: .env
	@echo "🚀 Setting up Surjence..."
	@if ! grep -q "NEWS_API_KEY=.*[^=]" .env 2>/dev/null || grep -q "NEWS_API_KEY=your_newsapi_key_here" .env; then \
		echo "⚠️  Please set NEWS_API_KEY in .env file (get one from https://newsapi.org)"; \
	fi
	@echo "📦 Building Docker images..."
	docker-compose build
	@echo "🔧 Starting services..."
	docker-compose up -d postgres redis go-service
	@echo "⏳ Waiting for services to be ready..."
	sleep 5
	@echo "🔨 Installing Laravel dependencies..."
	docker-compose exec -T laravel composer install --no-interaction || true
	@echo "🌱 Seeding database..."
	docker-compose exec -T laravel php artisan db:seed --force || true
	@echo "🔑 Generating application key..."
	docker-compose exec -T laravel php artisan key:generate --force || true
	@echo "✅ Setup complete! Run 'make up' to start all services."

.env:
	@if [ ! -f .env ]; then \
		cp .env.example .env; \
		echo "📝 Created .env file from .env.example"; \
		echo "⚠️  Please edit .env and add your NEWSDATA_API_KEY"; \
	fi

build:
	docker-compose build

up:
	docker-compose up -d
	@echo "✅ Services started. Frontend: http://localhost:3000"

down:
	docker-compose down

restart:
	docker-compose restart

logs:
	docker-compose logs -f

clean:
	docker-compose down -v
	@echo "🧹 Cleaned up volumes"

test:
	docker-compose exec laravel php artisan test
