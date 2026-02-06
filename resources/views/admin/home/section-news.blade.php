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
                             <tr>
                                 <td>Indonesia Mining Outlook 2026</td>
                                 <td>Industry</td>
                                 <td>4,320</td>
                                 <td><span class="badge badge-success">Published</span></td>
                                 <td>2 days ago</td>
                             </tr>
                             <tr>
                                 <td>Nickel & EV Supply Chain Update</td>
                                 <td>Commodity</td>
                                 <td>3,180</td>
                                 <td><span class="badge badge-success">Published</span></td>
                                 <td>5 days ago</td>
                             </tr>
                             <tr>
                                 <td>Mining Safety Regulation 2026</td>
                                 <td>Regulation</td>
                                 <td>2,450</td>
                                 <td><span class="badge badge-success">Published</span></td>
                                 <td>1 week ago</td>
                             </tr>
                             <tr>
                                 <td>Upcoming Mining Events Q2</td>
                                 <td>Event</td>
                                 <td>1,120</td>
                                 <td><span class="badge badge-warning">Draft</span></td>
                                 <td>-</td>
                             </tr>
                         </tbody>
                     </table>
                 </div>
             </div>
         </div>
     </div>
 </div>
