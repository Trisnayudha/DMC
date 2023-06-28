<?php

namespace App\Services\Users;

use App\Models\User;

class UsersService extends User
{

    public static function showChartCategory($events_id = null)
    {
        // dd($events_id);
        $query = User::leftJoin('company', 'company.users_id', 'users.id')
            ->leftJoin('profiles', 'profiles.users_id', 'users.id')
            ->leftJoin('payment', 'payment.member_id', 'users.id')
            ->select('company.company_category', 'company.company_other');

        if ($events_id) {
            $query->where('payment.events_id', $events_id);
        }

        $users = $query->get();
        $categoryData = [];
        $otherCount = 0;
        $colorPalette = ['#ff6384', '#36a2eb', '#ffce56', '#4bc0c0', '#9966ff']; // Add more colors if needed

        foreach ($users as $user) {
            if ($user->company_category === 'other') {
                $category = !empty($user->company_other) ? $user->company_other : 'Other';
                $otherCount++;
            } elseif (!empty($user->company_category)) {
                $category = $user->company_category;
            } else {
                $category = !empty($user->company_other) ? $user->company_other : 'Other';
                $otherCount++;
            }

            if (isset($categoryData[$category])) {
                $categoryData[$category]++;
            } else {
                $categoryData[$category] = 1;
            }
        }

        // Prepare data for Chart.js
        $chartData = [
            'labels' => array_keys($categoryData),
            'datasets' => [
                [
                    'data' => array_values($categoryData),
                    'backgroundColor' => self::generateColorPalette(count($categoryData), $colorPalette),
                ],
            ],
        ];

        // Add 'Other' category count if applicable
        if ($otherCount > 0) {
            $chartData['labels'][] = 'Other';
            $chartData['datasets'][0]['data'][] = $otherCount;
        }

        return $chartData;
    }

    public static function showChartJobTitle($events_id)
    {
        $query = User::leftJoin('company', 'company.users_id', 'users.id')
            ->leftJoin('profiles', 'profiles.users_id', 'users.id')
            ->leftJoin('payment', 'payment.member_id', 'users.id')
            ->select('company.company_category', 'company.company_other', 'profiles.job_title');

        if ($events_id) {
            $query->where('payment.events_id', $events_id);
        }

        $users = $query->get();

        $jobTitles = $users->pluck('job_title')->toArray();

        $processedJobTitles = [];

        foreach ($jobTitles as $jobTitle) {
            if (is_string($jobTitle)) {
                $jobTitle = strtolower($jobTitle);

                // Remove common prefixes and suffixes from job titles
                $jobTitle = str_replace(['manager', 'supervisor', 'director', 'direktur', 'consultant'], '', $jobTitle);

                // Remove leading/trailing whitespaces
                $jobTitle = trim($jobTitle);

                // If the processed job title is not empty, add it to the list
                if ($jobTitle !== '') {
                    $processedJobTitles[] = $jobTitle;
                }
            }
        }

        $jobTitleCounts = array_count_values($processedJobTitles);

        $jobTitleData = [
            'labels' => [],
            'datasets' => [
                [
                    'data' => [],
                    'backgroundColor' => [],
                ],
            ],
        ];

        foreach ($jobTitleCounts as $jobTitle => $count) {
            $jobTitleData['labels'][] = ucfirst($jobTitle); // Capitalize the job title
            $jobTitleData['datasets'][0]['data'][] = $count;
            $jobTitleData['datasets'][0]['backgroundColor'][] = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
        }

        return $jobTitleData;
    }




    private static function generateColorPalette($count, $colorPalette)
    {
        $backgroundColor = [];

        for ($i = 0; $i < $count; $i++) {
            $colorIndex = $i % count($colorPalette);
            $backgroundColor[] = $colorPalette[$colorIndex];
        }

        return $backgroundColor;
    }
}
