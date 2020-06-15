# Letters API

![Ameelio Letters Logo v2](./public/logo.png)

The JSON API for interacting with the Letters database.

**Please Note:** This is a very new project and is subject to change rapidly. Currently, this is meant to run alongside the Letters project, to provide an API that other clients can interact with easily, but in the future we hope to use this API to replace the current Letters functionality.

**For Documentation:** Documentation is hosted [here](https://ameeliodev.github.io/letters-api/).

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes.

### Prerequisites

* Install PHP 7.2+
* Install [Composer](https://getcomposer.org/)
* Install MySQL or MariaDB

### Installing

#### Setting Up the Database

Copy over the `.env` from your Letters project.

Then run `php artisan key:generate`.

There's no need to create a new DB. This API will use the one that already exists if you have previously set up the Letters project. If you haven't, please see [Letters](https://github.com/ameeliodev/letters).

#### Install Project Dependencies

Run the following to install dependencies

``` bash
composer install
php artisan key:generate
php artisan migrate
```

#### Starting the Project

To start the local development server run

``` bash
php artisan serve
```

Finally, visit http://localhost:8000/ in your browser and you should be greeted with the login screen. Hooray, you've successfully installed and run Ameelio Letters!

## Running the tests

Unit tests can be run with

``` bash
php artisan test
```

## Deployment

coming soon...


## Built With

* [Laravel](https://laravel.com/) - The web framework used
* [Composer](https://getcomposer.org/) - Dependency Management

## Contributing

Please read [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md) for details on our code of conduct, and [CONTRIBUTING.md](CONTRIBUTING.md) for the process for submitting pull requests to us.

## Authors

See the list of [contributors](https://github.com/AmeelioDev/letters/contributors) who participated in this project.

## License

This project is licensed under the GPLv3 License - see the [LICENSE.md](LICENSE.md) file for details

## Troubleshooting

coming soon...
