 <div class="row">

     {{-- 5. Upcoming Events --}}
     <div class="col-lg-3 col-md-6 col-sm-6 col-12">
         <div class="card card-statistic-1">
             <div class="card-icon bg-primary">
                 <i class="fas fa-calendar-day"></i>
             </div>
             <div class="card-wrap">
                 <div class="card-header">
                     <h4>Upcoming Events</h4>
                 </div>
                 <div class="card-body">
                     4
                 </div>
             </div>
         </div>
     </div>

     {{-- 6. Event Registrations --}}
     <div class="col-lg-3 col-md-6 col-sm-6 col-12">
         <div class="card card-statistic-1">
             <div class="card-icon bg-danger">
                 <i class="fas fa-users"></i>
             </div>
             <div class="card-wrap">
                 <div class="card-header">
                     <h4>Event Registrations</h4>
                 </div>
                 <div class="card-body">
                     486
                 </div>
             </div>
         </div>
     </div>

     {{-- 7. Published News --}}
     <div class="col-lg-3 col-md-6 col-sm-6 col-12">
         <div class="card card-statistic-1">
             <div class="card-icon bg-success">
                 <i class="far fa-newspaper"></i>
             </div>
             <div class="card-wrap">
                 <div class="card-header">
                     <h4>Published News</h4>
                 </div>
                 <div class="card-body">
                     78
                 </div>
             </div>
         </div>
     </div>

     {{-- 8. News Views --}}
     <div class="col-lg-3 col-md-6 col-sm-6 col-12">
         <div class="card card-statistic-1">
             <div class="card-icon bg-dark">
                 <i class="fas fa-eye"></i>
             </div>
             <div class="card-wrap">
                 <div class="card-header">
                     <h4>News Views</h4>
                 </div>
                 <div class="card-body">
                     12,430
                     <div class="text-small text-muted">This month</div>
                 </div>
             </div>
         </div>
     </div>

 </div>
 <div class="row">
     <div class="col-lg-8 col-md-12 col-12">
         <div class="card">
             <div class="card-header">
                 <h4>Membership Growth</h4>
                 <div class="card-header-action">
                     <span class="badge badge-primary">Last 6 Months</span>
                 </div>
             </div>
             <div class="card-body">
                 <canvas id="membershipGrowthChart" height="150"></canvas>
             </div>
         </div>
     </div>

     {{-- Summary kanan --}}
     <div class="col-lg-4 col-md-12 col-12">
         <div class="card">
             <div class="card-header">
                 <h4>Quick Insights</h4>
             </div>
             <div class="card-body">
                 <ul class="list-unstyled">
                     <li class="mb-3">
                         <strong>Best Month</strong><br>
                         November (84 New Members)
                     </li>
                     <li class="mb-3">
                         <strong>Lowest Month</strong><br>
                         August (21 New Members)
                     </li>
                     <li class="mb-3">
                         <strong>Avg / Month</strong><br>
                         52 Members
                     </li>
                 </ul>
             </div>
         </div>
     </div>
 </div>

 <div class="row">
     <div class="col-lg-6 col-md-12 col-12">
         <div class="card">
             <div class="card-header">
                 <h4>Member Activity Status</h4>
             </div>
             <div class="card-body">
                 <canvas id="membershipStatusChart" height="200"></canvas>
             </div>
         </div>
     </div>
     <div class="col-lg-6 col-md-12 col-12">
         <div class="card">
             <div class="card-header">
                 <h4>Member Engagement Insights</h4>
             </div>
             <div class="card-body">
                 <div class="row text-center">
                     <div class="col-4">
                         <h5>64%</h5>
                         <p class="text-muted mb-0">Active Members</p>
                     </div>
                     <div class="col-4">
                         <h5>38%</h5>
                         <p class="text-muted mb-0">Joined Event</p>
                     </div>
                     <div class="col-4">
                         <h5>5.6</h5>
                         <p class="text-muted mb-0">Avg News / Member</p>
                     </div>
                 </div>
             </div>
         </div>
     </div>
 </div>


 <div class="row">
     <div class="col-lg-6 col-md-12 col-12">
         <div class="card">
             <div class="card-header">
                 <h4>Member Activity Status</h4>
             </div>
             <div class="card-body">
                 <canvas id="membershipStatusChart" height="200"></canvas>
             </div>
         </div>
     </div>
     <div class="col-lg-6 col-md-12 col-12">
         <div class="card">
             <div class="card-header">
                 <h4>Member Engagement Insights</h4>
             </div>
             <div class="card-body">
                 <div class="row text-center">
                     <div class="col-4">
                         <h5>64%</h5>
                         <p class="text-muted mb-0">Active Members</p>
                     </div>
                     <div class="col-4">
                         <h5>38%</h5>
                         <p class="text-muted mb-0">Joined Event</p>
                     </div>
                     <div class="col-4">
                         <h5>5.6</h5>
                         <p class="text-muted mb-0">Avg News / Member</p>
                     </div>
                 </div>
             </div>
         </div>
     </div>
 </div>
 <div class="row">
     <div class="col-12">
         <div class="card">
             <div class="card-header">
                 <h4>Inactive Members (30+ Days)</h4>
             </div>
             <div class="card-body p-0">
                 <div class="table-responsive">
                     <table class="table table-striped mb-0">
                         <thead>
                             <tr>
                                 <th>Member</th>
                                 <th>Company</th>
                                 <th>Last Activity</th>
                                 <th>Joined At</th>
                                 <th>Status</th>
                             </tr>
                         </thead>
                         <tbody>
                             <tr>
                                 <td>Ahmad Fauzi</td>
                                 <td>PT Mineral Energi Indonesia</td>
                                 <td>45 days ago</td>
                                 <td>2024</td>
                                 <td><span class="badge badge-warning">Inactive</span></td>
                             </tr>
                             <tr>
                                 <td>Siti Rahmawati</td>
                                 <td>PT Tambang Sejahtera</td>
                                 <td>62 days ago</td>
                                 <td>2023</td>
                                 <td><span class="badge badge-danger">Dormant</span></td>
                             </tr>
                             <tr>
                                 <td>Budi Santoso</td>
                                 <td>PT Mining Global</td>
                                 <td>90 days ago</td>
                                 <td>2022</td>
                                 <td><span class="badge badge-danger">Dormant</span></td>
                             </tr>
                         </tbody>
                     </table>
                 </div>
             </div>
         </div>
     </div>
 </div>
