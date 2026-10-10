<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.terms-of-service')] class extends Component
{
    public string $lastUpdated = 'October 8, 2026';
};

?>

<div class="bg-white dark:bg-gray-950 min-h-screen">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 py-12 lg:py-16">

        {{-- Header --}}
        <div class="mb-10">
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-[#FF5E14]">Legal</p>
            <h1 class="mt-2 text-4xl sm:text-5xl font-extrabold tracking-tight text-gray-900 dark:text-white">
                Terms of Service
            </h1>
            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                Last updated: {{ $lastUpdated }}
            </p>
        </div>

        <div class="prose prose-slate dark:prose-invert max-w-none
                    prose-headings:font-bold prose-headings:text-gray-900 dark:prose-headings:text-white
                    prose-p:text-gray-600 dark:prose-p:text-gray-300
                    prose-li:text-gray-600 dark:prose-li:text-gray-300
                    prose-a:text-[#FF5E14] prose-a:no-underline hover:prose-a:underline
                    prose-strong:text-gray-900 dark:prose-strong:text-white">

            <p class="lead text-lg">
                These Terms of Service ("Terms") govern your access to and use of the VulcanPro platform. By
                registering an account or using any part of the platform, you agree to be bound by these Terms.
            </p>

            <p>
                If you do not agree to these Terms, you must not use VulcanPro.
            </p>

            <hr>

            <h2>1. Definitions</h2>
            <ul>
                <li><strong>"Platform"</strong> means the VulcanPro website and any related mobile or web applications.</li>
                <li><strong>"Customer"</strong> means any user who registers to book appointments, order products, or request services.</li>
                <li><strong>"Shop Owner"</strong> means any user who registers a vulcanizing shop on the platform.</li>
                <li><strong>"Shop"</strong> means a business listed on the platform offering vulcanizing, tire, or related services.</li>
                <li><strong>"Service"</strong> means any service offered by a Shop through the platform.</li>
                <li><strong>"Content"</strong> means text, images, reviews, photos, and any other material uploaded or submitted by users.</li>
            </ul>

            <h2>2. Eligibility</h2>
            <p>
                You must be at least <strong>18 years old</strong> and legally capable of entering into binding contracts
                to use the platform. By using VulcanPro, you represent and warrant that you meet these requirements.
            </p>

            <h2>3. Account Registration</h2>
            <ul>
                <li>You agree to provide accurate, current, and complete information during registration.</li>
                <li>You are responsible for maintaining the confidentiality of your password and account.</li>
                <li>You are responsible for all activity that occurs under your account.</li>
                <li>Notify us immediately if you suspect unauthorized access to your account.</li>
                <li>One person may not maintain multiple Customer accounts. We may suspend or merge duplicate accounts.</li>
            </ul>

            <h2>4. Shop Owner Obligations</h2>
            <p>If you register as a Shop Owner, you additionally agree to:</p>
            <ul>
                <li>Provide true, accurate, and current business information, including valid business permits and licenses where required.</li>
                <li>Keep your business hours, service list, prices, and contact details up to date at all times.</li>
                <li>Respond to appointment requests in a timely manner.</li>
                <li>Honor appointments and orders you accept, or notify customers promptly if you cannot.</li>
                <li>Perform all services in a professional, safe, and lawful manner, in accordance with applicable local regulations.</li>
                <li>Maintain appropriate insurance and licenses required to operate a vulcanizing or automotive service business.</li>
                <li>Refrain from misleading, deceptive, or fraudulent practices.</li>
                <li>Cooperate with reasonable dispute resolution requests from VulcanPro.</li>
            </ul>
            <p>
                We reserve the right to <strong>suspend or terminate</strong> any Shop at any time for violations of
                these Terms, with or without prior notice.
            </p>

            <h2>5. Appointments and Bookings</h2>
            <ul>
                <li>When you book an appointment, you enter into a direct agreement with the Shop. VulcanPro is not a party to that agreement.</li>
                <li>Shops may accept, reschedule, or reject appointments based on availability and business needs.</li>
                <li>Customers must arrive on time. Shops may cancel or reschedule appointments for late arrivals.</li>
                <li>Repeated no-shows may result in suspension of booking privileges.</li>
                <li>Availability shown on the platform is <strong>indicative</strong> and may change. A confirmed booking is required to guarantee a slot.</li>
            </ul>

            <h2>6. Real-Time Availability and Mapping</h2>
            <ul>
                <li>Availability information, opening hours, and shop status are provided by Shop Owners and updated by them. We do not guarantee accuracy.</li>
                <li>Location coordinates are used for display, distance calculation, and navigation. Actual travel times may vary.</li>
                <li>Mapping data may be provided by third-party providers and is subject to their own terms and accuracy limitations.</li>
                <li>Roadside assistance is subject to the responding shop's own capabilities, pricing, and service area.</li>
            </ul>

            <h2>7. Orders and Payments</h2>
            <ul>
                <li>Prices displayed on the platform are set by Shops and are inclusive of applicable taxes unless stated otherwise.</li>
                <li>Payment may be made by cash, online payment, or other methods supported by the platform.</li>
                <li>Online payments are processed by third-party payment providers. We do not store your full card details or banking credentials.</li>
                <li>Refunds, cancellations, and chargebacks for services are handled by the Shop unless the issue relates directly to a platform malfunction.</li>
                <li>For payments processed through the platform, we may retain a service fee or commission as disclosed in your Shop Owner agreement.</li>
                <li>Disputes about payment must be raised within <strong>seven (7) days</strong> of the transaction.</li>
            </ul>

            <h2>8. Cancellations and Refunds</h2>
            <ul>
                <li><strong>Customers</strong> may cancel appointments up to <strong>2 hours</strong> before the scheduled time without penalty. Shops may charge for late cancellations or no-shows.</li>
                <li><strong>Shops</strong> may cancel a booking if the customer does not arrive within a reasonable time or provides incorrect information.</li>
                <li>Refunds for prepaid services are at the Shop's discretion unless required by law.</li>
                <li>Platform-level payment errors are corrected within <strong>3–5 business days</strong>.</li>
            </ul>

            <h2>9. Reviews and User Content</h2>
            <ul>
                <li>You are solely responsible for the Content you submit.</li>
                <li>You must not submit Content that is false, defamatory, obscene, threatening, or that violates any law.</li>
                <li>You grant VulcanPro a non-exclusive, worldwide, royalty-free license to display, reproduce, and distribute your Content as part of the platform.</li>
                <li>We may remove Content that violates these Terms or that we believe is harmful to users or the platform.</li>
                <li>Reviews must reflect genuine experiences. Fake or incentivized reviews will be removed and may result in account suspension.</li>
            </ul>

            <h2>10. Prohibited Conduct</h2>
            <p>You agree not to:</p>
            <ul>
                <li>Use the platform for any unlawful purpose.</li>
                <li>Impersonate any person or entity.</li>
                <li>Interfere with the operation of the platform or attempt to access it using automated tools.</li>
                <li>Introduce viruses, malware, or any harmful code.</li>
                <li>Harvest or scrape user data.</li>
                <li>Circumvent fees, bypass the platform, or encourage other users to do so.</li>
                <li>Post false information about shops, services, or users.</li>
                <li>Use the platform to harass, threaten, or discriminate against anyone.</li>
            </ul>
            <p>
                Violation of these rules may result in immediate suspension or termination of your account
                and, where appropriate, referral to law enforcement.
            </p>

            <h2>11. Intellectual Property</h2>
            <ul>
                <li>The VulcanPro name, logo, software, design, and all original content are owned by us and protected by copyright and trademark laws.</li>
                <li>You may not copy, modify, distribute, or reverse-engineer any part of the platform without our written consent.</li>
                <li>Shop logos and uploaded materials remain the property of the respective Shop Owner.</li>
            </ul>

            <h2>12. Disclaimers</h2>
            <p>
                The platform is provided <strong>"as is"</strong> and <strong>"as available"</strong>. We do not warrant
                that:
            </p>
            <ul>
                <li>The platform will be uninterrupted, error-free, or free of viruses.</li>
                <li>Information provided by Shops is accurate or complete.</li>
                <li>Services performed by Shops will meet your expectations.</li>
                <li>Any errors in the platform will be corrected immediately.</li>
            </ul>
            <p>
                VulcanPro is a <strong>venue</strong> connecting Customers and Shops. We are not responsible for the
                quality, safety, or legality of services provided by Shops, nor for the acts or omissions of either party.
            </p>

            <h2>13. Limitation of Liability</h2>
            <p>
                To the fullest extent permitted by law, VulcanPro shall not be liable for any indirect, incidental,
                consequential, or punitive damages — including loss of profits, data, or goodwill — arising out of
                or related to your use of the platform.
            </p>
            <p>
                Our total liability to you for any claim related to the platform shall not exceed the total amount
                you paid to us in the <strong>three (3) months</strong> preceding the claim.
            </p>

            <h2>14. Indemnification</h2>
            <p>
                You agree to indemnify, defend, and hold harmless VulcanPro and its officers, directors, employees,
                and agents from any claims, losses, damages, and expenses (including reasonable attorney's fees)
                arising from:
            </p>
            <ul>
                <li>Your use of the platform.</li>
                <li>Your violation of these Terms.</li>
                <li>Your violation of any third-party rights.</li>
                <li>Content you submit.</li>
            </ul>

            <h2>15. Suspension and Termination</h2>
            <ul>
                <li>We may suspend or terminate your account at any time for violations of these Terms or for conduct we deem harmful to the platform or other users.</li>
                <li>You may delete your account at any time via your profile settings or by contacting support.</li>
                <li>On termination, your right to use the platform ends immediately. Sections that by their nature should survive termination (privacy, liability, indemnity) will continue to apply.</li>
            </ul>

            <h2>16. Dispute Resolution</h2>
            <p>
                Disputes between Customers and Shop Owners should first be resolved directly between the parties.
                If unresolved, either party may request mediation through VulcanPro by emailing
                <a href="mailto:disputes@vulcanpro.test">disputes@vulcanpro.test</a>. We may mediate but are not
                obligated to resolve disputes between users.
            </p>
            <p>
                Any dispute arising from these Terms shall be governed by the laws of the
                <strong>Republic of the Philippines</strong> and subject to the exclusive jurisdiction of the
                courts of <strong>[City of Business Registration]</strong>, Philippines.
            </p>

            <h2>17. Changes to These Terms</h2>
            <p>
                We may modify these Terms at any time. We will notify you of material changes via email or an
                in-app notice at least <strong>7 days</strong> before they take effect. Your continued use of the
                platform after that date constitutes acceptance of the revised Terms.
            </p>

            <h2>18. Severability</h2>
            <p>
                If any provision of these Terms is found to be invalid or unenforceable, the remaining provisions
                shall remain in full force and effect.
            </p>

            <h2>19. Entire Agreement</h2>
            <p>
                These Terms, together with the Privacy Policy and any Shop Owner agreement, constitute the entire
                agreement between you and VulcanPro regarding the platform.
            </p>

            <h2>20. Contact</h2>
            <p>For questions about these Terms:</p>
            <ul>
                <li><strong>Support</strong>: support@vulcanpro.test</li>
                <li><strong>Legal</strong>: legal@vulcanpro.test</li>
                <li><strong>Mailing address</strong>: [Your registered business address]</li>
            </ul>
        </div>
    </div>
</div>