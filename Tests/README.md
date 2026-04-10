# Run Tests local

## Requirements

* assure running mysql and having user with create database privileges
* chromedriver installed (or selenium grid)


## Setup

    composer install
    # prepare acceptance tests
    mkdir -p config/system
    cp Build/settings.php config/system
    .Build/bin/typo3 extension:setup
    mkdir -p .Build/Web/fileadmin/form_definitions && cp Tests/Acceptance/Fixtures/form_definitions/test-form.form.yaml .Build/Web/fileadmin/form_definitions/
    # run php webserver and chromedriver
    cp Build/router.php .Build/Web
    php -S 0.0.0.0:8080 -t .Build/Web/ .Build/Web/router.php &
    chromedriver --url-base=/wd/hub  --port=9515 &
    # create database with "_at" postfix
    mysql -e 'CREATE DATABASE IF NOT EXISTS foox_at;'

## Run tests

    php -d memory_limit=2G .Build/bin/codecept run Backend --env=local,classic -c Tests/codeception.yml

