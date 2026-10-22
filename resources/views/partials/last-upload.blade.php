<div class="mt-3 min-w-0 text-xs text-slate-500">
    <p class="font-medium text-slate-600">Last uploaded file</p>
    @if ($lastUpload)
        <p class="mt-1 break-all text-slate-700">{{ $lastUpload->original_filename }}</p>
        <p class="mt-1">Uploaded: <time datetime="{{ $lastUpload->created_at->toIso8601String() }}">{{ $lastUpload->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</time></p>
        <p class="mt-1">Import completed successfully</p>
    @else
        <p class="mt-1">No successful uploads yet.</p>
    @endif
</div>
