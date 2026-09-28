@switch($result)
    @case('active')
        <span class="badge hv-status-active"><i class="bi bi-check-circle me-1"></i>Aktif</span>
        @break
    @case('inactive')
        <span class="badge hv-status-inactive"><i class="bi bi-x-circle me-1"></i>Tidak Aktif</span>
        @break
    @default
        <span class="badge hv-status-notfound"><i class="bi bi-question-circle me-1"></i>Tidak Ditemukan</span>
@endswitch
