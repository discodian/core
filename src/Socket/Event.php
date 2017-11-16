<?php

/*
 * This file is part of the Discodian bot toolkit.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see http://discodian.com
 * @see https://github.com/discodian
 */

namespace Discodian\Core\Socket;

class Event
{
    // General
    const READY                = 'READY';
    const RESUMED              = 'RESUMED';
    const PRESENCE_UPDATE      = 'PRESENCE_UPDATE';
    const PRESENCES_REPLACE    = 'PRESENCES_REPLACE';
    const TYPING_START         = 'TYPING_START';
    const USER_SETTINGS_UPDATE = 'USER_SETTINGS_UPDATE';
    const VOICE_STATE_UPDATE   = 'VOICE_STATE_UPDATE';
    const VOICE_SERVER_UPDATE  = 'VOICE_SERVER_UPDATE';
    const GUILD_MEMBERS_CHUNK  = 'GUILD_MEMBERS_CHUNK';

    // Guild
    const GUILD_CREATE = 'GUILD_CREATE';
    const GUILD_DELETE = 'GUILD_DELETE';
    const GUILD_UPDATE = 'GUILD_UPDATE';

    const GUILD_BAN_ADD       = 'GUILD_BAN_ADD';
    const GUILD_BAN_REMOVE    = 'GUILD_BAN_REMOVE';
    const GUILD_MEMBER_ADD    = 'GUILD_MEMBER_ADD';
    const GUILD_MEMBER_REMOVE = 'GUILD_MEMBER_REMOVE';
    const GUILD_MEMBER_UPDATE = 'GUILD_MEMBER_UPDATE';
    const GUILD_ROLE_CREATE   = 'GUILD_ROLE_CREATE';
    const GUILD_ROLE_UPDATE   = 'GUILD_ROLE_UPDATE';
    const GUILD_ROLE_DELETE   = 'GUILD_ROLE_DELETE';

    // Channel
    const CHANNEL_CREATE = 'CHANNEL_CREATE';
    const CHANNEL_DELETE = 'CHANNEL_DELETE';
    const CHANNEL_UPDATE = 'CHANNEL_UPDATE';

    // Messages
    const MESSAGE_CREATE      = 'MESSAGE_CREATE';
    const MESSAGE_DELETE      = 'MESSAGE_DELETE';
    const MESSAGE_UPDATE      = 'MESSAGE_UPDATE';
    const MESSAGE_DELETE_BULK = 'MESSAGE_DELETE_BULK';
}
