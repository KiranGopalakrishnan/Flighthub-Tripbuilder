# Project Title

Tripbuilder API v1

## Docs

  Find the documentation for the project at https://levels8.com/flighthub/public/api

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes. See deployment for notes on how to deploy the project on a live system.

You can find the live deployed version of the project on https://levels8.com/flighthub/public/api

### Prerequisites

The repo is already up to date with the latest dependancies
But if you want to update the dependancies yourself run
```
composer update
```
on the project folder root.


### Installing

## Setting up the database
You can find the database credentials in src/classes/db.ini file.
Update the file according to your db credentials / Follow the default instalation routine provided below

# Installation using default db credentials

You need to create a MySql database with the name "trip_builder"
and import the file trip_builder.sql (Can be found on the project root folder)
into the database.

Once you are done with importing the sql file , Create a user as with the credentials specified in db.ini
on the database and grant all privillages to the user.

Once you are done , Push the files to the server

and you can access the project at www.yourdomain.com/public/api

Test your rpoject Installation by accessing the docs at www.yourdomain.com/public/api

End with an example of getting some data out of the system or using it for a little demo

## Deployment

Add additional notes about how to deploy this on a live system
