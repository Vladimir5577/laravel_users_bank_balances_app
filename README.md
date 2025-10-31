## Banking app

Install in docker:
> build - for the first time
```bash
$ docker-compose build 
```
run use id flag for detach mode

```bash
$ docker-compose up 
```
1. Copy .env.example to .env and put you credentials inside
2. Go to database UI and create database name like in .env file
    > UI available localhost:8087
3. Run migration and seeds
```bash
$ php artisan migrate
$ php artisan db:seed
```
4. Optionalli generate app key
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
