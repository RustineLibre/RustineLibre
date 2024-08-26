<?php

declare(strict_types=1);

namespace App\Messages\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Link;
use App\Entity\Discussion;
use App\Entity\Repairer;
use App\Messages\State\NumberOfMessageNotReadForCustomerProvider;
use App\Messages\State\NumberOfMessageNotReadForDiscussionProvider;
use App\Messages\State\NumberOfMessageNotReadForRepairerProvider;

#[ApiResource]
#[Get(
    uriTemplate: '/customers/messages_unread',
    security: 'is_granted("IS_AUTHENTICATED_FULLY")',
    provider: NumberOfMessageNotReadForCustomerProvider::class,
)]
#[Get(
    uriTemplate: '/repairers/{repairer_id}/messages_unread',
    uriVariables: [
        'repairer_id' => new Link(
            fromProperty: 'id',
            fromClass: Repairer::class,
        ),
    ],
    security: 'is_granted("IS_AUTHENTICATED_FULLY") and user.isAssociatedWithRepairer(repairer_id)',
    provider: NumberOfMessageNotReadForRepairerProvider::class,
)]
#[Get(
    uriTemplate: '/messages_unread/{discussion_id}',
    uriVariables: [
        'discussion_id' => new Link(
            fromProperty: 'id',
            fromClass: Discussion::class,
        ),
    ],
    security: 'is_granted("MESSAGE_UNREAD_BY_DISCUSSION", discussion_id)',
    provider: NumberOfMessageNotReadForDiscussionProvider::class,
)]
final class MessageUnread
{
    public function __construct(
        public int $count,
    ) {
    }
}
