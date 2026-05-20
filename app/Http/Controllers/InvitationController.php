<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvitationRequest;
use App\Models\Invitation;
use App\Models\User;
use App\Services\InvitationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvitationController extends Controller
{
    public function __construct(private readonly InvitationService $service) {}

    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $incoming = Invitation::query()
            ->forRecipient($user)
            ->with(['inviter:id,name,email,avatar_path', 'invitable'])
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        $sent = Invitation::query()
            ->forInviter($user)
            ->with(['invitee:id,name,email,avatar_path', 'invitable'])
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        $tab = $request->string('tab')->toString() === 'sent' ? 'sent' : 'incoming';

        return view('invitations.index', [
            'incoming' => $incoming,
            'sent' => $sent,
            'tab' => $tab,
        ]);
    }

    public function store(StoreInvitationRequest $request): JsonResponse
    {
        $invitable = $request->resolveInvitable();

        if ($invitable === null) {
            return response()->json(['message' => 'Target not found.'], 404);
        }

        $invitation = $this->service->send(
            inviter: $request->user(),
            invitable: $invitable,
            email: $request->string('email')->toString(),
            message: $request->string('message')->toString() ?: null,
            role: $request->string('role')->toString() ?: null,
        );

        $recipientStatus = $this->service->lastRecipientStatus ?? 'registered';
        $message = $recipientStatus === 'pending_registration'
            ? 'We sent an invitation email. They are not on ATLY yet, so we asked them to create an account first. The invitation will be linked automatically when they sign up.'
            : 'Invitation sent.';

        return response()->json([
            'message' => $message,
            'recipient_status' => $recipientStatus,
            'invitation' => $this->payload($invitation),
        ]);
    }

    public function accept(Request $request, Invitation $invitation): JsonResponse|RedirectResponse
    {
        $this->authorize('respond', $invitation);

        $this->service->accept($invitation, $request->user());

        return $this->respond($request, 'Invitation accepted.');
    }

    public function decline(Request $request, Invitation $invitation): JsonResponse|RedirectResponse
    {
        $this->authorize('respond', $invitation);

        $this->service->decline($invitation, $request->user());

        return $this->respond($request, 'Invitation declined.');
    }

    public function destroy(Request $request, Invitation $invitation): JsonResponse|RedirectResponse
    {
        $this->authorize('cancel', $invitation);

        $this->service->cancel($invitation, $request->user());

        return $this->respond($request, 'Invitation cancelled.');
    }

    private function respond(Request $request, string $message): JsonResponse|RedirectResponse
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => $message]);
        }

        return redirect()->route('invitations.index')->with('status', $message);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Invitation $invitation): array
    {
        return [
            'id' => $invitation->id,
            'status' => $invitation->status->value,
            'email' => $invitation->invitee_email,
            'role' => $invitation->role,
            'invitable_kind' => $invitation->invitableKind(),
            'invitable_label' => $invitation->invitableLabel(),
            'created_at' => $invitation->created_at?->toIso8601String(),
            'expires_at' => $invitation->expires_at?->toIso8601String(),
        ];
    }
}
