<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AnnouncementRead;
use App\Models\Notification;
use App\Models\Team;
use App\Models\User;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommunicationController extends Controller
{
    public function announcements(Request $request)
    {
        $user = Auth::user();
        $query = Announcement::with('author', 'reads');

        if (!$user->isSuperAdmin()) {
            $query->where('status', 'published')
                ->where(function ($q) use ($user) {
                    $q->where('audience_type', 'all')
                      ->orWhere(function ($sub) use ($user) {
                          $sub->where('audience_type', 'role')->where('audience_target', $user->role);
                      })
                      ->orWhere(function ($sub) use ($user) {
                          $sub->where('audience_type', 'team')->where('audience_target', (string) $user->team_id);
                      });
                });
        }

        $announcements = $query->orderByDesc('publish_date')->paginate(15);
        $teams   = Team::all();
        $isAdmin = $user->isSuperAdmin();

        return view('communication.announcements', compact('announcements', 'teams', 'isAdmin'));
    }

    public function storeAnnouncement(Request $request)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Only Super Admin can publish announcements.');
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|in:company,urgent,event,policy,technical',
            'priority' => 'required|in:low,medium,high,urgent',
            'audience_type' => 'required|in:all,team,project,role,specific_user',
            'audience_target' => 'nullable|string',
            'publish_date' => 'required|date',
            'expiry_date' => 'nullable|date',
        ]);

        $data['author_id'] = Auth::id();
        $data['status'] = 'published';

        $announcement = Announcement::create($data);
        AuditLogger::log('create', 'Announcement', $announcement->id, null, $announcement->toArray(), "New announcement published: {$announcement->title}");

        return back()->with('success', 'Announcement broadcasted to targeted audience.');
    }

    public function markAnnouncementRead(Announcement $announcement)
    {
        AnnouncementRead::firstOrCreate([
            'announcement_id' => $announcement->id,
            'user_id' => Auth::id(),
        ], [
            'read_at' => Carbon::now(),
        ]);

        return back();
    }

    public function notifications()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('communication.notifications', compact('notifications'));
    }

    public function markNotificationRead(Notification $notification)
    {
        if ($notification->user_id === Auth::id()) {
            $notification->update(['is_read' => true, 'read_at' => Carbon::now()]);
        }
        return back();
    }

    public function markAllNotificationsRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => Carbon::now()]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
