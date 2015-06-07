<?php
/**
 * Providence Slack Pingbot
 * User: ed
 * Date: 29/04/15
 * Time: 14:34
 */

namespace JamylBot\Pingbot;

use JamylBot\Userbot\SlackMonkey;

/**
 * Class Pingbot
 * @package JamylBot\Pingbot
 */
class Pingbot {

    /**
     * @var string
     */
    protected $postUrl;
    /**
     * @var array
     */
    protected $allowedChannels;
    /**
     * The text to be returned as the output of the slash command. If this default survives to be sent, then something
     * has gone wrong which is not handled well.
     *
     * @var string
     */
    protected $returnMessage = 'Unknown Error. Use /ping help for more info.';

    /**
     * @var SlackMonkey
     */
    protected $slack;

    /**
     * Load values from config.
     *
     * @param SlackMonkey $slack
     */
    public function __construct(SlackMonkey $slack)
    {
        $this->postUrl = config('pingbot.post-url');
        $this->allowedChannels = config('pingbot.ping-allowed-channels');
        $this->slack = $slack;
    }

    /**
     * Main function for building up a ping and sending it off. First checks if the type is valid.
     * Called after the input from the slash command has been parsed, or can be called by external scripts.
     *
     * @param String $type
     * @param String $message
     * @param String $sender
     *
     * @return bool
     */
    public function ping($type, $message, $sender)
    {
        if (!$this->isValidPingType($type))
        {
            return false;
        }
        $payload = $this->makePayload($type, $message, $sender);
        if ($this->slack->sendMessageToServer($payload))
        {
            $this->returnMessage = 'Ping sent';
            if (config('pingbot.ping-bots.'.$type.'.announce'))
            {
                $this->slack->sendMessageToServer($this->announcementPayload($type, $sender));
            }
            return true;
        }
        return false;
    }

    /**
     * Primary entry point for slash commands.
     *
     *
     * @param Array $requestVars
     *
     * @return string
     */
    public function processPingCommand($requestVars)
    {
        if ($this->authenticateSource($requestVars))
        {
            $messageArray = $this->parsePingType($requestVars['text']);
            if ($messageArray['ping-type'] == 'help')
            {
                return $this->makeHelpText();
            }
            $this->ping($messageArray['ping-type'], $messageArray['message'], $requestVars['user_name']);
        }
        return $this->returnMessage;
    }

    /**
     * Very basic help functionality. Triggered by sending /ping help.
     * Only outputs to the person who triggered teh command.
     *
     * Note: Will override a pingbot named "help"
     *
     * @return string
     */
    protected function makeHelpText()
    {
        return config('pingbot.help-text');
    }

    /**
     * Use values from the config file to generate teh payload of teh message to be sent to slack.
     * Calls makeAttachment() to offload some of the work, mostly just to make it a bit cleaner.
     *
     * @param String $type
     * @param String $message
     * @param String $sender
     *
     * @return array
     */
    protected function makePayload($type, $message, $sender)
    {
        $pingSettings = config('pingbot.ping-bots.'.$type);
        return [
            'username'      => config('pingbot.ping-bot-name'),
            'icon_emoji'    => config('pingbot.ping-bot-emoji'),
            'channel'       => $pingSettings['destination'],
            'text'          => 'Ping from '.$sender.' to <!channel>',
            'attachments'   => $this->makeAttachment($message, $pingSettings),
        ];
    }

    /**
     * Generate the attachment part of the slack message
     *
     * @param String $message
     * @param Array $pingSettings
     *
     * @return array
     */
    protected function makeAttachment($message, $pingSettings)
    {
        return [[
            'text'      => $pingSettings['pre-text'].$message,
            'color'     => $pingSettings['color'],
            'title'     => $pingSettings['title'],
            'fallback'  => $message,
        ]];
    }

    /**
     * For pings which also announce that the ping was sent in the sending channel, generates that message payload.
     *
     * The channel to announce to is not configurable on a per-pingbot basis.
     *
     * @param String $type
     * @param String $sender
     *
     * @return array
     */
    protected function announcementPayload($type, $sender)
    {
        return [
            'username'  => config('pingbot.ping-bot-name'),
            'icon_emoji'=> config('pingbot.ping-bot-emoji'),
            'channel'   => config('pingbot.ping-announce-channel'),
            'text'      => $type.' ping sent by '.$sender,
        ];
    }

    /**
     * Basic authentication of the source of the ping request. The token is sent by slack, set in the admin settings.
     * The channel ID is used to restrict the use fo teh /ping command to fc's by stopping it's use from other channels.
     *
     * @param Array $requestVars
     *
     * @return bool
     */
    protected function authenticateSource($requestVars)
    {
        if ( !( isset($requestVars['channel_id']) && isset($requestVars['token']) ) )
        {
            $this->returnMessage = 'Invalid call, are you trying to access from outside of Slack?';
            return false;
        }

        if (!in_array($requestVars['channel_id'], config('pingbot.ping-allowed-channels')))
        {
            $this->returnMessage = 'This command can only be used from the fc chat channel';
            return false;
        }

        if ($requestVars['token'] != config('pingbot.command-hash'))
        {
            $this->returnMessage = 'Invalid token, are you trying to access from outside of Slack?';
            return false;
        }

        return true;
    }

    /**
     * Splits the first word from the incoming message string and checks if it is the name of a configured pingbot.
     * Will gracefully handle missing information:
     *  - If no message is sent at all, it will simply not match a ping type and the error text will reflect that.
     *  - If a valid ping type is given with no ensuing message, a ping will be sent with no custom message.
     *
     * @param String $message
     *
     * @return array
     */
    protected function parsePingType($message)
    {
        $messageArray = explode(" ", $message, 2);

        if (!isset($messageArray[1]))
        {
            return [
                'valid'     => false,
                'ping-type' => $messageArray[0],
                'message'   => ''
            ];
        }

        return [
            'valid'         => $this->isValidPingType($messageArray[0]),
            'ping-type'     => $messageArray[0],
            'message'       => $messageArray[1]
        ];
    }

    /**
     * Use given $type string to compare against the ping types in the config.
     *
     * @param String $type
     *
     * @return bool
     */
    protected function isValidPingType($type)
    {
        if (array_key_exists($type, config('pingbot.ping-bots'))){
            return true;
        }
        $this->returnMessage = 'First parameter must be ping type. Use /ping help for more info';
        return false;
    }

}