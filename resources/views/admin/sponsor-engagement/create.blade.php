@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Add Sponsor Engagement</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active"><a href="{{ route('sponsor-engagement.index') }}">Engagement</a></div>
                </div>
            </div>
            <div class="section-body">
                <div class="card">
                    <div class="card-header">
                        <h4>Engagement Form</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('sponsor-engagement.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label for="sponsor_id">Sponsor</label>
                                <select name="sponsor_id" id="sponsor_id" class="form-control" required>
                                    <option value="">Select Sponsor</option>
                                    @foreach ($sponsors as $sponsor)
                                        <option value="{{ $sponsor->id }}">{{ $sponsor->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="activity_type">Activity Type</label>
                                <select name="activity_type" id="activity_type" class="form-control" required>
                                    <option value="">Select Activity</option>
                                    <option value="like">Like</option>
                                    <option value="comment">Comment</option>
                                    <option value="share">Share</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="platform">Platform</label>
                                <input type="text" name="platform" id="platform" class="form-control"
                                    placeholder="e.g., Instagram, LinkedIn" required>
                            </div>

                            <div class="form-group">
                                <label for="activity_date">Activity Date</label>
                                <input type="date" name="activity_date" id="activity_date" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="screenshot">Upload Screenshot (Optional)</label>
                                <input type="file" name="screenshot" id="screenshot" class="form-control-file">
                            </div>

                            <button type="submit" class="btn btn-primary">Add Engagement</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
