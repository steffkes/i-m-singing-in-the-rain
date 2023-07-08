# Initial Setup 

## Build images

```sh
$ docker compose build
```

## setup backend dependencies

```sh
$ docker compose run backend bash
# composer install
```

## setup frontend dependencies

``` sh
$ docker compose run frontend ash
# pnpm install
```

ignore final error message (for now): 
> sh: nuxi: Text file busy
> ELIFECYCLE  Command failed with exit code 126.

should be fixed - but doesn't break anything obvious right now.

# Usage
 
``` sh
$ docker compose up backend
$ docker compose up frontend
```

## Backend

panel: http://localhost:4488/panel
user: `root@localost.tld`
pass: `Secret12!`

## Frontend

http://localhost:4400

> Doesn't display images, since it's directly accesing the configured kirby instance (assuming `backend` as its hostname)
