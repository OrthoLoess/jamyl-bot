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
