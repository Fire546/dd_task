# Patient & Appointments API (Laravel + Docker)

REST API для управления пациентами и приёмами (**Laravel 10**, **MySQL**, **php-fpm**, **nginx**).  
Документация API генерируется через **Swagger (l5-swagger)**.

---

## 📋 Содержание
- [Требования](#требования)
- [Установка без Docker](#установка-без-docker)
- [Запуск через Docker](#🚀-запуск-через-docker)
- [Включение и выключение Docker](#включение-и-выключение-docker)
- [Маршруты API](#маршруты-api)
- [Миграции и база данных](#миграции-и-база-данных)
- [Swagger](#swagger)


---

## Требования
- PHP 8.1+ (если запускать без Docker)
- Composer 2.x
- MySQL или SQLite
- Docker + Docker Compose (для контейнерного запуска)

---

## Установка без Docker

```bash
git clone git@github.com:Fire546/dd_task.git
cd dd_task

composer install
cp .env.example .env
Заполнить `.env` (минимально для Docker):
   ```env
   APP_NAME=Laravel
   APP_ENV=local
   APP_KEY=
   APP_DEBUG=true
   APP_URL=http://localhost:8000

   LOG_CHANNEL=stack
   LOG_LEVEL=debug

   DB_CONNECTION=mysql
   DB_HOST=mysql
   DB_PORT=3306
   DB_DATABASE=clinic
   DB_USERNAME=laravel
   DB_PASSWORD=secret
php artisan key:generate
php artisan migrate

php artisan serve
```

Приложение будет доступно по адресу: http://127.0.0.1:8000

---

## 🚀 Запуск через Docker

```bash
# 1) Собрать и запустить контейнеры
docker compose up -d --build

# 2) Зайти внутрь php-fpm контейнера
docker compose exec app bash

# 3) Выполнить команды Laravel
composer install
php artisan key:generate
php artisan migrate
exit
```

Приложение доступно: [http://localhost:8000](http://localhost:8000)  
MySQL доступен на порту `3307` (user: `laravel`, password: `secret`, db: `clinic`).

---

## Включение и выключение Docker

- Запуск контейнеров:
  ```bash
  docker compose up -d
  ```
- Пересборка контейнеров:
  ```bash
  docker compose up -d --build
  ```
- Остановка контейнеров:
  ```bash
  docker compose down
  ```

👉 При остановке (`down`) код проекта остаётся в папке, а данные MySQL сохраняются в volume `db_data`.

---

## Маршруты API

Все API доступны по префиксу `/api`:

### Patients
- `GET    /api/patients`
- `POST   /api/patients`
- `GET    /api/patients/{patient}`
- `PATCH  /api/patients/{patient}`
- `DELETE /api/patients/{patient}`
- `GET    /api/patients/{patient}/appointments`

### Appointments
- `GET    /api/appointments`
- `POST   /api/appointments`
- `GET    /api/appointments/{appointment}`
- `PATCH  /api/appointments/{appointment}`
- `DELETE /api/appointments/{appointment}`
- `POST   /api/appointments/{appointment}/cancel`

---

## Миграции и база данных

```bash
php artisan migrate
php artisan db:seed
```

---

## Swagger

Генерация:
```bash
php artisan l5-swagger:generate
```

Документация доступна по адресу:
http://localhost:8000/api/documentation
