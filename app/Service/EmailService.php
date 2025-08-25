<?php

namespace App\Service;

use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EmailService
{
    public function getData(string $connectionName, Request $request): LengthAwarePaginator
    {
        $perPage = $request->get('per_page', 15);
        $type = $request->get('type');
        $status = $request->get('status');
        $search = $request->get('search');

        $query = DB::connection($connectionName)->table('emails')->orderBy('created_at', 'desc');

        if ($type !== null) {
            $query->where('type', $type);
        }

        if ($status !== null) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $emails = $query->paginate($perPage);

        // Format the data
        $emails->getCollection()->transform(function ($email) {
            return [
                'id' => $email->id,
                'email' => $email->email,
                'content' => $email->content,
                'status' => $email->status,
                'status_text' => $email->status == 1 ? 'Success' : 'Failed',
                'type' => $email->type,
                'type_text' => $email->type == 1 ? 'Feedback' : 'Subscribe',
                'created_at' => Carbon::parse($email->created_at)->format('Y-m-d H:i:s'),
                'created_at_human' => Carbon::parse($email->created_at)->diffForHumans(),
            ];
        });

        return $emails;
    }

    public function getStats(string $connectionName): array
    {
        $db = DB::connection($connectionName);

        return [
            'total' => $db->table('emails')->count(),
            'feedback' => $db->table('emails')->where('type', 1)->count(),
            'subscribe' => $db->table('emails')->where('type', 2)->count(),
            'success' => $db->table('emails')->where('status', 1)->count(),
            'failed' => $db->table('emails')->where('status', 2)->count(),
            'today' => $db->table('emails')->whereDate('created_at', now()->toDateString())->count(),
            'this_week' => $db->table('emails')->whereBetween('created_at', [
                now()->startOfWeek(), now()->endOfWeek()
            ])->count(),
            'this_month' => $db->table('emails')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];
    }
}
