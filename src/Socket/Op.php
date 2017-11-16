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

class Op
{
    // Dispatches an event.
    const DISPATCH = 0;
    // Used for ping checking.
    const HEARTBEAT = 1;
    // Used for client handshake.
    const IDENTIFY = 2;
    // Used to update the client presence.
    const PRESENCE_UPDATE = 3;
    // Used to join/move/leave voice channels.
    const VOICE_STATE_UPDATE = 4;
    // Used for voice ping checking.
    const VOICE_SERVER_PING = 5;
    // Used to resume a closed connection.
    const RESUME = 6;
    // Used to redirect clients to a new gateway.
    const RECONNECT = 7;
    // Used to request member chunks.
    const GUILD_MEMBER_CHUNK = 8;
    // Used to notify clients when they have an invalid session.
    const INVALID_SESSION = 9;
    // Used to pass through the heartbeat interval
    const HELLO = 10;
    // Used to acknowledge heartbeats.
    const HEARTBEAT_ACK = 11;
    ///////////////////////////////////////
    ///////////////////////////////////////
    ///////////////////////////////////////
    // Used to begin a voice WebSocket connection.
    const VOICE_IDENTIFY = 0;
    // Used to select the voice protocol.
    const VOICE_SELECT_PROTO = 1;
    // Used to complete the WebSocket handshake.
    const VOICE_READY = 2;
    // Used to keep the WebSocket connection alive.e
    const VOICE_HEARTBEAT = 3;
    // Used to describe the session.
    const VOICE_DESCRIPTION = 4;
    // Used to identify which users are speaking.
    const VOICE_SPEAKING = 5;
    ///////////////////////////////////////
    ///////////////////////////////////////
    ///////////////////////////////////////
    // Normal close or heartbeat is invalid.
    const CLOSE_NORMAL = 1000;

    const CLOSE_HEARTBEAT_ACK_MISSING = 1001;
    // Abnormal close.
    const CLOSE_ABNORMAL = 1006;
    // Unknown error.
    const CLOSE_UNKNOWN_ERROR = 1000;
    // Unknown opcode was went.
    const CLOSE_INVALID_OPCODE = 4001;
    // Invalid message was sent.
    const CLOSE_INVALID_MESSAGE = 4002;
    // Not authenticated.
    const CLOSE_NOT_AUTHENTICATED = 4003;
    // Invalid token on IDENTIFY.
    const CLOSE_INVALID_TOKEN = 4004;
    // Already authenticated.
    const CONST_ALREADY_AUTHD = 4005;
    // Session is invalid.
    const CLOSE_INVALID_SESSION = 4006;
    // Invalid RESUME sequence.
    const CLOSE_INVALID_SEQ = 4007;
    // Too many messages sent.
    const CLOSE_TOO_MANY_MSG = 4008;
    // Session timeout.
    const CLOSE_SESSION_TIMEOUT = 4009;
    // Invalid shard.
    const CLOSE_INVALID_SHARD = 4010;
}
