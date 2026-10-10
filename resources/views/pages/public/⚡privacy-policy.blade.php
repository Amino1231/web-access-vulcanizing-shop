<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.privacy-policy')] class extends Component
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
                Privacy Policy
            </h1>
            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                Last updated: {{ $lastUpdated }}
            </p>
        </div>

        {{-- Intro --}}
        <div class="prose prose-slate dark:prose-invert max-w-none
                    prose-headings:font-bold prose-headings:text-gray-900 dark:prose-headings:text-white
                    prose-p:text-gray-600 dark:prose-p:text-gray-300
                    prose-li:text-gray-600 dark:prose-li:text-gray-300
                    prose-a:text-[#FF5E14] prose-a:no-underline hover:prose-a:underline
                    prose-strong:text-gray-900 dark:prose-strong:text-white">

            <p class="lead text-lg">
                VulcanPro ("we", "us", "our") respects your privacy and is committed to protecting the personal
                information you share with us. This Privacy Policy explains what data we collect, why we collect it,
                how we use it, and the rights you have under the <strong>Data Privacy Act of 2012 (Republic Act No. 10173)</strong>
                and its Implementing Rules and Regulations.
            </p>

            <p>
                By using VulcanPro — whether as a <strong>Customer</strong> or as a <strong>Shop Owner</strong> — you agree to
                the practices described in this policy. If you do not agree, please do not use the platform.
            </p>

            <hr>

            <h2>1. Who We Are</h2>
            <p>
                VulcanPro is an online platform that connects customers with vulcanizing and tire service shops.
                We provide shop discovery, appointment booking, real-time availability information, integrated
                mapping, and payment processing services.
            </p>
            <p>
                We act as a <strong>Personal Information Controller</strong> for the data you provide when you
                register and use the platform. Individual shops on the platform act as separate Personal
                Information Controllers for any data they collect directly from you (for example, when you
                arrive at the shop for a service).
            </p>

            <h2>2. Information We Collect</h2>

            <h3>2.1 Information you give us</h3>
            <ul>
                <li><strong>Account information</strong>: full name, email address, password (stored hashed), and optionally gender and birth date.</li>
                <li><strong>Contact information</strong>: phone number, physical address, and profile photo.</li>
                <li><strong>Vehicle information</strong>: plate number, make, model, year, and vehicle type — used to match you with appropriate services.</li>
                <li><strong>Appointment details</strong>: chosen service, preferred date and time, notes, and preferred staff (where available).</li>
                <li><strong>Order and transaction information</strong>: items purchased, prices, payment method, invoice details, and service history.</li>
                <li><strong>Reviews and feedback</strong>: ratings and written reviews you submit about shops and services.</li>
                <li><strong>Shop Owner data</strong>: business name, business address, business contact details, business logo, business permit documents, and business hours. We may also collect government-issued identification or permit documents to verify your shop.</li>
            </ul>

            <h3>2.2 Information we collect automatically</h3>
            <ul>
                <li><strong>Device and log data</strong>: IP address, browser type, operating system, and pages visited.</li>
                <li><strong>Usage data</strong>: which features you use, when you use them, and how often.</li>
                <li><strong>Location data</strong>: with your permission, we collect your device's location to show nearby shops, calculate distances, and enable real-time roadside assistance. You can disable location access in your device settings at any time.</li>
            </ul>

            <h3>2.3 Information from third parties</h3>
            <ul>
                <li><strong>Payment processors</strong>: we receive confirmation of successful or failed payments and a transaction reference. We do <strong>not</strong> store your full card number, CVV, or online banking credentials.</li>
                <li><strong>Mapping services</strong>: coordinates are exchanged with our mapping provider to render maps and calculate routes.</li>
            </ul>

            <h2>3. How We Use Your Information</h2>
            <p>We use your personal information to:</p>
            <ul>
                <li>Create and manage your account.</li>
                <li>Process appointments, orders, and payments.</li>
                <li>Show you shops, services, and availability relevant to your location.</li>
                <li>Send transactional notifications — booking confirmations, appointment reminders, order status updates, and password resets.</li>
                <li>Verify and approve shop applications, and detect fraudulent or duplicate listings.</li>
                <li>Respond to your support requests and complaints.</li>
                <li>Improve the platform through aggregated, anonymized analytics.</li>
                <li>Comply with legal obligations and enforce our Terms of Service.</li>
            </ul>

            <h2>4. Legal Basis for Processing</h2>
            <p>Under the Data Privacy Act, we process your data on the following legal bases:</p>
            <ul>
                <li><strong>Contract</strong> — to provide the services you requested (booking, ordering, account management).</li>
                <li><strong>Consent</strong> — for optional features such as location access, marketing emails, and profile photos.</li>
                <li><strong>Legitimate interests</strong> — for fraud prevention, security, and platform improvements.</li>
                <li><strong>Legal obligation</strong> — to comply with tax, accounting, and law enforcement requests.</li>
            </ul>

            <h2>5. Sharing Your Information</h2>
            <p>We share your information only as necessary:</p>
            <ul>
                <li><strong>With shops</strong>: when you book an appointment or place an order, the shop receives your name, phone number, vehicle details, appointment time, and any notes you provide. This is required to perform the service.</li>
                <li><strong>With payment processors</strong>: to process your payment. We transmit only the minimum information required for the transaction.</li>
                <li><strong>With mapping providers</strong>: coordinates only, to render maps.</li>
                <li><strong>With service providers</strong>: email delivery, SMS, cloud hosting, and analytics providers, under contracts that require them to protect your data.</li>
                <li><strong>With authorities</strong>: when required by law, court order, or to protect the safety of our users.</li>
            </ul>
            <p>
                We do <strong>not sell</strong> your personal information to third parties.
            </p>

            <h2>6. Data Retention</h2>
            <p>
                We retain your personal information for as long as your account is active, or as needed to provide
                the services. Transaction records (invoices, payments, and service history) are retained for the period
                required by Philippine tax and accounting laws — typically <strong>ten (10) years</strong>.
            </p>
            <p>
                When you delete your account, we delete or anonymize your personal data within 30 days, except
                for records we are legally required to retain.
            </p>

            <h2>7. Your Rights Under the Data Privacy Act</h2>
            <p>You have the right to:</p>
            <ul>
                <li><strong>Be informed</strong> — know what data we collect and why.</li>
                <li><strong>Access</strong> — request a copy of the personal data we hold about you.</li>
                <li><strong>Correct</strong> — ask us to fix inaccurate or outdated information.</li>
                <li><strong>Erasure or blocking</strong> — request deletion of your data, subject to legal retention requirements.</li>
                <li><strong>Object</strong> — object to processing based on legitimate interests.</li>
                <li><strong>Data portability</strong> — receive your data in a structured, commonly used format.</li>
                <li><strong>Lodge a complaint</strong> — file a complaint with the National Privacy Commission (NPC).</li>
                <li><strong>Damages</strong> — claim compensation for damages sustained due to inaccurate, incomplete, or unlawfully obtained data.</li>
            </ul>
            <p>
                To exercise any of these rights, email us at
                <a href="mailto:privacy@vulcanpro.test">privacy@vulcanpro.test</a>. We will respond within
                <strong>fifteen (15) working days</strong>.
            </p>

            <h2>8. Security</h2>
            <p>
                We use industry-standard measures to protect your data, including:
            </p>
            <ul>
                <li>Encrypted transmission (HTTPS/TLS).</li>
                <li>Hashed passwords using modern algorithms.</li>
                <li>Access controls restricting employee access to personal data.</li>
                <li>Regular security reviews and dependency updates.</li>
                <li>Rate limiting and monitoring to detect abuse.</li>
            </ul>
            <p>
                No system is 100% secure. If we become aware of a breach affecting your data, we will notify
                you and the NPC within the timeframe required by law.
            </p>

            <h2>9. Cookies and Tracking</h2>
            <p>
                We use cookies and similar technologies to keep you signed in, remember your preferences, and
                analyze traffic. You can disable cookies in your browser, but some parts of the platform
                (including login) may not function properly.
            </p>

            <h2>10. Children</h2>
            <p>
                VulcanPro is not intended for users under 18 years of age. We do not knowingly collect personal
                information from minors. If you believe a minor has created an account, contact us and we will
                delete it.
            </p>

            <h2>11. International Transfers</h2>
            <p>
                Some of our service providers (cloud hosting, email, mapping) may store data outside the Philippines.
                When we transfer your data internationally, we take steps to ensure it receives a comparable
                level of protection — such as contractual clauses and privacy certifications.
            </p>

            <h2>12. Changes to This Policy</h2>
            <p>
                We may update this Privacy Policy from time to time. When we make significant changes, we will
                notify you via email or an in-app notice at least <strong>7 days</strong> before the changes take effect.
                Continued use of the platform after that date constitutes acceptance of the updated policy.
            </p>

            <h2>13. Contact Us</h2>
            <p>
                For questions, concerns, or to exercise your data privacy rights:
            </p>
            <ul>
                <li><strong>Data Protection Officer</strong>: dpo@vulcanpro.test</li>
                <li><strong>General inquiries</strong>: support@vulcanpro.test</li>
                <li><strong>Mailing address</strong>: [Your registered business address]</li>
            </ul>
            <p>
                If you are not satisfied with our response, you may file a complaint with the
                <strong>National Privacy Commission</strong> at
                <a href="https://www.privacy.gov.ph" target="_blank" rel="noopener">www.privacy.gov.ph</a>.
            </p>
        </div>
    </div>
</div>