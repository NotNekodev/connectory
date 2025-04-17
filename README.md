# Connectory

## How 2 run

### Setup:

run the following commands in the right order to setup
```shell
php bin/console doctrine:database:create
```

### Run:

```bash
symfony server:start
```

### other commands

To migrate (so if you add / remove fields to the ORM): 
```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```