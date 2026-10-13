<div class="space-y-6">
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">Business setup</p>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Submit your shop details</h1>
            </div>

            @php
                $current = \Illuminate\Support\Facades\Auth::user()?->currentTenant();
                $currentStatus = $current?->verification_status;
                $currentStatusValue = $currentStatus instanceof \App\Enums\VerificationStatus
                    ? $currentStatus->value
                    : $currentStatus;
            @endphp

            @if ($currentStatusValue !== 'approved')
                <div
                    class="rounded-full border border-orange-200 bg-orange-50 px-3 py-1.5 text-xs font-semibold text-orange-700">
                    Approval review required
                </div>
            @endif
        </div>

        @if (session('success'))
        <div
            class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('success') }}
        </div>
        @endif

        <form wire:submit="save" class="space-y-6">
            <div class="grid gap-6 md:grid-cols-2">
                {{-- Shop name --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Shop name</label>
                    <input wire:model="shop_name" type="text"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-slate-900 focus:bg-white focus:ring-4 focus:ring-slate-100"
                        placeholder="e.g. RoadMaster Tire Hub" />
                    @error('shop_name') <span class="mt-2 block text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                {{-- Business email --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Business email</label>
                    <input wire:model="shop_email" type="email"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-slate-900 focus:bg-white focus:ring-4 focus:ring-slate-100"
                        placeholder="business@shop.com" />
                    @error('shop_email') <span class="mt-2 block text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                {{-- Phone --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Business contact</label>
                    <input wire:model="phone" type="text"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-slate-900 focus:bg-white focus:ring-4 focus:ring-slate-100"
                        placeholder="+63 912 345 6789" />
                    @error('phone') <span class="mt-2 block text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                {{-- Logo --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Business logo</label>
                    <input wire:model="logo" type="file" accept="image/*"
                        class="block w-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-4 text-sm text-slate-600 file:mr-4 file:rounded-full file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white" />
                    @error('logo') <span class="mt-2 block text-sm text-red-600">{{ $message }}</span> @enderror

                    <div wire:loading wire:target="logo" class="mt-2 text-xs text-slate-400">Uploading…</div>

                    {{-- Preview: new upload takes priority, else show existing --}}
                    @if ($logo)
                    <div class="mt-3 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-3">
                        <div class="relative">
                            <img src="{{ $logo->temporaryUrl() }}" alt="New logo preview"
                                class="h-20 w-20 rounded-xl object-cover" />
                            <button type="button" wire:click="removeLogo" title="Remove this logo"
                                class="absolute -top-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-600 text-white shadow-md transition hover:bg-red-700">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div class="pt-1">
                            <p class="text-xs font-semibold text-emerald-700">New logo ready to save</p>
                            <p class="mt-0.5 text-[11px] text-emerald-600">Click the × to remove it.</p>
                        </div>
                    </div>
                    @elseif ($existingLogo)
                    <div class="mt-3 flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3">
                        <div class="relative">
                            <img src="{{ asset('storage/' . $existingLogo) }}" alt="Current logo"
                                class="h-20 w-20 rounded-xl object-cover" />
                            <button type="button" wire:click="removeExistingLogo"
                                wire:confirm="Remove the current logo? This won't save until you click Submit."
                                title="Remove current logo"
                                class="absolute -top-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-600 text-white shadow-md transition hover:bg-red-700">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div class="pt-1">
                            <p class="text-xs font-semibold text-slate-700">Current logo</p>
                            <p class="mt-0.5 text-[11px] text-slate-500">Upload a new file to replace, or click × to
                                remove.</p>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Business hours --}}
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-slate-700">Business hours</label>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                        @foreach ($dayNames as $day)
                        <div wire:key="hours-{{ $day }}" class="grid grid-cols-[100px_1fr_1fr] items-center gap-3 py-2">
                            <span class="text-sm font-medium text-slate-700">{{ $day }}</span>
                            <select wire:model="business_hours.{{ $day }}.open"
                                class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 outline-none focus:border-slate-900">
                                @foreach ($timeOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <select wire:model="business_hours.{{ $day }}.close"
                                class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 outline-none focus:border-slate-900">
                                @foreach ($timeOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endforeach
                    </div>
                    @error('business_hours') <span class="mt-2 block text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- Address --}}
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Business address</label>
                <textarea wire:model="address" rows="4"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-slate-900 focus:bg-white focus:ring-4 focus:ring-slate-100"
                    placeholder="Street address, city, region"></textarea>
                @error('address') <span class="mt-2 block text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            {{-- Permit documents --}}
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Permit / business documents</label>
                <p class="mb-2 text-xs text-slate-500">
                    Upload a scanned PDF, or a clear photo of your printed document.
                    Accepted formats: PDF, JPG, PNG, WEBP. Max 4 MB per file.
                </p>
                <input wire:model="permit_documents" type="file" multiple
                    class="block w-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-4 text-sm text-slate-600 file:mr-4 file:rounded-full file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white" />
                @error('permit_documents') <span class="mt-2 block text-sm text-red-600">{{ $message }}</span> @enderror
                @error('permit_documents.*') <span class="mt-2 block text-sm text-red-600">{{ $message }}</span>
                @enderror

                <div wire:loading wire:target="permit_documents" class="mt-2 text-xs text-slate-400">Uploading…</div>

                {{-- Newly selected files --}}
                @if (! empty($permit_documents))
                <div class="mt-3 space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-600">
                        Ready to upload ({{ count($permit_documents) }})
                    </p>
                    @foreach ($permit_documents as $index => $permit)
                    <div wire:key="new-permit-{{ $index }}-{{ $permit->getFilename() }}"
                        class="flex items-center justify-between gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2">
                        <div class="flex min-w-0 items-center gap-2">
                            <svg class="h-4 w-4 shrink-0 text-emerald-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="truncate text-sm font-medium text-emerald-700">
                                {{ $permit->getClientOriginalName() }}
                            </span>
                        </div>
                        <button type="button" wire:click="removePermitDocument({{ $index }})" title="Remove this file"
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-emerald-700 transition hover:bg-red-100 hover:text-red-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Existing attachments from a previous save --}}
                @if (! empty($existingAttachments) && empty($permit_documents))
                <div class="mt-3 space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Currently uploaded</p>
                    @foreach ($existingAttachments as $index => $path)
                    <div
                        class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                        <a href="{{ asset('storage/' . $path) }}" target="_blank"
                            class="text-sm font-medium text-slate-700 hover:text-slate-900 underline">
                            {{ basename($path) }}
                        </a>
                        <button type="button" wire:click="removeExistingAttachment({{ $index }})"
                            wire:confirm="Remove this document?"
                            class="text-xs font-semibold text-red-600 hover:text-red-700">
                            Remove
                        </button>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Submit --}}
            <div class="flex items-center justify-between gap-4 pt-4 border-t border-slate-100">
                <p class="text-xs text-slate-500">
                    On submit, your shop enters the admin approval queue. You'll be able to list products once approved.
                </p>
                <button type="submit" wire:loading.attr="disabled" wire:target="save,logo,permit_documents"
                    class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:opacity-50">
                    <span wire:loading.remove wire:target="save">Submit for approval</span>
                    <span wire:loading wire:target="save" class="flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Submitting…
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>