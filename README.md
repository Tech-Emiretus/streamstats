# StreamStats
A simple program to view some statistics from the top 1000 streams on Twitch. It also shares the relationship between the top 1000 streams and the authenticated user's streams.


## Platform Requirements
- PHP 8.0+
- Node 15+

## Setup
### With Laravel Sail
```
composer install
sail up -d
sail artisan migrate:fresh
```

### Manual
```
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh
```

### Complete Twitch Setup
In your .env
```
TWITCH_CLIENT_ID={client_id}
TWITCH_CLIENT_SECRET={client_secret}
TWITCH_USER_REDIRECT_URL=/authenticate
```

### Frontend
```
npm install
npm run watch [for development] || npm run build [production]
```

## Refresh Top Streams
```
sail artisan top-streams:refresh

OR

php artisan top-streams:refresh
```

## Testing
Please make sure you update `phpunit.xml` to match your test database. `[default = streamstats]`
```
sail test [--parallel]

OR

php artisan test [--parallel]
```
