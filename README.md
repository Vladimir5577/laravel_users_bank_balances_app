## Banking app

### Install in docker:
1. Copy .env.example to .env (optionally put you credentials inside).
```bash
$ cp .env.example .env
```

2. Build - for the first time.
```bash
$ docker-compose build 
```

3. run use -d flag for detach mode.
```bash
$ docker-compose up 
```

4. Install dependencies:
```bash
$ composer install
```

5. Go to database UI and create 2 databases name like in .env file 
    and for testing - banking_test (name in phpunit.xml). UI available:
    > type in browser - localhost:8087

6. Run migration.
```bash
$ php artisan migrate
```

7. Run seeder - optionally.
```bash
$ php artisan db:seed
```

8. Optionally generate app key
```bash
$ php artisan key:generate
```

## Postman
### For all request use Header - [Accept - application/json]

1. Add to accountPOST /api/deposit 
    > POST /api/deposit
body
```json
{
  "user_id": 1,
  "amount": 500.00,
  "comment": "Пополнение через карту"
}
```
2. Withdraw
    > POST /api/withdraw
body
```json
{
  "user_id": 1,
  "amount": 200.00,
  "comment": "Покупка подписки"
}
```
3. Transfer between users
    > POST /api/transfer
body
```json
{
  "from_user_id": 1,
  "to_user_id": 2,
  "amount": 150.00,
  "comment": "Перевод другу"
}
```
4. Get user balance
    > GET /api/balance/{user_id}
```json
{
  "user_id": 1,
  "balance": 350.00
}

```
## Run test
```bash
$ php artisan test
```
