# Jamyl Sarum Slack Bot

jamyl-bot is the management system and bot for ProviBloc Slack.

## Modules
#### User registration and checking
Uses EVE SSO for main login, then EVE API to check the character against standings. This checking is done regularly and the user's status is updated as needed.
An admin panel provides group and user management.

#### Slack account and group management
Sends invites to newly registered users, disables accounts of those who lose access and controls group access on slack.

#### PingBot
Allows FCs to send pings to a variety of groups using a simple /slash command in slack.

#### DankBot - kill tracker
Pulls kills for a given corp from zkillboard and informs corp channel on slack of high value or solo kills.

## Contributors
Ortho Loess - Lead programmer  
Prozn Zanjoahir - Significant contributions to DankBot (including the concept) and the frontend of the web site.

## Installation
#### Requirements
- PHP >= 5.4 (tested on 5.5) with Mcrypt, OpenSSL, Mbstring, Tokenizer and JSON extensions.
- Mysql
- Redis (recommended)
- [Composer](https://getcomposer.org)

I hope to set this up to install with composer at some point, these instructions will work for now (hopefully).

#### Getting the code
- Fork the repo and copy it to your local testing environment.
- Make the changes below and commit to your fork.
- Pull to your production server with git and run deployment as needed (see below).

#### Setup
- Copy .env.example to .env and set the values inside as appropriate (see below)
- Copy config/standings_template.php to config/standings.php and put your own IDs in.
- Change the host in config/app/php to match your environment.
- Change the various settings in config/pingbot.php and config/slack.php files.
- Point your webserver to the public/ folder as the document root.

#### Deployment
Once the files from git are on a server and you have your DB config set in the .env file, you use composer to get the
rest of the packages that are needed to actually run the system.

    cd jamyl-bot
    composer install
    
Note: use composer install, rather than update on the production system, it will just install the exact versions
specified, rather than looking for updated ones.

Once that's done, you can use teh laravel command line tool (artisan) to set up the database:

    php artisan migrate
    
Extra hint: `php artisan serve` will serve your site on port 8000 of your dev machine.

If you want to use the database cache driver for pheal (EVE API), you will need to set up a table for it manually:

```
CREATE TABLE `phealng-cache` (
    `userId` INT(10) UNSIGNED NOT NULL,
    `scope` VARCHAR(50) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `args` VARCHAR(250) NOT NULL,
    `cachedUntil` TIMESTAMP NOT NULL,
    `xml` LONGTEXT NOT NULL,
    PRIMARY KEY (`userId`, `scope`, `name`, `args`)
)
COMMENT='Caching for PhealNG'
COLLATE='utf8_general_ci'
ENGINE=InnoDB;
```

### .env settings
This file is used to set any settings which shouldn't be commited to source control, either because they are specific
to a particular environment (e.g. database settings) or because they need to be kept secret (e.g. slack API tokens).

Most are fairly simple to understand, and the defaults that are present in teh example should be ok for a development
environment. The file drivers for cache adn sessions are not particularly suited to a production environment however.
If you can set up redis on your server, that is a good choice for both, otherwise database can work well enough, see 
the laravel docs for more details (settings other than file or redis will require further setup).

The pheal cache setting must be either database or redis. If it is set to database, you will need to create the table.

EVE_CLIENT_ID, EVE_CLIENT_SECRET and EVE_REDIRECT are the settings from eve SSO. The app is set to respond to redirects
sent to yourdomain.com/callback

For communicating with slack, 3 things are needed. You will need to have a slack account that is used for access control.
This account needs to be an admin on slack and will need to be in every private groups that is controlled by the program.
It must be a full user, not a bot. SLACK_API_TOKEN is this users api token from [the slack api website](https://api.slack.com/web).
The SLACK_POST_URL is for an incoming webhook integration. This should be set up by the user who's api token is being
used (so that it can post to all channels that user is in).

The SLACK_ADMIN_TOKEN is a little more complicated. It is the token that is used by the slack admin pages when using
the API. To find it, you can use the dev tools in your browser to look at requests while doing something like sending a
slack invite, or disabling a user, or you can find it in the source of the admin pages (look through the javascript that
is sent inline).

The next block of 3 tokens are given by the slash commands webhook. The one you are most likely to want is /ping, which
should point to yourdomain.com/sendping. Once this is set up in slack admin, it will give you a token, which should be
put into PING_TOKEN.

Other commands:
- `/register` points to yourdomain.com/registerslack - This can be used by a new user to skip waiting for the cron job to notice that they have registered.
- `/portrait` points to yourdomain.com/portrait - This returns the user's eve character portrait, potentially useful for setting avatars.

The final setting is SLACK_DOMAIN. This needs to be the domain actually serving the slack bot. I use a slack. subdomain, so this setting would be slack.yourdomain.com
