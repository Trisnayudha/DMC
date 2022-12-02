<?php

namespace App\Repositories;

use App\Models\News\News as NewsModel;
use Illuminate\Support\Facades\DB;

class News extends NewsModel
{
    public static function index($category_news = [])
    {
        $column_filter = "news.id";
        $type_filter = "desc";


        return DB::table('news')
            ->select(
                'news.id',
                'news.title',
                'news.slug',
                'news.image',
                'news.location',
                'news.date_news',
                'news.desc'
            )
            ->join('news_category_list', function ($join) use ($category_news) {
                if (!empty($category_news)) {
                    $join->on('news_category_list.news_id', '=', 'news.id');
                    $join->whereIn('news_category_list.news_category_id', $category_news);
                }
            })
            ->orderby($column_filter, $type_filter)
            ->paginate(10);
    }

    public static function listAllNews($search, $category_news = [], $filter = null)
    {
        $column_filter = "news.id";
        $type_filter = "desc";

        if ($filter == "sort-name-ascend") {
            $column_filter = "news.title";
            $type_filter = "asc";
        } elseif ($filter == "sort-name-descend") {
            $column_filter = "news.title";
            $type_filter = "desc";
        } elseif ($filter == "sort-date-ascend") {
            $column_filter = "news.created_at";
            $type_filter = "asc";
        } elseif ($filter == "sort-date-descend") {
            $column_filter = "news.created_at";
            $type_filter = "desc";
        }

        return DB::table('news')
            ->select(
                'news.id',
                'news.title',
                'news.slug',
                'news.image',
                'news.location',
                'news.date_news',
                'news.desc'
            )
            ->leftJoin('news_category_list', function ($join) use ($category_news) {
                $join->on('news_category_list.news_id', '=', 'news.id');
                if (!empty($category_news)) {
                    $join->whereIn('news_category_list.news_category_id', $category_news);
                }
            })
            ->where(function ($q) use ($search, $category_news) {
                if (!empty($search)) {
                    $q->where('news.title', 'LIKE', '%' . $search . '%')
                        ->orWhere('news.location', 'LIKE', '%' . $search . '%')
                        ->orWhere('news.desc', 'LIKE', '%' . $search . '%');
                }
                //                if (!empty($category_news)) {
                //                    $q->whereIn('news.news_category_id', $category_news);
                //                }
                $q->where('news.flag', 'Portal');
                $q->orWhere('news.all_highlight', 'Yes');
            })
            ->where(function ($q) {
                //                $q->whereNotNull('news_category_list.news_id');
            })
            ->groupBy('news.id')
            ->orderby($column_filter, $type_filter)
            ->paginate(10);
    }

    public static function paginateWithFilter($search, $tags, $category, $filter = null, $company = null)
    {
        $column_filter = "news.id";
        $type_filter = "desc";

        if ($filter == "sort-name-ascend") {
            $column_filter = "news.title";
            $type_filter = "asc";
        } else if ($filter == "sort-name-descend") {
            $column_filter = "news.title";
            $type_filter = "desc";
        } elseif ($filter == "sort-date-ascend") {
            $column_filter = "news.created_at";
            $type_filter = "asc";
        } elseif ($filter == "sort-date-descend") {
            $column_filter = "news.created_at";
            $type_filter = "desc";
        }

        $query = DB::table('news')
            ->select(
                'news.id',
                'news.title',
                'news.slug',
                'news.image',
                'news.location',
                'news.date_news',
                'news.views',
                'news.desc',
                'news.share',
                'news.last_update',
                'news.created_at',
                'company.name as company_name',
                'news_events.id as news_events_id'
            )
            ->leftJoin("company", function ($join) {
                $join->on('company.id', '=', 'news.company_id');
                $join->whereNotNull('company.id');
            })
            ->leftJoin('news_category_list', function ($join) use ($category) {
                $join->on('news_category_list.news_id', '=', 'news.id');
                if (!empty($category)) {
                    $join->whereIn('news_category_list.news_category_id', $category);
                }
            })
            ->leftJoin('news_tag_list', function ($join) {
                $join->on('news_tag_list.news_id', '=', 'news.id');
                $join->whereNotNull('news_tag_list.news_tag_id');
            })
            ->leftJoin('news_events', function ($join) {
                $join->on('news_events.news_id', '=', 'news.id');
            })
            ->where(function ($q) use ($search, $tags, $category, $company, $is_directory) {
                if (!empty($search)) {
                    $q->where('news.title', 'LIKE', '%' . $search . '%')
                        ->orWhere('news.desc', 'LIKE', '%' . $search . '%');
                }
                if (!empty($tags)) {
                    $q->whereIn('news_tag_list.news_tag_id', $tags);
                }
                //                if (!empty($category)) {
                //                    $q->whereIn('news.news_category_id', $category);
                //                }
                if (!empty($company)) {
                    $q->where('news.company_id', $company);
                }
                $q->where('news.flag', 'Company');
                if (!empty($company)) {
                    $q->where('news.highlight', 'Yes');
                }
            })
            ->groupBy('news.id')
            ->orderby($column_filter, $type_filter)
            ->orderby('company.type', 'asc')
            // if(!$filter) {
            //     $query->inRandomOrder();
            // }
            // $query = $query
            ->paginate(10);
        return $query;
    }
    public static function listAllNewsOnlySearch($search, $except = [], $limit, $category)
    {
        $column_filter = "news.date_news";
        $type_filter = "desc";

        return NewsModel::select(
            'news.id',
            'news.title',
            'news.slug',
            'news.image',
            'news.location',
            'news.date_news',
            'news.desc',
            'news.views'
        )
            ->leftJoin('news_category_list', function ($join) {
                $join->on('news.id', '=', 'news_category_list.news_id');
            })
            ->leftJoin('news_category', function ($join) {
                $join->on('news_category.id', '=', 'news_category_list.news_category_id');
            })
            ->where(function ($q) use ($search, $except, $category) {
                if (!empty($search)) {
                    $q->where('news.title', 'LIKE', '%' . $search . '%');
                }

                if (!empty($category)) {
                    $q->where('news_category_list.news_category_id', '=', $category);
                }
            })
            ->whereNotIn('news.date_news', $except)
            ->orderby($column_filter, $type_filter)
            ->paginate($limit);
    }

    public static function listAll()
    {
        $data = News::select('id', 'title', 'title', 'date_news', 'slug', 'image')->take(6)->orderBy('id', 'desc')->get();

        return $data;
    }

    public static function listAllToArray()
    {
        $data = News::select('id', 'title', 'title', 'date_news', 'slug')->take(6)->orderBy('id', 'desc')
            ->pluck('news.id')
            ->toArray();

        return $data;
    }
}
