<?php

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RA\Permissions;
use RA\TicketAction;
use RA\TicketState;

if (!authenticateFromCookie($username, $permissions, $userDetail)) {
    return back()->withErrors(__('legacy.error.permissions'));
}

$input = Validator::validate(request()->post(), [
    'ticket' => 'required|integer|exists:mysql_legacy.Ticket,ID',
    'action' => ['required', 'string', Rule::in(TicketAction::cases())],
]);

$ticketId = (int) $input['ticket'];

$ticketData = getTicket($ticketId);

$reason = null;
$ticketState = null;
switch ($input['action']) {
    case TicketAction::ClosedMistaken:
        $ticketState = TicketState::Closed;
        $reason = "Mistaken report";
        break;

    case TicketAction::Resolved:
        if ($permissions >= Permissions::Developer) {
            $ticketState = TicketState::Resolved;
        }
        break;

    case TicketAction::Demoted:
        if ($permissions >= Permissions::Developer) {
            $ticketState = TicketState::Closed;
            $reason = "Demoted";
        }
        break;

    case TicketAction::NotEnoughInfo:
        if ($permissions >= Permissions::Developer) {
            $ticketState = TicketState::Closed;
            $reason = "Not enough information";
        }
        break;

    case TicketAction::WrongRom:
        if ($permissions >= Permissions::Developer) {
            $ticketState = TicketState::Closed;
            $reason = "Wrong ROM";
        }
        break;

    case TicketAction::Network:
        if ($permissions >= Permissions::Developer) {
            $ticketState = TicketState::Closed;
            $reason = "Network problems";
        }
        break;

    case TicketAction::UnableToReproduce:
        if ($permissions >= Permissions::Developer) {
            $ticketState = TicketState::Closed;
            $reason = "Unable to reproduce";
        }
        break;

    case TicketAction::ClosedOther:
        if ($permissions >= Permissions::Developer) {
            $ticketState = TicketState::Closed;
            $reason = "See the comments";
        }
        break;

    case TicketAction::Request:
        $ticketState = TicketState::Request;
        break;

    case TicketAction::Reopen:
        $ticketState = TicketState::Open;
        break;
}

if (
    $ticketState !== null && $ticketState !== (int) $ticketData['ReportState']
    && ($permissions >= Permissions::Developer || $username == $ticketData['ReportedBy'])
) {
    updateTicket($username, $ticketId, $ticketState, $reason);

    return back()->with('success', __('legacy.success.update'));
}

return back()->withErrors(__('legacy.error.error'));
