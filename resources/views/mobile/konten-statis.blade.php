@extends('mobile.layout', ['title' => $judulHalaman])

@section('content')

    {{-- ========================================== --}}
    {{-- MOBILE VIEW (< 1024px) - 100% UNTOUCHED ORIGINAL --}}
    {{-- ========================================== --}}
    <div class="lg:hidden">
        {{-- Header --}}
        <div class="-mt-6 flex flex-none items-center justify-between px-0 pt-6 pb-4">
            <a href="{{ route('member.akun') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
                class="flex h-9 w-9 items-center justify-center rounded-full border border-gray-200 text-brand-800 transition hover:bg-gray-50 active:scale-95">
                <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                    <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
            <h1 class="text-base font-extrabold text-gray-900">{{ $judulHalaman }}</h1>
            <span class="w-9"></span>
        </div>

        {{-- Content Body --}}
        @if ($konten && $konten->konten)
            <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed space-y-4 pb-8">
                {!! $konten->konten !!}
            </div>
        @else
            {{-- Empty state --}}
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-100">
                    @if ($slug === 'help-center')
                        <svg viewBox="0 0 24 24" fill="none" class="h-8 w-8 text-gray-400">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/>
                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            <circle cx="12" cy="17" r="0.5" fill="currentColor" stroke="currentColor" stroke-width="1.5"/>
                        </svg>
                    @elseif ($slug === 'terms-conditions')
                        <svg viewBox="0 0 24 24" fill="none" class="h-8 w-8 text-gray-400">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="1.8"/>
                            <path d="M14 2v6h6M16 13H8M16 17H8M10 9H8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    @else
                        <svg viewBox="0 0 24 24" fill="none" class="h-8 w-8 text-gray-400">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                        </svg>
                    @endif
                </div>
                <p class="text-sm font-semibold text-gray-500">Content not available yet</p>
                <p class="mt-1 text-xs text-gray-400">This page is currently being prepared.</p>
            </div>
        @endif
    </div>


    {{-- ========================================== --}}
    {{-- DESKTOP VIEW (>= 1024px) - NO HERO SECTION --}}
    {{-- Artisanal Warm French Bakery Editorial Hub  --}}
    {{-- ========================================== --}}
    <div id="konten-desktop-root" class="hidden lg:block w-full bg-[#FAF5E8] min-h-[calc(100vh-140px)] text-[#3C2415] py-10 px-6 sm:px-8 lg:px-12">
        <div class="max-w-5xl mx-auto space-y-8">

            {{-- Top Navigation & Metadata Bar --}}
            <div class="flex items-center justify-between border-b border-[#3C2415]/10 pb-5">
                <a href="{{ route('member.akun') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
                   class="inline-flex items-center gap-2 text-xs font-bold text-[#3C2415]/75 hover:text-[#8E1E26] transition">
                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                        <path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Back to Account</span>
                </a>
            </div>

            {{-- Clean Page Header (NO HERO SECTION) --}}
            <div class="space-y-2">
                @if ($slug === 'help-center')
                    <h1 class="font-display font-black text-3xl sm:text-4xl uppercase tracking-tight text-[#3C2415]">
                        Help Center & Store Support
                    </h1>
                    <p class="text-sm text-[#3C2415]/75 max-w-2xl leading-relaxed">
                        Find answers to common questions about fresh daily orders, store pickup, digital stamp collection, and member privileges.
                    </p>
                @elseif ($slug === 'terms-conditions')
                    <h1 class="font-display font-black text-3xl sm:text-4xl uppercase tracking-tight text-[#3C2415]">
                        Terms & Conditions
                    </h1>
                    <p class="text-sm text-[#3C2415]/75 max-w-2xl leading-relaxed">
                        Please review our operational terms, order fulfillment policies, and membership guidelines governing purchases at Daisy Bakery.
                    </p>
                @else
                    <h1 class="font-display font-black text-3xl sm:text-4xl uppercase tracking-tight text-[#3C2415]">
                        Privacy Policy
                    </h1>
                    <p class="text-sm text-[#3C2415]/75 max-w-2xl leading-relaxed">
                        Learn how Daisy Bakery collects, uses, and safeguards your artisanal member account data and transaction records.
                    </p>
                @endif
            </div>

            {{-- SLUG-SPECIFIC CONTENT LAYOUT --}}
            @if ($slug === 'help-center')
                {{-- 1. HELP CENTER FAQ & CONCIERGE --}}
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    {{-- Left Column: Store Concierge Card (Sticky) --}}
                    <div class="lg:col-span-4 space-y-6 lg:sticky lg:top-24">
                        <div class="rounded-[28px] bg-[#F5ECE3] p-6 border border-[#3C2415]/15 shadow-sm space-y-5">
                            <div class="flex items-center gap-3.5">
                                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white border border-[#3C2415]/15 shadow-xs">
                                    <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="Daisy Concierge" class="w-7 h-7 object-contain">
                                </span>
                                <div>
                                    <h2 class="font-display font-bold text-base uppercase tracking-tight text-[#3C2415]">Bakery Concierge</h2>
                                    <p class="text-xs text-[#8E1E26] font-bold">Live Store Support</p>
                                </div>
                            </div>

                            <p class="text-xs text-[#3C2415]/75 leading-relaxed">
                                Need personal assistance with celebration cakes, bulk orders, urgent allergen checks, or pickup schedule changes? Our concierge is ready to assist.
                            </p>

                            <a href="https://wa.me/62882000768044" target="_blank"
                               class="flex items-center justify-center gap-2.5 w-full py-3 px-4 rounded-xl bg-[#25D366] hover:bg-[#20ba5a] text-white text-xs font-bold uppercase tracking-wider shadow-sm transition active:scale-95">
                                <svg viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4">
                                    <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.81 9.81 0 0 0 12.04 2zm5.8 14.16c-.24.68-1.2 1.25-1.95 1.41-.51.11-1.18.2-3.43-.74-2.88-1.19-4.73-4.13-4.88-4.32-.14-.19-1.18-1.57-1.18-3 0-1.43.75-2.14 1.02-2.43.24-.26.63-.38.84-.38.21 0 .42 0 .6.01.19.01.45-.07.7.53.26.63.89 2.17.97 2.33.08.16.13.35.03.56-.1.21-.16.34-.31.52-.16.18-.33.4-.47.54-.15.15-.31.31-.13.62.18.31.8 1.32 1.72 2.14 1.18 1.05 2.18 1.38 2.49 1.53.31.16.5.13.68-.08.19-.21.79-.92 1-.1.24.18.24.31.4.52 1.32.21.21 2.22.42 2.51.52.29.1.47.16.53.27.06.11.06.63-.18 1.31z"/>
                                </svg>
                                <span>Chat with Store Concierge</span>
                            </a>
                        </div>

                        {{-- Quick Info Biscuit Box --}}
                        <div class="rounded-2xl bg-[#FDFBF7] p-5 border border-[#3C2415]/10 shadow-xs space-y-2">
                            <h3 class="font-display font-bold text-xs uppercase tracking-wider text-[#3C2415]">In-Store Cashier Help</h3>
                            <p class="text-[11px] text-[#3C2415]/70 leading-relaxed">
                                You can also show your digital member QR or telephone number directly to our barista or cashier when visiting our bakery.
                            </p>
                        </div>
                    </div>

                    {{-- Right Column: Structured FAQ Cards --}}
                    <div class="lg:col-span-8 space-y-4">
                        {{-- FAQ 1 --}}
                        <div class="rounded-[24px] bg-[#F5ECE3] p-6 border border-[#3C2415]/15 shadow-sm space-y-2">
                            <div class="flex items-center gap-3">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#3C2415] text-[#FAF5E8] font-bold text-xs">1</span>
                                <h3 class="font-display font-bold text-base text-[#3C2415]">How do I place an artisanal order?</h3>
                            </div>
                            <p class="text-xs text-[#3C2415]/80 leading-relaxed pl-10">
                                Browse our fresh daily bakes under the <strong>Menu</strong> tab. Select your favorite bread, pastry, or cake, pick size and quantity, and click <em>Add to Bag</em>. When you are ready, head to checkout to select your preferred in-store pickup slot.
                            </p>
                        </div>

                        {{-- FAQ 2 --}}
                        <div class="rounded-[24px] bg-[#F5ECE3] p-6 border border-[#3C2415]/15 shadow-sm space-y-2">
                            <div class="flex items-center gap-3">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#3C2415] text-[#FAF5E8] font-bold text-xs">2</span>
                                <h3 class="font-display font-bold text-base text-[#3C2415]">How does the Digital Stamp Program work?</h3>
                            </div>
                            <p class="text-xs text-[#3C2415]/80 leading-relaxed pl-10">
                                Every qualifying purchase with a minimum spend of Rp25,000 automatically credits <strong>1 digital stamp</strong> to your punch card. Collect 5 stamps to unlock a complimentary artisanal treat (such as our artisan Butter Croissant, Soft Milk Bun, or Cookie box) directly in the <strong>Vouchers & Rewards</strong> catalog.
                            </p>
                        </div>

                        {{-- FAQ 3 --}}
                        <div class="rounded-[24px] bg-[#F5ECE3] p-6 border border-[#3C2415]/15 shadow-sm space-y-2">
                            <div class="flex items-center gap-3">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#3C2415] text-[#FAF5E8] font-bold text-xs">3</span>
                                <h3 class="font-display font-bold text-base text-[#3C2415]">How are Loyalty Points earned and redeemed?</h3>
                            </div>
                            <p class="text-xs text-[#3C2415]/80 leading-relaxed pl-10">
                                Loyalty points are earned with every purchase made through your registered member account. Your active balance is shown on your <strong>Account</strong> overview. You can convert points into discount vouchers and seasonal gift coupons via the Rewards section.
                            </p>
                        </div>

                        {{-- FAQ 4 --}}
                        <div class="rounded-[24px] bg-[#F5ECE3] p-6 border border-[#3C2415]/15 shadow-sm space-y-2">
                            <div class="flex items-center gap-3">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#3C2415] text-[#FAF5E8] font-bold text-xs">4</span>
                                <h3 class="font-display font-bold text-base text-[#3C2415]">How do I confirm my payment?</h3>
                            </div>
                            <p class="text-xs text-[#3C2415]/80 leading-relaxed pl-10">
                                For Bank Transfer and QRIS payments, complete the transfer in your mobile banking app, then upload a screenshot of your transfer receipt on the <strong>Order Details</strong> page. Our cashier will verify it promptly, updating the order to <em>Processing / Baking</em>.
                            </p>
                        </div>

                        {{-- FAQ 5 --}}
                        <div class="rounded-[24px] bg-[#F5ECE3] p-6 border border-[#3C2415]/15 shadow-sm space-y-2">
                            <div class="flex items-center gap-3">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#3C2415] text-[#FAF5E8] font-bold text-xs">5</span>
                                <h3 class="font-display font-bold text-base text-[#3C2415]">Can I modify, reschedule, or cancel my order?</h3>
                            </div>
                            <p class="text-xs text-[#3C2415]/80 leading-relaxed pl-10">
                                Because our artisan batches are prepared and baked fresh without preservatives, orders enter preparation immediately upon verification. To adjust pickup times or request custom notes, please message our Bakery Concierge via WhatsApp as soon as possible.
                            </p>
                        </div>

                        {{-- FAQ 6 --}}
                        <div class="rounded-[24px] bg-[#F5ECE3] p-6 border border-[#3C2415]/15 shadow-sm space-y-2">
                            <div class="flex items-center gap-3">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#3C2415] text-[#FAF5E8] font-bold text-xs">6</span>
                                <h3 class="font-display font-bold text-base text-[#3C2415]">Fresh ingredients & allergen notifications</h3>
                            </div>
                            <p class="text-xs text-[#3C2415]/80 leading-relaxed pl-10">
                                We bake with 100% French butter, fresh farm eggs, unbleached flour, and premium chocolates. Certain recipes contain or may come into contact with tree nuts, almonds, dairy, and gluten. Please check individual item tags for detailed allergen disclosures.
                            </p>
                        </div>
                    </div>
                </div>

            @elseif ($slug === 'terms-conditions')
                {{-- 2. TERMS & CONDITIONS EDITORIAL LAYOUT --}}
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    {{-- Left Column: Sticky Table of Contents --}}
                    <div class="lg:col-span-4 space-y-6 lg:sticky lg:top-24">
                        <div class="rounded-[28px] bg-[#F5ECE3] p-6 border border-[#3C2415]/15 shadow-sm space-y-4">
                            <h2 class="font-display font-bold text-xs uppercase tracking-widest text-[#8E1E26]">Table of Contents</h2>
                            <nav class="space-y-1.5 text-xs text-[#3C2415]/80 font-medium">
                                <a href="#tc-1" class="block py-1.5 px-3 rounded-xl hover:bg-white/80 hover:text-[#8E1E26] transition">1. Acceptance of Terms</a>
                                <a href="#tc-2" class="block py-1.5 px-3 rounded-xl hover:bg-white/80 hover:text-[#8E1E26] transition">2. Member Account & Security</a>
                                <a href="#tc-3" class="block py-1.5 px-3 rounded-xl hover:bg-white/80 hover:text-[#8E1E26] transition">3. Fresh Baking & Pricing</a>
                                <a href="#tc-4" class="block py-1.5 px-3 rounded-xl hover:bg-white/80 hover:text-[#8E1E26] transition">4. Payment & Verification</a>
                                <a href="#tc-5" class="block py-1.5 px-3 rounded-xl hover:bg-white/80 hover:text-[#8E1E26] transition">5. In-Store Collection</a>
                                <a href="#tc-6" class="block py-1.5 px-3 rounded-xl hover:bg-white/80 hover:text-[#8E1E26] transition">6. Stamps & Rewards</a>
                                <a href="#tc-7" class="block py-1.5 px-3 rounded-xl hover:bg-white/80 hover:text-[#8E1E26] transition">7. Quality Guarantee</a>
                            </nav>
                        </div>

                        {{-- Quality Promise Callout --}}
                        <div class="rounded-2xl bg-[#FDFBF7] p-5 border border-[#3C2415]/10 shadow-xs space-y-2">
                            <div class="flex items-center gap-2 text-xs font-bold text-[#8E1E26] uppercase">
                                <span>★ Artisan Quality Guarantee</span>
                            </div>
                            <p class="text-[11px] text-[#3C2415]/70 leading-relaxed">
                                All Daisy Bakery items are handcrafted with pure butter and natural ingredients daily. If your pickup does not meet our standards, we replace it gladly.
                            </p>
                        </div>
                    </div>

                    {{-- Right Column: Full Terms Content --}}
                    <div class="lg:col-span-8 space-y-6 text-[#3C2415]/85 text-xs sm:text-sm leading-relaxed">
                        <section id="tc-1" class="rounded-[24px] bg-[#F5ECE3] p-7 border border-[#3C2415]/15 shadow-sm space-y-3">
                            <h3 class="font-display font-bold text-base text-[#3C2415] uppercase tracking-tight">1. Acceptance of Terms</h3>
                            <p>
                                By creating a member account, placing an order, or utilizing services at Daisy Bakery, you agree to be bound by these Terms and Conditions. These terms apply to all visitors, registered members, and customers accessing our in-store and online channels.
                            </p>
                        </section>

                        <section id="tc-2" class="rounded-[24px] bg-[#F5ECE3] p-7 border border-[#3C2415]/15 shadow-sm space-y-3">
                            <h3 class="font-display font-bold text-base text-[#3C2415] uppercase tracking-tight">2. Member Account & Security</h3>
                            <p>
                                Your Daisy Bakery member account is personal and strictly non-transferable. You are responsible for maintaining the confidentiality of your login credentials and password. Daisy Bakery reserves the right to suspend or terminate accounts found engaging in abusive or fraudulent behavior.
                            </p>
                        </section>

                        <section id="tc-3" class="rounded-[24px] bg-[#F5ECE3] p-7 border border-[#3C2415]/15 shadow-sm space-y-3">
                            <h3 class="font-display font-bold text-base text-[#3C2415] uppercase tracking-tight">3. Fresh Baking & Pricing</h3>
                            <p>
                                All breads, viennoiseries, and celebration cakes are produced in limited daily artisan batches. Prices are listed in Indonesian Rupiah (IDR) inclusive of applicable taxes. In the event an item sells out before confirmation, our team will contact you to offer an alternative or issue an immediate refund.
                            </p>
                        </section>

                        <section id="tc-4" class="rounded-[24px] bg-[#F5ECE3] p-7 border border-[#3C2415]/15 shadow-sm space-y-3">
                            <h3 class="font-display font-bold text-base text-[#3C2415] uppercase tracking-tight">4. Payment & Receipt Verification</h3>
                            <p>
                                Orders paid via Bank Transfer or QRIS require the submission of a valid payment receipt through your order tracking interface. Our store cashiers verify payments during operating hours (07:00 – 21:00 WIB). Orders without verified payment within the designated timeframe are subject to automated cancellation.
                            </p>
                        </section>

                        <section id="tc-5" class="rounded-[24px] bg-[#F5ECE3] p-7 border border-[#3C2415]/15 shadow-sm space-y-3">
                            <h3 class="font-display font-bold text-base text-[#3C2415] uppercase tracking-tight">5. In-Store Collection Guidelines</h3>
                            <p>
                                Customers are requested to collect their bakery orders within the selected pickup window to ensure optimum freshness and texture. Orders remaining unclaimed at store closing time cannot be refunded due to the perishable nature of freshly baked goods.
                            </p>
                        </section>

                        <section id="tc-6" class="rounded-[24px] bg-[#F5ECE3] p-7 border border-[#3C2415]/15 shadow-sm space-y-3">
                            <h3 class="font-display font-bold text-base text-[#3C2415] uppercase tracking-tight">6. Digital Stamps & Loyalty Rewards</h3>
                            <p>
                                Loyalty points and digital stamps possess no cash surrender value and cannot be redeemed for fiat currency. Rewards are redeemed according to availability in the catalog. Daisy Bakery reserves the right to modify or conclude active stamp programs with advance notice.
                            </p>
                        </section>

                        <section id="tc-7" class="rounded-[24px] bg-[#F5ECE3] p-7 border border-[#3C2415]/15 shadow-sm space-y-3">
                            <h3 class="font-display font-bold text-base text-[#3C2415] uppercase tracking-tight">7. Quality Guarantee & Adjustments</h3>
                            <p>
                                Once preparation begins, orders cannot be cancelled. However, if your order is damaged or does not align with your requested specifications upon collection, please inform our team immediately for a replacement or resolution.
                            </p>
                        </section>
                    </div>
                </div>

            @else
                {{-- 3. PRIVACY POLICY EDITORIAL LAYOUT --}}
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    {{-- Left Column: Sticky Table of Contents --}}
                    <div class="lg:col-span-4 space-y-6 lg:sticky lg:top-24">
                        <div class="rounded-[28px] bg-[#F5ECE3] p-6 border border-[#3C2415]/15 shadow-sm space-y-4">
                            <h2 class="font-display font-bold text-xs uppercase tracking-widest text-[#8E1E26]">Privacy Overview</h2>
                            <nav class="space-y-1.5 text-xs text-[#3C2415]/80 font-medium">
                                <a href="#pp-1" class="block py-1.5 px-3 rounded-xl hover:bg-white/80 hover:text-[#8E1E26] transition">1. Information We Collect</a>
                                <a href="#pp-2" class="block py-1.5 px-3 rounded-xl hover:bg-white/80 hover:text-[#8E1E26] transition">2. How We Use Data</a>
                                <a href="#pp-3" class="block py-1.5 px-3 rounded-xl hover:bg-white/80 hover:text-[#8E1E26] transition">3. Security & Hashing</a>
                                <a href="#pp-4" class="block py-1.5 px-3 rounded-xl hover:bg-white/80 hover:text-[#8E1E26] transition">4. Cookie Storage</a>
                                <a href="#pp-5" class="block py-1.5 px-3 rounded-xl hover:bg-white/80 hover:text-[#8E1E26] transition">5. Third Parties</a>
                                <a href="#pp-6" class="block py-1.5 px-3 rounded-xl hover:bg-white/80 hover:text-[#8E1E26] transition">6. Account Deletion</a>
                            </nav>
                        </div>

                        {{-- Privacy Commitment Callout --}}
                        <div class="rounded-2xl bg-[#FDFBF7] p-5 border border-[#3C2415]/10 shadow-xs space-y-2">
                            <div class="flex items-center gap-2 text-xs font-bold text-[#8E1E26] uppercase">
                                <span>🔒 Zero Ad-Tracking Guarantee</span>
                            </div>
                            <p class="text-[11px] text-[#3C2415]/70 leading-relaxed">
                                We respect your privacy. Daisy Bakery never sells customer data or uses invasive ad-tracking pixels.
                            </p>
                        </div>
                    </div>

                    {{-- Right Column: Full Privacy Content --}}
                    <div class="lg:col-span-8 space-y-6 text-[#3C2415]/85 text-xs sm:text-sm leading-relaxed">
                        <section id="pp-1" class="rounded-[24px] bg-[#F5ECE3] p-7 border border-[#3C2415]/15 shadow-sm space-y-3">
                            <h3 class="font-display font-bold text-base text-[#3C2415] uppercase tracking-tight">1. Information We Collect</h3>
                            <p>
                                We collect personal details you voluntarily provide when creating an account or placing an order: full name, WhatsApp telephone number, optional email address, profile avatar, and order transaction history.
                            </p>
                        </section>

                        <section id="pp-2" class="rounded-[24px] bg-[#F5ECE3] p-7 border border-[#3C2415]/15 shadow-sm space-y-3">
                            <h3 class="font-display font-bold text-base text-[#3C2415] uppercase tracking-tight">2. How We Use Data</h3>
                            <p>
                                Your data is utilized strictly for operational bakery purposes: managing your account, verifying order payments, maintaining your loyalty points and stamp counts, and providing direct customer support when necessary.
                            </p>
                        </section>

                        <section id="pp-3" class="rounded-[24px] bg-[#F5ECE3] p-7 border border-[#3C2415]/15 shadow-sm space-y-3">
                            <h3 class="font-display font-bold text-base text-[#3C2415] uppercase tracking-tight">3. Security & Hashing</h3>
                            <p>
                                We employ robust encryption protocols and Bcrypt password hashing. We never store raw banking PINs, payment card security codes, or unencrypted passwords on our servers.
                            </p>
                        </section>

                        <section id="pp-4" class="rounded-[24px] bg-[#F5ECE3] p-7 border border-[#3C2415]/15 shadow-sm space-y-3">
                            <h3 class="font-display font-bold text-base text-[#3C2415] uppercase tracking-tight">4. Cookies & Session Storage</h3>
                            <p>
                                Our application uses strictly functional cookies necessary to keep you securely signed in and preserve the items in your shopping bag. We do not use third-party marketing tracking cookies.
                            </p>
                        </section>

                        <section id="pp-5" class="rounded-[24px] bg-[#F5ECE3] p-7 border border-[#3C2415]/15 shadow-sm space-y-3">
                            <h3 class="font-display font-bold text-base text-[#3C2415] uppercase tracking-tight">5. Third-Party Sharing</h3>
                            <p>
                                Daisy Bakery will never monetize, lease, or distribute your personal records to third parties. Information is shared only with verified operational partners (such as payment verification channels) solely to fulfill your purchases.
                            </p>
                        </section>

                        <section id="pp-6" class="rounded-[24px] bg-[#F5ECE3] p-7 border border-[#3C2415]/15 shadow-sm space-y-3">
                            <h3 class="font-display font-bold text-base text-[#3C2415] uppercase tracking-tight">6. Account Deletion & Member Rights</h3>
                            <p>
                                You retain full ownership of your data. You may review and update your profile anytime, or permanently delete your account through the <strong>Edit Profile</strong> interface. Deletion completely removes your personal details and accumulated reward balances from our active records.
                            </p>
                        </section>
                    </div>
                </div>
            @endif

        </div>
    </div>

@endsection
