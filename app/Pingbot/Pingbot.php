<?php
/**
 * Providence Slack Pingbot
 * User: ed
 * Date: 29/04/15
 * Time: 14:34
 */

namespace JamylBot\Pingbot;


use GuzzleHttp\Client;

class Pingbot {

    protected $postUrl;
    protected $allowedChannels;
    protected $returnMessage = 'Unknown Error';

    public function __construct()
    {
        $this->postUrl = config('pingbot.post-url');
        $this->allowedChannels = config('pingbot.ping-allowed-channels');
    }

    public function ping($type, $message, $sender)
    {
        if (!$this->isValidPingType($type))
        {
            return false;
        }
        $payload = $this->makePayload($type, $message, $sender);
        if ($this->sendMessageToServer($payload))
        {
            $this->returnMessage = 'Ping sent';
            if (config('pingbot.ping-bots.'.$type.'.announce'))
            {
                $this->sendMessageToServer($this->announcementPayload($type, $sender));
            }
            return true;
        }
        return false;
    }

    protected function sendMessageToServer($payload)
    {
        $client = new Client();
        $client->get(config('pingbot.post-url'), ['json' => $payload]);
        return true;
    }

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

    protected function makeHelpText()
    {
        return "The ping bot is now multi-function!\n"
            ."Usage: /ping <type> [message]\n"
            ."Available types:\n"
            ."fc - Sends ping to p_fc_pings.\n"
            ."titan - Sends ping to the titan channel. Use this to request a bridge.\n"
            ."capfc - Sends ping to cap_fcs channel. Use to request cap support.";
    }

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

    protected function makeAttachment($message, $pingSettings)
    {
        return [[
            'text'      => $pingSettings['pre-text'].$message,
            'color'     => $pingSettings['color'],
            'title'     => $pingSettings['title'],
            'fallback'  => $message,
        ]];
    }

    protected function announcementPayload($type, $sender)
    {
        return [
            'username'  => config('pingbot.ping-bot-name'),
            'icon_emoji'=> config('pingbot.ping-bot-emoji'),
            'channel'   => config('pingbot.ping-announce-channel'),
            'text'      => $type.' ping sent by '.$sender,
        ];
    }

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

        if (!in_array($requestVars['token'], config('pingbot.slash-hashes')))
        {
            $this->returnMessage = 'Invalid token, are you trying to access from outside of Slack?';
            return false;
        }

        return true;
    }

    protected function parsePingType($message)
    {
        $messageArray = explode(" ", $message, 2);

        if (!isset($messageArray[1]))
        {
            return [
                'valid'     => false,
                'ping-type' => $messageArray[0]
            ];
        }

        return [
            'valid'         => $this->isValidPingType($messageArray[0]),
            'ping-type'     => $messageArray[0],
            'message'       => $messageArray[1]
        ];
    }

    protected function isValidPingType($type)
    {
        if (array_key_exists($type, config('pingbot.ping-bots'))){
            return true;
        }
        $this->returnMessage = 'First parameter must be ping type: fc or titan';
    }

}