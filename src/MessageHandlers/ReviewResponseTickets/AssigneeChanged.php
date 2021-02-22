<?php

namespace Friendemic\MessageBroker\MessageHandlers\ReviewResponseTickets;

use Friendemic\Api\Response\ReviewResponseTicket;
use Friendemic\Api\Response\ReviewResponseTicketMessage\Topic;
use Friendemic\MessageBroker\MessageHandlers\BaseMessageHandler;
use Google\Protobuf\Internal\Message;

class AssigneeChanged extends BaseMessageHandler
{
    /**
     * AssigneeChanged constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->topic = Topic::name(Topic::REVIEW_RESPONSE_TICKET_ASSIGNEE_CHANGED);
    }

    /**
     * @return Message|ReviewResponseTicket
     */
    public function newProtobufInstance(): Message
    {
        return new ReviewResponseTicket();
    }
}
