# Order App Kafka

A small study project that simulates an order pipeline with **two Laravel 13 APIs** communicating through **Apache Kafka**. Each service has its own PostgreSQL database and is containerized with Docker Compose.

### Flow 1 — Order placed

1. A client creates an order via the **Order service** (`POST /api/orders`).
2. `CreateOrder` persists the order and dispatches the `OrderPlaced` event.
3. `PublishOrderToKafka` publishes a JSON payload to the **`order.placed`** topic (message key: order id).
4. The **Shipping service** runs `kafka:consume-order-placed`, which consumes **`order.placed`** and `CreateShipment` inserts a shipment (tracking code, address from the payload, status `pending`).
5. On consumer failure, messages can be routed to **`order.placed-dlq`**; `kafka:process-order-placed-dlq` can reprocess them.

### Flow 2 — Shipment status updated

1. A client updates a shipment via the **Shipping service** (`PATCH /api/shipments/{shipment}`).
2. `UpdateShipmentStatus` updates the row and dispatches `ShipmentStatusUpdated`.
3. `ShipmentStatusUpdatedListener` publishes to **`shipment.updated`** (key: `order_id`).
4. The **Order service** runs `kafka:consume-shipment-status-updated`, which consumes **`shipment.updated`** and `UpdateOrderStatus` syncs the corresponding order’s status in its database.

### Topics and consumer groups

| Topic | Producer | Consumer | Notes |
| --- | --- | --- | --- |
| `order.placed` | Order service | Shipping service (`shipping_service_consumer`) | DLQ: `order.placed-dlq` |
| `shipment.updated` | Shipping service | Order service (`order_service_consumer`) | Manual commit after successful handling |

## Stack

- **PHP 8.4** (FPM) with **rdkafka** (`pecl rdkafka`) and **PostgreSQL** drivers
- **Laravel 13** with **[mateusjunges/laravel-kafka](https://github.com/mateusjunges/laravel-kafka)** (`^2.11`)
- **Kafka** (Confluent `cp-kafka` 7.6) + **ZooKeeper** (7.3.2)
- **Nginx** fronting each PHP app; **PostgreSQL 16** per service

## Repository layout

```
order-app-kafka/
├── docker-compose.yml      # All services + Kafka
├── order-service/          # Laravel: orders API, consumes shipment updates
├── shipping-service/       # Laravel: shipments API, consumes new orders
└── .docker/                # Persistent Postgres data (created at runtime)
```

## Prerequisites

- Docker and Docker Compose
- Ports **8001**, **8002**, **5432**, **5433**, **9092**, and **2181** available on the host

## Quick start

### 1. Start infrastructure

From the repository root:

```bash
docker compose up -d --build
```

### 2. Environment and application keys

Each Laravel app needs a `.env`. If you are starting fresh, copy the examples and generate keys:

```bash
cp order-service/.env.example order-service/.env
cp shipping-service/.env.example shipping-service/.env

docker compose exec order-app php artisan key:generate
docker compose exec shipping-app php artisan key:generate
```

### 3. Database (PostgreSQL in Compose)

Point both apps at the Compose databases (example values matching `docker-compose.yml`):

**`order-service/.env`**

```env
DB_CONNECTION=pgsql
DB_HOST=order-db
DB_PORT=5432
DB_DATABASE=order_service
DB_USERNAME=root
DB_PASSWORD=root

APP_URL=http://localhost:8001
KAFKA_BROKERS=kafka:9092
```

**`shipping-service/.env`**

```env
DB_CONNECTION=pgsql
DB_HOST=shipping-db
DB_PORT=5432
DB_DATABASE=shipping_service
DB_USERNAME=root
DB_PASSWORD=root

APP_URL=http://localhost:8002
KAFKA_BROKERS=kafka:9092
```

### 4. Migrations and sample data (order service)

```bash
docker compose exec order-app php artisan migrate
docker compose exec order-app php artisan db:seed
```

The order seeder creates a test user (`test@example.com`) and sample products so `POST /api/orders` can reference valid `user_id` and `product_id` values.

```bash
docker compose exec shipping-app php artisan migrate
```

### 5. Run Kafka consumers

Consumers are long-running Artisan commands. Run them in separate terminals (or use a process manager / extra Compose services):

**Order service** — syncs order status from `shipment.updated`:

```bash
docker compose exec order-app php artisan kafka:consume-shipment-status-updated
```

**Shipping service** — creates shipments from `order.placed`:

```bash
docker compose exec shipping-app php artisan kafka:consume-order-placed
```

**Shipping service** (optional) — replays failed messages from the DLQ:

```bash
docker compose exec shipping-app php artisan kafka:process-order-placed-dlq
```

Keep these processes running while you exercise the APIs.

## HTTP API (summary)

| Service | Base URL | Method | Endpoint | Purpose |
| --- | --- | --- | --- | --- |
| Order | `http://localhost:8001` | `POST` | `/api/orders` | Create order (body: `user_id`, `items[]` with `product_id`, `quantity`) |
| Shipping | `http://localhost:8002` | `PATCH` | `/api/shipments/{shipment}` | Update shipment `status` (enum: not `pending`) |

Health checks: `GET http://localhost:8001/up` and `GET http://localhost:8002/up`.

## Tests

Each service uses **Pest**. Inside the containers (or locally with matching PHP extensions):

```bash
docker compose exec order-app composer test
docker compose exec shipping-app composer test
```

## Kafka configuration

Broker list is read from `KAFKA_BROKERS` (default in `.env.example`: `kafka:9092` for Docker network DNS). Shared options live in `config/kafka.php` in each app (consumer group defaults, offset reset, compression, etc.). Producers use `acks=all` and idempotent sends via the shared `App\Kafka\Producers\KafkaProducer` class.

## Troubleshooting

- **Consumers cannot connect** — Ensure ZooKeeper and Kafka are healthy (`docker compose ps`) and `KAFKA_BROKERS` matches the `kafka` service hostname inside the Compose network.
- **Order API validation errors** — `user_id` and `product_id` must exist; run `php artisan db:seed` in the order service or create matching records.
- **Port clashes** — Adjust host port mappings in `docker-compose.yml` if `5432`, `8001`, or `8002` are already in use.
