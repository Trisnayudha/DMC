<?php

namespace App\Services\Users;

use App\Models\User;

class UsersService extends User
{

    public static function showChartCategory($events_id = null)
    {
        $query = User::leftJoin('company', 'company.users_id', 'users.id')
            ->leftJoin('profiles', 'profiles.users_id', 'users.id')
            ->leftJoin('payment', 'payment.member_id', 'users.id')
            ->select('company.company_category', 'company.company_other');

        if ($events_id) {
            $query->where('payment.events_id', $events_id);
            $query->where('payment.status_registration', 'Paid Off');
        }

        $users = $query->get();
        $categoryData = [];
        $otherCount = 0;
        $colorPalette = ['#ff6384', '#36a2eb', '#ffce56', '#4bc0c0', '#9966ff']; // Add more colors if needed

        foreach ($users as $user) {
            $category = self::mapCategory($user->company_category, $user->company_other);
            if (isset($categoryData[$category])) {
                $categoryData[$category]++;
            } else {
                $categoryData[$category] = 1;
            }
        }

        $chartData = [
            'labels' => array_keys($categoryData),
            'datasets' => [
                [
                    'data' => array_values($categoryData),
                    'backgroundColor' => self::generateColorPalette(count($categoryData), $colorPalette),
                ],
            ],
        ];

        if ($otherCount > 0) {
            $chartData['labels'][] = 'Other';
            $chartData['datasets'][0]['data'][] = $otherCount;
        }

        return $chartData;
    }

    private static function mapCategory($category, $otherCategory)
    {
        $categoryMap = [
            'Coal Mining' => 'Category 1',
            'Minerals Producer' => 'Category 1',
            'Contractor' => 'Category 1',
            'Supplier/Distributor/Manufacturer' => 'Category 2',
            'Consultant' => 'Category 3',
            'Association / Organization / Government' => 'Consultan/Association/Organization/Goverment',
            'Logistics and Shipping' => 'Category 4',
            'Financial Services' => 'Category 4',
            'Investors' => 'Category 4',
            'Media' => 'Category 4',
            'other' => 'Category 5',
        ];
        // $categoryMap = [
        //     'Coal Mining' => 'Coal Mining/Minerals producer/Contractor',
        //     'Minerals Producer' => 'Coal Mining/Minerals producer/Contractor',
        //     'Contractor' => 'Coal Mining/Minerals producer/Contractor',
        //     'Supplier/Distributor/Manufacturer' => 'Supplier/Distributor/Manufaturer/Technology',
        //     'Consultant' => 'Consultan/Association/Organization/Goverment',
        //     'Association / Organization / Government' => 'Consultan/Association/Organization/Goverment',
        //     'Logistics and Shipping' => 'Service/Logistic/Shipping',
        //     'Financial Services' => 'Service/Logistic/Shipping',
        //     'Investors' => 'Service/Logistic/Shipping',
        //     'Media' => 'Service/Logistic/Shipping',
        //     'other' => 'Other',
        // ];
        if (isset($categoryMap[$category])) {
            return $categoryMap[$category];
        } elseif ($category === 'Other' || empty($otherCategory)) {
            return 'Category 5';
        }
    }

    private static function generateColorPalette($count, $colorPalette)
    {
        $paletteCount = count($colorPalette);

        if ($count <= $paletteCount) {
            return array_slice($colorPalette, 0, $count);
        } else {
            $additionalColors = $count - $paletteCount;
            $colors = array_merge($colorPalette, array_fill(0, $additionalColors, '#000000'));
            return $colors;
        }
    }


    public static function showChartJobTitle($events_id)
    {
        $query = User::leftJoin('company', 'company.users_id', 'users.id')
            ->leftJoin('profiles', 'profiles.users_id', 'users.id')
            ->leftJoin('payment', 'payment.member_id', 'users.id')
            ->select('company.company_category', 'company.company_other', 'profiles.job_title');

        if ($events_id) {
            $query->where('payment.events_id', $events_id);
            $query->where('payment.status_registration', 'Paid Off');
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




    // private static function generateColorPalette($count, $colorPalette)
    // {
    //     $backgroundColor = [];

    //     for ($i = 0; $i < $count; $i++) {
    //         $colorIndex = $i % count($colorPalette);
    //         $backgroundColor[] = $colorPalette[$colorIndex];
    //     }

    //     return $backgroundColor;
    // }
}
