<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Response;
use App\Services\Dashboard;

final class DashboardController
{
    public function index(): Response
    {
        $checklist = Dashboard::checklist();

        return view('admin/dashboard', [
            'user' => auth_user(),
            'summary' => Dashboard::contentSummary(),
            'inquiry' => Dashboard::inquiryStats(),
            'recentInquiries' => can('inquiries.view') ? Dashboard::recentInquiries(5) : [],
            'mediaCount' => Dashboard::mediaCount(),
            'homepage' => Dashboard::homepageStats(),
            'recent' => Dashboard::recentPages(5),
            'checklist' => $checklist,
            'checklistDone' => count(array_filter($checklist, static fn (array $i): bool => $i['done'])),
            'noindex' => Dashboard::isNoindex(),
        ]);
    }
}