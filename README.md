# Letters API

![Ameelio Letters Logo v2](./public/logo.png)

The JSON API for interacting with the Letters database.

**Please Note:** This is a very new project and is subject to change rapidly. Currently, this is meant to run alongside the Letters project, to provide an API that other clients can interact with easily, but in the future we hope to use this API to replace the current Letters functionality.

**For Documentation:** Please check this repositories Wiki.

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes.

### Prerequisites

* Install PHP 7.2+
* Install [Composer](https://getcomposer.org/)
* Install MySQL or MariaDB

### Installing

#### Setting Up the Database

Create a MySQL database

``` mysql
CREATE DATABASE ameelio_letters;
```

Create a database user

``` mysql
CREATE USER letters IDENTIFIED BY 'ENTER A PASSWORD HERE';
```

Grand the user access to the database

``` mysql
GRANT ALL PRIVILEGES ON ameelio_letters.* TO 'letters'@'%';
```

Flush privileges so they take effect

``` mysql
FLUSH PRIVILEGES;
```

Exit the MySQL shell

``` mysql
exit
```

Change to the directory of the freshly cloned project and the copy the `.env.example` to `.env` in the same directory

``` bash
cd ~/Source/ameelio/letters
cp .env.example .env
```

Open the `.env` you just created in the projects root directory and change your DB_* values appropriately

```
DB_DATABASE=ameelio_letters
DB_USERNAME=letters
DB_PASSWORD=ENTER_PASSWORD_HERE
```

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

Code merged into master is automatically deployed by our [GitHub Actions](https://github.com/AmeelioDev/letters/actions?query=workflow%3A%22Deploy+Code%22).

## Accessing Admin Toolbox
If you're going to work on the Admin Toolbox, follow these steps:

1. Sign up as a user at /register
2. Using tinker, grant your user admin privileges
```bash
php artisan tinker
```
3. Access /toolbox

## Letters for Organizations Setup

If you're going to work on Letters for Organizations, follow these steps:

1. Sign up as a user at /register
2. Using tinker, grant your user admin privileges
```bash
php artisan tinker
```
```
$u = App\User::first(); // assuming it's the first user on the DB
$u->type="admin";
$u->save();
```
3. Access the /toolbox and create an organization
4. Create another account at register and select the organization name from the dropdown
5. Grant the organization user 'admin' privileges
```
php artisan tinker
$u = App\User::latest()->first();
$ou = App\OrgUser::where('user_id', $u->id);
$ou->role = "admin';
$ou->save();
```
6. Now the newly created user will have access to the organization admin dashboard at /organization

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

### Cannot `mysql -u root`

**NOTE: this will delete your existing database!**

First, run

``` bash
brew services stop mysql
sudo pkill mysqld
rm -rf /usr/local/var/mysql/
brew postinstall mysql
brew services restart mysql
mysql -u root
```

Then, run

``` mysql
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password
BY 'password';  

CREATE DATABASE ameelio_db;
```

Finally, run

``` bash
php artisan migrate
```

#
