@php
    use App\Models\Plan;
    use App\Models\Subscription;
    use Carbon\Carbon;

    $plans = Plan::where('active', true)
        ->where(function ($q) {
            $q->whereNotNull('monthly_price_id')->orWhereNotNull('yearly_price_id');
        })
        ->orderBy('sort_order')
        ->get();

    $authUser = auth()->user();
    $activeSub = null;
    if ($authUser) {
        $now = Carbon::now();
        $activeSub = Subscription::where('billable_type', 'user')
            ->where('billable_id', $authUser->id)
            ->whereIn('status', ['active', 'trialing'])
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>', $now);
            })
            ->orderByDesc('id')
            ->first();
    }
    $activePlanId = $activeSub?->plan_id;
@endphp

<section id="pricing">
    <div class="text-center max-w-3xl mx-auto mb-12">
        <h2 class="text-4xl sm:text-5xl font-bold tracking-tight text-white mb-4">
            {{ __('landing.pricing.heading') }}
        </h2>
        <p class="text-lg text-zinc-400">
            {{ __('landing.pricing.subheading') }}
        </p>
    </div>

    <div x-data="{ billing: 'monthly' }" class="max-w-6xl mx-auto">
        {{-- Billing toggle --}}
        <div class="flex justify-center mb-12">
            <div class="inline-flex p-1 rounded-full bg-white/5 border border-white/10">
                <button
                    x-on:click="billing = 'monthly'"
                    x-bind:class="billing === 'monthly' ? 'bg-[#E50914] text-white' : 'text-zinc-400 hover:text-white'"
                    class="px-6 py-2 rounded-full text-sm font-semibold transition-colors">
                    {{ __('landing.pricing.monthly') }}
                </button>
                <button
                    x-on:click="billing = 'yearly'"
                    x-bind:class="billing === 'yearly' ? 'bg-[#E50914] text-white' : 'text-zinc-400 hover:text-white'"
                    class="px-6 py-2 rounded-full text-sm font-semibold transition-colors">
                    {{ __('landing.pricing.yearly') }}
                    <span class="ml-1 text-xs opacity-80">{{ __('landing.pricing.save') }}</span>
                </button>
            </div>
        </div>

        {{-- Plans grid --}}
        <div class="grid md:grid-cols-3 gap-6">
            @foreach($plans as $plan)
                @php
                    $isDefault = (int) ($plan->default ?? 0) === 1;
                    $isCurrent = $activePlanId === $plan->id;
                    $features = array_filter(array_map('trim', explode(',', (string) $plan->features)));
                @endphp
                <div class="relative p-8 rounded-2xl bg-white/[0.03] border {{ $isDefault ? 'border-[#E50914]' : 'border-white/10' }} flex flex-col">
                    @if($isDefault)
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full bg-[#E50914] text-white text-xs font-bold uppercase tracking-wide">
                            {{ __('landing.pricing.recommended') }}
                        </div>
                    @endif

                    @php
                        // La descripción traducida manda; si el plan no tiene
                        // entrada en lang/, cae a la de la tabla `plans`.
                        $planKey = 'landing.plans.'.\Illuminate\Support\Str::slug($plan->name);
                    @endphp
                    <h3 class="text-2xl font-bold text-white mb-2">{{ $plan->name }}</h3>
                    <p class="text-sm text-zinc-400 mb-6 min-h-[40px]">{{ \Illuminate\Support\Facades\Lang::has($planKey) ? __($planKey) : $plan->description }}</p>

                    <div class="mb-6">
                        <div x-show="billing === 'monthly'" class="flex items-baseline">
                            <span class="text-5xl font-bold text-white">{{ $plan->currency }}{{ $plan->monthly_price }}</span>
                            <span class="text-zinc-400 ml-2">{{ __('landing.pricing.per_month') }}</span>
                        </div>
                        <div x-show="billing === 'yearly'" x-cloak class="flex items-baseline">
                            <span class="text-5xl font-bold text-white">{{ $plan->currency }}{{ $plan->yearly_price }}</span>
                            <span class="text-zinc-400 ml-2">{{ __('landing.pricing.per_year') }}</span>
                        </div>
                    </div>

                    <ul class="flex-1 space-y-3 mb-8 text-sm">
                        @foreach($features as $feat)
                            <li class="flex items-start text-zinc-300">
                                <svg class="w-5 h-5 text-[#E50914] mt-0.5 flex-shrink-0 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ $feat }}
                            </li>
                        @endforeach
                    </ul>

                    @if($isCurrent)
                        <div class="block text-center px-6 py-3 rounded-lg bg-emerald-500/10 border border-emerald-500/40 text-emerald-400 font-bold">
                            {{ __('landing.pricing.cta_current') }}
                        </div>
                    @elseif($authUser)
                        <form method="POST" action="{{ url('/pricing/checkout') }}">
                            @csrf
                            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                            <input type="hidden" name="billing_cycle" x-bind:value="billing">
                            <button type="submit"
                                class="w-full text-center px-6 py-3 rounded-lg {{ $isDefault ? 'bg-[#E50914] hover:bg-[#B0070F]' : 'bg-white/5 hover:bg-white/10 border border-white/10' }} text-white font-bold transition-colors">
                                {{ __('landing.pricing.cta') }}
                            </button>
                        </form>
                    @else
                        <a href="{{ url('/auth/login') }}?redirect={{ urlencode('/pricing') }}"
                           class="block text-center px-6 py-3 rounded-lg {{ $isDefault ? 'bg-[#E50914] hover:bg-[#B0070F]' : 'bg-white/5 hover:bg-white/10 border border-white/10' }} text-white font-bold transition-colors">
                            {{ __('landing.pricing.cta_login') }}
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
