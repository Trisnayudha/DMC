<div class="card mb-2 sponsor-card"
     data-name="{{ strtolower($sponsor->name) }}"
     data-package="{{ $sponsor->package }}">
    <div class="card-header py-2 d-flex align-items-center justify-content-between"
         style="cursor:pointer; border-left: 4px solid
            @if($sponsor->package === 'platinum') #4e73df
            @elseif($sponsor->package === 'gold') #f6c23e
            @else #858796 @endif"
         data-toggle="collapse"
         data-target="#sponsor-{{ $sponsor->id }}">
        <div class="d-flex align-items-center" style="gap:10px">
            <div style="width:36px;height:36px;border-radius:50%;
                background:@if($sponsor->package === 'platinum') #4e73df @elseif($sponsor->package === 'gold') #f6c23e @else #858796 @endif;
                color:#fff;font-size:14px;font-weight:700;
                display:flex;align-items:center;justify-content:center;flex-shrink:0">
                {{ strtoupper(substr($sponsor->name, 0, 1)) }}
            </div>
            <div>
                <div class="font-weight-bold" style="font-size:14px">{{ $sponsor->name }}</div>
                <div style="font-size:11px;margin-top:1px">
                    <span class="badge badge-sm
                        @if($sponsor->package === 'platinum') badge-primary
                        @elseif($sponsor->package === 'gold') badge-warning
                        @else badge-secondary @endif">
                        {{ ucfirst($sponsor->package) }}
                    </span>
                    <span class="text-muted ml-1">
                        {{ $sponsor->contactRows->count() }} contact{{ $sponsor->contactRows->count() === 1 ? '' : 's' }}
                        &middot; {{ $sponsor->contactRows->where('role', 'pic')->count() }} PIC
                        &middot; {{ $sponsor->contactRows->where('role', 'billing')->count() }} Billing
                    </span>
                </div>
            </div>
        </div>
        <i class="fas fa-chevron-down text-muted" style="font-size:12px"></i>
    </div>

    <div class="collapse" id="sponsor-{{ $sponsor->id }}">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0" style="font-size:13px">
                    <thead>
                        <tr class="text-uppercase text-muted" style="font-size:10px;letter-spacing:.5px">
                            <th style="padding-left:16px">Name</th>
                            <th>Role</th>
                            <th>Title / Position</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Social</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sponsor->contactRows as $contact)
                            @include('admin.sponsor.contact_directory._contact_row')
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3" style="font-size:12px">
                                    <i class="fas fa-user-slash"></i> No contacts for this sponsor
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
