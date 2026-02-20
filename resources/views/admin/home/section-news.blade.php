 <div class="row">
     <div class="col-lg-8 col-md-12 col-12">
         <div class="card">
             <div class="card-header">
                 <h4>News Views Trend</h4>
                 <div class="card-header-action">
                     <span class="badge badge-primary">Last 7 Days</span>
                 </div>
             </div>
             <div class="card-body">
                 <canvas id="newsViewsChart" height="150"></canvas>
             </div>
         </div>
     </div>

     {{-- News Status --}}
     <div class="col-lg-4 col-md-12 col-12">
         <div class="card">
             <div class="card-header">
                 <h4>News Status</h4>
             </div>
             <div class="card-body">
                 <canvas id="newsStatusChart" height="200"></canvas>
             </div>
         </div>
     </div>
 </div>
 <div class="row">
     <div class="col-12">
         <div class="card">
             <div class="card-header">
                 <h4>Top Viewed News</h4>
             </div>
             <div class="card-body p-0">
                 <div class="table-responsive">
                     <table class="table table-striped mb-0">
                         <thead>
                             <tr>
                                 <th>Title</th>
                                 <th>Category</th>
                                 <th>Views</th>
                                 <th>Status</th>
                                 <th>Published</th>
                             </tr>
                         </thead>
                         <tbody>
                             @forelse(($topNews ?? []) as $n)
                                 <tr>
                                     <td>{{ $n->title }}</td>
                                     <td>{{ $n->category_name ?? '-' }}</td>
                                     <td>{{ number_format($n->views ?? 0) }}</td>
                                     <td><span class="badge {{ $n->badge_class }}">{{ $n->status_label }}</span></td>
                                     <td>{{ $n->status === 'publish' ? $n->published_human : '-' }}</td>
                                 </tr>
                             @empty
                                 <tr>
                                     <td colspan="5" class="text-center text-muted p-4">No data</td>
                                 </tr>
                             @endforelse
                         </tbody>
                     </table>
                 </div>
             </div>
         </div>
     </div>
 </div>
