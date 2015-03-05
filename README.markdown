# Clear
Build status: [![master](https://ci.studentrnd.org/app/rest/builds/buildType:(id:Clear_Master)/statusIcon)](https://ci.studentrnd.org/viewType.html?buildTypeId=Clear_Master&guest=1)

Clear is the software StudentRND uses to manage CodeDays. It's the place where event managers can do things like send emails to attendees, and through its API it serves as a sort of clearinghouse for event data.

## Requirements

 * PHP &ge; 5.5
 * Memcached
 * Beanstalkd
 * MySQL
 
Additionally, Clear requires you to have accounts with:
 
 * Stripe
 * s5
 * UPS (Developer Account)
 
It also works best if you have accounts with:
 
 * Bugsnag
 * Slack
 * Mandrill

## Getting Started

 1. Check out the repository to your web directory
 1. Customize your config file in `app/config/local.json`
 1. Create `app/storage/logs`, `app/storage/meta`, `app/storage/sessions`, and `app/storage/views`. Ensure the web server can write to each.
 1. Run `composer install` to install all the dependencies
 1. Run `php artisan migrate` to set up the database
 1. Run `php artisan db:seed` to set up some default regions and a default batch (you can change these later)
