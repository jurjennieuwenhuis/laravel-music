# My Album Collection

## Installation

Clone the repo locally:

```sh
git clone https://github.com/jurjennieuwenhuis/laravel-music.git music && cd music
```

Configure the DDEV environment and spin it up:

```sh
ddev config --project-type=laravel --docroot=public
ddev start
```

Install PHP dependencies:

```sh
ddev composer install
```

Generate application key:

```sh
ddev art key:generate
```

Run database migrations and seeders:

```sh
ddev art migrate:fresh --seed
```

Create a symlink to the storage:

```sh
ddev art storage:link
```

Install the assets

```sh
ddev npm install && ddev npm run build
```
