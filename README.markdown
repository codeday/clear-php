# Clear

[![Deployment status from DeployBot](https://srnd.deploybot.com/badge/66802254112581/110470.svg)](http://deploybot.com)

Named for its function as a clearinghouse for event-related data, Clear is largely a glorified database frontend with features specifically designed to reduce the amount of busywork involved in managing your event.

Tasks on Clear largely fall into two categories: changing event configuration, and managing attendees. Some example tasks:

- See your current registrations and statistics; download an attendee waiver; and partially refund an attendee.
- Add a venue and open registrations.
- Send an email to all attendees, all past attendees, or anyone who's signed up for notifications.
- Generate a promo or scholarship code.
- Add a sponsor.
- Set your shipping address for CodeDay supplies.
- On the morning-of, check in attendees and get an auto-generated kickoff deck.
- Delegate access to any of these features to a volunteer.

## Requirements

- PHP &ge; 7.0.0
- Composer
- Memcached
- Redis
- A Raygun account (optional, used for error reporting)

## Deploying with Docker

We recommend using `docker-compose` to set everything up; a sample `docker-compose.yml` is included.
Make sure to create `config/local.json`. If you are using our `docker-compose.yml` file, this can get you
set up pretty quickly:

```bash
git clone https://github.com/srnd/Clear.git clear
cd clear
cp config/local.sample.json config/local.json # You will need to edit this file (remove the comments and set the vars)
cp docker-compose.sample.yml docker-compose.yml
docker-compose build
docker-compose up -d
docker exec -it clear_app_1 php artisan migrate # type "yes" when prompted
# Visit localhost:1337
```

<a href="https://raygun.com/products/crash-reporting/">Error and crash reporting software</a> provided by <a href="https://raygun.com/">Raygun</a>

<img width="200" src="http://i.imgur.com/yuoIAvf.png"></img>
